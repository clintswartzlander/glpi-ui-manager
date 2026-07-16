<?php

declare(strict_types=1);

use GlpiPlugin\Uimanager\ConfigurationController;
use GlpiPlugin\Uimanager\MenuDiagnostic;

include '../../../inc/includes.php';
require_once dirname(__DIR__) . '/inc/autoload.php';

ConfigurationController::authorize();

$diagnostic = $_SESSION['uimanager_menu_diagnostic'] ?? MenuDiagnostic::sanitize([]);
if (!is_array($diagnostic)) {
    $diagnostic = MenuDiagnostic::sanitize([]);
}

header('Content-Type: application/json; charset=UTF-8');
header('Content-Disposition: attachment; filename="uimanager-menu-diagnostic.json"');
header('X-Content-Type-Options: nosniff');
echo json_encode($diagnostic, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
