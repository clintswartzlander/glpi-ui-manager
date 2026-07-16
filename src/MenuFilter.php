<?php

declare(strict_types=1);

namespace GlpiPlugin\Uimanager;

final class MenuFilter
{
    /**
     * @param array<string, mixed> $menus
     * @param array<string, bool> $visibility
     * @return array<string, mixed>
     */
    public function filter(array $menus, array $visibility): array
    {
        foreach (SupportedMenuRegistry::getSections() as $section) {
            if (!isset($menus[$section->menuKey]) || !is_array($menus[$section->menuKey])) {
                continue;
            }

            if (($visibility[$section->configurationKey] ?? true) === false) {
                unset($menus[$section->menuKey]);
                continue;
            }

            $sector = $menus[$section->menuKey];
            $content = isset($sector['content']) && is_array($sector['content'])
                ? $sector['content']
                : [];

            foreach (SupportedMenuRegistry::getItemsForSection($section->key) as $item) {
                if (($visibility[$item->configurationKey] ?? true) === true) {
                    continue;
                }

                if ($item->isDashboard()) {
                    unset($sector['default_dashboard']);
                    continue;
                }

                foreach (SupportedMenuRegistry::resolveMenuKeysForItem($item) as $menuKey) {
                    unset($content[$menuKey]);
                }
            }

            $sector['content'] = $content;
            $dashboard = isset($sector['default_dashboard'])
                && is_string($sector['default_dashboard'])
                && $sector['default_dashboard'] !== ''
                    ? $sector['default_dashboard']
                    : null;

            if ($content === [] && $dashboard === null) {
                unset($menus[$section->menuKey]);
                continue;
            }

            $currentDefault = isset($sector['default']) && is_string($sector['default'])
                ? $sector['default']
                : null;
            if (!$this->isVisibleDefault($currentDefault, $content, $dashboard)) {
                $default = $dashboard ?? $this->findDefaultPage($content);
                if ($default === null) {
                    unset($sector['default']);
                } else {
                    $sector['default'] = $default;
                }
            }

            $menus[$section->menuKey] = $sector;
        }

        return $menus;
    }

    /** @param array<string, mixed> $content */
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
