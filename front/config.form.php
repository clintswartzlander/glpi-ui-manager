<?php

declare(strict_types=1);

use GlpiPlugin\Assetmenumanager\ConfigController;

include '../../../inc/includes.php';
require_once dirname(__DIR__) . '/inc/autoload.php';

ConfigController::authorize();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

try {
    ConfigController::process($_POST);
    Session::addMessageAfterRedirect(
        __('Asset menu visibility settings saved.', 'assetmenumanager'),
        true,
        INFO
    );
} catch (InvalidArgumentException | RuntimeException $exception) {
    Session::addMessageAfterRedirect($exception->getMessage(), true, ERROR);
}

Html::redirect($CFG_GLPI['root_doc'] . '/plugins/assetmenumanager/front/config.php');
