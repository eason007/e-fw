<?php
/**
 * 数据库对象操作类
 * 
 * 本类采用的是数据入口模式。主要用于实现CRUD的基本操作。
 * 并实现基本的关联操作
 * 
 * @package Class
 * @author eason007<eason007@163.com>
 * @copyright Copyright (c) 2007-2008 eason007<eason007@163.com>
 * @version 1.0.1.20080918
 */
 
class Class_TableDataGateway {
	/**
	 * 数据表名
	 *
	 * @var string
	 * @access public
	 */
	public $tableName = null;
	/**
	 * 主键字段名
	 *
	 * @var string
	 * @access public 
	 */
	public $primaryKey = null;

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
	 * @access public
	 */
	public $belongsTo = array();
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
	 * @access public
	 */
	public $hasOne = array();
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
	 * @access public
	 */
	public $hasMany = array();
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
	 * joinTable，指第三方表的表名
	 * linkKey，指在第三方表中，保存目标表主键字段的关联字段名
	 * joinKey，指在第三方表中，保存本表主键字段关联的字段名
	 * mappingName，指目标表数据在本表中显示的别名
	 * </pre>
	 *
	 * @var array
	 * @access public
	 */
	public $manyToMany = array();

	/**
	 * 是否在执行数据库中自动执行关联操作
	 * 仅在select和del方法中有效
	 *
	 * @var bool
	 * @access public
	 */
	public $autoLink = false;

	/**
	 * 显示字段名列表
	 *
	 * @var string
	 * @access public
	 */
	public $field = null;
	/**
	 * 查询条件
	 * 
	 * <pre>
	 * 数据库操作时的条件，可接受多种格式的数据，如：
	 * 1、$clsTable->where = 23，赋为数字，则以主键字段为查找字段
	 * 2、$clsTable->where = 'name = \'dualface\''，赋为字符串，则直接作为查询条件
	 * 3、$clsTable->where = array('name <> \'dualface\'', 'sex = \'male\'')
	 * 	$clsTable->where = array('born = \'1977/10/24\'', '13')
	 * 	$clsTable->where = array('sex' => 'sex', 'id' => 1)
	 * 赋为数组，则单独解释各维，各维之间为 AND 关键字
	 * </pre>
	 *
	 * @var object
	 * @access public
	 */
	public $where = null;
	/**
	 * 其他子句
	 * 
	 * 指跟随在where之后，order之前的自定义子句
	 *
	 * @var string
	 * @access public 
	 */
	public $other = null;
	/**
	 * order子句
	 *
	 * @var string
	 * @access public
	 */
	public $order = null;
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
	 * @var object
	 * @access public
	 */
	public $limit = null;

	/**
	 * 数据库连接对象
	 *
	 * @var object
	 * @access public
	 */
	public $db = null;


	function __construct() {
	
	}

	/**
	 * 设置DB类
	 * 
	 * <pre>
	 * 传入包含有dbServer、dbPort、dbName、dbUser、dbPassword、dbType的数组
	 * $isReload代表是否重载db类，默认为否。
	 * </pre>
	 *
	 * @param array $dbParams
	 * @param bool $isReload
	 * @access public
	 */
	public function setDB ($dbParams, $isReload = false) {
		if ( (is_null($this->db)) or ($isReload) ) {
			switch ($dbParams['dbType']) {
				case 'Mysqli':
				case 'PDO' :
					$this->db = E_FW::load_Class('db_Mysql5', true, $dbParams);
					break;
			}
		}
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
		return $this->db->query($sql);
	}


