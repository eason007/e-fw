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
 * @package Log
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2010 eason007<eason007@163.com>
 * @version 1.0.0.20100314
 */
class Writer_Db {
    protected $_db = null;
    protected $_mapping = null;

    function __construct (array $params = array()) {
    	if (isset($params['className'])){
			$this->_db = E_FW::load_Class($params['className']);
    	}
    	if (isset($params['mapping'])){
			$this->_mapping = $params['mapping'];
    	}
    }
    
	public function close () {
   		
    }
    
    public function write ($value) {
    	$_insert = array();
    	
    	foreach ($value as $k => $v) {
    		if ($_mappingName = $this->_mapping[$k]){
    			$_insert[$_mappingName] = $v;
    		}
    	}
    	
    	if (count($_insert)){
    		$this->_db->insert($_insert);
    	}
    }
}
?>