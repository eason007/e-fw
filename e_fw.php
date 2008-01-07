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
 * @version 1.0.0.20080107
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
	public function run () {
		setlocale(LC_TIME, $GLOBALS[E_FW_VAR]["TIME_FORMAT"]);
		date_default_timezone_set($GLOBALS[E_FW_VAR]["TIME_ZONE"]);

		$controllerAccessor = $GLOBALS[E_FW_VAR]["CONTROLLER"]["controllerAccessor"];
		$actionAccessor		= $GLOBALS[E_FW_VAR]["CONTROLLER"]["actionAccessor"];

		$r = array_change_key_case($_GET, CASE_LOWER);
		$data = array("controller" => null, "action" => null);

		if (isset($r[$controllerAccessor])) {
			$controllerName = $_GET[$controllerAccessor];
		}
		else{
			$controllerName = $GLOBALS[E_FW_VAR]["CONTROLLER"]["defaultController"];
		}
		if (isset($r[$actionAccessor])) {
			$actionName = $_GET[$actionAccessor];
		}
		else{
			$actionName = $GLOBALS[E_FW_VAR]["CONTROLLER"]["defaultaction"];
		}

		E_FW::executeAction($controllerName, $actionName);
	}

	public function executeAction ($controllerName, $actionName) {
		$actionPrefix = $GLOBALS[E_FW_VAR]["CONTROLLER"]["actionMethodPrefix"];
		if ($actionPrefix != "") {
			$actionName = ucfirst($actionName);
		}
		$actionMethod = $actionPrefix.$actionName.$GLOBALS[E_FW_VAR]["CONTROLLER"]["actionMethodSuffix"];

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

	public function import($dir)
    {
		if (array_search($dir, $GLOBALS[E_FW_VAR]["FILE_PATH"], true)) {
			return;
		}
		$GLOBALS[E_FW_VAR]["FILE_PATH"][] = $dir;
    }

	public function load_Class($className, $isLoad = true, $loadParams = null)
    {
		if ( (isset($GLOBALS[E_FW_VAR]["CLASS_OBJ"][$className])) && (is_object($GLOBALS[E_FW_VAR]["CLASS_OBJ"][$className])) ){
			return $GLOBALS[E_FW_VAR]["CLASS_OBJ"][$className];
		}

		if (class_exists($className, false)) {
			if ($isLoad){
				$t = new $className($loadParams);
				$GLOBALS[E_FW_VAR]["CLASS_OBJ"][$className] = $t;

				return $t;
			}
		}

		if (E_FW::load_File($className.".php")) {
			if (class_exists($className, false)) {
				if ($isLoad){
					$t = new $className;
					$GLOBALS[E_FW_VAR]["CLASS_OBJ"][$className] = $t;

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

	public function load_File ($filename) {
		$path = E_FW::get_FilePath($filename);

		if ($path != "") {
			if (isset($GLOBALS[E_FW_VAR]["LOAD_FILE_NAME"][$path])) {
				return true;
			}

			$is_loaded[$path] = true;
			return require_once($path);
		}
	}
	
	public function set_Config ($params = null) {
		if ( (!is_array($params)) and (is_string($params)) ){
			if (is_readable($params)){
				$tmp = require($params);
				$GLOBALS[E_FW_VAR] = array_merge($GLOBALS[E_FW_VAR], $tmp);
			}
		}
		else if (is_array($params)){
			foreach($params as $key => $val){
				if (is_array($val)){
					if (isset($GLOBALS[E_FW_VAR][$key])){
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
	
	public function get_view() {
		return E_FW::load_Class($GLOBALS[E_FW_VAR]["VIEW"]["class"]);
	}

	private function get_FilePath($filename)
    {
		if (isset($GLOBALS[E_FW_VAR]["SEARCH_FILE_NAME"][$filename])) {
			return $GLOBALS[E_FW_VAR]["SEARCH_FILE_NAME"][$filename];
		}

		$id = $filename;
		$filename = str_replace("_", DS, $filename);

		if (strtolower(substr($filename, -4)) != ".php") {
			$filename.= ".php";
		}

		if (is_file($filename)) {
			$GLOBALS[E_FW_VAR]["SEARCH_FILE_NAME"][$id] = $filename;
			return $filename;
		}
		else{
			foreach ($GLOBALS[E_FW_VAR]["FILE_PATH"] as $classdir) {
				$path = $classdir.$filename;
				if (is_file($path)) {
					$GLOBALS[E_FW_VAR]["SEARCH_FILE_NAME"][$id] = $path;
					return $path;
				}
			}
		}

		$GLOBALS[E_FW_VAR]["SEARCH_FILE_NAME"][$id] = false;
		return false;
    }
}

?>