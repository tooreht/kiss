<?php

class BaseModel extends SqlQuery
{
	
	protected $db;
	protected $result;
	
	protected $describe = array(); // not yet implemented
	
	protected $orderBy;
	protected $order;
	protected $extraConditions;
	protected $hO;
	protected $hM;
	protected $hMABTM;
	protected $page;
	protected $limit;
	
	public function __construct()
	{	
		$this->limit = PAGINATE_LIMIT;
		$this->db = MysqlDriver::getInstance();
		$this->db->connect();
	}
	
		
	public function __destruct(){
		//$this->db->disconnect();
	}
	
	/** Select Query **/

	public function modWhere($field, $value) {
		$this->extraConditions .= '`'.$this->model.'`.`'.$field.'` = \''.$this->db->escape($value).'\' AND ';
	}

	public function modLike($field, $value) {
		$this->extraConditions .= '`'.$this->model.'`.`'.$field.'` LIKE \'%'.$this->db->escape($value).'%\' AND ';
	}

	public function showHasOne() {
		$this->hO = 1;
	}

	public function showHasMany() {
		$this->hM = 1;
	}

	public function showHMABTM() {
		$this->hMABTM = 1;
	}

	public function setLimit($limit) {
		$this->limit = $limit;
	}

	public function setPage($page) {
		$this->page = $page;
	}

	public function orderBy($orderBy, $order = 'ASC') {
		$this->orderBy = $orderBy;
		$this->order = $order;
	}
	
	
	public function search()
	{
		$from = $from = '`'.$this->table.'` AS `'.$this->model.'` ';
		$conditions = "'1'='1' AND ";
		$conditionsChild = '';
		$fromChild = '';
		
		if($this->hO == 1 && isset($this->hasOne))
		{
			foreach($this->hasOne as $alias => $model)
			{
				$from .= 'LEFT JOIN `'.$model.'` AS `'.$alias.'` ';
				$from .= 'ON `'.$this->model.'`.`'.$alias.'_id` = `'.$alias.'`.`id` ';
			}
		}
		
echo "\nhO= ".$from."\n";
		
		if($this->id)
		{
			$conditions .= '`'.$this->model."`.`id` = '".$this->db->escape($this->id)."' AND ";
		}
		
		if ($this->extraConditions)
		{
			$conditions .= $this->extraConditions;
		}
		
		$conditions = substr($conditions,0,-4);
		
		if (isset($this->orderBy))
		{
			$conditions .= ' ORDER BY `'.$this->model.'`.`'.$this->orderBy.'` '.$this->order;
		}
		
		if (isset($this->page)) {
			$offset = ($this->page-1)*$this->_limit;
			$conditions .= ' LIMIT '.$this->_limit.' OFFSET '.$offset;
		}

echo 'conditions= '.$conditions."\n";
		
		$this->db->prepare('SELECT * FROM '.$from.' WHERE '.$conditions);
print($this->db->getQuery());
		$this->db->query();
		$result = array();
		$table = array();
		$field = array();
		$tmpResults = array();
		$numFields = $this->db->numFields;
		// fetched fields
		$fields = $this->db->fetch('fields');
		
		for($i = 0; $i < $numFields; $i++)
		{
			array_push($table, $fields[$i]->table);
			array_push($field, $fields[$i]->name);
		}

		if($this->db->numRows > 0)
		{
			while ($row = $this->db->fetch('row'))
			{
//var_dump($row);
				for ($i = 0; $i < $numFields; ++$i)
				{
					$tempResults[$table[$i]][$field[$i]] = $row[$i];
				}
//print_r($tempResults);
	
//print_r($this->hasMany);
				if ($this->hM == 1 && isset($this->hasMany))
				{
		
					foreach ($this->hasMany as $aliasChild => $modelChild)
					{
//print 'Alias Child:'.$aliasChild.' ModelChild:'.$modelChild;
						$queryChild = '';
						$conditionsChild = '';
						$fromChild = '';

						$tableChild = $modelChild;
						
						$fromChild .= '`'.$tableChild.'` AS `'.$aliasChild.'`';
						
						$conditionsChild .= '`'.$aliasChild.'`.`'.strtolower($this->model)."_id` = '".$tempResults[$this->model]['id']."'";
	
						$queryChild =  'SELECT * FROM '.$fromChild.' WHERE '.$conditionsChild;
						$this->db->prepare($queryChild);
echo "\n<!--".$this->db->getQuery()."-->\n";
						$resultChild = $this->db->query();
				
						$tableChild = array();
						$fieldChild = array();
						$tempResultsChild = array();
						$resultsChild = array();
					
						if ($this->db->numRows > 0) {
							$numFieldsChild = $this->db->numFields;
							$fields = $this->db->fetch('fields');
							for ($j = 0; $j < $numFieldsChild; ++$j) {
								array_push($tableChild, $fields[$j]->table);
								array_push($fieldChild, $fields[$j]->name);
							}
//print_r($fields);
//print_r($table);

							while ($rowChild = $this->db->fetch('row'))
							{
								for ($j = 0; $j < $numFieldsChild; ++$j)
								{
									$tempResultsChild[$tableChild[$j]][$fieldChild[$j]] = $rowChild[$j];
								}
								array_push($resultsChild,$tempResultsChild);
							}
						}
				
						$tempResults[$aliasChild] = $resultsChild;
//print_r($tempResults);						
						//$this->db->free();
					}
					
				}


				if ($this->hMABTM == 1 && isset($this->hasManyAndBelongsToMany))
				{
					foreach ($this->hasManyAndBelongsToMany as $aliasChild => $tableChild)
					{
						$queryChild = '';
						$conditionsChild = '';
						$fromChild = '';

						$sortTables = array($this->table,$pluralAliasChild);
						sort($sortTables);
						$joinTable = implode('_',$sortTables);

						$fromChild .= '`'.$tableChild.'` as `'.$aliasChild.'`,';
						$fromChild .= '`'.$joinTable.'`,';
						
						$conditionsChild .= '`'.$joinTable.'`.`'.$singularAliasChild.'_id` = `'.$aliasChild.'`.`id` AND ';
						$conditionsChild .= '`'.$joinTable.'`.`'.strtolower($this->model).'_id` = \''.$tempResults[$this->model]['id'].'\'';
						$fromChild = substr($fromChild,0,-1);

						$queryChild =  'SELECT * FROM '.$fromChild.' WHERE '.$conditionsChild;	
echo '<!--'.$this->db->getQuery().'-->';
						$resultChild = $this->db->query($queryChild);
				
						$tableChild = array();
						$fieldChild = array();
						$tempResultsChild = array();
						$resultsChild = array();
						
						if ($this->db->numRows > 0)
						{
							$numFieldsChild = $this->db->numFields;
							for ($j = 0; $j < $numFieldsChild; ++$j) {
								array_push($table, $fields[$j]->table);
								array_push($field, $fields[$j]->name);
							}

							while ($rowChild = $this->db->fetch('row'))
							{
								for ($j = 0;$j < $numFieldsChild; ++$j) {
									$tempResultsChild[$tableChild[$j]][$fieldChild[$j]] = $rowChild[$j];
								}
								array_push($resultsChild,$tempResultsChild);
							}
						}
						
						$tempResults[$aliasChild] = $resultsChild;
						$this->db->free(); // Check if this causes warnings
					}
				}

				array_push($result,$tempResults);
			}
			
			

			if ($this->db->numRows === 1 && $this->id != null)
			{
				$this->db->free();
				$this->clear();
				return $result[0];
			} 
			else
			{
				$this->db->free();
				$this->clear();
				return $result;
			}
		} else
		{
			$this->db->free();
			$this->clear();
			return $result;
		}
	}
	
