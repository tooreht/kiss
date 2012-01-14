<?php

class Router
{
	private $path, $controller, $action, $query;
	//static $instance;

	public function __construct()
	{
		/*
		 * TODO santizise request
		 */
		
		$this->path = SITE_ROOT;
		$request = $_GET['request'];
var_dump($request);
		$split = explode('/',trim($request,'/'));
		$tmp = array_shift($split);
		$this->controller = !empty($tmp) ? ucfirst($tmp) : 'Default';
		$tmp = array_shift($split);
		$this->action = !empty($tmp) ? $tmp : 'index';
		$this->query = $split;
print_r($split);
	}
	
	public function __get($property){
		return $this->$property;
	}

	public function route()
	{
echo "\n start routing \n";
		$file = ROOT.DS.'app'.DS.'controllers'.DS.$this->controller.'Controller.php';
		if(is_readable($file))
		{
			include $file;
			$class = $this->controller.'Controller';
		}
		else
		{
			include ROOT.DS.'app'.DS.'controllers'.DS.'Error404Controller.php';
			$class = 'Error404Controller';
		}
		$controller = new $class($this);

		if (is_callable(array($controller, $this->action)))
			$action = $this->action;
		else
			$action = 'index';
		$controller->beforeAction($this->query);
echo $this->controller.'->'.$action.'('.implode(',',$this->query).')'."\n";
		$controller->$action($this->query);
		$controller->afterAction($this->query);
	}
	
	public function call($controller, $action, $query)
	{
		return call_user_func_array(array($controller, $action), $query);
	}
}
