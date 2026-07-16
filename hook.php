<?php

declare(strict_types=1);

use GlpiPlugin\Uimanager\Config;
use GlpiPlugin\Uimanager\MenuFilter;
use GlpiPlugin\Uimanager\MenuDiagnostic;

require_once __DIR__ . '/inc/autoload.php';

function plugin_uimanager_redefine_menus(array $menus): array
{
    try {
        if (class_exists(Session::class) && Session::haveRight('config', UPDATE)) {
            $_SESSION['uimanager_menu_diagnostic'] = MenuDiagnostic::sanitize($menus);
        }
        return (new MenuFilter())->filter($menus, Config::getVisibility());
    } catch (Throwable $exception) {
        if (class_exists(Toolbox::class)) {
            Toolbox::logDebug(
                '[uimanager] Menu filtering was skipped: ' . $exception->getMessage()
            );
        }

        return $menus;
    }
}
