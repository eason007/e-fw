<?php
/**
 * @package Templates
 * @subpackage PHPLib
 */

E_FW::load_File("templates/phplib/phplib.php");

/**
 * Enter description here...
 *
 * @package Templates
 * @subpackage PHPLib
 */
class Templates_PHPLib_Plus extends Template {
	public $meta = array(
		"Keyword" => "",
		"Desc" => "",
		"Title" => "",
		"Robots" => ""
	);

	/**
	 * Enter description here...
	 *
	 * @see Template
	 */
	public function __construct () {
		$viewConfig = E_FW::get_Config('VIEW');

        if (is_array($viewConfig)) {
            foreach ($viewConfig as $key => $value) {
                if (isset($this->$key)) {
                    $this->$key = $value;
                }
            }
        }
	}


	public function getFinish () {
		$replaceVar = $this->getUndefined("index", "_");
		foreach($replaceVar as $varName){
			$tmp = explode("_", $varName);

			switch ($tmp[1]){
				case "loadFile":
					$this->setFile("_".$tmp[2]."Content", "_".$tmp[2].".html"); 

					$this->parse($varName, "_".$tmp[2]."Content");

					$this->unsetVar("_".$tmp[2]."Content");

					break;
					
				default:
					if (count($tmp) == 3){
						E_FW::execute_Action("Controller_".$tmp[1], "".$tmp[2]);

						$this->parse($varName, "_".strtolower($tmp[2])."Content"); 

						$this->unsetVar("_".strtolower($tmp[2])."Content");
					}
					break;
			}
		}

		foreach($this->meta as $varKey => $varName){
			$this->setVar("page".$varKey, $varName);
		}

		//模板最终处理
		$this->parse("out", "index");
		$page = $this->getVar("out");

		return $page;
	}
}
?>
