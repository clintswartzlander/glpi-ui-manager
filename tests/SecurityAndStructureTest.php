<?php

declare(strict_types=1);

namespace GlpiPlugin\Assetmenumanager\Tests;

use PHPUnit\Framework\TestCase;

final class SecurityAndStructureTest extends TestCase
{
    public function testConfigurationAuthorizationIsEnforced(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/src/ConfigController.php');

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
        self::assertStringContainsString("['redefine_menus']['assetmenumanager']", $source);
    }

    public function testRepositoryContainsNoGlpiCoreDirectories(): void
    {
        $root = dirname(__DIR__);

        foreach (['src/Glpi', 'inc/includes.php', 'front/computer.php'] as $corePath) {
            self::assertFileDoesNotExist($root . '/' . $corePath);
        }
    }
}
