<?php
/**
 * 框架引导文件
 * 
 * 当使用框架时，只需引用本文件即可，并调用静态 E_FW 类的 run 方法即可。
 * 当引用本文件时，会进行运行环境的初始化工作。
 * 
 * @package Core
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2008 eason007<eason007@163.com>
 * @version 1.0.0.20080108
 */

//标记文件启动时间
global $_load_time;
$_load_time = microtime();

set_magic_quotes_runtime(0);

define("DS", DIRECTORY_SEPARATOR);
define("E_FW_VAR", "_E_FW_CORE_");

/**
 * 初始化框架
 */
$GLOBALS[E_FW_VAR] = array(
    "VERSION" => "1.0.0.20080107",
	"DSN" => array(),
	"FILE_PATH" => array(),
	"LOAD_FILE_NAME" => array(),
	"SEARCH_FILE_NAME" => array(),
	"CONTROLLER" => array(
		"controllerAccessor" => "controller",
		"defaultController" => "controller_default",
		"actionAccessor" => "action",
		"defaultaction" => "index",
		"actionMethodPrefix" => "action",
		"actionMethodSuffix" => ""
	),
	"CLASS_OBJ" => array(),
	"VIEW" => array(),
	"TIME_FORMAT" => "zh_CN",
	"TIME_ZONE" => "Asia/Hong_Kong"
);

$GLOBALS[E_FW_VAR]["FILE_PATH"][] = dirname(__FILE__).DS;

