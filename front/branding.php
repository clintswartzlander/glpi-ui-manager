<?php

declare(strict_types=1);

use GlpiPlugin\Uimanager\Branding\BrandingController;
use GlpiPlugin\Uimanager\Branding\BrandingManager;
use GlpiPlugin\Uimanager\Branding\BrandingPageRenderer;

include '../../../inc/includes.php';
require_once dirname(__DIR__) . '/inc/autoload.php';
BrandingController::authorize();
$entityId = BrandingController::entityId($_GET['entities_id'] ?? ($_SESSION['glpiactive_entity'] ?? 0));
Html::header(__('UI Manager Branding', 'uimanager'), $_SERVER['PHP_SELF'], 'config', 'plugin');
BrandingPageRenderer::render($entityId, new BrandingManager());
Html::footer();
