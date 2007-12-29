<?php
E_FW::load_File("phplib/templateClass.php");

class phplib_templateClassPlus {
	public $TEMPLATE_PATH = "";

	public $meta = array(
		"Keyword" => "",
		"Desc" => "",
		"Title" => "",
		"Robots" => ""
	);

	public $tpl = null;

	public function __construct () {
		$this->TEMPLATE_PATH = $GLOBALS[G_E_FW_VAR]["VIEW"]["templateDir"];

		$this->tpl = new TEMPLATE_PHPLIB_CLASS($this->TEMPLATE_PATH);
	}


	public function getFinish () {
		$replaceVar = $this->tpl->getUndefined("index", "_");
		foreach($replaceVar as $varName){
			$tmp = explode("_", $varName);

			switch ($tmp[1]){
				case "loadFile":
					$this->tpl->setFile("_".$tmp[2]."Content", "_".$tmp[2].".html"); 

					$this->tpl->parse($varName, "_".$tmp[2]."Content");

					$this->tpl->unsetVar("_".$tmp[2]."Content");

					break;
					
				default:
					if (count($tmp) == 3){
						E_FW::executeAction("Controller_".$tmp[1], "".$tmp[2]);

						$this->tpl->parse($varName, "_".strtolower($tmp[2])."Content"); 

						$this->tpl->unsetVar("_".strtolower($tmp[2])."Content");
					}
					break;
			}
		}

		foreach($this->meta as $varKey => $varName){
			$this->tpl->setVar("page".$varKey, $varName);
		}

		//模板最终处理
		$this->tpl->parse("out", "index");
		$page = $this->tpl->getVar("out");

		return $page;
	}
}
?>
