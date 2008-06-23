<?php
class DB_Mysql5 {
	private $dbServer = "";
	private $dbUser = "";
	private $dbPassword = "";
	private $dbName = "";
	private $dbPort = "";

	private $db = null;
	
	public $sqlBox = array();

	public $rowCount = 0;

	function __construct ($dbParams) {
		$this->dbServer = $dbParams["dbServer"];
		$this->dbPort	= $dbParams["dbPort"];
		$this->dbName	= $dbParams["dbName"];
		$this->dbUser	= $dbParams["dbUser"];
		$this->dbPassword = $dbParams["dbPassword"];

		switch ($dbParams["dbType"]) {
			case "Mysqli":
				$this->db = new DB_Driver_Mysqli($this->dbServer,
													$this->dbPort,
													$this->dbName,
													$this->dbUser,
													$this->dbPassword
				);

				break;
			case "PDO":
				$this->db = new DB_Driver_PDO($this->dbServer,
													$this->dbPort,
													$this->dbName,
													$this->dbUser,
													$this->dbPassword
				);

				break;
		}

		$this->query("SET NAMES 'utf8'", 1);
	}

	public function query ($TSQL, $type = 0) {
		$this->sqlBox[] = $TSQL;

		switch ($type) {
			case 1:
				//get Last ID
				$this->db->execute($TSQL);

				return $this->db->lastID;

				break;

			case 2:
				//get row count
				return $this->db->execute($TSQL);

				break;

			default:
				//get result
				return $this->db->query($TSQL);
		}
	}
}


class DB_Driver_Mysqli {
	public $dbConnect = null;
	public $lastID = 0;

	function __construct (&$dbServer,
							&$dbPort,
							&$dbName,
							&$dbUser,
							&$dbPassword
	) {
		$this->dbConnect = new mysqli($dbServer, $dbUser, $dbPassword, $dbName, $dbPort);
		if(mysqli_connect_errno()) {
			return false;
		} 
		else {
			return $this->dbConnect;
		}
	}

	public function query ($sSQL) {
		if (!isset($this->dbConnect)){
			$this->Connect();
		}

		$flag = array();

		$result = $this->dbConnect->query($sSQL);

		while ($row = @$result->fetch_assoc()) {
			$flag[] = $row;	
		}

		@$result->close();

		return $flag;
	}

	public function execute ($sSQL) {
		if (!isset($this->dbConnect)){
			$this->Connect();
		}

		$this->dbConnect->query($sSQL);
	
		$this->lastID = @$this->dbConnect->insert_id;

		return @$this->dbConnect->affected_rows;
	}
}

class DB_Driver_PDO {
	public $dbConnect = null;
	public $lastID = 0;

	function __construct (&$dbServer,
							&$dbPort,
							&$dbName,
							&$dbUser,
							&$dbPassword
	) {
		try
		{
			$this->dbConnect = new PDO('mysql:host='.$dbServer.';dbname='.$dbName, $dbUser, $dbPassword);
			$this->dbConnect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			return $this->dbConnect;
		}
		catch (PDOException $e)
		{
			return false;
		}
	}

	public function query ($sSQL) {
		if (!isset($this->dbConnect)){
			$this->Connect();
		}

		$result = $this->dbConnect->query($sSQL);

		$result->setFetchMode(PDO::FETCH_ASSOC);

		return $result->fetchAll();
	}

	public function execute ($sSQL) {
		if (!isset($this->dbConnect)){
			$this->Connect();
		}

		$this->dbConnect->query($sSQL);

		$this->lastID = @$this->dbConnect->lastInsertId();

		return @$this->dbConnect->rowCount();
	}
}
?>