	/**
	 * 查询数据
	 * 
	 * <pre>
	 * 执行的查询条件及相关的显示及排序条件，以调用方法前的
	 * where、limit、field、order、other等属性为准。而本方法执行后
	 * 将自动清除以上属性。
	 * 
	 * 默认将直接返回符合设置的数据数组，并返回相关的关联数据（autoLink为true的情况下）。
	 * 
	 * 如$link参数不为空，则无论是否autoLink是否为true，均返回$link中指定的关联数据
	 * 例子：
	 * $clsTab->select('hasOne, manyToMany, belongsTo');
	 * $clsTab->select('hasOne');
	 * 如$isExecute参数为false，则不返回数据数组，而返回解释相关属性后的T-SQL语句
	 * 如需要同时返回符合条件的总记录数，则必须指定$isCount参数为true
	 * </pre>
	 *
	 * @param string $link
	 * @param bool $isExecute
	 * @param bool $isCount
	 * @return array/string
	 * @access public
	 */
	public function select ($link = '', $isExecute = true, $isCount = false) {
		if (!is_null($this->where)){
			$conditions = $this->getWhere();
		}
		else{
			$conditions = '';
		}
		if (!is_null($this->limit)){
			$limit = $this->getLimit();
		}
		else{
			$limit = '';
		}
		if (is_null($this->field)){
			$this->field = '*';
		}
		if (!is_null($this->order)){
			$this->order = ' ORDER BY '.$this->order;
		}


		$sql = 'SELECT '.$this->field.','.$this->primaryKey;
		$sql.= ' FROM `'.$this->tableName.'` AS MT';
		$sql.= ' WHERE 1=1'.$conditions;
		$sql.= $this->other;
		$sql.= $this->order;
		$sql.= $limit;

		$c_sql = 'SELECT COUNT('.$this->primaryKey.') AS RCount';
		$c_sql.= ' FROM `'.$this->tableName.'` AS MT';
		$c_sql.= ' WHERE 1=1'.$conditions;
		$c_sql.= $this->other;
		$c_sql.= $this->order;

		$this->clear();

		if (!$isExecute) {
			return $sql;
		}

		$result = $this->db->query($sql);
		if ($result) {
			if ( ($this->autoLink) and ($link == '')){
				$link = 'belongsTo,hasOne,hasMany,manyToMany';
			}

			if ( ($this->autoLink) or ($link != '') ){
				$linkValue = explode(',', $link);

				foreach($linkValue as $val){
					$this->_getLinkData($result, trim($val));
				}
			}
		}

		if ($isCount){
			$temp['result'] = $result;
			unset($result);

			$tmp = $this->db->query($c_sql);
			$temp['resultCount'] = $tmp[0]['RCount'];

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
	 * 如需要自动插入数据到关联表，则可指定hasOne、hasMany、manyToMany等键名的数据
	 * 但本表数据必须为单行
	 * belongsTo关系不支持关联更新
	 * 
	 * 可定义 _beforeInsert 方法，以便在更新数据表之前执行相关操作。但需注意在框架中
	 * 类是可cache对象，因此需要注意定义的方法是否需要及时销毁
	 * 
	 * 可定义 _afterInsert 方法，以便在更新数据表之后执行相关操作。
	 * 
	 * 如$isExecute参数为false，则不返回数据数组，而返回解释相关属性后的T-SQL语句
	 * 如$isExecute参数为true，则使用REPLACE INTO语法，否则使用INSERT INTO
	 * </pre>
	 * 
	 * @param array $rowData
	 * @param bool $isExecute
	 * @param bool $isRplace
	 * @return array
	 * @access public
	 */
	public function insert ($rowData, $isExecute = true, $isRplace = false){
		if (!$this->_beforeInsert($rowData)) {
            return false;
        }

		$field 		= '';
		$value 		= '';
		$linkData 	= array();
		$result		= array();

		foreach($rowData as $key => $val){
			if (!is_array($val)){
				$field.= '`'.$key.'`, ';

				if (is_numeric($val)){
					$value.= $val.', ';
				}
				else{
					$value.= '\''.$this->sqlEncode($val).'\', ';
				}
			}
			else{
				$linkData[$key] = $val;
			}
		}

		if ($isRplace){
			$sql = 'REPLACE INTO `'.$this->tableName.'`';
		}
		else{
			$sql = 'INSERT INTO `'.$this->tableName.'`';
		}
		$sql.= ' ('.substr($field, 0, - 2).')';
		$sql.= ' VALUES';
		$sql.= ' ('.substr($value, 0, - 2).')';

		$this->clear();

		if (!$isExecute) {
			return $sql;
		}

		$rt = $this->db->query($sql, 1);

		if ($rt > 0){
			$rowData[$this->primaryKey] = $rt;
			$this->_afterInsert($rowData);
			
			$result[$this->primaryKey] = $rt;

			if (!empty($linkData)){
				foreach($linkData as $key => $val){
					$result[$key] = $this->_insertLinkData($key, $val, $result[$this->primaryKey]);
				}
			}
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
	 * 如需要自动更新关联表数据，则可指定hasOne、hasMany、manyToMany等键名的数据
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
	 * @param bool $isExecute
	 * @return array
	 * @access public
	 */
	public function update ($rowData, $isExecute = true) {
		if (!$this->_beforeUpdate($rowData)) {
            return false;
        }

		if (!is_null($this->where)){
			$conditions = $this->getWhere();
		}
		else{
			$conditions = '';
		}
		if (!is_null($this->limit)){
			$limit = $this->getLimit();
		}
		else{
			$limit = '';
		}
		if (!is_null($this->order)){
			$this->order = ' ORDER BY '.$this->order;
		}

		$pk = '';
		$linkData = array();

		foreach($rowData as $key => $val){
			if (!is_array($val)){
				if (strtoupper($key) == strtoupper($this->primaryKey)){
					$conditions.= ' AND `'.$this->primaryKey.'` = '.$val;
					$this->where = $val;

					continue;
				}

				if (is_numeric($val)){
					$pk.= '`'.$key.'` = '.$val.', ';
				}
				else{
					$pk.= '`'.$key.'` = \''.$this->sqlEncode($val).'\', ';
				}
			}
			else{
				$linkData[$key] = $val;
			}
		}

		if ($isExecute){
			$swtichBox = $this->autoLink;

			$this->autoLink = false;
			$this->field	= $this->primaryKey;
			$this->other	= '';

			$ID	= $this->select();

			$this->autoLink = $swtichBox;
		}


		$sql = 'UPDATE `'.$this->tableName.'`';
		$sql.= ' SET '.substr($pk, 0, - 2);
		$sql.= ' WHERE 1=1'.$conditions;
		$sql.= $this->order;
		$sql.= $limit;

		$this->clear();

		if (!$isExecute) {
			return $sql;
		}

		$result['rowCount'] = $this->db->query($sql, 2);

		if ($result['rowCount'] > 0){
			$this->_afterUpdate($rowData);
			
			$IDStr = '';
			foreach($ID as $val){
				$IDStr.= $val[$this->primaryKey].', ';
			}

			foreach($linkData as $key => $val){
				$result[$key] = $this->_updateLinkData($key, $val, $IDStr);
			}
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
	 * 如$link参数不为空，则无论是否autoLink是否为true，均按照$link中指定的关联关系操作
	 * 例子：
	 * $clsTab->del('hasOne, manyToMany, belongsTo');
	 * $clsTab->del('hasOne');
	 * 如$isExecute参数为false，则不返回数据数组，而返回解释相关属性后的T-SQL语句
	 * </pre>
	 *
	 * @param string $link
	 * @param bool $isExecute
	 * @return array
	 * @access public
	 */
	public function del ($link = '', $isExecute = true) {
		if (!$this->_beforeDelete()) {
            return false;
        }

		if (!is_null($this->where)){
			$conditions = $this->getWhere();
		}
		else{
			$conditions = '';
		}
		if (!is_null($this->limit)){
			$limit = $this->getLimit();
		}
		else{
			$limit = '';
		}
		if (!is_null($this->order)){
			$this->order = ' ORDER BY '.$this->order;
		}

		if ($isExecute){
			$swtichBox = $this->autoLink;

			$this->autoLink = false;
			$this->other	= '';

			$ID	= $this->select();

			$this->autoLink = $swtichBox;
		}

		$sql = 'DELETE FROM `'.$this->tableName.'`';
		$sql.= ' WHERE 1=1'.$conditions;
		$sql.= $this->order;
		$sql.= $limit;

		$this->clear();

		if (!$isExecute) {
			return $sql;
		}

		$result['rowCount'] = $this->db->query($sql, 2);

		if ($ID) {
			$this->_afterDelete($ID);
			
			if ( ($this->autoLink) and ($link == '') ){
				$link = 'hasOne,hasMany,manyToMany';
			}

			if ( ($this->autoLink) or ($link != '') ){
				$linkValue  = explode(',', $link);
				$IDStr		= '';

				foreach($ID as $val){
					$IDStr.= $val[$this->primaryKey].', ';
				}

				foreach($linkValue as $key => $val){
					$result[$key] = $this->_delLinkData($val, $IDStr);
				}
			}
		}

		return $result;
	}

	
	/**
	 * 供del方法使用，面向关联表操作的del方法
	 *
	 * @param string $linkType
	 * @param string $primaryKeyStr
	 * @return array
	 * @access private
	 */
	private function _delLinkData ($linkType, $primaryKeyStr) {
		if (!is_null($this->$linkType)){
			$linkSetting = $this->$linkType;
			$linkClass	 = new $linkSetting['tableClass']();

			$linkClass->autoLink = false;
			$linkClass->where	 = '`'.$linkSetting['joinKey'].'` IN ('.$primaryKeyStr.'0)';

			$sql= $linkClass->del('', false);
			$rt	= $this->db->query($sql, 2);

			unset($linkClass);
	
			return $rt['rowCount'];
		}
	}

	
	/**
	 * 供update方法使用，面向关联表操作的update方法
	 *
	 * @param string $linkType
	 * @param array $row
	 * @param string $primaryKeyStr
	 * @return array
	 * @access private
	 */
	private function _updateLinkData ($linkType, &$row, $primaryKeyStr) {
		if (!is_null($this->$linkType)){
			$linkSetting = $this->$linkType;
			$linkClass	 = new $linkSetting['tableClass']();

			switch ($linkType) {
				case 'hasOne':
					$linkClass->where = '`'.$linkClass->joinKey.'` IN ('.$primaryKeyStr."0)";
					$sql = $linkClass->update($row, false);
					$linkRT = $this->db->query($sql, 2);

					break;

				case 'hasMany':
				case 'manyToMany':
					foreach($row as $val){
						if (!empty($val[$linkClass->primaryKey])){
							$linkClass->where = $val[$linkClass->primaryKey];
							$sql = $linkClass->update($val, false);
							$linkRT[] = $this->db->query($sql, 2);
						}
					}

					break;
			}

			unset($linkClass);

			return $linkRT;
		}
	}

	
	/**
	 * 供insert方法使用，面向关联表操作的insert方法
	 *
	 * @param string $linkType
	 * @param array $row
	 * @param string $primaryID
	 * @return array
	 * @access private
	 */
	private function _insertLinkData ($linkType, &$row, $primaryID) {
		if (!is_null($this->$linkType)){
			$linkSetting = $this->$linkType;
			$linkClass	 = new $linkSetting['tableClass']();

			switch ($linkType) {
				case 'hasOne':
					$row[$linkSetting['joinKey']] = $primaryID;
					$sql = $linkClass->insert($row, false);
					$linkRT = $this->db->query($sql, 2);

					break;

				case 'hasMany':
					foreach($row as $val){
						$val[$linkSetting['joinKey']] = $primaryID;
						$sql = $linkClass->insert($val, false);
						$linkRT[] = $this->db->query($sql, 2);
					}

					break;

				case 'manyToMany':
					$switchBox = $linkClass->tableName;
					$linkClass->tableName = $linkSetting['joinTable'];
					
					foreach($row as $val){
						$val[$linkSetting['joinKey']] = $primaryID;
						$sql = $linkClass->insert($val, false);
						$linkRT[] = $this->db->query($sql, 2);
					}

					$linkClass->tableName = $switchBox;

					break;
			}

			unset($linkClass);

			return $linkRT;
		}
	}

	
	/**
	 * 供select方法使用，面向关联表操作的select方法
	 *
	 * @param array $rt
	 * @param string $linkType
	 * @access public
	 */
	public function _getLinkData (&$rt, $linkType) {
		$linkSetting = $this->$linkType;

		if (!@is_null($linkSetting['tableClass'])){
			$linkClass = new $linkSetting['tableClass']();

			switch ($linkType) {
				case 'belongsTo':
					//Article->ColumnID Join Column->ID
					foreach($rt as $val){
						$ID[] = $val[$linkSetting['joinKey']];
					}
					$IDStr = implode(',', $ID);

					$linkClass->where = '`'.$linkClass->primaryKey.'` IN ('.$IDStr.')';
					$sql = $linkClass->select('', false);
					$linkData = $this->db->query($sql);

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
					foreach($rt as $val){
						$ID[] = $val[$linkSetting['linkKey']];
					}
					$IDStr = implode(',', $ID);

					$linkClass->where = '`'.$linkSetting['joinKey'].'` IN ('.$IDStr.')';
					$sql = $linkClass->select('', false);
					$linkData = $this->db->query($sql);

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

					$linkClass->where = '`'.$linkSetting['joinKey'].'` IN ('.$IDStr.')';
					$sql = $linkClass->select('', false);
					$linkData = $this->db->query($sql);

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

					$sql = 'SELECT JT.*, MT.'.$linkSetting['joinKey'];
					$sql.= ' FROM `'.$linkSetting['joinTable'].'` AS MT';
					$sql.= ' JOIN `'.$linkClass->tableName.'` AS JT ON MT.'.$linkSetting['linkKey'].' = JT.'.$linkClass->primaryKey;
					$sql.= ' WHERE MT.'.$linkSetting['joinKey'].' IN ('.$IDStr.')';
					$linkData = $this->db->query($sql);

					foreach($rt as $key => $val){
						foreach($linkData as $v){
							if ($val[$this->primaryKey] == $v[$linkSetting['joinKey']]){
								$rt[$key][$linkSetting['mappingName']][] = $v;
							}
						}
					}

					break;
			}

			unset($linkClass);
		}
	}

	
	/**
	 * sql字符过滤
	 * 
	 * 未完成
	 *
	 * @param string $value
	 * @param int $type
	 * @return string
	 * @access public
	 */
	public function sqlEncode ($value, $type = 0){
		return addslashes($value);
	}

	/**
	 * 获取解释where属性后的 where 语句
	 *
	 * @return string
	 * @access public
	 */
	public function getWhere () {
		if (is_array($this->where)){
			$rt = '';

			foreach($this->where as $key => $val){
				if (is_numeric($key)){
					if (is_numeric($val)){
						$rt.= ' AND `'.$this->primaryKey.'` = '.$val;
					}
					else{
						$rt.= ' AND '.$val;
					}
				}
				else{
					$rt.= ' AND `'.$key.'` = \''.$val.'\'';
				}
			}
		}
		else{
			if (is_numeric($this->where)){
				$rt = ' AND `'.$this->primaryKey.'` = '.$this->where;
			}
			else{
				$rt = ' AND '.$this->sqlEncode($this->where);
			}
		}

		return $rt;
	}

	/**
	 * 获取解释 limit 属性后的 limit 子句
	 *
	 * @return string
	 * @access public
	 */
	private function getLimit () {
		if (is_array($this->limit)){
			return ' LIMIT '.$this->limit['offset'] * $this->limit['length'].','.$this->limit['length'];
		}
		else{
			return ' LIMIT '.$this->limit;
		}
	}

	/**
	 * 清理属性
	 *
	 */
	private function clear () {
		$this->field = null;
		$this->where = null;
		$this->other = null;
		$this->order = null;
		$this->limit = null;
	}


	public function _beforeInsert (){
		return true;
	}
	
	public function _afterInsert () {
		
	}

	public function _beforeUpdate () {
		return true;
	}
	
	public function _afterUpdate () {
		
	}

	public function _beforeDelete () {
		return true;
	}
	
	public function _afterDelete () {
		
	}
}

?>