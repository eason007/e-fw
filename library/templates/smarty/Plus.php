<?php
/**
 * 
 * @package Templates
 * @subpackage Smarty
 */

E_FW::load_File('templates/smarty/Smarty.class.php');

/**
 * Enter description here...
 *
 * @package Templates
 * @subpackage Smarty
 */
class Templates_Smarty_Plus extends Smarty {

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
