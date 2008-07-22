<?php

$dsn = array(
			'dbServer' => 'localhost',
			'dbPort' => '3306',
			'dbName' => 'test',
			'dbUser' => 'root',
			'dbPassword' => '',
			'dbType' => 'Mysqli'
);

require('./e_fw.php');
require_once ('class/efw-db-TableDataGateway.php');


class user extends EFW_DB_TableDataGateway
{
    var $tableName = 'user';
    var $primaryKey = 'id';

    var $hasOne = array(
						'tableClass' => 'userinfo',
						'joinKey' => 'u_id',
						'mappingName' => 'UserProfile'
    );

	var $hasMany = array(
						'tableClass' => 'userorder',
						'joinKey' => 'u_id',
						'mappingName' => 'UserOrder'
    );

	var $manyToMany = array(
						'tableClass' => 'usergroup',
						'joinTable' => 'user_group',
						'linkKey' => 'g_id',
						'joinKey' => 'u_id',
						'mappingName' => 'UserGroup'
    );

	var $autoLink = true;
}


class userinfo extends EFW_DB_TableDataGateway
{
    var $tableName = 'userinfo';
    var $primaryKey = 'u_id';
}

class userorder extends EFW_DB_TableDataGateway
{
    var $tableName = 'order';
    var $primaryKey = 'id';
}

class usergroup extends EFW_DB_TableDataGateway
{
    var $tableName = 'group';
    var $primaryKey = 'id';
}

$user = new user();
$user->setDB($dsn);

$insert = array(
				'name' => '111',
				'hasOne' => array(
								'email' => '1@1.com'
				),
				'hasMany' => array(
								array(
									'price' => 1
								),
								array(
									'price' => 100
								),
				),
				'manyToMany' => array(
								array(
									'g_id' => 1
								)
				),
);
//print_r($user->insert($insert));


$update = array(
				'id' => 1,
				'name' => 'eason2',
				'hasOne' => array(
								'email' => 'eason@1.com'
				),
				'hasMany' => array(
								array(
									'id' => 1,
									'price' => 19
								),
								array(
									'id' => 2,
									'price' => 14
								),
				),
);
print_r($user->update($update));


//$user->where = 'id < 9';
//$user->autoLink = false;
//$user->del('hasMany');


print_r($user->select());

echo '=========================================================';


?>