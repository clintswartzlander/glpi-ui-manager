<?php

declare(strict_types=1);

namespace GlpiPlugin\Assetmenumanager;

final class ConfigPageRenderer
{
    /** @param array<string, bool> $visibility */
    public static function render(array $visibility): void
    {
        global $CFG_GLPI;

        $action = $CFG_GLPI['root_doc'] . '/plugins/assetmenumanager/front/config.form.php';
        $csrf = \Session::getNewCSRFToken();

        echo '<div class="container-xl py-3">';
        echo '<div class="card">';
        echo '<div class="card-header"><h2 class="card-title">'
            . self::escape(__('GLPI Asset Menu Manager', 'assetmenumanager'))
            . '</h2></div>';
        echo '<div class="card-body">';
        echo '<p>' . self::escape(__('Choose which supported native asset entries appear in the Assets navigation menu.', 'assetmenumanager')) . '</p>';
        echo '<div class="alert alert-warning" role="alert"><strong>'
            . self::escape(__('Visibility is not authorization.', 'assetmenumanager'))
            . '</strong> '
            . self::escape(__('Hiding an entry does not revoke permissions or prevent access by direct URL, API, search result, relationship, or another GLPI page. GLPI profile rights remain the authorization boundary.', 'assetmenumanager'))
            . '</div>';
        echo '<form method="post" action="' . self::escape($action) . '">';
        echo '<input type="hidden" name="_glpi_csrf_token" value="' . self::escape($csrf) . '">';
        echo '<div class="table-responsive"><table class="table table-hover align-middle">';
        echo '<thead><tr><th scope="col">' . self::escape(__('Native asset entry', 'assetmenumanager'))
            . '</th><th scope="col" class="text-center">' . self::escape(__('Show', 'assetmenumanager')) . '</th></tr></thead><tbody>';

        foreach (SupportedAssetRegistry::all() as $key => $item) {
            $id = 'assetmenumanager-' . $key;
            $checked = ($visibility[$key] ?? true) ? ' checked' : '';
            echo '<tr><td><label class="form-label mb-0" for="' . self::escape($id) . '">'
                . self::escape(__($item['label'], 'assetmenumanager'))
                . '</label><div class="text-secondary small"><code>' . self::escape($key) . '</code></div></td>';
            echo '<td class="text-center"><input class="form-check-input" type="checkbox" id="'
                . self::escape($id) . '" name="visible_items[' . self::escape($key) . ']" value="1"'
                . $checked . '></td></tr>';
        }

        echo '</tbody></table></div>';
        echo '<div class="d-flex flex-wrap gap-2 mt-3">';
        echo self::button('save', __('Save', 'assetmenumanager'), 'btn btn-primary');
        echo self::button('show_all', __('Show All', 'assetmenumanager'), 'btn btn-outline-success');
        echo self::button('hide_all', __('Hide All Native Assets', 'assetmenumanager'), 'btn btn-outline-warning');
        echo self::button('reset_defaults', __('Reset to Defaults', 'assetmenumanager'), 'btn btn-outline-secondary');
        echo '</div></form></div></div></div>';
    }

    private static function button(string $action, string $label, string $class): string
    {
        return '<button type="submit" name="action" value="' . self::escape($action)
            . '" class="' . self::escape($class) . '">' . self::escape($label) . '</button>';
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
