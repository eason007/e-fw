<?php
/**
 * 默认控制器
 * 
 * 类名以 [目录名]_[类名] 方式命名
 * 
 * @package Example
 *
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
}
?>