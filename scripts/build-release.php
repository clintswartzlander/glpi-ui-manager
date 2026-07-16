<?php

declare(strict_types=1);

$version = $argv[1] ?? '1.1.0';
if (preg_match('/^\d+\.\d+\.\d+$/', $version) !== 1) {
    fwrite(STDERR, "Version must use semantic version format.\n");
    exit(2);
}

$root = dirname(__DIR__);
$release = $root . DIRECTORY_SEPARATOR . 'release';
if (!is_dir($release) && !mkdir($release, 0777, true) && !is_dir($release)) {
    fwrite(STDERR, "Could not create release directory.\n");
    exit(1);
}

$archive = $release . DIRECTORY_SEPARATOR . "uimanager-{$version}.zip";
$zip = new ZipArchive();
if ($zip->open($archive, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, "Could not create release archive.\n");
    exit(1);
}

$entries = file($root . '/scripts/package-files.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($entries === false) {
    fwrite(STDERR, "Could not read package manifest.\n");
    exit(1);
}

foreach ($entries as $entry) {
    $source = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $entry);
    if (is_file($source)) {
        $zip->addFile($source, 'uimanager/' . str_replace('\\', '/', $entry));
        continue;
    }
    if (!is_dir($source)) {
        fwrite(STDERR, "Missing package entry: {$entry}\n");
        exit(1);
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }
        $relative = substr($file->getPathname(), strlen($root) + 1);
        $zip->addFile($file->getPathname(), 'uimanager/' . str_replace('\\', '/', $relative));
    }
}

$zip->close();
echo $archive . PHP_EOL;
