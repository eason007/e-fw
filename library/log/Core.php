<?php
/**
 * @package Log
 */

/**
 * 日志类
 * 
 * <pre>
 * 
 * </pre>
 * 
 * @package Log
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2010 eason007<eason007@163.com>
 * @version 1.0.0.20100313
 */
class Log_Core {
	const EMERG   = 0;  // Emergency: system is unusable
    const ALERT   = 1;  // Alert: action must be taken immediately
    const CRIT    = 2;  // Critical: critical conditions
    const ERR     = 3;  // Error: error conditions
    const WARN    = 4;  // Warning: warning conditions
    const NOTICE  = 5;  // Notice: normal but significant condition
    const INFO    = 6;  // Informational: informational messages
    const DEBUG   = 7;  // Debug: debug messages
    
    protected $_field = array();
    
    public function log ($message, $level) {
    	
    }
    
    public function 
}
?>