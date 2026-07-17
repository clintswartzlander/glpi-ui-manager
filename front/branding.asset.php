<?php

declare(strict_types=1);

use GlpiPlugin\Uimanager\Branding\BrandingAssets;

include '../../../inc/includes.php';
require_once dirname(__DIR__) . '/inc/autoload.php';
$file = basename((string) ($_GET['file'] ?? ''));
if ($file === '' || ($path = (new BrandingAssets())->path($file)) === null) { http_response_code(404); exit; }
$mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
header('Content-Type: ' . ($mime ?: 'application/octet-stream'));
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, max-age=31536000, immutable');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'");
readfile($path);
