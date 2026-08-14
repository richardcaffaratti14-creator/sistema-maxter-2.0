<?php
define("EW_PAGE_ID", "report", TRUE); // Page ID
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php

// PHPMaker 5 configuration for Table pedidosvend
$pedidosvend = new cpedidosvend; // Initialize table object

// Define table class
class cpedidosvend {

	// Define table level constants
	var $TableVar;
	var $TableName;
	var $SelectLimit = FALSE;
	var $Evento;
	var $Vendedor;
	var $Num;
	var $Subtotal;
	var $Descuento;
	var $Total;
	var $fields = array();

	function cpedidosvend() {
		$this->TableVar = "pedidosvend";
		$this->TableName = "pedidosvend";
		$this->Evento = new cField('pedidosvend', 'x_Evento', 'Evento', "pedidos.Evento", 201, -1, FALSE);
		$this->fields['Evento'] =& $this->Evento;
		$this->Vendedor = new cField('pedidosvend', 'x_Vendedor', 'Vendedor', "vendedores.Vendedor", 200, -1, FALSE);
		$this->fields['Vendedor'] =& $this->Vendedor;
		$this->Num = new cField('pedidosvend', 'x_Num', 'Num', "pedidos.id", 3, -1, FALSE);
		$this->fields['Num'] =& $this->Num;
		$this->Subtotal = new cField('pedidosvend', 'x_Subtotal', 'Subtotal', "pedidos.total", 4, -1, FALSE);
		$this->fields['Subtotal'] =& $this->Subtotal;
		$this->Descuento = new cField('pedidosvend', 'x_Descuento', 'Descuento', "pedidos.Descuento", 4, -1, FALSE);
		$this->fields['Descuento'] =& $this->Descuento;
		$this->Total = new cField('pedidosvend', 'x_Total', 'Total', "pedidos.total - pedidos.Descuento", 5, -1, FALSE);
		$this->fields['Total'] =& $this->Total;
	}

	// Report Group Level SQL
	function SqlGroupSelect() { // Select
		return "SELECT DISTINCT pedidos.Evento,vendedores.Vendedor FROM pedidos LEFT OUTER JOIN vendedores ON (pedidos.idVendedor = vendedores.id)";
	}

	function SqlGroupWhere() { // Where
		$_selevt = mysql_real_escape_string(isset($_POST['evt']) ? $_POST['evt'] : "");
		return (!empty($_selevt) ? " pedidos.Evento = '{$_selevt}'" : "");
	}

	function SqlGroupGroupBy() { // Group By
		return "";
	}

	function SqlGroupHaving() { // Having
		return "";
	}

	function SqlGroupOrderBy() { // Order By
		return "pedidos.Evento ASC,vendedores.Vendedor ASC";
	}

	// Report Detail Level SQL
	function SqlDetailSelect() { 
		// Select
		return "SELECT vendedores.Vendedor, pedidos.id AS Num, pedidos.total AS Subtotal, pedidos.Evento, pedidos.Descuento, pedidos.total - pedidos.Descuento AS Total FROM pedidos LEFT OUTER JOIN vendedores ON (pedidos.idVendedor = vendedores.id)";
	}

	function SqlDetailWhere() { // Where
		$_selevt = mysql_real_escape_string(isset($_POST['evt']) ? $_POST['evt'] : "");
		return (!empty($_selevt) ? " pedidos.Evento = '{$_selevt}'" : "");
	}

	function SqlDetailGroupBy() { // Group By
		return "";
	}

	function SqlDetailHaving() { // Having
		return "";
	}

	function SqlDetailOrderBy() { // Order By
		return "pedidos.id ASC, Vendedor";
	}

	// SQL variables
	var $CurrentFilter; // Current filter
	var $CurrentOrder; // Current order
	var $CurrentOrderType; // Current order type

