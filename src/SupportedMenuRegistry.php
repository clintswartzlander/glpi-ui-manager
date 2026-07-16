<?php

declare(strict_types=1);

namespace GlpiPlugin\Uimanager;

use InvalidArgumentException;

final class SupportedMenuRegistry
{
    public const DASHBOARD = 'assets_dashboard';

    /** @return array<string, NavigationSectionDefinition> */
    public static function getSections(): array
    {
        $definitions = [
            new NavigationSectionDefinition('assets', 'assets', 'section_assets', 'Assets', 10, 'Custom Asset Definitions and plugin entries are preserved.'),
            new NavigationSectionDefinition('assistance', 'helpdesk', 'section_assistance', 'Assistance', 20, 'Service Catalog and unregistered forms remain untouched.'),
            new NavigationSectionDefinition('management', 'management', 'section_management', 'Management', 30),
            new NavigationSectionDefinition('tools', 'tools', 'section_tools', 'Tools', 40),
            new NavigationSectionDefinition('administration', 'admin', 'section_administration', 'Administration', 50, 'Plugin-provided and unknown entries remain untouched.'),
            new NavigationSectionDefinition('setup', 'config', 'section_setup', 'Setup', 60, 'The direct UI Manager configuration URL remains available even if Plugins or Setup is hidden.'),
        ];

        $sections = [];
        foreach ($definitions as $definition) {
            $sections[$definition->key] = $definition;
        }
        return $sections;
    }

    /** @return list<NavigationItemDefinition> */
    public static function getItemsForSection(string $section): array
    {
        if (!isset(self::getSections()[$section])) {
            throw new InvalidArgumentException('Unknown navigation section.');
        }

        return array_values(array_filter(
            self::getItems(),
            static fn (NavigationItemDefinition $item): bool => $item->section === $section
        ));
    }

    /** @return array<string, NavigationItemDefinition> */
    public static function all(): array
    {
        $items = [];
        foreach (self::getItems() as $item) {
            $items[$item->configurationKey] = $item;
        }
        return $items;
    }

    /** @return list<string> */
    public static function getAllKnownKeys(): array
    {
        $keys = [];
        foreach (self::getSections() as $section) {
            $keys[] = $section->configurationKey;
        }
        foreach (self::getItems() as $item) {
            $keys[] = $item->configurationKey;
        }
        return $keys;
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return self::getAllKnownKeys();
    }

    /** @return array<string, bool> */
    public static function getDefaultVisibilityMap(): array
    {
        $defaults = [];
        foreach (self::getSections() as $section) {
            $defaults[$section->configurationKey] = true;
        }
        foreach (self::getItems() as $item) {
            $defaults[$item->configurationKey] = $item->defaultVisibility;
        }
        return $defaults;
    }

    /** @return array<string, bool> */
    public static function defaults(): array
    {
        return self::getDefaultVisibilityMap();
    }

    /** @return list<string> */
    public static function resolveMenuKeysForItem(string|NavigationItemDefinition $item): array
    {
        if (is_string($item)) {
            $item = self::all()[$item] ?? null;
        }
        if (!$item instanceof NavigationItemDefinition) {
            return [];
        }
        return $item->menuKeys();
    }

    /** @param list<string> $keys @return list<string> */
    public static function validateSubmittedKeys(array $keys): array
    {
        $known = array_fill_keys(self::getAllKnownKeys(), true);
        foreach ($keys as $key) {
            if (!is_string($key) || !isset($known[$key])) {
                throw new InvalidArgumentException('An unsupported navigation configuration key was submitted.');
            }
        }
        return $keys;
    }

    public static function isSupported(string $key): bool
    {
        return in_array($key, self::getAllKnownKeys(), true);
    }

    public static function getSection(string $section): NavigationSectionDefinition
    {
        return self::getSections()[$section] ?? throw new InvalidArgumentException('Unknown navigation section.');
    }

