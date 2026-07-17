# GLPI UI Manager 1.1.0 manual QA

Test on GLPI 11.0.8 with debug mode enabled, in light and dark themes, using an administrator and a profile without configuration-update rights.

## Upgrade and defaults

- [ ] Upgrade a working 1.0.0 installation without uninstalling.
- [ ] Confirm previously hidden Assets remain hidden.
- [ ] Confirm all new top-level sections and children default visible.
- [ ] Repeat the upgrade action; confirm settings are unchanged.

## Section filtering

- [ ] Assets: exercise all original 18 controls; confirm a custom Projectors Asset Definition and plugin children remain.
- [ ] Assistance: hide Problems, Changes, Statistics, Recurring Tickets, and Recurring Changes; confirm Tickets, Planning, Service Catalog/forms, and plugin entries remain.
- [ ] Management: hide Licenses, Phone Lines, Certificates, Datacenters, Clusters, Appliances, and Databases; confirm Documents, Domains, and unrelated/plugin entries remain.
- [ ] Tools: independently hide Projects, Knowledge Base, Reservations, Reports, Saved Searches, RSS Feeds, Reminders, and Impact Analysis.
- [ ] Administration: hide selected native entries; confirm unknown/plugin entries and native permissions remain.
- [ ] Setup: hide selected entries and Plugins; confirm `/plugins/uimanager/front/config.php` remains reachable to the administrator.

## Top-level and reset behavior

- [ ] Disable Management; confirm the whole sector disappears even with unknown children.
- [ ] Re-enable Management; confirm its stored child settings return.
- [ ] Repeat for another sector.
- [ ] Confirm Hide All Supported Items changes only the selected section's children.
- [ ] Confirm Reset Section affects only that section.
- [ ] Confirm Reset All restores every top-level and child setting visible.
- [ ] Confirm no empty or malformed top-level menus appear.

## Security and lifecycle

- [ ] Confirm a user without `config` UPDATE cannot view, save, reset, or download diagnostics.
- [ ] Confirm state changes reject GET and invalid submitted keys.
- [ ] Confirm normal GLPI automatic CSRF rejection for missing/invalid tokens.
- [ ] Hide an entry and confirm direct URL access remains possible when native rights allow it.
- [ ] Disable the plugin and confirm all normal menus return immediately.
- [ ] Uninstall and confirm only `glpi_plugin_uimanager_configs` is removed.

## Diagnostics and stability

- [ ] Download the diagnostic and confirm sector/submenu technical keys and class names are present.
- [ ] Confirm it contains no URLs, CSRF tokens, session IDs, or personal data.
- [ ] Verify the namespaced Asset Definition, Inventory, and Forms keys and note any missing GLPI 11.0.8 aliases.
- [ ] Confirm no PHP warnings, browser-console errors, or theme contrast/layout issues.

## Recovery Test

- [ ] Hide Plugins.
- [ ] Save.
- [ ] Confirm Plugins disappears.
- [ ] Browse directly to:

   /plugins/uimanager/front/config.php

- [ ] Confirm configuration page loads.
- [ ] Re-enable Plugins.
- [ ] Confirm Plugins menu returns.
