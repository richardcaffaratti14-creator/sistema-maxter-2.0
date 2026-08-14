<?php
define("EW_PAGE_ID", "list", TRUE); // Page ID
define("EW_TABLE_NAME", 'usuarios', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "usuariosinfo.php" ?>
<?php include "userfn50.php" ?>
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
$Security->LoadCurrentUserLevel('usuarios');
if (!$Security->IsLoggedIn()) {
	$Security->SaveLastUrl();
	Page_Terminate("login.php");
}
if (!$Security->CanList()) {
	$Security->SaveLastUrl();
	Page_Terminate("login.php");
}
if ($Security->IsLoggedIn() && $Security->CurrentUserID() == "") {
	$_SESSION[EW_SESSION_MESSAGE] = "Usted no tiene permisos para visualizar esta página";
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
$usuarios->Export = @$_GET["export"]; // Get export parameter
$sExport = $usuarios->Export; // Get export parameter, used in header
$sExportFile = $usuarios->TableVar; // Get export file, used in header
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
	$usuarios->setSearchWhere($sSrchWhere); // Save to Session
	$nStartRec = 1; // Reset start record counter
	$usuarios->setStartRecordNumber($nStartRec);
} else {
	RestoreSearchParms();
}

// Build filter
$sFilter = "";
if (!$Security->CanList()) {
	$sFilter = "(0=1)"; // Filter all records
}
if ($Security->CurrentUserID() <> "" && !$Security->IsAdmin()) { // Non system admin
	$sFilter = $usuarios->AddUserIDFilter($sFilter, $Security->CurrentUserID()); // Add User ID filter
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
$usuarios->setSessionWhere($sFilter);
$usuarios->CurrentFilter = "";

// Set Up Sorting Order
SetUpSortOrder();

// Set Return Url
$usuarios->setReturnUrl("usuarioslist.php");
?>
<?php include "header.php" ?>
<?php if ($usuarios->Export == "") { ?>
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
<?php if ($usuarios->Export == "") { ?>
<?php } ?>
<?php

// Load recordset
$bExportAll = (defined("EW_EXPORT_ALL") && $usuarios->Export <> "");
$bSelectLimit = ($usuarios->Export == "" && $usuarios->SelectLimit);
if (!$bSelectLimit) $rs = LoadRecordset();
$nTotalRecs = ($bSelectLimit) ? $usuarios->SelectRecordCount() : $rs->RecordCount();
$nStartRec = 1;
if ($nDisplayRecs <= 0) $nDisplayRecs = $nTotalRecs; // Display all records
if (!$bExportAll) SetUpStartRec(); // Set up start record position
if ($bSelectLimit) $rs = LoadRecordset($nStartRec-1, $nDisplayRecs);
?>
<?php if ($usuarios->Export == "") { ?>
<?php if ($Security->CanSearch()) { ?>
<form name="fusuarioslistsrch" id="fusuarioslistsrch" action="usuarioslist.php" >
<table class="ewBasicSearch" align="center">
	<tr>
		<td><span class="phpmaker">
			<input type="text" name="<?php echo EW_TABLE_BASIC_SEARCH ?>" id="<?php echo EW_TABLE_BASIC_SEARCH ?>" size="20" value="<?php echo $usuarios->getBasicSearchKeyword() ?>">
			<input type="Submit" name="Submit" id="Submit" value="Buscar (*)">&nbsp;
			<a href="usuarioslist.php?cmd=reset">Mostrar todo</a>&nbsp;
		</span></td>
	</tr>
	<tr>
	<td><span class="phpmaker"><input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="" <?php if ($usuarios->getBasicSearchType() == "") { ?>checked<?php } ?>>Frase exacta&nbsp;&nbsp;<input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="AND" <?php if ($usuarios->getBasicSearchType() == "AND") { ?>checked<?php } ?>>Todas las palabras&nbsp;&nbsp;<input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="OR" <?php if ($usuarios->getBasicSearchType() == "OR") { ?>checked<?php } ?>>Cualquier palabra</span></td>
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
<form method="post" name="fusuarioslist" id="fusuarioslist">
<?php if ($usuarios->Export == "") { ?>
<table class="subheader" align="center">
	<tr><td><span class="phpmaker">
<?php if ($Security->CanAdd()) { ?>
<div class="button-add"><a href="usuariosadd.php">Agregar</a></div>
<?php } ?>
<div class="tablername" style="margin-top:7px">Usuarios</div>
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
if ($Security->AllowList('usuarios_ingresos')) {
	$OptionCnt++; // detail
}
?>
	<!-- Table header -->
	<tr class="ewTableHeader">
		<td valign="top">
<?php if ($usuarios->Export <> "") { ?>
Nombre
<?php } else { ?>
	<a href="usuarioslist.php?order=<?php echo urlencode('Nombre') ?>&ordertype=<?php echo $usuarios->Nombre->ReverseSort() ?>">Nombre&nbsp;(*)<?php if ($usuarios->Nombre->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($usuarios->Nombre->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($usuarios->Export <> "") { ?>
Nivel
<?php } else { ?>
	<a href="usuarioslist.php?order=<?php echo urlencode('idLevel') ?>&ordertype=<?php echo $usuarios->idLevel->ReverseSort() ?>">Nivel<?php if ($usuarios->idLevel->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($usuarios->idLevel->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($usuarios->Export <> "") { ?>
Último Acceso
<?php } else { ?>
	<a href="usuarioslist.php?order=<?php echo urlencode('UltimoAcceso') ?>&ordertype=<?php echo $usuarios->UltimoAcceso->ReverseSort() ?>">Ultimo Acceso<?php if ($usuarios->UltimoAcceso->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($usuarios->UltimoAcceso->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($usuarios->Export <> "") { ?>
IP
<?php } else { ?>
	<a href="usuarioslist.php?order=<?php echo urlencode('IP') ?>&ordertype=<?php echo $usuarios->IP->ReverseSort() ?>">IP&nbsp;(*)<?php if ($usuarios->IP->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($usuarios->IP->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
<?php if ($usuarios->Export == "") { ?>
<?php if ($Security->CanView()) { ?>
<td nowrap>&nbsp;</td>
<?php } ?>
<?php if ($Security->CanEdit()) { ?>
<td nowrap width="1" style="padding:0">&nbsp;</td>
<?php } ?>
<?php if ($Security->CanDelete()) { ?>
<td nowrap>&nbsp;</td>
<?php } ?>
<?php if ($Security->AllowList('usuarios_ingresos')) { ?>
<td nowrap>&nbsp;</td>
<?php } ?>
<?php } ?>
	</tr>
<?php
if (defined("EW_EXPORT_ALL") && $usuarios->Export <> "") {
	$nStopRec = $nTotalRecs;
} else {
	$nStopRec = $nStartRec + $nDisplayRecs - 1; // Set the last record to display
}
$nRecCount = $nStartRec - 1;
if (!$rs->EOF) {
	$rs->MoveFirst();
	if (!$usuarios->SelectLimit) $rs->Move($nStartRec - 1); // Move to first record directly
}
$RowCnt = 0;
while (!$rs->EOF && $nRecCount < $nStopRec) {
	$nRecCount++;
	if (intval($nRecCount) >= intval($nStartRec)) {
		$RowCnt++;

	// Init row class and style
	$usuarios->CssClass = "ewTableRow";
	$usuarios->CssStyle = "";

	// Init row event
	$usuarios->RowClientEvents = "onmouseover='ew_MouseOver(this);' onmouseout='ew_MouseOut(this);' onclick='ew_Click(this);'";

	// Display alternate color for rows
	if ($RowCnt % 2 == 0) {
		$usuarios->CssClass = "ewTableAltRow";
	}
	LoadRowValues($rs); // Load row values
	$usuarios->RowType = EW_ROWTYPE_VIEW; // Render view
	RenderRow();
?>
	<!-- Table body -->
	<tr<?php echo $usuarios->DisplayAttributes() ?>>
		<!-- Nombre -->
		<td<?php echo $usuarios->Nombre->CellAttributes() ?>>
<div<?php echo $usuarios->Nombre->ViewAttributes() ?>><?php echo $usuarios->Nombre->ViewValue ?></div>
</td>
		<!-- idLevel -->
		<td<?php echo $usuarios->idLevel->CellAttributes() ?>>
<div<?php echo $usuarios->idLevel->ViewAttributes() ?>><?php echo $usuarios->idLevel->ViewValue ?></div>
</td>
		<!-- UltimoAcceso -->
		<td<?php echo $usuarios->UltimoAcceso->CellAttributes() ?>>
<div<?php echo $usuarios->UltimoAcceso->ViewAttributes() ?>><?php echo $usuarios->UltimoAcceso->ViewValue ?></div>
</td>
		<!-- IP -->
		<td<?php echo $usuarios->IP->CellAttributes() ?>>
<div<?php echo $usuarios->IP->ViewAttributes() ?>><?php echo $usuarios->IP->ViewValue ?></div>
</td>
<?php if ($usuarios->Export == "") { ?>
<?php if ($Security->CanView()) { ?>
<td nowrap width="1" style="padding:0"><span class="phpmaker"><?php if (ShowOptionLink()) { ?>
<div class="button-view"><a href="<?php echo $usuarios->ViewUrl() ?>">Vista</a></div>
<?php } ?></span></td>
<?php } ?>
<?php if ($Security->CanEdit()) { ?>
<td nowrap width="1" style="padding:0"><span class="phpmaker"><?php if (ShowOptionLink()) { ?>
<div class="button-edit"><a href="<?php echo $usuarios->EditUrl() ?>">Editar</a></div>
<?php } ?></span></td>
<?php } ?>
<?php if ($Security->CanDelete()) { ?>
<td nowrap width="1" style="padding:0"><span class="phpmaker"><?php if (ShowOptionLink()) { ?>
<div class="button-delete"><a href="<?php echo $usuarios->DeleteUrl() ?>">Borrar</a></div>
<?php } ?></span></td>
<?php } ?>
<?php if ($Security->AllowList('usuarios_ingresos')) { ?>
<td nowrap><span class="phpmaker"><?php if (ShowOptionLink()) { ?>
<a href="usuarios_ingresoslist.php?<?php echo EW_TABLE_SHOW_MASTER ?>=usuarios&usuario=<?php echo urlencode(strval($usuarios->usuario->CurrentValue)) ?>">Logins...</a>
<?php } ?></span></td>
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
<?php if ($usuarios->Export == "") { ?>
<form action="usuarioslist.php" name="ewpagerform" id="ewpagerform">
<table border="0" cellspacing="0" cellpadding="0" align="center" id="pagertable">
	<tr>
		<td nowrap align="center">
<span class="phpmaker">
<?php if (!isset($Pager)) $Pager = new cNumericPager($nStartRec, $nDisplayRecs, $nTotalRecs, $nRecRange) ?>
<?php if ($Pager->RecordCount > 0) { ?>
	<?php if ($Pager->FirstButton->Enabled) { ?>
	<a href="usuarioslist.php?start=<?php echo $Pager->FirstButton->Start ?>"><b>Primero</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->PrevButton->Enabled) { ?>
	<a href="usuarioslist.php?start=<?php echo $Pager->PrevButton->Start ?>"><b>Anterior</b></a>&nbsp;
	<?php } ?>
	<?php foreach ($Pager->Items as $PagerItem) { ?>
		<?php if ($PagerItem->Enabled) { ?><a href="usuarioslist.php?start=<?php echo $PagerItem->Start ?>"><?php } ?><b><?php echo $PagerItem->Text ?></b><?php if ($PagerItem->Enabled) { ?></a><?php } ?>&nbsp;
	<?php } ?>
	<?php if ($Pager->NextButton->Enabled) { ?>
	<a href="usuarioslist.php?start=<?php echo $Pager->NextButton->Start ?>"><b>Próximo</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->LastButton->Enabled) { ?>
	<a href="usuarioslist.php?start=<?php echo $Pager->LastButton->Start ?>"><b>Ultimo</b></a>&nbsp;
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
<?php if ($usuarios->Export == "") { ?>
<?php } ?>
<?php if ($usuarios->Export == "") { ?>
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
	$sql .= "`usuario` LIKE '%" . $sKeyword . "%' OR ";
	$sql .= "`Clave` LIKE '%" . $sKeyword . "%' OR ";
	$sql .= "`Nombre` LIKE '%" . $sKeyword . "%' OR ";
	$sql .= "`EMail` LIKE '%" . $sKeyword . "%' OR ";
	$sql .= "`IP` LIKE '%" . $sKeyword . "%' OR ";
	if (substr($sql, -4) == " OR ") $sql = substr($sql, 0, strlen($sql)-4);
	return $sql;
}

// Return Basic Search Where based on search keyword and type
function BasicSearchWhere() {
	global $Security, $usuarios;
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
		$usuarios->setBasicSearchKeyword($sSearchKeyword);
		$usuarios->setBasicSearchType($sSearchType);
	}
	return $sSearchStr;
}

// Clear all search parameters
function ResetSearchParms() {

	// Clear search where
	global $usuarios;
	$sSrchWhere = "";
	$usuarios->setSearchWhere($sSrchWhere);

	// Clear basic search parameters
	ResetBasicSearchParms();
}

// Clear all basic search parameters
function ResetBasicSearchParms() {

	// Clear basic search parameters
	global $usuarios;
	$usuarios->setBasicSearchKeyword("");
	$usuarios->setBasicSearchType("");
}

// Restore all search parameters
function RestoreSearchParms() {
	global $sSrchWhere, $usuarios;
	$sSrchWhere = $usuarios->getSearchWhere();
}

// Set up Sort parameters based on Sort Links clicked
function SetUpSortOrder() {
	global $usuarios;

	// Check for an Order parameter
	if (@$_GET["order"] <> "") {
		$usuarios->CurrentOrder = ew_StripSlashes(@$_GET["order"]);
		$usuarios->CurrentOrderType = @$_GET["ordertype"];

		// Field Nombre
		$usuarios->UpdateSort($usuarios->Nombre);

		// Field idLevel
		$usuarios->UpdateSort($usuarios->idLevel);

		// Field UltimoAcceso
		$usuarios->UpdateSort($usuarios->UltimoAcceso);

		// Field IP
		$usuarios->UpdateSort($usuarios->IP);
		$usuarios->setStartRecordNumber(1); // Reset start position
	}
	$sOrderBy = $usuarios->getSessionOrderBy(); // Get order by from Session
	if ($sOrderBy == "") {
		if ($usuarios->SqlOrderBy() <> "") {
			$sOrderBy = $usuarios->SqlOrderBy();
			$usuarios->setSessionOrderBy($sOrderBy);
			$usuarios->Nombre->setSort("ASC");
		}
	}
}

// Reset command based on querystring parameter cmd=
// - RESET: reset search parameters
// - RESETALL: reset search & master/detail parameters
// - RESETSORT: reset sort parameters
function ResetCmd() {
	global $sDbMasterFilter, $sDbDetailFilter, $nStartRec, $sOrderBy;
	global $usuarios;

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
			$usuarios->setSessionOrderBy($sOrderBy);
			$usuarios->Nombre->setSort("");
			$usuarios->idLevel->setSort("");
			$usuarios->UltimoAcceso->setSort("");
			$usuarios->IP->setSort("");
		}

		// Reset start position
		$nStartRec = 1;
		$usuarios->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Set up Starting Record parameters based on Pager Navigation
function SetUpStartRec() {
	global $nDisplayRecs, $nStartRec, $nTotalRecs, $nPageNo, $usuarios;
	if ($nDisplayRecs == 0) return;

	// Check for a START parameter
	if (@$_GET[EW_TABLE_START_REC] <> "") {
		$nStartRec = $_GET[EW_TABLE_START_REC];
		$usuarios->setStartRecordNumber($nStartRec);
	} elseif (@$_GET[EW_TABLE_PAGE_NO] <> "") {
		$nPageNo = $_GET[EW_TABLE_PAGE_NO];
		if (is_numeric($nPageNo)) {
			$nStartRec = ($nPageNo-1)*$nDisplayRecs+1;
			if ($nStartRec <= 0) {
				$nStartRec = 1;
			} elseif ($nStartRec >= intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1) {
				$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1;
			}
			$usuarios->setStartRecordNumber($nStartRec);
		} else {
			$nStartRec = $usuarios->getStartRecordNumber();
		}
	} else {
		$nStartRec = $usuarios->getStartRecordNumber();
	}

	// Check if correct start record counter
	if (!is_numeric($nStartRec) || $nStartRec == "") { // Avoid invalid start record counter
		$nStartRec = 1; // Reset start record counter
		$usuarios->setStartRecordNumber($nStartRec);
	} elseif (intval($nStartRec) > intval($nTotalRecs)) { // Avoid starting record > total records
		$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to last page first record
		$usuarios->setStartRecordNumber($nStartRec);
	} elseif (($nStartRec-1) % $nDisplayRecs <> 0) {
		$nStartRec = intval(($nStartRec-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to page boundary
		$usuarios->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Load recordset
function LoadRecordset($offset = -1, $rowcnt = -1) {
	global $conn, $usuarios;

	// Call Recordset Selecting event
	$usuarios->Recordset_Selecting($usuarios->CurrentFilter);

	// Load list page sql
	$sSql = $usuarios->SelectSQL();
	if ($offset > -1 && $rowcnt > -1) $sSql .= " LIMIT $offset, $rowcnt";

	// Load recordset
	$conn->raiseErrorFn = 'ew_ErrorFn';	
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';

	// Call Recordset Selected event
	$usuarios->Recordset_Selected($rs);
	return $rs;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $usuarios;
	$sFilter = $usuarios->SqlKeyFilter();
	if (!is_numeric($usuarios->idUsuario->CurrentValue)) {
		return FALSE; // Invalid key, exit
	}
	$sFilter = str_replace("@idUsuario@", ew_AdjustSql($usuarios->idUsuario->CurrentValue), $sFilter); // Replace key value
	if ($Security->CurrentUserID() <> "" && !$Security->IsAdmin()) { // Non system admin
		$sFilter = $usuarios->AddUserIDFilter($sFilter, $Security->CurrentUserID()); // Add User ID filter
	}

	// Call Row Selecting event
	$usuarios->Row_Selecting($sFilter);

	// Load sql based on filter
	$usuarios->CurrentFilter = $sFilter;
	$sSql = $usuarios->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$usuarios->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $usuarios;
	$usuarios->idUsuario->setDbValue($rs->fields('idUsuario'));
	$usuarios->usuario->setDbValue($rs->fields('usuario'));
	$usuarios->Clave->setDbValue($rs->fields('Clave'));
	$usuarios->Nombre->setDbValue($rs->fields('Nombre'));
	$usuarios->EMail->setDbValue($rs->fields('EMail'));
	$usuarios->idLevel->setDbValue($rs->fields('idLevel'));
	$usuarios->UltimoAcceso->setDbValue($rs->fields('UltimoAcceso'));
	$usuarios->IP->setDbValue($rs->fields('IP'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $usuarios;

	// Call Row Rendering event
	$usuarios->Row_Rendering();

	// Common render codes for all row types
	// Nombre

	$usuarios->Nombre->CellCssStyle = "";
	$usuarios->Nombre->CellCssClass = "";

	// idLevel
	$usuarios->idLevel->CellCssStyle = "";
	$usuarios->idLevel->CellCssClass = "";

	// UltimoAcceso
	$usuarios->UltimoAcceso->CellCssStyle = "";
	$usuarios->UltimoAcceso->CellCssClass = "";

	// IP
	$usuarios->IP->CellCssStyle = "";
	$usuarios->IP->CellCssClass = "";
	if ($usuarios->RowType == EW_ROWTYPE_VIEW) { // View row

		// Nombre
		$usuarios->Nombre->ViewValue = $usuarios->Nombre->CurrentValue;
		$usuarios->Nombre->CssStyle = "";
		$usuarios->Nombre->CssClass = "";
		$usuarios->Nombre->ViewCustomAttributes = "";

		// idLevel
		if ($Security->CanAdmin()) { // System admin
		if (!empty($usuarios->idLevel->CurrentValue)) {
			$sSqlWrk = "SELECT `UserLevelName` FROM `usuarioslevels` WHERE `UserLevelID` = " . ew_AdjustSql($usuarios->idLevel->CurrentValue) . "";
			$rswrk = $conn->Execute($sSqlWrk);
			if ($rswrk) {
				if (!$rswrk->EOF) {
					$usuarios->idLevel->ViewValue = $rswrk->fields('UserLevelName');
				}
				$rswrk->Close();
			} else {
				$usuarios->idLevel->ViewValue = $usuarios->idLevel->CurrentValue;
			}
		} else {
			$usuarios->idLevel->ViewValue = NULL;
		}
		} else {
			$usuarios->idLevel->ViewValue = "********";
		}
		$usuarios->idLevel->CssStyle = "";
		$usuarios->idLevel->CssClass = "";
		$usuarios->idLevel->ViewCustomAttributes = "";

		// UltimoAcceso
		$usuarios->UltimoAcceso->ViewValue = $usuarios->UltimoAcceso->CurrentValue;
		$usuarios->UltimoAcceso->ViewValue = ew_FormatDateTime($usuarios->UltimoAcceso->ViewValue, 7);
		$usuarios->UltimoAcceso->CssStyle = "";
		$usuarios->UltimoAcceso->CssClass = "";
		$usuarios->UltimoAcceso->ViewCustomAttributes = "";

		// IP
		$usuarios->IP->ViewValue = $usuarios->IP->CurrentValue;
		$usuarios->IP->CssStyle = "";
		$usuarios->IP->CssClass = "";
		$usuarios->IP->ViewCustomAttributes = "";

		// Nombre
		$usuarios->Nombre->HrefValue = "";

		// idLevel
		$usuarios->idLevel->HrefValue = "";

		// UltimoAcceso
		$usuarios->UltimoAcceso->HrefValue = "";

		// IP
		$usuarios->IP->HrefValue = "";
	} elseif ($usuarios->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($usuarios->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($usuarios->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$usuarios->Row_Rendered();
}
?>
<?php

// Show link optionally based on User ID
function ShowOptionLink() {
	global $Security, $usuarios;
	if ($Security->IsLoggedIn()) {
		if (!$Security->IsAdmin()) {
			return $Security->IsValidUserID($usuarios->idUsuario->CurrentValue);
		}
	}
	return TRUE;
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
