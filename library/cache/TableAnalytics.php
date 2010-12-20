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
	
	/**
	 * 缓存级别
	 * 
	 * 与 DB_TableGateway 的 isCache 对应
	 * 
	 * @var string
	 */
	public $cacheLevel = 1;
	/**
	 * 缓存标识
	 * 
	 * @var string
	 */
	public $cacheTag = '';
	
	function __construct() {
		E_FW::load_File('cache_Core');
		$this->_cache = Cache_Core::getInstance(E_FW::get_Config('CACHE'));

		$this->debuger = E_FW::load_Class('log_Core');
		$this->debuger->addWriter(E_FW::load_Class('writer_File', true, './sql_log.txt'));
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
		$this->debuger->addField('type', 'check');
		$this->debuger->addField('in-level', $this->cacheLevel);
		if ($this->cacheLevel > 1 and empty($this->cacheTag)) {
			$this->cacheLevel = 1;
		}
		$this->debuger->addField('out-level', $this->cacheLevel);
		$this->debuger->addField('cache-tag', $this->cacheTag);
		
		switch ($this->cacheLevel) {
			case 3:
			case 2:
				$_groupID = strtolower($tableName.'.'.$this->cacheLevel.'.'.$this->cacheTag);
				break;
			
			case 1:
				$_groupID = strtolower($tableName);
				break;
		}
		$this->debuger->addField('group-id', $_groupID);
		$tableCache = $this->_cache->fetch($_groupID);
		
		$_cacheID = md5(strtoupper($querySql));
		$this->debuger->addField('cache-id', $_cacheID);
		if ($tableCache && array_key_exists($_cacheID, $tableCache)){
			$queryCache = $this->_cache->fetch($_cacheID);
		}
		else{
			$queryCache = false;
		}
		$this->debuger->addField('result', count($queryCache));
		$this->debuger->addField('table', $tableName);
		$this->debuger->info($querySql);
		
		return $queryCache;
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
		if ($this->cacheLevel > 1 and empty($this->cacheTag)) {
			$this->cacheLevel = 1;
		}
		
		$_cacheID = md5(strtoupper($querySql));
		$this->_cache->store($_cacheID, $queryData);
		
		switch ($this->cacheLevel) {
			case 3:
			case 2:
				$_groupID = strtolower($tableName.'.'.$this->cacheLevel.'.'.$this->cacheTag);
				break;
			
			case 1:
				$_groupID = strtolower($tableName);
				break;
		}
		$tableCache = $this->_cache->fetch($_groupID);
		if (!$tableCache) {
			$tableCache = array();
		}
		$tableCache[$_cacheID] = count($queryData);
		$this->_cache->store($_groupID, $tableCache);
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
		$this->debuger->addField('type', 'del');
		$this->debuger->addField('in-level', $this->cacheLevel);
		if ($this->cacheLevel > 1 and empty($this->cacheTag)) {
			$this->cacheLevel = 1;
		}
		$this->debuger->addField('out-level', $this->cacheLevel);
		$this->debuger->addField('cache-tag', $this->cacheTag);
		
		switch ($this->cacheLevel) {
			case 3:
			case 2:
				$_groupID = strtolower($tableName.'.'.$this->cacheLevel.'.'.$this->cacheTag);
				$this->_cache->delete($_groupID);

				$this->debuger->addField('group-id', $_groupID);
				break;
		}
		$this->debuger->addField('table', $tableName);
		$this->debuger->info($querySql);
		
		$this->_cache->delete(strtolower($tableName));
	}
	
	public function cacheSet ($set) {
		$this->cacheLevel = $set['level'];
		$this->cacheTag = $set['tag'];
	}
}

?>