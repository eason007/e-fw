<?php
E_FW::load_File("base/efw-db-DriversMysql5.php");

class EFW_DB_TableDataGateway {
	public $tableName = null;
	public $primaryKey = null;

	public $belongsTo = array();
	public $hasOne = array();
	public $hasMany = array();
	public $manyToMany = array();

	public $autoLink = false;

	public $field = null;
	public $where = null;
	public $other = null;
	public $order = null;
	public $limit = null;

	private $db = null;


	function __construct() {
	
	}

	public function setDB ($dbParams, $isReload = false) {
		if ( (is_null($this->db)) or ($isReload) ) {
			switch ($dbParams["dbType"]) {
				case "Mysqli":
					$this->db = new EFW_DB_Mysql5($dbParams);
					break;
			}
		}
	}


	public function selectSQL ($sql) {
		return $this->db->query($sql);
	}


	public function select ($link = "", $isExecute = true, $isCount = false) {
		if (!is_null($this->where)){
			$conditions = $this->getWhere();
		}
		else{
			$conditions = "";
		}
		if (!is_null($this->limit)){
			$limit = $this->getLimit();
		}
		else{
			$limit = "";
		}
		if (is_null($this->field)){
			$this->field = "*";
		}
		if (!is_null($this->order)){
			$this->order = " ORDER BY ".$this->order;
		}


		$sql = "SELECT ".$this->field.",".$this->primaryKey;
		$sql.= " FROM `".$this->tableName."` AS MT";
		$sql.= " WHERE 1=1".$conditions;
		$sql.= $this->other;
		$sql.= $this->order;
		$sql.= $limit;

		$c_sql = "SELECT COUNT(".$this->primaryKey.") AS RCount";
		$c_sql.= " FROM `".$this->tableName."` AS MT";
		$c_sql.= " WHERE 1=1".$conditions;
		$c_sql.= $this->other;
		$c_sql.= $this->order;

		$this->clear();

		if (!$isExecute) {
			return $sql;
		}

		$result = $this->db->query($sql);
		if ($result) {
			if ( ($this->autoLink) and ($link == "")){
				$link = "belongsTo,hasOne,hasMany,manyToMany";
			}

			if ( ($this->autoLink) or ($link != "") ){
				$linkValue = explode(",", $link);

				foreach($linkValue as $val){
					$this->_getLinkData($result, $val);
				}
			}
		}

		if ($isCount){
			$temp["result"] = $result;
			unset($result);

			$tmp = $this->db->query($c_sql);
			$temp["resultCount"] = $tmp[0]["RCount"];

			$result = $temp;
		}

		return $result;
	}

	public function insert ($rowData, $isExecute = true){
		if (!$this->_beforeInsert($rowData)) {
            return false;
        }

		$field = "";
		$value = "";

		foreach($rowData as $key => $val){
			if (!is_array($val)){
				$field.= "`".$key."`, ";

				if (is_numeric($val)){
					$value.= $val.", ";
				}
				else{
					$value.= "'".$val."', ";
				}
			}
			else{
				$linkData[$key] = $val;
			}
		}

		$sql = "INSERT INTO `".$this->tableName."`";
		$sql.= " (".substr($field, 0, strlen($field) - 2).")";
		$sql.= " VALUES";
		$sql.= " (".substr($value, 0, strlen($value) - 2).")";

		$this->clear();

		if (!$isExecute) {
			return $sql;
		}

		$result[$this->primaryKey] = $this->db->query($sql, 1);

		if (!empty($linkData)){
			foreach($linkData as $key => $val){
				$result[$key] = $this->_insertLinkData($key, $val, $result[$this->primaryKey]);
			}
		}

		return $result;
	}

	public function update ($rowData, $isExecute = true) {
		if (!$this->_beforeUpdate($rowData)) {
            return false;
        }

		if (!is_null($this->where)){
			$conditions = $this->getWhere();
		}
		else{
			$conditions = "";
		}
		if (!is_null($this->limit)){
			$limit = $this->getLimit();
		}
		else{
			$limit = "";
		}
		if (!is_null($this->order)){
			$this->order = " ORDER BY ".$this->order;
		}

		$pk = "";
		$linkData = array();

		foreach($rowData as $key => $val){
			if (!is_array($val)){
				if (strtoupper($key) == strtoupper($this->primaryKey)){
					$conditions.= " AND `".$this->primaryKey."` = ".$val;
					$this->where = $val;

					continue;
				}

				if (is_numeric($val)){
					$pk.= "`".$key."` = ".$val.", ";
				}
				else{
					$pk.= "`".$key."` = '".$val."', ";
				}
			}
			else{
				$linkData[$key] = $val;
			}
		}

		if ($isExecute){
			$swtichBox = $this->autoLink;

			$this->autoLink = false;
			$this->field	= $this->primaryKey;
			$this->other	= "";

			$ID	= $this->select();

			$this->autoLink = $swtichBox;
		}


		$sql = "UPDATE `".$this->tableName."`";
		$sql.= " SET ".substr($pk, 0, strlen($pk) - 2);
		$sql.= " WHERE 1=1".$conditions;
		$sql.= $this->order;
		$sql.= $limit;

		$this->clear();

		if (!$isExecute) {
			return $sql;
		}

		$result["rowCount"] = $this->db->query($sql, 2);

		if ($result["rowCount"] > 0){
			$IDStr = "";
			foreach($ID as $val){
				$IDStr.= $val[$this->primaryKey].", ";
			}

			foreach($linkData as $key => $val){
				$result[$key] = $this->_updateLinkData($key, $val, $IDStr);
			}
		}

		return $result;
	}

