<?php

// PHPMaker 5 configuration for Table presupuestos
$presupuestos = new cpresupuestos; // Initialize table object

// Define table class
class cpresupuestos {

	// Define table level constants
	var $TableVar;
	var $TableName;
	var $SelectLimit = FALSE;
	var $id;
	var $nombre;
	var $apellido;
	var $telefono;
	var $idVendedor;
	var $evento;
	var $presupuesto;
	var $pedido;
	var $sena;
	var $subtotal;
	var $descuento;
	var $total;
	var $estado;
	var $presu_tarjeta;
	var $fields = array();

	function cpresupuestos() {
		$this->TableVar = "presupuestos";
		$this->TableName = "presupuestos";
		$this->SelectLimit = TRUE;
		$this->id = new cField('presupuestos', 'x_id', 'id', "`id`", 19, -1, FALSE);
		$this->fields['id'] =& $this->id;
		$this->nombre = new cField('presupuestos', 'x_nombre', 'nombre', "`nombre`", 200, -1, FALSE);
		$this->fields['nombre'] =& $this->nombre;
		$this->apellido = new cField('presupuestos', 'x_apellido', 'apellido', "`apellido`", 200, -1, FALSE);
		$this->fields['apellido'] =& $this->apellido;
		$this->telefono = new cField('presupuestos', 'x_telefono', 'telefono', "`telefono`", 200, -1, FALSE);
		$this->fields['telefono'] =& $this->telefono;
		$this->idVendedor = new cField('presupuestos', 'x_idVendedor', 'idVendedor', "`idVendedor`", 3, -1, FALSE);
		$this->fields['idVendedor'] =& $this->idVendedor;
		$this->evento = new cField('presupuestos', 'x_evento', 'evento', "`evento`", 200, -1, FALSE);
		$this->fields['evento'] =& $this->evento;
		$this->presupuesto = new cField('presupuestos', 'x_presupuesto', 'presupuesto', "`presupuesto`", 201, -1, FALSE);
		$this->fields['presupuesto'] =& $this->presupuesto;
		$this->pedido = new cField('presupuestos', 'x_pedido', 'pedido', "`pedido`", 201, -1, FALSE);
		$this->fields['pedido'] =& $this->pedido;
		$this->sena = new cField('presupuestos', 'x_sena', 'sena', "`sena`", 4, -1, FALSE);
		$this->fields['sena'] =& $this->sena;
		$this->subtotal = new cField('presupuestos', 'x_subtotal', 'subtotal', "`subtotal`", 4, -1, FALSE);
		$this->fields['subtotal'] =& $this->subtotal;
		$this->descuento = new cField('presupuestos', 'x_descuento', 'descuento', "`descuento`", 4, -1, FALSE);
		$this->fields['descuento'] =& $this->descuento;
		$this->total = new cField('presupuestos', 'x_total', 'total', "`total`", 4, -1, FALSE);
		$this->fields['total'] =& $this->total;
		$this->estado = new cField('presupuestos', 'x_estado', 'estado', "`estado`", 3, -1, FALSE);
		$this->fields['estado'] =& $this->estado;
		$this->presu_tarjeta = new cField('presupuestos', 'x_presu_tarjeta', 'presu_tarjeta', "`presu_tarjeta`", 16, -1, FALSE);
		$this->fields['presu_tarjeta'] =& $this->presu_tarjeta;
	}

