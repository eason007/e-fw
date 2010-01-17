<?php
/**
 * @package Cache
 */

/**
 * 数据库对象缓存分析类
 * 
 * 用于 Class_TableDataGateway 类中，通过类属性 isCache
 * 决定是否在查询中使用缓存
 * 
 * @package Cache
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2008 eason007<eason007@163.com>
 * @version 1.0.1.20100117
 */
 
class Cache_TableAnalytics {
	private $_cache = null;
	
	function __construct() {
		$this->_cache = E_FW::load_Class('Cache_Core');
	}
	
	/**
	 * 检查缓存是否存在
	 *
	 * @param string $tableName 当前打开的数据表名
	 * @param string $querySql 完整的SQL查询语句
	 * @return mixed
	 */
	public function chkCache ($tableName, $querySql) {
		$queryCache = $this->_cache->getCache(md5(strtoupper($querySql)));
		
		if ($queryCache) {
			echo 'cached';
			
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
	 * @param string $tableName 当前打开的数据表名
	 * @param string $querySql 完整的SQL查询语句
	 * @return mixed $queryData 被缓存的数据
	 */
	public function setCache ($tableName, $querySql, &$queryData) {
		$this->_cache->setCache(md5(strtoupper($querySql)), $queryData);
		
		$tableCache = $this->_cache->getCache($tableName);
		if (!$tableCache) {
			$tableCache = array();
		}
		$tableCache[md5(strtoupper($querySql))] = count($queryData);
		
		$this->_cache->setCache($tableName, $tableCache);
	}
	
	/**
	 * 清理缓存
	 * 
	 * 清理该数据表名下所有缓存
	 *
	 * @param string $tableName 当前打开的数据表名
	 */
	public function delCache ($tableName) {
		$tableCache = $this->_cache->getCache($tableName);
		
		if ($tableCache) {
			foreach ($tableCache as $key => $value) {
				$this->_cache->delCache($key);
			}
		}
		
		$this->_cache->delCache($tableName);
	}
}

?>