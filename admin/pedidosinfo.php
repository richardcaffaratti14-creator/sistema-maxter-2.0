<?php

// PHPMaker 5 configuration for Table pedidos
$pedidos = new cpedidos; // Initialize table object

// Define table class
class cpedidos {

	// Define table level constants
	var $TableVar;
	var $TableName;
	var $SelectLimit = FALSE;
	var $id;
	var $Fecha;
	var $estado;
	var $ped_tarjeta;
	var $total;
	var $Descuento;
	var $nombre;
	var $apellido;
	var $telefono;
	var $idVendedor;
	var $Evento;
	var $descripcion;
	var $pedido;
	var $extra;
	var $idPresupuesto;
	var $idFotolibro;
	var $sena;
	var $fields = array();

	function cpedidos() {
		$this->TableVar = "pedidos";
		$this->TableName = "pedidos";
		$this->SelectLimit = TRUE;
		$this->id = new cField('pedidos', 'x_id', 'id', "`id`", 3, -1, FALSE);
		$this->fields['id'] =& $this->id;
		$this->Fecha = new cField('pedidos', 'x_Fecha', 'Fecha', "`Fecha`", 135, 7, FALSE);
		$this->fields['Fecha'] =& $this->Fecha;
		$this->estado = new cField('pedidos', 'x_estado', 'estado', "`estado`", 3, -1, FALSE);
		$this->fields['estado'] =& $this->estado;
		$this->ped_tarjeta = new cField('pedidos', 'x_ped_tarjeta', 'ped_tarjeta', "`ped_tarjeta`", 16, -1, FALSE);
		$this->fields['ped_tarjeta'] =& $this->ped_tarjeta;
		$this->total = new cField('pedidos', 'x_total', 'total', "`total`", 4, -1, FALSE);
		$this->fields['total'] =& $this->total;
		$this->Descuento = new cField('pedidos', 'x_Descuento', 'Descuento', "`Descuento`", 4, -1, FALSE);
		$this->fields['Descuento'] =& $this->Descuento;
		$this->nombre = new cField('pedidos', 'x_nombre', 'nombre', "`nombre`", 200, -1, FALSE);
		$this->fields['nombre'] =& $this->nombre;
		$this->apellido = new cField('pedidos', 'x_apellido', 'apellido', "`apellido`", 200, -1, FALSE);
		$this->fields['apellido'] =& $this->apellido;
		$this->telefono = new cField('pedidos', 'x_telefono', 'telefono', "`telefono`", 200, -1, FALSE);
		$this->fields['telefono'] =& $this->telefono;
		$this->idVendedor = new cField('pedidos', 'x_idVendedor', 'idVendedor', "`idVendedor`", 3, -1, FALSE);
		$this->fields['idVendedor'] =& $this->idVendedor;
		$this->Evento = new cField('pedidos', 'x_Evento', 'Evento', "`Evento`", 200, -1, FALSE);
		$this->fields['Evento'] =& $this->Evento;
		$this->descripcion = new cField('pedidos', 'x_descripcion', 'descripcion', "`descripcion`", 201, -1, FALSE);
		$this->fields['descripcion'] =& $this->descripcion;
		$this->pedido = new cField('pedidos', 'x_pedido', 'pedido', "`pedido`", 201, -1, FALSE);
		$this->fields['pedido'] =& $this->pedido;
		$this->extra = new cField('pedidos', 'x_extra', 'extra', "`extra`", 201, -1, FALSE);
		$this->fields['extra'] =& $this->extra;
		$this->idPresupuesto = new cField('pedidos', 'x_idPresupuesto', 'idPresupuesto', "`idPresupuesto`", 19, -1, FALSE);
		$this->fields['idPresupuesto'] =& $this->idPresupuesto;
		$this->idFotolibro = new cField('pedidos', 'x_idFotolibro', 'idFotolibro', "`idFotolibro`", 19, -1, FALSE);
		$this->fields['idFotolibro'] =& $this->idFotolibro;
		$this->sena = new cField('pedidos', 'x_sena', 'sena', "`sena`", 4, -1, FALSE);
		$this->fields['sena'] =& $this->sena;
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
		return "SELECT * FROM `pedidos`";
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
		return "`Fecha` DESC";
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
		return "INSERT INTO `pedidos` ($names) VALUES ($values)";
	}