	// Return report group sql
	function GroupSQL() {
		$sFilter = $this->CurrentFilter;
		$sSort = "";
		return ew_BuildSql($this->SqlGroupSelect(), $this->SqlGroupWhere(),
			 $this->SqlGroupGroupBy(), $this->SqlGroupHaving(),
			 $this->SqlGroupOrderBy(), $sFilter, $sSort);
	}

	// Return report detail sql
	function DetailSQL() {
		$sFilter = $this->CurrentFilter;
		$sSort = "";
		return ew_BuildSql($this->SqlDetailSelect(), $this->SqlDetailWhere(),
			$this->SqlDetailGroupBy(), $this->SqlDetailHaving(),
			$this->SqlDetailOrderBy(), $sFilter, $sSort);
	}

	// Return url
	function getReturnUrl() {
		if (@$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_RETURN_URL] <> "") {
			return $_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_RETURN_URL];
		} else {
			return "pedidosvendlist.php";
		}
	}

	function setReturnUrl($v) {
		$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_RETURN_URL] = $v;
	}

	// View url
	function ViewUrl() {
		return $this->KeyUrl("pedidosvendview.php");
	}

	// Edit url
	function EditUrl() {
		return $this->KeyUrl("pedidosvendedit.php");
	}

	// Inline edit url
	function InlineEditUrl() {
		return $this->KeyUrl("pedidosvendlist.php", "a=edit");
	}

	// Copy url
	function CopyUrl() {
		return $this->KeyUrl("pedidosvendadd.php");
	}

	// Inline copy url
	function InlineCopyUrl() {
		return $this->KeyUrl("pedidosvendlist.php", "a=copy");
	}

	// Delete url
	function DeleteUrl() {
		return $this->KeyUrl("pedidosvenddelete.php");
	}

	// Key url
	function KeyUrl($url, $action = "") {
		$sUrl = $url . "?";
		if ($action <> "") $sUrl .= $action . "&";
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
		$this->Evento->setDbValue($rs->fields('Evento'));
		$this->Vendedor->setDbValue($rs->fields('Vendedor'));
		$this->Num->setDbValue($rs->fields('Num'));
		$this->Subtotal->setDbValue($rs->fields('Subtotal'));
		$this->Descuento->setDbValue($rs->fields('Descuento'));
		$this->Total->setDbValue($rs->fields('Total'));
	}

	// Render list row values
	function RenderListRow() {
		global $conn, $Security;

		// Evento
		$pedidosvend->Evento->ViewValue = $pedidosvend->Evento->CurrentValue;
		$pedidosvend->Evento->CssStyle = "";
		$pedidosvend->Evento->CssClass = "";
		$pedidosvend->Evento->ViewCustomAttributes = "";

		// Vendedor
		$pedidosvend->Vendedor->ViewValue = $pedidosvend->Vendedor->CurrentValue;
		$pedidosvend->Vendedor->CssStyle = "";
		$pedidosvend->Vendedor->CssClass = "";
		$pedidosvend->Vendedor->ViewCustomAttributes = "";

		// Num
		$pedidosvend->Num->ViewValue = $pedidosvend->Num->CurrentValue;
		$pedidosvend->Num->CssStyle = "";
		$pedidosvend->Num->CssClass = "";
		$pedidosvend->Num->ViewCustomAttributes = "";

		// Subtotal
		$pedidosvend->Subtotal->ViewValue = $pedidosvend->Subtotal->CurrentValue;
		$pedidosvend->Subtotal->ViewValue = ew_FormatCurrency($pedidosvend->Subtotal->ViewValue, 2, -2, -2, -2);
		$pedidosvend->Subtotal->CssStyle = "text-align:right;";
		$pedidosvend->Subtotal->CssClass = "";
		$pedidosvend->Subtotal->ViewCustomAttributes = "";

		// Descuento
		$pedidosvend->Descuento->ViewValue = $pedidosvend->Descuento->CurrentValue;
		$pedidosvend->Descuento->ViewValue = ew_FormatCurrency($pedidosvend->Descuento->ViewValue, 2, -2, -2, -2);
		$pedidosvend->Descuento->CssStyle = "text-align:right;";
		$pedidosvend->Descuento->CssClass = "";
		$pedidosvend->Descuento->ViewCustomAttributes = "";

		// Total
		$pedidosvend->Total->ViewValue = $pedidosvend->Total->CurrentValue;
		$pedidosvend->Total->ViewValue = ew_FormatCurrency($pedidosvend->Total->ViewValue, 2, -2, -2, -2);
		$pedidosvend->Total->CssStyle = "font-weight:bold;text-align:right;";
		$pedidosvend->Total->CssClass = "";
		$pedidosvend->Total->ViewCustomAttributes = "";

		// Evento
		$pedidosvend->Evento->HrefValue = "";

		// Vendedor
		$pedidosvend->Vendedor->HrefValue = "";

		// Num
		$pedidosvend->Num->HrefValue = "";

		// Subtotal
		$pedidosvend->Subtotal->HrefValue = "";

		// Descuento
		$pedidosvend->Descuento->HrefValue = "";

		// Total
		$pedidosvend->Total->HrefValue = "";
	}
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
}
?>
<?php include "userfn50.php" ?>
<?php include "usuariosinfo.php" ?>
<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // Always modified
header("Cache-Control: private, no-store, no-cache, must-revalidate"); // HTTP/1.1 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0
?>
<?php @set_time_limit(999); // Set the maximum execution time (seconds) ?>
<?php

