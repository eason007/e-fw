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
		echo '1>';
		$news = $this->_ModelCategory
								->select(array(
									'link' => ''
								));
		//print_r($news);
		
		echo '2>';
		$news = $this->_ModelCategory
								->select(array(
									'link' => ''
								));
		//print_r($news);
		
		echo '3>';
		$news = $this->_ModelCategory
								->where(1)
								->one(array(
									'link' => ''
								));
		print_r($news);
		
		echo '<br>4>';
		$news = $this->_ModelCategory
								->field('title, blog')
								->where(1)
								->one(array(
									'link' => ''
								));
		print_r($news);
		
		echo '<br>5>';
		$news = $this->_ModelCategory
								->field('title, blog')
								->where(4)
								->one(array(
									'link' => ''
								));
		print_r($news);
								
		echo '<br>6>';
		$news = $this->_ModelCategory
								->field('title, blog')
								->where(1)
								->one(array(
									'link' => ''
								));
		print_r($news);
    }

	function actionPost () {
		$insert = array(
			'title'		=> 'qwe1',
			'hasMany'	=> array(
				array (
					'category_id' 	=> '2',
					'category_title'=> 'qwe',
					'title'			=> 'hello word!',
					'content'		=> 'link push data',
				)
			),
			'hasOne' => array(
				'title' => '你bc'
			)
		);

		$this->_ModelCategory = E_FW::load_Class('Model_Category');

		print_r($this->_ModelCategory->insert($insert));
	}
	
	function actionUpdate () {
		$this->_ModelCategory = E_FW::load_Class('Model_Category');

		$this->_ModelCategory
							->where(4)
							->update(array(
								'title' => '4321'
							));
	}
	
	function actionDelete () {
		$this->_ModelCategory = E_FW::load_Class('Model_Category');
		
		$this->_ModelCategory->where(43);

		print_r($this->_ModelCategory->del());
	}
	
	function actionTest () {
		$this->actionIndex();
		echo '<br>====================<br>';
		$this->actionUpdate();
		echo '<br>====================<br>';
		$this->actionIndex();
	}
}
?>