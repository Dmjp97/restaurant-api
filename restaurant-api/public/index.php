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

if (isset($_GET['diag'])) {
	header('Content-Type: application/json');
	$loggerFile = FCPATH . '../app/Config/Logger.php';
	$userAgentsFile = FCPATH . '../app/Config/UserAgents.php';
	$exceptionsFile = FCPATH . '../app/Config/Exceptions.php';
	$autoloadFile = FCPATH . '../app/Config/Autoload.php';

	if (is_file($loggerFile)) {
		require_once $loggerFile;
	}
	if (is_file($userAgentsFile)) {
		require_once $userAgentsFile;
	}
	if (is_file($exceptionsFile)) {
		require_once $exceptionsFile;
	}

	echo json_encode([
		'logger_file' => is_file($loggerFile),
		'useragents_file' => is_file($userAgentsFile),
		'exceptions_file' => is_file($exceptionsFile),
		'autoload_file' => is_file($autoloadFile),
		'logger_class' => class_exists('Config\\Logger'),
		'useragents_class' => class_exists('Config\\UserAgents'),
		'exceptions_class' => class_exists('Config\\Exceptions'),
	], JSON_PRETTY_PRINT);

	exit;
}

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

if (! defined('CodeIgniter\\ENVIRONMENT')) {
	define('CodeIgniter\\ENVIRONMENT', $ciEnv);
}

if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
	chdir(FCPATH);
}

require FCPATH . '../app/Config/Paths.php';

$paths = new Paths();

require rtrim($paths->systemDirectory, '\\/') . DIRECTORY_SEPARATOR . 'Boot.php';

exit(Boot::bootWeb($paths));