    /** @return list<NavigationItemDefinition> */
    private static function getItems(): array
    {
        $i = static fn (string $key, string $section, ?string $menuKey, string $label, int $order, array $aliases = [], ?string $note = null): NavigationItemDefinition
            => new NavigationItemDefinition($key, $section, $menuKey, $label, true, $aliases, $order, $note);

        return [
            // Assets keys intentionally retain their 1.0.0 persistence identifiers.
            $i(self::DASHBOARD, 'assets', null, 'Assets Dashboard', 10),
            $i('computer', 'assets', 'computer', 'Computers', 20),
            $i('monitor', 'assets', 'monitor', 'Monitors', 30),
            $i('software', 'assets', 'software', 'Software', 40),
            $i('networkequipment', 'assets', 'networkequipment', 'Network Devices', 50),
            $i('peripheral', 'assets', 'peripheral', 'Peripherals', 60),
            $i('printer', 'assets', 'printer', 'Printers', 70),
            $i('cartridgeitem', 'assets', 'cartridgeitem', 'Cartridges', 80),
            $i('consumableitem', 'assets', 'consumableitem', 'Consumables', 90),
            $i('phone', 'assets', 'phone', 'Phones', 100),
            $i('rack', 'assets', 'rack', 'Racks', 110),
            $i('enclosure', 'assets', 'enclosure', 'Enclosures', 120),
            $i('pdu', 'assets', 'pdu', 'PDUs', 130),
            $i('passivedcequipment', 'assets', 'passivedcequipment', 'Passive Devices', 140),
            $i('unmanaged', 'assets', 'unmanaged', 'Unmanaged Assets', 150),
            $i('cable', 'assets', 'cable', 'Cables', 160),
            $i('item_devicesimcard', 'assets', 'item_devicesimcard', 'SIM Cards', 170),
            $i('allassets', 'assets', 'allassets', 'Global', 180),

            $i('assistance_dashboard', 'assistance', null, 'Assistance Dashboard', 10),
            $i('assistance_tickets', 'assistance', 'ticket', 'Tickets', 20),
            $i('assistance_problems', 'assistance', 'problem', 'Problems', 30),
            $i('assistance_changes', 'assistance', 'change', 'Changes', 40),
            $i('assistance_planning', 'assistance', 'planning', 'Planning', 50),
            $i('assistance_statistics', 'assistance', 'stat', 'Statistics', 60, ['statistics']),
            $i('assistance_recurring_tickets', 'assistance', 'ticketrecurrent', 'Recurring Tickets', 70),
            $i('assistance_recurring_changes', 'assistance', 'recurrentchange', 'Recurring Changes', 80),

            $i('management_licenses', 'management', 'softwarelicense', 'Licenses', 10),
            $i('management_documents', 'management', 'document', 'Documents', 20),
            $i('management_phone_lines', 'management', 'line', 'Phone Lines', 30),
            $i('management_certificates', 'management', 'certificate', 'Certificates', 40),
            $i('management_datacenters', 'management', 'datacenter', 'Datacenters', 50),
            $i('management_clusters', 'management', 'cluster', 'Clusters', 60),
            $i('management_appliances', 'management', 'appliance', 'Appliances', 70),
            $i('management_databases', 'management', 'database', 'Databases', 80),
            $i('management_suppliers', 'management', 'supplier', 'Suppliers', 90),
            $i('management_contacts', 'management', 'contact', 'Contacts', 100),
            $i('management_contracts', 'management', 'contract', 'Contracts', 110),
            $i('management_budgets', 'management', 'budget', 'Budgets', 120),
            $i('management_domains', 'management', 'domain', 'Domains', 130, [], 'Native GLPI 11 Management entry.'),

            $i('tools_projects', 'tools', 'project', 'Projects', 10),
            $i('tools_knowledge_base', 'tools', 'knowbaseitem', 'Knowledge Base', 20),
            $i('tools_reservations', 'tools', 'reservationitem', 'Reservations', 30),
            $i('tools_reports', 'tools', 'report', 'Reports', 40),
            $i('tools_saved_searches', 'tools', 'savedsearch', 'Saved Searches', 50),
            $i('tools_rss_feeds', 'tools', 'rssfeed', 'RSS Feeds', 60),
            $i('tools_reminders', 'tools', 'reminder', 'Reminders', 70, [], 'Native GLPI 11 Tools entry.'),
            $i('tools_impact', 'tools', 'impact', 'Impact Analysis', 80, [], 'Native GLPI 11 Tools entry.'),

            $i('administration_users', 'administration', 'user', 'Users', 10),
            $i('administration_groups', 'administration', 'group', 'Groups', 20),
            $i('administration_entities', 'administration', 'entity', 'Entities', 30),
            $i('administration_rules', 'administration', 'rule', 'Rules', 40),
            $i('administration_dictionaries', 'administration', 'dictionnary', 'Dictionaries', 50, ['dictionary']),
            $i('administration_profiles', 'administration', 'profile', 'Profiles', 60),
            $i('administration_notification_queue', 'administration', 'queuednotification', 'Notification Queue', 70),
            $i('administration_logs', 'administration', 'logviewer', 'Logs', 80, ['log']),
            $i('administration_inventory', 'administration', 'glpi\\inventory\\inventory', 'Inventory', 90, ['inventory']),
            $i('administration_forms', 'administration', 'glpi\\form\\form', 'Forms', 100, ['form']),

            $i('setup_asset_definitions', 'setup', 'glpi\\asset\\assetdefinition', 'Asset Definitions', 10, ['assetdefinition']),
            $i('setup_dropdowns', 'setup', 'commondropdown', 'Dropdowns', 20),
            $i('setup_components', 'setup', 'commondevice', 'Components', 30),
            $i('setup_notifications', 'setup', 'notification', 'Notifications', 40),
            $i('setup_webhooks', 'setup', 'webhook', 'Webhooks', 50),
            $i('setup_service_levels', 'setup', 'slm', 'Service Levels', 60),
            $i('setup_general', 'setup', 'config', 'General', 70),
            $i('setup_fields_uniqueness', 'setup', 'fieldunicity', 'Fields Uniqueness', 80),
            $i('setup_automatic_actions', 'setup', 'crontask', 'Automatic Actions', 90),
            $i('setup_authentication', 'setup', 'auth', 'Authentication', 100),
            $i('setup_oauth_clients', 'setup', 'oauthclient', 'OAuth Clients', 110),
            $i('setup_receivers', 'setup', 'mailcollector', 'Receivers', 120),
            $i('setup_external_links', 'setup', 'link', 'External Links', 130),
            $i('setup_plugins', 'setup', 'plugin', 'Plugins', 140, [], 'UI Manager remains reachable at /plugins/uimanager/front/config.php.'),
        ];
    }
}
