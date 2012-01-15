<?php

/**
 * The MySQL driver extends the DatabaseLibrary to provide 
 * interaction with a MySQL database
 */

final class MysqlDriver implements DatabaseLibrary
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
		if($this->connection)
		{
			if($this->connection->set_charset('utf8'))
			{
				return TRUE;
			}
			else
			{
				printf("Error loading character set utf8: %s\n", $mysqli->error);
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

    /**
     * Prepare query to execute
     * 
     * @param $query
     */
    public function prepare($query)
    {
        //store query in query variable
        $this->query = $query;    
    
        return TRUE;
    }
	
	// Debug
	public function getQuery()
	{
		return $this->query;
	}

    /**
     * Execute a prepared query
     */
    public function query()
    {
        if (isset($this->query))
        {
			try
			{
print $this->query;
				//execute prepared query and store in result variable
				$this->result = $this->connection->query($this->query);
				if($this->result === FALSE)
				{
					throw new Exception('MySQL error: '.$this->connection->error);
					return FALSE;
				}
//echo 'Result: '.var_dump($this->result)."end\n";
				//if( is_object($this->result) )
				if( is_object($this->result) )
					$this->getResultProperties();		
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
}