// Open connection to the database
$conn = ew_Connect();
?>
<?php
$Security = new cAdvancedSecurity();
?>
<?php
if (!$Security->IsLoggedIn()) $Security->AutoLogin();
$Security->LoadCurrentUserLevel('pedidosvend');
if (!$Security->IsLoggedIn()) {
	$Security->SaveLastUrl();
	Page_Terminate("login.php");
}
if (!$Security->CanReport()) {
	$Security->SaveLastUrl();
	Page_Terminate("login.php");
}
?>
<?php

// Common page loading event (in userfn*.php)
Page_Loading();
?>
<?php

// Page load event, used in current page
Page_Load();
?>
<?php
$pedidosvend->Export = @$_GET["export"]; // Get export parameter
$sExport = $pedidosvend->Export; // Get export parameter, used in header
$sExportFile = $pedidosvend->TableVar; // Get export file, used in header
?>
<?php
?>
<?php
$nRecCount = 0;
$nGrpRecs = 0;
$nDtlnRecCount = 0;
$nDtlRecs = 0;
$sFilter = "";
$sDbMasterFilter = "";
$sDbDetailFilter = "";
$sCmd = "";
$vGrps = ew_InitArray(3, NULL);
$nCntRecs = ew_InitArray(3, 0);
$bLvlBreak = ew_InitArray(3, FALSE);
$nTotals = ew_Init2DArray(3, 5, 0);
$nMaxs = ew_Init2DArray(3, 5, 0);
$nMins = ew_Init2DArray(3, 5, 0);
?>
<?php include "header.php" ?>
<script language="JavaScript" type="text/javascript">
<!--

// Write your client script here, no need to add script tags.
// To include another .js script, use:
// ew_ClientScriptInclude("my_javascript.js"); 
//-->

</script>
<?php if ($pedidosvend->Export == "") { ?>
<?php } ?>
<p><span class="phpmaker">Pedidos por eventos y vendedor

<form method="post">
Evento <select name="evt" style="font-size:14px; padding:3px 5px;">
<option value=""></option>
<?
$_selevt = isset($_POST['evt']) ? $_POST['evt'] : "";
$evtrs = mysql_query("select distinct Evento from pedidos order by Evento");
while ($e = mysql_fetch_array($evtrs)) {
	$tmp = trim(htmlentities($e['Evento']));
	if (empty($tmp) || is_null($tmp)) continue;
	?><option value="<?= htmlentities($e['Evento']) ?>" <?= $e['Evento'] == $_selevt ? "selected" : "" ?>><?=htmlentities($e['Evento'])?></option><?				
}
?>
</select> <input type="Submit" value="Aplicar" id="Submit" name="Submit">
</form>

