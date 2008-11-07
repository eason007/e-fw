<?php
//引入 model 基类
E_FW::load_File('class_TableDataGateway');

/**
 * 定义 category 表的 model 类
 * 
 * <pre>
 * 类名以 [目录名]_[类名] 方式命名
 * 需继承自 class_TableDataGateway 类
 * </pre>
 *
 * @package Example
 */
class Model_Category extends class_TableDataGateway{
	var $tableName 	= 'category';
	var $primaryKey = 'id';
	
	var $hasMany = array(
		'tableClass' 	=> 'Model_Blog_ForCategory',
		'joinKey'		=> 'categoryID',
		'mappingName'	=> 'Blog'
	);
}


class Model_Blog_ForCategory extends class_TableDataGateway{
	var $tableName 	= 'blog';
	var $primaryKey = 'id';
}
?>