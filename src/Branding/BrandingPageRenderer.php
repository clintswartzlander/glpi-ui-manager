<?php

declare(strict_types=1);

namespace GlpiPlugin\Uimanager\Branding;

final class BrandingPageRenderer
{
    /** @var array<string, array{label: string, description: string}> */
    private const SECTIONS = [
        'identity' => ['label' => 'Brand Identity', 'description' => 'Application identity used across GLPI.'],
        'theme' => ['label' => 'Theme Colors', 'description' => 'Accessible color tokens applied through CSS variables.'],
        'login' => ['label' => 'Login Page', 'description' => 'Anonymous page logo and background.'],
        'sidebar' => ['label' => 'Sidebar', 'description' => 'Expanded and collapsed navigation presentation.'],
        'advanced' => ['label' => 'Advanced CSS', 'description' => 'Optional CSS automatically scoped to UI Manager branding.'],
    ];

    private const LABELS = [
        'expanded_logo' => 'Expanded sidebar logo', 'collapsed_logo' => 'Collapsed sidebar logo',
        'login_logo' => 'Login logo', 'favicon' => 'Favicon', 'login_background' => 'Login background image',
        'application_name' => 'Application name', 'primary_color' => 'Primary color',
        'secondary_color' => 'Secondary color', 'sidebar_background' => 'Sidebar background',
        'sidebar_foreground' => 'Sidebar foreground', 'sidebar_icon_color' => 'Sidebar icon color',
        'link_color' => 'Link color', 'button_color' => 'Button color', 'danger_color' => 'Danger color',
        'warning_color' => 'Warning color', 'success_color' => 'Success color', 'info_color' => 'Info color',
        'custom_css' => 'Custom CSS',
    ];

    public static function render(int $entityId, BrandingManager $manager): void
    {
        global $CFG_GLPI;
        $configuration = $manager->getEntityConfiguration($entityId);
        $resolved = $manager->resolve($entityId);
        $action = $CFG_GLPI['root_doc'] . '/plugins/uimanager/front/branding.form.php';
        echo '<div class="container-xl py-3"><div class="d-flex justify-content-between align-items-center mb-3"><div><h1>' . self::e(__('Branding', 'uimanager')) . '</h1>';
        echo '<p class="text-secondary mb-0">' . self::e(__('Upgrade-safe visual customization with entity inheritance.', 'uimanager')) . '</p></div>';
        echo '<a class="btn btn-outline-secondary" href="' . self::e($CFG_GLPI['root_doc'] . '/plugins/uimanager/front/config.php') . '">' . self::e(__('Navigation Manager', 'uimanager')) . '</a></div>';
        self::entitySelector($entityId);
        echo '<form method="post" enctype="multipart/form-data" action="' . self::e($action) . '">';
        echo '<input type="hidden" name="_glpi_csrf_token" value="' . self::e(\Session::getNewCSRFToken()) . '"><input type="hidden" name="entities_id" value="' . $entityId . '">';
        foreach (self::SECTIONS as $section => $meta) {
            echo '<section class="card mb-3"><div class="card-header"><div><h2 class="card-title mb-1">' . self::e(__($meta['label'], 'uimanager')) . '</h2><div class="text-secondary small">' . self::e(__($meta['description'], 'uimanager')) . '</div></div></div><div class="card-body"><div class="row g-3">';
            foreach (BrandingManager::fields() as $key => $definition) {
                if ($definition['section'] !== $section) continue;
                self::field($key, $definition['type'], $configuration[$key], $resolved[$key], $entityId);
            }
            echo '</div><div class="mt-3"><button class="btn btn-primary" type="submit">' . self::e(__('Save', 'uimanager')) . '</button></div></div></section>';
        }
        echo '</form></div>';
    }

