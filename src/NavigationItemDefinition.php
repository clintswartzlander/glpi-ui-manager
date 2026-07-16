<?php

declare(strict_types=1);

namespace GlpiPlugin\Uimanager;

final class NavigationItemDefinition
{
    /** @param list<string> $aliases */
    public function __construct(
        public readonly string $configurationKey,
        public readonly string $section,
        public readonly ?string $menuKey,
        public readonly string $label,
        public readonly bool $defaultVisibility,
        public readonly array $aliases,
        public readonly int $order,
        public readonly ?string $note = null,
    ) {
    }

    public function isDashboard(): bool
    {
        return $this->menuKey === null;
    }

    /** @return list<string> */
    public function menuKeys(): array
    {
        if ($this->menuKey === null) {
            return [];
        }

        return array_values(array_unique([$this->menuKey, ...$this->aliases]));
    }
}
