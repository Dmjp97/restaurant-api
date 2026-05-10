<?php

/**
 * CodeIgniter front controller - v2
 */

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

require FCPATH . '../vendor/autoload.php';

$paths = new \Config\Paths();

require $paths->systemDirectory . '/bootstrap.php';
