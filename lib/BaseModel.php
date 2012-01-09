<?php

class BaseModel
{
	protected $registry;
	
	protected $db;
	protected $limit;
	protected $table;
	
	function __construct($registry)
	{
		$this->registry = $registry;
		$this->db = MysqlImprovedDriver::getInstance();
		$this->db->connect();
		$this->limit = PAGINATE_LIMIT;
		$this->table = $this->registry->modelName;
		
		/*
		if (!isset($this->abstract)) {
			$this->_describe();
		}
		*/
	}
	
	function __destruct(){
		//$this->db->disconnect();
	}
}