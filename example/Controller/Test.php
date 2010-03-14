<?php
/**
 * @package Example
 * @subpackage Controller
 */

/**
 * 页面控制器
 * 
 * 类名以 [目录名]_[类名] 方式命名
 * 
 * @package Example
 * @subpackage Controller
 */
class Controller_Test{
	function __construct(){

	}

    function actionIndex(){
    	$w = E_FW::load_Class('writer_File', true, './test.txt');
    	$w2 = E_FW::load_Class('writer_File', true, './test2.txt');
    	$w3 = E_FW::load_Class('writer_Db', true, array(
    		'className'	=> 'Model_Log',
    		'mapping'	=> array(
	    		'timestamp' => 'time',
	    		'message' 	=> 'msg',
	    		'priority' 	=> 'lvs',
	    		'priorityName' => 'lvsName',
	    		'pid' 		=> 'pid'
	    	)
    	));
    	
    	$a = E_FW::load_Class('log_Core');
    	
    	$a->addField('pid', getmypid());
    	$a->addWriter($w);
    	$a->addWriter($w2);
    	$a->addWriter($w3);
    	var_dump($a->info('123'));
    }
}
?>