<?php
/**
 * @package Data
 */

/**
 * 数据校验类
 * 
 * <pre>
 * 本类主要着眼于对数据的操作，包含的功能有：过滤和校验。
 * </pre>
 * 
 * @package Data
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2009 eason007<eason007@163.com>
 * @version 1.0.0.20100119
 */
 
class Data_Validator {
	/**
	 * 默认的校验种类
	 * 
	 * 可以自行添加正则表达式进行扩展
	 * 
	 * @var array
	 * @access public
	 */
	public $validatorRules = array(
		'Alnum'		=> '/[a-zA-Z0-9]+$/',
		'English' 	=> '/[a-zA-Z]+$/',
		'Number'	=> '/[0-9]+$/',
		'Chinese'	=> '/[\x{4e00}-\x{9fa5}]+$/u',
		'Date'		=> '/[0-9]{4}-(\d{1,2})-(\d{1,2})+$/',
		'Email'		=> '/^[A-Za-z0-9]+([._\-\+]*[A-Za-z0-9]+)*@([A-Za-z0-9]+[-A-Za-z0-9]*[A-Za-z0-9]+\.)+[A-Za-z0-9]+$/'
	);
	
	/**
	 * 默认的过滤种类
	 * 
	 * 可以自行添加正则表达式进行扩展
	 * 
	 * @var array
	 * @access public
	 */
	public $filterRules = array (
		'Alnum'		=> '/[^a-zA-Z0-9]/',
		'English' 	=> '/[^a-zA-Z]/',
		'Number'	=> '/[^0-9]/',
		'Chinese'	=> '/[^\x{4e00}-\x{9fa5}]/u'
	);
	
	/**
	 * 
	 * @var array
	 * @access private
	 */
	private $_filterFields = null;
	
	/**
	 * 
	 * @var array
	 * @access private
	 */
	private $_validatorFields = null;
	
	/**
	 * 全局的校验设置
	 * 
	 * <pre>
	 * require
	 * 即所有经 $validators 声明的数据均为必填，除非在 $validators 中有单独指定 require
	 * </pre>
	 * 
	 * @var array
	 * @access private
	 */
	private $_globalOptions = array(
		'require' 	=> true,
		'breakChain'=> true
	);
	
	/**
	 * 
	 * @var array
	 * @access private
	 */
	private $_data = null;
	
	/**
	 * 未知的数据
	 * 
	 * 即没有在 $validators 中声明，但又存在于 $data 里的数据
	 * 
	 * @var array
	 * @access public
	 */
	public $unknown  = array();
	
	/**
	 * 校验失败的数据
	 * 
	 * <pre>
	 * 在返回的数据中，会以字段名为数组键名，具体错误的信息为值
	 * length 指数据长度不符合
	 * require 指必填字段为空
	 * rule 指无法通过校验规则
	 * 如：
	 * array (
	 * 		'title' => 'length',
	 * 		'tag'	=> 'rule'
	 * )
	 * </pre>
	 * 
	 * @var array
	 * @access public
	 */
	public $invalid = array();
	
	/**
	 * 缺失的数据
	 * 
	 * 即在 $validators 中声明，但又不存在于 $data 里的字段
	 * 
	 * @var array
	 * @access public
	 */
	public $missing = array(); 
	
	/**
	 * 设置过滤/校验的条件及数据
	 * 
	 * <pre>
	 * $filters = array(
	 * 		'title' => array (
	 * 			'rule' 	=> 'Alnum'
	 * 		),
	 * 		'tag' 	=> array (
	 * 			'rule' 		=> 'English'
	 * 		)
	 * )
	 * $validators = array(
	 * 		'title' => array (
	 * 			'rule' 	=> 'Alnum',
	 * 			'min'	=> 4,
	 * 			'max'	=> 20
	 * 		),
	 * 		'tag' 	=> array (
	 * 			'require'	=> false,
	 * 			'rule' 		=> 'English'
	 * 		)
	 * )
	 * </pre>
	 * 
	 * @access public
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
		
		$this->_data = &$data;
		$this->_filterFields = $filters;
		$this->_validatorFields = $validators;
		
		$this->invalid = $this->unknown = $this->missing = null;
	}
	
	/**
	 * 过滤数据
	 * 
	 * 如果参数为空，则返回过滤后的整个数据块，否则返回指定数据内容
	 * 
	 * @access public
	 * @param string $fieldName
	 * @return mixed
	 */
	public function filter ($fieldName = '') {
		$this->_filter();
		
		if ( ($fieldName != '') && (array_key_exists($fieldName, $this->_data)) ){
			return $this->_data[$fieldName];
		}
		else{
			return $this->_data;
		}
	}
	
	/**
	 * @access prviate
	 * @return void
	 */
	private function _filter () {
		foreach ($this->_data as $key => $value) {
			if (!array_key_exists($key, $this->_filterFields)){
				$this->unknown[] = $key;
			}
		}
		
		foreach ($this->_filterFields as $key => $value) {
			if (array_key_exists($key, $this->_data)){
				if ( ($this->_data[$key] != '') && (isset($this->filterRules[$value['rule']])) ) {
					$this->_data[$key] = preg_replace($this->filterRules[$value['rule']], '', $this->_data[$key]);
				}
			}
		}
	}
	
	/**
	 * 校验数据
	 * 
	 * 如果参数为空，则返回整个数据块的校验结果，包括是否存在缺失数据
	 * 否则返回指定的数据校验结果，但忽略是否存在于缺失数据中
	 * 
	 * @param string $fieldName
	 * @return bool
	 * @access public
	 */
	public function validate ($fieldName = '') {
		$this->_validate();
		
		if ( ($fieldName != '') && (array_key_exists($fieldName, $this->_data)) ){
			return !array_key_exists($fieldName, $this->invalid);
		}
		else{
			return is_null($this->missing) && is_null($this->invalid);
		}
	}
	
	/**
	 * @access private
	 * @return void
	 */
	private function _validate () {
		foreach ($this->_data as $key => $value) {
			if (!array_key_exists($key, $this->_validatorFields)){
				$this->unknown[] = $key;
			}
		}
		
		foreach ($this->_validatorFields as $key => $value) {
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
				
				if (!preg_match($this->validatorRules[$value['rule']], $this->_data[$key])){
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