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
	protected $_write = array();
	
    protected $_field = array();
    
    protected $_level = array(
    	'EMERG',		// Emergency: system is unusable
    	'ALERT',		// Alert: action must be taken immediately
    	'CRIT',			// Critical: critical conditions
    	'ERR',			// Error: error conditions
    	'WARN',			// Warning: warning conditions
    	'NOTICE',		// Notice: normal but significant condition
    	'INFO',			// Informational: informational messages
    	'DEBUG'			// Debug: debug messages
   	);
   	
   	public function __destruct() {
   		foreach ($this->_write as $writer) {
            $writer->close();
        }
   	}
   	
   	public function __call($method, $params) {
   		$levelValue = array_search(strtoupper($method), $this->_level);
   		
   		if ($levelValue){
   			return $this->log(array_shift($params), $levelValue);
   		}
   		else{
   			return false;
   		}
   	}
    
    public function log ($message, $level) {
    	$event = array_merge(array(
    		'timestamp'    => date('c'),
			'message'      => $message,
            'priority'     => $level,
            'priorityName' => $this->_level[$level]),
            $this->_field);
        
    	foreach ($this->_write as $writer) {
            $writer->write($event);
        }
    }
    
    public function addLevel ($name, $value) {
    	$name = strtoupper($name);
    	
    	$this->_level[$value] = $name;
    }
    
    public function addField ($name, $value) {
    	$this->_field = array_merge($this->_field, array($name => $value));
    }
    
    public function addWriter ($objWriter) {
    	$this->_write[] = $objWriter;
    }
}
?>