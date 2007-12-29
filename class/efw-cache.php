<?php
class EFW_Cache {
	public $cacheDir = null;
	public $cacheType = null;
	public $cacheTime = 0;
	public $isSerialize = false;
	
	protected $exCacheTime = array();
	
	function __construct($Params = null) {
		if (!is_null($Params)){
			$this->cacheDir	 	= $Params["cacheDir"] ? $Params["cacheDir"] : null;
			$this->cacheType 	= $Params["cacheType"] ? $Params["cacheType"] : null;
			$this->cacheTime 	= $Params["cacheTime"] ? $Params["cacheTime"] : 3600;
			$this->isSerialize 	= $Params["isSerialize"] ? $Params["isSerialize"] : false;
		}
	}
	
	
	public function chkCache ($cacheID) {
		switch ($this->cacheType){
			case "file":
				clearstatcache();
				
				$cacheFile = $this->cacheDir.DS.$cacheID.".E-FW-Cache";
				
				if (!file_exists($cacheFile)){
					return false;
				}
				else{
					if (isset($this->exCacheTime[$cacheID])) {
						$overTime = $this->exCacheTime[$cacheID];
					}
					else{
						$overTime = $this->cacheTime;
					}
					
					if ( (time() - filemtime($cacheFile)) <= $overTime){
						return true;
					}
					else{
						return false;
					}
				}
				
				break;
				
			default:
		}
	}
	
	
	public function getCache ($cacheID, $unserialize = false) {
		switch ($this->cacheType){
			case "file":
				clearstatcache();
				
				$cacheFile = $this->cacheDir.DS.$cacheID.".E-FW-Cache";
				
				if (!file_exists($cacheFile)){
					return false;
				}
				else{
					if (isset($this->exCacheTime[$cacheID])) {
						$overTime = $this->exCacheTime[$cacheID];
					}
					else{
						$overTime = $this->cacheTime;
					}
					
					if ( (time() - filemtime($cacheFile)) <= $overTime){
						$cache = file_get_contents($cacheFile);
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
				
			default:
		}
	}
	
	
	public function setCache ($cacheID, $cacheData, $cacheTime = 0, $serialize = false){
		switch ($this->cacheType){
			case "file":
				if ( ($this->isSerialize) or ($serialize) ){
					$cacheData = serialize($cacheData);
				}
				
				$cacheFile = $this->cacheDir.DS.$cacheID.".E-FW-Cache";
				file_put_contents($cacheFile, $cacheData);
				
				if ($cacheTime > 0) {
					$this->exCacheTime[$cacheID] = $cacheTime;
				}
				
				break;
				
			default:
		}
	}
	
	
	public function delCache ($cacheID) {
		switch ($this->cacheType){
			case "file":
				@unlink($this->cacheDir.DS.$cacheID.".E-FW-Cache");
				
				break;
				
			default:
		}
	}
}

?>