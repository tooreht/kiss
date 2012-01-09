<?php

/**
 * The MySQL Improved driver extends the DatabaseLibrary to provide 
 * interaction with a MySQL database
 */
final class MysqlImprovedDriver extends DatabaseLibrary
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
	 * Singleton instance
	 */
	static $instance;

	/**
	 * overwrite constructor
	 */
	private function __construct(){}
	
	private function __destruct(){
		$this->disconnect();
	}
	
	/** prevent cloning */
	private function __clone(){}

	/**
	 * $instance can only be instantiated once
	 */
	public static function getInstance()
	{
		if(self::$instance ==  null)
			self::$instance = new self();
		return self::$instance;
	}

    /**
     * Create new connection to database
     */ 
    public function connect()
    {	  
        //create new mysqli connection
        $this->connection = new mysqli
        (
            DB_HOST , DB_USER , DB_PASSWORD , DB_NAME , DB_PORT , DB_SOCKET
        );
    
        return TRUE;
    }

    /**
     * Break connection to database
     */
    public function disconnect()
    {
        //clean up connection!
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

    /**
     * Execute a prepared query
     */
    public function query()
    {
        if (isset($this->query))
        {
            //execute prepared query and store in result variable
            $this->result = $this->connection->query($this->query);
    
            return TRUE;
        }
    
        return FALSE;        
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
}
