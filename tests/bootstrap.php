<?php

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects

use Symfony\Component\Filesystem\Filesystem;

require_once __DIR__ . '/../vendor/autoload.php';

$artifactDir = @getenv('ARTIFACT_DIR');
if (false === $artifactDir) {
    throw new \LogicException('Missing "ARTIFACT_DIR" env var.');
}

if (is_dir($artifactDir)) {
    (new Filesystem())->remove($artifactDir);
}

(new Filesystem())->mkdir($artifactDir);

define('ARTIFACT_DIR', $artifactDir);
