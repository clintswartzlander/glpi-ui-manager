<?php

declare(strict_types=1);

use GlpiPlugin\Uimanager\Config;

define('PLUGIN_UIMANAGER_VERSION', '1.2.0');
define('PLUGIN_UIMANAGER_MIN_GLPI', '11.0.0');
define('PLUGIN_UIMANAGER_MAX_GLPI', '11.0.99');

require_once __DIR__ . '/inc/autoload.php';
require_once __DIR__ . '/hook.php';

function plugin_init_uimanager(): void
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['uimanager'] = true;
    $PLUGIN_HOOKS['config_page']['uimanager'] = 'front/config.php';
    $PLUGIN_HOOKS['redefine_menus']['uimanager'] = 'plugin_uimanager_redefine_menus';
    $PLUGIN_HOOKS['add_css']['uimanager'][] = 'css/branding.css';
    $PLUGIN_HOOKS['add_javascript']['uimanager'][] = 'js/branding.js';
}

function plugin_version_uimanager(): array
{
    return [
        'name'         => 'GLPI UI Manager',
        'version'      => PLUGIN_UIMANAGER_VERSION,
        'author'       => 'Clint Swartzlander and contributors',
        'license'      => 'GPL-3.0-or-later',
        'homepage'     => 'https://github.com/clintswartzlander/glpi-ui-manager',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_UIMANAGER_MIN_GLPI,
                'max' => PLUGIN_UIMANAGER_MAX_GLPI,
            ],
            'php' => ['min' => '8.2.0'],
        ],
    ];
}

function plugin_uimanager_check_prerequisites(): bool
{
    return version_compare(PHP_VERSION, '8.2.0', '>=');
}

function plugin_uimanager_check_config(bool $verbose = false): bool
{
    return true;
}

function plugin_uimanager_install(): bool
{
    return Config::install() && \GlpiPlugin\Uimanager\Branding\BrandingManager::install();
}

function plugin_uimanager_upgrade(string $oldVersion): bool
{
    // The key/value schema already supports arbitrary registry keys. Re-running
    // install is an idempotent schema check and never writes visibility rows.
    return Config::install() && \GlpiPlugin\Uimanager\Branding\BrandingManager::install();
}

function plugin_uimanager_uninstall(): bool
{
    return \GlpiPlugin\Uimanager\Branding\BrandingManager::uninstall() && Config::uninstall();
}
