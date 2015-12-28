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
    
    protected $_priorities = array(
    	LOG_EMERG,
    	LOG_ALERT,
    	LOG_CRIT,
    	LOG_ERR,
    	LOG_WARNING,
    	LOG_NOTICE,
    	LOG_INFO,
    	LOG_DEBUG
    );
    
    protected $_defaultPriority = LOG_NOTICE;

    function __construct (array $params = array()) {
    	if (isset($params['application'])) {
            $this->_appName = $params['application'];
        }
        
        openlog($this->_appName, LOG_PID, LOG_USER);
    }
    
	public function close () {
   		closelog();
    }
    
    public function write ($value) {
    	if (array_key_exists($value['priority'], $this->_priorities)) {
            $priority = $this->_priorities[$value['priority']];
        } else {
            $priority = $this->_defaultPriority;
        }

        syslog($priority, $value['message']);
    }
}
?>