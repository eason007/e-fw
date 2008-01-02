<?php
class Class_Cache {
	public $cacheDir = null;
	public $cacheType = null;
	public $cacheTime = 3600;
	public $cacheData = null;
	public $cacheFileExt = ".EFW-Cache";
	public $isSerialize = false;
	
	function __construct($Params = null) {
		if (!is_null($Params)){
			$this->cacheDir	 	= $Params["cacheDir"] ? $Params["cacheDir"] : null;
			$this->cacheType 	= $Params["cacheType"] ? $Params["cacheType"] : null;
			$this->cacheTime 	= $Params["cacheTime"] ? $Params["cacheTime"] : 3600;
			$this->isSerialize 	= $Params["isSerialize"] ? $Params["isSerialize"] : false;
		}
	}
	
	
	public function getCache ($cacheID, $unserialize = false) {
		switch ($this->cacheType){
			case "file":
				clearstatcache();
				
				$cacheFile = $this->cacheDir.DS.$cacheID.$this->cacheFileExt;
				
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
				
				$cacheFile = $this->cacheDir.DS.$cacheID.$this->cacheFileExt;
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
				@unlink($this->cacheDir.DS.$cacheID.$this->cacheFileExt);
				
				break;
				
			default:
		}
	}
}

?>