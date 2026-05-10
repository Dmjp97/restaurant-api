<?php
/**
 * Front controller for CodeIgniter application (minimal bootstrap).
 */
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Define application and system paths
define('APPPATH', realpath(__DIR__ . '/../app') . DIRECTORY_SEPARATOR);
define('SYSTEMPATH', realpath(__DIR__ . '/../vendor/codeigniter4/framework/system') . DIRECTORY_SEPARATOR);

// Composer autoload
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
}

// Bootstrap CodeIgniter
if (!defined('SYSTEMPATH') || !file_exists(SYSTEMPATH . 'bootstrap.php')) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Framework not installed. Run composer install.';
    exit(1);
}

require SYSTEMPATH . 'bootstrap.php';
