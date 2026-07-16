<?php

declare(strict_types=1);

namespace GlpiPlugin\Assetmenumanager\Tests;

use GlpiPlugin\Assetmenumanager\MenuFilter;
use GlpiPlugin\Assetmenumanager\SupportedAssetRegistry;
use PHPUnit\Framework\TestCase;

final class MenuFilterTest extends TestCase
{
    private MenuFilter $filter;

    protected function setUp(): void
    {
        $this->filter = new MenuFilter();
    }

    public function testAllEntriesAreVisibleByDefault(): void
    {
        $menus = $this->menus();

        self::assertSame($menus, $this->filter->filter($menus, SupportedAssetRegistry::defaults()));
    }

    public function testOneConfiguredEntryIsRemoved(): void
    {
        $visibility = SupportedAssetRegistry::defaults();
        $visibility['phone'] = false;

        $result = $this->filter->filter($this->menus(), $visibility);

        self::assertArrayNotHasKey('phone', $result['assets']['content']);
        self::assertArrayHasKey('computer', $result['assets']['content']);
    }

    public function testMultipleConfiguredEntriesAreRemoved(): void
    {
        $visibility = SupportedAssetRegistry::defaults();
        foreach (['cartridgeitem', 'consumableitem', 'item_devicesimcard', 'pdu', 'passivedcequipment'] as $key) {
            $visibility[$key] = false;
        }

        $result = $this->filter->filter($this->menus(), $visibility);

        foreach (['cartridgeitem', 'consumableitem', 'item_devicesimcard', 'pdu', 'passivedcequipment'] as $key) {
            self::assertArrayNotHasKey($key, $result['assets']['content']);
        }
        self::assertArrayHasKey('phone', $result['assets']['content']);
    }

    public function testUnrelatedMenuSectionsRemainUnchanged(): void
    {
        $menus = $this->menus();
        $visibility = SupportedAssetRegistry::defaults();
        $visibility['phone'] = false;

        $result = $this->filter->filter($menus, $visibility);

        self::assertSame($menus['helpdesk'], $result['helpdesk']);
        self::assertSame($menus['config'], $result['config']);
    }

    public function testCustomAssetMenuEntriesRemain(): void
    {
        $visibility = array_fill_keys(SupportedAssetRegistry::keys(), false);

        $result = $this->filter->filter($this->menus(), $visibility);

        self::assertArrayHasKey('glpiassetprojector', $result['assets']['content']);
        self::assertArrayHasKey('assets', $result);
    }

    public function testMissingMenuKeysDoNotFail(): void
    {
        $menus = $this->menus();
        unset($menus['assets']['content']['rack'], $menus['assets']['content']['item_devicesimcard']);
        $visibility = SupportedAssetRegistry::defaults();
        $visibility['rack'] = false;
        $visibility['item_devicesimcard'] = false;

        self::assertIsArray($this->filter->filter($menus, $visibility));
    }

    public function testAssetsRemainsWhenAnyNativeChildRemains(): void
    {
        $visibility = array_fill_keys(SupportedAssetRegistry::keys(), false);
        $visibility['computer'] = true;
        $menus = $this->menus();
        unset($menus['assets']['content']['glpiassetprojector']);

        $result = $this->filter->filter($menus, $visibility);

        self::assertArrayHasKey('assets', $result);
        self::assertSame(['computer'], array_keys($result['assets']['content']));
    }

    public function testAssetsIsRemovedOnlyWhenNoVisibleChildOrDashboardRemains(): void
    {
        $visibility = array_fill_keys(SupportedAssetRegistry::keys(), false);
        $menus = $this->menus();
        unset($menus['assets']['content']['glpiassetprojector']);

        $result = $this->filter->filter($menus, $visibility);

        self::assertArrayNotHasKey('assets', $result);
        self::assertArrayHasKey('helpdesk', $result);
    }

    public function testDashboardKeepsAssetsMenuWhenItIsTheOnlyVisibleEntry(): void
    {
        $visibility = array_fill_keys(SupportedAssetRegistry::keys(), false);
        $visibility[SupportedAssetRegistry::DASHBOARD] = true;
        $menus = $this->menus();
        unset($menus['assets']['content']['glpiassetprojector']);

        $result = $this->filter->filter($menus, $visibility);

        self::assertArrayHasKey('assets', $result);
        self::assertSame('/front/dashboard_assets.php', $result['assets']['default']);
    }

    public function testFilteringDoesNotMutateInputAndDefaultBehaviorCanBeRestored(): void
    {
        $menus = $this->menus();
        $visibility = SupportedAssetRegistry::defaults();
        $visibility['computer'] = false;

        $this->filter->filter($menus, $visibility);

        self::assertArrayHasKey('computer', $menus['assets']['content']);
        self::assertSame($menus, $this->filter->filter($menus, SupportedAssetRegistry::defaults()));
    }

    /** @return array<string, mixed> */
    private function menus(): array
    {
        return [
            'assets' => [
                'title' => 'Assets',
                'default' => '/front/computer.php',
                'default_dashboard' => '/front/dashboard_assets.php',
                'content' => [
                    'computer' => ['page' => '/front/computer.php'],
                    'phone' => ['page' => '/front/phone.php'],
                    'cartridgeitem' => ['page' => '/front/cartridgeitem.php'],
                    'consumableitem' => ['page' => '/front/consumableitem.php'],
                    'item_devicesimcard' => ['page' => '/front/item_devicesimcard.php'],
                    'pdu' => ['page' => '/front/pdu.php'],
                    'passivedcequipment' => ['page' => '/front/passivedcequipment.php'],
                    'rack' => ['page' => '/front/rack.php'],
                    'allassets' => ['page' => '/front/allassets.php'],
                    'glpiassetprojector' => ['page' => '/front/asset/projector.php'],
                ],
            ],
            'helpdesk' => ['content' => ['ticket' => ['page' => '/front/ticket.php']]],
            'config' => ['content' => ['plugin' => ['page' => '/front/plugin.php']]],
        ];
    }
}
