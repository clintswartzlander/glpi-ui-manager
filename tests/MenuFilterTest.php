<?php

declare(strict_types=1);

namespace GlpiPlugin\Uimanager\Tests;

use GlpiPlugin\Uimanager\MenuFilter;
use GlpiPlugin\Uimanager\SupportedMenuRegistry;
use PHPUnit\Framework\Attributes\DataProvider;
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
        self::assertSame($this->menus(), $this->filter->filter($this->menus(), SupportedMenuRegistry::defaults()));
    }

    /** @return iterable<string, array{string, string, string}> */
    public static function hiddenItemProvider(): iterable
    {
        yield 'Assets' => ['phone', 'assets', 'phone'];
        yield 'Assistance' => ['assistance_problems', 'helpdesk', 'problem'];
        yield 'Management' => ['management_licenses', 'management', 'softwarelicense'];
        yield 'Tools' => ['tools_projects', 'tools', 'project'];
        yield 'Administration' => ['administration_users', 'admin', 'user'];
        yield 'Setup' => ['setup_plugins', 'config', 'plugin'];
    }

    #[DataProvider('hiddenItemProvider')]
    public function testHidingOneItemOnlyRemovesThatItem(string $configurationKey, string $sector, string $menuKey): void
    {
        $visibility = SupportedMenuRegistry::defaults();
        $visibility[$configurationKey] = false;
        $menus = $this->menus();
        $result = $this->filter->filter($menus, $visibility);

        self::assertArrayNotHasKey($menuKey, $result[$sector]['content']);
        self::assertArrayHasKey('unknown_plugin_entry', $result[$sector]['content']);
        foreach ($menus as $otherSector => $value) {
            if ($otherSector !== $sector) {
                self::assertSame($value, $result[$otherSector]);
            }
        }
    }

    public function testCustomAssetDefinitionsAndUnknownItemsRemain(): void
    {
        $visibility = SupportedMenuRegistry::defaults();
        foreach (SupportedMenuRegistry::getItemsForSection('assets') as $item) {
            $visibility[$item->configurationKey] = false;
        }
        $result = $this->filter->filter($this->menus(), $visibility);
        self::assertArrayHasKey('glpiassetprojector', $result['assets']['content']);
        self::assertArrayHasKey('unknown_plugin_entry', $result['assets']['content']);
    }

    public function testEmptySupportedTopLevelMenuIsRemoved(): void
    {
        $menus = $this->menus();
        unset($menus['management']['content']['unknown_plugin_entry']);
        $visibility = SupportedMenuRegistry::defaults();
        foreach (SupportedMenuRegistry::getItemsForSection('management') as $item) {
            $visibility[$item->configurationKey] = false;
        }
        self::assertArrayNotHasKey('management', $this->filter->filter($menus, $visibility));
    }

    public function testExplicitTopLevelDisableHidesUnknownChildrenToo(): void
    {
        $visibility = SupportedMenuRegistry::defaults();
        $visibility['section_management'] = false;
        self::assertArrayNotHasKey('management', $this->filter->filter($this->menus(), $visibility));
    }

    public function testReEnablingSectionUsesStoredChildSettings(): void
    {
        $visibility = SupportedMenuRegistry::defaults();
        $visibility['section_management'] = false;
        $visibility['management_licenses'] = false;
        self::assertArrayNotHasKey('management', $this->filter->filter($this->menus(), $visibility));

        $visibility['section_management'] = true;
        $result = $this->filter->filter($this->menus(), $visibility);
        self::assertArrayHasKey('management', $result);
        self::assertArrayNotHasKey('softwarelicense', $result['management']['content']);
        self::assertArrayHasKey('document', $result['management']['content']);
    }

    public function testMissingSectorsAndKeysDoNotThrow(): void
    {
        $menus = $this->menus();
        unset($menus['tools'], $menus['assets']['content']['rack']);
        $visibility = SupportedMenuRegistry::defaults();
        $visibility['rack'] = false;
        self::assertIsArray($this->filter->filter($menus, $visibility));
    }

    public function testHiddenDashboardRepointsDefaultToVisibleChild(): void
    {
        $visibility = SupportedMenuRegistry::defaults();
        $visibility['assistance_dashboard'] = false;
        $result = $this->filter->filter($this->menus(), $visibility);
        self::assertArrayNotHasKey('default_dashboard', $result['helpdesk']);
        self::assertSame('/front/ticket.php', $result['helpdesk']['default']);
    }

    /** @return array<string, mixed> */
    private function menus(): array
    {
        $unknown = ['page' => '/plugins/example/front/index.php'];
        return [
            'assets' => ['default' => '/front/dashboard_assets.php', 'default_dashboard' => '/front/dashboard_assets.php', 'content' => [
                'computer' => ['page' => '/front/computer.php'], 'phone' => ['page' => '/front/phone.php'],
                'rack' => ['page' => '/front/rack.php'], 'glpiassetprojector' => ['page' => '/front/asset/projector.php'],
                'unknown_plugin_entry' => $unknown,
            ]],
            'helpdesk' => ['default' => '/front/dashboard_helpdesk.php', 'default_dashboard' => '/front/dashboard_helpdesk.php', 'content' => [
                'ticket' => ['page' => '/front/ticket.php'], 'problem' => ['page' => '/front/problem.php'],
                'servicecatalog' => ['page' => '/ServiceCatalog'], 'unknown_plugin_entry' => $unknown,
            ]],
            'management' => ['default' => '/front/softwarelicense.php', 'content' => [
                'softwarelicense' => ['page' => '/front/softwarelicense.php'], 'document' => ['page' => '/front/document.php'],
                'unknown_plugin_entry' => $unknown,
            ]],
            'tools' => ['default' => '/front/project.php', 'content' => [
                'project' => ['page' => '/front/project.php'], 'knowbaseitem' => ['page' => '/front/knowbaseitem.php'],
                'unknown_plugin_entry' => $unknown,
            ]],
            'admin' => ['default' => '/front/user.php', 'content' => [
                'user' => ['page' => '/front/user.php'], 'group' => ['page' => '/front/group.php'],
                'unknown_plugin_entry' => $unknown,
            ]],
            'config' => ['default' => '/front/plugin.php', 'content' => [
                'plugin' => ['page' => '/front/plugin.php'], 'config' => ['page' => '/front/config.form.php'],
                'unknown_plugin_entry' => $unknown,
            ]],
        ];
    }
}
