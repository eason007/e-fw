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
	
	function actionReadWithActiveRecord () {
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
	
	function actionSaveWithActiveRecord () {
		//insert
		$blog = E_FW::load_Class('Model_BlogActiveRecord', true, array(
			'id'	=> '10a',
			'category_id' => 4,
			'title' => 'a3b'
		));
		
		var_dump($blog->save());
		var_dump($blog);
	}
	
	function actionDeleteWithActiveRecord () {
		//update
		E_FW::load_File('Model_BlogActiveRecord');
		
		$blog = Model_BlogActiveRecord::find('Model_BlogActiveRecord', array(
			'where' => 1
		));
		$blog->title = 'test_update';
		
		var_dump($blog->save());
		var_dump($blog);
	}
}
?>