	// Records per page
	function getRecordsPerPage() {
		return @$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_REC_PER_PAGE];
	}

	function setRecordsPerPage($v) {
		$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_REC_PER_PAGE] = $v;
	}

	// Start record number
	function getStartRecordNumber() {
		return @$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_START_REC];
	}

	function setStartRecordNumber($v) {
		$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_START_REC] = $v;
	}

	// Advanced search
	function getAdvancedSearch($fld) {
		return @$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_ADVANCED_SEARCH . "_" . $fld];
	}

	function setAdvancedSearch($fld, $v) {
		if (@$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_ADVANCED_SEARCH . "_" . $fld] <> $v) {
			$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_ADVANCED_SEARCH . "_" . $fld] = $v;
		}
	}

	// Basic search Keyword
	function getBasicSearchKeyword() {
		return @$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_BASIC_SEARCH];
	}

	function setBasicSearchKeyword($v) {
		$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_BASIC_SEARCH] = $v;
	}

	// Basic Search Type
	function getBasicSearchType() {
		return @$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_BASIC_SEARCH_TYPE];
	}

	function setBasicSearchType($v) {
		$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_BASIC_SEARCH_TYPE] = $v;
	}

	// Search where clause
	function getSearchWhere() {
		return @$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_SEARCH_WHERE];
	}

	function setSearchWhere($v) {
		$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_SEARCH_WHERE] = $v;
	}

	// Single column sort
	function UpdateSort(&$ofld) {
		if ($this->CurrentOrder == $ofld->FldName) {
			$sSortField = $ofld->FldExpression;
			$sLastSort = $ofld->getSort();
			if ($this->CurrentOrderType == "ASC" || $this->CurrentOrderType == "DESC") {
				$sThisSort = $this->CurrentOrderType;
			} else {
				$sThisSort = ($sLastSort == "ASC") ? "DESC" : "ASC";
			}
			$ofld->setSort($sThisSort);
			$this->setSessionOrderBy($sSortField . " " . $sThisSort); // Save to Session
		} else {
			$ofld->setSort("");
		}
	}

	// Session WHERE Clause
	function getSessionWhere() {
		return @$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_WHERE];
	}

	function setSessionWhere($v) {
		$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_WHERE] = $v;
	}

	// Session ORDER BY
	function getSessionOrderBy() {
		return @$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_ORDER_BY];
	}

	function setSessionOrderBy($v) {
		$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_ORDER_BY] = $v;
	}

	// Session Key
	function getKey($fld) {
		return @$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_KEY . "_" . $fld];
	}

	function setKey($fld, $v) {
		$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_KEY . "_" . $fld] = $v;
	}

	// Table level SQL
	function SqlSelect() { // Select
		return "SELECT * FROM `presupuestos`";
	}

	function SqlWhere() { // Where
		return "";
	}

	function SqlGroupBy() { // Group By
		return "";
	}

	function SqlHaving() { // Having
		return "";
	}

	function SqlOrderBy() { // Order By
		return "`id` DESC";
	}

	// SQL variables
	var $CurrentFilter; // Current filter
	var $CurrentOrder; // Current order
	var $CurrentOrderType; // Current order type

	// Report table sql
	function SQL() {
		$sFilter = $this->CurrentFilter;
		$sSort = $this->getSessionOrderBy();
		return ew_BuildSql($this->SqlSelect(), $this->SqlWhere(),
			$this->SqlGroupBy(), $this->SqlHaving(), $this->SqlOrderBy(),
			$sFilter, $sSort);
	}

	// Return table sql with list page filter
	function SelectSQL() {
		$sFilter = $this->getSessionWhere();
		if ($this->CurrentFilter <> "") {
			if ($sFilter <> "") $sFilter .= " AND ";
			$sFilter .= $this->CurrentFilter;
		}
		$sSort = $this->getSessionOrderBy();
		return ew_BuildSql($this->SqlSelect(), $this->SqlWhere(),
			$this->SqlGroupBy(), $this->SqlHaving(), $this->SqlOrderBy(),
			$sFilter, $sSort);
	}

	// Return record count
	function SelectRecordCount() {
		global $conn;
		$cnt = -1;
		$sFilter = $this->CurrentFilter;
		$this->Recordset_Selecting($this->CurrentFilter);
		if ($this->SelectLimit) {
			$sSelect = $this->SelectSQL();
			if (strtoupper(substr($sSelect, 0, 13)) == "SELECT * FROM") {
				$sSelect = "SELECT COUNT(*) FROM" . substr($sSelect, 13);
				if ($rs = $conn->Execute($sSelect)) {
					if (!$rs->EOF) $cnt = $rs->fields[0];
					$rs->Close();
				}
			}
		}
		if ($cnt == -1) {
			if ($rs = $conn->Execute($this->SelectSQL())) {
				$cnt = $rs->RecordCount();
				$rs->Close();
			}
		}
		$this->CurrentFilter = $sFilter;
		return intval($cnt);
	}

	// INSERT statement
	function InsertSQL(&$rs) {
		$names = "";
		$values = "";
		foreach ($rs as $name => $value) {
			$names .= $this->fields[$name]->FldExpression . ",";
			$values .= (is_null($value) ? "NULL" : ew_QuotedValue($value, $this->fields[$name]->FldDataType)) . ",";
		}
		if (substr($names, -1) == ",") $names = substr($names, 0, strlen($names)-1);
		if (substr($values, -1) == ",") $values = substr($values, 0, strlen($values)-1);
		return "INSERT INTO `presupuestos` ($names) VALUES ($values)";
	}

	// UPDATE statement
	function UpdateSQL(&$rs) {
		$SQL = "UPDATE `presupuestos` SET ";
		foreach ($rs as $name => $value) {
			$SQL .= $this->fields[$name]->FldExpression . "=" .
					(is_null($value) ? "NULL" : ew_QuotedValue($value, $this->fields[$name]->FldDataType)) . ",";
		}
		if (substr($SQL, -1) == ",") $SQL = substr($SQL, 0, strlen($SQL)-1);
		if ($this->CurrentFilter <> "")	$SQL .= " WHERE " . $this->CurrentFilter;
		return $SQL;
	}

	// DELETE statement
	function DeleteSQL(&$rs) {
		$SQL = "DELETE FROM `presupuestos` WHERE ";
		$SQL .= EW_DB_QUOTE_START . 'id' . EW_DB_QUOTE_END . '=' .	ew_QuotedValue($rs['id'], $this->id->FldDataType) . ' AND ';
		if (substr($SQL, -5) == " AND ") $SQL = substr($SQL, 0, strlen($SQL)-5);
		if ($this->CurrentFilter <> "")	$SQL .= " AND " . $this->CurrentFilter;
		return $SQL;
	}

	// Key filter for table
	function SqlKeyFilter() {
		return "`id` = @id@";
	}

	// Return url
	function getReturnUrl() {
		if (@$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_RETURN_URL] <> "") {
			return $_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_RETURN_URL];
		} else {
			return "presupuestoslist.php";
		}
	}

	function setReturnUrl($v) {
		$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_RETURN_URL] = $v;
	}

	// View url
	function ViewUrl() {
		return $this->KeyUrl("presupuestosview.php");
	}

	// Edit url
	function EditUrl() {
		return $this->KeyUrl("presupuestosedit.php");
	}

	// Inline edit url
	function InlineEditUrl() {
		return $this->KeyUrl("presupuestoslist.php", "a=edit");
	}

	// Copy url
	function CopyUrl() {
		return $this->KeyUrl("presupuestosadd.php");
	}

	// Inline copy url
	function InlineCopyUrl() {
		return $this->KeyUrl("presupuestoslist.php", "a=copy");
	}

	// Delete url
	function DeleteUrl() {
		return $this->KeyUrl("presupuestosdelete.php");
	}

	// Key url
	function KeyUrl($url, $action = "") {
		$sUrl = $url . "?";
		if ($action <> "") $sUrl .= $action . "&";
		if (!is_null($this->id->CurrentValue)) {
			$sUrl .= "id=" . urlencode($this->id->CurrentValue);
		} else {
			return "javascript:alert('Registro invalido! la llave es nula');";
		}
		return $sUrl;
	}

	// Function LoadRs
	// - Load Row based on Key Value
	function LoadRs($sFilter) {
		global $conn;

		// Set up filter (Sql Where Clause) and get Return Sql
		$this->CurrentFilter = $sFilter;
		$sSql = $this->SQL();
		return $conn->Execute($sSql);
	}

	// Load row values from rs
	function LoadListRowValues(&$rs) {
		$this->id->setDbValue($rs->fields('id'));
		$this->nombre->setDbValue($rs->fields('nombre'));
		$this->apellido->setDbValue($rs->fields('apellido'));
		$this->telefono->setDbValue($rs->fields('telefono'));
		$this->idVendedor->setDbValue($rs->fields('idVendedor'));
		$this->evento->setDbValue($rs->fields('evento'));
		$this->presupuesto->setDbValue($rs->fields('presupuesto'));
		$this->pedido->setDbValue($rs->fields('pedido'));
		$this->sena->setDbValue($rs->fields('sena'));
		$this->subtotal->setDbValue($rs->fields('subtotal'));
		$this->descuento->setDbValue($rs->fields('descuento'));
		$this->total->setDbValue($rs->fields('total'));
		$this->estado->setDbValue($rs->fields('estado'));
		$this->presu_tarjeta->setDbValue($rs->fields('presu_tarjeta'));
	}

	// Render list row values
	function RenderListRow() {
		global $conn, $Security;

		// id
		$this->id->ViewValue = $this->id->CurrentValue;
		$this->id->CssStyle = "";
		$this->id->CssClass = "";
		$this->id->ViewCustomAttributes = "";

		// nombre
		$this->nombre->ViewValue = $this->nombre->CurrentValue;
		$this->nombre->CssStyle = "";
		$this->nombre->CssClass = "";
		$this->nombre->ViewCustomAttributes = "";

		// apellido
		$this->apellido->ViewValue = $this->apellido->CurrentValue;
		$this->apellido->CssStyle = "";
		$this->apellido->CssClass = "";
		$this->apellido->ViewCustomAttributes = "";

		// telefono
		$this->telefono->ViewValue = $this->telefono->CurrentValue;
		$this->telefono->CssStyle = "";
		$this->telefono->CssClass = "";
		$this->telefono->ViewCustomAttributes = "";

		// idVendedor
		if (!empty($this->idVendedor->CurrentValue)) {
			$sSqlWrk = "SELECT `Vendedor` FROM `vendedores` WHERE `id` = " . ew_AdjustSql($this->idVendedor->CurrentValue) . "";
			$rswrk = $conn->Execute($sSqlWrk);
			if ($rswrk) {
				if (!$rswrk->EOF) {
					$this->idVendedor->ViewValue = $rswrk->fields('Vendedor');
				}
				$rswrk->Close();
			} else {
				$this->idVendedor->ViewValue = $this->idVendedor->CurrentValue;
			}
		} else {
			$this->idVendedor->ViewValue = NULL;
		}
		$this->idVendedor->CssStyle = "";
		$this->idVendedor->CssClass = "";
		$this->idVendedor->ViewCustomAttributes = "";

		// evento
		$this->evento->ViewValue = $this->evento->CurrentValue;
		$this->evento->CssStyle = "";
		$this->evento->CssClass = "";
		$this->evento->ViewCustomAttributes = "";

		// sena
		$this->sena->ViewValue = $this->sena->CurrentValue;
		$this->sena->ViewValue = ew_FormatCurrency($this->sena->ViewValue, 2, -2, -2, -2);
		$this->sena->CssStyle = "";
		$this->sena->CssClass = "";
		$this->sena->ViewCustomAttributes = "";

		// subtotal
		$this->subtotal->ViewValue = $this->subtotal->CurrentValue;
		$this->subtotal->ViewValue = ew_FormatCurrency($this->subtotal->ViewValue, 2, -2, -2, -2);
		$this->subtotal->CssStyle = "";
		$this->subtotal->CssClass = "";
		$this->subtotal->ViewCustomAttributes = "";

		// descuento
		$this->descuento->ViewValue = $this->descuento->CurrentValue;
		$this->descuento->ViewValue = ew_FormatCurrency($this->descuento->ViewValue, 2, -2, -2, -2);
		$this->descuento->CssStyle = "";
		$this->descuento->CssClass = "";
		$this->descuento->ViewCustomAttributes = "";

		// total
		$this->total->ViewValue = $this->total->CurrentValue;
		$this->total->ViewValue = ew_FormatCurrency($this->total->ViewValue, 2, -2, -2, -2);
		$this->total->CssStyle = "";
		$this->total->CssClass = "";
		$this->total->ViewCustomAttributes = "";

		// estado
		if (!is_null($this->estado->CurrentValue)) {
			switch ($this->estado->CurrentValue) {
				case "0":
					$this->estado->ViewValue = "Pendiente";
					break;
				case "1":
					$this->estado->ViewValue = "Pagado";
					break;
				case "2":
					$this->estado->ViewValue = "Cancelado";
					break;
				default:
					$this->estado->ViewValue = $this->estado->CurrentValue;
			}
		} else {
			$this->estado->ViewValue = NULL;
		}
		$this->estado->CssStyle = "";
		$this->estado->CssClass = "";
		$this->estado->ViewCustomAttributes = "";

		// presu_tarjeta
		if (!is_null($this->presu_tarjeta->CurrentValue)) {
			switch ($this->presu_tarjeta->CurrentValue) {
				case "0":
					$this->presu_tarjeta->ViewValue = "Contado";
					break;
				case "1":
					$this->presu_tarjeta->ViewValue = "TARJETA";
					break;
				default:
					$this->presu_tarjeta->ViewValue = $this->presu_tarjeta->CurrentValue;
			}
		} else {
			$this->presu_tarjeta->ViewValue = NULL;
		}
		$this->presu_tarjeta->CssStyle = "";
		$this->presu_tarjeta->CssClass = "";
		$this->presu_tarjeta->ViewCustomAttributes = "";

		// id
		$this->id->HrefValue = "";

		// nombre
		$this->nombre->HrefValue = "";

		// apellido
		$this->apellido->HrefValue = "";

		// telefono
		$this->telefono->HrefValue = "";

		// idVendedor
		$this->idVendedor->HrefValue = "";

		// evento
		$this->evento->HrefValue = "";

		// sena
		$this->sena->HrefValue = "";

		// subtotal
		$this->subtotal->HrefValue = "";

		// descuento
		$this->descuento->HrefValue = "";

		// total
		$this->total->HrefValue = "";

		// estado
		$this->estado->HrefValue = "";

		// presu_tarjeta
		$this->presu_tarjeta->HrefValue = "";
	}
	var $CurrentAction; // Current action
	var $EventName; // Event name
	var $EventCancelled; // Event cancelled
	var $CancelMessage; // Cancel message
	var $RowType; // Row Type
	var $CssClass; // Css class
	var $CssStyle; // Css style
	var $RowClientEvents; // Row client events

	// Display Attribute
	function DisplayAttributes() {
		$sAtt = "";
		if (trim($this->CssStyle) <> "") {
			$sAtt .= " style=\"" . trim($this->CssStyle) . "\"";
		}
		if (trim($this->CssClass) <> "") {
			$sAtt .= " class=\"" . trim($this->CssClass) . "\"";
		}
		if ($this->Export == "") {
			if (trim($this->RowClientEvents) <> "") {
				$sAtt .= " " . $this->RowClientEvents;
			}
		}
		return $sAtt;
	}

	// Export
	var $Export;

