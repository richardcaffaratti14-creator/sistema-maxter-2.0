<?php
define("EW_PAGE_ID", "list", TRUE); // Page ID
define("EW_TABLE_NAME", 'usuarioslevelpermissions', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "usuarioslevelpermissionsinfo.php" ?>
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
$Security->LoadCurrentUserLevel('usuarioslevelpermissions');
if (!$Security->CanAdmin()) {
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
$usuarioslevelpermissions->Export = @$_GET["export"]; // Get export parameter
$sExport = $usuarioslevelpermissions->Export; // Get export parameter, used in header
$sExportFile = $usuarioslevelpermissions->TableVar; // Get export file, used in header
?>
<?php
?>
<?php

// Paging variables
$nStartRec = 0; // Start record index
$nStopRec = 0; // Stop record index
$nTotalRecs = 0; // Total number of records
$nDisplayRecs = 30;
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
$usuarioslevelpermissions->setSessionWhere($sFilter);
$usuarioslevelpermissions->CurrentFilter = "";

// Set Up Sorting Order
SetUpSortOrder();

// Set Return Url
$usuarioslevelpermissions->setReturnUrl("usuarioslevelpermissionslist.php");
?>
<?php include "header.php" ?>
<?php if ($usuarioslevelpermissions->Export == "") { ?>
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

// js for DHtml Editor
//-->

</script>
<script type="text/javascript">
<!--

// js for Popup Calendar
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
<?php if ($usuarioslevelpermissions->Export == "") { ?>
<?php } ?>
<?php

// Load recordset
$bExportAll = (defined("EW_EXPORT_ALL") && $usuarioslevelpermissions->Export <> "");
$bSelectLimit = ($usuarioslevelpermissions->Export == "" && $usuarioslevelpermissions->SelectLimit);
if (!$bSelectLimit) $rs = LoadRecordset();
$nTotalRecs = ($bSelectLimit) ? $usuarioslevelpermissions->SelectRecordCount() : $rs->RecordCount();
$nStartRec = 1;
if ($nDisplayRecs <= 0) $nDisplayRecs = $nTotalRecs; // Display all records
if (!$bExportAll) SetUpStartRec(); // Set up start record position
if ($bSelectLimit) $rs = LoadRecordset($nStartRec-1, $nDisplayRecs);
?>
<?php if ($usuarioslevelpermissions->Export == "") { ?>
<?php } ?>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form method="post" name="fusuarioslevelpermissionslist" id="fusuarioslevelpermissionslist">
<?php if ($usuarioslevelpermissions->Export == "") { ?>
<table class="subheader" align="center">
	<tr><td><span class="phpmaker">
<div class="tablername" style="margin-top:7px">usuarioslevelpermissions</div>
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
?>
	<!-- Table header -->
	<tr class="ewTableHeader">
		<td valign="top">
<?php if ($usuarioslevelpermissions->Export <> "") { ?>
User Level ID
<?php } else { ?>
	<a href="usuarioslevelpermissionslist.php?order=<?php echo urlencode('UserLevelID') ?>&ordertype=<?php echo $usuarioslevelpermissions->UserLevelID->ReverseSort() ?>">User Level ID<?php if ($usuarioslevelpermissions->UserLevelID->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($usuarioslevelpermissions->UserLevelID->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($usuarioslevelpermissions->Export <> "") { ?>
Table Name
<?php } else { ?>
	<a href="usuarioslevelpermissionslist.php?order=<?php echo urlencode('TableName') ?>&ordertype=<?php echo $usuarioslevelpermissions->TableName->ReverseSort() ?>">Table Name<?php if ($usuarioslevelpermissions->TableName->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($usuarioslevelpermissions->TableName->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($usuarioslevelpermissions->Export <> "") { ?>
Permission
<?php } else { ?>
	<a href="usuarioslevelpermissionslist.php?order=<?php echo urlencode('Permission') ?>&ordertype=<?php echo $usuarioslevelpermissions->Permission->ReverseSort() ?>">Permission<?php if ($usuarioslevelpermissions->Permission->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($usuarioslevelpermissions->Permission->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
<?php if ($usuarioslevelpermissions->Export == "") { ?>
<?php if ($Security->CanEdit()) { ?>
<td nowrap width="1" style="padding:0">&nbsp;</td>
<?php } ?>
<?php } ?>
	</tr>
<?php
if (defined("EW_EXPORT_ALL") && $usuarioslevelpermissions->Export <> "") {
	$nStopRec = $nTotalRecs;
} else {
	$nStopRec = $nStartRec + $nDisplayRecs - 1; // Set the last record to display
}
$nRecCount = $nStartRec - 1;
if (!$rs->EOF) {
	$rs->MoveFirst();
	if (!$usuarioslevelpermissions->SelectLimit) $rs->Move($nStartRec - 1); // Move to first record directly
}
$RowCnt = 0;
while (!$rs->EOF && $nRecCount < $nStopRec) {
	$nRecCount++;
	if (intval($nRecCount) >= intval($nStartRec)) {
		$RowCnt++;

	// Init row class and style
	$usuarioslevelpermissions->CssClass = "ewTableRow";
	$usuarioslevelpermissions->CssStyle = "";

	// Init row event
	$usuarioslevelpermissions->RowClientEvents = "onmouseover='ew_MouseOver(this);' onmouseout='ew_MouseOut(this);' onclick='ew_Click(this);'";

	// Display alternate color for rows
	if ($RowCnt % 2 == 0) {
		$usuarioslevelpermissions->CssClass = "ewTableAltRow";
	}
	LoadRowValues($rs); // Load row values
	$usuarioslevelpermissions->RowType = EW_ROWTYPE_VIEW; // Render view
	RenderRow();
?>
	<!-- Table body -->
	<tr<?php echo $usuarioslevelpermissions->DisplayAttributes() ?>>
		<!-- UserLevelID -->
		<td<?php echo $usuarioslevelpermissions->UserLevelID->CellAttributes() ?>>
<div<?php echo $usuarioslevelpermissions->UserLevelID->ViewAttributes() ?>><?php echo $usuarioslevelpermissions->UserLevelID->ViewValue ?></div>
</td>
		<!-- TableName -->
		<td<?php echo $usuarioslevelpermissions->TableName->CellAttributes() ?>>
<div<?php echo $usuarioslevelpermissions->TableName->ViewAttributes() ?>><?php echo $usuarioslevelpermissions->TableName->ViewValue ?></div>
</td>
		<!-- Permission -->
		<td<?php echo $usuarioslevelpermissions->Permission->CellAttributes() ?>>
<div<?php echo $usuarioslevelpermissions->Permission->ViewAttributes() ?>><?php echo $usuarioslevelpermissions->Permission->ViewValue ?></div>
</td>
<?php if ($usuarioslevelpermissions->Export == "") { ?>
<?php if ($Security->CanEdit()) { ?>
<td nowrap width="1" style="padding:0"><span class="phpmaker">
<div class="button-edit"><a href="<?php echo $usuarioslevelpermissions->EditUrl() ?>">Editar</a></div>
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
</form>
<?php

// Close recordset and connection
if ($rs) $rs->Close();
?>
<?php if ($usuarioslevelpermissions->Export == "") { ?>
<form action="usuarioslevelpermissionslist.php" name="ewpagerform" id="ewpagerform">
<table border="0" cellspacing="0" cellpadding="0" align="center" id="pagertable">
	<tr>
		<td nowrap align="center">
<span class="phpmaker">
<?php if (!isset($Pager)) $Pager = new cNumericPager($nStartRec, $nDisplayRecs, $nTotalRecs, $nRecRange) ?>
<?php if ($Pager->RecordCount > 0) { ?>
	<?php if ($Pager->FirstButton->Enabled) { ?>
	<a href="usuarioslevelpermissionslist.php?start=<?php echo $Pager->FirstButton->Start ?>"><b>Primero</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->PrevButton->Enabled) { ?>
	<a href="usuarioslevelpermissionslist.php?start=<?php echo $Pager->PrevButton->Start ?>"><b>Anterior</b></a>&nbsp;
	<?php } ?>
	<?php foreach ($Pager->Items as $PagerItem) { ?>
		<?php if ($PagerItem->Enabled) { ?><a href="usuarioslevelpermissionslist.php?start=<?php echo $PagerItem->Start ?>"><?php } ?><b><?php echo $PagerItem->Text ?></b><?php if ($PagerItem->Enabled) { ?></a><?php } ?>&nbsp;
	<?php } ?>
	<?php if ($Pager->NextButton->Enabled) { ?>
	<a href="usuarioslevelpermissionslist.php?start=<?php echo $Pager->NextButton->Start ?>"><b>Proximo</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->LastButton->Enabled) { ?>
	<a href="usuarioslevelpermissionslist.php?start=<?php echo $Pager->LastButton->Start ?>"><b>Último</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->ButtonCount > 0) { ?><br><br><?php } ?>
	Registros <?php echo $Pager->FromIndex ?> a <?php echo $Pager->ToIndex ?> de <?php echo $Pager->RecordCount ?>
<?php } else { ?>	
	<?php if ($Security->CanList()) { ?>
	<?php if ($sSrchWhere == "0=101") { ?>
	Porfavor ingrese el criterio de busqueda
	<?php } else { ?>
	No se encontraron registros
	<?php } ?>
	<?php } else { ?>
	Usted no tiene permisos para visualizar esta pagina
	<?php } ?>
<?php } ?>
</span>
		</td>
	</tr>
</table>
</form>
<?php } ?>
<?php if ($usuarioslevelpermissions->Export == "") { ?>
<?php } ?>
<?php if ($usuarioslevelpermissions->Export == "") { ?>
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
	global $usuarioslevelpermissions;

	// Check for an Order parameter
	if (@$_GET["order"] <> "") {
		$usuarioslevelpermissions->CurrentOrder = ew_StripSlashes(@$_GET["order"]);
		$usuarioslevelpermissions->CurrentOrderType = @$_GET["ordertype"];

		// Field UserLevelID
		$usuarioslevelpermissions->UpdateSort($usuarioslevelpermissions->UserLevelID);

		// Field TableName
		$usuarioslevelpermissions->UpdateSort($usuarioslevelpermissions->TableName);

		// Field Permission
		$usuarioslevelpermissions->UpdateSort($usuarioslevelpermissions->Permission);
		$usuarioslevelpermissions->setStartRecordNumber(1); // Reset start position
	}
	$sOrderBy = $usuarioslevelpermissions->getSessionOrderBy(); // Get order by from Session
	if ($sOrderBy == "") {
		if ($usuarioslevelpermissions->SqlOrderBy() <> "") {
			$sOrderBy = $usuarioslevelpermissions->SqlOrderBy();
			$usuarioslevelpermissions->setSessionOrderBy($sOrderBy);
		}
	}
}

// Reset command based on querystring parameter cmd=
// - RESET: reset search parameters
// - RESETALL: reset search & master/detail parameters
// - RESETSORT: reset sort parameters
function ResetCmd() {
	global $sDbMasterFilter, $sDbDetailFilter, $nStartRec, $sOrderBy;
	global $usuarioslevelpermissions;

	// Get reset cmd
	if (@$_GET["cmd"] <> "") {
		$sCmd = $_GET["cmd"];

		// Reset Sort Criteria
		if (strtolower($sCmd) == "resetsort") {
			$sOrderBy = "";
			$usuarioslevelpermissions->setSessionOrderBy($sOrderBy);
			$usuarioslevelpermissions->UserLevelID->setSort("");
			$usuarioslevelpermissions->TableName->setSort("");
			$usuarioslevelpermissions->Permission->setSort("");
		}

		// Reset start position
		$nStartRec = 1;
		$usuarioslevelpermissions->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Set up Starting Record parameters based on Pager Navigation
function SetUpStartRec() {
	global $nDisplayRecs, $nStartRec, $nTotalRecs, $nPageNo, $usuarioslevelpermissions;
	if ($nDisplayRecs == 0) return;

	// Check for a START parameter
	if (@$_GET[EW_TABLE_START_REC] <> "") {
		$nStartRec = $_GET[EW_TABLE_START_REC];
		$usuarioslevelpermissions->setStartRecordNumber($nStartRec);
	} elseif (@$_GET[EW_TABLE_PAGE_NO] <> "") {
		$nPageNo = $_GET[EW_TABLE_PAGE_NO];
		if (is_numeric($nPageNo)) {
			$nStartRec = ($nPageNo-1)*$nDisplayRecs+1;
			if ($nStartRec <= 0) {
				$nStartRec = 1;
			} elseif ($nStartRec >= intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1) {
				$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1;
			}
			$usuarioslevelpermissions->setStartRecordNumber($nStartRec);
		} else {
			$nStartRec = $usuarioslevelpermissions->getStartRecordNumber();
		}
	} else {
		$nStartRec = $usuarioslevelpermissions->getStartRecordNumber();
	}

	// Check if correct start record counter
	if (!is_numeric($nStartRec) || $nStartRec == "") { // Avoid invalid start record counter
		$nStartRec = 1; // Reset start record counter
		$usuarioslevelpermissions->setStartRecordNumber($nStartRec);
	} elseif (intval($nStartRec) > intval($nTotalRecs)) { // Avoid starting record > total records
		$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to last page first record
		$usuarioslevelpermissions->setStartRecordNumber($nStartRec);
	} elseif (($nStartRec-1) % $nDisplayRecs <> 0) {
		$nStartRec = intval(($nStartRec-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to page boundary
		$usuarioslevelpermissions->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Load recordset
function LoadRecordset($offset = -1, $rowcnt = -1) {
	global $conn, $usuarioslevelpermissions;

	// Call Recordset Selecting event
	$usuarioslevelpermissions->Recordset_Selecting($usuarioslevelpermissions->CurrentFilter);

	// Load list page sql
	$sSql = $usuarioslevelpermissions->SelectSQL();
	if ($offset > -1 && $rowcnt > -1) $sSql .= " LIMIT $offset, $rowcnt";

	// Load recordset
	$conn->raiseErrorFn = 'ew_ErrorFn';	
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';

	// Call Recordset Selected event
	$usuarioslevelpermissions->Recordset_Selected($rs);
	return $rs;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $usuarioslevelpermissions;
	$sFilter = $usuarioslevelpermissions->SqlKeyFilter();
	if (!is_numeric($usuarioslevelpermissions->UserLevelID->CurrentValue)) {
		return FALSE; // Invalid key, exit
	}
	$sFilter = str_replace("@UserLevelID@", ew_AdjustSql($usuarioslevelpermissions->UserLevelID->CurrentValue), $sFilter); // Replace key value
	$sFilter = str_replace("@TableName@", ew_AdjustSql($usuarioslevelpermissions->TableName->CurrentValue), $sFilter); // Replace key value

	// Call Row Selecting event
	$usuarioslevelpermissions->Row_Selecting($sFilter);

	// Load sql based on filter
	$usuarioslevelpermissions->CurrentFilter = $sFilter;
	$sSql = $usuarioslevelpermissions->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$usuarioslevelpermissions->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $usuarioslevelpermissions;
	$usuarioslevelpermissions->UserLevelID->setDbValue($rs->fields('UserLevelID'));
	$usuarioslevelpermissions->TableName->setDbValue($rs->fields('TableName'));
	$usuarioslevelpermissions->Permission->setDbValue($rs->fields('Permission'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $usuarioslevelpermissions;

	// Call Row Rendering event
	$usuarioslevelpermissions->Row_Rendering();

	// Common render codes for all row types
	// UserLevelID

	$usuarioslevelpermissions->UserLevelID->CellCssStyle = "";
	$usuarioslevelpermissions->UserLevelID->CellCssClass = "";

	// TableName
	$usuarioslevelpermissions->TableName->CellCssStyle = "";
	$usuarioslevelpermissions->TableName->CellCssClass = "";

	// Permission
	$usuarioslevelpermissions->Permission->CellCssStyle = "";
	$usuarioslevelpermissions->Permission->CellCssClass = "";
	if ($usuarioslevelpermissions->RowType == EW_ROWTYPE_VIEW) { // View row

		// UserLevelID
		$usuarioslevelpermissions->UserLevelID->ViewValue = $usuarioslevelpermissions->UserLevelID->CurrentValue;
		$usuarioslevelpermissions->UserLevelID->CssStyle = "";
		$usuarioslevelpermissions->UserLevelID->CssClass = "";
		$usuarioslevelpermissions->UserLevelID->ViewCustomAttributes = "";

		// TableName
		$usuarioslevelpermissions->TableName->ViewValue = $usuarioslevelpermissions->TableName->CurrentValue;
		$usuarioslevelpermissions->TableName->CssStyle = "";
		$usuarioslevelpermissions->TableName->CssClass = "";
		$usuarioslevelpermissions->TableName->ViewCustomAttributes = "";

		// Permission
		$usuarioslevelpermissions->Permission->ViewValue = $usuarioslevelpermissions->Permission->CurrentValue;
		$usuarioslevelpermissions->Permission->CssStyle = "";
		$usuarioslevelpermissions->Permission->CssClass = "";
		$usuarioslevelpermissions->Permission->ViewCustomAttributes = "";

		// UserLevelID
		$usuarioslevelpermissions->UserLevelID->HrefValue = "";

		// TableName
		$usuarioslevelpermissions->TableName->HrefValue = "";

		// Permission
		$usuarioslevelpermissions->Permission->HrefValue = "";
	} elseif ($usuarioslevelpermissions->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($usuarioslevelpermissions->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($usuarioslevelpermissions->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$usuarioslevelpermissions->Row_Rendered();
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
