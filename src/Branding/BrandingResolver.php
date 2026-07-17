<?php

declare(strict_types=1);

namespace GlpiPlugin\Uimanager\Branding;

class BrandingResolver
{
    /**
     * @param array<string, array{type: string, default: string, section: string}> $fields
     * @param callable(int): iterable<array<string, mixed>> $rowProvider
     * @return array<string, string>
     */
    public function resolve(int $entityId, array $fields, callable $rowProvider): array
    {
        return $this->resolveWithSources($entityId, $fields, $rowProvider)['values'];
    }

    /**
     * @param array<string, array{type: string, default: string, section: string}> $fields
     * @param callable(int): iterable<array<string, mixed>> $rowProvider
     * @return array{values: array<string, string>, sources: array<string, int|null>}
     */
    public function resolveWithSources(int $entityId, array $fields, callable $rowProvider): array
    {
        $chain = $this->entityChain($entityId);
        $rows = [];
        foreach ($chain as $id) {
            foreach ($rowProvider($id) as $row) {
                $rows[$id][(string) $row['item_key']] = $row;
            }
        }
        $resolved = [];
        $sources = [];
        foreach ($fields as $key => $definition) {
            $resolved[$key] = $definition['default'];
            $sources[$key] = null;
            foreach ($chain as $id) {
                $row = $rows[$id][$key] ?? null;
                if ($row === null || !(bool) ($row['is_enabled'] ?? true)) {
                    continue;
                }
                $mode = (string) ($row['mode'] ?? BrandingManager::MODE_INHERIT);
                if ($mode === BrandingManager::MODE_DEFAULT) {
                    $resolved[$key] = $definition['default'];
                    $sources[$key] = $id;
                    break;
                }
                if ($mode === BrandingManager::MODE_OVERRIDE) {
                    $resolved[$key] = (string) ($row['value'] ?? '');
                    $sources[$key] = $id;
                    break;
                }
            }
        }
        return ['values' => $resolved, 'sources' => $sources];
    }

    /** @return list<int> child-to-root, including global zero */
    public function entityChain(int $entityId): array
    {
        if ($entityId <= 0 || !class_exists('Entity')) {
            return [0];
        }
        $chain = [$entityId];
        $seen = [$entityId => true];
        $entity = new \Entity();
        while ($entityId > 0 && $entity->getFromDB($entityId)) {
            $parent = (int) ($entity->fields['entities_id'] ?? 0);
            if ($parent <= 0 || isset($seen[$parent])) {
                break;
            }
            $chain[] = $parent;
            $seen[$parent] = true;
            $entityId = $parent;
        }
        $chain[] = 0;
        return $chain;
    }
}
