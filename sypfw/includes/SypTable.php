<?php

#############################################################################
#############################################################################
##																									##
##						EXPERIMENTAL->BETA													##
##																									##
#############################################################################
#############################################################################

class Paginator {

	public $total_pages;
	public $total_rows;
	public $current_page;

	public function __construct($total_pages, $total_rows, $current_page) {
		$this->total_pages = $total_pages;
		$this->total_rows = $total_rows;
		$this->current_page = $current_page;
	}

}

class SypTable {

	private $_conditions = array(); //	private
	private $_joins = array();  //	private
	
	private $_available = false;	//	if when i do a get there is something to avoid
											//	"delete from table;"

	/**
	 * @var SypTable $_parent
	 */
	protected $_parent = null;  //	private ? protected? va ? no va?
	/**
	 * @var SypDatabase $_db
	 */
	protected $_db;
	private $_groupBy = '';
	private $_orderBy = '';
	private $_limit = '';
	private $_extraField = array();
	private $_extraSelect = array();
	public $_table = '';	//	protected
	protected $_fields = array();
	protected $_pks = array();
	public $fieldsQty = 0;

	public function isAvailable(){
		return $this->_available;
	}


	public function __construct() {
		$this->_db = &$GLOBALS['DB'];
		$this->fieldsQty = count($this->_fields);
		$this->_parent = &$this;
	}

	public function getExtraSelect($extra) {
		return $this->_extraField[$extra];
	}

	public function setExtraSelect($extraField, $selectQuery) {
		$this->_extraField[$extraField] = '';
		$this->_extraSelect[] = strpos($selectQuery, $extraField) === false ? $selectQuery . ' AS ' . $extraField : $selectQuery;
	}

	public function addCondition($field, $value, $comparator = '=', $logicOperator = 'AND') {
		$this->_conditions[] = array(
			 'field' => $this->fixFieldWithTable($field),
			 'value' => $value,
			 'comparator' => $comparator,
			 'logic_operator' => $logicOperator
		);
	}

	public function clearConditions() {
		$this->_conditions = array();
	}

	public function orderBy($order, $dontFix = false) {
		if ($dontFix)
			$this->_orderBy = 'ORDER BY ' . $order;
		else
			$this->_orderBy = 'ORDER BY ' . $this->fixFieldWithTable($order);
	}

	public function groupBy($group) {
		$this->_groupBy = 'GROUP BY ' . $this->fixFieldWithTable($group);
	}

	public function limit($txt) {
		$this->_limit = 'LIMIT ' . $txt;
	}

	public function update() {
		$sql = $this->createUpdateQuery();
		$cursor = $this->doQuery($sql);
	}

	public function insert() {
		$sql = $this->createInsertQuery();
		$cursor = $this->doQuery($sql);
		return $this->_db->lastInsertId();
	}

	public function save() {
		$count = 0;
		$doSelect = true;
		for ($i = 0, $m = count($this->_pks); $i < $m; $i++) {
			if ($this->{$this->_pks[$i]['name']} == '')
				$doSelect = false;
		}

		if ($doSelect) {
			//	SELECT			--------------------
			$sql = 'SELECT ' . $this->getSelectFieldsSQL();

			//	FROM TABLES		--------------------
			$sql .= 'FROM ';
			$sql .= $this->_table . ' ';

			$sql .= $this->getPkConditions();
			$c = $this->doQuery($sql);
			$count = $this->_db->getAffectedRows();
		}

		if ($count == 0)
			return $this->insert();
		else
			$this->update();
	}

	public function delete() {
		if (!$this->_available){
			//Dump::dl('NOT AVAILABLE');
			return;
		}
		$sql = $this->createDeleteQuery();
		//Dump::dl($sql);
		$cursor = $this->doQuery($sql);
	}

	public function query($sql, $asArray = false) {
		$cursor = $this->doQuery($sql);
		if ($this->_db->getAffectedRows() > 1 || $asArray)
			return $this->createObjectArray($cursor);
		else
			$this->createObject($cursor);
	}

	public function getFields() {
		return $this->_fields;
	}

	public function select() {
		return $this->executeSelect();
	}

	public function paginator($currentPage, $itemsPerPage) {
		$sql = $this->createSelectQuery($this, true);
		$cursor = $this->doQuery($sql);
		$row = $this->_db->read($cursor);

		$totalRows = $row[0];
		$pagesQty = ceil($totalRows / $itemsPerPage);
		$this->limit(($currentPage * $itemsPerPage) . "," . $itemsPerPage);
		return array(
			 'total_pages' => $pagesQty,
			 'total_rows' => $totalRows,
		);
	}

