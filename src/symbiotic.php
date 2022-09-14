<?php

$config = include __DIR__ . '/config.sample.php';

// Collaboration mode with another framework
//$config['symbiotic'] = true;
// You can also run it in the root, without a prefix
//$config['uri_prefix'] = '';

// Loading the Core
if (isset($config['storage_path'])) {
    $cache = new Symbiotic\Cache\FilesystemCache($config['storage_path'] . '/cache/core');
    // Loading cached Core
    $app = (new \Symbiotic\Core\ContainerBuilder($cache))
        ->buildCore($config);
} else {
    $app = new \Symbiotic\Core\Core($config);
}

/// Starting processing
$app->run();

/**
 * In symbiosis mode, the framework processes only its own requests
 * Then your framework can be launched
 */

