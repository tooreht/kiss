<?php

abstract class BaseController
{
	protected $router;
	protected $template;
	
	protected $model;
	protected $isAjax;
	public $wholePage;

	function __construct($router)
	{
		$this->router = $router;

		$model = $this->router->controller;
		$class = $model.'Model'; // think about
		$table = $model;
		$this->$model = new $class();//ucfirst($controller);
		$this->template = new Template();
		$this->checkAjax();
	}
	
	public function __destruct(){}
	
	abstract function beforeAction();
	
	abstract function index();
	
	abstract function afterAction();
	
	/**
	 * checks if it's an ajax request
	 */
	protected function checkAjax()
	{
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
			!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
			strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
		{
			$this->isAjax = TRUE;
			$this->wholePage = FALSE;
			return TRUE;
		} 
		else
		{
			$this->isAjax = FALSE;
			$this->wholePage = TRUE;
			return FALSE;
		}
	}
}