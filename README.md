# GLPI UI Manager

GLPI UI Manager is a GLPI 11 plugin that gives global administrators a supported, plugin-owned foundation for simplifying and customizing the GLPI interface without modifying GLPI core. Version 1.0.0 controls whether selected native entries appear in the **Assets** navigation menu.

> [!IMPORTANT]
> This plugin controls navigation visibility only. Hiding an entry does **not** revoke permission, delete data, or prevent access through a direct URL, search result, API, relationship, or another GLPI page. GLPI profile rights remain the authorization boundary.

## Compatibility

- GLPI 11.0.x
- PHP 8.2, 8.3, or 8.4
- GPL-3.0-or-later

## Supported entries

Assets Dashboard, Computers, Monitors, Software, Network Devices, Peripherals, Printers, Cartridges, Consumables, Phones, Racks, Enclosures, PDUs, Passive Devices, Unmanaged Assets, Cables, SIM Cards, and Global.

The registry targets only these native identifiers. It never matches translated labels, broad class patterns, or every child of Assets. Asset types created through GLPI 11 Asset Definitions and third-party plugin entries are therefore preserved.

## Installation

1. Download `uimanager-1.0.0.zip`.
2. Extract it into GLPI's plugins directory so the plugin is at `plugins/uimanager/`.
3. In GLPI, open **Setup → Plugins**.
4. Install and enable **GLPI UI Manager**.

All settings default to visible. Installation creates an empty plugin-owned configuration table and does not change the menu until an administrator saves an override.

## Configuration

Open **Setup → Plugins**, locate GLPI UI Manager, and select its configuration action. The page requires GLPI's configuration update right.

- **Save** stores the displayed checkbox states.
- **Show All** makes every supported native entry visible.
- **Hide All Native Assets** hides every supported native entry without affecting custom Asset Definitions or plugin-created entries.
- **Reset Defaults** removes saved overrides and returns to the all-visible defaults.

The plugin clears GLPI's session menu cache after a change and redirects back with a status message. The updated menu appears on refresh; no web-server restart is needed.

## Behavior and safety

- Menu filtering uses GLPI's supported `redefine_menus` hook, never CSS or JavaScript.
- Unknown submitted configuration keys are rejected; missing GLPI menu keys are ignored safely.
- Only the `assets` sector and explicitly registered native keys are considered.
- The top-level Assets menu remains while a native, custom, or plugin-created child—or the dashboard—remains visible.
- Assets is removed only when no visible child or dashboard remains, so an empty menu is never rendered.
- Disabling the plugin immediately restores GLPI's normal menus because the hook no longer runs.
- Uninstalling removes only `glpi_plugin_uimanager_configs`; it does not alter assets, GLPI data, or native profile rights.

## Architecture

Runtime code uses the `GlpiPlugin\Uimanager` namespace. `SupportedMenuRegistry` owns the narrow list of supported native identifiers, `MenuFilter` is a pure and unit-tested menu transformation, `Config` owns persistence, and the configuration controller and renderer isolate POST handling from presentation. This separation is intended to support additional sectors and future UI operations without replacing the 1.0 filtering core.

## Live-verification assumptions

The registry follows GLPI 11's generated menu structure. The sector is `assets`; native entries use lowercased class names such as `computer`, `networkequipment`, `cartridgeitem`, and `passivedcequipment`; Global is generated as `allassets`; the Assets dashboard is stored separately as `default_dashboard`; and SIM Cards is expected from the configured device class `Item_DeviceSimcard` as `item_devicesimcard`.

The hook contract and native type list were checked against the official GLPI `11.0/bugfixes` source. Live QA should still confirm these deployment-dependent cases:

- SIM Cards appears only when its class is included in GLPI's **Devices displayed in menu** setting.
- Assets Dashboard appears only when the active profile has dashboard read permission and an Assets dashboard is configured.
- A distribution-specific patch has not renamed a native class. Missing keys are ignored safely if it has.

## Upgrade

Back up GLPI according to normal operational practice, replace the contents of `plugins/uimanager/` with the new release, and run GLPI's normal plugin upgrade action. The schema installer is idempotent and preserves saved visibility settings.

## Uninstall

Disable and uninstall the plugin from GLPI, then remove `plugins/uimanager/` if desired. Uninstall drops only the plugin configuration table. Native menus return normally and no GLPI asset data or profile rights are changed.

## Roadmap

The long-term UI-management foundation is intended to grow toward arbitrary menu visibility, profile-aware rules, menu renaming and ordering, custom icons, organization branding, dashboard layouts, default landing pages, and broader navigation simplification.

## Development

```bash
composer install
composer validate --strict
composer test
composer lint
powershell -File scripts/build-release.ps1
php scripts/validate-release.php release/uimanager-1.0.0.zip
```

See [MANUAL_QA.md](MANUAL_QA.md) for live GLPI checks.

## License

Copyright (C) 2026 Clint Swartzlander and contributors. Distributed under the GNU General Public License v3.0 or later. See [LICENSE](LICENSE).
