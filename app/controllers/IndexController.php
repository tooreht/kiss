<?php

class IndexController extends BaseController
{
	public function index()
	{
		$this->registry->template->test = $this->getModelData();
		$this->registry->template->show('indexView.php');
	}

	private function getModelData()
	{
		$model = TestModel::getInstance();
		return $model->getData();
	}
}