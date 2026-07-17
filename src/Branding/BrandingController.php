<?php

declare(strict_types=1);

namespace GlpiPlugin\Uimanager\Branding;

use InvalidArgumentException;

final class BrandingController
{
    public static function authorize(): void
    {
        \Session::checkRight('config', UPDATE);
    }

    public static function entityId(mixed $value): int
    {
        $entityId = filter_var($value ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        if ($entityId === false) {
            throw new InvalidArgumentException('A valid entity is required.');
        }
        if ($entityId > 0 && class_exists('Entity') && !(new \Entity())->getFromDB($entityId)) {
            throw new InvalidArgumentException('The selected entity does not exist.');
        }
        return $entityId;
    }

    /** @param array<string, mixed> $post @param array<string, mixed> $files */
    public static function process(array $post, array $files): int
    {
        self::authorize();
        $entityId = self::entityId($post['entities_id'] ?? 0);
        $settings = $post['branding'] ?? [];
        if (!is_array($settings)) {
            throw new InvalidArgumentException('Invalid branding submission.');
        }
        $normalizedFiles = [];
        if (isset($files['branding']) && is_array($files['branding'])) {
            foreach (BrandingManager::fields() as $key => $definition) {
                if ($definition['type'] !== 'asset') {
                    continue;
                }
                $normalizedFiles[$key] = [
                    'name' => $files['branding']['name'][$key]['file'] ?? '',
                    'type' => $files['branding']['type'][$key]['file'] ?? '',
                    'tmp_name' => $files['branding']['tmp_name'][$key]['file'] ?? '',
                    'error' => $files['branding']['error'][$key]['file'] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $files['branding']['size'][$key]['file'] ?? 0,
                ];
            }
        }
        (new BrandingManager())->save($entityId, $settings, $normalizedFiles);
        return $entityId;
    }
}