</span></p>
<form method="post">
<table class="ewReportTable" cellspacing="-1">
<?php
$sFilter = "";
if (!$Security->CanReport()) {
	if ($sFilter <> "") $sFilter .= " AND ";
	$sFilter .= "(0=1)";
}
if ($sDbDetailFilter <> "") {
	if ($sFilter <> "") $sFilter .= " AND ";
	$sFilter .= "(" . $sDbDetailFilter . ")";
}

// Set up filter and load Group level sql
$pedidosvend->CurrentFilter = $sFilter;
$sSql = $pedidosvend->GroupSQL();

// echo $sSql;
// Load recordset

$rs = $conn->Execute($sSql);

// Get First Row
if (!$rs->EOF) {
	$pedidosvend->Evento->setDbValue($rs->fields('Evento'));
	$vGrps[0] = $pedidosvend->Evento->DbValue;
	$pedidosvend->Vendedor->setDbValue($rs->fields('Vendedor'));
	$vGrps[1] = $pedidosvend->Vendedor->DbValue;
}
$nRecCount = 0;
$nCntRecs[0] = 0;
ChkLvlBreak();
while (!$rs->EOF) {

	// Render for view
	$pedidosvend->RowType = EW_ROWTYPE_VIEW;
	RenderRow();

	// Show group headers
	if ($bLvlBreak[1]) { // Reset counter and aggregation
?>
	<tr><td class="ewGroupField ewGroupField_1"><span class="phpmaker">Evento</span></td>
	<td colspan=4 class="ewGroupName ewGroupName_1"><span class="phpmaker">
<div<?php echo $pedidosvend->Evento->ViewAttributes() ?>><?php echo $pedidosvend->Evento->ViewValue ?></div>
</span></td></tr>
<?php
	}
	if ($bLvlBreak[2]) { // Reset counter and aggregation
?>
	<tr><td class="ewGroupField ewGroupField_2"><span class="phpmaker">Vendedor</span></td>
	<td colspan=4 class="ewGroupName ewGroupName_2"><span class="phpmaker">
<div<?php echo $pedidosvend->Vendedor->ViewAttributes() ?>><?php echo $pedidosvend->Vendedor->ViewValue ?></div>
</span></td></tr>
<?php
	}

	// Get detail records
	$sFilter = "";
	if ($sFilter <> "") $sFilter .= " AND ";
	if (is_null($pedidosvend->Evento->CurrentValue)) {
		$sFilter .= "(pedidos.Evento IS NULL)";
	} else {
		$sFilter .= "(pedidos.Evento = '" . ew_AdjustSql($pedidosvend->Evento->CurrentValue) . "')";
	}
	if ($sFilter <> "") $sFilter .= " AND ";
	if (is_null($pedidosvend->Vendedor->CurrentValue)) {
		$sFilter .= "(vendedores.Vendedor IS NULL)";
	} else {
		$sFilter .= "(vendedores.Vendedor = '" . ew_AdjustSql($pedidosvend->Vendedor->CurrentValue) . "')";
	}
	if ($sDbDetailFilter <> "") {
		if ($sFilter <> "") $sFilter .= " AND ";
		$sFilter .= "(" . $sDbDetailFilter . ")";
	}
	if (!$Security->CanReport()) {
		if ($sFilter <> "") $sFilter .= " AND ";
		$sFilter .= "(0=1)";
	}

	// Set up detail SQL
	$pedidosvend->CurrentFilter = $sFilter;
	$sSql = $pedidosvend->DetailSQL();

	// Load detail records
	$rsdtl = $conn->Execute($sSql);
	$nDtlRecs = $rsdtl->RecordCount();

	// Initialize Aggregate
	if (!$rsdtl->EOF) {
		$nRecCount++;
		$pedidosvend->Subtotal->setDbValue($rsdtl->fields('Subtotal'));
		$pedidosvend->Descuento->setDbValue($rsdtl->fields('Descuento'));
		$pedidosvend->Total->setDbValue($rsdtl->fields('Total'));
	}
	if ($nRecCount == 1) {
		$nCntRecs[0] = 0;
		$nTotals[0][1] = 0;
		$nTotals[0][2] = 0;
		$nTotals[0][3] = 0;
	}
	for ($i = 1; $i <= 2; $i++) {
		if ($bLvlBreak[$i]) { // Reset counter and aggregation
			$nCntRecs[$i] = 0;
			$nTotals[$i][1] = 0;
			$nTotals[$i][2] = 0;
			$nTotals[$i][3] = 0;
		}
	}
	$nCntRecs[0] += $nDtlRecs;
	$nCntRecs[1] += $nDtlRecs;
	$nCntRecs[2] += $nDtlRecs;
?>
	<tr>
		<td></td>
		<td valign="top" class="ewGroupHeader"><span class="phpmaker">N?mero</span></td>
		<td valign="top" class="ewGroupHeader"><span class="phpmaker">Subtotal</span></td>
		<td valign="top" class="ewGroupHeader"><span class="phpmaker">Descuento</span></td>
		<td valign="top" class="ewGroupHeader"><span class="phpmaker">Total</span></td>
	</tr>
<?php
	while (!$rsdtl->EOF) {
		$pedidosvend->Num->setDbValue($rsdtl->fields('Num'));
		$pedidosvend->Subtotal->setDbValue($rsdtl->fields('Subtotal'));
		$nTotals[0][1] += $pedidosvend->Subtotal->CurrentValue;
		$nTotals[1][1] += $pedidosvend->Subtotal->CurrentValue;
		$nTotals[2][1] += $pedidosvend->Subtotal->CurrentValue;
		$pedidosvend->Descuento->setDbValue($rsdtl->fields('Descuento'));
		$nTotals[0][2] += $pedidosvend->Descuento->CurrentValue;
		$nTotals[1][2] += $pedidosvend->Descuento->CurrentValue;
		$nTotals[2][2] += $pedidosvend->Descuento->CurrentValue;
		$pedidosvend->Total->setDbValue($rsdtl->fields('Total'));
		$nTotals[0][3] += $pedidosvend->Total->CurrentValue;
		$nTotals[1][3] += $pedidosvend->Total->CurrentValue;
		$nTotals[2][3] += $pedidosvend->Total->CurrentValue;

		// Render for view
		$pedidosvend->RowType = EW_ROWTYPE_VIEW;
		RenderRow();
?>
	<tr>
		<td></td>
		<td>
<div<?php echo $pedidosvend->Num->ViewAttributes() ?>><a style="text-decoration:none" href="pedidosview.php?id=<?php echo $pedidosvend->Num->ViewValue ?>" target="_blank"><?php echo $pedidosvend->Num->ViewValue ?></a></div>
</td>
		<td><span class="phpmaker">
<div<?php echo $pedidosvend->Subtotal->ViewAttributes() ?>><?php echo $pedidosvend->Subtotal->ViewValue ?></div>
</span></td>
		<td><span class="phpmaker">
<div<?php echo $pedidosvend->Descuento->ViewAttributes() ?>><?php echo $pedidosvend->Descuento->ViewValue ?></div>
</span></td>
		<td><span class="phpmaker">
<div<?php echo $pedidosvend->Total->ViewAttributes() ?>><?php echo $pedidosvend->Total->ViewValue ?></div>
</span></td>
	</tr>
<?php
		$rsdtl->MoveNext();
	}
	$rsdtl->Close();

	// Save old group data
	$vGrps[0] = $pedidosvend->Evento->CurrentValue;
	$vGrps[1] = $pedidosvend->Vendedor->CurrentValue;

	// Get next record
	$rs->MoveNext();
	if ($rs->EOF) {
		$nRecCount = 0; // EOF, force all level breaks
	} else {
		$pedidosvend->Evento->setDbValue($rs->fields('Evento'));
		$pedidosvend->Vendedor->setDbValue($rs->fields('Vendedor'));
	}
	ChkLvlBreak();

	// Show Footers
	if ($bLvlBreak[2]) {
		$pedidosvend->Vendedor->CurrentValue = $vGrps[1];

		// Render row for view
		$pedidosvend->RowType = EW_ROWTYPE_VIEW;
		RenderRow();
		$pedidosvend->Vendedor->CurrentValue = $pedidosvend->Vendedor->DbValue;
?>
	<tr><td colspan=5 class="ewGroupSummary"><span class="phpmaker"><?php echo $pedidosvend->Vendedor->ViewValue ?>: <?php echo ew_FormatNumber($nCntRecs[2],0) ?> pedidos</span></td></tr>
<?php
	$pedidosvend->Subtotal->CurrentValue = $nTotals[2][1];
	$pedidosvend->Descuento->CurrentValue = $nTotals[2][2];
	$pedidosvend->Total->CurrentValue = $nTotals[2][3];

	// Render row for view
	$pedidosvend->RowType = EW_ROWTYPE_VIEW;
	RenderRow();
?>
	<tr>
		<td class="ewGroupAggregate"><span class="phpmaker">Totales</span></td>
		<td class="ewGroupAggregate_2nd"><span class="phpmaker">&nbsp;</span></td>
		<td class="ewGroupAggregate_2nd"><span class="phpmaker">
<div<?php echo $pedidosvend->Subtotal->ViewAttributes() ?>><?php echo $pedidosvend->Subtotal->ViewValue ?></div>
</span></td>
		<td class="ewGroupAggregate_2nd"><span class="phpmaker">
<div<?php echo $pedidosvend->Descuento->ViewAttributes() ?>><?php echo $pedidosvend->Descuento->ViewValue ?></div>
</span></td>
		<td class="ewGroupAggregate_2nd"><span class="phpmaker">
<div<?php echo $pedidosvend->Total->ViewAttributes() ?>><?php echo $pedidosvend->Total->ViewValue ?></div>
</span></td>
	</tr>
	<tr><td colspan=6><span class="phpmaker">&nbsp;<br></span></td></tr>
<?php
}
	if ($bLvlBreak[1]) {
		$pedidosvend->Evento->CurrentValue = $vGrps[0];

		// Render row for view
		$pedidosvend->RowType = EW_ROWTYPE_VIEW;
		RenderRow();
		$pedidosvend->Evento->CurrentValue = $pedidosvend->Evento->DbValue;
?>
	<tr><td colspan=5 class="ewGroupSummary"><span class="phpmaker">Evento: <?php echo $pedidosvend->Evento->ViewValue ?> - <?php echo ew_FormatNumber($nCntRecs[1],0) ?> pedidos</span></td></tr>
<?php
	$pedidosvend->Subtotal->CurrentValue = $nTotals[1][1];
	$pedidosvend->Descuento->CurrentValue = $nTotals[1][2];
	$pedidosvend->Total->CurrentValue = $nTotals[1][3];

	// Render row for view
	$pedidosvend->RowType = EW_ROWTYPE_VIEW;
	RenderRow();
?>
	<tr>
		<td class="ewGroupAggregate"><span class="phpmaker">Totales</span></td>
		<td class="ewGroupAggregate_2nd"><span class="phpmaker">&nbsp;</span></td>
		<td class="ewGroupAggregate_2nd"><span class="phpmaker">
<div<?php echo $pedidosvend->Subtotal->ViewAttributes() ?>><?php echo $pedidosvend->Subtotal->ViewValue ?></div>
</span></td>
		<td class="ewGroupAggregate_2nd"><span class="phpmaker">
<div<?php echo $pedidosvend->Descuento->ViewAttributes() ?>><?php echo $pedidosvend->Descuento->ViewValue ?></div>
</span></td>
		<td class="ewGroupAggregate_2nd"><span class="phpmaker">
<div<?php echo $pedidosvend->Total->ViewAttributes() ?>><?php echo $pedidosvend->Total->ViewValue ?></div>
</span></td>
	</tr>
	<tr><td colspan=6><span class="phpmaker">&nbsp;<br></span></td></tr>
<?php
}
}

