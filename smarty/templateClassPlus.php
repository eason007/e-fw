<?php
E_FW::load_File("smarty/Smarty.class.php");

class smarty_templateClassPlus extends Smarty {

	public function __construct () {
		$this->template_dir		= E_FW::get_Config("VIEW/templateDir");
		$this->compile_dir		= E_FW::get_Config("VIEW/compile_dir");
	}
}
?>
