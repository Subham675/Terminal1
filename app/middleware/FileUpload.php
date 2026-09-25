<?php
class FileUpload {
    private const ALLOWED_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    private const MAX_SIZE_BYTES = 3 * 1024 * 1024; // 3 MB

    /**
     * Validates and moves an uploaded image into public/uploads/menu/.
     * Returns the public-relative URL (e.g. "/uploads/menu/xxxx.jpg") or null if no file was sent.
     * Throws RuntimeException on invalid file.
     */
    public static function handleMenuImage(?array $file): ?string {
        if (!$file || empty($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload failed (error code ' . $file['error'] . ').');
        }
        if ($file['size'] > self::MAX_SIZE_BYTES) {
            throw new \RuntimeException('Image must be under 3MB.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset(self::ALLOWED_TYPES[$mime])) {
            throw new \RuntimeException('Only JPG, PNG, or WEBP images are allowed.');
        }

        $ext      = self::ALLOWED_TYPES[$mime];
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $destDir  = dirname(__DIR__, 2) . '/public/uploads/menu';
        if (!is_dir($destDir)) mkdir($destDir, 0775, true);

        $destPath = $destDir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            throw new \RuntimeException('Could not save uploaded file.');
        }

        return '/uploads/menu/' . $filename;
    }

    /** Deletes a previously uploaded menu image (used when replacing/removing). */
    public static function deleteMenuImage(?string $url): void {
        if (!$url || !str_starts_with($url, '/uploads/menu/')) return;
        $path = dirname(__DIR__, 2) . '/public' . $url;
        if (is_file($path)) @unlink($path);
    }
}
