<?php

/**
 * A PHP session handler to keep session data within a MySQL database,
 * thus providing both better security and better performance.
 * 
 * The class also implements <i>session locking</i>. Session locking is a way to ensure that data is correctly handled
 * in a scenario with multiple concurrent AJAX requests. Read more about it in this excellent article by <b>Andy Bakun</b>
 * called {@link http://thwartedefforts.org/2006/11/11/race-conditions-with-ajax-and-php-sessions/ Race Conditions with Ajax and PHP Sessions}.
 *
 * The SessionHandler class is also a solution for applications that are scaled across multiple web servers (using a
 * load balancer or a round-robin DNS) and where the user's session data needs to be available. Storing sessions in a
 * database makes them available to all of the servers!
 * 
 * This class is was inspired by Stefan Gabos's code from {@link http://stefangabos.ro/php-libraries/zebra-session/} 
 * and John Herren's code from the {@link http://devzone.zend.com/node/view/id/141 Trick out
 * your session handler} article and Chris Shiflett's code from his book {@link http://phpsecurity.org/code/ch08-2
 * Essential PHP Security} chapter 8, Shared Hosting, Pg. 78-80.
 * 
 * @author Marc Zimmermann <tooreht@gmail.com>
 * @link https://tooreht.net/kiss
 */

class SessionHandler
{
	
