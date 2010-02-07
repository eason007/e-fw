<?php
/**
 * @package Example
 * @subpackage Model
 */

//引入 model 基类
E_FW::load_File('db_ActiveRecord');

/**
 * 定义 blog 表的 model 类
 * 
 * <pre>
 * 类名以 [目录名]_[类名] 方式命名
 * 需继承自 DB_ActiveRecord 类
 * </pre>
 *
 * @package Example
 * @subpackage Model
 * @see DB_ActiveRecord
 */
class Model_BlogActiveRecord extends DB_ActiveRecord{
	static function _define () {
		return array (
			'props' => array (
				'tableName'	=> 'e_fw_blog',
				'primaryKey'=> 'id',
				'dbParams'	=> array(
					'dbServer' 	=> 'localhost',
					'dbPort' 	=> '3306',
					'dbName' 	=> 'test2',
					'dbUser' 	=> 'root',
					'dbPassword'=> '',
					'dbType' 	=> 'PDO'
				)
			),
			'funcs' => array(
				'Category'	=> array(
					'linkType'		=> 'belongsTo',
					'tableClass'	=> 'Model_CategoryActiveRecord',
					'joinKey'		=> 'category_id',
					'mappingName'	=> 'Category'
				)
			)
		);
	}
}
?>