<?php
E_FW::load_File('db_TableDataGateway');

class DB_ActiveRecord {
	protected $_props = array();
	
	protected $tableName;
	protected $primaryKey;
	
	protected $_meta;
	
	function __construct ($args = NULL){
		if (!is_null($args)){
			$this->_meta = E_FW::load_Class('DB_TableDataGateway');
			$this->_meta->tableName = $this->tableName;
			$this->_meta->primaryKey= $this->primaryKey;
			
			foreach ($args as $key => $value) {
				$this->_meta->$key($value);
			}
			
			$row = $this->_meta->select();
			
			$rowSet = array();
			$class_name = get_class($this);
			
			foreach ($row as $value) {
				$t = E_FW::load_Class($class_name, true);
				
				foreach ($value as $k => $v) {
					$t->$k = $v;
				}
				
				$rowSet[] = $t;
			}
			
			return $rowSet;
		}
	}
	
	function __set($prop_name, $value) {
		$this->_props[$prop_name] = $value;
	}
}

?>