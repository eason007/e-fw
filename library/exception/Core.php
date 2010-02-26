<?php
/**
 * Enter description here...
 *
 * @package Exception
 */
class Exception_Core extends Exception
{
	public $message = '';
	
    //重定义构造器使 message 变为必须被指定的属性
    public function __construct($message, $code = 0) {
        //确保所有变量都被正确赋值
        parent::__construct($message, $code);
    }
    
    // 自定义字符串输出的样式
    public function __toString() {
    	if (E_FW::get_Config('DEBUG')){
    		return parent::__toString();
    	}
    	else{
    		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    	}
    }
}
?>