<?php

class DefaultController extends BaseController
{
	public function beforeAction()
	{
		echo 'beforeAction '.__CLASS__;
	}
	
	public function afterAction()
	{
		echo 'afterAction '.__CLASS__;
	}
		
	public function index()
	{
		$this->wholePage = TRUE;
		$this->template->title = 'kiss | testpage';
		$this->template->test = "You're nuts!";
		$this->template->show('indexView', $this->wholePage);
	}
}