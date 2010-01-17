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
 * @version 1.0.0.20100117
 */
 
class Cache_OutputAnalytics {
	private $_cache = null;
	private $_cacheID = null;
	
	function __construct() {
		$this->_cache = E_FW::load_Class('Cache_Core');
	}
	
	/**
	 * 开始缓存
	 * 
	 * 查找标记为 $cacheID 的缓存是否存在，如果存在，则直接输出缓存，并返回 true
	 * 如果不存在，则开始记录缓存，并返回 false
	 *
	 * @param string $cacheID cache key
	 * @return mixed
	 */
	public function start ($cacheID) {
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
        	
			return false;
		}
	}
	
	/**
	 * 缓存终止
	 * 
	 * 缓存记录终止，并且保存缓存
	 * 如果 $options[flash] = true,则输出缓存
	 *
	 * @param array $options
	 * @return void
	 */
	public function end ($options = array()) {
		$params = array(
			'flash'	=> true
		);
		foreach ($options as $key => $value) {
			$params[$key] = $value;
		}
		
		$data = ob_get_contents();
		ob_end_clean();
		
		$this->_cache->setCache($this->_cacheID, $data);
		$this->_cacheID = null;
		
		if ($params['flash']){
			echo $data;
		}
	}
}

?>