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
	
	/**
	 * 设定属性（魔术方法）
	 * 
	 * 如果设定的属性为非主键字段，则记录到 _chgProps 属性中
	 * 以便调用 save 方法时使用
	 * 
	 * @param $prop_name
	 * @param $value
	 * @return void
	 */
	function __set($prop_name, $value) {
		$pkName = self::$_define['props']['primaryKey'];
		
		if ($prop_name != $pkName) {
			$this->_props[$prop_name] 	= $value;
			$this->_chgProps[$prop_name]= $value;
		}
	}
	
	/**
	 * 获取属性（魔术方法）
	 * 
	 * 如果是对象基本属性，则直接返回。
	 * 如果是关联属性，则实时查询数据库获取。
	 * 
	 * @param $prop_name
	 * @access mixed
	 */
	function __get ($prop_name) {
		if (isset($this->_props[$prop_name])) {
			return $this->_props[$prop_name];
		}
		
		if (array_key_exists($prop_name, $this->_funcs)) {
			$funcSet = $this->_funcs[$prop_name];
			
			switch ($funcSet['linkType']){
				case 'belongsTo':
				case 'hasMany':
					E_FW::load_File($funcSet['tableClass']);
					
					return call_user_func_array(array(
						$funcSet['tableClass'], 'find'
					), array(
						$funcSet['tableClass'], array(
							'where' => $this->$funcSet['joinKey']
						)
					));
					
					break;
				case 'hasOne':
					E_FW::load_File($funcSet['tableClass']);
					
					return call_user_func_array(array(
						$funcSet['tableClass'], 'find'
					), array(
						$funcSet['tableClass'], array(
							'where' => $this->$funcSet['joinKey'].' = '.$this->$funcSet['linkKey']
						)
					));
					
					break;
			}
		}
	}
	
	/**
	 * 获取 Model 的定义
	 * 
	 * 调用 Model 的 _define 方法，获取对 Model 的定义
	 * _define 返回的为多维数组，其中
	 * props 代表对数据库连接的定义
	 * funcs 代表对模型的定义
	 * 
	 * @param string $class_name
	 * @return array
	 * @access static
	 */
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
	
	/**
	 * 查找数据（静态方法）
	 * 
	 * 通过 $args 的指定，查找符合的数据
	 * 返回对象或对象集合
	 * 
	 * @param string $class_name 数据 Model 名
	 * @param array $args 查找的条件，与 TableDataGateway 的方法对应
	 * @return mixed
	 * @access static
	 */
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
	
	/**
	 * 保存数据
	 * 
	 * 将对象内的数据保存到数据库
	 * 如果主键属性为空，则为新增记录，否则为更新记录
	 * 
	 * @access public
	 * @return bool
	 */
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