class E_FW {
	/**
	 * 启动框架
	 * 
	 * 目前只支持 URL 重写方法。
	 */
	public function run () {
		setlocale(LC_TIME, E_FW::get_Config("TIME_FORMAT"));
		date_default_timezone_set(E_FW::get_Config("TIME_ZONE"));

		$controllerAccessor = E_FW::get_Config("CONTROLLER/controllerAccessor");
		$actionAccessor		= E_FW::get_Config("CONTROLLER/actionAccessor");

		$r = array_change_key_case($_GET, CASE_LOWER);
		$data = array("controller" => null, "action" => null);

		if (isset($r[$controllerAccessor])) {
			$controllerName = $_GET[$controllerAccessor];
		}
		else{
			$controllerName = E_FW::get_Config("CONTROLLER/defaultController");
		}
		if (isset($r[$actionAccessor])) {
			$actionName = $_GET[$actionAccessor];
		}
		else{
			$actionName = E_FW::get_Config("CONTROLLER/defaultaction");
		}

		E_FW::execute_Action($controllerName, $actionName);
	}

	
	/**
	 * 执行控制器调用
	 *
	 * @param string $controllerName 控制器名称
	 * @param string $actionName 方法名称
	 * @return object
	 */
	public function execute_Action ($controllerName, $actionName) {
		$actionPrefix = E_FW::get_Config("CONTROLLER/actionMethodPrefix");
		if ($actionPrefix != "") {
			$actionName = ucfirst($actionName);
		}
		$actionMethod = $actionPrefix.$actionName.E_FW::get_Config("CONTROLLER/actionMethodSuffix");

		$controller = E_FW::load_Class($controllerName);
		if (!$controller) {
			return false;
		}

		if (!method_exists($controller, $actionMethod)) {
			return false;
		}
		else{
			if (method_exists($controller, "_beforeExecute")) {
				$controller->_beforeExecute($actionMethod);
			}

			$ret = $controller->{$actionMethod}();

			if (method_exists($controller, "_afterExecute")) {
				$controller->_afterExecute($actionMethod);
			}

			return $ret;
		}
	}

	
	/**
	 * 导入包含文件路径
	 *
	 * @param string $dir
	 * @return null
	 */
	public function import($dir)
    {
		if (array_search($dir, E_FW::get_Config("FILE_PATH"), true)) {
			return false;
		}
		E_FW::set_Config(array("FILE_PATH" => array($dir)));
    }

    
    /**
     * 加载类
     * 
     * 首先检查全局变量中，是否已有该类的实例
     * 如没有，则先调用 load_File 方法加载文件
     * 然后实例化该类，并保存到全局变量中，以便下次调用
     *
     * @param string $className
     * @param bool $isLoad
     * @param array $loadParams
     * @return object/bool
     */
	public function load_Class($className, $isLoad = true, $loadParams = null)
    {
    	$v = E_FW::get_Config("CLASS_OBJ/".$className);
    	
		if ( (isset($v)) && (is_object($v)) ){
			return $v;
		}

		if (class_exists($className, false)) {
			if ($isLoad){
				$t = new $className($loadParams);
				E_FW::set_Config(array("CLASS_OBJ" => array($className => $t)));
				
				return $t;
			}
		}

		if (E_FW::load_File($className.".php")) {
			if (class_exists($className, false)) {
				if ($isLoad){
					$t = new $className($loadParams);
					E_FW::set_Config(array("CLASS_OBJ" => array($className => $t)));

					return $t;
				}
			}
			else{
				return false;
			}
		} 
		else {
			return false;
		}
    }

    
    /**
     * 包含文件
     * 
     * 先调用 get_FilePath 方法解释路径
     * 然后在全局变量中检查是否已包含该文件
     * 如没有，则按照一定的规则，解释文件路径，并包含
     * 然后保存到全局变量，以便下次使用时无需重复包含
     * 
     *
     * @param string $filename
     * @return var
     */
	public function load_File ($filename) {
		$path = E_FW::get_FilePath($filename);

		if ($path != "") {
			if (E_FW::get_Config("LOAD_FILE_NAME/".$path)) {
				return true;
			}

			$is_loaded[$path] = true;
			return require_once($path);
		}
	}
	
	
	/**
	 * 设定全局变量
	 * 
	 * 当传入一个字符串时，则假定为文件路径，程序会试图包含该文件
	 * 并将该文件内的内容，追加到全局变量中。
	 * 因此该文件内容必须为数组形式。
	 * 如传入参数为数组时，则追加或覆盖全局变量
	 *
	 * @param string/array $params
	 */
	public function set_Config ($params) {
		if ( (!is_array($params)) and (is_string($params)) ){
			if (is_readable($params)){
				$tmp = require($params);
				$GLOBALS[E_FW_VAR] = array_merge($GLOBALS[E_FW_VAR], $tmp);
			}
		}
		else if (is_array($params)){
			foreach($params as $key => $val){
				if (is_array($val)){
					if (E_FW::get_Config($key)){
						$GLOBALS[E_FW_VAR][$key] = array_merge($GLOBALS[E_FW_VAR][$key], $val);
					}
					else{
						$GLOBALS[E_FW_VAR][$key] = $val;
					}
				}
				else{
					$GLOBALS[E_FW_VAR][$key] = $val;
				}
			}
		}
	}
	
	
	/**
	 * 获取全局变量
	 * 
	 * 可以获取所有的全局变量，或部分变量
	 * 根据传入的数据路径决定，如在多层结点下，利用 / 号分隔。
	 * 如全局变量是：
	 * array(
	 * 		"DSN" => array(
	 * 			"name" => "a",
	 * 			"pwd" => "b"
	 * 		),
	 * 		"CACHE" => true
	 * )
	 * 则：
	 * get_Config();			//获取所有
	 * get_Config("DSN");		//仅获取 DSN 结点
	 * get_Config("DSN/name");	//仅获取 DSN 结点下的 name
	 *
	 * @param string $path
	 * @return var
	 */
	public function get_Config ($path = null) {
		if (is_null($path)){
			return $GLOBALS[E_FW_VAR];
		}
		else{
			$fullPath = explode("/", $path);
			$rt = $GLOBALS[E_FW_VAR];
			
			foreach($fullPath as $val){
				if (!isset($rt[$val])){
					return false;
				}
				$rt = $rt[$val];
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
		return E_FW::load_Class(E_FW::get_Config("VIEW/class"));
	}

	
	/**
	 * 分析文件路径
	 * 
	 * 按一定规则拆分输入的字符串参数为目录路径
	 * 当检测到存在该文件时，返回正确的路径地址
	 * 文件后缀必须为 .php
	 * 在 linux 下，区分路径大小写
	 * 如：
	 * get_FilePath("class_cache");		//返回class/cache.php
	 * get_FilePath("db_Mysql5.php");	//返回db/Mysql5.php
	 *
	 * @param string $filename
	 * @return string
	 */
	private function get_FilePath($filename)
    {
		if (E_FW::get_Config("SEARCH_FILE_NAME/".$filename)) {
			return E_FW::get_Config("SEARCH_FILE_NAME/".$filename);
		}

		$id = $filename;
		$filename = str_replace("_", DS, $filename);

		if (strtolower(substr($filename, -4)) != ".php") {
			$filename.= ".php";
		}

		if (is_file($filename)) {
			E_FW::set_Config(array("SEARCH_FILE_NAME" => array($id => $filename)));
			return $filename;
		}
		else{
			foreach (E_FW::get_Config("FILE_PATH") as $classdir) {
				$path = $classdir.$filename;
				if (is_file($path)) {
					E_FW::set_Config(array("SEARCH_FILE_NAME" => array($id => $path)));
					return $path;
				}
			}
		}

		return false;
    }
}
?>