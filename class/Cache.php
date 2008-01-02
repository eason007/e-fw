<?php
class Class_Cache {
	public $cacheDir = null;
	public $cacheType = null;
	public $cacheTime = 3600;
	public $hashFile = 2;
	public $cacheFileExt = ".EFW-Cache";
	public $isSerialize = false;
	public $isDebug = false;
	
	public $cacheData = null;
	
	function __construct($Params = null) {
		if (!is_null($Params)){
			$this->cacheDir	 	= $Params["cacheDir"] ? $Params["cacheDir"] : null;
			$this->cacheType 	= $Params["cacheType"] ? $Params["cacheType"] : null;
			$this->cacheTime 	= $Params["cacheTime"] ? $Params["cacheTime"] : 3600;
			$this->cacheFileExt	= $Params["cacheFileExt"] ? $Params["cacheFileExt"] : ".EFW-Cache";
			$this->isSerialize 	= $Params["isSerialize"] ? $Params["isSerialize"] : false;
			$this->hashFile 	= $Params["hashFile"] ? $Params["hashFile"] : 2;
			$this->isDebug 		= $Params["isDebug"] ? $Params["isDebug"] : false;
		}
	}
	
	
	public function getCache ($cacheID, $unserialize = false) {
		if ($this->isDebug){
			return false;
		}
		
		switch ($this->cacheType){
			case "file":
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
			case "file":
				if ( ($this->isSerialize) or ($serialize) ){
					$cacheData = serialize($cacheData);
				}
				
				$cacheFile = $this->_getHashPath($cacheID);
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
			case "file":
				$cacheFile = $this->_getHashPath($cacheID);
				@unlink($cacheFile);
				
				break;
				
			default:
		}
	}
	
	
	private function _getHashPath ($cacheID) {
		if ($this->hashFile > 0){
			$hashName = md5($cacheID);
			$path = $this->cacheDir.DS;
			
			for ($i = 0;$i < $this->hashFile; $i++){
				$path.= $hashName{$i}.DS;
				
				if (!is_readable($path)){
					@mkdir($path, 0777);
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