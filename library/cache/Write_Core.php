<?php
/**
 * @package Writer
 */

/**
 * 写入类
 * 
 * <pre>
 * 
 * </pre>
 * 
 * @package Writer
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2010 eason007<eason007@163.com>
 * @version 1.0.0.20101220
 */
abstract class Writer_Core {
	/**
	 * 读取
	 * 
	 * Enter description here ...
	 * @param unknown_type $key
	 */
	abstract public function fetch($key); 
	
	/**
	 * 写
	 * 
	 * Enter description here ...
	 * @param unknown_type $key
	 * @param unknown_type $value
	 */
	abstract public function store($key, $value);
	
	/**
	 * 删除
	 * 
	 * Enter description here ...
	 * @param unknown_type $key
	 */
	abstract public function delete($key);  
}
?>