// Close recordset
$rs->Close();
?>
	<tr><td colspan=5><span class="phpmaker">&nbsp;<br></span></td></tr>
	<tr><td colspan=5 class="ewGrandSummary"><span class="phpmaker">Gran Total: <?php echo ew_FormatNumber($nCntRecs[0],0) ?> pedidos</span></td></tr>
<?php
	$pedidosvend->Subtotal->CurrentValue = $nTotals[0][1];
	$pedidosvend->Descuento->CurrentValue = $nTotals[0][2];
	$pedidosvend->Total->CurrentValue = $nTotals[0][3];

	// Render row for view
	$pedidosvend->RowType = EW_ROWTYPE_VIEW;
	RenderRow();
?>
	<tr>
		<td class="ewGroupAggregate"><span class="phpmaker">Totales</span></td>
		<td class="ewGroupAggregate_2nd"><span class="phpmaker">&nbsp;</span></td>
		<td class="ewGroupAggregate_2nd"><span class="phpmaker">
<div<?php echo $pedidosvend->Subtotal->ViewAttributes() ?>><?php echo $pedidosvend->Subtotal->ViewValue ?></div>
</span></td>
		<td class="ewGroupAggregate_2nd"><span class="phpmaker">
<div<?php echo $pedidosvend->Descuento->ViewAttributes() ?>><?php echo $pedidosvend->Descuento->ViewValue ?></div>
</span></td>
		<td class="ewGroupAggregate_2nd"><span class="phpmaker">
