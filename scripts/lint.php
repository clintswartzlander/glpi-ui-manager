<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);
$failed = false;

foreach ($iterator as $file) {
    if (!$file instanceof SplFileInfo || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    if (preg_match('~[\\\\/](vendor|build|release)[\\\\/]~', $path) === 1) {
        continue;
    }

    $command = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($path);
    passthru($command, $exitCode);
    if ($exitCode !== 0) {
        $failed = true;
    }
}

exit($failed ? 1 : 0);
