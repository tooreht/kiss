<?php

class Router
{
	private $path, $controller, $action, $query;
	static $instance;

	public function __construct()
	{
		/*
		 * TODO santizise request
		 */
		
		$this->path = SITE_ROOT;
		$request = $_GET['request'];
		$split = explode('/',trim($request,'/'));
		
		$tmp = array_shift($split);
		$this->controller = !empty($tmp) ? $tmp : 'Index';
		$tmp = array_shift($split);
		$this->action = !empty($tmp) ? $tmp : 'index';
		$this->query = $split;
	}

	public function route($registry)
	{
		$registry->controllerName = $this->controller.'Controller';
		$registry->modelName = $this->controller;
		
		require_once(ROOT.DS.'lib'.DS.'BaseController.php');
		$file = ROOT.DS.'app'.DS.'controllers'.DS.$this->controller.'Controller.php';
		if(is_readable($file))
		{
			include $file;
			$class = $this->controller . 'Controller';
		}
		else
		{
			include ROOT.DS.'app'.DS.'controllers'.DS.'Error404Controller.php';
			$class = 'Error404Controller';
		}
		$controller = new $class($registry);

		if (is_callable(array($controller, $this->action)))
			$action = $this->action;
		else
			$action = 'index';
		$controller->beforeAction($this->query);
		$controller->$action($this->query);
		$controller->afterAction($this->query);
	}
}
