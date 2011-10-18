<?php
/**
 * @package Cache
 */

/**
 * 缓存类
 * 
 * <pre>
 * 提供缓存服务，支持文件、memcache、rediska
 * memcache使用memcache扩展实现
 * </pre>
 * 
 * @package Cache
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2011 eason007<eason007@163.com>
 * @version 3.0.3.20110428
 */
abstract class Cache_Abstract {
	protected $expireTime = 3600;
	protected $isSerialize;
	public $prefix;
	
	/**
	 * 读取
	 * 
	 * Enter description here ...
	 * @param unknown_type $key
	 */
	abstract public function fetch($key, $unserialize = false);
	
	/**
	 * 写
	 * 
	 * Enter description here ...
	 * @param unknown_type $key
	 * @param unknown_type $value
	 */
	abstract public function store($key, $value, $parSet = array());
	
	/**
	 * 删除
	 * 
	 * Enter description here ...
	 * @param unknown_type $key
	 */
	abstract public function delete($key);
}

final class Cache_Core {
	private static $_selfHash = array();
	
	public static function getInstance ($setParams) {
		$hashTag = md5(strtolower($setParams['type'].$setParams['prefix']));
		
		if ( !array_key_exists($hashTag, self::$_selfHash) ) {
			$className = 'Cache_Driver_'.ucfirst($setParams['type']);
			
			self::$_selfHash[$hashTag] = new $className($setParams['detail']);
			self::$_selfHash[$hashTag]->prefix = $setParams['prefix'];
		}
		
		return self::$_selfHash[$hashTag];
	}
}

class Cache_Driver_File extends Cache_Abstract {
	private $dirPath;
	private $hashLevel = 0;
	private $ext = '.txt';

	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $Params
	 */
	function __construct($Params = null) {
		if (is_null($Params)){
			E_FW::load_File('exception_Cache');
			throw new Exception_Cache('Cache Not Have Params.');
		}
		
		foreach ($Params as $key => $value) {
			$this->$key = $value;
		}
	}

	/**
	 * 获取缓存/检查缓存是否存在
	 * 
	 * <pre>
	 * 如数据存在并有效，则读出并返回。
	 * 如数据存在但过期，或者不存在，则返回 false
	 * </pre>
	 *
	 * @access public
	 * @param string $cacheID 缓存标记名
	 * @param bool $unserialize 是否反序列化
	 * @return mixed
	 */
	public function fetch ($key, $unserialize = false) {
		clearstatcache();
		
		$cacheFile = $this->_getHashPath($this->prefix.$key);
		
		if (!file_exists($cacheFile)){
			return false;
		}
		else{
			if ( time() <= filemtime($cacheFile) ){
				$cache = @file_get_contents($cacheFile);
				if ( ($this->isSerialize) or ($unserialize) ){
					$cache = unserialize($cache);
				}
				
				return $cache;
			}
			else{
				return false;
			}
		}
	}
	
	/**
	 * 保存缓存
	 * 
	 * <pre>
	 * parSet 参数为数组格式，目前包含的设置为：expireTime = int, serialize = bool
	 * 默认设置为：serialize = 0, serialize = false
	 *
	 * expireTime = 缓存有效时间，以秒为单位
	 * serialize = 是否序列化数据再保存
	 * </pre>
	 *
	 * @access public
	 * @param string $cacheID 缓存标记名
	 * @param mixed $cacheData 缓存内容
	 * @param array $parSet 
	 */
	public function store ($key, $cacheData, $parSet = array()){
		$params = array(
			'expireTime'=> $this->expireTime,
			'serialize'	=> false
		);
		foreach ($parSet as $k => $v) {
			$params[$k] = $v;
		}
		
		if ( ($this->isSerialize) or ($params['serialize']) ){
			$cacheData = serialize($cacheData);
		}
		
		$cacheFile = $this->_getHashPath($this->prefix.$key, false);
		@file_put_contents($cacheFile, $cacheData);
		
		@touch($cacheFile, time() + $params['expireTime']);
	}
	
	/**
	 * 删除缓存
	 *
	 * @access public
	 * @param string $cacheID 缓存标记名
	 * @return void
	 */
	public function delete ($key) {
		$cacheFile = $this->_getHashPath($this->prefix.$key);
		@unlink($cacheFile);
	}
	
