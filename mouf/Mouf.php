<?php
define('ROOT_PATH', realpath(__DIR__.'/..').DIRECTORY_SEPARATOR);
require_once __DIR__.'/../config.php';
if (defined('ROOT_URL')) {
	define('MOUF_URL', ROOT_URL.'vendor/mouf/mouf/');
}
		
require_once __DIR__.'/../vendor/autoload.php';
		
require_once 'MoufComponents.php';
?>