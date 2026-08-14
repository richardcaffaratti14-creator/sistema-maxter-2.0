<?php
define("EW_PAGE_ID", "list", TRUE); // Page ID
define("EW_TABLE_NAME", 'formato_video', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "formato_videoinfo.php" ?>
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
$Security->LoadCurrentUserLevel('formato_video');
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
$formato_video->Export = @$_GET["export"]; // Get export parameter
$sExport = $formato_video->Export; // Get export parameter, used in header
$sExportFile = $formato_video->TableVar; // Get export file, used in header
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
$formato_video->setSessionWhere($sFilter);
$formato_video->CurrentFilter = "";

// Set Up Sorting Order
SetUpSortOrder();

// Set Return Url
$formato_video->setReturnUrl("formato_videolist.php");
?>
<?php include "header.php" ?>
<?php if ($formato_video->Export == "") { ?>
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
<?php if ($formato_video->Export == "") { ?>
<?php } ?>
<?php

// Load recordset
$bExportAll = (defined("EW_EXPORT_ALL") && $formato_video->Export <> "");
$bSelectLimit = ($formato_video->Export == "" && $formato_video->SelectLimit);
if (!$bSelectLimit) $rs = LoadRecordset();
$nTotalRecs = ($bSelectLimit) ? $formato_video->SelectRecordCount() : $rs->RecordCount();
$nStartRec = 1;
if ($nDisplayRecs <= 0) $nDisplayRecs = $nTotalRecs; // Display all records
if (!$bExportAll) SetUpStartRec(); // Set up start record position
if ($bSelectLimit) $rs = LoadRecordset($nStartRec-1, $nDisplayRecs);
?>
<?php if ($formato_video->Export == "") { ?>
<?php } ?>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form method="post" name="fformato_videolist" id="fformato_videolist">
<?php if ($formato_video->Export == "") { ?>
<table class="subheader" align="center">
	<tr><td><span class="phpmaker">
<?php if ($Security->CanAdd()) { ?>
<div class="button-add"><a href="formato_videoadd.php">Agregar</a></div>
<?php } ?>
<div class="tablername" style="margin-top:7px">Formatos de videos</div>
	</span></td></tr>
</table>
<?php } ?>
<?php if ($nTotalRecs > 0) { ?>
<div id="whiteback">
<table id="ewlistmain" class="ewTable" align="center">
<?php
	$OptionCnt = 0;
if ($Security->CanView()) {
	$OptionCnt++; // view
}
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
<?php if ($formato_video->Export <> "") { ?>
Formato
<?php } else { ?>
	<a href="formato_videolist.php?order=<?php echo urlencode('nombre') ?>&ordertype=<?php echo $formato_video->nombre->ReverseSort() ?>">Formato<?php if ($formato_video->nombre->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($formato_video->nombre->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($formato_video->Export <> "") { ?>
Precio
<?php } else { ?>
	<a href="formato_videolist.php?order=<?php echo urlencode('precio') ?>&ordertype=<?php echo $formato_video->precio->ReverseSort() ?>">Precio<?php if ($formato_video->precio->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($formato_video->precio->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($formato_video->Export <> "") { ?>
Carpeta
<?php } else { ?>
	<a href="formato_videolist.php?order=<?php echo urlencode('carpeta') ?>&ordertype=<?php echo $formato_video->carpeta->ReverseSort() ?>">Carpeta<?php if ($formato_video->carpeta->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($formato_video->carpeta->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($formato_video->Export <> "") { ?>
Orden
<?php } else { ?>
	<a href="formato_videolist.php?order=<?php echo urlencode('orden') ?>&ordertype=<?php echo $formato_video->orden->ReverseSort() ?>">Orden<?php if ($formato_video->orden->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($formato_video->orden->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($formato_video->Export <> "") { ?>
Sufijo
<?php } else { ?>
	<a href="formato_videolist.php?order=<?php echo urlencode('Sufijo') ?>&ordertype=<?php echo $formato_video->Sufijo->ReverseSort() ?>">Sufijo<?php if ($formato_video->Sufijo->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($formato_video->Sufijo->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
<?php if ($formato_video->Export == "") { ?>
<?php if ($Security->CanView()) { ?>
<td nowrap>&nbsp;</td>
<?php } ?>
<?php if ($Security->CanEdit()) { ?>
<td nowrap width="1" style="padding:0">&nbsp;</td>
<?php } ?>
<?php if ($Security->CanDelete()) { ?>
<td nowrap>&nbsp;</td>
<?php } ?>
<?php } ?>
	</tr>
<?php
if (defined("EW_EXPORT_ALL") && $formato_video->Export <> "") {
	$nStopRec = $nTotalRecs;
} else {
	$nStopRec = $nStartRec + $nDisplayRecs - 1; // Set the last record to display
}
$nRecCount = $nStartRec - 1;
if (!$rs->EOF) {
	$rs->MoveFirst();
	if (!$formato_video->SelectLimit) $rs->Move($nStartRec - 1); // Move to first record directly
}
$RowCnt = 0;
while (!$rs->EOF && $nRecCount < $nStopRec) {
	$nRecCount++;
	if (intval($nRecCount) >= intval($nStartRec)) {
		$RowCnt++;

	// Init row class and style
	$formato_video->CssClass = "ewTableRow";
	$formato_video->CssStyle = "";

	// Init row event
	$formato_video->RowClientEvents = "onmouseover='ew_MouseOver(this);' onmouseout='ew_MouseOut(this);' onclick='ew_Click(this);'";

	// Display alternate color for rows
	if ($RowCnt % 2 == 0) {
		$formato_video->CssClass = "ewTableAltRow";
	}
	LoadRowValues($rs); // Load row values
	$formato_video->RowType = EW_ROWTYPE_VIEW; // Render view
	RenderRow();
?>
	<!-- Table body -->
	<tr<?php echo $formato_video->DisplayAttributes() ?>>
		<!-- nombre -->
		<td<?php echo $formato_video->nombre->CellAttributes() ?>>
<div<?php echo $formato_video->nombre->ViewAttributes() ?>><?php echo $formato_video->nombre->ViewValue ?></div>
</td>
		<!-- precio -->
		<td<?php echo $formato_video->precio->CellAttributes() ?>>
<div<?php echo $formato_video->precio->ViewAttributes() ?>><?php echo $formato_video->precio->ViewValue ?></div>
</td>
		<!-- carpeta -->
		<td<?php echo $formato_video->carpeta->CellAttributes() ?>>
<div<?php echo $formato_video->carpeta->ViewAttributes() ?>><?php echo $formato_video->carpeta->ViewValue ?></div>
</td>
		<!-- orden -->
		<td<?php echo $formato_video->orden->CellAttributes() ?>>
<div<?php echo $formato_video->orden->ViewAttributes() ?>><?php echo $formato_video->orden->ViewValue ?></div>
</td>
		<!-- Sufijo -->
		<td<?php echo $formato_video->Sufijo->CellAttributes() ?>>
<div<?php echo $formato_video->Sufijo->ViewAttributes() ?>><?php echo $formato_video->Sufijo->ViewValue ?></div>
</td>
<?php if ($formato_video->Export == "") { ?>
<?php if ($Security->CanView()) { ?>
<td nowrap width="1" style="padding:0"><span class="phpmaker">
<div class="button-view"><a href="<?php echo $formato_video->ViewUrl() ?>">Vista</a></div>
</span></td>
<?php } ?>
<?php if ($Security->CanEdit()) { ?>
<td nowrap width="1" style="padding:0"><span class="phpmaker">
<div class="button-edit"><a href="<?php echo $formato_video->EditUrl() ?>">Editar</a></div>
</span></td>
<?php } ?>
<?php if ($Security->CanDelete()) { ?>
<td nowrap width="1" style="padding:0"><span class="phpmaker">
<div class="button-delete"><a href="<?php echo $formato_video->DeleteUrl() ?>">Borrar</a></div>
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
<?php if ($formato_video->Export == "") { ?>
<form action="formato_videolist.php" name="ewpagerform" id="ewpagerform">
<table border="0" cellspacing="0" cellpadding="0" align="center" id="pagertable">
	<tr>
		<td nowrap align="center">
<span class="phpmaker">
<?php if (!isset($Pager)) $Pager = new cNumericPager($nStartRec, $nDisplayRecs, $nTotalRecs, $nRecRange) ?>
<?php if ($Pager->RecordCount > 0) { ?>
	<?php if ($Pager->FirstButton->Enabled) { ?>
	<a href="formato_videolist.php?start=<?php echo $Pager->FirstButton->Start ?>"><b>Primero</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->PrevButton->Enabled) { ?>
	<a href="formato_videolist.php?start=<?php echo $Pager->PrevButton->Start ?>"><b>Anterior</b></a>&nbsp;
	<?php } ?>
	<?php foreach ($Pager->Items as $PagerItem) { ?>
		<?php if ($PagerItem->Enabled) { ?><a href="formato_videolist.php?start=<?php echo $PagerItem->Start ?>"><?php } ?><b><?php echo $PagerItem->Text ?></b><?php if ($PagerItem->Enabled) { ?></a><?php } ?>&nbsp;
	<?php } ?>
	<?php if ($Pager->NextButton->Enabled) { ?>
	<a href="formato_videolist.php?start=<?php echo $Pager->NextButton->Start ?>"><b>Próximo</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->LastButton->Enabled) { ?>
	<a href="formato_videolist.php?start=<?php echo $Pager->LastButton->Start ?>"><b>Último</b></a>&nbsp;
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
<?php if ($formato_video->Export == "") { ?>
<?php } ?>
<?php if ($formato_video->Export == "") { ?>
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
	global $formato_video;

	// Check for an Order parameter
	if (@$_GET["order"] <> "") {
		$formato_video->CurrentOrder = ew_StripSlashes(@$_GET["order"]);
		$formato_video->CurrentOrderType = @$_GET["ordertype"];

		// Field nombre
		$formato_video->UpdateSort($formato_video->nombre);

		// Field precio
		$formato_video->UpdateSort($formato_video->precio);

		// Field carpeta
		$formato_video->UpdateSort($formato_video->carpeta);

		// Field orden
		$formato_video->UpdateSort($formato_video->orden);

		// Field Sufijo
		$formato_video->UpdateSort($formato_video->Sufijo);
		$formato_video->setStartRecordNumber(1); // Reset start position
	}
	$sOrderBy = $formato_video->getSessionOrderBy(); // Get order by from Session
	if ($sOrderBy == "") {
		if ($formato_video->SqlOrderBy() <> "") {
			$sOrderBy = $formato_video->SqlOrderBy();
			$formato_video->setSessionOrderBy($sOrderBy);
			$formato_video->orden->setSort("ASC");
		}
	}
}

// Reset command based on querystring parameter cmd=
// - RESET: reset search parameters
// - RESETALL: reset search & master/detail parameters
// - RESETSORT: reset sort parameters
function ResetCmd() {
	global $sDbMasterFilter, $sDbDetailFilter, $nStartRec, $sOrderBy;
	global $formato_video;

	// Get reset cmd
	if (@$_GET["cmd"] <> "") {
		$sCmd = $_GET["cmd"];

		// Reset Sort Criteria
		if (strtolower($sCmd) == "resetsort") {
			$sOrderBy = "";
			$formato_video->setSessionOrderBy($sOrderBy);
			$formato_video->nombre->setSort("");
			$formato_video->precio->setSort("");
			$formato_video->carpeta->setSort("");
			$formato_video->orden->setSort("");
			$formato_video->Sufijo->setSort("");
		}

		// Reset start position
		$nStartRec = 1;
		$formato_video->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Set up Starting Record parameters based on Pager Navigation
function SetUpStartRec() {
	global $nDisplayRecs, $nStartRec, $nTotalRecs, $nPageNo, $formato_video;
	if ($nDisplayRecs == 0) return;

	// Check for a START parameter
	if (@$_GET[EW_TABLE_START_REC] <> "") {
		$nStartRec = $_GET[EW_TABLE_START_REC];
		$formato_video->setStartRecordNumber($nStartRec);
	} elseif (@$_GET[EW_TABLE_PAGE_NO] <> "") {
		$nPageNo = $_GET[EW_TABLE_PAGE_NO];
		if (is_numeric($nPageNo)) {
			$nStartRec = ($nPageNo-1)*$nDisplayRecs+1;
			if ($nStartRec <= 0) {
				$nStartRec = 1;
			} elseif ($nStartRec >= intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1) {
				$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1;
			}
			$formato_video->setStartRecordNumber($nStartRec);
		} else {
			$nStartRec = $formato_video->getStartRecordNumber();
		}
	} else {
		$nStartRec = $formato_video->getStartRecordNumber();
	}

	// Check if correct start record counter
	if (!is_numeric($nStartRec) || $nStartRec == "") { // Avoid invalid start record counter
		$nStartRec = 1; // Reset start record counter
		$formato_video->setStartRecordNumber($nStartRec);
	} elseif (intval($nStartRec) > intval($nTotalRecs)) { // Avoid starting record > total records
		$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to last page first record
		$formato_video->setStartRecordNumber($nStartRec);
	} elseif (($nStartRec-1) % $nDisplayRecs <> 0) {
		$nStartRec = intval(($nStartRec-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to page boundary
		$formato_video->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Load recordset
function LoadRecordset($offset = -1, $rowcnt = -1) {
	global $conn, $formato_video;

	// Call Recordset Selecting event
	$formato_video->Recordset_Selecting($formato_video->CurrentFilter);

	// Load list page sql
	$sSql = $formato_video->SelectSQL();
	if ($offset > -1 && $rowcnt > -1) $sSql .= " LIMIT $offset, $rowcnt";

	// Load recordset
	$conn->raiseErrorFn = 'ew_ErrorFn';	
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';

	// Call Recordset Selected event
	$formato_video->Recordset_Selected($rs);
	return $rs;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $formato_video;
	$sFilter = $formato_video->SqlKeyFilter();
	if (!is_numeric($formato_video->id->CurrentValue)) {
		return FALSE; // Invalid key, exit
	}
	$sFilter = str_replace("@id@", ew_AdjustSql($formato_video->id->CurrentValue), $sFilter); // Replace key value

	// Call Row Selecting event
	$formato_video->Row_Selecting($sFilter);

	// Load sql based on filter
	$formato_video->CurrentFilter = $sFilter;
	$sSql = $formato_video->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$formato_video->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $formato_video;
	$formato_video->id->setDbValue($rs->fields('id'));
	$formato_video->nombre->setDbValue($rs->fields('nombre'));
	$formato_video->precio->setDbValue($rs->fields('precio'));
	$formato_video->carpeta->setDbValue($rs->fields('carpeta'));
	$formato_video->orden->setDbValue($rs->fields('orden'));
	$formato_video->Sufijo->setDbValue($rs->fields('Sufijo'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $formato_video;

	// Call Row Rendering event
	$formato_video->Row_Rendering();

	// Common render codes for all row types
	// nombre

	$formato_video->nombre->CellCssStyle = "";
	$formato_video->nombre->CellCssClass = "";

	// precio
	$formato_video->precio->CellCssStyle = "";
	$formato_video->precio->CellCssClass = "";

	// carpeta
	$formato_video->carpeta->CellCssStyle = "";
	$formato_video->carpeta->CellCssClass = "";

	// orden
	$formato_video->orden->CellCssStyle = "";
	$formato_video->orden->CellCssClass = "";

	// Sufijo
	$formato_video->Sufijo->CellCssStyle = "";
	$formato_video->Sufijo->CellCssClass = "";
	if ($formato_video->RowType == EW_ROWTYPE_VIEW) { // View row

		// nombre
		$formato_video->nombre->ViewValue = $formato_video->nombre->CurrentValue;
		$formato_video->nombre->CssStyle = "";
		$formato_video->nombre->CssClass = "";
		$formato_video->nombre->ViewCustomAttributes = "";

		// precio
		$formato_video->precio->ViewValue = $formato_video->precio->CurrentValue;
		$formato_video->precio->ViewValue = ew_FormatCurrency($formato_video->precio->ViewValue, 2, -2, -2, -2);
		$formato_video->precio->CssStyle = "";
		$formato_video->precio->CssClass = "";
		$formato_video->precio->ViewCustomAttributes = "";

		// carpeta
		$formato_video->carpeta->ViewValue = $formato_video->carpeta->CurrentValue;
		$formato_video->carpeta->CssStyle = "";
		$formato_video->carpeta->CssClass = "";
		$formato_video->carpeta->ViewCustomAttributes = "";

		// orden
		$formato_video->orden->ViewValue = $formato_video->orden->CurrentValue;
		$formato_video->orden->CssStyle = "";
		$formato_video->orden->CssClass = "";
		$formato_video->orden->ViewCustomAttributes = "";

		// Sufijo
		$formato_video->Sufijo->ViewValue = $formato_video->Sufijo->CurrentValue;
		$formato_video->Sufijo->CssStyle = "";
		$formato_video->Sufijo->CssClass = "";
		$formato_video->Sufijo->ViewCustomAttributes = "";

		// nombre
		$formato_video->nombre->HrefValue = "";

		// precio
		$formato_video->precio->HrefValue = "";

		// carpeta
		$formato_video->carpeta->HrefValue = "";

		// orden
		$formato_video->orden->HrefValue = "";

		// Sufijo
		$formato_video->Sufijo->HrefValue = "";
	} elseif ($formato_video->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($formato_video->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($formato_video->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$formato_video->Row_Rendered();
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
