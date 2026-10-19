<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AttachmentType;
use App\Exceptions\BusinessRuleException;
use CodeIgniter\HTTP\Files\UploadedFile;
use Config\Sipala;

/**
 * Penyimpanan berkas bukti aduan.
 *
 * Pertahanan berlapis:
 *  - whitelist ekstensi + pencocokan MIME asli (finfo) terhadap ekstensi;
 *  - batas ukuran dan jumlah berkas;
 *  - berkas disimpan di writable/ (di luar dokumen root);
 *  - gambar di-encode ulang memakai GD sehingga metadata EXIF/GPS dan
 *    payload tersembunyi terbuang;
 *  - nama berkas diacak penuh (random_bytes).
 */
final class ImageUploadService
{
    /** @var array<string, string> */
    private const EXTENSION_MIME_MAP = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
    ];

    private const MAX_DIMENSION = 6000;

    public function __construct(private readonly Sipala $config)
    {
    }

    /**
     * Menyimpan sekumpulan berkas untuk satu aduan.
     *
     * @param list<UploadedFile> $files
     *
     * @return list<array{file_path: string, original_name: string, mime_type: string, size: int}>
     */
    public function storeMany(array $files, int $complaintId, int $userId, AttachmentType $type, int $alreadyStored = 0): array
    {
        $stored = [];

        try {
            foreach ($files as $file) {
                if ($alreadyStored + count($stored) >= $this->config->uploadMaxFiles) {
                    throw new BusinessRuleException(
                        'Maksimal ' . $this->config->uploadMaxFiles . ' foto per aduan.',
                    );
                }

                $stored[] = $this->store($file, $complaintId, $userId, $type);
            }
        } catch (\Throwable $e) {
            // Jangan tinggalkan berkas yatim bila salah satu gagal.
            foreach ($stored as $item) {
                $this->delete($item['file_path']);
            }

            throw $e;
        }

        return $stored;
    }

    /**
     * @return array{file_path: string, original_name: string, mime_type: string, size: int}
     */
    public function store(UploadedFile $file, int $complaintId, int $userId, AttachmentType $type): array
    {
        $this->assertUploadIsSafe($file);

        $extension = strtolower($file->getClientExtension());
        $mime      = self::EXTENSION_MIME_MAP[$extension];
        $source    = $file->getTempName();

        $dimensions = @getimagesize($source);

        if ($dimensions === false || $dimensions[0] < 1 || $dimensions[1] < 1) {
            throw new BusinessRuleException('Berkas yang diunggah bukan gambar yang valid.');
        }

        if ($dimensions[0] > self::MAX_DIMENSION || $dimensions[1] > self::MAX_DIMENSION) {
            throw new BusinessRuleException('Dimensi gambar terlalu besar (maksimal ' . self::MAX_DIMENSION . 'px).');
        }

        $relativePath = $this->buildRelativePath($extension, $complaintId);
        $absolutePath = $this->absolutePath($relativePath);

        $this->ensureDirectory(dirname($absolutePath));
        $this->reencode($source, $absolutePath, $mime);

        $size = (int) (filesize($absolutePath) ?: 0);

        if ($size <= 0) {
            @unlink($absolutePath);

            throw new BusinessRuleException('Gagal menyimpan berkas. Silakan coba lagi.');
        }

        return [
            'file_path'     => $relativePath,
            'original_name' => mb_substr($file->getClientName(), 0, 150),
            'mime_type'     => $mime,
            'size'          => $size,
        ];
    }

    public function absolutePath(string $relativePath): string
    {
        $relative = str_replace(['..', '\\'], ['', '/'], $relativePath);
        $relative = ltrim($relative, '/');

        return $this->config->uploadPath() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    }

    public function delete(string $relativePath): void
    {
        if (! $this->isManagedPath($relativePath)) {
            return;
        }

        $absolute = $this->absolutePath($relativePath);

        if (is_file($absolute)) {
            @unlink($absolute);
        }
    }

    /**
     * Memastikan path benar-benar berada di dalam folder unggahan.
     */
    public function isManagedPath(string $relativePath): bool
    {
        $absolute = $this->absolutePath($relativePath);
        $root     = realpath($this->config->uploadPath());
        $real     = realpath($absolute);

        if ($root === false || $real === false) {
            return false;
        }

        return str_starts_with($real, $root);
    }

    private function assertUploadIsSafe(UploadedFile $file): void
    {
        if ($file->hasMoved()) {
            throw new BusinessRuleException('Berkas sudah pernah diproses.');
        }

        if ($file->getError() !== UPLOAD_ERR_OK) {
            throw new BusinessRuleException($this->uploadErrorMessage($file->getError()));
        }

        $temp = $file->getTempName();

        if ($temp === '' || ! is_file($temp)) {
            throw new BusinessRuleException('Berkas tidak ditemukan. Silakan unggah ulang.');
        }

        // is_uploaded_file() tidak dapat diuji dari CLI, jadi dilewati saat testing.
        if (ENVIRONMENT !== 'testing' && ! $file->isValid()) {
            throw new BusinessRuleException('Berkas tidak valid.');
        }

        $extension = strtolower($file->getClientExtension());

        if (! in_array($extension, $this->config->allowedImageExtensions, true)) {
            throw new BusinessRuleException('Format berkas harus JPG, JPEG, PNG, atau WEBP.');
        }

        if ($file->getSize() > $this->config->uploadMaxSizeKB * 1024) {
            throw new BusinessRuleException(
                'Ukuran berkas maksimal ' . (int) ($this->config->uploadMaxSizeKB / 1024) . ' MB.',
            );
        }

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($temp);

        if (! is_string($realMime) || ! in_array($realMime, $this->config->allowedImageMimes, true)) {
            throw new BusinessRuleException('Isi berkas bukan gambar yang diizinkan.');
        }

        // MIME asli harus cocok dengan ekstensi yang diklaim.
        if ($realMime !== self::EXTENSION_MIME_MAP[$extension]) {
            throw new BusinessRuleException('Ekstensi berkas tidak sesuai dengan isinya.');
        }
    }

    private function buildRelativePath(string $extension, int $complaintId): string
    {
        $extension = $extension === 'jpeg' ? 'jpg' : $extension;
        $filename  = bin2hex(random_bytes(16)) . '.' . $extension;

        return sprintf(
            'complaints/%s/%s/%d_%s',
            date('Y'),
            date('m'),
            $complaintId,
            $filename,
        );
    }

    private function ensureDirectory(string $directory): void
    {
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new BusinessRuleException('Tidak dapat menyiapkan folder penyimpanan.');
        }
    }

    /**
     * Menulis ulang gambar (menghapus metadata dan payload tersembunyi).
     */
    private function reencode(string $source, string $target, string $mime): void
    {
        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($source),
            'image/png'  => @imagecreatefrompng($source),
            'image/webp' => @imagecreatefromwebp($source),
            default      => false,
        };

        if ($image === false) {
            throw new BusinessRuleException('Gambar tidak dapat diproses.');
        }

        try {
            if ($mime === 'image/jpeg') {
                $canvas = imagecreatetruecolor(imagesx($image), imagesy($image));

                if ($canvas === false) {
                    throw new BusinessRuleException('Gambar tidak dapat diproses.');
                }

                $white = imagecolorallocate($canvas, 255, 255, 255);
                imagefilledrectangle($canvas, 0, 0, imagesx($image), imagesy($image), $white);
                imagecopy($canvas, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
                imagedestroy($image);
                $image = $canvas;

                $written = imagejpeg($image, $target, 85);
            } else {
                imagealphablending($image, false);
                imagesavealpha($image, true);

                $written = $mime === 'image/png'
                    ? imagepng($image, $target, 7)
                    : imagewebp($image, $target, 85);
            }

            if ($written === false) {
                throw new BusinessRuleException('Gagal menulis berkas gambar.');
            }
        } finally {
            if ($image instanceof \GdImage) {
                imagedestroy($image);
            }
        }
    }

    private function uploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ukuran berkas melebihi batas yang diizinkan.',
            UPLOAD_ERR_PARTIAL                        => 'Berkas hanya terunggah sebagian.',
            UPLOAD_ERR_NO_FILE                        => 'Tidak ada berkas yang diunggah.',
            default                                   => 'Terjadi kesalahan saat mengunggah berkas.',
        };
    }
}
