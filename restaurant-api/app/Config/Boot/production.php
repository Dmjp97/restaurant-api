<?php

defined('CI_DEBUG') || define('CI_DEBUG', false);
defined('CodeIgniter\\CI_DEBUG') || define('CodeIgniter\\CI_DEBUG', CI_DEBUG);

error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', '0');
