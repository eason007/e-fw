<?php
/**
 * @package Example
 */

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
 * @subpackage Model
 * @see Class_TableDataGateway
 */
class Model_Blog extends Class_TableDataGateway{
	var $tableName 	= 'e_fw_blog';
	var $primaryKey = 'id';

	var $autoLink	= true;
	
	var $belongsTo	= array(
		'tableClass'	=> 'Model_Category_ForBlog',
		'joinKey'		=> 'category_id',
		'mappingName'	=> 'Category'
	);

	var $dbParams	= array(
		'dbServer' 	=> 'localhost',
		'dbPort' 	=> '3306',
		'dbName' 	=> 'test2',
		'dbUser' 	=> 'root',
		'dbPassword'=> '',
		'dbType' 	=> 'Mysqli'
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
 * 是因为考虑到在原型的 Model_Category 中可能有其他复杂的代码
 * 所以在这里演示了另一种使用方法
 * </pre>
 *
 * @package Example
 * @subpackage Model
 * @see Class_TableDataGateway
 */
class Model_Category_ForBlog extends Class_TableDataGateway{
	var $tableName 	= 'e_fw_category';
	var $primaryKey = 'id';

	var $dbParams	= array(
		'dbServer' 	=> 'localhost',
		'dbPort' 	=> '3306',
		'dbName' 	=> 'test',
		'dbUser' 	=> 'root',
		'dbPassword'=> '',
		'dbType' 	=> 'Mysqli'
	);
}
?>