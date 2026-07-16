<?php

declare(strict_types=1);

namespace GlpiPlugin\Uimanager\Tests;

use GlpiPlugin\Uimanager\ConfigurationController;
use GlpiPlugin\Uimanager\SupportedMenuRegistry;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SecurityAndStructureTest extends TestCase
{
    public function testConfigurationAuthorizationIsEnforced(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/src/ConfigurationController.php');

        self::assertIsString($source);
        self::assertStringContainsString("Session::checkRight('config', UPDATE)", $source);
    }

    public function testPostControllerIsPostOnlyAndUsesNativeCsrfHandling(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/front/config.form.php');

        self::assertIsString($source);
        self::assertStringContainsString("REQUEST_METHOD", $source);
        self::assertStringContainsString("'POST'", $source);
        self::assertStringNotContainsString('checkCSRF', $source);
    }

    public function testSetupRegistersSupportedRedefineMenusHook(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/setup.php');

        self::assertIsString($source);
        self::assertStringContainsString("['redefine_menus']['uimanager']", $source);
    }

    public function testShowAllPresetUsesDefaults(): void
    {
        self::assertSame(
            SupportedMenuRegistry::defaults(),
            ConfigurationController::visibilityForPreset('show_all')
        );
    }

    public function testHideAllPresetOnlyTargetsSupportedNativeMenus(): void
    {
        self::assertSame(
            array_fill_keys(SupportedMenuRegistry::keys(), false),
            ConfigurationController::visibilityForPreset('hide_all')
        );
    }

    public function testUnknownPresetIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ConfigurationController::visibilityForPreset('hide_everything');
    }

    public function testResetSectionAffectsOnlyThatSection(): void
    {
        $visibility = array_fill_keys(SupportedMenuRegistry::keys(), false);
        $result = ConfigurationController::visibilityForSectionAction(
            $visibility,
            'management',
            'section_reset'
        );
        self::assertTrue($result['section_management']);
        self::assertTrue($result['management_licenses']);
        self::assertFalse($result['section_tools']);
        self::assertFalse($result['tools_projects']);
    }

    public function testResetAllRemovesOverrides(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/src/ConfigurationController.php');

        self::assertIsString($source);
        self::assertStringContainsString("case 'reset_all':", $source);
        self::assertStringContainsString('Config::reset();', $source);
    }

    public function testDiagnosticControllerRequiresConfigurationUpdateRight(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/front/menu-diagnostic.php');
        self::assertIsString($source);
        self::assertStringContainsString('ConfigurationController::authorize();', $source);
    }

    public function testNoManualDuplicateCsrfValidationExists(): void
    {
        foreach (['front/config.form.php', 'src/ConfigurationController.php'] as $file) {
            $source = file_get_contents(dirname(__DIR__) . '/' . $file);
            self::assertIsString($source);
            self::assertStringNotContainsString('checkCSRF', $source);
        }
    }

    public function testPluginDisableRestoresMenusByLeavingCoreUntouched(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/hook.php');

        self::assertIsString($source);
        self::assertStringContainsString('plugin_uimanager_redefine_menus', $source);
        self::assertStringNotContainsString('register_shutdown_function', $source);
    }

    public function testRepositoryContainsNoGlpiCoreDirectories(): void
    {
        $root = dirname(__DIR__);

        foreach (['src/Glpi', 'inc/includes.php', 'front/computer.php'] as $corePath) {
            self::assertFileDoesNotExist($root . '/' . $corePath);
        }
    }
}
