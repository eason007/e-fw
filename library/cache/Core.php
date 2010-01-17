<?php
/**
 * @package Cache
 */

/**
 * 缓存类
 * 
 * 提供缓存服务，目前支持文件及memcache两种方式
 * memcache使用memcache扩展实现
 * 
 * @package Cache
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2008 eason007<eason007@163.com>
 * @version 2.0.1.20100117
 */
class Cache_Core {
	/**
	 * 缓存配置
	 * 
	 * <pre>
	 * 格式：array(
	 * 		'File' => array (
	 * 			'dir' => '',
	 * 			'hashFile' => '',
	 * 			'ext' => ''
	 * 		),
	 * 		'Memcache' => array (
	 * 			array(
	 *	 			'host' => '',
	 * 				'port' => ''
	 * 			)
	 * 		)
	 * )
	 * 
	 * [File][dir]
	 * 缓存目录
	 * 
	 * [File][hashFile]
	 * 缓存散列层次
	 * 仅支持 file 方式
	 * 0则为不散列，以 cacheid 为文件名保存在缓存根目录中
	 * 大于0则以md5加密 cacheid 为文件名，保存于多层子目录下
	 * 最大支持32层子目录散列
	 * 
	 * [File][ext]
	 * 缓存文件扩展名
	 * 
	 * [Memcache][host]
	 * Memcache的服务器地址
	 * 
	 * [Memcache][port]
	 * Memcache的服务端口
	 * 
	 * Memcache支持多台服务器
	 * </pre>
	 *
	 * @var array
	 */
	public $cacheParams = array(
		'File' 		=> array(
			'dir' 		=> null,
			'hashFile'	=> 2,
			'ext'		=> '.EFW-Cache'
		),
		'Memcache' 	=> array(
			array(
				'host' => '127.0.0.1',
				'port' => '11211'
			)
		)
	);
	
	/**
	 * 缓存方式
	 * 
	 * <pre>
	 * 'file' = 以文件方式保存
	 * 'memcache' = 以memcache方式保存
	 * </pre>
	 * 
	 * @var string
	 */
	public $type = null;
	
	/**
	 * 缓存生命期
	 * 
	 * 以秒为单位
	 *
	 * @var int
	 */
	public $expireTime = 3600;
	
	/**
	 * 是否将缓存数据序列化后保存
	 *
	 * @var bool
	 */
	public $isSerialize = false;
	
	/**
	 * 是否启动缓存
	 * 
	 * <pre>
	 * 主要用于临时调试使用。
	 * 如设为是，则 getCache 方法永远返回 false
	 * </pre>
	 *
	 * @var bool
	 */
	public $isDebug = false;
	
	private $_memCache = null;
	
	/**
	 * 类的初始化
	 * 
	 * <pre>
	 * 可以在实例化缓存类时，同时把设置参数以数组形式传递入内
	 * 可以对类的属性进行全部、部分或不设置
	 * 如：
	 * new Class_Cache();			//不设置
	 * new Class_Cache(array(		//只设置两个类属性
	 * 			'type' => 'file',
	 * 			'expireTime' => 86400
	 * 		)
	 * );
	 * </pre>
	 *
	 * @param array $Params
	 */
	function __construct($Params = null) {
		if (is_null($Params)){
			$Params = E_FW::get_Config('CACHE');
		}
		
		foreach ($Params as $key => $value) {
			$this->$key = $value;
		}
		
		$this->type = strtolower($this->type);
		
		if ($this->type == 'memcache') {
			$this->_memCache = new Memcache;
			
			foreach ($this->cacheParams['Memcache'] as $value) {
				$this->_memCache->addServer($value['host'], $value['port']);
			}
		}
	}
	
	
	/**
	 * 获取缓存/检查缓存是否存在
	 * 
	 * <pre>
	 * 如数据存在并有效，则读出并返回。
	 * 如数据存在但过期，或者不存在，或者 isDebug 属性为 true，则返回 false
	 * </pre>
	 *
	 * @param string $cacheID 缓存标记名
	 * @param bool $unserialize 是否反序列化
	 * @return mixed
	 */
	public function getCache ($cacheID, $unserialize = false) {
		if ($this->isDebug){
			return false;
		}
		
		switch ($this->type){
			case 'file':
				clearstatcache();
				
				$cacheFile = $this->_getHashPath($cacheID);
				
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
				
				break;
			
			case 'memcache':
				return $this->_memCache->get($cacheID);
				break;
				
			default:
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
	 * @param string $cacheID 缓存标记名
	 * @param mixed $cacheData 缓存内容
	 * @param array $parSet 
	 */
	public function setCache ($cacheID, $cacheData, $parSet = array()){
		$params = array(
			'expireTime'=> $this->expireTime,
			'serialize'	=> false
		);
		foreach ($parSet as $key => $value) {
			$params[$key] = $value;
		}
		
		switch ($this->type){
			case 'file':
				if ( ($this->isSerialize) or ($params['serialize']) ){
					$cacheData = serialize($cacheData);
				}
				
				$cacheFile = $this->_getHashPath($cacheID, false);
				@file_put_contents($cacheFile, $cacheData);
				
				if ($cacheTime == 0) {
					$cacheTime = $this->$params['expireTime'];
				}
				@touch($cacheFile, time() + $cacheTime);
				
				break;
				
			case 'memcache':
				$this->_memCache->set($cacheID, $cacheData, 0, $params['expireTime']);
				break;
				
			default:
		}
	}
	
	
	/**
	 * 删除缓存
	 *
	 * @param string $cacheID 缓存标记名
	 */
	public function delCache ($cacheID) {
		switch ($this->type){
			case 'file':
				$cacheFile = $this->_getHashPath($cacheID);
				@unlink($cacheFile);
				
				break;
				
			case 'memcache':
				return $this->_memCache->delete($cacheID);
				break;
				
			default:
		}
	}
	
	
	/**
	 * 获取缓存文件的保存地址
	 * 
	 * <pre>
	 * 根据 hashFile 属性决定是否将缓存文件散列保存。
	 * 散列方式为：
	 * 将 cacheID 做MD5运算，然后将密码串按位数拆出，例如第一位为第一层目录名，第二位为第二层目录名
	 * 如某 cacheID的MD5为 aD4stsdfsd3Dtsfg6sdfsid1dn3iidji，而 hashFile = 4，则保存地址为：
	 * $cacheDir./a/D/4/s/aD4stsdfsd3Dtsfg6sdfsid1dn3iidji.$cacheFileExt
	 * </pre>
	 *
	 * @param string $cacheID 缓存标记名
	 * @param bool $isRead 是否只读，如为true，则不自动创建目录
	 * @return string
	 */
	private function _getHashPath ($cacheID, $isRead = true) {
		if ($this->cacheParams['File']['hashFile'] > 0){
			$hashName = md5($cacheID);
			$path = $this->cacheParams['File']['dir'].DS;
			
			for ($i = 0;$i < $this->cacheParams['File']['hashFile']; $i++){
				$path.= $hashName{$i}.DS;
				
				if (!$isRead){
					if (!is_readable($path)){
						@mkdir($path, 0777);
					}
				}
			}
			
			return $path.$hashName.$this->cacheParams['File']['ext'];
		}
		else{
			return $this->cacheParams['File']['dir'].DS.$cacheID.$this->$this->cacheParams['File']['ext'];
		}
	}
}

?>