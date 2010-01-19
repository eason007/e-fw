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
 
class Class_Validator {
	public $rules = array(
		'Alnum'		=> '/[a-zA-Z0-9]+$/',
		'English' 	=> '/[a-zA-Z]+$/',
		'Number'	=> '/[0-9]+$/',
		'Chinese'	=> '/[\x{4e00}-\x{9fa5}]+$/u',
		'Date'		=> '/[0-9]{4}-(\d{1,2})-(\d{1,2})+$/',
		'Email'		=> '/^[A-Za-z0-9]+([._\-\+]*[A-Za-z0-9]+)*@([A-Za-z0-9]+[-A-Za-z0-9]*[A-Za-z0-9]+\.)+[A-Za-z0-9]+$/'
	);
	
	private $_filterRules = null;
	
	private $_validatorRules = null;
	
	private $_globalOptions = array(
		'require' 	=> true,
		'breakChain'=> true
	);
	
	private $_data = null;
	
	public $unknown  = array();
	
	public $invalid = array();
	
	public $missing = array(); 
	
	/**
	 * <pre>
	 * $validators = array(
	 * 		'title' => array (
	 * 			'rule' 	=> 'Alnum',
	 * 			'min'	=> 4,
	 * 			'max'	=> 20
	 * 		),
	 * 		'tag' 	=> array (
	 * 			'require'	=> false,
	 * 			'rule' 		=> ''
	 * 		)
	 * )
	 * </pre>
	 * 
	 * @param array $filters
	 * @param array $validators
	 * @param array $data
	 * @param array $options
	 * @return void
	 */
	public function set ($filters, $validators, &$data, $options = NULL) {
		if (!is_null($options)){
			foreach ($options as $key => $value) {
				$this->_globalOptions[$key] = $value;
			}
		}
		
		$this->_data = $data;
		$this->_filterRules = $filters;
		$this->_validatorRules = $validators;
		
		$this->invalid = $this->unknown = $this->missing = null;
	}
	
	public function filter ($fieldName = '') {
		$this->_filter();
		
		if ( ($fieldName != '') && (array_key_exists($fieldName, $this->_data)) ){
			return $this->_data[$fieldName];
		}
		else{
			return $this->_data;
		}
	}
	
	private function _filter () {
		foreach ($this->_data as $key => $value) {
			if (!array_key_exists($key, $this->_filterRules)){
				$this->unknown[] = $key;
			}
		}
		
		foreach ($this->_filterRules as $key => $value) {
			if (array_key_exists($key, $this->_data)){
				if ($this->_data[$key] != ''){
					
				}
			}
			else{
				switch (true){
					case isset($value['require']) && !$value['require']:
						break;
					case isset($value['require']) && $value['require']:
					case $this->_globalOptions['require']:
						$this->missing[] = $key;
				}
			}
		}
	}
	
	public function validate ($fieldName = '') {
		$this->_validate();
		
		if ( ($fieldName != '') && (array_key_exists($fieldName, $this->_data)) ){
			return !array_key_exists($fieldName, $this->invalid);
		}
		else{
			return is_null($this->missing) && is_null($this->invalid);
		}
	}
	
	private function _validate () {
		foreach ($this->_data as $key => $value) {
			if (!array_key_exists($key, $this->_validatorRules)){
				$this->unknown[] = $key;
			}
		}
		
		foreach ($this->_validatorRules as $key => $value) {
			if (array_key_exists($key, $this->_data)){
				if ($this->_data[$key] == ''){
					switch (true){
						case isset($value['require']) && !$value['require']:
							break;
						case isset($value['require']) && $value['require']:
						case $this->_globalOptions['require']:
							$this->invalid[$key] = 'require';
					}
					
					continue;
				}
				
				switch (true){
					case (isset($value['min'])) && mb_strlen($this->_data[$key], 'UTF-8') < $value['min']:
						$this->invalid[$key] = 'length';
						continue 2;
					case (isset($value['max'])) && mb_strlen($this->_data[$key], 'UTF-8') > $value['max']:
						$this->invalid[$key] = 'length';
						continue 2;
				}
				
				echo $key.'='.preg_match($this->rules[$value['rule']], $this->_data[$key]).'<br>';
				
				if (!preg_match($this->rules[$value['rule']], $this->_data[$key])){
					$this->invalid[$key] = 'rule';
				}
			}
			else{
				switch (true){
					case isset($value['require']) && !$value['require']:
						break;
					case isset($value['require']) && $value['require']:
					case $this->_globalOptions['require']:
						$this->missing[] = $key;
				}
			}
		}
	}
}

?>