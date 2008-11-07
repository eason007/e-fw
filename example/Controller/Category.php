<?php
/**
 * 默认控制器
 * 
 * 类名以 [目录名]_[类名] 方式命名
 * 
 * @package Example
 *
 */
class Controller_Category{
	/**
	 * 类构造方法
	 * 
	 * 可用于定义一些公用的东西
	 *
	 */
	function __construct(){
		/**
		 * <pre>
		 * 这里使用了框架中的 cache 类。
		 * cache方案为：
		 * 当 get 参数中 debug != 'abc123' 时，则使用cache，过期时间为默认的1小时
		 * 如 get 参数中 debug == 'abc123' 时，则不使用cache
		 * </pre>
		 */
        $this->_cacheClass = E_FW::load_Class("class_Cache");
		$this->_cacheClass->cacheDir 	= "html";
		$this->_cacheClass->cacheType 	= "file";
		$this->_cacheClass->cacheFileExt= ".html";
		$this->_cacheClass->hashFile	= 0;
	}
 
	
	/**
	 * 控制器方法
	 *
	 */
    function actionIndex(){
    	if (empty($_GET['page'])){
			$page = 0;
		}
		else{
			$page = intval($_GET['page']) - 1;
		}
		
    	if ($_GET['debug'] == 'abc123'){
    		$this->_cacheClass->isDebug = true;
    	}
    	if ($this->_cacheClass->getCache('list-'.$page)){
    		echo $this->_cacheClass->cacheData;
			exit;
    	}
    	else{
    		$this->_ModelCategory = clsExample::getModel('Model_Category');
    	}

    	
		//数据库操作
		$this->_ModelCategory->field = 'id, title';
		$this->_ModelCategory->order = 'id desc';
		$this->_ModelCategory->limit = array(
											'offset' => $page,
											'length' => 10
		);
		$category = $this->_ModelCategory->select();
		
		
		//模版操作
		$tpl = E_FW::get_view();
		$tpl->assign('category', $category);
		$html = $tpl->fetch('list.html');
		echo $html;
		
		
		//判断是否需要保存
		if ($_GET['debug'] != 'abc123'){
			$this->_cacheClass->setCache('list-'.$page, $html);
		}
    }
}
?>