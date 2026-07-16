<?php

declare(strict_types=1);

namespace GlpiPlugin\Uimanager\Tests;

use GlpiPlugin\Uimanager\MenuDiagnostic;
use PHPUnit\Framework\TestCase;

final class MenuDiagnosticTest extends TestCase
{
    public function testDiagnosticContainsTechnicalKeysAndExcludesSensitiveFields(): void
    {
        $result = MenuDiagnostic::sanitize([
            'config' => [
                'title' => 'Setup',
                'types' => ['Plugin', 'Glpi\\Asset\\AssetDefinition'],
                'csrf_token' => 'secret',
                'session_id' => 'secret',
                'content' => [
                    'plugin' => ['title' => 'Plugins', 'page' => '/front/plugin.php?token=secret'],
                ],
            ],
        ]);
        $json = json_encode($result, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('config', $result['sectors']);
        self::assertArrayHasKey('plugin', $result['sectors']['config']['content']);
        self::assertStringNotContainsString('secret', $json);
        self::assertStringNotContainsString('csrf', $json);
        self::assertStringNotContainsString('/front/', $json);
    }
}
