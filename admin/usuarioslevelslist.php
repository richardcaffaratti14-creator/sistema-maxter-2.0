<?php
define("EW_PAGE_ID", "list", TRUE); // Page ID
define("EW_TABLE_NAME", 'usuarioslevels', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "usuarioslevelsinfo.php" ?>
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
$Security->LoadCurrentUserLevel('usuarioslevels');
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
$usuarioslevels->Export = @$_GET["export"]; // Get export parameter
$sExport = $usuarioslevels->Export; // Get export parameter, used in header
$sExportFile = $usuarioslevels->TableVar; // Get export file, used in header
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
	$usuarioslevels->setSearchWhere($sSrchWhere); // Save to Session
	$nStartRec = 1; // Reset start record counter
	$usuarioslevels->setStartRecordNumber($nStartRec);
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
$usuarioslevels->setSessionWhere($sFilter);
$usuarioslevels->CurrentFilter = "";

// Set Up Sorting Order
SetUpSortOrder();

// Set Return Url
$usuarioslevels->setReturnUrl("usuarioslevelslist.php");
?>
<?php include "header.php" ?>
<?php if ($usuarioslevels->Export == "") { ?>
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
<?php if ($usuarioslevels->Export == "") { ?>
<?php } ?>
<?php

// Load recordset
$bExportAll = (defined("EW_EXPORT_ALL") && $usuarioslevels->Export <> "");
$bSelectLimit = ($usuarioslevels->Export == "" && $usuarioslevels->SelectLimit);
if (!$bSelectLimit) $rs = LoadRecordset();
$nTotalRecs = ($bSelectLimit) ? $usuarioslevels->SelectRecordCount() : $rs->RecordCount();
$nStartRec = 1;
if ($nDisplayRecs <= 0) $nDisplayRecs = $nTotalRecs; // Display all records
if (!$bExportAll) SetUpStartRec(); // Set up start record position
if ($bSelectLimit) $rs = LoadRecordset($nStartRec-1, $nDisplayRecs);
?>
<?php if ($usuarioslevels->Export == "") { ?>
<?php if ($Security->CanSearch()) { ?>
<form name="fusuarioslevelslistsrch" id="fusuarioslevelslistsrch" action="usuarioslevelslist.php" >
<table class="ewBasicSearch" align="center">
	<tr>
		<td><span class="phpmaker">
			<input type="text" name="<?php echo EW_TABLE_BASIC_SEARCH ?>" id="<?php echo EW_TABLE_BASIC_SEARCH ?>" size="20" value="<?php echo $usuarioslevels->getBasicSearchKeyword() ?>">
			<input type="Submit" name="Submit" id="Submit" value="Buscar (*)">&nbsp;
			<a href="usuarioslevelslist.php?cmd=reset">Mostrar todo</a>&nbsp;
		</span></td>
	</tr>
	<tr>
	<td><span class="phpmaker"><input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="" <?php if ($usuarioslevels->getBasicSearchType() == "") { ?>checked<?php } ?>>Frase exacta&nbsp;&nbsp;<input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="AND" <?php if ($usuarioslevels->getBasicSearchType() == "AND") { ?>checked<?php } ?>>Todas las palabras&nbsp;&nbsp;<input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="OR" <?php if ($usuarioslevels->getBasicSearchType() == "OR") { ?>checked<?php } ?>>Cualquier palabra</span></td>
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
<form method="post" name="fusuarioslevelslist" id="fusuarioslevelslist">
<?php if ($usuarioslevels->Export == "") { ?>
<table class="subheader" align="center">
	<tr><td><span class="phpmaker">
<?php if ($Security->CanAdd()) { ?>
<div class="button-add"><a href="usuarioslevelsadd.php">Agregar</a></div>
<?php } ?>
<div class="tablername" style="margin-top:7px">Niveles de Usuarios</div>
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
	$OptionCnt++; // permission
?>
	<!-- Table header -->
	<tr class="ewTableHeader">
		<td valign="top">
<?php if ($usuarioslevels->Export <> "") { ?>
ID de Nivel de Usuario
<?php } else { ?>
	<a href="usuarioslevelslist.php?order=<?php echo urlencode('UserLevelID') ?>&ordertype=<?php echo $usuarioslevels->UserLevelID->ReverseSort() ?>">ID de Nivel de Usuario<?php if ($usuarioslevels->UserLevelID->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($usuarioslevels->UserLevelID->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($usuarioslevels->Export <> "") { ?>
Nivel de Usuario
<?php } else { ?>
	<a href="usuarioslevelslist.php?order=<?php echo urlencode('UserLevelName') ?>&ordertype=<?php echo $usuarioslevels->UserLevelName->ReverseSort() ?>">Nivel de Usuario&nbsp;(*)<?php if ($usuarioslevels->UserLevelName->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($usuarioslevels->UserLevelName->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
<?php if ($usuarioslevels->Export == "") { ?>
<?php if ($Security->CanView()) { ?>
<td nowrap>&nbsp;</td>
<?php } ?>
<?php if ($Security->CanEdit()) { ?>
<td nowrap width="1" style="padding:0">&nbsp;</td>
<?php } ?>
<?php if ($Security->CanDelete()) { ?>
<td nowrap>&nbsp;</td>
<?php } ?>
<td nowrap>&nbsp;</td>
<?php } ?>
	</tr>
<?php
if (defined("EW_EXPORT_ALL") && $usuarioslevels->Export <> "") {
	$nStopRec = $nTotalRecs;
} else {
	$nStopRec = $nStartRec + $nDisplayRecs - 1; // Set the last record to display
}
$nRecCount = $nStartRec - 1;
if (!$rs->EOF) {
	$rs->MoveFirst();
	if (!$usuarioslevels->SelectLimit) $rs->Move($nStartRec - 1); // Move to first record directly
}
$RowCnt = 0;
while (!$rs->EOF && $nRecCount < $nStopRec) {
	$nRecCount++;
	if (intval($nRecCount) >= intval($nStartRec)) {
		$RowCnt++;

	// Init row class and style
	$usuarioslevels->CssClass = "ewTableRow";
	$usuarioslevels->CssStyle = "";

	// Init row event
	$usuarioslevels->RowClientEvents = "onmouseover='ew_MouseOver(this);' onmouseout='ew_MouseOut(this);' onclick='ew_Click(this);'";

	// Display alternate color for rows
	if ($RowCnt % 2 == 0) {
		$usuarioslevels->CssClass = "ewTableAltRow";
	}
	LoadRowValues($rs); // Load row values
	$usuarioslevels->RowType = EW_ROWTYPE_VIEW; // Render view
	RenderRow();
?>
	<!-- Table body -->
	<tr<?php echo $usuarioslevels->DisplayAttributes() ?>>
		<!-- UserLevelID -->
		<td<?php echo $usuarioslevels->UserLevelID->CellAttributes() ?>>
<div<?php echo $usuarioslevels->UserLevelID->ViewAttributes() ?>><?php echo $usuarioslevels->UserLevelID->ViewValue ?></div>
</td>
		<!-- UserLevelName -->
		<td<?php echo $usuarioslevels->UserLevelName->CellAttributes() ?>>
<div<?php echo $usuarioslevels->UserLevelName->ViewAttributes() ?>><?php echo $usuarioslevels->UserLevelName->ViewValue ?></div>
</td>
<?php if ($usuarioslevels->Export == "") { ?>
<?php if ($Security->CanView()) { ?>
<td nowrap width="1" style="padding:0"><span class="phpmaker"><?php if ($usuarioslevels->UserLevelID->CurrentValue <= 0) { ?>-<?php } else { ?>
<div class="button-view"><a href="<?php echo $usuarioslevels->ViewUrl() ?>">Vista</a></div>
<?php } ?></span></td>
<?php } ?>
<?php if ($Security->CanEdit()) { ?>
<td nowrap width="1" style="padding:0"><span class="phpmaker"><?php if ($usuarioslevels->UserLevelID->CurrentValue <= 0) { ?>-<?php } else { ?>
<div class="button-edit"><a href="<?php echo $usuarioslevels->EditUrl() ?>">Editar</a></div>
<?php } ?></span></td>
<?php } ?>
<?php if ($Security->CanDelete()) { ?>
<td nowrap width="1" style="padding:0"><span class="phpmaker"><?php if ($usuarioslevels->UserLevelID->CurrentValue <= 0) { ?>-<?php } else { ?>
<div class="button-delete"><a href="<?php echo $usuarioslevels->DeleteUrl() ?>">Borrar</a></div>
<?php } ?></span></td>
<?php } ?>
<td nowrap width="1" style="padding:0"><span class="phpmaker"><?php if ($usuarioslevels->UserLevelID->CurrentValue < 0) { ?>-<?php } else { ?>
<div class="button-permission"><a href="userpriv.php?UserLevelID=<?php echo $usuarioslevels->UserLevelID->CurrentValue ?>">Permiso</a></div>
<?php } ?></span></td>
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
<?php if ($usuarioslevels->Export == "") { ?>
<form action="usuarioslevelslist.php" name="ewpagerform" id="ewpagerform">
<table border="0" cellspacing="0" cellpadding="0" align="center" id="pagertable">
	<tr>
		<td nowrap align="center">
<span class="phpmaker">
<?php if (!isset($Pager)) $Pager = new cNumericPager($nStartRec, $nDisplayRecs, $nTotalRecs, $nRecRange) ?>
<?php if ($Pager->RecordCount > 0) { ?>
	<?php if ($Pager->FirstButton->Enabled) { ?>
	<a href="usuarioslevelslist.php?start=<?php echo $Pager->FirstButton->Start ?>"><b>Primero</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->PrevButton->Enabled) { ?>
	<a href="usuarioslevelslist.php?start=<?php echo $Pager->PrevButton->Start ?>"><b>Anterior</b></a>&nbsp;
	<?php } ?>
	<?php foreach ($Pager->Items as $PagerItem) { ?>
		<?php if ($PagerItem->Enabled) { ?><a href="usuarioslevelslist.php?start=<?php echo $PagerItem->Start ?>"><?php } ?><b><?php echo $PagerItem->Text ?></b><?php if ($PagerItem->Enabled) { ?></a><?php } ?>&nbsp;
	<?php } ?>
	<?php if ($Pager->NextButton->Enabled) { ?>
	<a href="usuarioslevelslist.php?start=<?php echo $Pager->NextButton->Start ?>"><b>Próximo</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->LastButton->Enabled) { ?>
	<a href="usuarioslevelslist.php?start=<?php echo $Pager->LastButton->Start ?>"><b>Último</b></a>&nbsp;
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
<?php if ($usuarioslevels->Export == "") { ?>
<?php } ?>
<?php if ($usuarioslevels->Export == "") { ?>
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
	$sql .= "`UserLevelName` LIKE '%" . $sKeyword . "%' OR ";
	if (substr($sql, -4) == " OR ") $sql = substr($sql, 0, strlen($sql)-4);
	return $sql;
}

// Return Basic Search Where based on search keyword and type
function BasicSearchWhere() {
	global $Security, $usuarioslevels;
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
		$usuarioslevels->setBasicSearchKeyword($sSearchKeyword);
		$usuarioslevels->setBasicSearchType($sSearchType);
	}
	return $sSearchStr;
}

// Clear all search parameters
function ResetSearchParms() {

	// Clear search where
	global $usuarioslevels;
	$sSrchWhere = "";
	$usuarioslevels->setSearchWhere($sSrchWhere);

	// Clear basic search parameters
	ResetBasicSearchParms();
}

// Clear all basic search parameters
function ResetBasicSearchParms() {

	// Clear basic search parameters
	global $usuarioslevels;
	$usuarioslevels->setBasicSearchKeyword("");
	$usuarioslevels->setBasicSearchType("");
}

// Restore all search parameters
function RestoreSearchParms() {
	global $sSrchWhere, $usuarioslevels;
	$sSrchWhere = $usuarioslevels->getSearchWhere();
}

// Set up Sort parameters based on Sort Links clicked
function SetUpSortOrder() {
	global $usuarioslevels;

	// Check for an Order parameter
	if (@$_GET["order"] <> "") {
		$usuarioslevels->CurrentOrder = ew_StripSlashes(@$_GET["order"]);
		$usuarioslevels->CurrentOrderType = @$_GET["ordertype"];

		// Field UserLevelID
		$usuarioslevels->UpdateSort($usuarioslevels->UserLevelID);

		// Field UserLevelName
		$usuarioslevels->UpdateSort($usuarioslevels->UserLevelName);
		$usuarioslevels->setStartRecordNumber(1); // Reset start position
	}
	$sOrderBy = $usuarioslevels->getSessionOrderBy(); // Get order by from Session
	if ($sOrderBy == "") {
		if ($usuarioslevels->SqlOrderBy() <> "") {
			$sOrderBy = $usuarioslevels->SqlOrderBy();
			$usuarioslevels->setSessionOrderBy($sOrderBy);
			$usuarioslevels->UserLevelID->setSort("ASC");
		}
	}
}

// Reset command based on querystring parameter cmd=
// - RESET: reset search parameters
// - RESETALL: reset search & master/detail parameters
// - RESETSORT: reset sort parameters
function ResetCmd() {
	global $sDbMasterFilter, $sDbDetailFilter, $nStartRec, $sOrderBy;
	global $usuarioslevels;

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
			$usuarioslevels->setSessionOrderBy($sOrderBy);
			$usuarioslevels->UserLevelID->setSort("");
			$usuarioslevels->UserLevelName->setSort("");
		}

		// Reset start position
		$nStartRec = 1;
		$usuarioslevels->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Set up Starting Record parameters based on Pager Navigation
function SetUpStartRec() {
	global $nDisplayRecs, $nStartRec, $nTotalRecs, $nPageNo, $usuarioslevels;
	if ($nDisplayRecs == 0) return;

	// Check for a START parameter
	if (@$_GET[EW_TABLE_START_REC] <> "") {
		$nStartRec = $_GET[EW_TABLE_START_REC];
		$usuarioslevels->setStartRecordNumber($nStartRec);
	} elseif (@$_GET[EW_TABLE_PAGE_NO] <> "") {
		$nPageNo = $_GET[EW_TABLE_PAGE_NO];
		if (is_numeric($nPageNo)) {
			$nStartRec = ($nPageNo-1)*$nDisplayRecs+1;
			if ($nStartRec <= 0) {
				$nStartRec = 1;
			} elseif ($nStartRec >= intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1) {
				$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1;
			}
			$usuarioslevels->setStartRecordNumber($nStartRec);
		} else {
			$nStartRec = $usuarioslevels->getStartRecordNumber();
		}
	} else {
		$nStartRec = $usuarioslevels->getStartRecordNumber();
	}

	// Check if correct start record counter
	if (!is_numeric($nStartRec) || $nStartRec == "") { // Avoid invalid start record counter
		$nStartRec = 1; // Reset start record counter
		$usuarioslevels->setStartRecordNumber($nStartRec);
	} elseif (intval($nStartRec) > intval($nTotalRecs)) { // Avoid starting record > total records
		$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to last page first record
		$usuarioslevels->setStartRecordNumber($nStartRec);
	} elseif (($nStartRec-1) % $nDisplayRecs <> 0) {
		$nStartRec = intval(($nStartRec-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to page boundary
		$usuarioslevels->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Load recordset
function LoadRecordset($offset = -1, $rowcnt = -1) {
	global $conn, $usuarioslevels;

	// Call Recordset Selecting event
	$usuarioslevels->Recordset_Selecting($usuarioslevels->CurrentFilter);

	// Load list page sql
	$sSql = $usuarioslevels->SelectSQL();
	if ($offset > -1 && $rowcnt > -1) $sSql .= " LIMIT $offset, $rowcnt";

	// Load recordset
	$conn->raiseErrorFn = 'ew_ErrorFn';	
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';

	// Call Recordset Selected event
	$usuarioslevels->Recordset_Selected($rs);
	return $rs;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $usuarioslevels;
	$sFilter = $usuarioslevels->SqlKeyFilter();
	if (!is_numeric($usuarioslevels->UserLevelID->CurrentValue)) {
		return FALSE; // Invalid key, exit
	}
	$sFilter = str_replace("@UserLevelID@", ew_AdjustSql($usuarioslevels->UserLevelID->CurrentValue), $sFilter); // Replace key value

	// Call Row Selecting event
	$usuarioslevels->Row_Selecting($sFilter);

	// Load sql based on filter
	$usuarioslevels->CurrentFilter = $sFilter;
	$sSql = $usuarioslevels->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$usuarioslevels->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $usuarioslevels;
	$usuarioslevels->UserLevelID->setDbValue($rs->fields('UserLevelID'));
	if (is_null($usuarioslevels->UserLevelID->CurrentValue)) {
		$usuarioslevels->UserLevelID->CurrentValue = 0;
	} else {
		$usuarioslevels->UserLevelID->CurrentValue = intval($usuarioslevels->UserLevelID->CurrentValue);
	}
	$usuarioslevels->UserLevelName->setDbValue($rs->fields('UserLevelName'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $usuarioslevels;

	// Call Row Rendering event
	$usuarioslevels->Row_Rendering();

	// Common render codes for all row types
	// UserLevelID

	$usuarioslevels->UserLevelID->CellCssStyle = "";
	$usuarioslevels->UserLevelID->CellCssClass = "";

	// UserLevelName
	$usuarioslevels->UserLevelName->CellCssStyle = "";
	$usuarioslevels->UserLevelName->CellCssClass = "";
	if ($usuarioslevels->RowType == EW_ROWTYPE_VIEW) { // View row

		// UserLevelID
		$usuarioslevels->UserLevelID->ViewValue = $usuarioslevels->UserLevelID->CurrentValue;
		$usuarioslevels->UserLevelID->CssStyle = "";
		$usuarioslevels->UserLevelID->CssClass = "";
		$usuarioslevels->UserLevelID->ViewCustomAttributes = "";

		// UserLevelName
		$usuarioslevels->UserLevelName->ViewValue = $usuarioslevels->UserLevelName->CurrentValue;
		$usuarioslevels->UserLevelName->CssStyle = "";
		$usuarioslevels->UserLevelName->CssClass = "";
		$usuarioslevels->UserLevelName->ViewCustomAttributes = "";

		// UserLevelID
		$usuarioslevels->UserLevelID->HrefValue = "";

		// UserLevelName
		$usuarioslevels->UserLevelName->HrefValue = "";
	} elseif ($usuarioslevels->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($usuarioslevels->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($usuarioslevels->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$usuarioslevels->Row_Rendered();
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
