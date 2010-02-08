<?php
/**
 * @package Cache
 */

/**
 * 页面输出缓存分析类
 * 
 * 在页面输出时使用
 * 
 * @package Cache
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2008 eason007<eason007@163.com>
 * @version 1.0.3.20100208
 */
 
class Cache_OutputAnalytics {
	/**
	 * 
	 * @var object
	 * @see Cache_Core
	 * @access private
	 */
	private $_cache = null;
	
	/**
	 * 
	 * @var string
	 * @access private
	 */
	private $_cacheID = null;
	
	/**
	 * 
	 * @var string
	 * @access private
	 */
	private $_tableID = null;
	
	function __construct() {
		$this->_cache = E_FW::load_Class('cache_Core');
	}
	
	/**
	 * 开始缓存
	 * 
	 * 查找标记为 $cacheID 的缓存是否存在，如果存在，则直接输出缓存，并返回 true
	 * 如果不存在，则开始记录缓存，并返回 false
	 * 如果设定 $tableID，则可以关联后端的数据缓存
	 * 当数据改变时，前端页面缓存会同时失效
	 *
	 * @param string $cacheID
	 * @param string $tableID
	 * @return bool
	 * @access public
	 */
	public function start ($cacheID, $tableID = NULL) {
		$t = md5(strtoupper($cacheID));
		$queryCache = $this->_cache->getCache($t);
		
		if ($queryCache) {
			echo $queryCache;
			
			return true;
		}
		else{
			ob_start();
        	ob_implicit_flush(false);
        	
        	$this->_cacheID = $t;
			$this->_tableID = $tableID;
        	
			return false;
		}
	}
	
	/**
	 * 缓存终止
	 * 
	 * 停止缓存记录，并且保存缓存
	 * 如果 $options[flash] = true,则马上输出缓存
	 *  $options[time] = 缓存时间
	 *
	 * @param array $options
	 * @return void
	 * @access public
	 */
	public function end ($options = array()) {
		if ($this->_cacheID == null) {
			return false;
		}

		$params = array(
			'flash'	=> true,
			'time'	=> null
		);
		foreach ($options as $key => $value) {
			$params[$key] = $value;
		}
		
		$data = ob_get_contents();
		ob_end_clean();
		
		if (is_null($params['time'])){
			$this->_cache->setCache($this->_cacheID, $data);
		}
		else{
			$this->_cache->setCache($this->_cacheID, $data, array('expireTime' => $params['time']));
		}
		$this->_cacheID = null;
		
		if (!is_null($this->_tableID)) {
			$tableCache = $this->_cache->getCache($this->_tableID);
			if (!$tableCache) {
				$tableCache = array();
			}
			$tableCache[md5(strtoupper($this->_cacheID))] = mb_strlen($data, 'UTF-8');
			$this->_cache->setCache($this->_tableID, $tableCache);
			$this->_tableID = null;
		}
		
		if ($params['flash']){
			echo $data;
		}
	}
	
	/**
	 * 删除缓存
	 * 
	 * 根据传入的 cacheID，删除缓存
	 * 
	 * @param string $cacheID
	 * @return void
	 * @access public
	 */
	public function clear ($cacheID) {
		$this->_cache->delCache(md5(strtoupper($cacheID)));
	}
}

?>