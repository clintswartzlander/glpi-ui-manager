<?php

declare(strict_types=1);

use GlpiPlugin\Uimanager\Branding\BrandingHooks;
use GlpiPlugin\Uimanager\Branding\ThemeInjection;

include '../../../inc/includes.php';
require_once dirname(__DIR__) . '/inc/autoload.php';
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: private, no-store');
echo json_encode([
    'css' => (new ThemeInjection())->cssForEntity(BrandingHooks::currentEntityId()),
], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
