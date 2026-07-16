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

        $visibility = array_fill_keys(SupportedMenuRegistry::keys(), false);
        foreach (array_keys($submittedKeys) as $key) {
            if (!is_string($key) || !SupportedMenuRegistry::isSupported($key)) {
                throw new InvalidArgumentException('An unsupported asset menu key was submitted.');
            }
            $visibility[$key] = true;
        }

        return $visibility;
    }
}
