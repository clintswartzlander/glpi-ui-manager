<?php

declare(strict_types=1);

use GlpiPlugin\Uimanager\Branding\BrandingAssets;

include '../../../inc/includes.php';
require_once dirname(__DIR__) . '/inc/autoload.php';
$file = (string) ($_GET['file'] ?? '');
$delivery = $file !== '' && basename($file) === $file
    ? (new BrandingAssets())->delivery($file)
    : null;
if ($delivery === null) { http_response_code(404); exit; }
header('Content-Type: ' . $delivery['mime']);
header('Content-Length: ' . $delivery['size']);
header('Content-Disposition: inline; filename="' . rawurlencode($file) . '"');
header('Cache-Control: private, max-age=31536000, immutable');
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'");
readfile($delivery['path']);
