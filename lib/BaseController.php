<?php

abstract class BaseController
{
	protected $registry;
	
	protected $model;
	protected $template;
	public $showWholePage;

	function __construct($registry)
	{
		require_once(ROOT.DS.'lib'.DS.'BaseModel.php');
		$this->registry = $registry;
		$model = $this->registry->modelName;
		$this->model = new $model($this->registry);//ucfirst($controller);
	}

	abstract function index();
	
	abstract function beforeAction();
	
	abstract function afterAction(); 
}