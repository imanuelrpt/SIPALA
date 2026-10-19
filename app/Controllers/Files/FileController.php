<?php

declare(strict_types=1);

namespace App\Controllers\Files;

use App\Controllers\BaseController;
use App\Enums\ActivityAction;
use App\Services\ComplaintService;
use App\Services\ImageUploadService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Penyajian berkas unggahan.
 *
 * Berkas disimpan di `writable/uploads` (di luar public) sehingga hanya dapat
 * diakses lewat rute ini, setelah pemeriksaan hak akses.
 */
final class FileController extends BaseController
{
    public function show(int $id): ResponseInterface
    {
        $user = $this->currentUser;

        if ($user === null) {
            throw new PageNotFoundException('Lampiran tidak dapat diakses.');
        }

        /** @var ComplaintService $complaints */
        $complaints = service('complaintService');

        $attachment = $complaints->attachmentForViewing($user, $id);

        /** @var ImageUploadService $uploads */
        $uploads = service('imageUploads');

        $relativePath = (string) $attachment->file_path;

        if (! $uploads->isManagedPath($relativePath)) {
            throw new PageNotFoundException('Lampiran tidak ditemukan.');
        }

        $absolute = $uploads->absolutePath($relativePath);

        if (! is_file($absolute)) {
            throw new PageNotFoundException('Lampiran tidak ditemukan.');
        }

        $mime = (string) $attachment->mime_type;

        if (! in_array($mime, $this->sipalaConfig->allowedImageMimes, true)) {
            $mime = 'application/octet-stream';
        }

        $contents = file_get_contents($absolute);

        if ($contents === false) {
            throw new PageNotFoundException('Lampiran tidak dapat dibaca.');
        }

        service('activityLogger')->log(
            ActivityAction::AttachmentDownloaded,
            'complaint',
            (int) $attachment->complaint_id,
            'Lampiran #' . $id . ' diakses',
            (int) $user->id,
        );

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Length', (string) strlen($contents))
            ->setHeader('Content-Disposition', 'inline; filename="' . $this->safeFilename((string) $attachment->original_name) . '"')
            ->setHeader('Cache-Control', 'private, max-age=300, no-store')
            ->setBody($contents);
    }

    /**
     * Membersihkan nama berkas sebelum dipakai pada header Content-Disposition.
     */
    private function safeFilename(string $originalName): string
    {
        $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName) ?? 'lampiran';

        return mb_substr($name === '' ? 'lampiran' : $name, 0, 100);
    }
}