	/**
	 * (Optional) The number of seconds after which a session will be considered
     * as <i>expired</i>.
	 * 
	 * Expired sessions are cleaned up from the database whenever the <i>garbage
     * collection routine</i> is run. The probability of the <i>garbage collection
     * routine</i> to be executed is given by the values of <i>$gc_probability</i>
     * and <i>$gc_divisor</i>. See below.
     *
     * Default is the value of <i>session.gc_maxlifetime</i> as set in in php.ini.
     * Read more at {@link http://www.php.net/manual/en/session.configuration.php}
     *
     * To clear any confusions that may arise: in reality, <i>session.gc_maxlifetime</i>
     * does not represent a session's lifetime but the number of seconds after
     * which a session is seen as <i>garbage</i> and is deleted by the <i>garbage
     * collection routine</i>. The PHP setting that sets a session's lifetime is
     * <i>session.cookie_lifetime</i> and is usually set to "0" - indicating that
     * a session is active until the browser/browser tab is closed. When this class
     * is used, a session is active until the browser/browser tab is closed and/or
     * a session has been inactive for more than the number of seconds specified
     * by <i>session.gc_maxlifetime</i>.
     *
     * To see the actual value of <i>session.gc_maxlifetime</i> for your
     * environment, use the {@link get_settings()} method.
     *
     * Do not set this property to keep default value.
	 * 
	 * @var integer
	 */
	protected $sessionLifetime = SESSION_LIFETIME;
	
	
	/**
	 * (Optional) Used in conjunction with <i>$gc_divisor</i>. It defines the
     * probability that the <i>garbage collection routine</i> is started.
	 * 
	 * The probability is expressed by the formula:
     *
     * <code>
     * $probability = $gc_probability / $gc_divisor;
     * </code>
	 *
	 * So, if <i>$gc_probability</i> is 1 and <i>$gc_divisor</i> is 100, it means
	 * that there is a 1% chance the the <i>garbage collection routine</i> will
	 * be called on each request.
	 * 
	 * Default is the value of <i>session.gc_probability</i> as set in php.ini.
     * Read more at {@link http://www.php.net/manual/en/session.configuration.php}
     *
     * To see the actual value of <i>session.gc_probability</i> for your
     * environment, and the computed <i>probability</i>, use the
     * {@link get_settings()} method.
     *
     * Do not set this property to keep default value.
	 * 
	 * @var integer 
	 */
	protected $gcProbability = GC_PROBABILITY;
	
	
	/**
	 * (Optional) Used in conjunction with <i>$gc_probability</i>. It defines the
     * probability that the <i>garbage collection routine</i> is started.
     *
     * The probability is expressed by the formula:
     *
     * <code>
     * $probability = $gc_probability / $gc_divisor;
     * </code>
     *
     * So, if <i>$gc_probability</i> is 1 and <i>$gc_divisor</i> is 100, it means
     * that there is a 1% chance the the <i>garbage collection routine</i> will
     * be called on each request.
     *
     * Default is the value of <i>session.gc_divisor</i> as set in php.ini.
     * Read more at {@link http://www.php.net/manual/en/session.configuration.php}
     *
     * To see the actual value of <i>session.gc_divisor</i> for your
     * environment, and the computed <i>probability</i>, use the
     * {@link get_settings()} method.
     *
     * Do not set this property to keep default value.
	 * @var integer
	 */
	protected $gcDivisor = GC_DIVISOR;
	
	
	/** 
	 * (Optional) The value of this argument is appended to the HTTP_USER_AGENT
     * string before creating an MD5 hash out of it and storing it in the database.
     * This way we'll try to prevent HTTP_USER_AGENT spoofing.
     *
     * <i>Make sure you change this code to something else!</i>
     *
     * Default is <i>sEcUr1tY_c0dE</i>
	 * 
	 * @var string
	 */
	protected $securityCode = SECURITY_CODE;
	
	
	/** 
	 * (Optional) The maximum amount of time (in seconds) for which a lock on
     * the session data can be kept.
     *
     * <i>This must be lower than the maximum execution time of the script!</i>
     * Session locking is a way to ensure that data is correctly handled in a
     * scenario with multiple concurrent AJAX requests.
     *
     * Read more about it at
     * {@link http://thwartedefforts.org/2006/11/11/race-conditions-with-ajax-and-php-sessions/}
     *
     * Default is <i>60</i>
	 * @var string
	 */
	protected $lockTimeout = LOCK_TIMEOUT;
	
	
	/**
	 * lock name, associated with the current session
	 * @var string
	 */
    protected $sessionLock;
	
	
   	/**
	 * Name of the MySQL table used by the class
	 * @var resource
	 */
    protected $db;
	
	
	/**
	 * the name of the DB table which handles the sessions
	 * @var string
	 */
	protected $table = SESSION_TABLE;
	
	
	/**
	 * Instantiate the MysqlDriver for a connection
	 */
	public function __construct(){
		$this->db = MysqliDriver::getInstance();
		$this->db->connect();
		
		/**
		 * make sure session cookies never expire so that session lifetime
		 * will depend only on the value of $session_lifetime
		 */ 
		ini_set('session.cookie_lifetime', 0);
		
		if($this->sessionLifetime !== 'default' && is_integer($this->sessionLifetime)) {
			ini_set('session.gc_maxlifetime', $this->sessionLifetime);
		} else {
			$this->sessionLifetime = ini_get('session.gc_maxlifetime');
		}
		
		if($this->gcProbability !== 'default' && is_integer($this->gcProbability)) {
			ini_set('session.gc_probability', $this->gcProbability);
		} else {
			$this->gcProbability = ini_get('session.gc_probability');
		}
		
		if($this->gcDivisor !== 'default' && is_integer($this->gcDivisor)){
			ini_set('session.gc_divisor', $this->gcDivisor);
		} else {
			$this->gcDivisor = ini_get('session.gc_divisor');
		}
		
		$this->sessionLifetime = ini_get('session.gc_maxlifetime');
	
		session_set_save_handler(
			array($this, 'open'),
			array($this, 'close'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'destroy'),
			array($this, 'gc')
		);
		
		session_start();
	}
	

