<?php
/**
 * @package Example
 * @subpackage Controller
 */

/**
 * 数据库控制器
 * 
 * 类名以 [目录名]_[类名] 方式命名
 * 
 * @package Example
 * @subpackage Controller
 */
class Controller_Database{
	function __construct(){

	}
	
    public function actionRead(){
    	$this->_ModelBlog = E_FW::load_Class('Model_Blog');
    	
		//数据库操作
		$this->_ModelBlog->where('');
		$this->_ModelBlog->order('id desc');
		$this->_ModelBlog->limit(30);
		$news = $this->_ModelBlog->select(array(
			'isCount'	=> true
		));
		
		print_r($news);
    }

	public function actionCreate () {
		$insert = array(
			'category_id' 	=> '2',
			'category_title'=> 'qwe',
			'title'			=> 'hello word!',
			'content'		=> 'single push data'
		);

		$this->_ModelBlog = E_FW::load_Class('Model_Blog');

		print_r($this->_ModelBlog->insert($insert));
	}
	
	public function actionUpdate () {
		$update = array(
			'id' 		 => '2',
			'category_id'=> '1'
		);

		$this->_ModelBlog = E_FW::load_Class('Model_Blog');

		print_r($this->_ModelBlog->update($update));
	}
	
	public function actionDelete () {
		$this->_ModelBlog = E_FW::load_Class('Model_Blog');
		
		$this->_ModelBlog->where(1);

		print_r($this->_ModelBlog->del());
	}
}
?>