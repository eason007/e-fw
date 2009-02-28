<?php
//---------------------index.php
require('./e_fw.php');

$dsn = array(
	'dbServer' 	=> '192.168.1.243',
	'dbPort' 	=> '3306',
	'dbName' 	=> '169v3-2',
	'dbUser' 	=> 'dba',
	'dbPassword'=> '123456',
	'dbType' 	=> 'Mysqli'
);


//---------------------model
E_FW::load_File('class_TableDataGateway');
class user extends Class_TableDataGateway
{
    var $tableName 	= 'user';
    var $primaryKey = 'Id';
    
    var $dbConfig = array(
		'dbServer' 	=> '192.168.1.243',
		'dbPort' 	=> '3306',
		'dbName' 	=> 'test',
		'dbUser' 	=> 'dba',
		'dbPassword'=> '123456',
		'dbType' 	=> 'Mysqli'
	);

	var $autoLink = true;
	
    var $hasOne = array(
		'tableClass' 	=> 'userinfo',
		'joinKey' 		=> 'userID',
		'mappingName' 	=> 'Profile'
    );
    
	var $hasMany = array(
		'tableClass' 	=> 'blog',
		'joinKey' 		=> 'userID',
		'mappingName' 	=> 'Blog'
    );
    
	var $manyToMany = array(
		'tableClass' 	=> 'Application',
		'relateClass' 	=> 'userApp',
		'linkKey' 		=> 'appID',
		'joinKey' 		=> 'userID',
		'mappingName' 	=> 'App'
    );
}

class userinfo extends Class_TableDataGateway
{
    var $tableName = 'user_detail';
    var $primaryKey = 'userID';
}

class blog extends Class_TableDataGateway
{
    var $tableName = 'blog';
    var $primaryKey = 'ID';
}

class userApp extends Class_TableDataGateway
{
    var $tableName = 'user_app';
    var $primaryKey = 'ID';
    
    var $dbConfig = array(
		'dbServer' 	=> '192.168.1.243',
		'dbPort' 	=> '3306',
		'dbName' 	=> '169v3',
		'dbUser' 	=> 'dba',
		'dbPassword'=> '123456',
		'dbType' 	=> 'Mysqli'
	);
}

class Application extends Class_TableDataGateway
{
    var $tableName = 'application';
    var $primaryKey = 'ID';
}


//------------------------
$user = new user();
$user->setDB($dsn);

$userinfo = new userinfo();
$userinfo->setDB($dsn);

if (!isset($_GET['step'])) {
	$_GET['step'] = '';
}

switch ($_GET['step']){
	case 'insert' :
		$insert = array(
			'Id'		=> 7,
			'userName' 	=> 'test_abc',
			'hasOne' => array(
				'lastLoginTime' => '1',
				'loginTime'		=> '2',
				'regTime'		=> '3',
				'regIP'			=> 'abc'
			),
			'hasMany' => array(
				array(
					'title' => 'user1-1',
					'createTime' => 1,
					'IP'	=> 'abc'
				),
				array(
					'title' => 'user1-2',
					'createTime' => 2,
					'IP'	=> '123'
				),
			),
			'manyToMany' => array(
				array(
					'appID' => 5
				)
			)
		);
		print_r($user->insert($insert));
		
		break;
	
	case 'update':
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
			)
		);
		print_r($user->update($update));
		break;
	
	case 'delete':
		$user->where = 7;
		print_r($user->del());
		break;
		
	default:
		$user->where = 7;
		print_r($user->select());
}

echo '=========================================================';
?>