	/**
	 * Open the session
	 * @return bool
	 */
    public function open($savePath, $sessionName)
    {
		return TRUE;
    }
	
	
	/**
	 * Close the session
	 * @return bool
	 */
    public function close()
    {
    	// release the lock associated with the current session
		return $this->db->query('SELECT RELEASE_LOCK("'.$this->sessionLock.'")');
    }
	
	
	/**
	 * Read the session
	 * @param int session id
	 * @return string data of the session bool false on if there is no entry
	 */
	public function read($id)
	{
		// get the lock name, associated with the current session
		$this->sessionLock = $this->db->escape('session_' . $id);
		
		// try to obtain a lock with the given name and timeout
		$this->db->query('SELECT GET_LOCK("'.$this->sessionLock.'", '.$this->db->escape($this->lockTimeout).')');
		
		$sql = 'SELECT `session_data` FROM `'.$this->table.'` WHERE `session_id` = '."'".$this->db->escape($id)."' AND "; 
		$sql .= '`session_expire` > '.time().' AND `http_user_agent` = ';
		$sql .= '"'.$this->db->escape(md5((isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '').$this->securityCode)).'" LIMIT 1';

		if($this->db->query($sql))
		{
			if($this->db->numRows && $this->db->numRows > 0)
			{
				$record = $this->db->fetch('assoc');
				return $record['session_data'];	
			}
		}
		return '';
	}
	

	/**
	 * Write the session
	 * @param int session id
	 * @param string data of the session
	 */
	public function write($id, $data)
	{
		$id = $this->db->escape($id);
		$agent = md5((isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '') . $this->securityCode);
		$data = $this->db->escape($data);
		$expire = $this->db->escape(time() + $this->sessionLifetime);
		
		$sql = 'INSERT INTO `'.$this->table.'` ';
		$sql .= '(session_id,http_user_agent,session_data,session_expire)';
		$sql .= ' VALUES("'.$id.'","'.$agent.'","'.$data.'","'.$expire.'") ';
		$sql .= 'ON DUPLICATE KEY UPDATE ';
		$sql .= '`session_data` = "'.$data.'",';
		$sql .= '`session_expire` = "'.$expire.'"'; 
		
		if($this->db->query($sql) && $this->db->affectedRows !== -1)
			return TRUE;
		return FALSE;
	}
	

	/**
	 * Destoroy the session
	 * @param int session id
	 * @return bool
	 */
	public function destroy($id)
	{
		$sql = 'DELETE FROM `'.$this->table.'` WHERE session_id = \''.$this->db->escape($id).'\'';
		if($this->db->query($sql) && $this->db->affectedRows !== -1)
			return TRUE;
		return FALSE;
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
    public function gc($maxLivetime)
    {
    	$expired = $this->db->escape( (time() - $maxLivetime) );
        $sql = 'DELETE FROM `'.$this->table.'` WHERE `session_expire` < '.$expired ;
        return $this->db->query($sql);
    }
	
    public function getActiveSessions()
    {
        // call the garbage collector
        $this->gc($this->sessionLifetime);

        // counts the rows from the database
        $sql = 'SELECT COUNT(`session_id`) AS count FROM `'.$this->table.'`';
		$this->db->query($sql);
		$result = $this->db->fetch('assoc');
		// return the number of found rows
		return $result['count'];
    }
	
	public function getSettings()
	{
		return array
		(
			'session.gc_maxlifetime'    =>  $this->sessionLifetime . ' seconds (' . round($this->sessionLifetime / 60) . ' minutes)',
            'session.gc_probability'    =>  $this->gcProbability,
            'session.gc_divisor'        =>  $this->gcDivisor,
            'probability'               =>  $this->gcProbability / $this->gcDivisor * 100 . '%',
            'lock_timeout'				=> 	$this->lockTimeout,
            'session_table'				=>	$this->table
		);
	}
	/**
	 * Regenerates the session id.
     *
     * <b>Call this method whenever you do a privilege change in order to prevent session hijacking!</b>
	 */
	public function regenerateId(){
		// saves the old session's id
        $oldSessionId = session_id();
		
		/* 
		 * regenerates the id
         * this function will create a new session, with a new id and containing the data from the old session
         * but will not delete the old session
		 */
        session_regenerate_id();

        // because the session_regenerate_id() function does not delete the old session,
        // we have to delete it manually
        $this->destroy($oldSessionId);
	}
	
	
	/**
     * Deletes all data related to the session 
	 */
	public function stop()
    {
        $this->regenerateId();

        session_unset();

        session_destroy();
    }
}
