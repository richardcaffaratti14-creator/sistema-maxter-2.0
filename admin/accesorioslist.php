<?php
define("EW_PAGE_ID", "list", TRUE); // Page ID
define("EW_TABLE_NAME", 'accesorios', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "accesoriosinfo.php" ?>
<?php include "userfn50.php" ?>
<?php include "usuariosinfo.php" ?>
<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // Always modified
header("Cache-Control: private, no-store, no-cache, must-revalidate"); // HTTP/1.1 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0
?>
<?php

// Open connection to the database
$conn = ew_Connect();
?>
<?php
$Security = new cAdvancedSecurity();
?>
<?php
if (!$Security->IsLoggedIn()) $Security->AutoLogin();
$Security->LoadCurrentUserLevel('accesorios');
if (!$Security->IsLoggedIn()) {
	$Security->SaveLastUrl();
	Page_Terminate("login.php");
}
if (!$Security->CanList()) {
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
$accesorios->Export = @$_GET["export"]; // Get export parameter
$sExport = $accesorios->Export; // Get export parameter, used in header
$sExportFile = $accesorios->TableVar; // Get export file, used in header
?>
<?php
?>
<?php

// Paging variables
$nStartRec = 0; // Start record index
$nStopRec = 0; // Stop record index
$nTotalRecs = 0; // Total number of records
$nDisplayRecs = 20;
$nRecRange = 10;
$nRecCount = 0; // Record count

// Search filters
$sSrchAdvanced = ""; // Advanced search filter
$sSrchBasic = ""; // Basic search filter
$sSrchWhere = ""; // Search where clause
$sFilter = "";

// Master/Detail
$sDbMasterFilter = ""; // Master filter
$sDbDetailFilter = ""; // Detail filter
$sSqlMaster = ""; // Sql for master record

// Handle reset command
ResetCmd();

// Get basic search criteria
$sSrchBasic = BasicSearchWhere();

// Build search criteria
if ($sSrchAdvanced <> "") {
	if ($sSrchWhere <> "") $sSrchWhere .= " AND ";
	$sSrchWhere .= "(" . $sSrchAdvanced . ")";
}
if ($sSrchBasic <> "") {
	if ($sSrchWhere <> "") $sSrchWhere .= " AND ";
	$sSrchWhere .= "(" . $sSrchBasic . ")";
}

// Save search criteria
if ($sSrchWhere <> "") {
	if ($sSrchBasic == "") ResetBasicSearchParms();
	$accesorios->setSearchWhere($sSrchWhere); // Save to Session
	$nStartRec = 1; // Reset start record counter
	$accesorios->setStartRecordNumber($nStartRec);
} else {
	RestoreSearchParms();
}

// Build filter
$sFilter = "";
if (!$Security->CanList()) {
	$sFilter = "(0=1)"; // Filter all records
}
if ($sDbDetailFilter <> "") {
	if ($sFilter <> "") $sFilter .= " AND ";
	$sFilter .= "(" . $sDbDetailFilter . ")";
}
if ($sSrchWhere <> "") {
	if ($sFilter <> "") $sFilter .= " AND ";
	$sFilter .= "(" . $sSrchWhere . ")";
}

// Set up filter in Session
$accesorios->setSessionWhere($sFilter);
$accesorios->CurrentFilter = "";

// Set Up Sorting Order
SetUpSortOrder();

// Set Return Url
$accesorios->setReturnUrl("accesorioslist.php");
?>
<?php include "header.php" ?>
<?php if ($accesorios->Export == "") { ?>
<script type="text/javascript">
<!--
var EW_PAGE_ID = "list"; // Page id

//-->
</script>
<script type="text/javascript">
<!--
var firstrowoffset = 1; // First data row start at
var lastrowoffset = 0; // Last data row end at
var EW_LIST_TABLE_NAME = 'ewlistmain'; // Table name for list page
var rowclass = 'ewTableRow'; // Row class
var rowaltclass = 'ewTableAltRow'; // Row alternate class
var rowmoverclass = 'ewTableHighlightRow'; // Row mouse over class
var rowselectedclass = 'ewTableSelectRow'; // Row selected class
var roweditclass = 'ewTableEditRow'; // Row edit class

//-->
</script>
<script type="text/javascript">
<!--
var ew_DHTMLEditors = [];

//-->
</script>
<script language="JavaScript" type="text/javascript">
<!--

// Write your client script here, no need to add script tags.
// To include another .js script, use:
// ew_ClientScriptInclude("my_javascript.js"); 
//-->

</script>
<?php } ?>
<?php if ($accesorios->Export == "") { ?>
<?php } ?>
<?php

// Load recordset
$bExportAll = (defined("EW_EXPORT_ALL") && $accesorios->Export <> "");
$bSelectLimit = ($accesorios->Export == "" && $accesorios->SelectLimit);
if (!$bSelectLimit) $rs = LoadRecordset();
$nTotalRecs = ($bSelectLimit) ? $accesorios->SelectRecordCount() : $rs->RecordCount();
$nStartRec = 1;
if ($nDisplayRecs <= 0) $nDisplayRecs = $nTotalRecs; // Display all records
if (!$bExportAll) SetUpStartRec(); // Set up start record position
if ($bSelectLimit) $rs = LoadRecordset($nStartRec-1, $nDisplayRecs);
?>
<?php if ($accesorios->Export == "") { ?>
<?php if ($Security->CanSearch()) { ?>
<form name="faccesorioslistsrch" id="faccesorioslistsrch" action="accesorioslist.php" >
<table class="ewBasicSearch" align="center">
	<tr>
		<td><span class="phpmaker">
			<input type="text" name="<?php echo EW_TABLE_BASIC_SEARCH ?>" id="<?php echo EW_TABLE_BASIC_SEARCH ?>" size="20" value="<?php echo $accesorios->getBasicSearchKeyword() ?>">
			<input type="Submit" name="Submit" id="Submit" value="Buscar (*)">&nbsp;
			<a href="accesorioslist.php?cmd=reset">Mostrar todo</a>&nbsp;
		</span></td>
	</tr>
	<tr>
	<td><span class="phpmaker"><input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="" <?php if ($accesorios->getBasicSearchType() == "") { ?>checked<?php } ?>>Frase exacta&nbsp;&nbsp;<input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="AND" <?php if ($accesorios->getBasicSearchType() == "AND") { ?>checked<?php } ?>>Todas las palabras&nbsp;&nbsp;<input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="OR" <?php if ($accesorios->getBasicSearchType() == "OR") { ?>checked<?php } ?>>Cualquier palabra</span></td>
	</tr>
</table>
</form>
<?php } ?>
<?php } ?>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form method="post" name="faccesorioslist" id="faccesorioslist">
<?php if ($accesorios->Export == "") { ?>
<table class="subheader" align="center">
	<tr><td><span class="phpmaker">
<?php if ($Security->CanAdd()) { ?>
<div class="button-add"><a href="accesoriosadd.php">Agregar</a></div>
<?php } ?>
<div class="tablername" style="margin-top:7px">Accesorios</div>
	</span></td></tr>
</table>
<?php } ?>
<?php if ($nTotalRecs > 0) { ?>
<div id="whiteback">
<table id="ewlistmain" class="ewTable" align="center">
<?php
	$OptionCnt = 0;
if ($Security->CanEdit()) {
	$OptionCnt++; // edit
}
if ($Security->CanDelete()) {
	$OptionCnt++; // delete
}
?>
	<!-- Table header -->
	<tr class="ewTableHeader">
		<td valign="top">
<?php if ($accesorios->Export <> "") { ?>
Nombre
<?php } else { ?>
	<a href="accesorioslist.php?order=<?php echo urlencode('nombre') ?>&ordertype=<?php echo $accesorios->nombre->ReverseSort() ?>">Nombre&nbsp;(*)<?php if ($accesorios->nombre->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($accesorios->nombre->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($accesorios->Export <> "") { ?>
Precio
<?php } else { ?>
	<a href="accesorioslist.php?order=<?php echo urlencode('precio') ?>&ordertype=<?php echo $accesorios->precio->ReverseSort() ?>">Precio<?php if ($accesorios->precio->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($accesorios->precio->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($accesorios->Export <> "") { ?>
Activo
<?php } else { ?>
	<a href="accesorioslist.php?order=<?php echo urlencode('activo') ?>&ordertype=<?php echo $accesorios->activo->ReverseSort() ?>">Activo<?php if ($accesorios->activo->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($accesorios->activo->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
<?php if ($accesorios->Export == "") { ?>
<?php if ($Security->CanEdit()) { ?>
<td nowrap width="1" style="padding:0">&nbsp;</td>
<?php } ?>
<?php if ($Security->CanDelete()) { ?>
<td nowrap>&nbsp;</td>
<?php } ?>
<?php } ?>
	</tr>
<?php
if (defined("EW_EXPORT_ALL") && $accesorios->Export <> "") {
	$nStopRec = $nTotalRecs;
} else {
	$nStopRec = $nStartRec + $nDisplayRecs - 1; // Set the last record to display
}
$nRecCount = $nStartRec - 1;
if (!$rs->EOF) {
	$rs->MoveFirst();
	if (!$accesorios->SelectLimit) $rs->Move($nStartRec - 1); // Move to first record directly
}
$RowCnt = 0;
while (!$rs->EOF && $nRecCount < $nStopRec) {
	$nRecCount++;
	if (intval($nRecCount) >= intval($nStartRec)) {
		$RowCnt++;

	// Init row class and style
	$accesorios->CssClass = "ewTableRow";
	$accesorios->CssStyle = "";

	// Init row event
	$accesorios->RowClientEvents = "onmouseover='ew_MouseOver(this);' onmouseout='ew_MouseOut(this);' onclick='ew_Click(this);'";

	// Display alternate color for rows
	if ($RowCnt % 2 == 0) {
		$accesorios->CssClass = "ewTableAltRow";
	}
	LoadRowValues($rs); // Load row values
	$accesorios->RowType = EW_ROWTYPE_VIEW; // Render view
	RenderRow();
?>
	<!-- Table body -->
	<tr<?php echo $accesorios->DisplayAttributes() ?>>
		<!-- nombre -->
		<td<?php echo $accesorios->nombre->CellAttributes() ?>>
<div<?php echo $accesorios->nombre->ViewAttributes() ?>><?php echo $accesorios->nombre->ViewValue ?></div>
</td>
		<!-- precio -->
		<td<?php echo $accesorios->precio->CellAttributes() ?>>
<div<?php echo $accesorios->precio->ViewAttributes() ?>>$ <?php echo $accesorios->precio->ViewValue ?></div>
</td>
		<!-- activo -->
		<td<?php echo $accesorios->activo->CellAttributes() ?>>
<div<?php echo $accesorios->activo->ViewAttributes() ?>><?php echo $accesorios->activo->ViewValue ?></div>
</td>
<?php if ($accesorios->Export == "") { ?>
<?php if ($Security->CanEdit()) { ?>
<td nowrap width="1" style="padding:0"><span class="phpmaker">
<div class="button-edit"><a href="<?php echo $accesorios->EditUrl() ?>">Editar</a></div>
</span></td>
<?php } ?>
<?php if ($Security->CanDelete()) { ?>
<td nowrap width="1" style="padding:0"><span class="phpmaker">
<div class="button-delete"><a href="<?php echo $accesorios->DeleteUrl() ?>">Borrar</a></div>
</span></td>
<?php } ?>
<?php } ?>
	</tr>
<?php
	}
	$rs->MoveNext();
}
?>
</table>
<?php } ?>
</div>
</form>
<?php

// Close recordset and connection
if ($rs) $rs->Close();
?>
<?php if ($accesorios->Export == "") { ?>
<form action="accesorioslist.php" name="ewpagerform" id="ewpagerform">
<table border="0" cellspacing="0" cellpadding="0" align="center" id="pagertable">
	<tr>
		<td nowrap align="center">
<span class="phpmaker">
<?php if (!isset($Pager)) $Pager = new cNumericPager($nStartRec, $nDisplayRecs, $nTotalRecs, $nRecRange) ?>
<?php if ($Pager->RecordCount > 0) { ?>
	<?php if ($Pager->FirstButton->Enabled) { ?>
	<a href="accesorioslist.php?start=<?php echo $Pager->FirstButton->Start ?>"><b>Primero</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->PrevButton->Enabled) { ?>
	<a href="accesorioslist.php?start=<?php echo $Pager->PrevButton->Start ?>"><b>Anterior</b></a>&nbsp;
	<?php } ?>
	<?php foreach ($Pager->Items as $PagerItem) { ?>
		<?php if ($PagerItem->Enabled) { ?><a href="accesorioslist.php?start=<?php echo $PagerItem->Start ?>"><?php } ?><b><?php echo $PagerItem->Text ?></b><?php if ($PagerItem->Enabled) { ?></a><?php } ?>&nbsp;
	<?php } ?>
	<?php if ($Pager->NextButton->Enabled) { ?>
	<a href="accesorioslist.php?start=<?php echo $Pager->NextButton->Start ?>"><b>Próximo</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->LastButton->Enabled) { ?>
	<a href="accesorioslist.php?start=<?php echo $Pager->LastButton->Start ?>"><b>Último</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->ButtonCount > 0) { ?><br><br><?php } ?>
	Registros <?php echo $Pager->FromIndex ?> a <?php echo $Pager->ToIndex ?> de <?php echo $Pager->RecordCount ?>
<?php } else { ?>	
	<?php if ($Security->CanList()) { ?>
	<?php if ($sSrchWhere == "0=101") { ?>
	Por favor ingrese el criterio de búsqueda
	<?php } else { ?>
	No se encontraron registros
	<?php } ?>
	<?php } else { ?>
	Usted no tiene permisos para visualizar esta página
	<?php } ?>
<?php } ?>
</span>
		</td>
	</tr>
</table>
</form>
<?php } ?>
<?php if ($accesorios->Export == "") { ?>
<?php } ?>
<?php if ($accesorios->Export == "") { ?>
<script language="JavaScript" type="text/javascript">
<!--

// Write your table-specific startup script here
// document.write("page loaded");
//-->

</script>
<?php } ?>
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

// Return Basic Search sql
function BasicSearchSQL($Keyword) {
	$sKeyword = ew_AdjustSql($Keyword);
	$sql = "";
	$sql .= "`nombre` LIKE '%" . $sKeyword . "%' OR ";
	if (substr($sql, -4) == " OR ") $sql = substr($sql, 0, strlen($sql)-4);
	return $sql;
}

// Return Basic Search Where based on search keyword and type
function BasicSearchWhere() {
	global $Security, $accesorios;
	$sSearchStr = "";
	if (!$Security->CanSearch()) return "";
	$sSearchKeyword = ew_StripSlashes(@$_GET[EW_TABLE_BASIC_SEARCH]);
	$sSearchType = @$_GET[EW_TABLE_BASIC_SEARCH_TYPE];
	if ($sSearchKeyword <> "") {
		$sSearch = trim($sSearchKeyword);
		if ($sSearchType <> "") {
			while (strpos($sSearch, "  ") !== FALSE)
				$sSearch = str_replace("  ", " ", $sSearch);
			$arKeyword = explode(" ", trim($sSearch));
			foreach ($arKeyword as $sKeyword) {
				if ($sSearchStr <> "") $sSearchStr .= " " . $sSearchType . " ";
				$sSearchStr .= "(" . BasicSearchSQL($sKeyword) . ")";
			}
		} else {
			$sSearchStr = BasicSearchSQL($sSearch);
		}
	}
	if ($sSearchKeyword <> "") {
		$accesorios->setBasicSearchKeyword($sSearchKeyword);
		$accesorios->setBasicSearchType($sSearchType);
	}
	return $sSearchStr;
}

// Clear all search parameters
function ResetSearchParms() {

	// Clear search where
	global $accesorios;
	$sSrchWhere = "";
	$accesorios->setSearchWhere($sSrchWhere);

	// Clear basic search parameters
	ResetBasicSearchParms();
}

// Clear all basic search parameters
function ResetBasicSearchParms() {

	// Clear basic search parameters
	global $accesorios;
	$accesorios->setBasicSearchKeyword("");
	$accesorios->setBasicSearchType("");
}

// Restore all search parameters
function RestoreSearchParms() {
	global $sSrchWhere, $accesorios;
	$sSrchWhere = $accesorios->getSearchWhere();
}

// Set up Sort parameters based on Sort Links clicked
function SetUpSortOrder() {
	global $accesorios;

	// Check for an Order parameter
	if (@$_GET["order"] <> "") {
		$accesorios->CurrentOrder = ew_StripSlashes(@$_GET["order"]);
		$accesorios->CurrentOrderType = @$_GET["ordertype"];

		// Field nombre
		$accesorios->UpdateSort($accesorios->nombre);

		// Field precio
		$accesorios->UpdateSort($accesorios->precio);

		// Field activo
		$accesorios->UpdateSort($accesorios->activo);
		$accesorios->setStartRecordNumber(1); // Reset start position
	}
	$sOrderBy = $accesorios->getSessionOrderBy(); // Get order by from Session
	if ($sOrderBy == "") {
		if ($accesorios->SqlOrderBy() <> "") {
			$sOrderBy = $accesorios->SqlOrderBy();
			$accesorios->setSessionOrderBy($sOrderBy);
			$accesorios->nombre->setSort("ASC");
		}
	}
}

// Reset command based on querystring parameter cmd=
// - RESET: reset search parameters
// - RESETALL: reset search & master/detail parameters
// - RESETSORT: reset sort parameters
function ResetCmd() {
	global $sDbMasterFilter, $sDbDetailFilter, $nStartRec, $sOrderBy;
	global $accesorios;

	// Get reset cmd
	if (@$_GET["cmd"] <> "") {
		$sCmd = $_GET["cmd"];

		// Reset search criteria
		if (strtolower($sCmd) == "reset" || strtolower($sCmd) == "resetall") {
			ResetSearchParms();
		}

		// Reset Sort Criteria
		if (strtolower($sCmd) == "resetsort") {
			$sOrderBy = "";
			$accesorios->setSessionOrderBy($sOrderBy);
			$accesorios->nombre->setSort("");
			$accesorios->precio->setSort("");
			$accesorios->activo->setSort("");
		}

		// Reset start position
		$nStartRec = 1;
		$accesorios->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Set up Starting Record parameters based on Pager Navigation
function SetUpStartRec() {
	global $nDisplayRecs, $nStartRec, $nTotalRecs, $nPageNo, $accesorios;
	if ($nDisplayRecs == 0) return;

	// Check for a START parameter
	if (@$_GET[EW_TABLE_START_REC] <> "") {
		$nStartRec = $_GET[EW_TABLE_START_REC];
		$accesorios->setStartRecordNumber($nStartRec);
	} elseif (@$_GET[EW_TABLE_PAGE_NO] <> "") {
		$nPageNo = $_GET[EW_TABLE_PAGE_NO];
		if (is_numeric($nPageNo)) {
			$nStartRec = ($nPageNo-1)*$nDisplayRecs+1;
			if ($nStartRec <= 0) {
				$nStartRec = 1;
			} elseif ($nStartRec >= intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1) {
				$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1;
			}
			$accesorios->setStartRecordNumber($nStartRec);
		} else {
			$nStartRec = $accesorios->getStartRecordNumber();
		}
	} else {
		$nStartRec = $accesorios->getStartRecordNumber();
	}

	// Check if correct start record counter
	if (!is_numeric($nStartRec) || $nStartRec == "") { // Avoid invalid start record counter
		$nStartRec = 1; // Reset start record counter
		$accesorios->setStartRecordNumber($nStartRec);
	} elseif (intval($nStartRec) > intval($nTotalRecs)) { // Avoid starting record > total records
		$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to last page first record
		$accesorios->setStartRecordNumber($nStartRec);
	} elseif (($nStartRec-1) % $nDisplayRecs <> 0) {
		$nStartRec = intval(($nStartRec-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to page boundary
		$accesorios->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Load recordset
function LoadRecordset($offset = -1, $rowcnt = -1) {
	global $conn, $accesorios;

	// Call Recordset Selecting event
	$accesorios->Recordset_Selecting($accesorios->CurrentFilter);

	// Load list page sql
	$sSql = $accesorios->SelectSQL();
	if ($offset > -1 && $rowcnt > -1) $sSql .= " LIMIT $offset, $rowcnt";

	// Load recordset
	$conn->raiseErrorFn = 'ew_ErrorFn';	
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';

	// Call Recordset Selected event
	$accesorios->Recordset_Selected($rs);
	return $rs;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $accesorios;
	$sFilter = $accesorios->SqlKeyFilter();
	if (!is_numeric($accesorios->id->CurrentValue)) {
		return FALSE; // Invalid key, exit
	}
	$sFilter = str_replace("@id@", ew_AdjustSql($accesorios->id->CurrentValue), $sFilter); // Replace key value

	// Call Row Selecting event
	$accesorios->Row_Selecting($sFilter);

	// Load sql based on filter
	$accesorios->CurrentFilter = $sFilter;
	$sSql = $accesorios->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$accesorios->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $accesorios;
	$accesorios->id->setDbValue($rs->fields('id'));
	$accesorios->nombre->setDbValue($rs->fields('nombre'));
	$accesorios->precio->setDbValue($rs->fields('precio'));
	$accesorios->activo->setDbValue($rs->fields('activo'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $accesorios;

	// Call Row Rendering event
	$accesorios->Row_Rendering();

	// Common render codes for all row types
	// nombre

	$accesorios->nombre->CellCssStyle = "";
	$accesorios->nombre->CellCssClass = "";

	// precio
	$accesorios->precio->CellCssStyle = "";
	$accesorios->precio->CellCssClass = "";

	// activo
	$accesorios->activo->CellCssStyle = "";
	$accesorios->activo->CellCssClass = "";
	if ($accesorios->RowType == EW_ROWTYPE_VIEW) { // View row

		// nombre
		$accesorios->nombre->ViewValue = $accesorios->nombre->CurrentValue;
		$accesorios->nombre->CssStyle = "";
		$accesorios->nombre->CssClass = "";
		$accesorios->nombre->ViewCustomAttributes = "";

		// precio
		$accesorios->precio->ViewValue = $accesorios->precio->CurrentValue;
		$accesorios->precio->CssStyle = "";
		$accesorios->precio->CssClass = "";
		$accesorios->precio->ViewCustomAttributes = "";

		// activo
		if (!is_null($accesorios->activo->CurrentValue)) {
			switch ($accesorios->activo->CurrentValue) {
				case "1":
					$accesorios->activo->ViewValue = "Activo";
					break;
				case "0":
					$accesorios->activo->ViewValue = "Inactivo";
					break;
				default:
					$accesorios->activo->ViewValue = $accesorios->activo->CurrentValue;
			}
		} else {
			$accesorios->activo->ViewValue = NULL;
		}
		$accesorios->activo->CssStyle = "";
		$accesorios->activo->CssClass = "";
		$accesorios->activo->ViewCustomAttributes = "";

		// nombre
		$accesorios->nombre->HrefValue = "";

		// precio
		$accesorios->precio->HrefValue = "";

		// activo
		$accesorios->activo->HrefValue = "";
	} elseif ($accesorios->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($accesorios->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($accesorios->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$accesorios->Row_Rendered();
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