	public function del ($link = "", $isExecute = true) {
		if (!$this->_beforeDelete()) {
            return false;
        }

		if (!is_null($this->where)){
			$conditions = $this->getWhere();
		}
		else{
			$conditions = "";
		}
		if (!is_null($this->limit)){
			$limit = $this->getLimit();
		}
		else{
			$limit = "";
		}
		if (!is_null($this->order)){
			$this->order = " ORDER BY ".$this->order;
		}

		if ($isExecute){
			$swtichBox = $this->autoLink;

			$this->autoLink = false;
			$this->field	= $this->primaryKey;
			$this->other	= "";

			$ID	= $this->select();

			$this->autoLink = $swtichBox;
		}

		$sql = "DELETE FROM `".$this->tableName."`";
		$sql.= " WHERE 1=1".$conditions;
		$sql.= $this->order;
		$sql.= $limit;

		$this->clear();

		if (!$isExecute) {
			return $sql;
		}

		$result["rowCount"] = $this->db->query($sql, 2);

		if ($ID) {
			if ( ($this->autoLink) and ($link == "") ){
				$link = "hasOne,hasMany,manyToMany";
			}

			if ( ($this->autoLink) or ($link != "") ){
				$linkValue  = explode(",", $link);
				$IDStr		= "";

				foreach($ID as $val){
					$IDStr.= $val[$this->primaryKey].", ";
				}

				foreach($linkValue as $key => $val){
					$result[$key] = $this->_delLinkData($val, $IDStr);
				}
			}
		}

		return $result;
	}

	private function _delLinkData ($linkType, $primaryKeyStr) {
		if (!is_null($this->$linkType)){
			$linkSetting = $this->$linkType;
			$linkClass	 = new $linkSetting["tableClass"]();

			$linkClass->autoLink = false;
			$linkClass->where	 = "`".$linkSetting["joinKey"]."` IN (".$primaryKeyStr."0)";

			$sql= $linkClass->del("", false);
			$rt	= $this->db->query($sql, 2);

			unset($linkClass);
	
			return $rt["rowCount"];
		}
	}

	private function _updateLinkData ($linkType, &$row, $primaryKeyStr) {
		if (!is_null($this->$linkType)){
			$linkSetting = $this->$linkType;
			$linkClass	 = new $linkSetting["tableClass"]();

			switch ($linkType) {
				case "hasOne":
					if (!isset($row[$linkClass->primaryKey])){
						$linkClass->where = "`".$linkClass->primaryKey."` IN (".$primaryKeyStr."0)";
					}
					$sql = $linkClass->update($row, false);
					$linkRT = $this->db->query($sql, 2);

					break;

				case "hasMany":
				case "manyToMany":
					foreach($row as $val){
						if (!empty($val[$linkClass->primaryKey])){
							$sql = $linkClass->update($val, false);
							$linkRT[] = $this->db->query($sql, 2);
						}
					}

					break;
			}

			unset($linkClass);

			return $linkRT;
		}
	}

	private function _insertLinkData ($linkType, &$row, $primaryID) {
		if (!is_null($this->$linkType)){
			$linkSetting = $this->$linkType;
			$linkClass	 = new $linkSetting["tableClass"]();

			switch ($linkType) {
				case "hasOne":
					$row[$linkSetting["joinKey"]] = $primaryID;
					$sql = $linkClass->insert($row, false);
					$linkRT = $this->db->query($sql, 2);

					break;

				case "hasMany":
					foreach($row as $val){
						$val[$linkSetting["joinKey"]] = $primaryID;
						$sql = $linkClass->insert($val, false);
						$linkRT[] = $this->db->query($sql, 2);
					}

					break;

				case "manyToMany":
					$switchBox = $linkClass->tableName;
					$linkClass->tableName = $linkSetting["joinTable"];
					
					foreach($row as $val){
						$val[$linkSetting["joinKey"]] = $primaryID;
						$sql = $linkClass->insert($val, false);
						$linkRT[] = $this->db->query($sql, 2);
					}

					$linkClass->tableName = $switchBox;

					break;
			}

			unset($linkClass);

			return $linkRT;
		}
	}

