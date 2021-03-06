<?php
/**
 * @package DB
 */

/**
 * 数据库对象操作类
 * 
 * <pre>
 * 本类是 TableGateway 模式。
 * 实现了：
 * CRUD的基本操作；(v1.0)
 * 基本的关联操作；(v1.0)
 * 数据分布式存储；(v1.1)
 * 前置读缓存；(v1.2)
 * </pre>
 * 
 * @package DB
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2011 eason007<eason007@163.com>
 * @version 1.2.10.20110428
 */
 
class DB_TableGateway {
	/**
	 * 数据表名
	 *
	 * @var string
	 * @access protected
	 */
	protected $tableName = NULL;
	/**
	 * 主键字段名
	 *
	 * @var string
	 * @access protected 
	 */
	protected $primaryKey = NULL;
	
	/**
	 * 是否在执行数据库中自动执行关联操作
	 * 仅在select和del方法中有效
	 *
	 * @var bool
	 * @access protected
	 */
	protected $isLink = FALSE;
	
	/**
	 * 是否支持事务
	 * 
	 * Enter description here ...
	 * @var bool
	 * @access protected
	 */
	protected $isTransact = FALSE;
	
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
	 * 数据读缓存方式
	 * 
	 * 0=不缓存
	 * 1=表级缓存
	 * 2=块级缓存
	 * 3=行级缓存
	 *
	 * @var int
	 * @access protected
	 */
	protected $isCache = 1;
	/**
	 * 缓存字段名
	 * 
	 * 只在块级缓存下生效
	 * 
	 * @var string
	 * @access protected
	 */
	protected $cacheField = '';

	/**
	 * 从属关联
	 * 
	 * <pre>
	 * 指定与本表有主从关系的其他数据表（本表为从，对方表为主）
	 * 比较典型的例子为：
	 * 在文章表中指定与分类表的从属关系
	 * 包含键名有：
	 * tableClass，指该数据表的类名
	 * joinKey，指对方主键字段与本表关联的字段名
	 * mappingName，指对方数据在本表中显示的别名
	 * </pre>
	 *
	 * @var array
	 * @access protected
	 */
	public $belongsTo = NULL;
	/**
	 * 一对一关联
	 * 
	 * <pre>
	 * 指定与本表有一对一关系的其他数据表
	 * 比较典型的例子为：
	 * 在用户表中指定与用户详细信息表的一对一关系
	 * 包含键名有：
	 * tableClass，指该数据表的类名
	 * linkKey，指与对方关联的本表字段名，一般为本表主键。目前只在select有效
	 * joinKey，指对方与linkKey字段关联的字段名
	 * mappingName，指该表数据在本表中显示的别名
	 * </pre>
	 *
	 * @var array
	 * @access protected
	 */
	public $hasOne = NULL;
	/**
	 * 一对多关系
	 * 
	 * <pre>
	 * 指定与本表有一对多关系的其他数据表
	 * 比较典型的例子为：
	 * 在用户表中指定与用户定单表的一对多关系
	 * 包含键名有：
	 * tableClass，指该数据表的类名
	 * joinKey，指对方与本表主键字段关联的字段名
	 * mappingName，指该表数据在本表中显示的别名
	 * </pre>
	 *
	 * @var array
	 * @access protected
	 */
	public $hasMany = NULL;
	/**
	 * 多对多关系
	 * 
	 * <pre>
	 * 指定与本表有多对多关系的其他数据表
	 * 比较典型的例子为：
	 * 在用户表中指定与用户组表的多对多关系，
	 * 两表中数据的关联关系，使用第三方表保存
	 * 包含键名有：
	 * tableClass，指关联目标表的类名
	 * relateClass，指第三方表的类名
	 * linkKey，指在第三方表中，保存目标表主键字段的关联字段名
	 * joinKey，指在第三方表中，保存本表主键字段关联的字段名
	 * mappingName，指目标表数据在本表中显示的别名
	 * </pre>
	 *
	 * @var array
	 * @access protected
	 */
	public $manyToMany = NULL;

