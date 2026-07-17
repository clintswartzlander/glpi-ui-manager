<?php

declare(strict_types=1);

namespace GlpiPlugin\Uimanager;

final class ConfigurationPageRenderer
{
    /** @param array<string, bool> $visibility */
    public static function render(array $visibility): void
    {
        global $CFG_GLPI;

        $action = $CFG_GLPI['root_doc'] . '/plugins/uimanager/front/config.form.php';
        $diagnostic = $CFG_GLPI['root_doc'] . '/plugins/uimanager/front/menu-diagnostic.php';
        $branding = $CFG_GLPI['root_doc'] . '/plugins/uimanager/front/branding.php';

        echo '<div class="container-xl py-3">';
        echo '<div class="card mb-3"><div class="card-header"><h2 class="card-title">'
            . self::escape(__('GLPI UI Manager', 'uimanager')) . '</h2></div><div class="card-body">';
        echo '<p>' . self::escape(__('Globally show or hide supported native GLPI navigation entries. Section switches override child switches without changing the stored child choices.', 'uimanager')) . '</p>';
        echo '<div class="alert alert-warning" role="alert"><strong>'
            . self::escape(__('Visibility is not authorization.', 'uimanager')) . '</strong> '
            . self::escape(__('Hiding navigation does not revoke permissions or prevent direct URL, API, search, or relationship access. GLPI profile rights remain the authorization boundary.', 'uimanager'))
            . '</div>';
        echo '<p class="mb-0 d-flex gap-2"><a class="btn btn-primary" href="' . self::escape($branding) . '">'
            . self::escape(__('Branding', 'uimanager')) . '</a><a class="btn btn-outline-secondary" href="' . self::escape($diagnostic) . '">'
            . self::escape(__('Download Menu Diagnostic', 'uimanager')) . '</a></p>';
        echo '</div></div>';

        echo '<form method="post" action="' . self::escape($action) . '">';
        echo '<input type="hidden" name="_glpi_csrf_token" value="' . self::escape(\Session::getNewCSRFToken()) . '">';
        echo '<input type="hidden" name="action" value="save">';

        foreach (SupportedMenuRegistry::getSections() as $section) {
            $sectionVisible = $visibility[$section->configurationKey] ?? true;
            echo '<details class="card mb-3" open><summary class="card-header cursor-pointer">';
            echo '<span class="card-title mb-0">' . self::escape(__($section->label, 'uimanager')) . '</span>';
            echo '<span class="badge ' . ($sectionVisible ? 'bg-success' : 'bg-secondary') . ' ms-2">'
                . self::escape($sectionVisible ? __('Shown', 'uimanager') : __('Hidden', 'uimanager')) . '</span>';
            echo '</summary><div class="card-body">';

            $sectionId = 'uimanager-' . $section->configurationKey;
            echo '<div class="form-check form-switch mb-3">';
            echo '<input class="form-check-input" type="checkbox" role="switch" id="' . self::escape($sectionId)
                . '" name="visible_items[' . self::escape($section->configurationKey) . ']" value="1"'
                . ($sectionVisible ? ' checked' : '') . '>';
            echo '<label class="form-check-label fw-bold" for="' . self::escape($sectionId) . '">'
                . self::escape(sprintf(__('Show %s menu', 'uimanager'), __($section->label, 'uimanager'))) . '</label></div>';
            if ($section->note !== null) {
                echo '<p class="text-secondary small">' . self::escape(__($section->note, 'uimanager')) . '</p>';
            }

            echo '<div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-2">';
            foreach (SupportedMenuRegistry::getItemsForSection($section->key) as $item) {
                $id = 'uimanager-' . preg_replace('/[^a-z0-9_-]/i', '-', $item->configurationKey);
                echo '<div class="col"><div class="border rounded p-2 h-100"><div class="form-check form-switch">';
                echo '<input class="form-check-input" type="checkbox" role="switch" id="' . self::escape($id)
                    . '" name="visible_items[' . self::escape($item->configurationKey) . ']" value="1"'
                    . (($visibility[$item->configurationKey] ?? true) ? ' checked' : '') . '>';
                echo '<label class="form-check-label" for="' . self::escape($id) . '">'
                    . self::escape(sprintf(__('Show %s', 'uimanager'), __($item->label, 'uimanager'))) . '</label></div>';
                echo '<code class="small">' . self::escape($item->configurationKey) . '</code>';
                if ($item->note !== null) {
                    echo '<div class="text-secondary small">' . self::escape(__($item->note, 'uimanager')) . '</div>';
                }
                echo '</div></div>';
            }
            echo '</div><div class="d-flex flex-wrap gap-2 mt-3">';
            echo self::sectionButton($section->key, 'show_all', __('Show All', 'uimanager'), 'btn btn-sm btn-outline-success');
            echo self::sectionButton($section->key, 'hide_all', __('Hide All Supported Items', 'uimanager'), 'btn btn-sm btn-outline-warning');
            echo self::sectionButton($section->key, 'reset', __('Reset Section', 'uimanager'), 'btn btn-sm btn-outline-secondary');
            echo '</div></div></details>';
        }

        echo '<div class="d-flex flex-wrap gap-2 sticky-bottom bg-body py-3">';
        echo '<button type="submit" class="btn btn-primary">' . self::escape(__('Save Changes', 'uimanager')) . '</button>';
        echo '<button type="submit" name="action" value="reset_all" class="btn btn-outline-secondary">'
            . self::escape(__('Reset All to Defaults', 'uimanager')) . '</button>';
        echo '</div></form></div>';
    }

    private static function sectionButton(string $section, string $action, string $label, string $class): string
    {
        return '<button type="submit" name="section_action[' . self::escape($section) . ']" value="'
            . self::escape($action) . '" class="' . self::escape($class) . '">' . self::escape($label) . '</button>';
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