<div<?php echo $pedidosvend->Total->ViewAttributes() ?>><?php echo $pedidosvend->Total->ViewValue ?></div>
</span></td>
	</tr>
	<tr><td colspan=5><span class="phpmaker">&nbsp;<br></span></td></tr>
</table>
</form>
<?php

// Check level break
function ChkLvlBreak() {
	global $nRecCount, $bLvlBreak, $vGrps, $pedidosvend;
	$bLvlBreak[1] = FALSE;
	$bLvlBreak[2] = FALSE;
	if ($nRecCount == 0) { // Start Or End of Recordset
		$bLvlBreak[1] = TRUE;
		$bLvlBreak[2] = TRUE;
	} else {
		if (!ew_CompareValue($pedidosvend->Evento->CurrentValue, $vGrps[0])) {
			$bLvlBreak[1] = TRUE;
			$bLvlBreak[2] = TRUE;
		}
		if (!ew_CompareValue($pedidosvend->Vendedor->CurrentValue, $vGrps[1])) {
			$bLvlBreak[2] = TRUE;
		}
	}
}
?>
<script language="JavaScript" type="text/javascript">
<!--

// Write your table-specific startup script here
// document.write("page loaded");
//-->

</script>
<?php include "footer.php" ?>
<?php

// If control is passed here, simply terminate the page without redirect
Page_Terminate();

