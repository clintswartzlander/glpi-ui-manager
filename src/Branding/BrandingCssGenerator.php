<?php

declare(strict_types=1);

namespace GlpiPlugin\Uimanager\Branding;

final class BrandingCssGenerator
{
    /** @param array<string, string> $branding */
    public function generate(array $branding, callable $assetUrl): string
    {
        $map = [
            'primary_color' => '--ui-primary', 'secondary_color' => '--ui-secondary',
            'sidebar_background' => '--ui-sidebar-bg', 'sidebar_foreground' => '--ui-sidebar-fg',
            'sidebar_icon_color' => '--ui-sidebar-icon', 'link_color' => '--ui-link',
            'button_color' => '--ui-button', 'danger_color' => '--ui-danger',
            'warning_color' => '--ui-warning', 'success_color' => '--ui-success', 'info_color' => '--ui-info',
        ];
        $variables = [];
        foreach ($map as $key => $variable) {
            if (preg_match('/^#[0-9a-f]{6}$/i', $branding[$key] ?? '') === 1) {
                $variables[] = $variable . ':' . $branding[$key];
            }
        }
        $css = 'html[data-uimanager-branding]{' . implode(';', $variables) . '}';
        $css .= 'html[data-uimanager-branding] a{--tblr-link-color:var(--ui-link)}';
        $css .= 'html[data-uimanager-branding] .btn-primary{--tblr-btn-bg:var(--ui-button);--tblr-btn-border-color:var(--ui-button)}';
        $css .= 'html[data-uimanager-branding] .navbar-vertical{--tblr-navbar-bg:var(--ui-sidebar-bg);color:var(--ui-sidebar-fg)}';
        $css .= 'html[data-uimanager-branding] .navbar-vertical .nav-link,html[data-uimanager-branding] .navbar-vertical .nav-link-icon{color:var(--ui-sidebar-icon)}';
        if (($branding['login_background'] ?? '') !== '') {
            $css .= 'html[data-uimanager-branding] body.page-anonymous{background-image:url("' . $this->cssString((string) $assetUrl($branding['login_background'])) . '");background-size:cover;background-position:center}';
        }
        $custom = trim($branding['custom_css'] ?? '');
        if ($custom !== '') {
            $css .= $this->scopeCustomCss($custom);
        }
        return $css;
    }

    public function scopeCustomCss(string $css): string
    {
        $css = preg_replace('~/\*.*?\*/~s', '', $css) ?? '';
        $css = preg_replace('/@import\b[^;]*;?/i', '', $css) ?? '';
        return preg_replace_callback('/(^|})(\s*)([^@}{][^{]*)\{/m', static function (array $match): string {
            $selectors = implode(',', array_map(static fn (string $selector): string => 'html[data-uimanager-branding] ' . trim($selector), explode(',', $match[3])));
            return $match[1] . $match[2] . $selectors . '{';
        }, $css) ?? '';
    }

    private function cssString(string $value): string
    {
        return str_replace(['\\', '"', "\n", "\r"], ['\\\\', '\\"', '', ''], $value);
    }
}
