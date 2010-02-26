<?php
/**
 * 框架引导文件
 * 
 * <pre>
 * 当引用本文件时，会进行运行环境的初始化工作。
 * </pre>
 * 
 * @package Core
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2008 eason007<eason007@163.com>
 * @version 1.0.7.20100226
 */

/**
 * 标记文件启动时间
 * 
 * @global int $_load_time
 */
global $_load_time;
$_load_time = microtime();

set_magic_quotes_runtime(0);

define('DS', DIRECTORY_SEPARATOR);
define('E_FW_VAR', '_E_FW_CORE_');

/**
 * 框架默认设置
 * 
 * @global array $GLOBALS
 */
$GLOBALS[E_FW_VAR] = array(
    'VERSION' 	=> '1.0.6.20100220',			//框架版本号
	'DSN' 		=> array(),						//数据库连接配置信息。dbServer/dbPort/dbName/dbUser/dbPassword/dbType
	'FILE_PATH' => array(),
	'LOAD_FILE_NAME'	=> array(),
	'SEARCH_FILE_NAME' 	=> array(),
	'CONTROLLER'=> array(
		'controllerAccessor'=> 'controller',	//控制器键名
		'defaultController' => 'default',		//默认控制器名称
		'actionAccessor' 	=> 'action',		//方法键名
		'defaultAction' 	=> 'index',			//默认方法名称
		'actionMethodPrefix'=> 'action',		//方法名前缀
		'actionMethodSuffix'=> ''				//方法名后缀
	),
	'CLASS_OBJ' => array(),
	'VIEW' 		=> array(),						//模版类配置信息
	'TIME_FORMAT'	=> 'zh_CN',					//默认时间格式
	'TIME_ZONE' => 'Asia/Shanghai',				//默认时区
	'CHARSET'	=> 'utf-8',						//默认页面编码
	'URL_MODEL' => 1,							//路由模式，0=URL Rewrite，1=PATHINFO
	'DEBUG'		=> 1							//调试模式，0=否，1=是
);

$GLOBALS[E_FW_VAR]['FILE_PATH'][] = dirname(__FILE__).DS;

E_FW::load_Class('Exception_Core', false);

/**
 * E_FW 类
 * 
 * 当使用框架时，只需引用本文件即可，并调用静态 E_FW 类的 run 方法即可。
 *
 * @package Core
 */
