<?php

declare(strict_types=1);

namespace GlpiPlugin\Uimanager\Branding;

use Throwable;

final class BrandingHooks
{
    public static function currentEntityId(): int
    {
        return isset($_SESSION['glpiactive_entity']) ? max(0, (int) $_SESSION['glpiactive_entity']) : 0;
    }

    public static function renderHead(): void
    {
        global $CFG_GLPI;
        try {
            $branding = (new BrandingManager())->resolve(self::currentEntityId());
            $root = rtrim((string) ($CFG_GLPI['root_doc'] ?? ''), '/');
            $assetUrl = static fn (string $name): string => $root . '/plugins/uimanager/front/branding.asset.php?file=' . rawurlencode($name);
            $css = (new BrandingCssGenerator())->generate($branding, $assetUrl);
            echo '<style id="uimanager-branding">' . $css . '</style>';
            echo '<script>document.documentElement.setAttribute("data-uimanager-branding","");</script>';
            if (($branding['favicon'] ?? '') !== '') {
                echo '<link rel="icon" href="' . htmlspecialchars($assetUrl($branding['favicon']), ENT_QUOTES, 'UTF-8') . '">';
            }
            self::renderImageReplacementScript($branding, $assetUrl);
        } catch (Throwable $exception) {
            self::log($exception->getMessage());
        }
    }

    public static function renderLoginMarker(): void
    {
        echo '<span class="uimanager-branding-login-hook" hidden></span>';
    }

    /** @param array<string, string> $branding */
    private static function renderImageReplacementScript(array $branding, callable $assetUrl): void
    {
        $settings = [];
        foreach (['expanded_logo', 'collapsed_logo', 'login_logo'] as $key) {
            if (($branding[$key] ?? '') !== '') {
                $settings[$key] = $assetUrl($branding[$key]);
            }
        }
        if (($branding['application_name'] ?? '') !== '') {
            $settings['application_name'] = $branding['application_name'];
        }
        if ($settings === []) {
            return;
        }
        $json = json_encode($settings, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        echo '<script>(function(c){function a(){var q=function(s){return document.querySelector(s)};'
            . 'if(c.expanded_logo){var e=q(".navbar-brand-image:not(.navbar-brand-image-collapsed)");if(e)e.src=c.expanded_logo}'
            . 'if(c.collapsed_logo){var e=q(".navbar-brand-image-collapsed");if(e)e.src=c.collapsed_logo}'
            . 'if(c.login_logo){var e=q("body.page-anonymous .navbar-brand-image,body.page-anonymous .logo img");if(e)e.src=c.login_logo}'
            . 'if(c.application_name){var e=q("body.page-anonymous h1,body.page-anonymous .card-title");if(e)e.textContent=c.application_name;document.title=c.application_name}'
            . '}document.readyState==="loading"?document.addEventListener("DOMContentLoaded",a):a()}(' . $json . '));</script>';
    }

    private static function log(string $message): void
    {
        if (class_exists('Toolbox')) {
            \Toolbox::logDebug('[uimanager branding] ' . $message);
        }
    }
}
