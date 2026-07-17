<?php

declare(strict_types=1);

namespace GlpiPlugin\Uimanager\Branding;

use InvalidArgumentException;
use RuntimeException;

final class BrandingAssets
{
    private const MIME_EXTENSIONS = [
        'image/png' => 'png', 'image/svg+xml' => 'svg', 'image/x-icon' => 'ico',
        'image/vnd.microsoft.icon' => 'ico', 'image/webp' => 'webp', 'image/jpeg' => 'jpg',
    ];

    /** @param array<string, mixed> $file */
    public function store(array $file, int $entityId, string $field): string
    {
        if ((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file((string) ($file['tmp_name'] ?? ''))) {
            throw new InvalidArgumentException('The branding asset upload failed.');
        }
        if ((int) ($file['size'] ?? 0) > 5 * 1024 * 1024) {
            throw new InvalidArgumentException('Branding assets must be 5 MB or smaller.');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file((string) $file['tmp_name']);
        if (!is_string($mime) || !isset(self::MIME_EXTENSIONS[$mime])) {
            throw new InvalidArgumentException('Only PNG, SVG, ICO, WEBP, and JPG assets are supported.');
        }
        if ($mime === 'image/svg+xml') {
            $svg = file_get_contents((string) $file['tmp_name']);
            if (!is_string($svg) || stripos($svg, '<script') !== false || preg_match('/\bon\w+\s*=|javascript:/i', $svg) === 1) {
                throw new InvalidArgumentException('SVG assets may not contain scripts or event handlers.');
            }
        }
        $directory = $this->directory();
        if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
            throw new RuntimeException('Could not create the branding upload directory.');
        }
        $name = sprintf('%d-%s-%s.%s', $entityId, preg_replace('/[^a-z0-9_-]/i', '-', $field), bin2hex(random_bytes(12)), self::MIME_EXTENSIONS[$mime]);
        if (!move_uploaded_file((string) $file['tmp_name'], $directory . DIRECTORY_SEPARATOR . $name)) {
            throw new RuntimeException('Could not store the branding asset.');
        }
        return $name;
    }

    public function remove(string $name): void
    {
        if ($name === '' || basename($name) !== $name) {
            return;
        }
        $path = $this->directory() . DIRECTORY_SEPARATOR . $name;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    public function removeAll(): void
    {
        $directory = $this->directory();
        if (!is_dir($directory)) {
            return;
        }
        foreach (glob($directory . DIRECTORY_SEPARATOR . '*') ?: [] as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
        @rmdir($directory);
    }

    public function path(string $name): ?string
    {
        if ($name === '' || basename($name) !== $name) {
            return null;
        }
        $path = $this->directory() . DIRECTORY_SEPARATOR . $name;
        return is_file($path) ? $path : null;
    }

    private function directory(): string
    {
        $root = defined('GLPI_UPLOAD_DIR') ? GLPI_UPLOAD_DIR : sys_get_temp_dir();
        return rtrim((string) $root, '/\\') . DIRECTORY_SEPARATOR . 'uimanager' . DIRECTORY_SEPARATOR . 'branding';
    }
}
