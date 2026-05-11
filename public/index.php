<?php

/*
 * CodeIgniter 4 front controller.
 *
 * Keep this file in public/ so Apache can expose only public assets while
 * the framework, app, tests and writable directories remain outside web root.
 */

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

require FCPATH . '../app/Config/Paths.php';

$paths = new Config\Paths();

require $paths->systemDirectory . '/Boot.php';

exit(CodeIgniter\Boot::bootWeb($paths));
