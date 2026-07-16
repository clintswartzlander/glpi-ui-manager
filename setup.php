<?php

declare(strict_types=1);

use GlpiPlugin\Assetmenumanager\Config;

define('PLUGIN_ASSETMENUMANAGER_VERSION', '1.0.0');
define('PLUGIN_ASSETMENUMANAGER_MIN_GLPI', '11.0.0');
define('PLUGIN_ASSETMENUMANAGER_MAX_GLPI', '11.0.99');

require_once __DIR__ . '/inc/autoload.php';
require_once __DIR__ . '/hook.php';

function plugin_init_assetmenumanager(): void
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['assetmenumanager'] = true;
    $PLUGIN_HOOKS['config_page']['assetmenumanager'] = 'front/config.php';
    $PLUGIN_HOOKS['redefine_menus']['assetmenumanager'] = 'plugin_assetmenumanager_redefine_menus';

}

function plugin_version_assetmenumanager(): array
{
    return [
        'name'         => 'GLPI Asset Menu Manager',
        'version'      => PLUGIN_ASSETMENUMANAGER_VERSION,
        'author'       => 'Clint Swartzlander and contributors',
        'license'      => 'GPL-3.0-or-later',
        'homepage'     => 'https://github.com/clintswartzlander/glpi-asset-menu-manager',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_ASSETMENUMANAGER_MIN_GLPI,
                'max' => PLUGIN_ASSETMENUMANAGER_MAX_GLPI,
            ],
            'php' => ['min' => '8.2.0'],
        ],
    ];
}

function plugin_assetmenumanager_check_prerequisites(): bool
{
    return version_compare(PHP_VERSION, '8.2.0', '>=');
}

function plugin_assetmenumanager_check_config(bool $verbose = false): bool
{
    return true;
}

function plugin_assetmenumanager_install(): bool
{
    return Config::install();
}

function plugin_assetmenumanager_uninstall(): bool
{
    return Config::uninstall();
}