// -----------------------------------------------------------------
//  Subroutine Page_Terminate
//  - called when exit page
//  - clean up connection and objects
//  - if url specified, redirect to url, otherwise end response
function Page_Terminate($url = "") {
	global $conn;

	// Page unload event, used in current page
	Page_Unload();

	// Global page unloaded event (in userfn*.php)
	Page_Unloaded();

	 // Close Connection
	$conn->Close();

	// Go to url if specified
	if ($url <> "") {
		ob_end_clean();
		header("Location: $url");
	}
	exit();
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $pedidosvend;

	// Common render codes for all row types
	// Evento

	$pedidosvend->Evento->CellCssStyle = "";
	$pedidosvend->Evento->CellCssClass = "";

	// Vendedor
	$pedidosvend->Vendedor->CellCssStyle = "";
	$pedidosvend->Vendedor->CellCssClass = "";

	// Num
	$pedidosvend->Num->CellCssStyle = "";
	$pedidosvend->Num->CellCssClass = "";

	// Subtotal
	$pedidosvend->Subtotal->CellCssStyle = "";
	$pedidosvend->Subtotal->CellCssClass = "";

	// Descuento
	$pedidosvend->Descuento->CellCssStyle = "";
	$pedidosvend->Descuento->CellCssClass = "";

	// Total
	$pedidosvend->Total->CellCssStyle = "";
	$pedidosvend->Total->CellCssClass = "";
	if ($pedidosvend->RowType == EW_ROWTYPE_VIEW) { // View row

		// Evento
		$pedidosvend->Evento->ViewValue = $pedidosvend->Evento->CurrentValue;
		$pedidosvend->Evento->CssStyle = "";
		$pedidosvend->Evento->CssClass = "";
		$pedidosvend->Evento->ViewCustomAttributes = "";

		// Vendedor
		$pedidosvend->Vendedor->ViewValue = $pedidosvend->Vendedor->CurrentValue;
		$pedidosvend->Vendedor->CssStyle = "";
		$pedidosvend->Vendedor->CssClass = "";
		$pedidosvend->Vendedor->ViewCustomAttributes = "";

		// Num
		$pedidosvend->Num->ViewValue = $pedidosvend->Num->CurrentValue;
		$pedidosvend->Num->CssStyle = "";
		$pedidosvend->Num->CssClass = "";
		$pedidosvend->Num->ViewCustomAttributes = "";

		// Subtotal
		$pedidosvend->Subtotal->ViewValue = $pedidosvend->Subtotal->CurrentValue;
		$pedidosvend->Subtotal->ViewValue = ew_FormatCurrency($pedidosvend->Subtotal->ViewValue, 2, -2, -2, -2);
		$pedidosvend->Subtotal->CssStyle = "text-align:right;";
		$pedidosvend->Subtotal->CssClass = "";
		$pedidosvend->Subtotal->ViewCustomAttributes = "";

		// Descuento
		$pedidosvend->Descuento->ViewValue = $pedidosvend->Descuento->CurrentValue;
		$pedidosvend->Descuento->ViewValue = ew_FormatCurrency($pedidosvend->Descuento->ViewValue, 2, -2, -2, -2);
		$pedidosvend->Descuento->CssStyle = "text-align:right;";
		$pedidosvend->Descuento->CssClass = "";
		$pedidosvend->Descuento->ViewCustomAttributes = "";

		// Total
		$pedidosvend->Total->ViewValue = $pedidosvend->Total->CurrentValue;
		$pedidosvend->Total->ViewValue = ew_FormatCurrency($pedidosvend->Total->ViewValue, 2, -2, -2, -2);
		$pedidosvend->Total->CssStyle = "font-weight:bold;text-align:right;";
		$pedidosvend->Total->CssClass = "";
		$pedidosvend->Total->ViewCustomAttributes = "";

		// Evento
		$pedidosvend->Evento->HrefValue = "";

		// Vendedor
		$pedidosvend->Vendedor->HrefValue = "";

		// Num
		$pedidosvend->Num->HrefValue = "";

		// Subtotal
		$pedidosvend->Subtotal->HrefValue = "";

		// Descuento
		$pedidosvend->Descuento->HrefValue = "";

		// Total
		$pedidosvend->Total->HrefValue = "";
	} elseif ($pedidosvend->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($pedidosvend->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($pedidosvend->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}
}
?>
<?php

// Page Load event
function Page_Load() {

	//echo "Page Load";
}

// Page Unload event
function Page_Unload() {

	//echo "Page Unload";
}
?>