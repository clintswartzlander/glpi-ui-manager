<?php

declare(strict_types=1);

namespace GlpiPlugin\Uimanager;

use InvalidArgumentException;

final class ConfigurationController
{
    public static function authorize(): void
    {
        \Session::checkRight('config', UPDATE);
    }

    /** @param array<string, mixed> $post */
    public static function process(array $post): void
    {
        self::authorize();

        $action = $post['action'] ?? null;
        if (!is_string($action)) {
            throw new InvalidArgumentException('A valid configuration action is required.');
        }

        switch ($action) {
            case 'save':
                Config::save(InputValidator::visibilityFromSubmittedKeys($post['visible_items'] ?? []));
                break;

            case 'reset_defaults':
                Config::reset();
                break;

            case 'show_all':
            case 'hide_all':
                Config::save(self::visibilityForPreset($action));
                break;

            default:
                throw new InvalidArgumentException('The requested configuration action is not supported.');
        }
    }

    /** @return array<string, bool> */
    public static function visibilityForPreset(string $action): array
    {
        return match ($action) {
            'show_all' => SupportedMenuRegistry::defaults(),
            'hide_all' => array_fill_keys(SupportedMenuRegistry::keys(), false),
            default => throw new InvalidArgumentException('The requested visibility preset is not supported.'),
        };
    }
}
