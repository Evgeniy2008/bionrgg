<?php

namespace App\Support;

use RuntimeException;

class MediaStorage
{
    private const MAX_SIZE = 10485760; // 10 MB

    private string $basePath;
    private string $baseUrl;

    /**
     * @param string $basePath Absolute path to uploads directory
     * @param string $baseUrl  Public URL prefix (e.g. "/uploads")
     */
    public function __construct(string $basePath, string $baseUrl = '/uploads')
    {
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * @param array<string, mixed> $file
     */
    public function storeImage(array $file, string $folder): string
    {
        $this->assertValidUpload($file);

        $mime = $this->detectMime($file['tmp_name']);
        $extension = $this->extensionFromMime($mime);

        $relativeDir = trim($folder, '/');
        $storageDir = $this->basePath . DIRECTORY_SEPARATOR . $relativeDir;
        $this->ensureDirectory($storageDir);

        $filename = uniqid('', true) . '.' . $extension;
        $destination = $storageDir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('Failed to store uploaded file.');
        }

        return $this->baseUrl . '/' . $relativeDir . '/' . $filename;
    }

    public function delete(string $publicPath): void
    {
        $publicPath = trim($publicPath);
        if ($publicPath === '' || !str_starts_with($publicPath, $this->baseUrl)) {
            return;
        }

        $relative = ltrim(substr($publicPath, strlen($this->baseUrl)), '/');
        $absolute = $this->basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);

        if (is_file($absolute)) {
            @unlink($absolute);
        }
    }

    /**
     * @param array<string, mixed> $file
     */
    private function assertValidUpload(array $file): void
    {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('File upload error.');
        }

        if (!isset($file['size']) || (int)$file['size'] > self::MAX_SIZE) {
            throw new RuntimeException('File exceeds maximum size of 10 MB.');
        }

        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('Invalid uploaded file.');
        }
    }

    private function detectMime(string $path): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            throw new RuntimeException('Failed to inspect uploaded file.');
        }

        $mime = finfo_file($finfo, $path);
        finfo_close($finfo);

        if ($mime === false) {
            throw new RuntimeException('Unable to determine file type.');
        }

        return $mime;
    }

    private function extensionFromMime(string $mime): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            default => throw new RuntimeException('Unsupported image format.'),
        };
    }

    private function ensureDirectory(string $path): void
    {
        if (!is_dir($path) && !mkdir($path, 0775, true) && !is_dir($path)) {
            throw new RuntimeException('Unable to create upload directory.');
        }
    }
}


