<?php

class IndexController extends BaseController
{
	public function beforeAction(){}
	public function afterAction(){}
		
	public function index()
	{
		$this->registry->template->test = $this->getModelData();
		$this->registry->template->show('indexView');
	}

	private function getModelData()
	{
		$model = TestModel::getInstance();
		return $model->getData();
	}
}