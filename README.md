# GLPI UI Manager

GLPI UI Manager 1.1.0 gives administrators global visibility controls for supported native navigation entries in GLPI 11.0.x. It uses GLPI's `redefine_menus` hook and never changes core files, rights, or data.

> **Visibility is not authorization.** Hidden pages can remain reachable by direct URL, API, search, or relationships when the user has native rights. GLPI profile permissions remain the authorization boundary.
>
> > [!WARNING]
> ## Recovery if Plugins or Setup are hidden
>
> GLPI UI Manager allows administrators to hide the **Plugins** and **Setup**
> navigation entries.
>
> If you hide those menus, the plugin configuration remains available directly at:
>
> `/plugins/uimanager/front/config.php`
>
> Example:
>
> `https://your-glpi-server/plugins/uimanager/front/config.php`
>
> Bookmark this URL before hiding either menu.
>
> Hiding navigation entries does **not** lock administrators out of the plugin.

## Compatibility

- GLPI 11.0.x
- PHP 8.2, 8.3, and 8.4
- Plugin folder/key `uimanager`; namespace `GlpiPlugin\Uimanager`

## Supported navigation

- **Assets:** dashboard, Computers, Monitors, Software, Network Devices, Peripherals, Printers, Cartridges, Consumables, Phones, Racks, Enclosures, PDUs, Passive Devices, Unmanaged Assets, Cables, SIM Cards, Global.
- **Assistance:** dashboard, Tickets, Problems, Changes, Planning, Statistics, Recurring Tickets, Recurring Changes. Service Catalog and unregistered forms are preserved.
- **Management:** Licenses, Documents, Phone Lines, Certificates, Datacenters, Clusters, Appliances, Databases, Suppliers, Contacts, Contracts, Budgets, and the native GLPI 11 Domains entry.
- **Tools:** Projects, Knowledge Base, Reservations, Reports, Saved Searches, RSS Feeds, plus native GLPI 11 Reminders and Impact Analysis.
- **Administration:** Users, Groups, Entities, Rules, Dictionaries, Profiles, Notification Queue, Logs, Inventory, Forms.
- **Setup:** Asset Definitions, Dropdowns, Components, Notifications, Webhooks, Service Levels, General, Fields Uniqueness, Automatic Actions, Authentication, OAuth Clients, Receivers, External Links, Plugins.

Each section has an independent top-level switch. Turning it off hides the whole sector but preserves child choices for re-enable. When the section is on, only explicitly hidden supported children are removed. Unknown, custom, and third-party children remain untouched. A sector is removed when it has no dashboard or remaining child; an explicit top-level hide removes it regardless of unknown children.

## Install and configure

1. Extract `uimanager-1.1.0.zip` into GLPI's plugins directory, producing `plugins/uimanager/setup.php`.
2. Install and enable **GLPI UI Manager** under **Setup → Plugins**.
3. Open its configuration action, or browse directly to `/plugins/uimanager/front/config.php`.

The direct configuration path remains usable by an authorized administrator even when Setup or Plugins is hidden. The page requires the GLPI `config` update right. All top-level and child settings default visible.

Section actions affect only their section. **Reset All to Defaults** deletes plugin overrides and returns every registered setting to visible. Menu cache is cleared immediately after a change.

## Upgrade from 1.0.0

Back up GLPI normally, replace `plugins/uimanager/` with the 1.1.0 release, then run GLPI's plugin upgrade. The existing arbitrary-key table is reused. Its idempotent installer does not overwrite rows: all saved 1.0.0 Assets choices survive, while every new section and item defaults visible. No uninstall/reinstall is required.

## Diagnostics

Authorized administrators can select **Download Menu Diagnostic** on the configuration page. Visit the normal GLPI menu once first so the hook captures the current tree. The JSON contains sector keys, submenu/option keys, class/itemtype identifiers where present, and labels as supplemental context. It intentionally excludes URLs, CSRF/session values, and arbitrary data. Use it to confirm deployment-specific aliases on GLPI 11.0.8.

## Safety and lifecycle

- No CSS/JavaScript menu hiding and no GLPI core modifications.
- Unknown/plugin children and custom Asset Definitions (for example Projectors) are preserved unless their entire sector is explicitly disabled.
- Hiding Licenses does not affect Assets Software; hiding Documents does not affect attachments; hiding Suppliers affects navigation only.
- Disabling the plugin stops the hook and restores normal menus immediately.
- Uninstall drops only `glpi_plugin_uimanager_configs`; it changes no native data or profile rights.

## Technical keys and verification

The registry follows GLPI's official `11.0/bugfixes` menu generation: sector keys `assets`, `helpdesk`, `management`, `tools`, `admin`, `config`; child keys are listed in `SupportedMenuRegistry`. Namespaced GLPI 11 keys have compatibility aliases for Asset Definitions, Inventory, and Forms; Dictionaries also accepts GLPI's internal `dictionnary` spelling.

Live 11.0.8 QA should confirm dashboard availability, optional SIM Card/device visibility, namespaced `glpi\asset\assetdefinition`, `glpi\inventory\inventory`, and `glpi\form\form`, plus any distribution patch. Missing keys are ignored safely and the diagnostic is designed to identify additional aliases.

## Roadmap

Future work may include profile- or entity-specific visibility, ordering, renaming, dashboards, landing pages, ticket-screen customization, branding, and Quick Actions integration. These are not implemented in 1.1.0.

## Development

```text
composer validate --strict
composer lint
composer test
powershell -File scripts/build-release.ps1 -Version 1.1.0
php scripts/validate-release.php release/uimanager-1.1.0.zip
```

See `MANUAL_QA.md` for deployment checks. Licensed GPL-3.0-or-later.
