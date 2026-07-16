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
    'uimanager/setup.php',
    'uimanager/hook.php',
    'uimanager/README.md',
    'uimanager/LICENSE',
];
$seen = [];
$forbidden = ['vendor/', 'tests/', '.git/', '.github/', '.idea/', '.vscode/', 'release/'];

for ($index = 0; $index < $zip->numFiles; $index++) {
    $rawName = (string) $zip->getNameIndex($index);
    if (str_contains($rawName, '\\')) {
        fwrite(STDERR, "Archive entry does not use forward slashes: {$rawName}\n");
        exit(1);
    }
    $name = $rawName;
    if ($name === '' || str_starts_with($name, '/') || str_contains($name, '../')) {
        fwrite(STDERR, "Unsafe archive entry: {$name}\n");
        exit(1);
    }
    if (!str_starts_with($name, 'uimanager/')) {
        fwrite(STDERR, "Unexpected top-level entry: {$name}\n");
        exit(1);
    }
    $relative = substr($name, strlen('uimanager/'));
    foreach ($forbidden as $prefix) {
        if (str_starts_with($relative, $prefix)) {
            fwrite(STDERR, "Forbidden release entry: {$name}\n");
            exit(1);
        }
    }
    $seen[$name] = true;
}

foreach ($required as $name) {
    if (!isset($seen[$name])) {
        fwrite(STDERR, "Missing required archive entry: {$name}\n");
        exit(1);
    }
}

if (!preg_match('/uimanager-(\d+\.\d+\.\d+)\.zip$/', str_replace('\\', '/', $argv[1]), $match)) {
    fwrite(STDERR, "Archive filename must contain the semantic version.\n");
    exit(1);
}
$setup = $zip->getFromName('uimanager/setup.php');
if (!is_string($setup) || !str_contains($setup, "PLUGIN_UIMANAGER_VERSION', '" . $match[1] . "'")) {
    fwrite(STDERR, "Plugin version does not match archive filename.\n");
    exit(1);
}

$zip->close();
echo "Release archive structure is valid.\n";
