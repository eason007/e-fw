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
class Writer_File {
    protected $_stream = null;

    function __construct ($streamOrUrl) {
		if ( is_resource($streamOrUrl) and 
			get_resource_type($streamOrUrl) == 'stream'
		) {
            $this->_stream = $streamOrUrl;
        } else {
            $this->_stream = @fopen($streamOrUrl, 'a', false);
        }
        
        if (!$this->_stream){
        	E_FW::load_File('exception_Writer');
			throw new Exception_Writer('File Not Exists.');
        }
    }
    
	public function close () {
   		if (is_resource($this->_stream)) {
            fclose($this->_stream);
        }
    }
    
    public function write ($value) {
    	if (is_array($value)){
    		$line = '';
    		foreach ($value as $k => $v) {
    			$line.= $k.':'.$v.' | ';
    		}
    		$line.= PHP_EOL;
    	}
    	else {
    		$line = (string) $value; 
    	} 
    	
    	if ($this->_stream){
    		@fwrite($this->_stream, $line);
    	}
    }
}
?>