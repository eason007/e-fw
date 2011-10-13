<?php
/**
 * @package DB
 * @subpackage Driver
 */

/**
 * @package DB
 * @subpackage Driver
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2011 eason007<eason007@163.com>
 * @version 1.3.2.20110428
 */

class DB_Mysql5 {
	/**
	 * Enter description here...
	 *
	 * @var object
	 * @see DB_Driver_PDO
	 * @access private
	 */
	private $db = NULL;
	
	private $_ConnectPond = NULL;
	
	private static $_dbHash = array();
	
	private $onTransact = FALSE;
	
	/**
	 * 查询SQL日志
	 *
	 * @var array
	 * @access public
	 */
	public $sqlBox = array();

	private function __construct ($dbParams) {
		$this->_ConnectPond = $dbParams;
	}
	
	function __destruct() {
		$this->db = null;
	}

	/**
	 * 连接数据库
	 * 
	 * 根据 dbType 的设定，调用不同的 driver 连接数据库
	 * 
	 * @return void
	 * @access private
	 */
	private function dbConnect () {
		switch ($this->_ConnectPond['dbType']) {
			case 'Mysql':
				$this->db = new DB_Driver_PDO(
					$this->_ConnectPond['dbServer'],
					$this->_ConnectPond['dbPort'],
					$this->_ConnectPond['dbName'],
					$this->_ConnectPond['dbUser'],
					$this->_ConnectPond['dbPassword']
				);

				break;
		}
	}
	
	public static function getInstance ($dbParams) {
		$hashTag = md5(strtolower($dbParams['dbServer'].
			$dbParams['dbPort'].
			$dbParams['dbName'].
			$dbParams['dbUser'].
			$dbParams['dbPassword']));
		
		if ( !array_key_exists($hashTag, self::$_dbHash) ) {
			self::$_dbHash[$hashTag] = new DB_Mysql5($dbParams);
		}
		
		return self::$_dbHash[$hashTag];
	}

	/**
	 * 操作数据库
	 * 
	 * <pre>
	 * 对于不同的操作，会有不同的返回值
	 * $type = 'LastID',则返回 lastinsertId
	 * $type = 'RowCount',则返回 删除或更新的影响行数
	 * $type = 'None',则返回恒返回 1
	 * </pre>
	 * 
	 * @param string $TSQL
	 * @param string $type
	 * @access public
	 * @return mixed
	 */
	public function query ($TSQL, $type = '') {
		if (is_null($this->db)){
			$this->dbConnect();
		}

		$this->sqlBox[] = $TSQL;

		switch ($type) {
			case 'LastID':
				$this->db->execute($TSQL, $type);
				return $this->db->lastID;
				break;
				
			case 'RowCount':
				$this->db->execute($TSQL, $type);
				return $this->db->rowCount;
				break;
				
			case 'None':
				return $this->db->execute($TSQL, $type);
				break;

			default:
				//get result
				return $this->db->query($TSQL);
		}
	}

	/**
	 * 启动事务
	 * 
	 * @access public
	 * @return void
	 */
	public function beginT () {
		if (is_null($this->db)){
			$this->dbConnect();
		}
		
		if ($this->onTransact) {
			return ;
		}
		
		switch ($this->_ConnectPond['dbType']) {
			case 'Mysql':
				$this->db->dbConnect->beginTransaction();
				$this->onTransact = true;
				break;
		}
	}

	/**
	 * 回滚事务
	 * 
	 * @access public
	 * @return void
	 */
	public function rollBackT () {
		switch ($this->_ConnectPond['dbType']) {
			case 'Mysql':
				$this->db->dbConnect->rollBack();
				break;
		}
		
		$this->onTransact = false;
	}

	/**
	 * 提交事务
	 * 
	 * @access public
	 * @return void
	 */
	public function commitT () {
		switch ($this->_ConnectPond['dbType']) {
			case 'Mysql':
				$this->db->dbConnect->commit();
				break;
		}
		
		$this->onTransact = false;
	}
}

/**
 * @package DB
 * @subpackage Driver
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2008 eason007<eason007@163.com>
 * @version 1.1.3.20100314
 */
class DB_Driver_PDO {
	public $dbConnect= null;
	public $lastID 	 = 0;
	public $rowCount = 0;
	private $errCount = 1;

	function __construct (
		$dbServer,
		$dbPort,
		$dbName,
		$dbUser,
		$dbPassword
	) {
		try
		{
			$this->dbConnect = @new PDO('mysql:host='.$dbServer.';dbname='.$dbName.';port='.$dbPort, $dbUser, $dbPassword,
				array(
					PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"
				)
			);

			$this->errCount = 0;

			return $this->dbConnect;
		}
		catch (PDOException $e)
		{
			if ($this->errCount < 3) {
				$this->errCount++;

				$this->__construct($dbServer,
					$dbPort,
					$dbName,
					$dbUser,
					$dbPassword
				);
			}
			else {
				throw new exception_Game('Database Not Exists.'.$this->errCount++.'>>', 11);
			}
		}
	}
	
	function __destruct() {
		$this->dbConnect = null;
	}

	public function query ($sSQL) {
		try{
			$result = $this->dbConnect->query($sSQL);
		}
		catch (PDOException $e)
		{
			throw new exception_Game('Query Error.', 12);
		}

		return $result->fetchAll();
	}

	public function execute ($sSQL, $type) {
		try{
			$result = $this->dbConnect->exec($sSQL);
		}
		catch (PDOException $e)
		{
			throw new exception_Game('Execute Error.', 13);
		}
		
		switch ($type) {
			case 'LastID':
				$this->lastID = $this->dbConnect->lastInsertId();
				break;
			case 'RowCount':
				$this->rowCount = $result;
				break;
			case 'None':
		}
		
		return $result;
	}
}
?>