class E_FW {
	/**
	 * 启动框架
	 * 
	 * <p>
	 * 分析URL
	 * 如果是 url rewrite 模式，则从 $_GET 中获取，规则为
	 * ?[controllerAccessor]=xxx&[actionAccessor]=yyy&zzz=111...
	 * 如果是 pathinfo 模式，规则为
	 * /[controllerAccessor]/[actionAccessor]/key1/value1/key2/value2...
	 * </p>
	 * 
	 * @return void
	 */
	public function run () {
		setlocale(LC_TIME, E_FW::get_Config('TIME_FORMAT'));
		date_default_timezone_set(E_FW::get_Config('TIME_ZONE'));
		header('Content-Type:text/html;charset='.E_FW::get_Config('CHARSET'));

		switch (E_FW::get_Config('URL_MODEL')){
			case 0:
				$controllerAccessor = E_FW::get_Config('CONTROLLER/controllerAccessor');
				$actionAccessor		= E_FW::get_Config('CONTROLLER/actionAccessor');

				$r = array_change_key_case($_GET, CASE_LOWER);
				$data = array('controller' => null, 'action' => null);

				if (isset($r[$controllerAccessor])) {
					$controllerName = $_GET[$controllerAccessor];
				}
				if (isset($r[$actionAccessor])) {
					$actionName = $_GET[$actionAccessor];
				}

				break;

			case 1:
				if (isset($_SERVER['PATH_INFO'])){
					$parts = explode('/', substr($_SERVER['PATH_INFO'], 1));
	
					if (isset($parts[0])) {
						$controllerName = $parts[0];
					}
					if (isset($parts[1])) {
						$actionName = $parts[1];
					}

					for ($i = 2; $i < count($parts); $i += 2) {
						if (isset($parts[$i + 1])) {
							$_GET[$parts[$i]] = $parts[$i + 1];
						}
					}
				}
				
				break;
				
			default:
				break;
		}

		if (!isset($controllerName)) {
			$controllerName = E_FW::get_Config('CONTROLLER/defaultController');
		}
		if (!isset($actionName)) {
			$actionName = E_FW::get_Config('CONTROLLER/defaultAction');
		}

		E_FW::execute_Action('Controller_'.$controllerName, $actionName);
	}

	
	/**
	 * 执行控制器调用
	 * 
	 * <pre>
	 * 调用指定的控制器方法。
	 * 如控制器存在 _beforeExecute 方法，则先调用 _beforeExecute 方法
	 * 如控制器存在 _afterExecute 方法，则在调用指定方法后，再调用 _afterExecute 方法
	 * </pre>
	 *
	 * @param string $controllerName 控制器名称
	 * @param string $actionName 方法名称
	 * @param array $loadParam 加载类时的传递参数。可选
	 * @return mixed
	 */
	public function execute_Action ($controllerName, $actionName, $loadParam = null) {
		$actionPrefix = E_FW::get_Config('CONTROLLER/actionMethodPrefix');
		if ($actionPrefix != '') {
			$actionName = ucfirst($actionName);
		}
		$actionMethod = $actionPrefix.$actionName.E_FW::get_Config('CONTROLLER/actionMethodSuffix');

		$controller = E_FW::load_Class($controllerName);
		if (!$controller) {
			return false;
		}

		if (!method_exists($controller, $actionMethod)) {
			return false;
		}
		else{
			if (method_exists($controller, '_beforeExecute')) {
				$controller->_beforeExecute($actionMethod);
			}

			$ret = $controller->{$actionMethod}($loadParam);

			if (method_exists($controller, '_afterExecute')) {
				$controller->_afterExecute($actionMethod);
			}

			return $ret;
		}
	}

	
	/**
	 * 导入包含文件路径
	 *
	 * @param string $dir 目录地址
	 * @return void
	 */
	public function import($dir)
    {
		if (array_search($dir, E_FW::get_Config('FILE_PATH'))) {
			return false;
		}
		E_FW::set_Config(array('FILE_PATH' => array($dir)));
    }

    
    /**
     * 加载类
     * 
     * <pre>
     * 首先检查全局变量中，是否已有该类的实例
     * 如没有，则调用 load_File 方法加载文件
     * 然后实例化该类，并保存到全局变量中，以便下次调用
     * </pre>
     *
     * @param string $className 类名
     * @param bool $isLoad 是否马上实例化该类
     * @param array $loadParams 实例化参数
     * @return mixed
     */
	public function load_Class($className, $isLoad = true, $loadParams = null)
    {
    	$v = E_FW::get_Config('CLASS_OBJ/'.$className);
    	
		if ( (isset($v)) && (is_object($v)) && (!$isLoad) ){
			return $v;
		}

		if (!class_exists($className, false)) {
			E_FW::load_File($className);
		}

		if (class_exists($className, false)) {
			if ($isLoad){
				$t = new $className($loadParams);
				E_FW::set_Config(array('CLASS_OBJ' => array($className => $t)));

				return $t;
			}
			else {
				return true;
			}
		}
		
		return false;
    }

    
    /**
     * 包含文件
     * 
     * <pre>
     * 先调用 get_FilePath 方法解释路径
     * 然后在全局变量中检查是否已包含该文件
     * 如没有，则按照一定的规则，解释文件路径，并包含
     * 然后保存到全局变量，以便下次使用时无需重复包含
     * </pre>
     *
     * @param string $filename 文件名
     * @return void
     */
	public function load_File ($filename, $loadOnce = true) {
		$path = E_FW::get_FilePath($filename);

		if ($path != '') {
			if ( (E_FW::get_Config('LOAD_FILE_NAME/'.strtolower($filename))) and ($loadOnce) ) {
				return true;
			}
			
			E_FW::set_Config(array('LOAD_FILE_NAME' => array(strtolower($filename) => $path)));
			return include($path);
		}
	}
	
	
	/**
	 * 设定全局变量
	 * 
	 * <pre>
	 * 当传入一个字符串时，则假定为文件路径，程序会试图包含该文件
	 * 并将该文件内的内容，追加到全局变量中。
	 * 因此该文件内容必须为数组形式。
	 * 如传入参数为数组时，则追加或覆盖全局变量
	 * </pre>
	 * 
	 * <code>
	 * set_Config('config/global.php');
	 * set_Config(
	 * 		array(
	 * 			'LOAD_FILE_NAME' => array (
	 * 				'config/global.php' => 'config/global.php'
	 * 			)
	 * 		)
	 * );
	 * set_Config(
	 * 		array(
	 * 			'DSN/dbServer' => '192.168.0.10'
	 * 		)
	 * );
	 * </code>
	 *
	 * @param string/array $params
	 * @return void
	 */
	public function set_Config ($params) {
		if (is_string($params)){
			$params = E_FW::get_FilePath($params);
			if (is_readable($params)){
				$tmp = require($params);
				E_FW::set_Config($tmp);
			}
		}
		else if (is_array($params)){
			foreach($params as $key => $val){
				if (strstr($key, '/')){
					$tmp = &E_FW::get_Config($key, true);
				}
				else{
					if (!isset($GLOBALS[E_FW_VAR][$key])){
						$GLOBALS[E_FW_VAR][$key] = null;
					}
					$tmp = &$GLOBALS[E_FW_VAR][$key];
				}
				
				if ( (is_array($val)) and (is_array($tmp)) ){
					$tmp = array_merge($tmp, $val);
				}
				else{
					$tmp = $val;
				}
			}
		}
	}
	
	
	/**
	 * 获取全局变量
	 * 
	 * <pre>
	 * 可以获取所有的全局变量，或部分变量
	 * 根据传入的数据路径决定，如在多层结点下，利用 / 号分隔。
	 * </pre>
	 * 
	 * <code>
	 * array(
	 * 		'DSN' => array(
	 * 			'name' => 'a',
	 * 			'pwd' => 'b'
	 * 		),
	 * 		'CACHE' => true
	 * )
	 * 
	 * get_Config();			//获取所有
	 * get_Config('DSN');		//仅获取 DSN 结点
	 * get_Config('DSN/name');	//仅获取 DSN 结点下的 name
	 * </code>
	 *
	 * @param string $path
	 * @return mixed
	 */
	public function get_Config ($path = null, $returnRoot = false) {
		if (is_null($path)){
			return $GLOBALS[E_FW_VAR];
		}
		else{
			$fullPath = explode('/', $path);
			$rt = $GLOBALS[E_FW_VAR];
			
			foreach($fullPath as $val){
				if (isset($rt[$val])){
					$rt = $rt[$val];
				}
				else{
					if ($returnRoot){
						return $rt;
					}
					else{
						return false;
					}
				}
			}
			
			return $rt;
		}
	}
	
	
	/**
	 * 获取当前设定的模版类
	 *
	 * @return object
	 */
	public function get_view() {
		return E_FW::load_Class('templates_'.E_FW::get_Config('VIEW/class').'_Plus');
	}

	
	/**
	 * 分析文件路径
	 * 
	 * <pre>
	 * 按一定规则拆分输入的字符串参数为目录路径
	 * 当检测到存在该文件时，返回正确的路径地址
	 * 文件后缀必须为 .php
	 * 在 linux 下，区分路径大小写
	 * </pre>
	 * 
	 * <code>
	 * get_FilePath('class_cache');		//返回class/cache.php
	 * get_FilePath('db_Mysql5.php');	//返回db/Mysql5.php
	 * </code>
	 *
	 * @param string $filename
	 * @return string
	 */
	private function get_FilePath($filename)
    {
		if (E_FW::get_Config('SEARCH_FILE_NAME/'.$filename)) {
			return E_FW::get_Config('SEARCH_FILE_NAME/'.$filename);
		}

		$id = $filename;
		$filename = str_replace('_', DS, $filename);

		if (strtolower(substr($filename, -4)) != '.php') {
			$filename.= '.php';
		}

		if (is_file($filename)) {
			E_FW::set_Config(array('SEARCH_FILE_NAME' => array($id => $filename)));
			return $filename;
		}
		else{
			foreach (E_FW::get_Config('FILE_PATH') as $classdir) {
				$path = $classdir.$filename;
				if (is_file($path)) {
					E_FW::set_Config(array('SEARCH_FILE_NAME' => array($id => $path)));
					return $path;
				}
			}
		}

		return false;
    }
}
?>