	/**
	 * 获取缓存文件的保存地址
	 * 
	 * <pre>
	 * 根据 hashFile 属性决定是否将缓存文件散列保存。
	 * 散列方式为：
	 * 将 cacheID 做MD5运算，然后将密码串按位数拆出，例如第一位为第一层目录名，第二位为第二层目录名
	 * 如某 cacheID的MD5为 aD4stsdfsd3Dtsfg6sdfsid1dn3iidji，而 hashFile = 4，则保存地址为：
	 * $cacheDir/a/D/4/s/aD4stsdfsd3Dtsfg6sdfsid1dn3iidji.$cacheFileExt
	 * </pre>
	 *
	 * @access private
	 * @param string $cacheID 缓存标记名
	 * @param bool $isRead 是否只读，如为true，则不自动创建目录
	 * @return string
	 */
	private function _getHashPath ($cacheID, $isRead = true) {
		if ($this->hashLevel > 0){
			$hashName = md5($cacheID);
			$path = $this->dirPath.DS;
			
			for ($i = 0;$i < $this->hashLevel; $i++){
				$path.= $hashName{$i}.DS;
				
				if (!$isRead){
					if (!is_readable($path)){
						@mkdir($path, 0777);
					}
				}
			}
			
			return $path.$hashName.$this->ext;
		}
		else{
			return $this->dirPath.DS.$cacheID.$this->ext;
		}
	}
}

class Cache_Driver_Memcache extends Cache_Abstract {
	private $_memCache;
	
	function __construct($Params) {
		if (!class_exists('Memcache', false)){
			E_FW::load_File('exception_Cache');
			throw new Exception_Cache('Memcache Object Not Exists.');
		}
		else{
			$this->_memCache = new Memcache;
		}
		
		foreach ($Params as $value) {
			$this->_memCache->addServer($value['host'], $value['port']);
		}
		
		if (!@$this->_memCache->getStats()){
			E_FW::load_File('exception_Cache');
			throw new Exception_Cache('Memcache Service Not Exists.');
		}
	}
	
	function __destruct() {
		$this->_memCache->close();
		$this->_memCache = null;
	}
	
	public function fetch($key, $unserialize = false) {
		$value = $this->_memCache->get($this->prefix.$key);
		
		if ( ($this->isSerialize) or ($unserialize) ){
			$value = unserialize($value);
		}
		
		return $value;
	}
	
	public function store($key, $cacheData, $parSet = array()) {
		$params = array(
			'expireTime'=> $this->expireTime,
			'serialize'	=> false
		);
		foreach ($parSet as $k => $v) {
			$params[$k] = $v;
		}
		
		if ( ($this->isSerialize) or ($params['serialize']) ){
			$cacheData = serialize($cacheData);
		}
		
		return $this->_memCache->set($this->prefix.$key, $cacheData, 0, $params['expireTime']);
	}
	
	public function delete($key) {
		$this->_memCache->delete($this->prefix.$key);
	}
}

class Cache_Driver_Rediska extends Cache_Abstract {
	private $_rediska;
	
	function __construct($Params) {
		$options = array(
		    'servers' => array()
		);
		
		foreach ($Params as $value) {
			$options['servers'][] = array(
				'host' => $value['host'],
				'port' => $value['port']
			);
		}
		
		E_FW::load_File('helper_Rediska_Rediska');
		$this->_rediska = new Rediska($options);
	}
	
	function __destruct() {
		$this->_rediska = null;
	}
	
	public function fetch($key, $unserialize = false) {
		$cacheID = $this->prefix.$key;
		
		$key = new Rediska_Key($cacheID);
		$value = $key->getValue();
		
		if ( ($this->isSerialize) or ($unserialize) ){
			$value = unserialize($value);
		}
		
		return $value;
	}
	
	public function store($key, $cacheData, $parSet = array()) {
		$params = array(
			'expireTime'=> $this->expireTime,
			'serialize'	=> false
		);
		foreach ($parSet as $k => $v) {
			$params[$k] = $v;
		}
		
		$key = new Rediska_Key($this->prefix.$key);
		$key->setExpire($params['expireTime']);
		
		if ( ($this->isSerialize) or ($params['serialize']) ){
			$cacheData = serialize($cacheData);
		}
		
		return $key->setValue($cacheData);
	}
	
	public function delete($key) {
		$key = new Rediska_Key($this->prefix.$key);
		return $key->delete();
	}
}

class Cache_Driver_Apc extends Cache_Abstract {
	function __construct($Params) {
	}
	
	function __destruct() {
	}
	
	public function fetch($key, $unserialize = false) {
		$value = apc_fetch($this->prefix.$key);
		
		if ( ($this->isSerialize) or ($unserialize) ){
			$value = unserialize($value);
		}
		
		return $value;
	}
	
	public function store($key, $cacheData, $parSet = array()) {
		$params = array(
			'expireTime'=> $this->expireTime,
			'serialize'	=> false
		);
		foreach ($parSet as $k => $v) {
			$params[$k] = $v;
		}
		
		if ( ($this->isSerialize) or ($params['serialize']) ){
			$cacheData = serialize($cacheData);
		}
		
		return apc_store($this->prefix.$key, $cacheData, $params['expireTime']);
	}
	
	public function delete($key) {
		return apc_delete($this->prefix.$key);
	}
}
?>