	// UPDATE statement
	function UpdateSQL(&$rs) {
		$SQL = "UPDATE `pedidos` SET ";
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
		$SQL = "DELETE FROM `pedidos` WHERE ";
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
			return "pedidoslist.php";
		}
	}

	function setReturnUrl($v) {
		$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_RETURN_URL] = $v;
	}

	// View url
	function ViewUrl() {
		return $this->KeyUrl("pedidosview.php");
	}

	// Edit url
	function EditUrl() {
		return $this->KeyUrl("pedidosedit.php");
	}

	// Inline edit url
	function InlineEditUrl() {
		return $this->KeyUrl("pedidoslist.php", "a=edit");
	}

	// Copy url
	function CopyUrl() {
		return $this->KeyUrl("pedidosadd.php");
	}

	// Inline copy url
	function InlineCopyUrl() {
		return $this->KeyUrl("pedidoslist.php", "a=copy");
	}

	// Delete url
	function DeleteUrl() {
		return $this->KeyUrl("pedidosdelete.php");
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
		$this->Fecha->setDbValue($rs->fields('Fecha'));
		$this->estado->setDbValue($rs->fields('estado'));
		$this->ped_tarjeta->setDbValue($rs->fields('ped_tarjeta'));
		$this->total->setDbValue($rs->fields('total'));
		$this->Descuento->setDbValue($rs->fields('Descuento'));
		$this->nombre->setDbValue($rs->fields('nombre'));
		$this->apellido->setDbValue($rs->fields('apellido'));
		$this->telefono->setDbValue($rs->fields('telefono'));
		$this->idVendedor->setDbValue($rs->fields('idVendedor'));
		$this->Evento->setDbValue($rs->fields('Evento'));
		$this->descripcion->setDbValue($rs->fields('descripcion'));
		$this->pedido->setDbValue($rs->fields('pedido'));
		$this->extra->setDbValue($rs->fields('extra'));
		$this->idPresupuesto->setDbValue($rs->fields('idPresupuesto'));
		$this->idFotolibro->setDbValue($rs->fields('idFotolibro'));
		$this->sena->setDbValue($rs->fields('sena'));
	}

	// Render list row values
	function RenderListRow() {
		global $conn, $Security;

		// id
		$this->id->ViewValue = $this->id->CurrentValue;
		$this->id->CssStyle = "";
		$this->id->CssClass = "";
		$this->id->ViewCustomAttributes = "";

		// Fecha
		$this->Fecha->ViewValue = $this->Fecha->CurrentValue;
		$this->Fecha->ViewValue = ew_FormatDateTime($this->Fecha->ViewValue, 7);
		$this->Fecha->CssStyle = "";
		$this->Fecha->CssClass = "";
		$this->Fecha->ViewCustomAttributes = "";

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

		// ped_tarjeta
		if (!is_null($this->ped_tarjeta->CurrentValue)) {
			switch ($this->ped_tarjeta->CurrentValue) {
				case "0":
					$this->ped_tarjeta->ViewValue = "Contado";
					break;
				case "1":
					$this->ped_tarjeta->ViewValue = "TARJETA";
					break;
				default:
					$this->ped_tarjeta->ViewValue = $this->ped_tarjeta->CurrentValue;
			}
		} else {
			$this->ped_tarjeta->ViewValue = NULL;
		}
		$this->ped_tarjeta->CssStyle = "";
		$this->ped_tarjeta->CssClass = "";
		$this->ped_tarjeta->ViewCustomAttributes = "";

		// total
		$this->total->ViewValue = $this->total->CurrentValue;
		$this->total->ViewValue = ew_FormatCurrency($this->total->ViewValue, 2, -2, -2, -2);
		$this->total->CssStyle = "";
		$this->total->CssClass = "";
		$this->total->ViewCustomAttributes = "";

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

		// Evento
		$this->Evento->ViewValue = $this->Evento->CurrentValue;
		$this->Evento->CssStyle = "";
		$this->Evento->CssClass = "";
		$this->Evento->ViewCustomAttributes = "";

		// idPresupuesto
		$this->idPresupuesto->ViewValue = $this->idPresupuesto->CurrentValue;
		$this->idPresupuesto->CssStyle = "";
		$this->idPresupuesto->CssClass = "";
		$this->idPresupuesto->ViewCustomAttributes = "";

		// idFotolibro
		$this->idFotolibro->ViewValue = $this->idFotolibro->CurrentValue;
		$this->idFotolibro->CssStyle = "";
		$this->idFotolibro->CssClass = "";
		$this->idFotolibro->ViewCustomAttributes = "";

		// sena
		$this->sena->ViewValue = $this->sena->CurrentValue;
		$this->sena->CssStyle = "";
		$this->sena->CssClass = "";
		$this->sena->ViewCustomAttributes = "";

		// id
		$this->id->HrefValue = "";

		// Fecha
		$this->Fecha->HrefValue = "";

		// estado
		$this->estado->HrefValue = "";

		// ped_tarjeta
		$this->ped_tarjeta->HrefValue = "";

		// total
		$this->total->HrefValue = "";

		// nombre
		$this->nombre->HrefValue = "";

		// apellido
		$this->apellido->HrefValue = "";

		// telefono
		$this->telefono->HrefValue = "";

		// idVendedor
		$this->idVendedor->HrefValue = "";

		// Evento
		$this->Evento->HrefValue = "";

		// idPresupuesto
		$this->idPresupuesto->HrefValue = "";

		// idFotolibro
		$this->idFotolibro->HrefValue = "";

		// sena
		$this->sena->HrefValue = "";
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
