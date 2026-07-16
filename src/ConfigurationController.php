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
        if (isset($post['section_action'])) {
            [$section, $action] = self::submittedSectionAction($post['section_action']);
            Config::save(self::visibilityForSectionAction(Config::getVisibility(), $section, $action));
            return;
        }
        $action = $post['action'] ?? null;
        if (!is_string($action)) {
            throw new InvalidArgumentException('A valid configuration action is required.');
        }

        switch ($action) {
            case 'save':
                Config::save(InputValidator::visibilityFromSubmittedKeys($post['visible_items'] ?? []));
                return;
            case 'reset_all':
                Config::reset();
                return;
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

    /**
     * @param array<string, bool> $visibility
     * @return array<string, bool>
     */
    public static function visibilityForSectionAction(array $visibility, string $sectionKey, string $action): array
    {
        $section = SupportedMenuRegistry::getSection($sectionKey);
        $defaults = SupportedMenuRegistry::defaults();

        if ($action === 'section_show_all') {
            $visibility[$section->configurationKey] = true;
        } elseif ($action === 'section_reset') {
            $visibility[$section->configurationKey] = $defaults[$section->configurationKey];
        } elseif ($action !== 'section_hide_all') {
            throw new InvalidArgumentException('The requested section action is not supported.');
        }

        foreach (SupportedMenuRegistry::getItemsForSection($sectionKey) as $item) {
            $visibility[$item->configurationKey] = match ($action) {
                'section_show_all' => true,
                'section_hide_all' => false,
                'section_reset' => $defaults[$item->configurationKey],
            };
        }
        return $visibility;
    }

    /** @return array{string, string} */
    private static function submittedSectionAction(mixed $submitted): array
    {
        if (!is_array($submitted) || count($submitted) !== 1) {
            throw new InvalidArgumentException('A single valid section action is required.');
        }
        $section = array_key_first($submitted);
        $shortAction = $submitted[$section] ?? null;
        if (!is_string($section) || !is_string($shortAction)) {
            throw new InvalidArgumentException('A valid section action is required.');
        }
        $action = match ($shortAction) {
            'show_all' => 'section_show_all',
            'hide_all' => 'section_hide_all',
            'reset' => 'section_reset',
            default => throw new InvalidArgumentException('The requested section action is not supported.'),
        };
        SupportedMenuRegistry::getSection($section);
        return [$section, $action];
    }
}