	public function pagina($currentPage, $itemsPerPage) {
		$sql = $this->createSelectQuery($this, true);
		$cursor = $this->doQuery($sql);
		$row = $this->_db->read($cursor);

		$totalRows = $row[0];
		$pagesQty = ceil($totalRows / $itemsPerPage);
		$this->limit(($currentPage * $itemsPerPage) . "," . $itemsPerPage);
		return new Paginator($pagesQty, $totalRows, $currentPage);
	}

	//	Protecteds
	//--------------------------------------
	protected function doQuery($sql) {
		if ($this->_db === NULL)
			throw new Exception("Database note defined");

		return $this->_db->query($sql);
	}

	protected function addSubchild(&$obj) {
		$this->_subChilds[] = $obj;
	}

	protected function setParent(&$obj) {
		$this->_parent = $obj;
	}

	protected function isParent() {
		return $this->_parent === null;
	}

	protected function getSelectFieldsSQL($alias = '') {
		if ($alias == '')
			$alias = $this->_table;
		for ($i = 0, $m = count($this->_fields); $i < $m; $i++) {
			$sqltmp[] = $alias . '.' . $this->_fields[$i]['name'] . ' AS ' . $alias . '_' . $this->_fields[$i]['name'];
		}

		for ($i = 0, $m = count($this->_joins); $i < $m; $i++) {
			$sqltmp[] = $this->_joins[$i]['ref']->getSelectFieldsSQL($this->_joins[$i]['alias']);
		}

		for ($i = 0, $m = count($this->_extraSelect); $i < $m; $i++) {
			$sqltmp[] = $this->_extraSelect[$i];
		}

		return implode(', ', $sqltmp) . ' ';
	}

	protected function executeGet() {
		$sql = $this->createSelectQuery($this);
		$cursor = $this->doQuery($sql);
		$this->createObject($cursor);
	}

	protected function executeSelect() {
		$sql = $this->createSelectQuery($this);
		$cursor = $this->doQuery($sql);
		return $this->createObjectArray($cursor);
	}

	private function createObjectArray($cursor) {
		//echo "Create: ".$this->_table."<br>";
		$rtnList = array();
		$c = 0;
		$get_something = false;
		while ($row = $this->_db->read($cursor)) {
			$get_something = true;
			$rtnList[$c] = unserialize(serialize($this)); //	a little bit slow
			$rtnList[$c]->_db = $this->_db;
			$rtnList[$c]->fillObject($row);
			
			for ($i = 0, $m = count($this->_joins); $i < $m; $i++) {
				$rtnList[$c]->_joins[$i]['ref']->fillObject($row, $this->_joins[$i]['alias']);
			}
			$c++;
		}
		$this->_available = $get_something;
		return $rtnList;
	}

	private function createObject($cursor) {
		$fieldOffset = 0;
		$get_something = false;
		while ($row = $this->_db->read($cursor)) {
			$get_something = true;
			$this->fillObject($row);
			$fieldOffset = $this->fieldsQty;

			for ($i = 0, $m = count($this->_joins); $i < $m; $i++) {
				$obj = &$this->_joins[$i]['ref'];
				$obj->fillObject($row, $this->_joins[$i]['alias']);
				$fieldOffset += $obj->fieldsQty;
			}
		}
		$this->_available = $get_something;
	}

	protected function addJoin($thisField, $otherField, $otherTable, $joinType, &$obj, $alias='') {
		if ($alias == '')
			$alias = $otherTable;

		$this->_joins[] = array(
			 'this_field' => $thisField,
			 'other_field' => $otherField,
			 'other_table' => $otherTable,
			 'join_type' => $joinType,
			 'alias' => $alias,
			 'ref' => $obj
		);
	}

	//	Private
	//--------------------------------------
	private function fillObject($row, $alias = '') {
		if ($alias == '')
			$alias = $this->_table;
		$get_something = false;
		for ($i = 0, $m = count($this->_fields); $i < $m; $i++) {
			$get_something = true;
			$this->{$this->_fields[$i]['name']} = ($row[$alias . '_' . $this->_fields[$i]['name']]);
		}
		$this->_available = $get_something;
		
		foreach ($this->_extraField as $key => $value) {
			$this->_extraField[$key] = $row[$key];
		}
	}

	private function fixFieldWithTable($field) {
		return strpos($field, '.') || strpos($field, '(') ? $field : $this->_table . "." . $field;
	}

	private function escape($text) {
		return mysql_real_escape_string($text);
	}

