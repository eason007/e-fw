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
 * @version 1.0.0.20100314
 */
class Writer_Syslog {
    protected $_appName = 'E-FW_LOG';
    
    

    function __construct (array $params = array()) {
    	
    }
    
	public function close () {
   		closelog();
    }
    
    public function write ($value) {
    	
    }
}
?>