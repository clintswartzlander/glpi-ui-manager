<?php

declare(strict_types=1);

if ($argc !== 2) {
    fwrite(STDERR, "Usage: php scripts/validate-release.php <archive.zip>\n");
    exit(2);
}

$zip = new ZipArchive();
if ($zip->open($argv[1]) !== true) {
    fwrite(STDERR, "Could not open release archive.\n");
    exit(1);
}

$required = [
    'assetmenumanager/setup.php',
    'assetmenumanager/hook.php',
    'assetmenumanager/README.md',
    'assetmenumanager/LICENSE',
];
$seen = [];

for ($index = 0; $index < $zip->numFiles; $index++) {
    $name = str_replace('\\', '/', (string) $zip->getNameIndex($index));
    if ($name === '' || str_starts_with($name, '/') || str_contains($name, '../')) {
        fwrite(STDERR, "Unsafe archive entry: {$name}\n");
        exit(1);
    }
    if (!str_starts_with($name, 'assetmenumanager/')) {
        fwrite(STDERR, "Unexpected top-level entry: {$name}\n");
        exit(1);
    }
    $seen[$name] = true;
}

foreach ($required as $name) {
    if (!isset($seen[$name])) {
        fwrite(STDERR, "Missing required archive entry: {$name}\n");
        exit(1);
    }
}

$zip->close();
echo "Release archive structure is valid.\n";
