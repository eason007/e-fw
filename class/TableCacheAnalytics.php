<?php
/**
 * @package Class
 */

/**
 * 数据库对象操作类
 * 
 * 本类采用的是数据入口模式。主要用于实现CRUD的基本操作。
 * 并实现基本的关联操作
 * 
 * @package Class
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2008 eason007<eason007@163.com>
 * @version 1.0.0.20091230
 */
 
class Class_TableCacheAnalytics {
	private $_cache = null;
	
	function __construct() {
		E_FW::load_File('class_Cache');
		
		$this->_cache = E_FW::load_Class('Class_Cache');
	}
	
	public function chkCache ($tableName, $querySql) {
		$queryCache = $this->_cache->getCache(md5(strtoupper($querySql)));
		
		if ($queryCache) {
			$tableCache = $this->_cache->getCache($tableName);
			print_r($tableCache);
			echo 'it\'s cached.<br>';
			return $queryCache;
		}
		else{
			return false;
		}
	}
	
	public function setCache ($tableName, $querySql, &$queryData) {
		$this->_cache->setCache(md5(strtoupper($querySql)), $queryData);
		
		$tableCache = $this->_cache->getCache($tableName);
		print_r($tableCache);
		if (!$tableCache) {
			$tableCache = array();
		}
		$tableCache[md5(strtoupper($querySql))] = count($queryData);
		
		$this->_cache->setCache($tableName, $tableCache);
	}
	
	public function delCache ($tableName) {
		$tableCache = $this->_cache->getCache($tableName);
		
		foreach ($tableCache as $key => $value) {
			$this->_cache->delCache(md5(strtoupper($key)));
		}
		
		$this->_cache->delCache($tableName);
	}
}

?>