	/** Clear All Variables **/

	function clear() {
		foreach($this->describe as $field) {
			$this->$field = null;
		}

		$this->orderBy = null;
		$this->order = null;
		$this->extraConditions = null;
		$this->hO = null;
		$this->hM = null;
		$this->hMABTM = null;
		$this->page = null;
	}
	
	    /** Describes a Table **/

	protected function describe() {
		$cache = new Cache();

		$this->describe = $cache->get('describe'.$this->table);

		if (!$this->describe) {
			$this->describe = array();
			$query = 'DESCRIBE '.$this->table;
			$this->db->prepare($query);
			while ($row = $this->db->fetch('row')) {
				 array_push($this->describe,$row[0]);
			}

			$this->db->freeResult();
			$cache->set('describe'.$this->table,$this->describe);
		}

		foreach ($this->describe as $field) {
			$this->$field = null;
		}
	}
	
	    /** Saves an Object i.e. Updates/Inserts Query **/

	function save() {
		$query = '';
		if (isset($this->id)) {
			$updates = '';
			foreach ($this->describe as $field) {
				if ($this->$field) {
					$updates .= '`'.$field."` = '".$this->db->escape($this->$field)."',";
				}
			}

			$updates = substr($updates,0,-1);

			$query = 'UPDATE '.$this->table.' SET '.$updates." WHERE `id`='".$this->db->escape($this->id)."'";			
		} else {
			$fields = '';
			$values = '';
			foreach ($this->describe as $field) {
				if ($this->$field) {
					$fields .= '`'.$field.'`,';
					$values .= "'".mysql_real_escape_string($this->$field)."',";
				}
			}
			$values = substr($values,0,-1);
			$fields = substr($fields,0,-1);

			$query = 'INSERT INTO '.$this->table.' ('.$fields.') VALUES ('.$values.')';
		}
print $query."\n";
		$this->db->prepare($query);
		$this->clear();
		if (!$this->db->query()) {
            /** Error Generation **/
			return FALSE;
        }
		return TRUE;
	}
}