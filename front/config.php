<?php

declare(strict_types=1);

use GlpiPlugin\Assetmenumanager\Config;
use GlpiPlugin\Assetmenumanager\ConfigController;
use GlpiPlugin\Assetmenumanager\ConfigPageRenderer;

include '../../../inc/includes.php';
require_once dirname(__DIR__) . '/inc/autoload.php';

ConfigController::authorize();

Html::header(
    __('GLPI Asset Menu Manager', 'assetmenumanager'),
    $_SERVER['PHP_SELF'],
    'config',
    'plugin'
);

ConfigPageRenderer::render(Config::getVisibility());

Html::footer();
