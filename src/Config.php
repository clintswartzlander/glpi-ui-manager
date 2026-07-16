<?php

declare(strict_types=1);

namespace GlpiPlugin\Assetmenumanager;

use RuntimeException;
use Throwable;

final class Config
{
    public const TABLE = 'glpi_plugin_assetmenumanager_configs';

    public static function install(): bool
    {
        global $DB;

        $query = 'CREATE TABLE IF NOT EXISTS `' . self::TABLE . '` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `item_key` varchar(64) NOT NULL,
            `is_visible` tinyint(1) NOT NULL DEFAULT 1,
            `date_mod` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `item_key` (`item_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        return $DB->doQuery($query) !== false;
    }

    public static function uninstall(): bool
    {
        global $DB;

        return $DB->doQuery('DROP TABLE IF EXISTS `' . self::TABLE . '`') !== false;
    }

    /** @return array<string, bool> */
    public static function getVisibility(): array
    {
        global $DB;

        $visibility = SupportedAssetRegistry::defaults();

        try {
            if (!$DB->tableExists(self::TABLE)) {
                return $visibility;
            }

            foreach ($DB->request(['FROM' => self::TABLE]) as $row) {
                $key = (string) ($row['item_key'] ?? '');
                if (SupportedAssetRegistry::isSupported($key)) {
                    $visibility[$key] = (bool) ($row['is_visible'] ?? true);
                }
            }
        } catch (Throwable $exception) {
            self::logDebug('Could not read configuration; defaults are being used: ' . $exception->getMessage());
        }

        return $visibility;
    }

    /** @param array<string, bool> $visibility */
    public static function save(array $visibility): void
    {
        global $DB;

        self::assertCompleteVisibilityMap($visibility);

        $DB->beginTransaction();
        try {
            foreach ($visibility as $key => $isVisible) {
                $DB->updateOrInsert(
                    self::TABLE,
                    [
                        'is_visible' => $isVisible ? 1 : 0,
                        'date_mod'   => date('Y-m-d H:i:s'),
                    ],
                    ['item_key' => $key]
                );
            }

            $DB->commit();
        } catch (Throwable $exception) {
            $DB->rollBack();
            throw new RuntimeException(
                'Could not save the Asset Menu Manager configuration.',
                0,
                $exception
            );
        }

        self::clearMenuCache();
    }

    public static function reset(): void
    {
        global $DB;

        if ($DB->tableExists(self::TABLE)) {
            $DB->doQuery('DELETE FROM `' . self::TABLE . '`');
        }

        self::clearMenuCache();
    }

    /** @param array<string, bool> $visibility */
    private static function assertCompleteVisibilityMap(array $visibility): void
    {
        $expected = SupportedAssetRegistry::keys();
        $actual = array_keys($visibility);
        sort($expected);
        sort($actual);

        if ($expected !== $actual) {
            throw new RuntimeException('The visibility map contains missing or unsupported keys.');
        }

        foreach ($visibility as $value) {
            if (!is_bool($value)) {
                throw new RuntimeException('Visibility values must be boolean.');
            }
        }
    }

    private static function clearMenuCache(): void
    {
        unset($_SESSION['glpimenu']);
    }

    private static function logDebug(string $message): void
    {
        if (class_exists('Toolbox')) {
            \Toolbox::logDebug('[assetmenumanager] ' . $message);
        }
    }
}
