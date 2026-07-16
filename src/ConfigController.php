<?php

declare(strict_types=1);

namespace GlpiPlugin\Assetmenumanager;

use InvalidArgumentException;

final class ConfigController
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

            case 'show_all':
            case 'reset_defaults':
                if ($action === 'reset_defaults') {
                    Config::reset();
                } else {
                    Config::save(SupportedAssetRegistry::defaults());
                }
                break;

            case 'hide_all':
                Config::save(array_fill_keys(SupportedAssetRegistry::keys(), false));
                break;

            default:
                throw new InvalidArgumentException('The requested configuration action is not supported.');
        }
    }
}
