<?php
	
	require_once('config/config.php/');
	require_once('config/helper.php/');
	require_once('app/Router.php');
	require_once('app/Registry.php');
	require_once('app/Template.php');

	$router = new Router();
	$registry = new Registry();
	$registry->template = new Template();

	$router->route($registry);

	/*** auto load model classes ***/
	function __autoload($class_name)
	{
		try
		{
			$filename = strtolower($class_name) . '.php';
			$file = 'app/models/' . $filename;

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