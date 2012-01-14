<?php

abstract class BaseController
{
	protected $router;
	protected $template;
	
	protected $model;
	public $wholePage;

	function __construct($router)
	{
		$this->router = $router;

		$model = $this->router->controller;
		$class = $model.'Model'; // think about
		$table = $model;
		$this->$model = new $class();//ucfirst($controller);
		$this->template = new Template();
		$this->showWholePage = TRUE;
	}
	
	public function __destruct(){}
	
	abstract function beforeAction();
	
	abstract function index();
	
	abstract function afterAction();
}