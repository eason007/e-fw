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
    	$tpl = E_FW::get_view();
    	//var_dump($tpl);
    	
    	$validator = E_FW::load_Class('data_Core');
    	$f = array(
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
    	$v = array(
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
	  		)
    	);
    	$d = array(
    		'title' => '1q1123@',
    		'tag'	=> 'qwer1',
    		'content' => '阿萨德飞1',
    		'date'	=> '1300-11-11a',
    		'postTime' => '12345678900a'
    	);
    	$validator->set($f, $v, $d);
    	$validator->filter();
    	var_dump($d);
    	
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
			
	
			print_r($news);
			$output->end();
    	}

		//throw new MyException('fuck');
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
	
	function actionTest () {
		E_FW::load_File('Model_BlogActiveRecord');
		
		$test = Model_BlogActiveRecord::find('Model_BlogActiveRecord', array(
			'where' => 'id < 100'
		));
		
		foreach ($test as $value) {
			echo 'id:'.$value->id.' = '.$value->title.'<br />';
			echo 'Category-id:'.$value->category_id.' = '.$value->Category->title.'<p />';
		}
	}
}
?>