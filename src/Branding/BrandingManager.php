<?php

declare(strict_types=1);

namespace GlpiPlugin\Uimanager\Branding;

use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class BrandingManager
{
    public const TABLE = 'glpi_plugin_uimanager_branding';
    public const MODE_DEFAULT = 'default';
    public const MODE_INHERIT = 'inherit';
    public const MODE_OVERRIDE = 'override';

    /** @var array<string, array{type: string, default: string, section: string}> */
    private const FIELDS = [
        'expanded_logo' => ['type' => 'asset', 'default' => '', 'section' => 'identity'],
        'collapsed_logo' => ['type' => 'asset', 'default' => '', 'section' => 'sidebar'],
        'login_logo' => ['type' => 'asset', 'default' => '', 'section' => 'login'],
        'favicon' => ['type' => 'asset', 'default' => '', 'section' => 'identity'],
        'login_background' => ['type' => 'asset', 'default' => '', 'section' => 'login'],
        'application_name' => ['type' => 'text', 'default' => '', 'section' => 'identity'],
        'primary_color' => ['type' => 'color', 'default' => '#206bc4', 'section' => 'theme'],
        'secondary_color' => ['type' => 'color', 'default' => '#6c7a91', 'section' => 'theme'],
        'sidebar_background' => ['type' => 'color', 'default' => '#1f2937', 'section' => 'sidebar'],
        'sidebar_foreground' => ['type' => 'color', 'default' => '#ffffff', 'section' => 'sidebar'],
        'sidebar_icon_color' => ['type' => 'color', 'default' => '#cbd5e1', 'section' => 'sidebar'],
        'link_color' => ['type' => 'color', 'default' => '#206bc4', 'section' => 'theme'],
        'button_color' => ['type' => 'color', 'default' => '#206bc4', 'section' => 'theme'],
        'danger_color' => ['type' => 'color', 'default' => '#d63939', 'section' => 'theme'],
        'warning_color' => ['type' => 'color', 'default' => '#f59f00', 'section' => 'theme'],
        'success_color' => ['type' => 'color', 'default' => '#2fb344', 'section' => 'theme'],
        'info_color' => ['type' => 'color', 'default' => '#4299e1', 'section' => 'theme'],
        'custom_css' => ['type' => 'css', 'default' => '', 'section' => 'advanced'],
    ];

    public function __construct(
        private readonly BrandingResolver $resolver = new BrandingResolver(),
        private readonly BrandingAssets $assets = new BrandingAssets()
    ) {
    }

    public static function install(): bool
    {
        global $DB;
        $query = 'CREATE TABLE IF NOT EXISTS `' . self::TABLE . '` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `entities_id` int unsigned NOT NULL DEFAULT 0,
            `item_key` varchar(64) NOT NULL,
            `mode` varchar(16) NOT NULL DEFAULT \'inherit\',
            `value` mediumtext NULL,
            `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
            `date_mod` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`), UNIQUE KEY `entity_item` (`entities_id`, `item_key`),
            KEY `entities_id` (`entities_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
        return $DB->doQuery($query) !== false;
    }

    public static function uninstall(): bool
    {
        global $DB;
        (new BrandingAssets())->removeAll();
        return $DB->doQuery('DROP TABLE IF EXISTS `' . self::TABLE . '`') !== false;
    }

    /** @return array<string, array{type: string, default: string, section: string}> */
    public static function fields(): array
    {
        return self::FIELDS;
    }

    /** @return array<string, array{mode: string, value: string, is_enabled: bool}> */
    public function getEntityConfiguration(int $entityId): array
    {
        $result = [];
        foreach (self::FIELDS as $key => $definition) {
            $result[$key] = ['mode' => $entityId === 0 ? self::MODE_DEFAULT : self::MODE_INHERIT, 'value' => '', 'is_enabled' => true];
        }
        foreach ($this->rowsForEntity($entityId) as $row) {
            $key = (string) ($row['item_key'] ?? '');
            if (isset(self::FIELDS[$key])) {
                $result[$key] = [
                    'mode' => (string) $row['mode'], 'value' => (string) ($row['value'] ?? ''),
                    'is_enabled' => (bool) ($row['is_enabled'] ?? true),
                ];
            }
        }
        return $result;
    }

    /** @return array<string, string> */
    public function resolve(int $entityId): array
    {
        return $this->resolver->resolve($entityId, self::FIELDS, fn (int $id): array => $this->rowsForEntity($id));
    }

    /** @return array{values: array<string, string>, sources: array<string, int|null>} */
    public function resolveWithSources(int $entityId): array
    {
        return $this->resolver->resolveWithSources(
            $entityId,
            self::FIELDS,
            fn (int $id): array => $this->rowsForEntity($id)
        );
    }

    /** @param array<string, mixed> $submitted @param array<string, mixed> $files */
    public function save(int $entityId, array $submitted, array $files): void
    {
        global $DB;
        $current = $this->getEntityConfiguration($entityId);
        $prepared = [];
        foreach (self::FIELDS as $key => $definition) {
            $mode = (string) ($submitted[$key]['mode'] ?? ($entityId === 0 ? self::MODE_DEFAULT : self::MODE_INHERIT));
            if (!in_array($mode, [self::MODE_DEFAULT, self::MODE_INHERIT, self::MODE_OVERRIDE], true)) {
                throw new InvalidArgumentException('Invalid branding inheritance mode.');
            }
            if ($entityId === 0 && $mode === self::MODE_INHERIT) {
                $mode = self::MODE_DEFAULT;
            }
            $value = (string) ($submitted[$key]['value'] ?? $current[$key]['value']);
            if ($definition['type'] === 'asset') {
                $value = $current[$key]['value'];
                if (!empty($submitted[$key]['delete'])) {
                    $this->assets->remove($value);
                    $value = '';
                }
                if (isset($files[$key]) && is_array($files[$key]) && (int) ($files[$key]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                    $newValue = $this->assets->store($files[$key], $entityId, $key);
                    $this->assets->remove($value);
                    $value = $newValue;
                }
            } else {
                $value = $this->validateValue($definition['type'], $value);
            }
            $prepared[$key] = ['mode' => $mode, 'value' => $value, 'is_enabled' => !isset($submitted[$key]['disabled'])];
        }

        $DB->beginTransaction();
        try {
            foreach ($prepared as $key => $row) {
                $DB->updateOrInsert(self::TABLE, [
                    'mode' => $row['mode'], 'value' => $row['value'], 'is_enabled' => $row['is_enabled'] ? 1 : 0,
                    'date_mod' => date('Y-m-d H:i:s'),
                ], ['entities_id' => $entityId, 'item_key' => $key]);
            }
            $DB->commit();
        } catch (Throwable $exception) {
            $DB->rollBack();
            throw new RuntimeException('Could not save branding settings.', 0, $exception);
        }
    }

    /** @return list<array<string, mixed>> */
    private function rowsForEntity(int $entityId): array
    {
        global $DB;
        if (!$DB->tableExists(self::TABLE)) {
            return [];
        }
        return self::normalizeRows($DB->request([
            'FROM' => self::TABLE,
            'WHERE' => ['entities_id' => $entityId],
        ]));
    }

    /**
     * Normalize GLPI database iterators at the data-access boundary.
     *
     * @param iterable<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private static function normalizeRows(iterable $rows): array
    {
        $normalized = [];
        foreach ($rows as $row) {
            $normalized[] = $row;
        }
        return $normalized;
    }

    private function validateValue(string $type, string $value): string
    {
        $value = trim($value);
        if ($type === 'color' && preg_match('/^#[0-9a-fA-F]{6}$/', $value) !== 1) {
            throw new InvalidArgumentException('Colors must use six-digit hexadecimal notation.');
        }
        if ($type === 'text' && mb_strlen($value) > 120) {
            throw new InvalidArgumentException('Application name must be 120 characters or fewer.');
        }
        if ($type === 'css' && strlen($value) > 50000) {
            throw new InvalidArgumentException('Custom CSS must be 50 KB or smaller.');
        }
        return $value;
    }
}
