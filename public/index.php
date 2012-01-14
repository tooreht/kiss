<?php
	
	define('DS', DIRECTORY_SEPARATOR);
	define('ROOT', dirname(dirname(__FILE__)));
	
	require_once(ROOT.DS.'config'.DS.'config.php');
	require_once(ROOT.DS.'config'.DS.'helper.php');
	setReporting();
	
	session_start();
	
	require_once(ROOT.DS.'lib'.DS.'Autoloader.php');
	
	/*** auto load classes ***/
	$autoloader = new Autoloader();
	
	/** Let's go! */
	$router = new Router();
	$router->route(); 