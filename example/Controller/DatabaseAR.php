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
class Controller_DatabaseAR{
	function __construct(){

	}
	
	function actionRead () {
		//list
		E_FW::load_File('Model_BlogActiveRecord');
		
		$test = Model_BlogActiveRecord::find('Model_BlogActiveRecord', array(
			'where' => 'id < 100'
		));
		
		foreach ($test as $value) {
			echo 'id:'.$value->id.' = '.$value->title.'<br />';
			echo 'Category-id:'.$value->category_id.' = '.$value->Category->title.'<p />';
		}
	}
	
	function actionSave () {
		//insert
		$blog = E_FW::load_Class('Model_BlogActiveRecord', true, array(
			'id'			=> '2',
			'category_id' 	=> 4,
			'title' 		=> 'a3b'
		));
		$blog->category_id = 11;
		
		var_dump($blog->save());
		var_dump($blog);
	}
	
	function actionDelete () {
		//del
		E_FW::load_File('Model_BlogActiveRecord');
		
		$blog = Model_BlogActiveRecord::destroyWhere('Model_BlogActiveRecord', array(
			'where' => 1
		));
		
		var_dump($blog);
	}
}
?>