	private function unescape($text) {
		return stripcslashes($text);
	}

	private function getJoinsSQL() {
		$tmp = array();
		for ($i = 0, $m = count($this->_joins); $i < $m; $i++) {
			//				LEFT							otra_tabla						AS     ALIAS
			$txt = $this->_joins[$i]['join_type'] . ' JOIN ' . $this->_joins[$i]['other_table'] . ' ' . $this->_joins[$i]['alias'];
			if (strpos($this->_joins[$i]['this_field'], '.') > 0)
				$txt .= ' ON ( ' . $this->_joins[$i]['this_field'] . ' = ';
			else
				$txt .= ' ON ( ' . $this->_table . '.' . $this->_joins[$i]['this_field'] . ' = ';

			if (strpos($this->_joins[$i]['other_field'], '.') > 0)
				$txt .= $this->_joins[$i]['other_field'] . ' ) ';
			else
				$txt .= $this->_joins[$i]['alias'] . '.' . $this->_joins[$i]['other_field'] . ' ) ';

			$tmp[] = $txt;
		}
		return implode('', $tmp);
	}

	private function getConditionsSQL() {
		$tmp = array();
		$sql = '';
		$txt = '';
		if (count($this->_conditions) > 0) {
			$sql .= 'WHERE ';
			for ($i = 0, $m = count($this->_conditions); $i < $m; $i++) {
				$field = $this->_conditions[$i]['field'] . ' ';
				$comparator = $this->_conditions[$i]['comparator'] . ' ';
				$value = $this->_conditions[$i]['value'];

				if (strpos($value, '(') === false)
					$value = '"' . $value . '"';

				if (strpos($value, 'NULL')) {
					$comparator = '';
					$value = $this->_conditions[$i]['value'];
				}

				$txt .= $field . $comparator . $value;

				/*
				  $txt .= $this->_conditions[$i]['field'].' ';
				  $txt .= $this->_conditions[$i]['comparator'].' ';

				  if ( strpos($this->_conditions[$i]['value'], '(') === false )
				  $txt .= '"'.$this->_conditions[$i]['value'].'"';
				  elseif(strpos($this->_conditions[$i]['value'], '(') === false)
				  $txt .= '"'.$this->_conditions[$i]['value'].'"';
				  else
				  $txt .= $this->_conditions[$i]['value'];
				 */
				if ($i < ($m - 1))
					$txt .= ' ' . $this->_conditions[$i]['logic_operator'] . ' ';
				//$tmp[] = $txt;
			}
		}
		//$sql .= implode( ' AND ', $tmp );
		$rtn = $sql . $txt;
		return $rtn;
	}

	private function createSelectQuery(&$obj, $onlyCount = false) {
		//	SELECT			--------------------
		if (!$onlyCount)
			$sql = 'SELECT ' . $obj->getSelectFieldsSQL();
		else
			$sql = 'SELECT COUNT(*) as QTY ';

		//	FROM TABLES		--------------------
		$sql .= 'FROM ';
		$sql .= $obj->_table . ' ';
		$sql .= $obj->getJoinsSQL();

		//	CONDITIONS		--------------------
		$sql .= $this->getConditionsSQL();

		//	GROUP, ORDER AND LIMIT	--------------------
		if (!$onlyCount)
			$sql .= ' ' . $this->_groupBy;

		$sql .= ' ' . $this->_orderBy;
		$sql .= ' ' . $this->_limit;

		return $sql;
	}

	private function createDeleteQuery() {
		$sql = 'DELETE FROM `' . $this->_table . '`';

		//	CONDITIONS PK only		--------------------
		$tmp = array();
		$sqltmp = "\n" . ' WHERE ' . "\n";
		;
		$fv = array();
		$cond = false;
		for ($i = 0, $m = count($this->_pks); $i < $m; $i++) {
			if ($this->{$this->_fields[$i]['name']} != '' && $this->{$this->_fields[$i]['name']} != NULL)
				$cond = true;

			if ($this->_pks[$i]['type'] == 'NUMERIC')
				$fv[] = '`' . $this->_pks[$i]['name'] . '` = ' . $this->{$this->_fields[$i]['name']};
			elseif ($this->_pks[$i]['type'] == 'TEXT')
				$fv[] = '`' . $this->_pks[$i]['name'] . '` = "' . $this->escape($this->{$this->_fields[$i]['name']}) . '"';
		}
		$sqltmp .= implode(' AND ' . "\n", $fv);
		if ($cond)
			$sql .= $sqltmp;
		return $sql;
	}

