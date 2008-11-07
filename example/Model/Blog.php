<?php
//引入 model 基类
E_FW::load_File('class_TableDataGateway');

/**
 * 定义 blog 表的 model 类
 * 
 * <pre>
 * 类名以 [目录名]_[类名] 方式命名
 * 需继承自 class_TableDataGateway 类
 * </pre>
 *
 * @package Example
 */
class Model_Blog extends class_TableDataGateway{
	var $tableName 	= 'blog';
	var $primaryKey = 'id';
	
	var $belongsTo = array(
		'tableClass'	=> 'Model_Category_ForBlog',
		'joinKey'		=> 'categoryID',
		'mappingName'	=> 'Category'
	);
}


/**
 * 定义 category 表的 model 类
 * 
 * <pre>
 * 这里如果不想重复定义 model 类，可以使用
 * E_FW::load_File('Model_Category')
 * 
 * 之所以在这里重复定义 category 表的 model 类
 * 是因为考虑到在原型的 Model_Category 中有对 blog 表的引用
 * 因此会造成 A 与 B 关联， B 与 A 关联，从而有可能造成引用死循环
 * </pre>
 *
 */
class Model_Category_ForBlog extends class_TableDataGateway{
	var $tableName 	= 'category';
	var $primaryKey = 'id';
}
?>