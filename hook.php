<?php

declare(strict_types=1);

use GlpiPlugin\Assetmenumanager\Config;
use GlpiPlugin\Assetmenumanager\MenuFilter;

require_once __DIR__ . '/inc/autoload.php';

function plugin_assetmenumanager_redefine_menus(array $menus): array
{
    try {
        return (new MenuFilter())->filter($menus, Config::getVisibility());
    } catch (Throwable $exception) {
        if (class_exists(Toolbox::class)) {
            Toolbox::logDebug(
                '[assetmenumanager] Menu filtering was skipped: ' . $exception->getMessage()
            );
        }

        return $menus;
    }
}