	/**
	 * 显示字段名列表
	 *
	 * @var string
	 * @access private
	 */
	private $_field = '*';
	/**
	 * 查询条件
	 * 
	 * <pre>
	 * 数据库操作时的条件，可接受多种格式的数据
	 * </pre>
	 * 
	 * <code>
	 * $clsTable->where(23)						//赋为数字，则以主键字段为查找字段
	 * $clsTable->where('name = \'dualface\'')	//赋为字符串，则直接作为查询条件
	 * $clsTable->where(array('name <> \'dualface\'', 'sex' => 'sex'))
	 * $clsTable->where(array('born = \'1977/10/24\'', '13'))
	 * $clsTable->where(array('sex' => 'sex')) 	//赋为数组，则单独解释各维，各维之间为 AND 关键字
	 * </code>
	 *
	 * @var mixed
	 * @access private
	 */
	private $_where = '';
	/**
	 * 其他子句
	 * 
	 * 指跟随在where之后，order之前的自定义子句
	 *
	 * @var string
	 * @access private 
	 */
	private $_other = '';
	/**
	 * order子句
	 *
	 * @var string
	 * @access private
	 */
	private $_order = '';
	/**
	 * limit子句
	 * 
	 * <pre>
	 * 如传入数字，则代表指定返回的记录数
	 * 如传入数组，array(offset, length)，
	 * 则offset代表页数，length代表步长
	 * 即limit offset * length, length;
	 * </pre>
	 *
	 * @var mixed
	 * @access private
	 */
	private $_limit = '';
	
	/**
	 * 缓存分析对象
	 *
	 * @var object
	 * @access private
	 */
	private $_cacheAnalytics = NULL;

	/**
	 * 数据库连接对象
	 *
	 * @var object
	 * @access protected
	 */
	protected $db = NULL;

