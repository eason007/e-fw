<?php
/**
 * @package DB
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2008 eason007<eason007@163.com>
 * @version 1.2.1.20091221
 */

class DB_Mysql5 {
	private $db = null;
	
	public $sqlBox = array();

	public $rowCount = 0;

	function __construct ($dbParams) {
		foreach ($dbParams as $key => $value) {
			$this->$key = $value;
		}
	}
	
	function __destruct() {
		$this->db = null;
	}

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

				break;
		}

		$this->query('SET NAMES \'utf8\';', 'None');
	}

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

	public function rollBackT () {
		switch ($this->dbType) {
			case 'Mysqli':
			case 'PDO':
				$this->db->dbConnect->rollBack();
				break;
		}
	}

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
 * @subpackage DB_Driver
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2008 eason007<eason007@163.com>
 * @version 1.1.1.20091216
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
			throw new MyException('1 is an invalid parameter');
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
					$rt = $this->dbConnect->insert_id;
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
 * @subpackage DB_Driver
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2008 eason007<eason007@163.com>
 * @version 1.0.0.20080108
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
			$this->dbConnect = new PDO('mysql:host='.$dbServer.';dbname='.$dbName, $dbUser, $dbPassword);
			$this->dbConnect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			return $this->dbConnect;
		}
		catch (PDOException $e)
		{
			throw new MyException('1 is an invalid parameter');
		}
	}

	public function query ($sSQL) {
		$result = $this->dbConnect->query($sSQL);

		$result->setFetchMode(PDO::FETCH_ASSOC);

		return $result->fetchAll();
	}

	public function execute ($sSQL, $type) {
		$result = $this->dbConnect->query($sSQL);
		
		$rt = 0;
		switch ($type) {
			case 'LastID':
				$rt = $this->dbConnect->lastInsertId();
				break;
			case 'RowCount':
				$rt = $result->rowCount();
				break;
			case 'None':
				$rt = 1;
				break;
		}
		
		return $rt;
	}
}
?>