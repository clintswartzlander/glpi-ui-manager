<?php

declare(strict_types=1);

namespace GlpiPlugin\Uimanager;

final class SupportedMenuRegistry
{
    public const DASHBOARD = 'assets_dashboard';

    /** @var array<string, array{label: string, menu_key: string|null}> */
    private const ITEMS = [
        self::DASHBOARD      => ['label' => 'Assets Dashboard',  'menu_key' => null],
        'computer'           => ['label' => 'Computers',         'menu_key' => 'computer'],
        'monitor'            => ['label' => 'Monitors',          'menu_key' => 'monitor'],
        'software'           => ['label' => 'Software',          'menu_key' => 'software'],
        'networkequipment'   => ['label' => 'Network Devices',   'menu_key' => 'networkequipment'],
        'peripheral'         => ['label' => 'Peripherals',       'menu_key' => 'peripheral'],
        'printer'            => ['label' => 'Printers',          'menu_key' => 'printer'],
        'cartridgeitem'      => ['label' => 'Cartridges',        'menu_key' => 'cartridgeitem'],
        'consumableitem'     => ['label' => 'Consumables',       'menu_key' => 'consumableitem'],
        'phone'              => ['label' => 'Phones',            'menu_key' => 'phone'],
        'rack'               => ['label' => 'Racks',             'menu_key' => 'rack'],
        'enclosure'          => ['label' => 'Enclosures',        'menu_key' => 'enclosure'],
        'pdu'                => ['label' => 'PDUs',              'menu_key' => 'pdu'],
        'passivedcequipment' => ['label' => 'Passive Devices',   'menu_key' => 'passivedcequipment'],
        'unmanaged'          => ['label' => 'Unmanaged Assets',  'menu_key' => 'unmanaged'],
        'cable'              => ['label' => 'Cables',            'menu_key' => 'cable'],
        'item_devicesimcard' => ['label' => 'SIM Cards',         'menu_key' => 'item_devicesimcard'],
        'allassets'          => ['label' => 'Global',            'menu_key' => 'allassets'],
    ];

    /** @return array<string, array{label: string, menu_key: string|null}> */
    public static function all(): array
    {
        return self::ITEMS;
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::ITEMS);
    }

    /** @return array<string, bool> */
    public static function defaults(): array
    {
        return array_fill_keys(self::keys(), true);
    }

    public static function isSupported(string $key): bool
    {
        return isset(self::ITEMS[$key]);
    }
}
