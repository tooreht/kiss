<?php

class SqlQuery
{
	protected $errors = 0;
	protected $query;
	
	/** METHOD CHAINLOADING **
	 *
	 * Because the object is returned after each call of a chainloding method
	 * (return $this;), a nice and clearly arranged syntax can be used.
	 *
	 * the following functions are all representing a mysql statement
	 *
	 *
	 *
	 * mysql 'SELECT' statement
	 *
	 * @param $what selection from a table
	 *
	 * @return $this
	 */
	public function select($what)
	{
		if(empty($what))
			$this->errors++;
		$this->query .= 'SELECT '. $what;
		return $this;
	}

	/**
	 * mysql 'FROM' statement
	 *
	 * @param $table mysql table
	 *
	 * @return $this
	 */
	public function from($table)
	{
		if(empty($table))
			$this->errors++;
		$this->query .= ' FROM ' . $table;
		return $this;
	}

	/**
	 * mysql 'WHERE' statement
	 *
	 * @param $condition where condition(s)
	 *
	 * @return $this
	 */
	public function where($condition)
	{
		if(empty($condition))
			$this->errors++;
		$this->query .= ' WHERE ' .$condition;
		return $this;
	}

	/**
	 * mysql 'ORDER BY' statement
	 *
	 * @param $what attribute(s)
	 *
	 * @return $this
	 */
	public function order_by($what)
	{
		if(empty($what))
			$this->errors++;
		$this->query .= ' ORDER BY ' . $what;
		return $this;
	}

	/**
	 * mysql 'LIMIT' statement
	 *
	 * @param $start startpoint
	 * @param $end endpoint
	 *
	 * @return $this
	 */
	public function limit($start, $end)
	{
		if(empty($start) || empty($end))
			$this->errors++;
		$this->query .= ' LIMIT ' . $start .' ,  ' . $end;
		return $this;
	} 

	/**
	 * mysql 'JOIN' statement
	 *
	 * @param $table mysql table
	 * @param $condition on condition(s)
	 * @param $which join specification (optional)
	 *
	 * @return $this
	 */
	public function join($table, $condition, $which = '')
	{
		if(empty($table) || empty($condition))
			$this->errors++;
		$this->query .= $which . ' JOIN ' . $table . ' ON ' . $condition;
		return $this;
	}

	/**
	 * mysql 'INSERT' statement
	 *
	 * @param $table mysql table
	 * @param $ins Array with this structure:
	 *		array(
	 *			'column' => value,
	 *			'foo' => 'bar'
	 *		);
	 *
	 * @return $this
	 */
	public function insert($table, $ins)
	{
		foreach($ins as $key => $value){
			if($value !== NULL && $key !== NULL){
				$value = $this->db->escape($value);
				if(!is_numeric($value))
					$value = "'".$value."'";
				$columns[] = $key;
				$values[] = $value;
			} else {
				$this->errors++;
			}
		}
		$columns = implode(', ', $columns);
		$values = implode(', ', $values);
		if(empty($table))
			$this->errors++;
		$this->query .= 'INSERT INTO ' . $table . ' (' . $columns . ') VALUES(' . $values .')';
		return $this;
	}

	/** 
	 * mysql 'UPDATE' statement
	 *
	 * @param $table mysql table
	 * @param $set Array with this structure:
	 *		array(
	 *			'column' => value,
	 *			'foo' => 'bar'
	 *		);
	 *
	 * @return $this
	 */
	public function update($table, $set)
	{
		$tmp = array();
		foreach($set as $key => $value){
//print 'Key: '.$key.' Value: '.$value."\n";
			if($value !== null && $key !== null){
				$value = $this->db->escape($value);
				if(!is_numeric($value))
					$value = "'".$value."'";
				$tmp[] = $key.' = '.$value;
			} else {
				$this->errors++;
			}
		}
//print 'Errors: '.$this->errors;
		$tmp = implode(', ', $tmp);
		if(empty($table))
			$this->errors++;
		$this->query .= 'UPDATE '.$table.' SET '.$tmp;
		return $this;
	}

	/**
	 * mysql 'DELETE' statement
	 *
	 * @param $table mysql table
	 *
	 * @return $this
	 */
	public function delete($table)
	{
		$this->query = 'DELETE FROM '.$table;
		return $this;
	}
	
	public function query()
	{
		if($this->errors !== 0)
			return FALSE;
		$query = $this->query;
		$this->query = null;
		return $this->db->query($query);
	}
	
	public function sql()
	{
		if($this->errors !== 0)
			return FALSE;
		$query = $this->query;
		$this->query = NULL;
		return $query;
	}
	
}