    /** @param array{mode: string, value: string, is_enabled: bool} $setting */
    private static function field(string $key, string $type, array $setting, string $resolved, int $entityId): void
    {
        $wide = $type === 'css' ? 'col-12' : 'col-12 col-lg-6';
        echo '<div class="' . $wide . '"><div class="border rounded p-3 h-100"><label class="form-label fw-bold">' . self::e(__(self::LABELS[$key], 'uimanager')) . '</label>';
        echo '<select class="form-select form-select-sm mb-2" name="branding[' . self::e($key) . '][mode]">';
        $modes = $entityId === 0 ? ['default' => 'Default', 'override' => 'Override'] : ['default' => 'Default', 'inherit' => 'Inherit Parent', 'override' => 'Override'];
        foreach ($modes as $value => $label) {
            echo '<option value="' . $value . '"' . ($setting['mode'] === $value ? ' selected' : '') . '>' . self::e(__($label, 'uimanager')) . '</option>';
        }
        echo '</select>';
        if ($type === 'asset') {
            echo '<input class="form-control" type="file" accept=".png,.svg,.ico,.webp,.jpg,.jpeg" name="branding[' . self::e($key) . '][file]">';
            if ($resolved !== '') {
                global $CFG_GLPI;
                $url = $CFG_GLPI['root_doc'] . '/plugins/uimanager/front/branding.asset.php?file=' . rawurlencode($resolved);
                echo '<div class="mt-2 p-2 bg-light rounded"><img src="' . self::e($url) . '" alt="" style="max-height:80px;max-width:100%"></div>';
            }
            if ($setting['value'] !== '') echo '<label class="form-check mt-2"><input class="form-check-input" type="checkbox" name="branding[' . self::e($key) . '][delete]" value="1"><span class="form-check-label">' . self::e(__('Delete / restore default', 'uimanager')) . '</span></label>';
            echo '<div class="form-hint">PNG, SVG, ICO, WEBP, or JPG; maximum 5 MB.</div>';
        } elseif ($type === 'color') {
            echo '<div class="input-group"><input class="form-control form-control-color" type="color" value="' . self::e($setting['value'] ?: $resolved) . '" oninput="this.nextElementSibling.value=this.value"><input class="form-control" pattern="#[0-9A-Fa-f]{6}" name="branding[' . self::e($key) . '][value]" value="' . self::e($setting['value'] ?: $resolved) . '"></div>';
        } elseif ($type === 'css') {
            echo '<textarea class="form-control font-monospace" rows="10" name="branding[' . self::e($key) . '][value]" placeholder=".my-selector { color: var(--ui-primary); }">' . self::e($setting['value']) . '</textarea><div class="form-hint">Selectors are prefixed with the UI Manager scope; @import rules are removed.</div>';
        } else {
            echo '<input class="form-control" maxlength="120" name="branding[' . self::e($key) . '][value]" value="' . self::e($setting['value']) . '" placeholder="' . self::e($resolved) . '">';
        }
        echo '<div class="small text-secondary mt-2">' . self::e(__('Effective value:', 'uimanager')) . ' ' . self::e($type === 'asset' ? ($resolved === '' ? __('GLPI default', 'uimanager') : basename($resolved)) : ($resolved ?: __('GLPI default', 'uimanager'))) . '</div></div></div>';
    }

    private static function entitySelector(int $entityId): void
    {
        echo '<form method="get" class="card card-body mb-3"><label class="form-label">' . self::e(__('Configuration scope', 'uimanager')) . '</label><div class="d-flex gap-2"><select class="form-select" name="entities_id"><option value="0">' . self::e(__('Global', 'uimanager')) . '</option>';
        if (class_exists('Entity')) {
            foreach ((new \Entity())->find([], ['completename ASC']) as $row) {
                $id = (int) $row['id'];
                echo '<option value="' . $id . '"' . ($id === $entityId ? ' selected' : '') . '>' . self::e((string) ($row['completename'] ?? $row['name'])) . '</option>';
            }
        }
        echo '</select><button class="btn btn-outline-primary">' . self::e(__('Select', 'uimanager')) . '</button></div></form>';
    }

    private static function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
}
