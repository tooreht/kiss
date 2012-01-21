<?php

	define('DS', DIRECTORY_SEPARATOR);
	define('ROOT', dirname(dirname(__FILE__)));
	
	require_once(ROOT.DS.'config'.DS.'config.php');
	require_once(ROOT.DS.'config'.DS.'helper.php');

	require_once(ROOT.DS.'lib'.DS.'Autoloader.php');
	
	/*** auto load classes ***/
	$autoloader = new Autoloader();
	
	$session = new SessionHandler();
	
	setReporting();
	
	print_r($session->getSettings());
	print 'Active Sessions: '.$session->getActiveSessions();
	//$session->stop();
	
	$_SESSION['test'] = 'How do you think about?';
	
	/** Let's go! */
	$router = new Router();
	$router->route(); 