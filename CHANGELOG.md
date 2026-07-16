# Changelog

All notable changes to this project are documented here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project uses [Semantic Versioning](https://semver.org/).

## [1.0.0] - 2026-07-16

### Added

- GLPI UI Manager foundation with global visibility controls for 18 supported native GLPI Assets menu entries.
- Safe `redefine_menus` filtering that preserves custom Asset Definitions and unrelated menus.
- Administrator-only configuration page with Save, Show All, Hide All Native Assets, and Reset to Defaults actions.
- Idempotent plugin-owned configuration storage and safe uninstall behavior.
- Automated unit, controller-safety, syntax, Composer, and release-archive checks.
- Installation, security-model, upgrade, uninstall, and manual QA documentation.
- Extensible `GlpiPlugin\Uimanager` architecture and `uimanager` release identity.
