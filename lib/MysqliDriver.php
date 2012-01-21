<?php

/**
 * The MySQL driver extends the DatabaseLibrary to provide 
 * interaction with a MySQL database
 */

final class MysqliDriver implements DatabaseLibrary
{	
    /**
     * Connection holds MySQLi resource
     */
    private $connection;

    /**
     * Query to perform
     */
    private $query;

    /**
     * Result holds data retrieved from server
     */
    private $result;
	
	/**
	 * Ressource that holds the prepeared statement 
	 */
	private $stmt;

	/**
	 * result information
	 */
	public $numRows;
	public $numFields;
	public $affectedRows;
	
	/**
	 * Singleton instance
	 */
	private static $instance;

	/**
	 * overwrite constructor
	 */
	public function __construct()
	{
		$this->connect();
		$this->stmt = $this->connection->stmt_init();
	}
	
	public function __destruct(){
		//$this->disconnect();
	}
	
	/** prevent cloning */
	private function __clone(){}

	/**
	 * $instance can only be instantiated once
	 */
	public static function getInstance()
	{
		if(self::$instance === NULL)
			self::$instance = new self();
		return self::$instance;
	}

    /**
     * Create new connection to database
     */ 
    public function connect()
    {	  
	
//print "Before........".var_dump($this->connection);
        //create new mysqli connection
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME , DB_PORT, DB_SOCKET);
//print "After........".var_dump($this->connection);
		//mysqli_connect_errno()
		if($this->connection)
		{
			if($this->connection->set_charset('utf8'))
			{
				return TRUE;
			}
			else
			{
				printf("Error loading character set utf8: %s\n", $this->connection->error);
			}
		}
		printf("Connect failed: %s\n", mysqli_connect_error());
        return FALSE;
    }

    /**
     * Break connection to database
     */
    public function disconnect()
    {
        //clean up connection!
		if($this->connection)
			$this->connection->close();
			
        return TRUE;
    }
	
		
	// Debug
	public function getQuery()
	{
		return $this->query;
	}

	/**
     * Execute a normal query
     */
	public function query($query)
	{
		if (!empty($query))
		{
			try
			{
print 'query: '.$query;
				$this->result = $this->connection->query($query);
				if($this->result)
				{
//echo 'Result: '.var_dump($this->result)."end\n";
				//if( is_object($this->result) )
					if( is_object($this->result) )
						$this->getResultProperties();	
					return TRUE;
				}
				else
				{
					throw new Exception('MySQL error: '.$this->connection->error);
					return FALSE;
				}
			}
			catch(Exception $e)
			{
				echo $e->getMessage();
				exit(0);
			}
        }
        return FALSE;
	} 
    
	
	private function getResultProperties(){
		$this->numRows = $this->result->num_rows;
		$this->numFields = $this->result->field_count;
		$this->affectedRows = $this->connection->affected_rows;
	}

    /**
     * Fetch a row from the query result
     * 
     * @param $type
     */
    public function fetch($type = 'object')
    {
        if (isset($this->result))
        {
            switch ($type)
            {
                case 'array':
                    //fetch a row as array
                    $row = $this->result->fetch_array();
            
                break;
				
				case 'assoc':
                    //fetch a row as array
                    $row = $this->result->fetch_assoc();
            
                break;
				
				case 'row':
                    //fetch a row as array
                    $row = $this->result->fetch_row();
 //"Row".print_r($row);           
                break;
				
				case 'fields':
                    //fetch a field as array
                    $row = $this->result->fetch_fields();
            
                break;  
				
				case 'all':
					return $this->result->fetch_all();
            
                case 'object':
            
                //fall through...
            
                default:
                
                    //fetch a row as object
                    $row = $this->result->fetch_object();    
                    
                break;
            }
            return $row;
        }
    
        return FALSE;
    }
	
	public function escape($string)
	{
		//print "String to escape: ".$string."\n";
		return $this->connection->real_escape_string($string);
	}
	
	
	public function free()
	{
	//print_r($this->result);
		if($this->numRows !== 0)
		{
			$this->result->free();
			return TRUE;
		}
		return FALSE;
	}
	
	public function freeResult()
	{
	//print_r($this->result);
		if($this->numRows !== 0)
		{
			$this->result->free_result();
			return TRUE;
		}
		return FALSE;
	}



	/** prepared statements code, not mature for real use!!! */
	
	public function closeStmt()
	{
		if($this->stmt)
			$this->stmt->close();
	}
	
	/**
	* This method is needed for prepared statements. They require
	* the data type of the field to be bound with "i" s", etc.
	* This function takes the input, determines what type it is,
	* and then updates the param_type.
	*
	* @param mixed $item Input to determine the type.
	* @return string The joined parameter types.
	*/
	protected function determineType($item)
	{
		switch (gettype($item))
		{
		case 'string':
			return 's';
			break;
	
		case 'integer':
			return 'i';
			break;
	
		case 'blob':
			return 'b';
			break;
	
		case 'double':
			return 'd';
			break;
		}
	}

    /**
     * Prepare query to execute
     * 
     * @param $query
     */
    public function prepare($query)
    {
        //store query in query variable
        //$this->query = $query;
print $query."\n";
        if(! $this->stmt = $this->connection->prepare($query))
        {
        	trigger_error("Problem preparing query", E_USER_ERROR);
        	return TRUE;
		}
		return FALSE;
    }
    
	/** May only work on new php versions, use more time consuming bindResult method instead */
	public function getResult()
	{
		return $this->stmt->get_result();
	}
	
    public function bindParam()
	{
		$params = func_get_args();
var_export($params);
		
		$tmpArray = array();
		foreach ($params as $i => $value)
		{
			$tmpArray[$i] = &$params[$i];
		}
print_r($tmpArray);
		return call_user_func_array(array($this->stmt, 'bind_param'),$tmpArray);
	}
	
    public function bindResult()
	{
		$parameters = array();
		$results = array();
		
		$meta = $this->stmt->result_metadata();
		
		while($field = $meta->fetch_field())
		{
			$parameters[] = &$row[$field->name];
		}
		
		call_user_func_array(array($this->stmt, 'bind_result'),$parameters);

		while ($this->stmt->fetch())
		{
			$x = array();
			foreach ($row as $key => $val)
			{
				$x[$key] = $val;
			}
			$results[] = $x;
		}
		 
		$meta->free();
		
		return $results;
	}
	
    /**
     * Execute a prepared query
     */
    public function exec()
    {
        if (isset($this->stmt))
        {
			try
			{
				//execute prepared query
				$this->stmt->execute();
		
			}
			catch(Exception $e)
			{
				echo $e->getMessage();
				exit(0);
			}
			
            return TRUE;
        }
    
        return FALSE;        
    }
}
