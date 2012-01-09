<?php

abstract class BaseController
{
	protected $registry;
	
	protected $model;
	public $showWholePage;

	function __construct($registry)
	{
		$this->registry = $registry;
		$class = $this->registry->modelName;
		$this->model = new $class($this->registry);//ucfirst($controller);
		$this->showWholePage = TRUE;
	}
	
	abstract function beforeAction();
	
	abstract function index();
	
	abstract function afterAction();
}