<?php

defined('APP_NAMESPACE') || define('APP_NAMESPACE', 'App');

defined('COMPOSER_PATH') || define('COMPOSER_PATH', ROOTPATH . 'vendor/autoload.php');
defined('CodeIgniter\\COMPOSER_PATH') || define('CodeIgniter\\COMPOSER_PATH', COMPOSER_PATH);

defined('SECOND') || define('SECOND', 1);
defined('MINUTE') || define('MINUTE', 60);
defined('HOUR')   || define('HOUR', 3600);
defined('DAY')    || define('DAY', 86400);
defined('WEEK')   || define('WEEK', 604800);
defined('MONTH')  || define('MONTH', 2_592_000);
defined('YEAR')   || define('YEAR', 31_536_000);
defined('DECADE') || define('DECADE', 315_360_000);

defined('EXIT_SUCCESS')        || define('EXIT_SUCCESS', 0);
defined('EXIT_ERROR')          || define('EXIT_ERROR', 1);
defined('EXIT_CONFIG')         || define('EXIT_CONFIG', 3);
defined('EXIT_UNKNOWN_FILE')   || define('EXIT_UNKNOWN_FILE', 4);
defined('EXIT_UNKNOWN_CLASS')  || define('EXIT_UNKNOWN_CLASS', 5);
defined('EXIT_UNKNOWN_METHOD') || define('EXIT_UNKNOWN_METHOD', 6);
defined('EXIT_USER_INPUT')     || define('EXIT_USER_INPUT', 7);
defined('EXIT_DATABASE')       || define('EXIT_DATABASE', 8);
defined('EXIT__AUTO_MIN')      || define('EXIT__AUTO_MIN', 9);
defined('EXIT__AUTO_MAX')      || define('EXIT__AUTO_MAX', 125);

defined('CodeIgniter\\EXIT_SUCCESS')        || define('CodeIgniter\\EXIT_SUCCESS', EXIT_SUCCESS);
defined('CodeIgniter\\EXIT_ERROR')          || define('CodeIgniter\\EXIT_ERROR', EXIT_ERROR);
defined('CodeIgniter\\EXIT_CONFIG')         || define('CodeIgniter\\EXIT_CONFIG', EXIT_CONFIG);
defined('CodeIgniter\\EXIT_UNKNOWN_FILE')   || define('CodeIgniter\\EXIT_UNKNOWN_FILE', EXIT_UNKNOWN_FILE);
defined('CodeIgniter\\EXIT_UNKNOWN_CLASS')  || define('CodeIgniter\\EXIT_UNKNOWN_CLASS', EXIT_UNKNOWN_CLASS);
defined('CodeIgniter\\EXIT_UNKNOWN_METHOD') || define('CodeIgniter\\EXIT_UNKNOWN_METHOD', EXIT_UNKNOWN_METHOD);
defined('CodeIgniter\\EXIT_USER_INPUT')     || define('CodeIgniter\\EXIT_USER_INPUT', EXIT_USER_INPUT);
defined('CodeIgniter\\EXIT_DATABASE')       || define('CodeIgniter\\EXIT_DATABASE', EXIT_DATABASE);
defined('CodeIgniter\\EXIT__AUTO_MIN')      || define('CodeIgniter\\EXIT__AUTO_MIN', EXIT__AUTO_MIN);
defined('CodeIgniter\\EXIT__AUTO_MAX')      || define('CodeIgniter\\EXIT__AUTO_MAX', EXIT__AUTO_MAX);
