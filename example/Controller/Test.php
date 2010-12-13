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
    	$w4 = E_FW::load_Class('writer_Syslog', true, array(
    		'application'	=> 'E_FW-DEMO'
    	));
    	
    	$a = E_FW::load_Class('log_Core');
    	
    	$a->addField('pid', getmypid());
    	$a->addWriter($w);
    	$a->addWriter($w2);
    	$a->addWriter($w3);
    	$a->addWriter($w4);
    	var_dump($a->info('abc'));
    }
    
    function actionTest () {
    	$options = array(
		    //'namespace' => 'Application_',
		    'servers'   => array(
		       array('host' => '127.0.0.1', 'port' => 6379)
		    )
		);
		
		E_FW::load_File('helper_Rediska_Rediska');
		$rediska = new Rediska($options);
		
		$key = new Rediska_Key('mykey');
		//$key->setValue('value');
		
		echo $key->getValue();
    }
}
?>