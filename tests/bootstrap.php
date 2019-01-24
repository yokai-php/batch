<?php

use Symfony\Component\Filesystem\Filesystem;

require_once __DIR__.'/../vendor/autoload.php';

$artifactDir = @getenv('ARTIFACT_DIR');
if (false === $artifactDir) {
    throw new \LogicException('Missing "ARTIFACT_DIR" env var.');
}

if (is_dir($artifactDir)) {
    (new Filesystem())->remove($artifactDir);
}

define('UNIT_ARTIFACT_DIR', $artifactDir.'/unit');
define('INTEGRATION_ARTIFACT_DIR', $artifactDir.'/integration');
