<?php

declare(strict_types=1);

namespace GlpiPlugin\Uimanager\Tests;

use GlpiPlugin\Uimanager\Config;
use GlpiPlugin\Uimanager\SupportedMenuRegistry;
use PHPUnit\Framework\TestCase;

final class RegistryAndUpgradeTest extends TestCase
{
    public function testAllSixSectionsExist(): void
    {
        self::assertSame(
            ['assets', 'assistance', 'management', 'tools', 'administration', 'setup'],
            array_keys(SupportedMenuRegistry::getSections())
        );
    }

    public function testConfigurationKeysAreUniqueAndDefaultVisible(): void
    {
        $keys = SupportedMenuRegistry::getAllKnownKeys();
        self::assertSame($keys, array_values(array_unique($keys)));
        self::assertSame([], array_filter(
            SupportedMenuRegistry::getDefaultVisibilityMap(),
            static fn (bool $visible): bool => $visible === false
        ));
    }

    public function testAliasesDoNotCollideAcrossItems(): void
    {
        foreach (SupportedMenuRegistry::getSections() as $section) {
            $seen = [];
            foreach (SupportedMenuRegistry::getItemsForSection($section->key) as $item) {
                foreach ($item->menuKeys() as $key) {
                    self::assertArrayNotHasKey($key, $seen, "Alias collision in {$section->key}: {$key}");
                    $seen[$key] = true;
                }
            }
        }
    }

    public function testOnePointZeroAssetOverridesSurviveAndNewKeysDefaultVisible(): void
    {
        $result = Config::mergeStoredVisibility([
            ['item_key' => 'computer', 'is_visible' => 0],
            ['item_key' => 'phone', 'is_visible' => 1],
        ]);
        self::assertFalse($result['computer']);
        self::assertTrue($result['phone']);
        self::assertTrue($result['section_assistance']);
        self::assertTrue($result['management_licenses']);
    }

    public function testUpgradeMergeIsIdempotent(): void
    {
        $rows = [['item_key' => 'computer', 'is_visible' => 0]];
        self::assertSame(Config::mergeStoredVisibility($rows), Config::mergeStoredVisibility($rows));
    }
}
