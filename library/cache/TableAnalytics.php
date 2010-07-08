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
 * @copyright Copyright (c) 2007-2010 eason007<eason007@163.com>
 * @version 1.0.1.20100117
 */
 
class Cache_TableAnalytics {
	/**
	 * 
	 * @var object
	 * @see Cache_Core
	 * @access private
	 */
	private $_cache = null;
	
	function __construct() {
		$this->_cache = E_FW::load_Class('cache_Core');
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
		$_cacheID = md5(strtoupper($querySql));

		$tableCache = $this->_cache->getCache($tableName);

		if ($tableCache && array_key_exists($_cacheID, $tableCache)){
			$queryCache = $this->_cache->getCache($_cacheID);
		}
		else{
			$queryCache = false;
		}
		
		if ($queryCache) {
			return $queryCache;
		}
		else{
			return false;
		}
	}
	
	/**
	 * 设置缓存
	 * 
	 * 缓存的键名为 md5($querySql) 的密文，同时将会记录到该数据表名下
	 * 以便有其他 update 操作后，能将与该表有关的所有缓存清理
	 *
	 * @access public
	 * @param string $tableName 当前打开的数据表名
	 * @param string $querySql 完整的SQL查询语句
	 * @return mixed $queryData 被缓存的数据
	 */
	public function setCache ($tableName, $querySql, &$queryData) {
		$_cacheID = md5(strtoupper($querySql));

		$this->_cache->setCache($_cacheID, $queryData);
		
		$tableCache = $this->_cache->getCache($tableName);
		if (!$tableCache) {
			$tableCache = array();
		}
		$tableCache[$_cacheID] = count($queryData);
		
		$this->_cache->setCache($tableName, $tableCache);
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
	public function delCache ($tableName) {
		$this->_cache->delCache($tableName);
	}
}

?>