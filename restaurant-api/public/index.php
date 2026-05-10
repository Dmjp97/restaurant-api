<?php

/**
 * CodeIgniter 4.5+ front controller
 */

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Require Composer autoload
require_once FCPATH . '../vendor/autoload.php';

// Setup configuration - create app from BaseConfig
$config = new \Config\App();

// Create and run the application
$app = new \CodeIgniter\App($config);
$app->run();
