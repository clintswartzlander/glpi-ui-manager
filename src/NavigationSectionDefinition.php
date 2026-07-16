<?php

declare(strict_types=1);

namespace GlpiPlugin\Uimanager;

final class NavigationSectionDefinition
{
    public function __construct(
        public readonly string $key,
        public readonly string $menuKey,
        public readonly string $configurationKey,
        public readonly string $label,
        public readonly int $order,
        public readonly ?string $note = null,
    ) {
    }
}
