<?php

declare(strict_types=1);

namespace GlpiPlugin\Uimanager;

use InvalidArgumentException;

final class InputValidator
{
    /**
     * Converts submitted checkbox keys into a complete visibility map.
     *
     * @param mixed $submittedKeys
     * @return array<string, bool>
     */
    public static function visibilityFromSubmittedKeys(mixed $submittedKeys): array
    {
        if (!is_array($submittedKeys)) {
            throw new InvalidArgumentException('The visible_items field must be an array.');
        }

        $keys = array_keys($submittedKeys);
        SupportedMenuRegistry::validateSubmittedKeys($keys);

        $visibility = array_fill_keys(SupportedMenuRegistry::keys(), false);
        foreach ($keys as $key) {
            $visibility[$key] = true;
        }

        return $visibility;
    }
}
