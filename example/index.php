<?php
/**
 * 程序入口
 * 
 * <pre>
 * 首先设置服务器，使用 URL_Rewrite 将网站所有连接指定到本文件中
 * 然后由本文件调用框架，分发请求。
 * </pre>
 * 
 * @package Example
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2010 eason007<eason007@163.com>
 * @version 1.1.1.20091215
 */

/**
 * 引用框架
 */
require('../library/e_fw.php');

//将本目录加入到默认引用文件路径中
E_FW::import(dirname(__FILE__).DS);

//定义系统配置
$Config = array(
	'DSN' => array(
		'dbServer' 	=> 'localhost',								//数据库地址
		'dbPort' 	=> '3306',									//数据库端口
		'dbName' 	=> 'test',									//数据库名
		'dbUser' 	=> 'root',									//登陆用户名
		'dbPassword'=> '',										//登陆密码
		'dbType' 	=> 'Mysql'									//DB类连接类型
	),
	'VIEW' => array(
		'class' 		=> 'smarty',							//设置调用的模版类
		'template_dir' 	=> '.'.DS.'Res',						//设置模版存放目录
		'compile_dir' 	=> '.'.DS.'Compiler',					//设置模版缓存目录
		'left_delimiter'=> '${'									//设置模版左定界符号
	),
	'CONTROLLER' => array(
		'defaultController' => 'Index'							//设置默认控制器名
	)
);

//将配置数组加入到全局变量中
E_FW::set_Config($Config);

//Start
E_FW::run();
?>