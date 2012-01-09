<?php

class TestModel extends BaseModel
{
	private $testData = 'data from the model';
	static $instance;

	public function getData()
	{
		//return $this->testData;
		$this->db->prepare('SELECT * FROM `categories`');
		$this->db->query();
		return $this->db->fetch('array');
		
	}
	
	
}