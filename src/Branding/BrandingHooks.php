<?php

declare(strict_types=1);

namespace GlpiPlugin\Uimanager\Branding;

final class BrandingHooks
{
    public static function currentEntityId(): int
    {
        return isset($_SESSION['glpiactive_entity']) ? max(0, (int) $_SESSION['glpiactive_entity']) : 0;
    }
}
