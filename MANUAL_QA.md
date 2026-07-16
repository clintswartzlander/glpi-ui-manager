# Manual QA checklist

Test on a representative GLPI 11.0.x installation with PHP 8.2 or later. Run checks with GLPI debug mode enabled and retain a profile with full configuration rights.

## A. Fresh installation

- [ ] Install and enable the plugin.
- [ ] Confirm all native asset types remain visible by default.
- [ ] Confirm the configuration page shows the visibility-only warning.

## B. Hide one type

- [ ] Clear **Phones**, save, and reload.
- [ ] Confirm Phones disappears.
- [ ] Confirm other asset types remain.

## C. Hide several types

- [ ] Hide Cartridges, Consumables, SIM Cards, PDUs, and Passive Devices.
- [ ] Confirm only those entries disappear.

## D. Custom assets

- [ ] Create or use a custom Asset Definition named Projectors.
- [ ] Hide all supported native assets.
- [ ] Confirm Projectors remains visible and the top-level Assets menu remains.

## E. Restore

- [ ] Show Phones again and save.
- [ ] Confirm Phones returns.
- [ ] Select **Reset to Defaults** and confirm all supported entries return.

## F. Disable plugin

- [ ] Disable the plugin.
- [ ] Confirm GLPI's native Assets menu returns to its normal state.

## G. Permissions

- [ ] Sign in with a profile that lacks configuration update rights.
- [ ] Confirm the plugin configuration action is unavailable.
- [ ] Open the configuration URL directly and confirm GLPI's normal access-denied response.
- [ ] Attempt a direct POST and confirm it is denied.

## H. Direct access and authorization boundary

- [ ] Hide a type for which the test user retains profile permission.
- [ ] Confirm its normal menu link is absent.
- [ ] Confirm its direct URL can still be reached.
- [ ] Confirm documentation and UI clearly say hiding is not permission revocation.

## I. Themes

- [ ] Verify the configuration page in GLPI's light theme.
- [ ] Verify the configuration page in GLPI's dark theme.

## J. Logs and compatibility

- [ ] Confirm there are no PHP warnings or errors in GLPI logs.
- [ ] Confirm there are no browser-console errors.
- [ ] Confirm the Assets menu contains no empty or malformed entries.
- [ ] Repeat with one expected native entry unavailable to the active profile; confirm filtering fails safely.
- [ ] Verify GLPI 11.0.x menu keys listed under **Live-verification assumptions** in the README/release report.
