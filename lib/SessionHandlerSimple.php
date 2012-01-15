<?php

/**
 * A PHP session handler to keep session data within a MySQL database
 * 
 * @author Marc Zimmermann <tooreht@gmail.com>
 * @link https://tooreht.net/kiss
 */

class SessionHandler
{
	
	
   	/**
	 * a database MySQLi connection resource
	 * @var resource
	 */
    protected $db;
	
	
	/**
	 * the name of the DB table which handles the sessions
	 * @var string
	 */
	protected $table;
	
	
	/**
	 * Instantiate the MysqlDriver for a connection
	 */
	public function __construct(){
		$this->db = MysqlDriver::getInstance();
		$this->db->connect();
	}
	

	/**
	 * Set the DB table to store the Sessions
	 * @param string $dbTable 
	 */
	public function setTable($dbTable)
	{
		$this->table = $dbTable;
	}
	

	/**
	 * Open the session
	 * @return bool
	 */
    public function open()
    {
        //delete old session handlers
        $limit = time() - (3600 * 24);
        $sql = 'DELETE FROM `'.$this->table.'` WHERE timestamp < '.$limit;
		$this->db->prepare($sql);
        return $this->db->query();
    }
	
	
	/**
	 * Close the session
	 * @return bool
	 */
    public function close()
    {
        return $this->db->disconnect();
    }
	
	
	/**
	 * Read the session
	 * @param int session id
	 * @return string data of the session bool false on if there is no entry
	 */
	public function read($id)
	{
		$sql = 'SELECT `data` FROM `'.$this->table.'` WHERE `id` = '."'".$this->db->escape($id)."'";
		$this->db->prepare($sql);
		if($this->db->query())
		{
			if($this->db->numRows && $this->db->numRows > 0)
			{
				$record = $this->db->fetch('assoc');
				return $record['data'];	
			}
		}
		return FALSE;
	}
	

	/**
	 * Write the session
	 * @param int session id
	 * @param string data of the session
	 */
	public function write($id, $data)
	{
		$sql = 'REPLACE INTO `'.$this->table."` VALUES('".
			$this->db->escape($id)."','".
			$this->db->escape($data)."',".
			time().")";
		$this->db->prepare($sql);
		return $this->db->query();
	}
	

	/**
	 * Destoroy the session
	 * @param int session id
	 * @return bool
	 */
	public function destroy($id)
	{
		$sql = 'DELETE FROM `'.$this->table-'` WHERE id = '.$this->db->escape($id);
		$this->db->prepare($sql);
		return $this->db->query();
	}
	
	
	
	/**
	 * Garbage Collector
	 * @param int life time (sec.)
	 * @return bool
	 * @see session.gc_divisor 100
	 * @see session.gc_maxlifetime 1440
	 * @see session.gc_probability 1
	 * @usage execution rate 1/100
	 * (session.gc_probability/session.gc_divisor)
	 */
    public function gc($max)
    {
        $sql = 'DELETE FROM `'.$this->table.'` WHERE `timestamp` < '.( time() - intval($max) );
		$this->db->prepare($sql);
        return $this->db->query();
    }
}
