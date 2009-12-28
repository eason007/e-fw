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
 * @subpackage Model
 * @see Class_TableDataGateway
 */
class Model_Category extends Class_TableDataGateway{
	var $tableName 	= 'e_fw_category';
	var $primaryKey = 'id';
	
	var $hasMany	= array(
		'tableClass' 	=> 'Model_Blog',
		'joinKey'		=> 'category_id',
		'mappingName'	=> 'Blog'
	);
}
?>