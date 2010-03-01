<?php
/**
 * @package Example
 * @subpackage Controller
 */

/**
 * 数据控制器
 * 
 * 类名以 [目录名]_[类名] 方式命名
 * 
 * @package Example
 * @subpackage Controller
 */
class Controller_Data{
	function __construct(){

	}
	
	/**
	 * 演示数据过滤
	 */
	public function actionFilter () {
		echo 'source data:';
		$data = array(
    		'title' => '1q1123@',
    		'tag'	=> 'qwer1',
    		'content' => '阿萨德飞1',
    		'date'	=> '1300-11-11a',
    		'postTime' => '12345678900a'
    	);
    	var_dump($data);
    	
		//获取数据对象
    	$validator = E_FW::load_Class('data_Core');
    	//设定过滤规则
    	$filterRule = array(
    		'title' => array(
    			'rule' 	=> 'Alnum'
    		),
    		'tag' 	=> array (
	  			'rule' 	=> 'English'
	  		),
	  		'date' 	=> array (
	  			'rule' 	=> 'Date'
	  		),
	  		'content' => array(
	  			'rule'	=> 'Chinese'
	  		),
	  		'postTime' => array(
	  			'rule' 	=> 'Number'
	  		)
    	);
    	
    	$validator->set($filterRule, null, $data);
    	$validator->filter();
    	
    	echo 'format data:';
    	var_dump($data);
	}
	
	/**
	 * 演示数据校验
	 */
	public function actionValidator () {
		echo 'source data:';
		$data = array(
    		'title' 	=> '1q1123@',
    		'tag'		=> '',
    		'content' 	=> '阿萨德飞1',
    		'date'		=> '1300-11-11a',
    		'postTime' 	=> '1234',
			'test'		=> 'abc'
    	);
    	var_dump($data);
    	
		//获取数据对象
    	$validator = E_FW::load_Class('data_Core');
		//设定校验规则
    	$validatorRule = array(
    		'title' => array (
	  			'rule' 	=> 'Alnum',
	  			'min'	=> 4,
	  			'max'	=> 20
	  		),
	  		'tag' 	=> array (
	  			'require' 	=> true,
	  			'rule' 		=> 'English'
	  		),
	  		'date' 	=> array (
	  			'require' 	=> false,
	  			'rule' 		=> 'Date'
	  		),
	  		'content' => array(
	  			'rule'	=> 'Chinese',
	  			'min'	=> 4,
	  			'max'	=> 500
	  		),
	  		'postTime' => array(
	  			'min'	=> 11,
	  			'rule' 	=> 'Number'
	  		),
	  		'author'	=> array(
	  			'max'	=> 50
	  		)
    	);
    	
    	$validator->set(null, $validatorRule, $data);
    	$validator->validate();
    	
    	echo 'unknown data:';
    	var_dump($validator->unknown);
    	
    	echo 'invalid data:';
    	var_dump($validator->invalid);
    	
    	echo 'missing data:';
    	var_dump($validator->missing);
	}
}
?>