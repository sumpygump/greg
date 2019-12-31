<?php

// App root
define('APP_ROOT', __DIR__);

// Autoloader
if (file_exists(__DIR__ . '/vendor.phar')) {
    $autoload = require_once __DIR__ . '/vendor.phar';
} elseif (file_exists(__DIR__ . '/vendor/autoload.php')) {
    $autoload = require_once __DIR__ . '/vendor/autoload.php';
} else {
    die("Autoloader not found. Run `composer install`\n");
}

$autoload->add('Greg', APP_ROOT . '/src');

function get_user_home($app_dir = '.greg')
{
    $home = getenv('HOME');
    if (!empty($home)) {
        // home should never end with a trailing slash.
        $home = rtrim($home, '/');
    }
    elseif (!empty($_SERVER['HOMEDRIVE']) && !empty($_SERVER['HOMEPATH'])) {
        // home on windows
        $home = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
        // If HOMEPATH is a root directory the path can end with a slash. Make sure
        // that doesn't happen.
        $home = rtrim($home, '\\/');
    }

    return empty($home) ? __DIR__ . "/data" : $home . '/' . $app_dir;
}

$config = [
    'base_dir' => get_user_home(),
];
return $config;