	function __construct() {
		if (is_null($this->dbParams)){
			$this->dbParams = E_FW::get_Config('DSN');
		}
			
		//如果开启缓存，则延迟连接数据库
		if ($this->isCache) {
			$this->_cacheAnalytics = E_FW::load_Class('cache_TableAnalytics');
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
		
		switch ($dbParams['dbType']) {
			case 'Mysql':
				E_FW::load_File('db_Mysql5');
				
				$this->db = DB_Mysql5::getInstance($dbParams);
				
				break;
		}
	}
	
	public function query ($sql, $type = '', $cacheTag = '') {
		if ($this->isCache) {
			if ($type == '') {
				if ($cacheTag == '') {$cacheTag = $sql;}
				
				$result = $this->_cacheAnalytics->chkCache($this->tableName, $cacheTag);
				
				if (!$result) {
					$result = $this->db->query($sql);
					
					$this->_cacheAnalytics->setCache($this->tableName, $cacheTag, $result);
				}
			}
			else{
				$result = $this->db->query($sql, $type);
				
				$this->_cacheAnalytics->delCache($this->tableName);
			}
			
			$this->_cacheAnalytics->level = 1;
		}
		else{
			$result = $this->db->query($sql, $type);
		}
		
		return $result;
	}

	/**
	 * 执行自定义查询语句
	 * 
	 * <pre>
	 * 执行自定义查询语句，作用相当于select方法
	 * 只能'查询'，而不能'执行'
	 * </pre>
	 *
	 * @param string $sql
	 * @return array
	 * @access public
	 */
	public function selectSQL ($sql) {
		return $this->query($sql);
	}

	/**
	 * 查询数据
	 * 
	 * <pre>
	 * 执行的查询条件及相关的显示及排序条件，以调用方法前的
	 * where、limit、field、order、other等属性为准。而本方法执行后
	 * 将自动清除以上属性。
	 * 
	 * 默认将直接返回符合条件的数据数组，并返回相关的关联数据（$this->isLink 为 true 的情况下）。
	 * 
	 * parSet 参数为数组格式，目前包含的设置为：link = string, isExecute = bool, isCount = bool
	 * 默认设置为：link = null, isExecute = true, isCount = false
	 *
	 * 如 link 参数不为空，则无论是否 $this->isLink 是否为 true ，均返回$link中指定的关联数据
	 * 如 isExecute 参数为 false，则不返回数据数组，而返回解释相关属性后的T-SQL语句
	 * 如需要同时返回符合条件的总记录数，则必须指定 isCount 参数为 true
	 * </pre>
	 * 
	 * <code>
	 * $clsTab->select(array('link' => 'belongsTo'));
	 * $clsTab->select(array('isExecute' => true));
	 * $clsTab->select(array('link' => 'belongsTo', 'isCount' => true));
	 * </code>
	 *
	 * @param array $parSet
	 * @return mixed
	 * @access public
	 */
	public function select ($parSet = array()) {
		$params = array(
			'link'		=> null,
			'isExecute'	=> true,
			'isCount'	=> false,
			'isCache'	=> $this->isCache,
			'tag'		=> ''
		);
		foreach ($parSet as $key => $value) {
			$params[$key] = $value;
		}

		//组合查询语句
		$sql = 'SELECT '.$this->_field;
		if ($this->_field != '*') {
			$sql.= ','.$this->primaryKey;
		}
		$sql.= ' FROM `'.$this->tableName.'` AS MT';
		$sql.= $this->getSubSql('WHERE,OTHER,ORDER,LIMIT');
		
		//组合统计语句
		$c_sql = 'SELECT COUNT('.$this->primaryKey.') AS RCount';
		$c_sql.= ' FROM `'.$this->tableName.'` AS MT';
		$c_sql.= $this->getSubSql('WHERE,OTHER,ORDER');

		//清理条件设定
		$this->clear();

		if (!$params['isExecute']) {
			return $sql;
		}
		
		//获取查询结果
		$result = $this->query($sql, '', $params['tag']);
		
		if ($result) {
			//获取关联数据
			switch (true) {
				//默认自动连接
				case ( $this->isLink and $params['link'] === null):
					$params['link'] = 'belongsTo,hasOne,hasMany,manyToMany';
				//手动设定连接
				case ( $this->isLink and strlen($params['link']) > 0):
				case ( !$this->isLink and strlen($params['link']) > 0):
					$linkValue = explode(',', $params['link']);

					foreach($linkValue as $val){
						if (!is_null($this->$val)){
							$this->_getLinkData($result, trim($val));
						}
					}
					break;
			}
		}

		if ($params['isCount']){
			//需要获取统计结果
			$temp['result'] = $result;
			unset($result);

			$tmp = $this->query($c_sql);
			if ($tmp){
				$temp['resultCount'] = $tmp[0]['RCount'];
			}
			else{
				$temp['resultCount'] = 0;
			}

			$result = $temp;
			unset($temp);
		}

		return $result;
	}

	
	/**
	 * 插入数据
	 *
	 * <pre>
	 * 根据传入的数据数组，更新相关的数据表。数组的键名对应数据表中的字段名
	 * 返回以主键字段为键名的数据。如该键名值为0，则代表更新失败。
	 * 
	 * 如需要自动插入数据到关联表，则可指定 hasOne、hasMany、manyToMany 等键名的数据
	 * 但本表数据必须为单行
	 * belongsTo 关系不支持关联更新
	 * 
	 * 可定义 _beforeInsert 方法，以便在更新数据表之前执行相关操作。
	 * 但需注意在框架中类是可cache对象，因此需要注意定义的方法是否需要及时销毁
	 * 
	 * 可定义 _afterInsert 方法，以便在更新数据表之后执行相关操作。
	 * 
	 * parSet 参数为数组格式，目前包含的设置为：isExecute = bool, isRplace = bool, isTransact = bool
	 * 默认设置为：isExecute = true, isRplace = false, isTransact = true
	 *
	 * 如 isExecute 参数为 false，则不返回数据数组，而返回解释相关属性后的T-SQL语句
	 * 如 isRplace 参数为 true，则使用 REPLACE INTO 语法，否则使用 INSERT INTO
	 * 如 isTransact 参数为 true，则使用事务
	 * </pre>
	 * 
	 * @param array $rowData
	 * @param array $parSet
	 * @return array
	 * @access public
	 */
	public function insert ($rowData, $parSet = array()){
		$params = array(
			'isExecute'	=> true,
			'isRplace'	=> false,
			'isTransact'=> true
		);
		foreach ($parSet as $key => $value) {
			$params[$key] = $value;
		}

		$field 		= '';
		$value 		= '';
		$linkData 	= array();
		$result		= array();
		
		foreach($rowData as $key => $val){
			if (is_array($val)){
				$linkData[$key] = $val;
			}
			else{
				$field.= '`'.$key.'`, ';
				
				if ($this->isCache == 2 and $this->cacheField == $key) {
					$this->_cacheAnalytics->level = $this->isCache.$val;
				}

				if (strstr($val, 'FN:')){
					$value.= str_replace('FN:', '', $val).", ";
				}
				else{
					$value.= "'".$this->sqlEncode($val)."', ";
				}
			}
		}

		if ($params['isRplace']){
			$sql = 'REPLACE INTO `'.$this->tableName.'`';
		}
		else{
			$sql = 'INSERT INTO `'.$this->tableName.'`';
		}
		$sql.= ' ('.substr($field, 0, - 2).')';
		$sql.= ' VALUES';
		$sql.= ' ('.substr($value, 0, - 2).')';
		$this->clear();

		if (!$params['isExecute']) {
			return $sql;
		}
		
		$this->db->beginT();
		
		if (!$this->_beforeInsert($rowData)) {
			if ($params['isTransact']){
				$this->db->rollBackT();
			}
            return false;
        }
        
		$rt = $this->query($sql, 'LastID');
		$result['lastID'] = $rt;
		$isFound = false;

		if ($rt > 0){
			$rowData[$this->primaryKey] = $rt;
			$this->_afterInsert($rowData);

			if (!empty($linkData)){
				foreach($linkData as $key => $val){
					if (!is_null($this->$key)){
						$result[$key] = $this->_insertLinkData(trim($key), $val, $result['lastID']);
	
						if ($result[$key] == 0){
							$this->db->rollBackT();
	
							$result['lastID'] = 0;
							
							$isFound = true;
	
							break;
						}
					}
				}
			}
		}

		if (!$isFound && $params['isTransact']){
			$this->db->commitT();
		}

		return $result;
	}

	
	/**
	 * 更新数据
	 * 
	 * <pre>
	 * 更新数据表中的某行或多行数据。数组的键名对应数据表中的字段名
	 * 返回已更新的行数。如值为0，则代表没有任何数据行被更新
	 * 
	 * 如需要自动更新关联表数据，则可指定 hasOne、hasMany、manyToMany 等键名的数据
	 * 使用hasOne时，待更新本表数据必须为单行
	 * belongsTo关系不支持关联更新
	 * 
	 * 可定义 _beforeUpdate 方法，以便在更新数据表之前执行相关操作。但需注意在框架中
	 * 类是可cache对象，因此需要注意定义的方法是否需要及时销毁
	 * 
	 * 如$isExecute参数为false，则不返回数据数组，而返回解释相关属性后的T-SQL语句
	 * </pre>
	 *
	 * @param array $rowData
	 * @param array $parSet
	 * @return array
	 * @access public
	 */
	public function update ($rowData, $parSet = array()) {
		$params = array(
			'isExecute'	=> true,
			'isRplace'	=> false,
			'isTransact'=> true
		);
		foreach ($parSet as $key => $value) {
			$params[$key] = $value;
		}

		$pk			= '';
		$linkData 	= array();
		$rowSet 	= array();

		//插入数据格式化
		foreach($rowData as $key => $val){
			if (is_array($val)){
				$linkData[$key] = $val;
			}
			else{
				if (strtoupper($key) == strtoupper($this->primaryKey)){
					$this->where($val);

					continue;
				}

				if (strstr($val, 'FN:')){
					$pk.= '`'.$key."` = ".str_replace('FN:', '', $val).", ";
				}
				else if (strstr($val, 'OP:')){
					$pk.= '`'.$key."` = `".$key."`".str_replace('OP:', '', $val).", ";
				}
				else{
					$pk.= '`'.$key."` = '".$this->sqlEncode($val)."', ";
					$rowSet[$key] = $val;
				}
			}
		}
		$subSql = $this->getSubSql('WHERE,ORDER,LIMIT');
		$this->clear();

		$sql = 'UPDATE `'.$this->tableName.'`';
		$sql.= ' SET '.substr($pk, 0, - 2);
		$sql.= $subSql;

		if (!$params['isExecute']) {
			return $sql;
		}

		$this->db->beginT();

		if (!$this->_beforeUpdate($rowData)) {
			if ($params['isTransact']){
				$this->db->rollBackT();
			}
            return false;
        }
        
		$result['rowCount'] = $this->query($sql, 'RowCount');
		$isFound = false;

		if ($result['rowCount'] > 0){
			$this->_afterUpdate($rowData);

			if (!empty($linkData)){
				foreach($linkData as $key => $val){
					if (!is_null($this->$key)){
						$result[$key] = $this->_updateLinkData(trim($key), $val, $rowSet);
						
						if ($result[$key] == 0){
							$this->db->rollBackT();
		
							$result['rowCount'] = 0;
							
							$isFound = true;
		
							break;
						}
					}
				}
			}
		}
		
		if (!$isFound && $params['isTransact']){
			$this->db->commitT();
		}

		return $result;
	}

	
	/**
	 * 删除数据
	 * 
	 * <pre>
	 * 删除数据表中的某行或多行数据。具体删除条件及行数以where和limit属性为依据
	 * 返回已删除的行数。如值为0，则代表没有任何数据行被删除
	 * 
	 * 如需要自动删除关联表数据，则可指定$link参数
	 * 
	 * 可定义 _beforeDelete 方法，以便在更新数据表之前执行相关操作。但需注意在框架中
	 * 类是可cache对象，因此需要注意定义的方法是否需要及时销毁
	 * 
	 * 如 link 参数不为空，则无论是否 $this->isLink 是否为 true ，均返回$link中指定的关联数据
	 * 如 isExecute 参数为 false，则不返回数据数组，而返回解释相关属性后的T-SQL语句
	 * </pre>
	 * 
	 * <code>
	 * $clsTab->del(array('link' => 'hasOne,hasMany'));
	 * $clsTab->del(array('isExecute' => false));
	 * </code>
	 *
	 * @param array $parSet
	 * @return array
	 * @access public
	 */
	public function del ($parSet = array()) {
		$params = array(
			'link'		=> null,
			'isExecute'	=> true,
			'isTransact'=> true
		);
		foreach ($parSet as $key => $value) {
			$params[$key] = $value;
		}

		if ( $this->isLink and $params['link'] === null) {
			$params['link'] = 'hasOne,hasMany,manyToMany';
		}

		$subSql = $this->getSubSql('WHERE,ORDER,LIMIT');

		$sql = 'DELETE FROM `'.$this->tableName.'`';
		$sql.= $subSql;

		if ($params['isExecute']){
			if (strlen($params['link']) > 0) {
				$ID	= $this->select(array(
					'link' => ''
				));
			}
		}
		else{
			return $sql;
		}

		$this->db->beginT();

		if (!$this->_beforeDelete()) {
			if ($params['isTransact']){
				$this->db->rollBackT();
			}
            return false;
        }
        
		$result['rowCount'] = $this->query($sql, 'RowCount');

		if ($result['rowCount']) {
			$this->_afterDelete();

			if (strlen($params['link']) > 0) {
				$linkValue  = explode(',', $params['link']);
				$IDStr		= '';

				foreach($ID as $val){
					$IDStr.= $val[$this->primaryKey].', ';
				}

				foreach($linkValue as $val){
					if (!is_null($this->$val)){
						$result[$val] = $this->_delLinkData(trim($val), $IDStr);
					}
				}
			}
		}

		if ($params['isTransact']){
			$this->db->commitT();
		}

		return $result;
	}

	
	/**
	 * 供del方法使用，面向关联表操作的del方法
	 *
	 * @param string $linkType
	 * @param string $primaryKeyStr
	 * @return array
	 * @access protected
	 */
	protected function _delLinkData ($linkType, $primaryKeyStr) {
		$linkSetting = $this->$linkType;
		
		switch ($linkType) {
			case 'hasOne':
			case 'hasMany':
				$linkClass	 = E_FW::load_Class($linkSetting['tableClass']);
				
				$linkClass->where('`'.$linkSetting['joinKey'].'` IN ('.$primaryKeyStr.'0)');

				$rt = $linkClass->del(array(
					'link' => '',
					'isTransact' => false
				));
				
				break;
				
			case 'manyToMany':
				$linkClass	 = E_FW::load_Class($linkSetting['relateClass']);
				
				$linkClass->where('`'.$linkSetting['joinKey'].'` IN ('.$primaryKeyStr.'0)');

				$rt = $linkClass->del(array(
					'link' => '',
					'isTransact' => false
				));
				break;
		}
		
		unset($linkClass);

		return $rt['rowCount'];
	}

	
	/**
	 * 供update方法使用，面向关联表操作的update方法
	 *
	 * @param string $linkType
	 * @param array $row
	 * @param string $primaryKeyStr
	 * @return array
	 * @access protected
	 */
	protected function _updateLinkData ($linkType, &$row, $rowStr) {
		$linkSetting = $this->$linkType;
		$linkClass	 = E_FW::load_Class($linkSetting['tableClass']);

		switch ($linkType) {
			case 'hasOne':
				$linkRT = $linkClass
								->where(array(
									$linkSetting['joinKey'] => $rowStr[$this->primaryKey]
								))
								->update($row);

				break;

			case 'hasMany':
				$linkRT['rowCount'] = 0;
				
				foreach($row as $val){
					if (is_array($val)){
						if (!empty($val[$linkClass->primaryKey])){
							$tmp = $linkClass
										->where($val[$linkClass->primaryKey])
										->update($val);
							$linkRT['rowCount']+= $tmp['rowCount'];
						}
					}
					else{
						$tmp = $linkClass
									->where(array(
										$linkSetting['joinKey'] => $rowStr[$this->primaryKey]
									))
									->update($row);
						$linkRT['rowCount']+= $tmp['rowCount'];
					}
				}

				break;
		}

		unset($linkClass);

		return $linkRT['rowCount'];
	}

	
	/**
	 * 供insert方法使用，面向关联表操作的insert方法
	 *
	 * @param string $linkType
	 * @param array $row
	 * @param string $primaryID
	 * @return array
	 * @access protected
	 */
	protected function _insertLinkData ($linkType, &$row, $primaryID) {
		$linkSetting = $this->$linkType;
		$linkClass	 = E_FW::load_Class($linkSetting['tableClass']);

		switch ($linkType) {
			case 'hasOne':
				$row[$linkSetting['joinKey']] = $primaryID;
				$tmp = $linkClass->insert($row, array('isTransact' => false));
				$linkRT['rowCount'] = $tmp['lastID'];

				break;

			case 'hasMany':
				$linkRT['rowCount'] = 0;
				
				foreach($row as $val){
					$val[$linkSetting['joinKey']] = $primaryID;
					$tmp = $linkClass->insert($val, array('isTransact' => false));
					$linkRT['rowCount'] = $tmp['lastID'];
				}

				break;

			case 'manyToMany':
				$linkClass = E_FW::load_Class($linkSetting['relateClass']);
				$linkRT['rowCount'] = 0;
				
				foreach($row as $val){
					$val[$linkSetting['joinKey']] = $primaryID;
					$tmp = $linkClass->insert($val, array('isTransact' => false));
					$linkRT['rowCount']+= $tmp['lastID'];
				}

				break;
			
			case 'belongsTo':
				$linkRT = array(
					'rowCount' => 0
				);
		}

		unset($linkClass);

		return $linkRT['rowCount'];
	}

	
	/**
	 * 供select方法使用，面向关联表操作的select方法
	 *
	 * @param array $rt
	 * @param string $linkType
	 * @return void
	 * @access protected
	 */
	protected function _getLinkData (&$rt, $linkType) {
		if (!count($rt)){
			return ;
		}

		$linkSetting = $this->$linkType;

		$linkClass = E_FW::load_Class($linkSetting['tableClass']);

		switch ($linkType) {
			case 'belongsTo':
				//Article->ColumnID Join Column->ID
				foreach($rt as $val){
					$ID[] = $val[$linkSetting['joinKey']];
				}
				$IDStr = implode(',', $ID);

				$linkClass->where('`'.$linkClass->primaryKey.'` IN ('.$IDStr.')');
				$linkData = $linkClass->select(array(
					'link'	=> ''
				));

				foreach($rt as $key => $val){
					foreach($linkData as $k => $v){
						if ($val[$linkSetting['joinKey']] == $v[$linkClass->primaryKey]){
							$rt[$key][$linkSetting['mappingName']] = $v;
							//unset($linkData[$k]);

							break;
						}
					}
				}

				break;

			case 'hasOne':
				//User->ID Join UserProfiles->UserID
				if (!isset($linkSetting['linkKey'])) {
					$linkSetting['linkKey'] = $this->primaryKey;
				}
				
				foreach($rt as $val){
					$ID[] = $val[$linkSetting['linkKey']];
				}
				$IDStr = implode(',', $ID);

				$linkClass->where('`'.$linkSetting['joinKey'].'` IN ('.$IDStr.')');
				$linkData = $linkClass->select(array(
					'link'	=> ''
				));

				foreach($rt as $key => $val){
					foreach($linkData as $k => $v){
						if ($val[$linkSetting['linkKey']] == $v[$linkSetting['joinKey']]){
							$rt[$key][$linkSetting['mappingName']] = $v;
							unset($linkData[$k]);

							break;
						}
					}
				}

				break;

			case 'hasMany':
				//User->ID Join Order->UserID
				foreach($rt as $val){
					$ID[] = $val[$this->primaryKey];
				}
				$IDStr = implode(',', $ID);

				$linkClass->where('`'.$linkSetting['joinKey'].'` IN ('.$IDStr.')');
				$linkData = $linkClass->select(array(
					'link'	=> ''
				));

				foreach($rt as $key => $val){
					foreach($linkData as $v){
						if ($val[$this->primaryKey] == $v[$linkSetting['joinKey']]){
							$rt[$key][$linkSetting['mappingName']][] = $v;
						}
					}
				}

				break;

			case 'manyToMany':
				//User->ID Join UserRoles->UserID | UserRoles->RolesID Join Roles->ID
				foreach($rt as $val){
					$ID[] = $val[$this->primaryKey];
				}
				$IDStr = implode(',', $ID);
				
				//首先查询第三方表的关系数据
				$relateClass = E_FW::load_Class($linkSetting['relateClass']);

				$relateData = $relateClass
									->field($linkSetting['linkKey'].', '.$linkSetting['joinKey'])
									->where($linkSetting['joinKey'].' IN ('.$IDStr.')')
									->select(array(
										'link'	=> ''
									));
				unset($relateClass);
				
				foreach($relateData as $val){
					$ID[] = $val[$linkSetting['linkKey']];
				}
				$IDStr = implode(',', $ID);
				
				//根据第三方的关系数据查找目标表的记录
				$linkData = $linkClass
									->where($linkClass->primaryKey.' IN ('.$IDStr.')')
									->select(array(
										'link'	=> ''
									));
				
				//将目标记录组合到关系数据数组中
				foreach($relateData as $key => $val){
					foreach($linkData as $v){
						if ($val[$linkSetting['linkKey']] == $v[$linkClass->primaryKey]){
							$relateData[$key] = array_merge($val, $v);
						}
					}
				}
				unset($linkData);

				//将关系数据组合到最终数组
				foreach($rt as $key => $val){
					foreach($relateData as $v){
						if ($val[$this->primaryKey] == $v[$linkSetting['joinKey']]){
							$rt[$key][$linkSetting['mappingName']][] = $v;
						}
					}
				}
				unset($relateData);

				break;
		}

		unset($linkClass);
	}

	
	/**
	 * sql字符过滤
	 * 
	 * 未完成
	 *
	 * @param string $value
	 * @param int $type
	 * @return string
	 * @access protected
	 */
	protected function sqlEncode ($value, $type = 0){
		return addslashes($value);
	}

	/**
	 * 获取组合后的 SQL 子句
	 * 
	 * <pre>
	 * 可同时获取多种子句，条件以英文,号分隔
	 * </pre>
	 * 
	 * <code>
	 * $clsTab->getSubSql('WHERE');
	 * $clsTab->getSubSql('WHERE,OTHER');
	 * </code>
	 *
	 * @param string $codeList
	 * @return string
	 * @access protected
	 */
	protected function getSubSql ($codeList) {
		$codeList = explode(',', $codeList);
		$rt = '';

		foreach($codeList as $val){
			switch (trim($val)) {
				case 'WHERE':
					$rt.= $this->_where;
					break;
				case 'OTHER':
					$rt.= ' '.$this->_other;
					break;
				case 'ORDER':
					$rt.= ' '.$this->_order;
					break;
				case 'LIMIT':
					$rt.= $this->_limit;
					break;
			}
		}

		return $rt;
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
		$this->_field = $p;

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
		$rt = '';
		
		if (is_array($p)){
			foreach($p as $key => $val){
				if (is_numeric($key)){
					if (is_numeric($val)){
						$rt.= ' AND `'.$this->primaryKey.'` = '.$val;
						
						if ($this->isCache == 3) {
							$this->_cacheAnalytics->level = $this->isCache.$val;
						}
					}
					else{
						$rt.= ' AND '.$val;
					}
				}
				else{
					$rt.= ' AND `'.$key.'` = \''.$val.'\'';
					
					if ($this->isCache == 2 and $this->cacheField == $key) {
						$this->_cacheAnalytics->level = $this->isCache.$val;
					}
				}
			}
		}
		else{
			if (is_numeric($p)){
				$rt = ' AND `'.$this->primaryKey.'` = '.$p;
				
				if ($this->isCache == 3) {
					$this->_cacheAnalytics->level = $this->isCache.$p;
				}
			}
			else if (strlen($p) > 0){
				$rt = ' AND '.$this->sqlEncode($p);
			}
		}

		$this->_where = ' WHERE 1=1'.$rt;

		return $this;
	}

	/**
	 * 设置复合条件子句
	 *
	 * @param string $p
	 * @return void
	 * @access public
	 */
	public function other ($p) {
		$this->_other = $p;

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
		$this->_order = ' ORDER BY '.$p;

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
		if (is_array($p)){
			$this->_limit = ' LIMIT '.$p['offset'] * $p['length'].','.$p['length'];
		}
		else{
			$this->_limit = ' LIMIT '.$p;
		}

		return $this;
	}

	/**
	 * 清理属性
	 *
	 * @return void
	 * @access private
	 */
	private function clear () {
		$this->_field = '*';
		$this->_where = $this->_other = $this->_order = $this->_limit = '';
	}

	/**
	 * 插入操作前执行的方法 
	 * 
	 * 提供给子类复写
	 * 
	 * @access protected
	 */
	protected function _beforeInsert (){
		return true;
	}
	
	/**
	 * 插入操作后执行的方法 
	 * 
	 * 提供给子类复写
	 * 
	 * @access protected
	 */
	protected function _afterInsert () {
		
	}

	/**
	 * 更新操作前执行的方法 
	 * 
	 * 提供给子类复写
	 * 
	 * @access protected
	 */
	protected function _beforeUpdate () {
		return true;
	}
	
	/**
	 * 更新操作后执行的方法 
	 * 
	 * 提供给子类复写
	 * 
	 * @access protected
	 */
	protected function _afterUpdate () {
		
	}

	/**
	 * 删除操作前执行的方法 
	 * 
	 * 提供给子类复写
	 * 
	 * @access protected
	 */
	protected function _beforeDelete () {
		return true;
	}
	
	/**
	 * 删除操作后执行的方法 
	 * 
	 * 提供给子类复写
	 * 
	 * @access protected
	 */
	protected function _afterDelete () {
		
	}

	public function one ($set = array()) {
		$rt = $this
				->limit(1)
				->select($set);
		if ($rt){
			return $rt[0];
		}
		else{
			return false;
		}
	}

	public function flashCache ($level = '', $pt = '') {
		if ($pt) {
			$this->_cacheAnalytics->level = $this->isCache.$pt;
		}
		if ($level) {
			$this->_cacheAnalytics->level = $level;
		}
		$this->_cacheAnalytics->delCache($this->tableName);
	}
}

?>