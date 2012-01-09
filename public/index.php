<?php
	
	define('DS', DIRECTORY_SEPARATOR);
	define('ROOT', dirname(dirname(__FILE__)));
	
	require_once(ROOT.DS.'config'.DS.'config.php');
	require_once(ROOT.DS.'config'.DS.'helper.php');
	setReporting();
	
	require_once(ROOT.DS.'lib'.DS.'Router.php');
	require_once(ROOT.DS.'lib'.DS.'Registry.php');
	require_once(ROOT.DS.'lib'.DS.'Template.php');

	$router = new Router();
	$registry = new Registry();
	$registry->template = new Template();

	$router->route($registry);

	/*** auto load model classes ***/
	function __autoload($class_name)
	{
		try
		{
			$filename = $class_name . '.php';
			$file = ROOT.DS.'app'.DS.'models'.DS.$filename;

			if (file_exists($file))
				include ($file);
			else
				throw new Exception('model ' . $class_name . '.php not found');
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
			exit(0);
		}
	}