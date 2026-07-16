<?php

declare(strict_types=1);

namespace GlpiPlugin\Uimanager;

final class MenuDiagnostic
{
    /** @param array<string, mixed> $menus @return array<string, mixed> */
    public static function sanitize(array $menus): array
    {
        $result = ['format' => 1, 'sectors' => []];
        foreach ($menus as $sectorKey => $sector) {
            if (!is_string($sectorKey) || !is_array($sector)) {
                continue;
            }
            $result['sectors'][$sectorKey] = self::sanitizeEntry($sector);
        }
        return $result;
    }

    /** @param array<string, mixed> $entry @return array<string, mixed> */
    private static function sanitizeEntry(array $entry): array
    {
        $safe = [];
        if (isset($entry['title']) && is_string($entry['title'])) {
            $safe['label'] = trim(strip_tags($entry['title']));
        }
        if (isset($entry['types']) && is_array($entry['types'])) {
            $safe['types'] = array_values(array_filter(
                $entry['types'],
                static fn (mixed $value): bool => is_string($value) && self::isTechnicalIdentifier($value)
            ));
        }
        foreach (['itemtype', 'class'] as $field) {
            if (isset($entry[$field]) && is_string($entry[$field]) && self::isTechnicalIdentifier($entry[$field])) {
                $safe[$field] = $entry[$field];
            }
        }
        foreach (['content', 'options'] as $group) {
            if (!isset($entry[$group]) || !is_array($entry[$group])) {
                continue;
            }
            $safe[$group] = [];
            foreach ($entry[$group] as $key => $child) {
                if (is_string($key) && is_array($child)) {
                    $safe[$group][$key] = self::sanitizeEntry($child);
                }
            }
        }
        return $safe;
    }

    private static function isTechnicalIdentifier(string $value): bool
    {
        return preg_match('/^[A-Za-z0-9_\\\\.:-]+$/', $value) === 1;
    }
}
