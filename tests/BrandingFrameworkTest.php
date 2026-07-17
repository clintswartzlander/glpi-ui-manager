<?php

declare(strict_types=1);

namespace GlpiPlugin\Uimanager\Tests;

use ArrayIterator;
use GlpiPlugin\Uimanager\Branding\BrandingManager;
use GlpiPlugin\Uimanager\Branding\BrandingResolver;
use GlpiPlugin\Uimanager\Branding\ThemeInjection;
use PHPUnit\Framework\TestCase;

final class BrandingFrameworkTest extends TestCase
{
    public function testRegistryContainsAllPhaseOneFields(): void
    {
        self::assertSame([
            'expanded_logo', 'collapsed_logo', 'login_logo', 'favicon', 'login_background',
            'application_name', 'primary_color', 'secondary_color', 'sidebar_background',
            'sidebar_foreground', 'sidebar_icon_color', 'link_color', 'button_color',
            'danger_color', 'warning_color', 'success_color', 'info_color', 'custom_css',
        ], array_keys(BrandingManager::fields()));
    }

    public function testResolverUsesChildOverrideAndParentFallback(): void
    {
        $resolver = new class extends BrandingResolver {
            public function entityChain(int $entityId): array { return [2, 1, 0]; }
        };
        $rows = [
            2 => [['item_key' => 'color', 'mode' => 'inherit', 'value' => '', 'is_enabled' => 1]],
            1 => [['item_key' => 'color', 'mode' => 'override', 'value' => '#112233', 'is_enabled' => 1]],
            0 => [['item_key' => 'color', 'mode' => 'override', 'value' => '#445566', 'is_enabled' => 1]],
        ];
        self::assertSame(['color' => '#112233'], $resolver->resolve(2, [
            'color' => ['type' => 'color', 'default' => '#000000', 'section' => 'theme'],
        ], static fn (int $id): array => $rows[$id]));
    }

    public function testDefaultModeStopsParentInheritance(): void
    {
        $resolver = new class extends BrandingResolver {
            public function entityChain(int $entityId): array { return [2, 1, 0]; }
        };
        $rows = [
            2 => [['item_key' => 'color', 'mode' => 'default', 'value' => '', 'is_enabled' => 1]],
            1 => [['item_key' => 'color', 'mode' => 'override', 'value' => '#112233', 'is_enabled' => 1]],
            0 => [],
        ];
        self::assertSame(['color' => '#000000'], $resolver->resolve(2, [
            'color' => ['type' => 'color', 'default' => '#000000', 'section' => 'theme'],
        ], static fn (int $id): array => $rows[$id]));
    }

    public function testThemeInjectionGeneratesOnlyValidNamespacedVariables(): void
    {
        $css = (new ThemeInjection())->generateVariables([
            'primary_color' => '#005a9c',
            'secondary_color' => '#00A3E0',
            'link_color' => 'not-a-color',
        ]);

        self::assertSame(
            ':root{--uimanager-primary-color:#005A9C;--uimanager-secondary-color:#00A3E0}',
            $css
        );
        self::assertSame('', (new ThemeInjection())->generateVariables([]));
    }

    public function testPluginUsesSupportedHooksAndDoesNotPatchTemplates(): void
    {
        $setup = file_get_contents(dirname(__DIR__) . '/setup.php');
        self::assertStringContainsString("['add_css']['uimanager']", (string) $setup);
        self::assertStringContainsString("['add_javascript']['uimanager']", (string) $setup);
        self::assertStringNotContainsString("['add_css_anonymous_page']['uimanager']", (string) $setup);
        self::assertStringNotContainsString("['add_javascript_anonymous_page']['uimanager']", (string) $setup);
        self::assertStringNotContainsString("['display_login']['uimanager']", (string) $setup);
        self::assertStringContainsString("= 'css/branding.css'", (string) $setup);
        self::assertStringContainsString("= 'js/branding.js'", (string) $setup);
        self::assertStringNotContainsString("front/branding.css", (string) $setup);
    }

