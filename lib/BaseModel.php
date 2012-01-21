<?php

class BaseModel extends SqlQuery
{
	protected $db;
	protected $fields = array();
	protected $data = array();
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
		$this->db = MysqliDriver::getInstance();
		$this->db->connect();
		$this->initData();
	}
		
	public function __destruct(){
		//$this->db->disconnect();
	}
	
	protected function initData()
	{
		$ok = $this->select('*')
			->from("`$this->table`")
			->query();
		$fields = $this->db->fetch('fields');
		foreach($fields as $field)
		{
			$this->fields[] = $field->name;
		}
//print_r($this->fields);
	}
	
	protected function prepareData($data)
	{
		$tmp = array();
		foreach($data as $key => $value){
			if(empty($key) || empty($value)) continue;
			$value = $this->db->escape($value);
			if(!is_numeric($value))
				$value = "'".$value."'";
				$tmp[] = $key.' = '.$value;
		}
		return $tmp;
	}

	public function __get($key)
	{
		if(array_key_exists($key, $this->data))
			return $this->data[$key];
		return FALSE;
	}
	
	public function __set($key, $value)
	{
		if(in_array($key, $this->fields))
		{
//print 'Set: '.$key.' '.$value."\n";
			if(!mb_detect_encoding($value, 'UTF-8', TRUE))
				$value = utf8_encode($value);
			$this->data[$key] = $value;
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * This function saves a model in the database.
	 * It determines if the entry already exists, if yes 
	 * the data is updated, else a new entry is inserted.
	 *
	 * Special classes who inherit from model may override
	 * this function
	 *
	 * @return Integer $id on failure Boolean false
	 */
	public function save($id = NULL){
//print 'save start...........'.$id;
		$table = $this->table;
		$data = $this->data;
		if(empty($data))
			return FALSE;
		if($id === NULL && isset($this->id))
			$id = $this->id;
		if($id !== NULL && $this->pkExists($id)){
			$ok = $this
				->update($table, $data)
				->where('`id` = '.$id)
				->query();
			if($ok)
				return (int)$id;
			return FALSE;
		}
		$id = $this->findPk();
		if(is_numeric($id))
			$this->id = $id;
//var_dump($this->id);
		if(is_numeric($this->id)){
			$ok = $this
				->update($table, $data)
				->where('`id` = '.$this->id)
				->query();
			if($ok)
				return $this->id;
		} else {
			// remove id because it's empty
			$ok = $this->insert($table, $data)
				->query();
			if($ok)
				return $this->id = $this->findPk();
		}
		return FALSE;
	}
	
	
	/**
	 * This function checks if a certain entry exists in the database.
	 * If yes the primary key is returned, else Boolean false
	 *
	 * @param $table (optional) table to search
	 *
	 * @return mixed Integer primary key / Boolean false 
	 */
	public function findPk(){
		$table = $this->table;
		$data = $this->prepareData($this->data);		
		$cond = implode(' && ', $data);
		$ok = $this->select('`'.$table.'`.`id`')
			->from('`'.$table.'`')
			->where($cond)
			->query();
		$result = $this->db->fetch();
//var_dump($result);
		if($ok && is_object($result))
			return $result->id;
		return FALSE;		
	}
	
	/**
	 * This function checks if a certain primary key exists in a specific table
	 *
	 * @param $id primary key
	 * @param $table database table
	 *
	 * @return Boolean success
	 */
	public function pkExists($id, $table = NULL){
		if($table === NULL)
			$table = $this->table;
		if(is_numeric($id) && is_string($table)){
			$ok = $this->select('`'.$table.'`.`id`')
				->from('`'.$table.'`')
				->where("`$table`.`id` = ".$id)
				->query();
			$result = $this->db->fetch();
			if(!empty($result))
			return TRUE;
		}
		return FALSE;
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

	function saveCache() {
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

	public function getIdByAttr($attributes)
	{

		$sql = "SELECT `id` FROM `$this->table` WHERE $attr";
		$this->db->query($sql);
		$ids = array();
		while($row = $this->db->fetch('row'))
		{
			$ids[] = $row;
		}
		print_r($ids);
	}
	
	public function getIdsByColumns($columns)
	{
		foreach($columns as $key => $value){
			if($value !== null && $key !== null){
				$value = $this->db->escape($value);
				if(!is_numeric($value))
					$value = "'".$value."'";
				$cond[] = '`'.$key.'` = '.$value;
			}
		}
		$cond = implode(',', $cond);	
		$ok = $this->select('`id`')
			->from('`'.$this->table.'`')
			->where($cond)
			->query();
		if($ok)
		{
			$ids = array();
			while($row = $this->db->fetch()){
				$ids[] = $row->id;
			}
			if(count($ids) === 1)
				return $ids[0];
			else if(count($ids) > 1)
				return $ids;
		}
		return FALSE;
	}
}