# GLPI UI Manager 1.2.0 manual QA

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

## Branding framework

- [ ] Upload distinct expanded and collapsed sidebar logos; refresh normally and confirm both variants change with successful HTTP 200 image requests containing a `v` cache key.
- [ ] Change each logo again and confirm the new image appears without clearing browser cache.
- [ ] Delete each override and confirm GLPI defaults remain with no broken-image placeholder or image 404.
- [ ] Set a long application name and confirm the visible brand text truncates without shifting navigation layout; verify the full value remains in title/accessible labels.
- [ ] Test parent logo overrides with a child inheriting them; inspect Runtime logo diagnostics for entity, source, relative URL, and Ready status.
- [ ] Confirm authenticated pages perform one branding configuration request, produce no logo-related PHP warnings or JavaScript errors, and do not affect the login page or favicon.
- [ ] Set Primary to `#005A9C` and Secondary to `#00A3E0`; clear GLPI cache and confirm `:root` exposes the corresponding `--uimanager-primary-color` and `--uimanager-secondary-color` values.
- [ ] Confirm primary buttons/active navigation and secondary buttons/utilities reflect the resolved colors, then save a second color pair and confirm the single `#uimanager-branding-runtime` block updates on refresh.
- [ ] Confirm authenticated pages request `branding.config.php` once, contain no duplicate runtime style blocks, and report no PHP warnings, CSS errors, or JavaScript errors.
- [ ] Confirm the login page does not request Branding CSS or JavaScript during this authenticated-theme-only sprint.
- [ ] Open `/plugins/uimanager/front/config.php`; confirm it loads without Branding asset errors and existing menu visibility controls still work.
- [ ] Open `/plugins/uimanager/front/branding.php`; confirm HTTP 200, a rendered configuration page, and no red GLPI error banner.
- [ ] In Network, confirm `/plugins/uimanager/css/branding.css` returns HTTP 200 with `text/css` and `/plugins/uimanager/js/branding.js` returns HTTP 200 with a JavaScript content type; neither response may contain a GLPI HTML error page.
- [ ] Confirm the browser console has no Branding MIME errors, 404s, or uncaught JavaScript errors.
- [ ] Confirm GLPI logs have no new `BrandingManager` TypeError, SQL errors, or PHP warnings after loading both pages.
- [ ] Configure global colors and application name; confirm variables and title apply without a core/template change.
- [ ] Upload each supported format (PNG, sanitized SVG, ICO, WEBP, JPG); confirm preview, replacement, deletion, and default restore.
- [ ] Reject an unsupported type, a file over 5 MB, and an SVG containing script/event-handler content.
- [ ] Set a parent override, child inheritance, and grandchild inheritance; confirm the parent value resolves.
- [ ] Set a child override and confirm it wins; choose Default and confirm the framework default wins instead.
- [ ] Confirm the expanded/collapsed sidebar logos, login logo/background, and favicon in authenticated and anonymous views.
- [ ] Confirm theme colors in light/dark themes and check contrast manually.
- [ ] Add custom CSS with multiple selectors; confirm it is scoped beneath `html[data-uimanager-branding]` and `@import` is removed.
- [ ] Disable and uninstall the plugin; confirm native presentation returns and only plugin data/assets are removed.
