<?php

declare(strict_types=1);

use GlpiPlugin\Uimanager\Config;
use GlpiPlugin\Uimanager\ConfigurationController;
use GlpiPlugin\Uimanager\ConfigurationPageRenderer;

include '../../../inc/includes.php';
require_once dirname(__DIR__) . '/inc/autoload.php';

ConfigurationController::authorize();

Html::header(
    __('GLPI UI Manager', 'uimanager'),
    $_SERVER['PHP_SELF'],
    'config',
    'plugin'
);

ConfigurationPageRenderer::render(Config::getVisibility());

Html::footer();
