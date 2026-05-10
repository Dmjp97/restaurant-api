<?php

/**
 * CodeIgniter front controller.
 */

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

require FCPATH . '../app/Config/Paths.php';

$paths = new Config\Paths();

require $paths->systemDirectory . '/bootstrap.php';
