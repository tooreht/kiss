<?php

class BaseModel
{
	protected $registry;
	
	protected $db;
	protected $limit;
	
	function __construct($registry)
	{
		$this->registry = $registry;
		$this->db = new MysqlImprovedDriver();
		$this->db->connect();
		$this->limit = PAGINATE_LIMIT;
		
		/*
		if (!isset($this->abstract)) {
			$this->_describe();
		}
		*/
	}
	
	function __destruct(){
		$this->db->disconnect();
	}
}