	private function _getLinkData (&$rt, $linkType) {
		$linkSetting = $this->$linkType;

		if (!@is_null($linkSetting["tableClass"])){
			$linkClass = new $linkSetting["tableClass"]();

			switch ($linkType) {
				case "belongsTo":
					//Article->ColumnID Join Column->ID
					foreach($rt as $val){
						$ID[] = $val[$linkSetting["joinKey"]];
					}
					$IDStr = implode(",", $ID);

					$linkClass->where = "`".$linkClass->primaryKey."` IN (".$IDStr.")";
					$sql = $linkClass->select("", false);
					$linkData = $this->db->query($sql);

					foreach($rt as $key => $val){
						foreach($linkData as $k => $v){
							if ($val[$linkSetting["joinKey"]] == $v[$linkClass->primaryKey]){
								$rt[$key][$linkSetting["mappingName"]] = $v;
								unset($linkData[$k]);

								break;
							}
						}
					}

					break;

				case "hasOne":
					//User->ID Join UserProfiles->UserID
					foreach($rt as $val){
						$ID[] = $val[$this->primaryKey];
					}
					$IDStr = implode(",", $ID);

					$linkClass->where = "`".$linkSetting["joinKey"]."` IN (".$IDStr.")";
					$sql = $linkClass->select("", false);
					$linkData = $this->db->query($sql);

					foreach($rt as $key => $val){
						foreach($linkData as $k => $v){
							if ($val[$this->primaryKey] == $v[$linkSetting["joinKey"]]){
								$rt[$key][$linkSetting["mappingName"]] = $v;
								unset($linkData[$k]);

								break;
							}
						}
					}

					break;

				case "hasMany":
					//User->ID Join Order->UserID
					foreach($rt as $val){
						$ID[] = $val[$this->primaryKey];
					}
					$IDStr = implode(",", $ID);

					$linkClass->where = "`".$linkSetting["joinKey"]."` IN (".$IDStr.")";
					$sql = $linkClass->select("", false);
					$linkData = $this->db->query($sql);

					foreach($rt as $key => $val){
						foreach($linkData as $v){
							if ($val[$this->primaryKey] == $v[$linkSetting["joinKey"]]){
								$rt[$key][$linkSetting["mappingName"]][] = $v;
							}
						}
					}

					break;

				case "manyToMany":
					//User->ID Join UserRoles->UserID | UserRoles->RolesID Join Roles->ID
					foreach($rt as $val){
						$ID[] = $val[$this->primaryKey];
					}
					$IDStr = implode(",", $ID);

					$sql = "SELECT JT.*, MT.".$linkSetting["joinKey"];
					$sql.= " FROM `".$linkSetting["joinTable"]."` AS MT";
					$sql.= " JOIN `".$linkClass->tableName."` AS JT ON MT.".$linkSetting["linkKey"]." = JT.".$linkClass->primaryKey;
					$sql.= " WHERE MT.".$linkSetting["joinKey"]." IN (".$IDStr.")";
					$linkData = $this->db->query($sql);

					foreach($rt as $key => $val){
						foreach($linkData as $v){
							if ($val[$this->primaryKey] == $v[$linkSetting["joinKey"]]){
								$rt[$key][$linkSetting["mappingName"]][] = $v;
							}
						}
					}

					break;
			}

			unset($linkClass);
		}
	}


	/**
	$conditions = 23;
	$conditions = "name = 'dualface'";
	$conditions = array("name <> 'dualface'", "sex = 'male'");
	$conditions = array("born = '1977/10/24'", "13");
	$conditions = array('sex' => 'sex', 'id' => 1);
	*/
	public function getWhere () {
		if (is_array($this->where)){
			$rt = "";

			foreach($this->where as $key => $val){
				if (is_numeric($key)){
					if (is_numeric($val)){
						$rt.= " AND `".$this->primaryKey."` = ".$val;
					}
					else{
						$rt.= " AND ".$this->sqlEncode($val);
					}
				}
				else{
					$rt.= " AND `".$key."` = '".$val."'";
				}
			}
		}
		else{
			if (is_numeric($this->where)){
				$rt = " AND `".$this->primaryKey."` = ".$this->where;
			}
			else{
				$rt = " AND ".$this->sqlEncode($this->where);
			}
		}

		return $rt;
	}

	public function sqlEncode ($value, $type = 0){
		return $value;
	}

	private function getLimit () {
		if (is_array($this->limit)){
			return " LIMIT ".$this->limit["offset"] * $this->limit["length"].",".$this->limit["length"];
		}
		else{
			return " LIMIT ".$this->limit;
		}
	}

	private function clear () {
		$this->field = null;
		$this->where = null;
		$this->other = null;
		$this->order = null;
		$this->limit = null;
	}


	private function _beforeInsert (){
		return true;
	}

	private function _beforeUpdate () {
		return true;
	}

	private function _beforeDelete () {
		return true;
	}
}

?>