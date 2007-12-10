<?php

set_magic_quotes_runtime(0);
setlocale(LC_TIME, 'zh_CN');
date_default_timezone_set ('Asia/Hong_Kong');

define('DS', DIRECTORY_SEPARATOR);

$_load_time = microtime();

/**
 * 初始化框架
 */
define("G_E_FW_VAR", "_E_FW_CORE_");
$GLOBALS[G_E_FW_VAR] = array(
    "VERSION" => "0.0.9.20071128",
	"DEBUG" => false,
	"HTTP" => array(
		"HTTP_HEADER" => "html",
		"HTTP_CHARSET" => "utf-8"
	),
	"DSN" => array(),
	"FILE_PATH" => array(),
	"LOAD_FILE_NAME" => array(),
	"SEARCH_FILE_NAME" => array(),
	"CONTROLLER" => array(),
	"CLASS_OBJ" => array(),
	"VIEW" => array(),
	"CACHE" => array(
		"type" => "none"
	)
);

$GLOBALS[G_E_FW_VAR]['FILE_PATH'][] = dirname(__FILE__).DS;
$GLOBALS[G_E_FW_VAR]['CONTROLLER'] = array(
	"controllerAccessor" => "controller",
	"defaultController" => "controller_default",
	"actionAccessor" => "action",
	"defaultaction" => "index",
	"actionMethodPrefix" => "action",
	"actionMethodSuffix" => ""
);

class E_FW {
	public function run () {
		global $_load_time;
		
		header('Content-Type: text/'.$GLOBALS[G_E_FW_VAR]["HTTP"]["HTTP_HEADER"].'; charset='.$GLOBALS[G_E_FW_VAR]["HTTP"]["HTTP_CHARSET"]);
		
		if ($GLOBALS[G_E_FW_VAR]["CACHE"]["type"] != "none"){
			E_FW::load_File("class/efw-cache.php");
			
			$objCache = E_FW::load_Class("EFW_Cache", true, $GLOBALS[G_E_FW_VAR]["CACHE"]);
			$isCache = $objCache->run();
		}
		
		if (!$isCache) {
			$controllerAccessor = $GLOBALS[G_E_FW_VAR]["CONTROLLER"]["controllerAccessor"];
			$actionAccessor		= $GLOBALS[G_E_FW_VAR]["CONTROLLER"]["actionAccessor"];
	
			$r = array_change_key_case($_GET, CASE_LOWER);
			$data = array('controller' => null, 'action' => null);
	
			if (isset($r[$controllerAccessor])) {
				$controllerName = $_GET[$controllerAccessor];
			}
			else{
				$controllerName = $GLOBALS[G_E_FW_VAR]['CONTROLLER']["defaultController"];
			}
			if (isset($r[$actionAccessor])) {
				$actionName = $_GET[$actionAccessor];
			}
			else{
				$actionName = $GLOBALS[G_E_FW_VAR]['CONTROLLER']["defaultaction"];
			}
	
			E_FW::executeAction($controllerName, $actionName);
		}
		
		if ($GLOBALS[G_E_FW_VAR]["DEBUG"]){
			echo "Run Time:".(microtime()-$_load_time);
		}
	}

	public function executeAction ($controllerName, $actionName) {
		$actionPrefix = $GLOBALS[G_E_FW_VAR]["CONTROLLER"]["actionMethodPrefix"];
		if ($actionPrefix != '') {
			$actionName = ucfirst($actionName);
		}
		$actionMethod = $actionPrefix.$actionName.$GLOBALS[G_E_FW_VAR]["CONTROLLER"]["actionMethodSuffix"];

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

			$ret = $controller->{$actionMethod}();

			if (method_exists($controller, '_afterExecute')) {
				$controller->_afterExecute($actionMethod);
			}

			return $ret;
		}
	}

	public function import($dir)
    {
		if (array_search($dir, $GLOBALS[G_E_FW_VAR]['FILE_PATH'], true)) {
			return;
		}
		$GLOBALS[G_E_FW_VAR]['FILE_PATH'][] = $dir;
    }

	public function load_Class($className, $isLoad = true, $loadParams = null)
    {
		if ( (isset($GLOBALS[G_E_FW_VAR]['CLASS_OBJ'][$className])) && (is_object($GLOBALS[G_E_FW_VAR]['CLASS_OBJ'][$className])) ){
			return $GLOBALS[G_E_FW_VAR]['CLASS_OBJ'][$className];
		}

		if (class_exists($className, false)) {
			if ($isLoad){
				$t = new $className($loadParams);
				$GLOBALS[G_E_FW_VAR]['CLASS_OBJ'][$className] = $t;

				return $t;
			}
		}

		if (E_FW::load_File($className.'.php')) {
			if (class_exists($className, false)) {
				if ($isLoad){
					$t = new $className;
					$GLOBALS[G_E_FW_VAR]['CLASS_OBJ'][$className] = $t;

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

		if ($path != '') {
			if (isset($GLOBALS[G_E_FW_VAR]["LOAD_FILE_NAME"][$path])) {
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
				$GLOBALS[G_E_FW_VAR] = array_merge($GLOBALS[G_E_FW_VAR], $tmp);
			}
		}
		else if (is_array($params)){
			foreach($params as $key => $val){
				if (is_array($val)){
					if (isset($GLOBALS[G_E_FW_VAR][$key])){
						$GLOBALS[G_E_FW_VAR][$key] = array_merge($GLOBALS[G_E_FW_VAR][$key], $val);
					}
					else{
						$GLOBALS[G_E_FW_VAR][$key] = $val;
					}
				}
				else{
					$GLOBALS[G_E_FW_VAR][$key] = $val;
				}
			}
			
		}
	}
	
	public function get_view() {
		return E_FW::load_Class($GLOBALS[G_E_FW_VAR]["VIEW"]["class"]);
	}

	private function get_FilePath($filename)
    {
		if (isset($GLOBALS[G_E_FW_VAR]["SEARCH_FILE_NAME"][$filename])) {
			return $GLOBALS[G_E_FW_VAR]["SEARCH_FILE_NAME"][$filename];
		}

		$id = $filename;
		$filename = str_replace('_', DS, $filename);

		if (strtolower(substr($filename, -4)) != '.php') {
			$filename.= '.php';
		}

		if (is_file($filename)) {
			$GLOBALS[G_E_FW_VAR]["SEARCH_FILE_NAME"][$id] = $filename;
			return $filename;
		}
		else{
			foreach ($GLOBALS[G_E_FW_VAR]["FILE_PATH"] as $classdir) {
				$path = $classdir.DS.$filename;
				if (is_file($path)) {
					$GLOBALS[G_E_FW_VAR]["SEARCH_FILE_NAME"][$id] = $path;
					return $path;
				}
			}
		}

		$GLOBALS[G_E_FW_VAR]["SEARCH_FILE_NAME"][$id] = false;
		return false;
    }
}

?>