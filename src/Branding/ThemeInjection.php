<?php

declare(strict_types=1);

namespace GlpiPlugin\Uimanager\Branding;

final class ThemeInjection
{
    /** @var array<string, string> */
    private const COLOR_VARIABLES = [
        'primary_color' => '--uimanager-primary-color',
        'secondary_color' => '--uimanager-secondary-color',
        'sidebar_background' => '--uimanager-sidebar-background',
        'sidebar_foreground' => '--uimanager-sidebar-foreground',
        'sidebar_icon_color' => '--uimanager-sidebar-icon-color',
        'link_color' => '--uimanager-link-color',
        'danger_color' => '--uimanager-danger-color',
        'warning_color' => '--uimanager-warning-color',
        'success_color' => '--uimanager-success-color',
        'info_color' => '--uimanager-info-color',
    ];

    public function __construct(private readonly BrandingManager $manager = new BrandingManager())
    {
    }

    public function cssForEntity(int $entityId): string
    {
        return $this->generateVariables($this->manager->resolve($entityId));
    }

    /** @param array<string, string> $branding */
    public function generateVariables(array $branding): string
    {
        $declarations = [];
        foreach (self::COLOR_VARIABLES as $key => $variable) {
            $value = $branding[$key] ?? '';
            if (preg_match('/^#[0-9a-fA-F]{6}$/', $value) === 1) {
                $declarations[] = $variable . ':' . strtoupper($value);
            }
        }

        return $declarations === [] ? '' : ':root{' . implode(';', $declarations) . '}';
    }
}
