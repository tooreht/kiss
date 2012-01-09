<?php

class IndexController extends BaseController
{
	public function beforeAction(){
		echo 'beforeAction '.$this->registry->controllerName;
	}
	public function afterAction(){
		echo 'afterAction '.$this->registry->controllerName;
	}
		
	public function index()
	{
		$this->showWholePage = FALSE;
		$this->registry->template->test = $this->getModelData();
		$this->registry->template->show('indexView', $this->showWholePage);
	}

	private function getModelData()
	{
		return $this->model->getData();
	}
}