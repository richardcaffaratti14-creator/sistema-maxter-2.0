<?php
define("EW_PAGE_ID", "list", TRUE); // Page ID
define("EW_TABLE_NAME", 'usuarios_ingresos', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "usuarios_ingresosinfo.php" ?>
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
$Security->LoadCurrentUserLevel('usuarios_ingresos');
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
$usuarios_ingresos->Export = @$_GET["export"]; // Get export parameter
$sExport = $usuarios_ingresos->Export; // Get export parameter, used in header
$sExportFile = $usuarios_ingresos->TableVar; // Get export file, used in header
?>
<?php
?>
<?php

// Paging variables
$nStartRec = 0; // Start record index
$nStopRec = 0; // Stop record index
$nTotalRecs = 0; // Total number of records
$nDisplayRecs = 25;
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

// Set up master detail parameters
SetUpMasterDetail();

// Build filter
$sFilter = "";
if (!$Security->CanList()) {
	$sFilter = "(0=1)"; // Filter all records
}
if ($Security->CurrentUserID() <> "" && !$Security->IsAdmin()) { // Non system admin
	if ($usuarios_ingresos->getCurrentMasterTable() == "usuarios") {
		$sFilter = $usuarios_ingresos->AddDetailUserIDFilter($sFilter, "usuarios", $Security->CurrentUserID()); // Add detail User ID filter
		$sDbMasterFilter = $usuarios_ingresos->AddMasterUserIDFilter($sDbMasterFilter, "usuarios", $Security->CurrentUserID()); // Add master User ID filter
	}
}
if ($sDbDetailFilter <> "") {
	if ($sFilter <> "") $sFilter .= " AND ";
	$sFilter .= "(" . $sDbDetailFilter . ")";
}
if ($sSrchWhere <> "") {
	if ($sFilter <> "") $sFilter .= " AND ";
	$sFilter .= "(" . $sSrchWhere . ")";
}

// Load master record
if ($usuarios_ingresos->getMasterFilter() <> "" && $usuarios_ingresos->getCurrentMasterTable() == "usuarios") {
	$rsmaster = $usuarios->LoadRs($sDbMasterFilter);
	$bMasterRecordExists = ($rsmaster && !$rsmaster->EOF);
	if (!$bMasterRecordExists) {
		$usuarios_ingresos->setMasterFilter(""); // Clear master filter
		$usuarios_ingresos->setDetailFilter(""); // Clear detail filter
		$_SESSION[EW_SESSION_MESSAGE] = "No se encontraron registros"; // Set no record found
		Page_Terminate("usuarioslist.php"); // Return to caller
	} else {
		$usuarios->LoadListRowValues($rsmaster);
		$usuarios->RenderListRow();
		$rsmaster->Close();
	}
}

// Set up filter in Session
$usuarios_ingresos->setSessionWhere($sFilter);
$usuarios_ingresos->CurrentFilter = "";

// Set Up Sorting Order
SetUpSortOrder();

// Set Return Url
$usuarios_ingresos->setReturnUrl("usuarios_ingresoslist.php");
?>
<?php include "header.php" ?>
<?php if ($usuarios_ingresos->Export == "") { ?>
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
<?php if ($usuarios_ingresos->Export == "") { ?>
<?php
$sMasterReturnUrl = "usuarioslist.php";
if ($usuarios_ingresos->getMasterFilter() <> "" && $usuarios_ingresos->getCurrentMasterTable() == "usuarios") {
	if ($bMasterRecordExists) {
		if ($usuarios_ingresos->getCurrentMasterTable() == $usuarios_ingresos->TableVar) $sMasterReturnUrl .= "?" . EW_TABLE_SHOW_MASTER . "=";
?>
<?php include "usuariosmaster.php" ?>
<?php
	}
}
?>
<?php } ?>
<?php

// Load recordset
$bExportAll = (defined("EW_EXPORT_ALL") && $usuarios_ingresos->Export <> "");
$bSelectLimit = ($usuarios_ingresos->Export == "" && $usuarios_ingresos->SelectLimit);
if (!$bSelectLimit) $rs = LoadRecordset();
$nTotalRecs = ($bSelectLimit) ? $usuarios_ingresos->SelectRecordCount() : $rs->RecordCount();
$nStartRec = 1;
if ($nDisplayRecs <= 0) $nDisplayRecs = $nTotalRecs; // Display all records
if (!$bExportAll) SetUpStartRec(); // Set up start record position
if ($bSelectLimit) $rs = LoadRecordset($nStartRec-1, $nDisplayRecs);
?>
<?php if ($usuarios_ingresos->Export == "") { ?>
<?php } ?>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form method="post" name="fusuarios_ingresoslist" id="fusuarios_ingresoslist">
<?php if ($usuarios_ingresos->Export == "") { ?>
<table class="subheader" align="center">
	<tr><td><span class="phpmaker">
<div class="tablername" style="margin-top:7px">Logins</div>
	</span></td></tr>
</table>
<?php } ?>
<?php if ($nTotalRecs > 0) { ?>
<div id="whiteback">
<table id="ewlistmain" class="ewTable" align="center">
<?php
	$OptionCnt = 0;
?>
	<!-- Table header -->
	<tr class="ewTableHeader">
		<td valign="top">
<?php if ($usuarios_ingresos->Export <> "") { ?>
Fecha
<?php } else { ?>
	<a href="usuarios_ingresoslist.php?order=<?php echo urlencode('Fecha') ?>&ordertype=<?php echo $usuarios_ingresos->Fecha->ReverseSort() ?>">Fecha<?php if ($usuarios_ingresos->Fecha->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($usuarios_ingresos->Fecha->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($usuarios_ingresos->Export <> "") { ?>
IP
<?php } else { ?>
	<a href="usuarios_ingresoslist.php?order=<?php echo urlencode('IP') ?>&ordertype=<?php echo $usuarios_ingresos->IP->ReverseSort() ?>">IP<?php if ($usuarios_ingresos->IP->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($usuarios_ingresos->IP->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
<?php if ($usuarios_ingresos->Export == "") { ?>
<?php } ?>
	</tr>
<?php
if (defined("EW_EXPORT_ALL") && $usuarios_ingresos->Export <> "") {
	$nStopRec = $nTotalRecs;
} else {
	$nStopRec = $nStartRec + $nDisplayRecs - 1; // Set the last record to display
}
$nRecCount = $nStartRec - 1;
if (!$rs->EOF) {
	$rs->MoveFirst();
	if (!$usuarios_ingresos->SelectLimit) $rs->Move($nStartRec - 1); // Move to first record directly
}
$RowCnt = 0;
while (!$rs->EOF && $nRecCount < $nStopRec) {
	$nRecCount++;
	if (intval($nRecCount) >= intval($nStartRec)) {
		$RowCnt++;

	// Init row class and style
	$usuarios_ingresos->CssClass = "ewTableRow";
	$usuarios_ingresos->CssStyle = "";

	// Init row event
	$usuarios_ingresos->RowClientEvents = "onmouseover='ew_MouseOver(this);' onmouseout='ew_MouseOut(this);' onclick='ew_Click(this);'";

	// Display alternate color for rows
	if ($RowCnt % 2 == 0) {
		$usuarios_ingresos->CssClass = "ewTableAltRow";
	}
	LoadRowValues($rs); // Load row values
	$usuarios_ingresos->RowType = EW_ROWTYPE_VIEW; // Render view
	RenderRow();
?>
	<!-- Table body -->
	<tr<?php echo $usuarios_ingresos->DisplayAttributes() ?>>
		<!-- Fecha -->
		<td<?php echo $usuarios_ingresos->Fecha->CellAttributes() ?>>
<div<?php echo $usuarios_ingresos->Fecha->ViewAttributes() ?>><?php echo $usuarios_ingresos->Fecha->ViewValue ?></div>
</td>
		<!-- IP -->
		<td<?php echo $usuarios_ingresos->IP->CellAttributes() ?>>
<div<?php echo $usuarios_ingresos->IP->ViewAttributes() ?>><?php echo $usuarios_ingresos->IP->ViewValue ?></div>
</td>
<?php if ($usuarios_ingresos->Export == "") { ?>
<?php } ?>
	</tr>
<?php
	}
	$rs->MoveNext();
}
?>
</table>
<?php } ?>
</form>
<?php

// Close recordset and connection
if ($rs) $rs->Close();
?>
<?php if ($usuarios_ingresos->Export == "") { ?>
<form action="usuarios_ingresoslist.php" name="ewpagerform" id="ewpagerform">
<table border="0" cellspacing="0" cellpadding="0" align="center" id="pagertable">
	<tr>
		<td nowrap align="center">
<span class="phpmaker">
<?php if (!isset($Pager)) $Pager = new cNumericPager($nStartRec, $nDisplayRecs, $nTotalRecs, $nRecRange) ?>
<?php if ($Pager->RecordCount > 0) { ?>
	<?php if ($Pager->FirstButton->Enabled) { ?>
	<a href="usuarios_ingresoslist.php?start=<?php echo $Pager->FirstButton->Start ?>"><b>Primero</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->PrevButton->Enabled) { ?>
	<a href="usuarios_ingresoslist.php?start=<?php echo $Pager->PrevButton->Start ?>"><b>Anterior</b></a>&nbsp;
	<?php } ?>
	<?php foreach ($Pager->Items as $PagerItem) { ?>
		<?php if ($PagerItem->Enabled) { ?><a href="usuarios_ingresoslist.php?start=<?php echo $PagerItem->Start ?>"><?php } ?><b><?php echo $PagerItem->Text ?></b><?php if ($PagerItem->Enabled) { ?></a><?php } ?>&nbsp;
	<?php } ?>
	<?php if ($Pager->NextButton->Enabled) { ?>
	<a href="usuarios_ingresoslist.php?start=<?php echo $Pager->NextButton->Start ?>"><b>Próximo</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->LastButton->Enabled) { ?>
	<a href="usuarios_ingresoslist.php?start=<?php echo $Pager->LastButton->Start ?>"><b>Último</b></a>&nbsp;
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
<?php if ($usuarios_ingresos->Export == "") { ?>
<?php } ?>
<?php if ($usuarios_ingresos->Export == "") { ?>
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

// Set up Sort parameters based on Sort Links clicked
function SetUpSortOrder() {
	global $usuarios_ingresos;

	// Check for an Order parameter
	if (@$_GET["order"] <> "") {
		$usuarios_ingresos->CurrentOrder = ew_StripSlashes(@$_GET["order"]);
		$usuarios_ingresos->CurrentOrderType = @$_GET["ordertype"];

		// Field Fecha
		$usuarios_ingresos->UpdateSort($usuarios_ingresos->Fecha);

		// Field IP
		$usuarios_ingresos->UpdateSort($usuarios_ingresos->IP);
		$usuarios_ingresos->setStartRecordNumber(1); // Reset start position
	}
	$sOrderBy = $usuarios_ingresos->getSessionOrderBy(); // Get order by from Session
	if ($sOrderBy == "") {
		if ($usuarios_ingresos->SqlOrderBy() <> "") {
			$sOrderBy = $usuarios_ingresos->SqlOrderBy();
			$usuarios_ingresos->setSessionOrderBy($sOrderBy);
			$usuarios_ingresos->Fecha->setSort("DESC");
		}
	}
}

// Reset command based on querystring parameter cmd=
// - RESET: reset search parameters
// - RESETALL: reset search & master/detail parameters
// - RESETSORT: reset sort parameters
function ResetCmd() {
	global $sDbMasterFilter, $sDbDetailFilter, $nStartRec, $sOrderBy;
	global $usuarios_ingresos;

	// Get reset cmd
	if (@$_GET["cmd"] <> "") {
		$sCmd = $_GET["cmd"];

		// Reset master/detail keys
		if (strtolower($sCmd) == "resetall") {
			$usuarios_ingresos->setMasterFilter(""); // Clear master filter
			$sDbMasterFilter = "";
			$usuarios_ingresos->setDetailFilter(""); // Clear detail filter
			$sDbDetailFilter = "";
			$usuarios_ingresos->usuario->setSessionValue("");
		}

		// Reset Sort Criteria
		if (strtolower($sCmd) == "resetsort") {
			$sOrderBy = "";
			$usuarios_ingresos->setSessionOrderBy($sOrderBy);
			$usuarios_ingresos->Fecha->setSort("");
			$usuarios_ingresos->IP->setSort("");
		}

		// Reset start position
		$nStartRec = 1;
		$usuarios_ingresos->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Set up Starting Record parameters based on Pager Navigation
function SetUpStartRec() {
	global $nDisplayRecs, $nStartRec, $nTotalRecs, $nPageNo, $usuarios_ingresos;
	if ($nDisplayRecs == 0) return;

	// Check for a START parameter
	if (@$_GET[EW_TABLE_START_REC] <> "") {
		$nStartRec = $_GET[EW_TABLE_START_REC];
		$usuarios_ingresos->setStartRecordNumber($nStartRec);
	} elseif (@$_GET[EW_TABLE_PAGE_NO] <> "") {
		$nPageNo = $_GET[EW_TABLE_PAGE_NO];
		if (is_numeric($nPageNo)) {
			$nStartRec = ($nPageNo-1)*$nDisplayRecs+1;
			if ($nStartRec <= 0) {
				$nStartRec = 1;
			} elseif ($nStartRec >= intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1) {
				$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1;
			}
			$usuarios_ingresos->setStartRecordNumber($nStartRec);
		} else {
			$nStartRec = $usuarios_ingresos->getStartRecordNumber();
		}
	} else {
		$nStartRec = $usuarios_ingresos->getStartRecordNumber();
	}

	// Check if correct start record counter
	if (!is_numeric($nStartRec) || $nStartRec == "") { // Avoid invalid start record counter
		$nStartRec = 1; // Reset start record counter
		$usuarios_ingresos->setStartRecordNumber($nStartRec);
	} elseif (intval($nStartRec) > intval($nTotalRecs)) { // Avoid starting record > total records
		$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to last page first record
		$usuarios_ingresos->setStartRecordNumber($nStartRec);
	} elseif (($nStartRec-1) % $nDisplayRecs <> 0) {
		$nStartRec = intval(($nStartRec-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to page boundary
		$usuarios_ingresos->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Load recordset
function LoadRecordset($offset = -1, $rowcnt = -1) {
	global $conn, $usuarios_ingresos;

	// Call Recordset Selecting event
	$usuarios_ingresos->Recordset_Selecting($usuarios_ingresos->CurrentFilter);

	// Load list page sql
	$sSql = $usuarios_ingresos->SelectSQL();
	if ($offset > -1 && $rowcnt > -1) $sSql .= " LIMIT $offset, $rowcnt";

	// Load recordset
	$conn->raiseErrorFn = 'ew_ErrorFn';	
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';

	// Call Recordset Selected event
	$usuarios_ingresos->Recordset_Selected($rs);
	return $rs;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $usuarios_ingresos;
	$sFilter = $usuarios_ingresos->SqlKeyFilter();
	if (!is_numeric($usuarios_ingresos->id->CurrentValue)) {
		return FALSE; // Invalid key, exit
	}
	$sFilter = str_replace("@id@", ew_AdjustSql($usuarios_ingresos->id->CurrentValue), $sFilter); // Replace key value
	if ($Security->CurrentUserID() <> "" && !$Security->IsAdmin()) { // Non system admin
		$sFilter = $usuarios_ingresos->AddDetailUserIDFilter($sFilter, "usuarios", $Security->CurrentUserID()); // Add User ID filter for master table
	}

	// Call Row Selecting event
	$usuarios_ingresos->Row_Selecting($sFilter);

	// Load sql based on filter
	$usuarios_ingresos->CurrentFilter = $sFilter;
	$sSql = $usuarios_ingresos->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$usuarios_ingresos->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $usuarios_ingresos;
	$usuarios_ingresos->id->setDbValue($rs->fields('id'));
	$usuarios_ingresos->usuario->setDbValue($rs->fields('usuario'));
	$usuarios_ingresos->Fecha->setDbValue($rs->fields('Fecha'));
	$usuarios_ingresos->IP->setDbValue($rs->fields('IP'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $usuarios_ingresos;

	// Call Row Rendering event
	$usuarios_ingresos->Row_Rendering();

	// Common render codes for all row types
	// Fecha

	$usuarios_ingresos->Fecha->CellCssStyle = "";
	$usuarios_ingresos->Fecha->CellCssClass = "";

	// IP
	$usuarios_ingresos->IP->CellCssStyle = "";
	$usuarios_ingresos->IP->CellCssClass = "";
	if ($usuarios_ingresos->RowType == EW_ROWTYPE_VIEW) { // View row

		// Fecha
		$usuarios_ingresos->Fecha->ViewValue = $usuarios_ingresos->Fecha->CurrentValue;
		$usuarios_ingresos->Fecha->ViewValue = ew_FormatDateTime($usuarios_ingresos->Fecha->ViewValue, 7);
		$usuarios_ingresos->Fecha->CssStyle = "";
		$usuarios_ingresos->Fecha->CssClass = "";
		$usuarios_ingresos->Fecha->ViewCustomAttributes = "";

		// IP
		$usuarios_ingresos->IP->ViewValue = $usuarios_ingresos->IP->CurrentValue;
		$usuarios_ingresos->IP->CssStyle = "";
		$usuarios_ingresos->IP->CssClass = "";
		$usuarios_ingresos->IP->ViewCustomAttributes = "";

		// Fecha
		$usuarios_ingresos->Fecha->HrefValue = "";

		// IP
		$usuarios_ingresos->IP->HrefValue = "";
	} elseif ($usuarios_ingresos->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($usuarios_ingresos->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($usuarios_ingresos->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$usuarios_ingresos->Row_Rendered();
}
?>
<?php

// Set up Master Detail based on querystring parameter
function SetUpMasterDetail() {
	global $nStartRec, $sDbMasterFilter, $sDbDetailFilter, $usuarios_ingresos;
	$bValidMaster = FALSE;

	// Get the keys for master table
	if (@$_GET[EW_TABLE_SHOW_MASTER] <> "") {
		$sMasterTblVar = $_GET[EW_TABLE_SHOW_MASTER];
		if ($sMasterTblVar == "") {
			$bValidMaster = TRUE;
			$sDbMasterFilter = "";
			$sDbDetailFilter = "";
		}
		if ($sMasterTblVar == "usuarios") {
			$bValidMaster = TRUE;
			$sDbMasterFilter = $usuarios_ingresos->SqlMasterFilter_usuarios();
			$sDbDetailFilter = $usuarios_ingresos->SqlDetailFilter_usuarios();
			if (@$_GET["usuario"] <> "") {
				$GLOBALS["usuarios"]->usuario->setQueryStringValue($_GET["usuario"]);
				$usuarios_ingresos->usuario->setQueryStringValue($GLOBALS["usuarios"]->usuario->QueryStringValue);
				$usuarios_ingresos->usuario->setSessionValue($usuarios_ingresos->usuario->QueryStringValue);
				$sDbMasterFilter = str_replace("@usuario@", ew_AdjustSql($GLOBALS["usuarios"]->usuario->QueryStringValue), $sDbMasterFilter);
				$sDbDetailFilter = str_replace("@usuario@", ew_AdjustSql($GLOBALS["usuarios"]->usuario->QueryStringValue), $sDbDetailFilter);
			} else {
				$bValidMaster = FALSE;
			}
		}
	}
	if ($bValidMaster) {

		// Save current master table
		$usuarios_ingresos->setCurrentMasterTable($sMasterTblVar);

		// Reset start record counter (new master key)
		$nStartRec = 1;
		$usuarios_ingresos->setStartRecordNumber($nStartRec);
		$usuarios_ingresos->setMasterFilter($sDbMasterFilter); // Set up master filter
		$usuarios_ingresos->setDetailFilter($sDbDetailFilter); // Set up detail filter

		// Clear previous master session values
		if ($sMasterTblVar <> "usuarios") {
			if ($usuarios_ingresos->usuario->QueryStringValue == "") $usuarios_ingresos->usuario->setSessionValue("");
		}
	} else {
		$sDbMasterFilter = $usuarios_ingresos->getMasterFilter(); //  Restore master filter
		$sDbDetailFilter = $usuarios_ingresos->getDetailFilter(); // Restore detail filter
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