//	 ----------------
//	  Field objects
//	 ----------------
	function fields($fldname) {
		return $this->fields[$fldname];
	}

	// Table level events
	// Recordset Selecting event
	function Recordset_Selecting(&$filter) {

		// Enter your code here	
	}

	// Recordset Selected event
	function Recordset_Selected(&$rs) {

		//echo "Recordset Selected";
	}

	// Row_Selecting event
	function Row_Selecting(&$filter) {

		// Enter your code here	
	}

	// Row Selected event
	function Row_Selected(&$rs) {

		//echo "Row Selected";
	}

	// Row Rendering event
	function Row_Rendering() {

		// Enter your code here	
	}

	// Row Rendered event
	function Row_Rendered() {

		// To view properties of field class, use:
		//var_dump($this-><FieldName>); 

	}

	// Row Inserting event
	function Row_Inserting(&$rs) {

		// Enter your code here
		// To cancel, set return value to False

		return TRUE;
	}

	// Row Inserted event
	function Row_Inserted(&$rs) {

		//echo "Row Inserted";
	}

	// Row Updating event
	function Row_Updating(&$rsold, &$rsnew) {

		// Enter your code here
		// To cancel, set return value to False

		return TRUE;
	}

	// Row Updated event
	function Row_Updated(&$rsold, &$rsnew) {

		//echo "Row Updated";
	}

	// Row Deleting event
	function Row_Deleting($rs) {

		// Enter your code here
		// To cancel, set return value to False

		return TRUE;
	}

	// Row Deleted event
	function Row_Deleted(&$rs) {

		//echo "Row Deleted";
	}
}
?>