	private function createUpdateQuery() {
		$sql = 'UPDATE `' . $this->_table . '` SET ' . "\n";
		$fv = array();
		for ($i = 0, $m = $this->fieldsQty; $i < $m; $i++) {
			$field = &$this->_fields[$i];
			$value = &$this->{$this->_fields[$i]['name']};


			if ($field['type'] == 'NUMERIC')
				$fv[] = '`' . $field['name'] . '` = ' . $this->valueForNum($value);
			elseif ($field['type'] == 'TEXT')
				$fv[] = '`' . $field['name'] . '` = "' . $this->escape($value) . '"';
			elseif ($field['type'] == 'DATE') {
				//echo $field['update']." ".$value."<br>";
				if ($field['update'] == 'CURRENT_TIMESTAMP' && ($value == '' || $value == '0000-00-00 00:00:00')) {
					$fv[] = '`' . $field['name'] . '` = NOW() ';
				} elseif ($field['extra'] != 'on update CURRENT_TIMESTAMP' &&
						  $field['update'] != 'IGNORE_EMPTY') {
					if (strpos($value, '(') === false)
						$fv[] = '`' . $field['name'] . '` = "' . $this->escape($value) . '"';
					else
						$fv[] = '`' . $field['name'] . '` = ' . $this->valueForNum($value);
				}
			}
		}
		$sql .= implode(', ' . "\n", $fv);

		//	CONDITIONS PK only		--------------------
		$tmp = array();
		$sql .= "\n" . ' WHERE ' . "\n";
		;
		$fv = array();
		for ($i = 0, $m = count($this->_pks); $i < $m; $i++) {
			if ($this->_pks[$i]['type'] == 'NUMERIC')
				$fv[] = '`' . $this->_pks[$i]['name'] . '` = ' . $this->{$this->_fields[$i]['name']};
			elseif ($this->_pks[$i]['type'] == 'TEXT')
				$fv[] = '`' . $this->_pks[$i]['name'] . '` = "' . $this->escape($this->{$this->_fields[$i]['name']}) . '"';
		}
		$sql .= implode(' AND ' . "\n", $fv);
		return $sql;
	}

	private function createInsertQuery() {
		$sql = 'INSERT INTO `' . $this->_table . '` ( ' . "\n";
		$f = array();
		$v = array();

		for ($i = 0, $m = $this->fieldsQty; $i < $m; $i++) {
			$field = &$this->_fields[$i];
			$value = &$this->{$this->_fields[$i]['name']};

			if ($field['type'] == 'NUMERIC') {
				$f[] = '`' . $field['name'] . '`';
				$tmpval = $this->valueForNum($value);
				if (($field['null'] == 'NO') && ($tmpval == 'NULL')) $tmpval = 0;
				$v[] = $tmpval;
			} elseif ($field ['type'] == 'TEXT') {
				$f[] = '`' . $field['name'] . '`';
				$v[] = '"' . $this->escape($value) . '"';
			} elseif ($this->_fields[$i]['type'] == 'DATE') {
				if ($value != '') {
					$f[] = '`' . $field['name'] . '`';
					$v[] = '"' . $this->escape($value) . '"';
				} elseif ($field['insert'] == 'CURRENT_TIMESTAMP') {
					$f[] = '`' . $field['name'] . '`';
					$v[] = 'NOW()';
				}
			}
		}
		$sql .= implode(', ' . "\n", $f) . "\n" . ') VALUES (' . "\n" . implode(', ' . "\n", $v) . "\n" . ')';
		return $sql;
	}

	private function getPkConditions() {
		//	CONDITIONS PK only		--------------------
		$tmp = array();
		$sqltmp = "\n" . ' WHERE ' . "\n";
		;
		$fv = array();
		$cond = false;
		for ($i = 0, $m = count($this->_pks); $i < $m; $i++) {
			if ($this->{$this->_fields[$i]['name']} != '')
				$cond = true;

			if ($this->_pks[$i]['type'] == 'NUMERIC')
				$fv[] = '`' . $this->_pks[$i]['name'] . '` = ' . $this->{$this->_fields[$i]['name']};
			elseif ($this->_pks[$i]['type'] == 'TEXT')
				$fv[] = '`' . $this->_pks[$i]['name'] . '` = "' . $this->escape($this->{$this->_fields[$i]['name']}) . '"';
		}
		$sqltmp .= implode(' AND ' . "\n", $fv);
		return $sqltmp;
	}

	private function valueForNum($num) {
		return ($num == '' ) ? 'NULL' : $num;
	}

	//-------------------
	protected function errorHandler($txt) {
		throw new Exception($txt);
	}

}
