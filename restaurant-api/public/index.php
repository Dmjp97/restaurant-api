<?php

/**
 * CodeIgniter 4.5+ Front Controller
 */

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Require Composer autoload
require_once FCPATH . '../vendor/autoload.php';

// Get framework instance
$app = new \CodeIgniter\CodeIgniter(new \Config\App());
$app->run();