    public function testEveryRegisteredBrowserAssetExistsOnlyBeneathPublic(): void
    {
        $root = dirname(__DIR__);
        $setup = (string) file_get_contents($root . '/setup.php');
        preg_match_all(
            '~\\$PLUGIN_HOOKS\\[\'(?:add_css|add_javascript|add_css_anonymous_page|add_javascript_anonymous_page)\'\\]\\[\'uimanager\'\\]\\[\\]\\s*=\\s*\'([^\']+)\'~',
            $setup,
            $matches
        );

        self::assertNotEmpty($matches[1]);
        foreach (array_unique($matches[1]) as $asset) {
            self::assertFileExists($root . '/public/' . $asset);
            self::assertFileDoesNotExist($root . '/' . $asset);
        }
    }

    public function testRuntimeScriptInjectsOneStyleBlockWithoutDeferredFeatures(): void
    {
        $script = (string) file_get_contents(dirname(__DIR__) . '/public/js/branding.js');

        self::assertStringContainsString("getElementById('uimanager-branding-runtime')", $script);
        self::assertStringNotContainsString('login_logo', $script);
        self::assertStringNotContainsString('favicon', $script);
        self::assertStringNotContainsString('/plugins/', $script);
        self::assertStringNotContainsString('/marketplace/', $script);
    }

    public function testBrandingStylesheetConsumesVariablesWithoutHardcodedColors(): void
    {
        $css = (string) file_get_contents(dirname(__DIR__) . '/public/css/branding.css');

        self::assertStringContainsString('var(--uimanager-primary-color', $css);
        self::assertStringContainsString('var(--uimanager-secondary-color', $css);
        self::assertStringContainsString('var(--uimanager-link-color', $css);
        self::assertDoesNotMatchRegularExpression('/#[0-9a-fA-F]{3,8}\b/', $css);
        self::assertSame(substr_count($css, '{'), substr_count($css, '}'));
    }

    public function testThemeInjectionResolvesConfigurationOncePerRequest(): void
    {
        global $DB;
        $previous = $DB ?? null;
        $DB = new class {
            public int $requests = 0;
            public function tableExists(string $table): bool { return true; }
            public function request(array $query): ArrayIterator
            {
                $this->requests++;
                return new ArrayIterator([[
                    'item_key' => 'primary_color', 'mode' => 'override',
                    'value' => '#005A9C', 'is_enabled' => 1,
                ]]);
            }
        };
        try {
            $css = (new ThemeInjection())->cssForEntity(0);
            self::assertStringContainsString('--uimanager-primary-color:#005A9C', $css);
            self::assertSame(1, $DB->requests);
        } finally {
            $DB = $previous;
        }
    }

    public function testManagerNormalizesOneDatabaseRowWithoutLeakingIterator(): void
    {
        global $DB;
        $previous = $DB ?? null;
        $DB = new class {
            public function tableExists(string $table): bool { return true; }
            public function request(array $query): ArrayIterator
            {
                return new ArrayIterator([[
                    'item_key' => 'primary_color', 'mode' => 'override',
                    'value' => '#112233', 'is_enabled' => 1,
                ]]);
            }
        };
        try {
            $resolved = (new BrandingManager())->resolve(0);
            self::assertIsArray($resolved);
            self::assertSame('#112233', $resolved['primary_color']);
            self::assertNotInstanceOf(\Traversable::class, $resolved);
        } finally {
            $DB = $previous;
        }
    }

    public function testManagerNormalizesNoDatabaseRowsToTypedDefaults(): void
    {
        global $DB;
        $previous = $DB ?? null;
        $DB = new class {
            public function tableExists(string $table): bool { return true; }
            public function request(array $query): ArrayIterator { return new ArrayIterator([]); }
        };
        try {
            $resolved = (new BrandingManager())->resolve(0);
            self::assertIsArray($resolved);
            self::assertSame('#206bc4', $resolved['primary_color']);
            self::assertNotInstanceOf(\Traversable::class, $resolved);
        } finally {
            $DB = $previous;
        }
    }
}
