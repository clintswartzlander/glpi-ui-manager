<?php

declare(strict_types=1);

use GlpiPlugin\Uimanager\Branding\BrandingHooks;
use GlpiPlugin\Uimanager\Branding\BrandingManager;

include '../../../inc/includes.php';
require_once dirname(__DIR__) . '/inc/autoload.php';
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: private, no-store');
$branding = (new BrandingManager())->resolve(BrandingHooks::currentEntityId());
$root = rtrim((string) ($CFG_GLPI['root_doc'] ?? ''), '/');
$result = ['application_name' => $branding['application_name'] ?? ''];
foreach (['expanded_logo', 'collapsed_logo', 'login_logo', 'favicon'] as $key) {
    $result[$key] = ($branding[$key] ?? '') === '' ? '' : $root . '/plugins/uimanager/front/branding.asset.php?file=' . rawurlencode($branding[$key]);
}
echo json_encode($result, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
