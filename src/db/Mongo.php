<?php
/**
 * @package DB
 * @subpackage Driver
 */

/**
 * @package DB
 * @subpackage Driver
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2008 eason007<eason007@163.com>
 * @version 0.0.8.20111019
 */
class DB_Driver_Mongo {
	public $dbConnect = NULL;
	private $hostConnect = NULL;
	private $errCount = 1;
	private static $_dbHash = array();
	
	function __construct($params) {
		try
		{
			$connStr = 'mongodb://';

			if (isset($params['dbUser'])) {
				$connStr.= $params['dbUser'].':'.$params['dbPassword'].'@';
			}
			$connStr.= $params['dbServer'];
			if (isset($params['dbPort'])) {
				$connStr.= ':'.$params['dbPort'];
			}

			$this->hostConnect = @new Mongo($connStr);
			$this->dbConnect = $this->hostConnect->selectDB($params['dbName']);
			
			$this->errCount = 0;
			
			return $this->dbConnect;
		}
		catch (Exception $e)
		{
			if ($this->errCount < 3) {
				$this->errCount++;

				$this->__construct($params);
			}
			else {
				throw new Exception('Mongo Service Not Running', 21);
			}
		}
	}
	
	function __destruct() {
		$this->hostConnect->close();
		$this->hostConnect = null;
	}
	
	public static function getInstance ($dbParams) {
		$hashTag = md5(strtolower($dbParams['dbServer'].
			$dbParams['dbPort'].
			$dbParams['dbName'].
			@$dbParams['dbUser'].
			@$dbParams['dbPassword']));
		
		if ( !array_key_exists($hashTag, self::$_dbHash) ) {
			self::$_dbHash[$hashTag] = new DB_Driver_Mongo($dbParams);
		}
		
		return self::$_dbHash[$hashTag]->dbConnect;
	}
}

class DB_Mongo {
	/**
	 * 数据表名
	 *
	 * @var string
	 * @access protected
	 */
	protected $tableName = NULL;
	
	/**
	 * 数据库连接信息
	 * 
	 * 可对某个表进行单独定义，为空时则读取全局配置
	 *
	 * @var array
	 * @access protected
	 */
	protected $dbParams = NULL;
	
	/**
	 * 数据库连接对象
	 *
	 * @var object
	 * @access protected
	 */
	protected $db = NULL;
	
	/**
	 * 显示字段名列表
	 *
	 * @var array
	 * @access private
	 */
	private $_field = array ();
	/**
	 * 查询条件
	 *
	 * @var array
	 * @access private
	 */
	private $_where = array ();
	/**
	 * order子句
	 *
	 * @var array
	 * @access private
	 */
	private $_order = array ();
	/**
	 * limit子句
	 *
	 * @var array
	 * @access private
	 */
	private $_limit = 0;
	
	function __construct() {
		if (is_null($this->dbParams)){
			$this->dbParams = E_FW::get_Config('MONGO');
		}
		
		$this->setDB();
	}
	
	/**
	 * 设置DB类
	 * 
	 * <pre>
	 * 传入包含有dbServer、dbPort、dbName、dbUser、dbPassword、dbType的数组
	 * </pre>
	 *
	 * @param array $dbParams
	 * @access public
	 */
	public function setDB ($dbParams = NULL) {
		if (!is_null($this->db)) {
			return ;
		}
		if (is_null($dbParams)) {
			$dbParams = $this->dbParams;
		}
		
		$this->db = DB_Driver_Mongo::getInstance($dbParams);
	}

	public function one () {
		$collection = $this->db->selectCollection($this->tableName);
		$result = $collection->findOne($this->_where, $this->_field);

		return iterator_to_array($result, false);
	}
	
	public function select ($parSet = array()) {
		$params = array(
			'isCount'	=> false
		);
		foreach ($parSet as $key => $value) {
			$params[$key] = $value;
		}
		
		$collection = $this->db->selectCollection($this->tableName);
		
		$result = $collection->find($this->_where, $this->_field);
		if ($this->_order) {
			$result = $result->sort($this->_order);
		}
		if ($this->_limit) {
			$result = $result
							->skip($this->_limit['offset'] * $this->_limit['length'])
							->limit($this->_limit['length']);
		}
		
		if ($params['isCount']){
			//需要获取统计结果
			$temp['result'] = iterator_to_array($result, false);
			
			$tmp = $result->count();
			$temp['resultCount'] = $tmp;
			
			$result = $temp;
			unset($temp);
		}
		else {
			$result = iterator_to_array($result, false);
		}
		
		//清理条件设定
		$this->clear();
		
		return $result;
	}
	
	public function insert ($rowData) {
		$collection = $this->db->selectCollection($this->tableName);
		
		$result = $collection->insert($rowData);
		//清理条件设定
		$this->clear();
		
		return $result;
	}
	
	public function update ($rowData) {
		$collection = $this->db->selectCollection($this->tableName);
		
		$result = $collection->update($this->_where, $rowData);
		
		//清理条件设定
		$this->clear();
		
		return $result;
	}
	
	public function del () {
		$collection = $this->db->selectCollection($this->tableName);
		
		$result = $collection->remove($this->_where);
		
		//清理条件设定
		$this->clear();
		
		return $result;
	}
	
	/**
	 * 设置查询的字段
	 *
	 * $clsTab->field('id, title');
	 * 
	 * @param string $p
	 * @return void
	 * @access public
	 */
	public function field ($p) {
		$this->_field = explode(',', $p);
		
		function trim_key(&$value)
		{
			$value = trim($value);
		}
		array_walk($this->_field, 'trim_key');
		$this->_field = array_fill_keys($this->_field, 1);
		
		return $this;
	}

	/**
	 * 设置条件子句
	 *
	 * @param mixed $p
	 * @return void
	 * @access public
	 */
	public function where ($p) {
		$this->_where = $p;
		
		return $this;
	}

	/**
	 * 设置排序条件子句
	 *
	 * @param string $p
	 * @return void
	 * @access public
	 */
	public function order ($p) {
		$this->_order = $p;
		
		return $this;
	}
	
	/**
	 * 设置分页条件子句
	 *
	 * @param mixed $p
	 * @return void
	 * @access public
	 */
	public function limit ($p) {
		$this->_limit = $p;
		
		return $this;
	}

	/**
	 * 清理属性
	 *
	 * @return void
	 * @access private
	 */
	private function clear () {
		$this->_limit = 0;
		$this->_where = $this->_order = $this->_field = array();
	}
}
?>