<?php

declare(strict_types=1);

use GlpiPlugin\Uimanager\ConfigurationController;

include '../../../inc/includes.php';
require_once dirname(__DIR__) . '/inc/autoload.php';

ConfigurationController::authorize();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

try {
    ConfigurationController::process($_POST);
    Session::addMessageAfterRedirect(
        __('UI visibility settings saved.', 'uimanager'),
        true,
        INFO
    );
} catch (InvalidArgumentException | RuntimeException $exception) {
    Session::addMessageAfterRedirect($exception->getMessage(), true, ERROR);
}

Html::redirect($CFG_GLPI['root_doc'] . '/plugins/uimanager/front/config.php');
