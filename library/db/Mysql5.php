<?php
/**
 * @package DB
 * @subpackage Driver
 */

/**
 * @package DB
 * @subpackage Driver
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2010 eason007<eason007@163.com>
 * @version 1.2.2.20100310
 */

class DB_Mysql5 {
	/**
	 * Enter description here...
	 *
	 * @var object
	 * @see DB_Driver_Mysqli
	 * @see DB_Driver_PDO
	 * @access private
	 */
	private $db = null;
	
	/**
	 * 查询SQL日志
	 *
	 * @var array
	 * @access public
	 */
	public $sqlBox = array();

	/**
	 * Enter description here...
	 *
	 * @var int
	 * @access public
	 */
	public $rowCount = 0;

	function __construct ($dbParams) {
		foreach ($dbParams as $key => $value) {
			$this->$key = $value;
		}
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
		switch ($this->dbType) {
			case 'Mysqli':
				$this->db = new DB_Driver_Mysqli(
					$this->dbServer,
					$this->dbPort,
					$this->dbName,
					$this->dbUser,
					$this->dbPassword
				);

				break;
			case 'PDO':
				$this->db = new DB_Driver_PDO(
					$this->dbServer,
					$this->dbPort,
					$this->dbName,
					$this->dbUser,
					$this->dbPassword
				);
				
				$this->query('SET NAMES \'utf8\';', 'None');

				break;
		}
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
			case 'RowCount':
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
		
		switch ($this->dbType) {
			case 'Mysqli':
				$this->db->dbConnect->autocommit(false);
				break;
			case 'PDO':
				$this->db->dbConnect->beginTransaction();
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
		switch ($this->dbType) {
			case 'Mysqli':
			case 'PDO':
				$this->db->dbConnect->rollBack();
				break;
		}
	}

	/**
	 * 提交事务
	 * 
	 * @access public
	 * @return void
	 */
	public function commitT () {
		switch ($this->dbType) {
			case 'Mysqli':
			case 'PDO':
				$this->db->dbConnect->commit();
				break;
		}
	}
}


/**
 * @package DB
 * @subpackage Driver
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2008 eason007<eason007@163.com>
 * @version 1.1.2.20100126
 */
class DB_Driver_Mysqli {
	public $dbConnect= null;
	public $lastID 	 = 0;
	public $rowCount = 0;

	function __construct (
		$dbServer,
		$dbPort,
		$dbName,
		$dbUser,
		$dbPassword
	) {
		$this->dbConnect = @new mysqli($dbServer, $dbUser, $dbPassword, $dbName, $dbPort);
		if(mysqli_connect_errno()) {
			E_FW::load_Class('exception_DB');
			throw new exception_DB('Database Not Exists.');
		}
		else {
			return $this->dbConnect;
		}
	}
	
	function __destruct() {
		$this->dbConnect->close();
	}

	public function query ($sSQL) {
		$flag = array();

		$result = $this->dbConnect->query($sSQL);

		if ($result){
			while ($row = $result->fetch_assoc()) {
				$flag[] = $row;
			}

			$result->close();

			return $flag;
		}
		else{
			return false;
		}
	}

	public function execute ($sSQL, $type) {
		$result = $this->dbConnect->query($sSQL);

		if ($result){
			$rt = 0;
			switch ($type) {
				case 'LastID':
					$rt = $this->dbConnect->insert_id ? $this->dbConnect->insert_id : $this->dbConnect->affected_rows;
					break;
				case 'RowCount':
					$rt = $this->dbConnect->affected_rows;
					break;
				case 'None':
					$rt = 1;
					break;
			}
			
			return $rt;
		}
		else{
			return 0;
		}
	}
}

/**
 * @package DB
 * @subpackage Driver
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2008 eason007<eason007@163.com>
 * @version 1.1.3.20100310
 */
class DB_Driver_PDO {
	public $dbConnect= null;
	public $lastID 	 = 0;
	public $rowCount = 0;

	function __construct (
		$dbServer,
		$dbPort,
		$dbName,
		$dbUser,
		$dbPassword
	) {
		try
		{
			$this->dbConnect = new PDO('mysql:host='.$dbServer.';dbname='.$dbName, $dbUser, $dbPassword,
				array(
					PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"
				)	
			);

			return $this->dbConnect;
		}
		catch (PDOException $e)
		{
			E_FW::load_Class('exception_DB');
			throw new exception_DB('Database Not Exists.');
		}
	}

	public function query ($sSQL) {
		try{
			$result = $this->dbConnect->query($sSQL);
		}
		catch (PDOException $e)
		{
			E_FW::load_Class('exception_DB');
			throw new exception_DB('Query Error.');
		}

		return $result->fetchAll();
	}

	public function execute ($sSQL, $type) {
		try{
			$result = $this->dbConnect->exec($sSQL);
		}
		catch (PDOException $e)
		{
			E_FW::load_Class('exception_DB');
			throw new exception_DB('Execute Error.');
		}
		
		$rt = 0;
		switch ($type) {
			case 'LastID':
				$rt = $this->dbConnect->lastInsertId() ? $this->dbConnect->lastInsertId() : $result;
				break;
			case 'RowCount':
				$rt = $result;
				break;
			case 'None':
				$rt = 1;
				break;
		}
		
		return $rt;
	}
}
?>