<?php
/**
 * 缓存类
 * 
 * 提供页面/数据的缓存服务
 * 
 * @package class
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2008 eason007<eason007@163.com>
 * @version 1.0.0.20080108
 */

class Class_Cache {
	/**
	 * 缓存目录
	 *
	 * @var string
	 */
	public $cacheDir = null;
	
	/**
	 * 缓存方式
	 * 
	 * 目前只支持文件形式保存
	 * 以后计划增加数据库、mmcache方式
	 * 
	 * @var string
	 */
	public $cacheType = null;
	
	/**
	 * 缓存生命期
	 *
	 * @var int
	 */
	public $cacheTime = 3600;
	
	/**
	 * 缓存散列层次，仅支持 file 方式
	 * 0则为不散列，以cachaid为文件名保存在缓存跟目录中
	 * 大于0则以md5加密cacheid为文件名，保存于多层子目录下
	 * 最大支持32层子目录散列
	 *
	 * @var int
	 */
	public $hashFile = 2;
	
	/**
	 * 缓存文件扩展名
	 *
	 * @var string
	 */
	public $cacheFileExt = '.EFW-Cache';
	
	/**
	 * 是否将缓存数据序列化后保存
	 *
	 * @var bool
	 */
	public $isSerialize = false;
	
	/**
	 * 是否启动缓存
	 * 
	 * 主要用于临时调试使用。
	 * 如设为是，则 getCache 方法永远返回 false
	 *
	 * @var bool
	 */
	public $isDebug = false;
	
	/**
	 * 缓存内容
	 * 
	 * 调用 getCache 方法后，缓存数据除直接返回外，
	 * 同时亦保存到这里
	 *
	 * @var var
	 */
	public $cacheData = null;
	
	
	/**
	 * 类的初始化
	 * 
	 * 可以在实例化缓存类时，同时把设置参数以数组形式传递入内
	 * 可以对类的属性进行全部、部分或不设置
	 * 如：
	 * new Class_Cache();			//不设置
	 * new Class_Cache(array(		//只设置两个类属性
	 * 	'cacheDir' => './Tmp',
	 * 	'cacheTime' => 86400
	 * 	)
	 * );
	 *
	 * @param array $Params
	 */
	function __construct($Params = null) {
		if ( (!is_null($Params)) and is_array($Params) ){
			foreach ($Params as $key => $value) {
				$this->$key = $value;
			}
		}
	}
	
	
	/**
	 * 获取缓存/检查缓存是否存在
	 * 
	 * 
	 *
	 * @param string $cacheID 缓存标记名
	 * @param bool $unserialize 是否反序列化
	 * @return var
	 */
	public function getCache ($cacheID, $unserialize = false) {
		if ($this->isDebug){
			return false;
		}
		
		switch ($this->cacheType){
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
						
						$this->cacheData = $cache;
						
						return $cache;
					}
					else{
						return false;
					}
				}
				
				break;
				
			default:
		}
	}
	
	
	public function setCache ($cacheID, $cacheData, $cacheTime = 0, $serialize = false){
		switch ($this->cacheType){
			case 'file':
				if ( ($this->isSerialize) or ($serialize) ){
					$cacheData = serialize($cacheData);
				}
				
				$cacheFile = $this->_getHashPath($cacheID, false);
				@file_put_contents($cacheFile, $cacheData);
				
				if ($cacheTime == 0) {
					$cacheTime = $this->cacheTime;
				}
				@touch($cacheFile, time() + $cacheTime);
				
				break;
				
			default:
		}
	}
	
	
	public function delCache ($cacheID) {
		switch ($this->cacheType){
			case 'file':
				$cacheFile = $this->_getHashPath($cacheID);
				@unlink($cacheFile);
				
				break;
				
			default:
		}
	}
	
	
	private function _getHashPath ($cacheID, $isRead = true) {
		if ($this->hashFile > 0){
			$hashName = md5($cacheID);
			$path = $this->cacheDir.DS;
			
			for ($i = 0;$i < $this->hashFile; $i++){
				$path.= $hashName{$i}.DS;
				
				if (!$isRead){
					if (!is_readable($path)){
						@mkdir($path, 0777);
					}
				}
			}
			
			return $path.$hashName.$this->cacheFileExt;
		}
		else{
			return $this->cacheDir.DS.$cacheID.$this->cacheFileExt;
		}
	}
}

?>