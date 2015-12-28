<?php
/**
 * @package Cache
 */

/**
 * 数据库对象缓存分析类
 * 
 * <pre>
 * 用于 db_TableGateway 类中，通过类属性 isCache
 * 决定是否在查询中使用缓存
 * </pre>
 * 
 * @package Cache
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2011 eason007<eason007@163.com>
 * @version 2.0.4.20110428
 */
 
class Cache_TableAnalytics {
	/**
	 * 
	 * @var object
	 * @see Cache_Core
	 * @access private
	 */
	private $_cache = null;
	
	/**
	 * 缓存级别
	 * 
	 * 与 DB_TableGateway 的 isCache 对应
	 * 
	 * @var string
	 */
	public $level = '1';
	
	function __construct() {
		E_FW::load_File('cache_Core');
		$this->_cache = Cache_Core::getInstance(E_FW::get_Config('CACHE'));
	}
	
	/**
	 * 检查缓存是否存在
	 *
	 * @access public
	 * @param string $tableName 当前打开的数据表名
	 * @param string $querySql 完整的SQL查询语句
	 * @return mixed
	 */
	public function chkCache ($tableName, $querySql) {
		$tableCache = $this->_cache->fetch(strtolower($tableName));
		
		$_cacheID = md5(strtoupper($querySql));
		if ($tableCache and isset($tableCache[$this->level]) and array_key_exists($_cacheID, $tableCache[$this->level])){
			$queryCache = $this->_cache->fetch($_cacheID);
		}
		else{
			$queryCache = false;
		}
		
		return $queryCache;
	}
	
	/**
	 * 设置缓存
	 * 
	 * 缓存的键名为 md5($querySql) 的密文，同时将会记录到该数据表名下
	 * 以便有其他 update 操作后，能将与该表有关的所有缓存清理
	 * 
	 * 存储格式：
	 * tableCache = array (
	 * 	1 => array(cacheID => int, x => int),
	 *  $this->level => array(cacheID => int, x => int)
	 * )
	 *
	 * @access public
	 * @param string $tableName 当前打开的数据表名
	 * @param string $querySql 完整的SQL查询语句
	 * @return mixed $queryData 被缓存的数据
	 */
	public function setCache ($tableName, $querySql, &$queryData) {
		$_cacheID = md5(strtoupper($querySql));
		$this->_cache->store($_cacheID, $queryData);
		
		$tableName = strtolower($tableName);
		$tableCache = $this->_cache->fetch($tableName);
		if (!$tableCache) {
			$tableCache = array();
		}
		if (!isset($tableCache[$this->level])) {
			$tableCache[$this->level] = array();
		}
		$tableCache[$this->level][$_cacheID] = count($queryData);
		$this->_cache->store($tableName, $tableCache);
	}
	
	/**
	 * 清理缓存
	 * 
	 * 清理该数据表名下所有缓存
	 *
	 * @access public
	 * @param string $tableName 当前打开的数据表名
	 * @return void
	 */
	public function delCache ($tableName, $querySql = '') {
		$tableName = strtolower($tableName);
		
		if (substr($this->level, 0, 1) != '1') {
			$tableCache = $this->_cache->fetch($tableName);
			if (is_array($tableCache)) {
				unset($tableCache[$this->level]);
				unset($tableCache['1']);
			}
			$this->_cache->store($tableName, $tableCache);
		}
		else{
			$this->_cache->delete($tableName);
		}
	}
}

?>