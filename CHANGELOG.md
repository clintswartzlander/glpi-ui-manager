# Changelog

All notable changes follow Keep a Changelog and Semantic Versioning.

## [1.1.0] - 2026-07-16

### Added

- Operational top-level and child visibility controls for Assets, Assistance, Management, Tools, Administration, and Setup.
- Native GLPI 11 controls for Management Domains and Tools Reminders and Impact Analysis.
- Accessible section cards with Show All, Hide All Supported Items, Reset Section, Save Changes, and Reset All actions.
- Administrator-only sanitized menu diagnostic download containing sector, submenu, and class/type identifiers.
- PHP 8.2/8.3/8.4 CI and stricter release archive/version validation.

### Changed

- Generalized the registry and filter while preserving all 18 version 1.0.0 Assets configuration keys.
- Added independent top-level section settings; disabling a section preserves its child choices.
- Expanded upgrade, filtering, security, diagnostic, and configuration tests.

### Security

- Configuration, reset, and diagnostic access require GLPI configuration-update rights.
- POST keys are restricted to the registry; GLPI's automatic CSRF handling remains authoritative.

## [1.0.0] - 2026-07-16

- Initial GLPI 11 release with 18 native Assets navigation controls.
