<?php
E_FW::load_File('db_TableDataGateway');

class DB_ActiveRecord {
	protected $_props = array();
	
	protected $_funcs = array();
	
	protected $_chgProps = array();
	
	protected $_pkID;
	
	static $_meta;
	
	function __set($prop_name, $value) {
		$this->_props[$prop_name] = $value;
		$this->_chgProps[$prop_name] = $value;
	}
	
	function __get ($prop_name) {
		if (isset($this->_props[$prop_name])) {
			return $this->_props[$prop_name];
		}
		
		if (array_key_exists($prop_name, $this->_funcs)) {
			$funcSet = $this->_funcs[$prop_name];
			
			switch ($funcSet['linkType']){
				case 'belongsTo':
					E_FW::load_File($funcSet['tableClass']);
					
					return call_user_func_array(array(
						$funcSet['tableClass'], 'find'
					), array(
						$funcSet['tableClass'], array(
							'where' => $this->$funcSet['joinKey']
						)
					));
			}
		}
	}
	
	static function find ($class_name, $args) {
		$define = (array) call_user_func(array($class_name, '_define'));

		self::$_meta = E_FW::load_Class('DB_TableDataGateway');
		
		foreach ($define['props'] as $key => $value) {
			self::$_meta->$key = $value;
		}
		if (isset($define['props']['dbParams'])){
			self::$_meta->setDB($define['props']['dbParams'], true);
		}
		foreach ($args as $key => $value) {
			self::$_meta->$key($value);
		}
		$row = self::$_meta->select();
		
		if (count($row) > 1) {
			$rowSet = array();
			foreach ($row as $value) {
				$t = E_FW::load_Class($class_name, true);
				if (isset($define['funcs'])){
					$t->_funcs = $define['funcs'];
				}
				
				foreach ($value as $k => $v) {
					$t->$k = $v;
					
					if ($k == $define['props']['primaryKey']) {
						$t->_pkID = $v;
					}
				}
				
				$rowSet[] = $t;
			}
			
			return $rowSet;
		}
		else{
			$t = E_FW::load_Class($class_name, true);
			if (isset($define['funcs'])){
				$t->_funcs = $define['funcs'];
			}
			
			foreach ($row[0] as $k => $v) {
				$t->$k = $v;
				
				if ($k == $define['props']['primaryKey']) {
					$t->_pkID = $v;
				}
			}
			
			return $t;
		}
		
	} 
}

?>