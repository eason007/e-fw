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
 
	
	/**
	 * 控制器方法
	 *
	 */
    function actionIndex(){
    	$this->_ModelBlog = E_FW::load_Class('Model_Blog');
    	
    	
		//数据库操作
		$this->_ModelBlog->where('');
		$this->_ModelBlog->order('id desc');
		$this->_ModelBlog->limit(30);
		$news = $this->_ModelBlog->select(array(
			'isCount'	=> true
		));
		

		print_r($news);

		//throw new MyException('fuck', 0);
    }

	function actionPost () {
		$insert = array(
			'category_id' 	=> '2',
			'category_title'=> 'qwe',
			'title'			=> 'hello word!',
			'content'		=> 'single push data'
		);

		$this->_ModelBlog = E_FW::load_Class('Model_Blog');

		print_r($this->_ModelBlog->insert($insert));
	}
	
	function actionUpdate () {
		$update = array(
			'id' 		 => '2',
			'category_id'=> '1'
		);

		$this->_ModelBlog = E_FW::load_Class('Model_Blog');

		print_r($this->_ModelBlog->update($update));
	}
	
	function actionDelete () {
		$this->_ModelBlog = E_FW::load_Class('Model_Blog');
		
		$this->_ModelBlog->where('id in (20,19,18,17,16,15,14)');

		print_r($this->_ModelBlog->del());
	}
}
?>