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
class Controller_Page{
	function __construct(){

	}

    function actionCache(){
    	$output = E_FW::load_Class('cache_OutputAnalytics');
    	
    	if (!$output->start('blog_index')){
	    	$this->_ModelBlog = E_FW::load_Class('Model_Blog');
	    	
			//数据库操作
			$this->_ModelBlog->where('');
			$this->_ModelBlog->order('id desc');
			$this->_ModelBlog->limit(30);
			$news = $this->_ModelBlog->select(array(
				'isCount'	=> true
			));
			
			var_dump($news);
			$output->end();
    	}
    }
}
?>