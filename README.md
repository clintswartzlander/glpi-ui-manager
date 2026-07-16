# GLPI Asset Menu Manager

GLPI Asset Menu Manager is a GLPI 11 plugin that lets administrators globally show or hide supported native entries in the **Assets** navigation menu. It is intended for organizations that use only part of GLPI's native inventory and want a simpler menu without editing every profile or changing GLPI core.

> [!IMPORTANT]
> This plugin controls navigation visibility only. Hiding an entry does **not** revoke permission, delete data, or prevent access through a direct URL, search result, API, relationship, or another GLPI page. GLPI profile rights remain the authorization boundary.

## Compatibility

- GLPI 11.0.x
- PHP 8.2, 8.3, or 8.4
- License: GPL-3.0-or-later

## Supported entries

Assets Dashboard, Computers, Monitors, Software, Network Devices, Peripherals, Printers, Cartridges, Consumables, Phones, Racks, Enclosures, PDUs, Passive Devices, Unmanaged Assets, Cables, SIM Cards, and Global.

Only these explicitly registered native keys are filtered. Asset types created through GLPI 11 Asset Definitions are preserved.

## Installation

1. Download `assetmenumanager-1.0.0.zip`.
2. Extract it into GLPI's plugins directory. The resulting folder must be `plugins/assetmenumanager/`.
3. In GLPI, open **Setup → Plugins**.
4. Install and enable **GLPI Asset Menu Manager**.

All entries are shown by default, so installation does not change an existing menu until an administrator saves different settings.

## Configuration

Open **Setup → Plugins**, locate the plugin, and select its configuration action. A user needs GLPI's configuration update right to view or change these settings.

- **Save** stores the individual checkbox states.
- **Show All** makes every supported native entry visible.
- **Hide All Native Assets** hides every supported native entry without affecting custom Asset Definitions.
- **Reset to Defaults** removes overrides and returns to showing all entries.

The menu is regenerated after a save. Reload the page if the browser still displays an older expanded menu.

## Behavior and safety

- The plugin uses GLPI's `redefine_menus` hook; it does not use CSS or JavaScript to hide entries.
- Unknown or missing GLPI menu keys are ignored safely.
- Unrelated menu sectors and custom asset definitions are preserved.
- The top-level Assets menu stays present while any visible native or custom child (or the Assets Dashboard) remains.
- The top-level Assets menu is removed only when no visible child remains.
- Disabling the plugin immediately stops menu filtering.
- Uninstalling removes only `glpi_plugin_assetmenumanager_configs`. It does not alter assets or native profile rights.

## Live-verification assumptions

The registry follows GLPI 11's generated menu structure: the sector is `assets`; native child keys are lowercased class names (`computer`, `networkequipment`, `cartridgeitem`, `passivedcequipment`, and so on); SIM Cards use the class-derived key `item_devicesimcard`; Global uses `allassets`; and the dashboard link is stored separately as `default_dashboard`. These were checked against GLPI's `11.0/bugfixes` source branch when 1.0.0 was built.

Live QA should still confirm the following for the exact GLPI 11.0.x deployment:

- SIM Cards appears only when `Item_DeviceSimcard` is enabled in GLPI's **Devices displayed in menu** setting.
- Assets Dashboard appears only when the active profile has dashboard read permission and an Assets dashboard is configured.
- A distribution-specific patch has not renamed a native menu key. Missing keys are ignored safely if it has.

## Upgrade

Replace the `assetmenumanager/` folder with the new release contents, then run GLPI's normal plugin upgrade action. The schema installer is idempotent and preserves saved visibility settings.

## Development

```bash
composer install
composer validate --strict
composer test
composer lint
```

See [MANUAL_QA.md](MANUAL_QA.md) for live GLPI checks.

## License

Copyright (C) 2026 Clint Swartzlander and contributors. Distributed under the GNU General Public License v3.0 or later. See [LICENSE](LICENSE).
