<?php

use CodeIgniter\Boot;
use Config\Paths;

/*
 * ---------------------------------------------------------------
 * CHECK PHP VERSION
 * ---------------------------------------------------------------
 */

$minPhpVersion = '8.1';
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
	$message = sprintf(
		'Your PHP version must be %s or higher to run CodeIgniter. Current version: %s',
		$minPhpVersion,
		PHP_VERSION,
	);

	header('HTTP/1.1 503 Service Unavailable.', true, 503);
	echo $message;

	exit(1);
}

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

if (! defined('COMPOSER_PATH')) {
	define('COMPOSER_PATH', FCPATH . '../vendor/autoload.php');
}

if (! defined('CodeIgniter\\COMPOSER_PATH')) {
	define('CodeIgniter\\COMPOSER_PATH', COMPOSER_PATH);
}

$ciEnv = getenv('CI_ENVIRONMENT') ?: ($_SERVER['CI_ENVIRONMENT'] ?? 'production');
if (! in_array($ciEnv, ['production', 'development', 'testing'], true)) {
	$ciEnv = 'production';
}
putenv("CI_ENVIRONMENT={$ciEnv}");
$_ENV['CI_ENVIRONMENT'] = $ciEnv;
$_SERVER['CI_ENVIRONMENT'] = $ciEnv;

if (! defined('ENVIRONMENT')) {
	define('ENVIRONMENT', $ciEnv);
}

if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
	chdir(FCPATH);
}

require FCPATH . '../app/Config/Paths.php';

$paths = new Paths();

require rtrim($paths->systemDirectory, '\\/') . DIRECTORY_SEPARATOR . 'Boot.php';

exit(Boot::bootWeb($paths));
