<?php
define("EW_PAGE_ID", "list", TRUE); // Page ID
define("EW_TABLE_NAME", 'formato_coreo', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "formato_coreoinfo.php" ?>
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
$Security->LoadCurrentUserLevel('formato_coreo');
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
$formato_coreo->Export = @$_GET["export"]; // Get export parameter
$sExport = $formato_coreo->Export; // Get export parameter, used in header
$sExportFile = $formato_coreo->TableVar; // Get export file, used in header
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
$formato_coreo->setSessionWhere($sFilter);
$formato_coreo->CurrentFilter = "";

// Set Up Sorting Order
SetUpSortOrder();

// Set Return Url
$formato_coreo->setReturnUrl("formato_coreolist.php");
?>
<?php include "header.php" ?>
<?php if ($formato_coreo->Export == "") { ?>
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
<?php if ($formato_coreo->Export == "") { ?>
<?php } ?>
<?php

// Load recordset
$bExportAll = (defined("EW_EXPORT_ALL") && $formato_coreo->Export <> "");
$bSelectLimit = ($formato_coreo->Export == "" && $formato_coreo->SelectLimit);
if (!$bSelectLimit) $rs = LoadRecordset();
$nTotalRecs = ($bSelectLimit) ? $formato_coreo->SelectRecordCount() : $rs->RecordCount();
$nStartRec = 1;
if ($nDisplayRecs <= 0) $nDisplayRecs = $nTotalRecs; // Display all records
if (!$bExportAll) SetUpStartRec(); // Set up start record position
if ($bSelectLimit) $rs = LoadRecordset($nStartRec-1, $nDisplayRecs);
?>
<?php if ($formato_coreo->Export == "") { ?>
<?php } ?>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form method="post" name="fformato_coreolist" id="fformato_coreolist">
<?php if ($formato_coreo->Export == "") { ?>
<table class="subheader" align="center">
	<tr><td><span class="phpmaker">
<?php if ($Security->CanAdd()) { ?>
<div class="button-add"><a href="formato_coreoadd.php">Agregar</a></div>
<?php } ?>
<div class="tablername" style="margin-top:7px">Formato de coreos</div>
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
<?php if ($formato_coreo->Export <> "") { ?>
Nombre
<?php } else { ?>
	<a href="formato_coreolist.php?order=<?php echo urlencode('Nombre') ?>&ordertype=<?php echo $formato_coreo->Nombre->ReverseSort() ?>">Nombre<?php if ($formato_coreo->Nombre->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($formato_coreo->Nombre->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($formato_coreo->Export <> "") { ?>
Precio
<?php } else { ?>
	<a href="formato_coreolist.php?order=<?php echo urlencode('Precio') ?>&ordertype=<?php echo $formato_coreo->Precio->ReverseSort() ?>">Precio<?php if ($formato_coreo->Precio->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($formato_coreo->Precio->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($formato_coreo->Export <> "") { ?>
Sufijo
<?php } else { ?>
	<a href="formato_coreolist.php?order=<?php echo urlencode('Sufijo') ?>&ordertype=<?php echo $formato_coreo->Sufijo->ReverseSort() ?>">Sufijo<?php if ($formato_coreo->Sufijo->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($formato_coreo->Sufijo->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
<?php if ($formato_coreo->Export == "") { ?>
<?php if ($Security->CanEdit()) { ?>
<td nowrap width="1" style="padding:0">&nbsp;</td>
<?php } ?>
<?php if ($Security->CanDelete()) { ?>
<td nowrap>&nbsp;</td>
<?php } ?>
<?php } ?>
	</tr>
<?php
if (defined("EW_EXPORT_ALL") && $formato_coreo->Export <> "") {
	$nStopRec = $nTotalRecs;
} else {
	$nStopRec = $nStartRec + $nDisplayRecs - 1; // Set the last record to display
}
$nRecCount = $nStartRec - 1;
if (!$rs->EOF) {
	$rs->MoveFirst();
	if (!$formato_coreo->SelectLimit) $rs->Move($nStartRec - 1); // Move to first record directly
}
$RowCnt = 0;
while (!$rs->EOF && $nRecCount < $nStopRec) {
	$nRecCount++;
	if (intval($nRecCount) >= intval($nStartRec)) {
		$RowCnt++;

	// Init row class and style
	$formato_coreo->CssClass = "ewTableRow";
	$formato_coreo->CssStyle = "";

	// Init row event
	$formato_coreo->RowClientEvents = "onmouseover='ew_MouseOver(this);' onmouseout='ew_MouseOut(this);' onclick='ew_Click(this);'";

	// Display alternate color for rows
	if ($RowCnt % 2 == 0) {
		$formato_coreo->CssClass = "ewTableAltRow";
	}
	LoadRowValues($rs); // Load row values
	$formato_coreo->RowType = EW_ROWTYPE_VIEW; // Render view
	RenderRow();
?>
	<!-- Table body -->
	<tr<?php echo $formato_coreo->DisplayAttributes() ?>>
		<!-- Nombre -->
		<td<?php echo $formato_coreo->Nombre->CellAttributes() ?>>
<div<?php echo $formato_coreo->Nombre->ViewAttributes() ?>><?php echo $formato_coreo->Nombre->ViewValue ?></div>
</td>
		<!-- Precio -->
		<td<?php echo $formato_coreo->Precio->CellAttributes() ?>>
<div<?php echo $formato_coreo->Precio->ViewAttributes() ?>><?php echo $formato_coreo->Precio->ViewValue ?></div>
</td>
		<!-- Sufijo -->
		<td<?php echo $formato_coreo->Sufijo->CellAttributes() ?>>
<div<?php echo $formato_coreo->Sufijo->ViewAttributes() ?>><?php echo $formato_coreo->Sufijo->ViewValue ?></div>
</td>
<?php if ($formato_coreo->Export == "") { ?>
<?php if ($Security->CanEdit()) { ?>
<td nowrap width="1" style="padding:0"><span class="phpmaker">
<div class="button-edit"><a href="<?php echo $formato_coreo->EditUrl() ?>">Editar</a></div>
</span></td>
<?php } ?>
<?php if ($Security->CanDelete()) { ?>
<td nowrap width="1" style="padding:0"><span class="phpmaker">
<div class="button-delete"><a href="<?php echo $formato_coreo->DeleteUrl() ?>">Borrar</a></div>
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
<?php if ($formato_coreo->Export == "") { ?>
<form action="formato_coreolist.php" name="ewpagerform" id="ewpagerform">
<table border="0" cellspacing="0" cellpadding="0" align="center" id="pagertable">
	<tr>
		<td nowrap align="center">
<span class="phpmaker">
<?php if (!isset($Pager)) $Pager = new cNumericPager($nStartRec, $nDisplayRecs, $nTotalRecs, $nRecRange) ?>
<?php if ($Pager->RecordCount > 0) { ?>
	<?php if ($Pager->FirstButton->Enabled) { ?>
	<a href="formato_coreolist.php?start=<?php echo $Pager->FirstButton->Start ?>"><b>Primero</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->PrevButton->Enabled) { ?>
	<a href="formato_coreolist.php?start=<?php echo $Pager->PrevButton->Start ?>"><b>Anterior</b></a>&nbsp;
	<?php } ?>
	<?php foreach ($Pager->Items as $PagerItem) { ?>
		<?php if ($PagerItem->Enabled) { ?><a href="formato_coreolist.php?start=<?php echo $PagerItem->Start ?>"><?php } ?><b><?php echo $PagerItem->Text ?></b><?php if ($PagerItem->Enabled) { ?></a><?php } ?>&nbsp;
	<?php } ?>
	<?php if ($Pager->NextButton->Enabled) { ?>
	<a href="formato_coreolist.php?start=<?php echo $Pager->NextButton->Start ?>"><b>Próximo</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->LastButton->Enabled) { ?>
	<a href="formato_coreolist.php?start=<?php echo $Pager->LastButton->Start ?>"><b>Último</b></a>&nbsp;
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
<?php if ($formato_coreo->Export == "") { ?>
<?php } ?>
<?php if ($formato_coreo->Export == "") { ?>
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
	global $formato_coreo;

	// Check for an Order parameter
	if (@$_GET["order"] <> "") {
		$formato_coreo->CurrentOrder = ew_StripSlashes(@$_GET["order"]);
		$formato_coreo->CurrentOrderType = @$_GET["ordertype"];

		// Field Nombre
		$formato_coreo->UpdateSort($formato_coreo->Nombre);

		// Field Precio
		$formato_coreo->UpdateSort($formato_coreo->Precio);

		// Field Sufijo
		$formato_coreo->UpdateSort($formato_coreo->Sufijo);
		$formato_coreo->setStartRecordNumber(1); // Reset start position
	}
	$sOrderBy = $formato_coreo->getSessionOrderBy(); // Get order by from Session
	if ($sOrderBy == "") {
		if ($formato_coreo->SqlOrderBy() <> "") {
			$sOrderBy = $formato_coreo->SqlOrderBy();
			$formato_coreo->setSessionOrderBy($sOrderBy);
			$formato_coreo->Nombre->setSort("ASC");
		}
	}
}

// Reset command based on querystring parameter cmd=
// - RESET: reset search parameters
// - RESETALL: reset search & master/detail parameters
// - RESETSORT: reset sort parameters
function ResetCmd() {
	global $sDbMasterFilter, $sDbDetailFilter, $nStartRec, $sOrderBy;
	global $formato_coreo;

	// Get reset cmd
	if (@$_GET["cmd"] <> "") {
		$sCmd = $_GET["cmd"];

		// Reset Sort Criteria
		if (strtolower($sCmd) == "resetsort") {
			$sOrderBy = "";
			$formato_coreo->setSessionOrderBy($sOrderBy);
			$formato_coreo->Nombre->setSort("");
			$formato_coreo->Precio->setSort("");
			$formato_coreo->Sufijo->setSort("");
		}

		// Reset start position
		$nStartRec = 1;
		$formato_coreo->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Set up Starting Record parameters based on Pager Navigation
function SetUpStartRec() {
	global $nDisplayRecs, $nStartRec, $nTotalRecs, $nPageNo, $formato_coreo;
	if ($nDisplayRecs == 0) return;

	// Check for a START parameter
	if (@$_GET[EW_TABLE_START_REC] <> "") {
		$nStartRec = $_GET[EW_TABLE_START_REC];
		$formato_coreo->setStartRecordNumber($nStartRec);
	} elseif (@$_GET[EW_TABLE_PAGE_NO] <> "") {
		$nPageNo = $_GET[EW_TABLE_PAGE_NO];
		if (is_numeric($nPageNo)) {
			$nStartRec = ($nPageNo-1)*$nDisplayRecs+1;
			if ($nStartRec <= 0) {
				$nStartRec = 1;
			} elseif ($nStartRec >= intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1) {
				$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1;
			}
			$formato_coreo->setStartRecordNumber($nStartRec);
		} else {
			$nStartRec = $formato_coreo->getStartRecordNumber();
		}
	} else {
		$nStartRec = $formato_coreo->getStartRecordNumber();
	}

	// Check if correct start record counter
	if (!is_numeric($nStartRec) || $nStartRec == "") { // Avoid invalid start record counter
		$nStartRec = 1; // Reset start record counter
		$formato_coreo->setStartRecordNumber($nStartRec);
	} elseif (intval($nStartRec) > intval($nTotalRecs)) { // Avoid starting record > total records
		$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to last page first record
		$formato_coreo->setStartRecordNumber($nStartRec);
	} elseif (($nStartRec-1) % $nDisplayRecs <> 0) {
		$nStartRec = intval(($nStartRec-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to page boundary
		$formato_coreo->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Load recordset
function LoadRecordset($offset = -1, $rowcnt = -1) {
	global $conn, $formato_coreo;

	// Call Recordset Selecting event
	$formato_coreo->Recordset_Selecting($formato_coreo->CurrentFilter);

	// Load list page sql
	$sSql = $formato_coreo->SelectSQL();
	if ($offset > -1 && $rowcnt > -1) $sSql .= " LIMIT $offset, $rowcnt";

	// Load recordset
	$conn->raiseErrorFn = 'ew_ErrorFn';	
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';

	// Call Recordset Selected event
	$formato_coreo->Recordset_Selected($rs);
	return $rs;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $formato_coreo;
	$sFilter = $formato_coreo->SqlKeyFilter();
	if (!is_numeric($formato_coreo->id->CurrentValue)) {
		return FALSE; // Invalid key, exit
	}
	$sFilter = str_replace("@id@", ew_AdjustSql($formato_coreo->id->CurrentValue), $sFilter); // Replace key value

	// Call Row Selecting event
	$formato_coreo->Row_Selecting($sFilter);

	// Load sql based on filter
	$formato_coreo->CurrentFilter = $sFilter;
	$sSql = $formato_coreo->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$formato_coreo->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $formato_coreo;
	$formato_coreo->id->setDbValue($rs->fields('id'));
	$formato_coreo->Nombre->setDbValue($rs->fields('Nombre'));
	$formato_coreo->Precio->setDbValue($rs->fields('Precio'));
	$formato_coreo->Sufijo->setDbValue($rs->fields('Sufijo'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $formato_coreo;

	// Call Row Rendering event
	$formato_coreo->Row_Rendering();

	// Common render codes for all row types
	// Nombre

	$formato_coreo->Nombre->CellCssStyle = "";
	$formato_coreo->Nombre->CellCssClass = "";

	// Precio
	$formato_coreo->Precio->CellCssStyle = "";
	$formato_coreo->Precio->CellCssClass = "";

	// Sufijo
	$formato_coreo->Sufijo->CellCssStyle = "";
	$formato_coreo->Sufijo->CellCssClass = "";
	if ($formato_coreo->RowType == EW_ROWTYPE_VIEW) { // View row

		// Nombre
		$formato_coreo->Nombre->ViewValue = $formato_coreo->Nombre->CurrentValue;
		$formato_coreo->Nombre->CssStyle = "";
		$formato_coreo->Nombre->CssClass = "";
		$formato_coreo->Nombre->ViewCustomAttributes = "";

		// Precio
		$formato_coreo->Precio->ViewValue = $formato_coreo->Precio->CurrentValue;
		$formato_coreo->Precio->CssStyle = "";
		$formato_coreo->Precio->CssClass = "";
		$formato_coreo->Precio->ViewCustomAttributes = "";

		// Sufijo
		$formato_coreo->Sufijo->ViewValue = $formato_coreo->Sufijo->CurrentValue;
		$formato_coreo->Sufijo->CssStyle = "";
		$formato_coreo->Sufijo->CssClass = "";
		$formato_coreo->Sufijo->ViewCustomAttributes = "";

		// Nombre
		$formato_coreo->Nombre->HrefValue = "";

		// Precio
		$formato_coreo->Precio->HrefValue = "";

		// Sufijo
		$formato_coreo->Sufijo->HrefValue = "";
	} elseif ($formato_coreo->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($formato_coreo->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($formato_coreo->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$formato_coreo->Row_Rendered();
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
