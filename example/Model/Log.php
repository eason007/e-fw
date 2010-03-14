<?php
/**
 * @package Example
 * @subpackage Model
 */

//引入 model 基类
E_FW::load_File('db_TableGateway');

/**
 * 定义 blog 表的 model 类
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
class Model_Log extends DB_TableGateway {
	var $tableName 	= 'log';
	var $primaryKey = 'id';
}
?>