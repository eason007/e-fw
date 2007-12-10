<?php
class EFW_Cache {
	public $type = null;
	public $dir  = null;
	public $path = null;
	
	function __construct($Params) {
		$this->type = $Params["type"];
		$this->dir 	= $Params["cacheDir"];
		
		$uri = $_SERVER['REQUEST_URI'];
		$cacheFile = strtolower(urlencode($uri));
		
		$this->path = $this->dir.DS.date("Y", time()).DS.date("m-d", time()).DS.date("H", time()).DS.$cacheFile;
		$this->chkDir();
	}

	
	public function run () {
		clearstatcache();
		
		switch ($this->type){
			case "html":
				if (is_file($this->path)){
					$pageContent = file_get_contents($this->path);
					echo $pageContent;
					
					return true;
				}
				else{
					return false;
				}
				
				break;
				
			case "database":
				break;
		}
	}
	
	
	public function saveCache ($content) {
		return file_put_contents($this->path, $content);
	}
	
	
	private function chkDir () {
		$hashDir = str_replace($this->dir, "", $this->path);
		$tmp 	 = explode(DS, $hashDir);
		$folder	 = $this->dir;
		
		for ($i = 1; $i < count($tmp) - 1; $i++){
			$folder.= DS.$tmp[$i];
			
			if (!is_readable($folder)){
				mkdir($folder, 0777);
			}
		}
	}
}

?>