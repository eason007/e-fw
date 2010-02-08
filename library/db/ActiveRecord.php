<?php
/**
 * @package DB
 */

E_FW::load_File('db_TableDataGateway');

/**
 * DB_ActiveRecord 类
 * 
 * <pre>
 * 基于 TableDataGateway 上实现的 ActiveRecord 模式
 * </pre>
 * 
 * @package DB
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2008 eason007<eason007@163.com>
 * @version 1.0.0.20100207
 */

class DB_ActiveRecord {
	protected $_props = array();
	
	protected $_funcs = array();
	
	protected $_chgProps = array();
	
	static $_meta;
	
	static $_define;
	
	function __construct($props) {
		$define = self::_defMeta(get_class($this));
		if (isset($define['funcs'])){
			$this->_funcs = $define['funcs'];
		}
		
		$this->_props = $props;
		unset($this->_props[self::$_define['props']['primaryKey']]);
	}
	
	function __set($prop_name, $value) {
		$pkName = self::$_define['props']['primaryKey'];
		
		if ($prop_name != $pkName) {
			$this->_props[$prop_name] 	= $value;
			$this->_chgProps[$prop_name]= $value;
		}
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
	
	static function _defMeta ($class_name) {
		$define = (array) call_user_func(array($class_name, '_define'));
		
		if (!isset(self::$_meta)){
			self::$_meta = E_FW::load_Class('DB_TableDataGateway');
			
			foreach ($define['props'] as $key => $value) {
				self::$_meta->$key = $value;
			}
			if (isset($define['props']['dbParams'])){
				self::$_meta->setDB($define['props']['dbParams'], true);
			}
			
			self::$_define = $define;
		}
		
		return $define;
	}
	
	static function find ($class_name, $args) {
		$define = self::_defMeta($class_name);
		
		foreach ($args as $key => $value) {
			self::$_meta->$key($value);
		}
		$row = self::$_meta->select();
		
		if (count($row) > 1) {
			$rowSet = array();
			foreach ($row as $value) {
				$t = E_FW::load_Class($class_name, true, $value);
				if (isset($define['funcs'])){
					$t->_funcs = $define['funcs'];
				}
				
				$rowSet[] = $t;
			}
			
			return $rowSet;
		}
		else{
			$t = E_FW::load_Class($class_name, true, $row[0]);
			if (isset($define['funcs'])){
				$t->_funcs = $define['funcs'];
			}
			
			return $t;
		}
	}
	
	public function save () {
		$pkName = self::$_define['props']['primaryKey'];
		
		if (isset($this->_props[$pkName])){
			$this->_chgProps[$pkName] = $this->_props[$pkName];
			
			$rt = self::$_meta->update($this->_chgProps);
			
			if ($rt['rowCount'] > 0){
				$this->_chgProps = array();
				return true;
			}
			else{
				return false;
			}
		}
		else{
			$rt = self::$_meta->insert($this->_props);
			
			if ($rt['lastID'] > 0){
				$this->_props[$pkName] = $rt['lastID'];
				
				return true;
			}
			else{
				return false;
			}
		}
	}
}

?>