<?php
/**
 * @package Example
 * @subpackage Controller
 */

/**
 * 默认控制器
 * 
 * 类名以 [目录名]_[类名] 方式命名
 * 
 * @package Example
 * @subpackage Controller
 */
class Controller_Index{
	/**
	 * 类构造方法
	 * 
	 * 可用于定义一些公用的东西
	 *
	 */
	function __construct(){

	}

	function actionIndex() {
		//获取模板对象
    	$tpl = E_FW::get_view();
    	
    	echo $tpl->fetch('index.html');
	}
}
?>