<?php
/**
 * @package Class
 */

/**
 * 数据校验类
 * 
 * @package Class
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2009 eason007<eason007@163.com>
 * @version 1.0.0.20090108
 */
 
class Validator {
	private static $rules = array(
		'English' 	=> '/^[A-Za-z]+$/',
		'Number'	=> '!^\d+$!',
		'Chinese'	=> '^([\x00-\x7F]|[\x80-\xFE][\x40-\x7E\x80-\xFE])/',
		'Text'		=> '',
		'Date'		=> '/[0-9]{4}-[0-9]{2}-[0-9]{2}/',
		'Email'		=> '/^[A-Za-z0-9]+([._\-\+]*[A-Za-z0-9]+)*@([A-Za-z0-9]+[-A-Za-z0-9]*[A-Za-z0-9]+\.)+[A-Za-z0-9]+$/'
	);
	
	public function validate ($rule, &$value) {
		if ( $rule['require'] and $value == ''){
			return false;
		}
		
		$result = true;
		
		if (strstr($rule['type'], '|')){
			$rules = explode($rule['type'], '|');
			
			foreach($rules as $v){
				$result = $result or preg_match($v, $value);
			}
		}
		else if (strstr($rule['type'], '&')) {
			$rules = explode($rule['type'], '&');
			
			foreach($rules as $v){
				$result = $result and preg_match($v, $value);
			}
		}
		else{
			$result = preg_match($rule['type'], $value);
		}
		
		if (isset($rule['length'])) {
			$value = substr((string)$value, 0, $rule['length']);
		}
		
		return $result;
	}
}

?>