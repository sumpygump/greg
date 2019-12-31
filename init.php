<?php

// App root
define('APP_ROOT', __DIR__);

// Autoloader
/*
if (file_exists(__DIR__ . '/vendor.phar')) {
    $autoload = require_once __DIR__ . '/vendor.phar';
} elseif (file_exists(__DIR__ . '/vendor/autoload.php')) {
    $autoload = require_once __DIR__ . '/vendor/autoload.php';
} else {
    die("Autoloader not found. Run `composer install`\n");
}
 */

// Find where the composer autoload is
// This tool was installed as a composed dependency or directly
$root = realpath(__DIR__);
$autoloadLocations = array(
    __DIR__ . '/vendor.phar',
    __DIR__ . '/../../autoload.php',
    $root . DIRECTORY_SEPARATOR . 'vendor/autoload.php',
);
foreach ($autoloadLocations as $file) {
    if (file_exists($file)) {
        define('APP_COMPOSER_AUTOLOAD', $file);
        break;
    }
}
// Composer autoload require guard
if (!defined('APP_COMPOSER_AUTOLOAD')) {
    die(
        "You must run the command `composer install` from the terminal "
        . "in the directory '$root' before using this tool.\n"
    );
}
// Load composer autoloader
$autoload = require_once APP_COMPOSER_AUTOLOAD;

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
