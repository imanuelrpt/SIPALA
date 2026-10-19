<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AttachmentType;
use App\Exceptions\BusinessRuleException;
use CodeIgniter\HTTP\Files\UploadedFile;
use Config\Sipala;
use Tests\Support\TestCase\FeatureTestCase;

/**
 * Keamanan unggahan berkas bukti.
 *
 * Skenario wajib: "unggahan berbahaya ditolak".
 *
 * @internal
 */
final class UploadSecurityTest extends FeatureTestCase
{
    /** @var list<string> */
    private array $tempFiles = [];

    /** @var list<string> */
    private array $storedPaths = [];

    protected function tearDown(): void
    {
        $service = service('imageUploads');

        foreach ($this->storedPaths as $path) {
            $service->delete($path);
        }

        foreach ($this->tempFiles as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        parent::tearDown();
    }

    private function tempFile(string $contents, string $originalName): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'sipala_upload_');

        if ($path === false) {
            $this->fail('Gagal membuat berkas sementara.');
        }

        file_put_contents($path, $contents);
        $this->tempFiles[] = $path;

        return new UploadedFile($path, $originalName, 'image/jpeg', (int) filesize($path), UPLOAD_ERR_OK);
    }

    private function pngBytes(int $width = 48, int $height = 48): string
    {
        $image = imagecreatetruecolor($width, $height);

        if ($image === false) {
            $this->fail('GD tidak tersedia.');
        }

        $color = imagecolorallocate($image, 12, 180, 90);
        imagefilledrectangle($image, 0, 0, $width, $height, $color);

        ob_start();
        imagepng($image);
        $bytes = (string) ob_get_clean();

        imagedestroy($image);

        return $bytes;
    }

    public function testPhpPayloadDisguisedAsJpegIsRejected(): void
    {
        $file = $this->tempFile("<?php system('id'); ?>", 'foto.jpg');

        $this->expectException(BusinessRuleException::class);

        service('imageUploads')->store($file, 1, 1, AttachmentType::BuktiAduan);
    }

    public function testUnsupportedExtensionIsRejected(): void
    {
        $file = $this->tempFile($this->pngBytes(), 'foto.php');

        $this->expectException(BusinessRuleException::class);

        service('imageUploads')->store($file, 1, 1, AttachmentType::BuktiAduan);
    }

    public function testExtensionMustMatchActualFileContent(): void
    {
        // Isi PNG tetapi dinamai .jpg → MIME asli tidak cocok dengan ekstensi.
        $file = $this->tempFile($this->pngBytes(), 'foto.jpg');

        $this->expectException(BusinessRuleException::class);

        service('imageUploads')->store($file, 1, 1, AttachmentType::BuktiAduan);
    }

    public function testOversizedFileIsRejected(): void
    {
        $config = config(Sipala::class);
        $padding = str_repeat('A', ($config->uploadMaxSizeKB * 1024) + 1024);

        $file = $this->tempFile($this->pngBytes() . $padding, 'foto.png');

        $this->expectException(BusinessRuleException::class);

        service('imageUploads')->store($file, 1, 1, AttachmentType::BuktiAduan);
    }

    public function testExcessiveImageDimensionsAreRejected(): void
    {
        $file = $this->tempFile($this->pngBytes(6001, 2), 'foto.png');

        $this->expectException(BusinessRuleException::class);

        service('imageUploads')->store($file, 1, 1, AttachmentType::BuktiAduan);
    }

    public function testValidImageIsReencodedAndStoredOutsidePublicDirectory(): void
    {
        $source = $this->pngBytes();
        $file   = $this->tempFile($source, 'bukti.png');

        $stored = service('imageUploads')->store($file, 7, 1, AttachmentType::BuktiAduan);
        $this->storedPaths[] = $stored['file_path'];

        $this->assertSame('image/png', $stored['mime_type']);
        $this->assertSame('bukti.png', $stored['original_name']);
        $this->assertGreaterThan(0, $stored['size']);

        // Nama berkas diacak sepenuhnya dan tidak memakai nama asli.
        $this->assertMatchesRegularExpression(
            '#^complaints/\d{4}/\d{2}/7_[0-9a-f]{32}\.png$#',
            $stored['file_path'],
        );
        $this->assertStringNotContainsString('bukti', $stored['file_path']);

        $absolute = service('imageUploads')->absolutePath($stored['file_path']);

        $this->assertFileExists($absolute);
        $this->assertStringStartsWith(
            realpath(WRITEPATH . 'uploads'),
            (string) realpath($absolute),
            'Berkas harus tersimpan di dalam writable/uploads (di luar public).',
        );
        $this->assertStringNotContainsString(
            (string) realpath(FCPATH),
            (string) realpath($absolute),
            'Berkas tidak boleh berada di dalam public/.',
        );

        // Gambar di-encode ulang oleh GD sehingga byte-nya tidak identik.
        $this->assertNotSame($source, (string) file_get_contents($absolute));
        $this->assertNotFalse(getimagesize($absolute));
    }

    public function testTooManyFilesAreRejected(): void
    {
        $files = [];

        for ($i = 0; $i < 4; $i++) {
            $files[] = $this->tempFile($this->pngBytes(), 'bukti-' . $i . '.png');
        }

        $directory = config(Sipala::class)->uploadPath('complaints/' . date('Y/m'));
        $before    = $this->relativeFiles($directory);

        $complaintId = random_int(100000, 999999);

        try {
            service('imageUploads')->storeMany($files, $complaintId, 1, AttachmentType::BuktiAduan);
            $this->fail('Melebihi batas jumlah berkas seharusnya ditolak.');
        } catch (BusinessRuleException $e) {
            $this->assertStringContainsString('Maksimal', $e->getMessage());
        }

        // Tidak boleh ada berkas yatim yang tersisa.
        $this->assertSame(
            $before,
            $this->relativeFiles($directory),
            'Tidak boleh ada berkas tersimpan setelah unggahan ditolak.',
        );

        if ($directory !== null && is_dir($directory)) {
            $left = glob($directory . DIRECTORY_SEPARATOR . $complaintId . '_*');
            $this->assertSame([], $left === false ? [] : $left);
        }
    }

    /**
     * Daftar nama berkas di dalam sebuah direktori (bila ada).
     *
     * @return list<string>
     */
    private function relativeFiles(?string $directory): array
    {
        if ($directory === null || ! is_dir($directory)) {
            return [];
        }

        $files = scandir($directory);

        if ($files === false) {
            return [];
        }

        return array_values(array_diff($files, ['.', '..']));
    }
}
