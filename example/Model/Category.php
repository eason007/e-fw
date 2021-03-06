<?php
/**
 * @package Example
 * @subpackage Model
 */

//引入 model 基类
E_FW::load_File('db_TableGateway');

/**
 * 定义 category 表的 model 类
 * 
 * <pre>
 * 类名以 [目录名]_[类名] 方式命名
 * 需继承自 db_TableGateway 类
 * </pre>
 *
 * @package Example
 * @subpackage Model
 * @see DB_TableGateway
 */
class Model_Category extends DB_TableGateway {
	var $tableName 	= 'e_fw_category';
	var $primaryKey = 'id';
	
	var $autoLink 	= true;
	
	var $hasMany	= array(
		'tableClass' 	=> 'Model_Blog',
		'joinKey'		=> 'category_id',
		'mappingName'	=> 'Blog'
	);
	
	var $hasOne 		= array(
		'tableClass' 	=> 'test',
		'joinKey'		=> 'id',
		'mappingName'	=> 'test'
	);
}

class test extends DB_TableGateway {
	var $tableName 	= 'test';
	var $primaryKey = 'id';
}
?>