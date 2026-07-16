<?php

declare(strict_types=1);

namespace GlpiPlugin\Assetmenumanager;

final class MenuFilter
{
    /**
     * @param array<string, mixed> $menus
     * @param array<string, bool> $visibility
     * @return array<string, mixed>
     */
    public function filter(array $menus, array $visibility): array
    {
        if (!isset($menus['assets']) || !is_array($menus['assets'])) {
            return $menus;
        }

        $assets = $menus['assets'];
        $content = isset($assets['content']) && is_array($assets['content'])
            ? $assets['content']
            : [];

        foreach (SupportedAssetRegistry::all() as $configKey => $item) {
            if (($visibility[$configKey] ?? true) === true) {
                continue;
            }

            if ($configKey === SupportedAssetRegistry::DASHBOARD) {
                unset($assets['default_dashboard']);
                continue;
            }

            $menuKey = $item['menu_key'];
            if ($menuKey !== null) {
                unset($content[$menuKey]);
            }
        }

        $assets['content'] = $content;
        $hasDashboard = isset($assets['default_dashboard'])
            && is_string($assets['default_dashboard'])
            && $assets['default_dashboard'] !== '';

        if ($content === [] && !$hasDashboard) {
            unset($menus['assets']);
            return $menus;
        }

        $currentDefault = isset($assets['default']) && is_string($assets['default'])
            ? $assets['default']
            : null;
        if (!$this->isVisibleDefault($currentDefault, $content, $hasDashboard ? $assets['default_dashboard'] : null)) {
            $default = $hasDashboard ? $assets['default_dashboard'] : $this->findDefaultPage($content);
            if ($default !== null) {
                $assets['default'] = $default;
            } else {
                unset($assets['default']);
            }
        }

        $menus['assets'] = $assets;
        return $menus;
    }

    /**
     * @param array<string, mixed> $content
     */
    private function findDefaultPage(array $content): ?string
    {
        foreach ($content as $entry) {
            if (is_array($entry) && isset($entry['page']) && is_string($entry['page'])) {
                return $entry['page'];
            }
        }

        return null;
    }

    /** @param array<string, mixed> $content */
    private function isVisibleDefault(?string $default, array $content, ?string $dashboard): bool
    {
        if ($default === null) {
            return false;
        }
        if ($dashboard !== null && $default === $dashboard) {
            return true;
        }

        foreach ($content as $entry) {
            if (is_array($entry) && ($entry['page'] ?? null) === $default) {
                return true;
            }
        }

        return false;
    }
}
