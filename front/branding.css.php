<?php

declare(strict_types=1);

use GlpiPlugin\Uimanager\Branding\BrandingCssGenerator;
use GlpiPlugin\Uimanager\Branding\BrandingHooks;
use GlpiPlugin\Uimanager\Branding\BrandingManager;

include '../../../inc/includes.php';
require_once dirname(__DIR__) . '/inc/autoload.php';
header('Content-Type: text/css; charset=UTF-8');
header('Cache-Control: private, no-store');
$root = rtrim((string) ($CFG_GLPI['root_doc'] ?? ''), '/');
$branding = (new BrandingManager())->resolve(BrandingHooks::currentEntityId());
echo (new BrandingCssGenerator())->generate($branding, static fn (string $file): string => $root . '/plugins/uimanager/front/branding.asset.php?file=' . rawurlencode($file));
