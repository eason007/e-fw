<?php
/**
 * 默认控制器
 * 
 * 类名以 [目录名]_[类名] 方式命名
 * 
 * @package Example
 * @subpackage Controller
 */
class Controller_Category{
	/**
	 * 类构造方法
	 * 
	 * 可用于定义一些公用的东西
	 *
	 */
	function __construct(){
		
	}
 
	
	/**
	 * 控制器方法
	 *
	 */
    function actionIndex(){
    	$this->_ModelCategory = E_FW::load_Class('Model_Category');
    	
    	
		//数据库操作
		$this->_ModelCategory->order = 'id desc';
		$this->_ModelCategory->limit = 30;
		$news = $this->_ModelCategory->select(array(
			'link' => 'hasMany'
		));
		
		print_r($news);
    }

	function actionPost () {
		$insert = array(
			'title'		=> 'qwe',
			'hasMany'	=> array(
				array (
					'category_id1' 	=> '2',
					'category_title'=> 'qwe',
					'title'			=> 'hello word!',
					'content'		=> 'link push data',
				)
			)
		);

		$this->_ModelCategory = E_FW::load_Class('Model_Category');

		print_r($this->_ModelCategory->insert($insert));
	}
	
	function actionUpdate () {
		$update = array(
			'id' 	=> '1',
			'title'	=> '你好bc',
			'hasMany' => array(
				array(
					'id'	=> 1,
					'category_title' => '你bc'
				)
			)
		);

		$this->_ModelCategory = E_FW::load_Class('Model_Category');

		print_r($this->_ModelCategory->update($update));
	}
}
?>