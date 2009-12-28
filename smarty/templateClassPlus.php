<?php
/**
 * @package Smarty
 */

E_FW::load_File('smarty/Smarty.class.php');

/**
 * Enter description here...
 *
 * @package Smarty
 */
class smarty_templateClassPlus extends Smarty {

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
}
?>
