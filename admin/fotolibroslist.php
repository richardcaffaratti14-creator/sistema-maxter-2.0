<?php
define("EW_PAGE_ID", "list", TRUE); // Page ID
define("EW_TABLE_NAME", 'fotolibros', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "fotolibrosinfo.php" ?>
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
$Security->LoadCurrentUserLevel('fotolibros');
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
$fotolibros->Export = @$_GET["export"]; // Get export parameter
$sExport = $fotolibros->Export; // Get export parameter, used in header
$sExportFile = $fotolibros->TableVar; // Get export file, used in header
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
	$fotolibros->setSearchWhere($sSrchWhere); // Save to Session
	$nStartRec = 1; // Reset start record counter
	$fotolibros->setStartRecordNumber($nStartRec);
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

if (!$Security->IsAdmin()) {
	//Usuarios no admin no puedan ver otros eventos
	if ($sFilter <> "") $sFilter .= " AND ";
	$_evento = getSiteInfo('evento');
	$sFilter .= "(Evento = '" . mysql_real_escape_string($_evento) . "')";
}


// Set up filter in Session
$fotolibros->setSessionWhere($sFilter);
$fotolibros->CurrentFilter = "";

// Set Up Sorting Order
SetUpSortOrder();

// Set Return Url
$fotolibros->setReturnUrl("fotolibroslist.php");
?>
<?php include "header.php" ?>
<?php if ($fotolibros->Export == "") { ?>
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
<?php if ($fotolibros->Export == "") { ?>
<?php } ?>
<?php

// Load recordset
$bExportAll = (defined("EW_EXPORT_ALL") && $fotolibros->Export <> "");
$bSelectLimit = ($fotolibros->Export == "" && $fotolibros->SelectLimit);
if (!$bSelectLimit) $rs = LoadRecordset();
$nTotalRecs = ($bSelectLimit) ? $fotolibros->SelectRecordCount() : $rs->RecordCount();
$nStartRec = 1;
if ($nDisplayRecs <= 0) $nDisplayRecs = $nTotalRecs; // Display all records
if (!$bExportAll) SetUpStartRec(); // Set up start record position
if ($bSelectLimit) $rs = LoadRecordset($nStartRec-1, $nDisplayRecs);
?>
<?php if ($fotolibros->Export == "") { ?>
<?php if ($Security->CanSearch()) { ?>
<form name="ffotolibroslistsrch" id="ffotolibroslistsrch" action="fotolibroslist.php" >
<table class="ewBasicSearch" align="center">
	<tr>
		<td><span class="phpmaker">
			<input type="text" name="<?php echo EW_TABLE_BASIC_SEARCH ?>" id="<?php echo EW_TABLE_BASIC_SEARCH ?>" size="20" value="<?php echo $fotolibros->getBasicSearchKeyword() ?>">
			<input type="Submit" name="Submit" id="Submit" value="Buscar (*)">&nbsp;
			<a href="fotolibroslist.php?cmd=reset">Mostrar todo</a>&nbsp;
		</span></td>
	</tr>
	<tr>
	<td><span class="phpmaker"><input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="" <?php if ($fotolibros->getBasicSearchType() == "") { ?>checked<?php } ?>>Frase exacta&nbsp;&nbsp;<input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="AND" <?php if ($fotolibros->getBasicSearchType() == "AND") { ?>checked<?php } ?>>Todas las palabras&nbsp;&nbsp;<input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="OR" <?php if ($fotolibros->getBasicSearchType() == "OR") { ?>checked<?php } ?>>Cualquier palabra</span></td>
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
<form method="post" name="ffotolibroslist" id="ffotolibroslist">
<?php if ($fotolibros->Export == "") { ?>
<table class="subheader" align="center">
	<tr><td><span class="phpmaker">
<div class="tablername" style="margin-top:7px">Fotolibros</div>
	</span></td></tr>
</table>
<?php } ?>
<?php if ($nTotalRecs > 0) { ?>
<div id="whiteback">
<table id="ewlistmain" class="ewTable" align="center">
<?php
	$OptionCnt = 0;
if ($Security->CanDelete()) {
	$OptionCnt++; // delete
}
?>
	<!-- Table header -->
	<tr class="ewTableHeader">
		<td valign="top">
<?php if ($fotolibros->Export <> "") { ?>
Nro.
<?php } else { ?>
	<a href="fotolibroslist.php?order=<?php echo urlencode('id') ?>&ordertype=<?php echo $fotolibros->id->ReverseSort() ?>">Nro&nbsp;(*)<?php if ($fotolibros->id->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($fotolibros->id->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($fotolibros->Export <> "") { ?>
Nombre
<?php } else { ?>
	<a href="fotolibroslist.php?order=<?php echo urlencode('nombre') ?>&ordertype=<?php echo $fotolibros->nombre->ReverseSort() ?>">Nombre&nbsp;(*)<?php if ($fotolibros->nombre->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($fotolibros->nombre->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($fotolibros->Export <> "") { ?>
Apellido
<?php } else { ?>
	<a href="fotolibroslist.php?order=<?php echo urlencode('apellido') ?>&ordertype=<?php echo $fotolibros->apellido->ReverseSort() ?>">Apellido&nbsp;(*)<?php if ($fotolibros->apellido->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($fotolibros->apellido->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($fotolibros->Export <> "") { ?>
Telefono
<?php } else { ?>
	<a href="fotolibroslist.php?order=<?php echo urlencode('telefono') ?>&ordertype=<?php echo $fotolibros->telefono->ReverseSort() ?>">Telefono&nbsp;(*)<?php if ($fotolibros->telefono->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($fotolibros->telefono->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($fotolibros->Export <> "") { ?>
Vendedor
<?php } else { ?>
	<a href="fotolibroslist.php?order=<?php echo urlencode('idVendedor') ?>&ordertype=<?php echo $fotolibros->idVendedor->ReverseSort() ?>">Vendedor<?php if ($fotolibros->idVendedor->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($fotolibros->idVendedor->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($fotolibros->Export <> "") { ?>
Seña
<?php } else { ?>
	<a href="fotolibroslist.php?order=<?php echo urlencode('sena') ?>&ordertype=<?php echo $fotolibros->sena->ReverseSort() ?>">Seña<?php if ($fotolibros->sena->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($fotolibros->sena->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($fotolibros->Export <> "") { ?>
Total
<?php } else { ?>
	<a href="fotolibroslist.php?order=<?php echo urlencode('total') ?>&ordertype=<?php echo $fotolibros->total->ReverseSort() ?>">Total<?php if ($fotolibros->total->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($fotolibros->total->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($fotolibros->Export <> "") { ?>
Evento
<?php } else { ?>
	<a href="fotolibroslist.php?order=<?php echo urlencode('evento') ?>&ordertype=<?php echo $fotolibros->evento->ReverseSort() ?>">Evento&nbsp;(*)<?php if ($fotolibros->evento->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($fotolibros->evento->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
<?php if ($fotolibros->Export == "") { ?>
<?php if ($Security->CanDelete()) { ?>
<td nowrap>&nbsp;</td>
<?php } ?>
<?php } ?>
	</tr>
<?php
if (defined("EW_EXPORT_ALL") && $fotolibros->Export <> "") {
	$nStopRec = $nTotalRecs;
} else {
	$nStopRec = $nStartRec + $nDisplayRecs - 1; // Set the last record to display
}
$nRecCount = $nStartRec - 1;
if (!$rs->EOF) {
	$rs->MoveFirst();
	if (!$fotolibros->SelectLimit) $rs->Move($nStartRec - 1); // Move to first record directly
}
$RowCnt = 0;
while (!$rs->EOF && $nRecCount < $nStopRec) {
	$nRecCount++;
	if (intval($nRecCount) >= intval($nStartRec)) {
		$RowCnt++;

	// Init row class and style
	$fotolibros->CssClass = "ewTableRow";
	$fotolibros->CssStyle = "";

	// Init row event
	$fotolibros->RowClientEvents = "onmouseover='ew_MouseOver(this);' onmouseout='ew_MouseOut(this);' onclick='ew_Click(this);'";

	// Display alternate color for rows
	if ($RowCnt % 2 == 0) {
		$fotolibros->CssClass = "ewTableAltRow";
	}
	LoadRowValues($rs); // Load row values
	$fotolibros->RowType = EW_ROWTYPE_VIEW; // Render view
	RenderRow();
?>
	<!-- Table body -->
	<tr<?php echo $fotolibros->DisplayAttributes() ?>>
		<!-- id -->
		<td<?php echo $fotolibros->id->CellAttributes() ?>>
<div style="white-space: nowrap">
<?php echo $fotolibros->id->ViewValue ?>
	<?
	$idped = ExecReturnFirstField("select id from pedidos where idFotolibro = " . $fotolibros->id->CurrentValue);
	if ($idped) {
		?>&nbsp;&nbsp;&nbsp;<a href="../ajax/pdf?id=<?php echo $idped ?>" target="_blank">pedido: <?php echo $idped ?></a><?
	}
	?>
</div>
</td>
		<!-- nombre -->
		<td<?php echo $fotolibros->nombre->CellAttributes() ?>>
<div<?php echo $fotolibros->nombre->ViewAttributes() ?>><?php echo $fotolibros->nombre->ViewValue ?></div>
</td>
		<!-- apellido -->
		<td<?php echo $fotolibros->apellido->CellAttributes() ?>>
<div<?php echo $fotolibros->apellido->ViewAttributes() ?>><?php echo $fotolibros->apellido->ViewValue ?></div>
</td>
		<!-- telefono -->
		<td<?php echo $fotolibros->telefono->CellAttributes() ?>>
<div<?php echo $fotolibros->telefono->ViewAttributes() ?>><?php echo $fotolibros->telefono->ViewValue ?></div>
</td>
		<!-- idVendedor -->
		<td<?php echo $fotolibros->idVendedor->CellAttributes() ?>>
<div<?php echo $fotolibros->idVendedor->ViewAttributes() ?>><?php echo $fotolibros->idVendedor->ViewValue ?></div>
</td>
		<!-- sena -->
		<td<?php echo $fotolibros->sena->CellAttributes() ?>>
<div<?php echo $fotolibros->sena->ViewAttributes() ?>><?php echo $fotolibros->sena->ViewValue ?></div>
</td>
		<!-- total -->
		<td<?php echo $fotolibros->total->CellAttributes() ?>>
<div<?php echo $fotolibros->total->ViewAttributes() ?>><?php echo $fotolibros->total->ViewValue ?></div>
</td>
		<!-- evento -->
		<td<?php echo $fotolibros->evento->CellAttributes() ?>>
<div<?php echo $fotolibros->evento->ViewAttributes() ?>><?php echo $fotolibros->evento->ViewValue ?></div>
</td>
<?php if ($fotolibros->Export == "") { ?>
<?php if ($Security->CanDelete()) { ?>
<td nowrap width="1" style="padding:0"><span class="phpmaker">
<div class="button-delete"><a href="<?php echo $fotolibros->DeleteUrl() ?>">Borrar</a></div>
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
<?php if ($fotolibros->Export == "") { ?>
<form action="fotolibroslist.php" name="ewpagerform" id="ewpagerform">
<table border="0" cellspacing="0" cellpadding="0" align="center" id="pagertable">
	<tr>
		<td nowrap align="center">
<span class="phpmaker">
<?php if (!isset($Pager)) $Pager = new cNumericPager($nStartRec, $nDisplayRecs, $nTotalRecs, $nRecRange) ?>
<?php if ($Pager->RecordCount > 0) { ?>
	<?php if ($Pager->FirstButton->Enabled) { ?>
	<a href="fotolibroslist.php?start=<?php echo $Pager->FirstButton->Start ?>"><b>Primero</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->PrevButton->Enabled) { ?>
	<a href="fotolibroslist.php?start=<?php echo $Pager->PrevButton->Start ?>"><b>Anterior</b></a>&nbsp;
	<?php } ?>
	<?php foreach ($Pager->Items as $PagerItem) { ?>
		<?php if ($PagerItem->Enabled) { ?><a href="fotolibroslist.php?start=<?php echo $PagerItem->Start ?>"><?php } ?><b><?php echo $PagerItem->Text ?></b><?php if ($PagerItem->Enabled) { ?></a><?php } ?>&nbsp;
	<?php } ?>
	<?php if ($Pager->NextButton->Enabled) { ?>
	<a href="fotolibroslist.php?start=<?php echo $Pager->NextButton->Start ?>"><b>Próximo</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->LastButton->Enabled) { ?>
	<a href="fotolibroslist.php?start=<?php echo $Pager->LastButton->Start ?>"><b>Último</b></a>&nbsp;
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
<?php if ($fotolibros->Export == "") { ?>
<?php } ?>
<?php if ($fotolibros->Export == "") { ?>
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
	$sql .= "`id` = " . $sKeyword . " OR ";
	$sql .= "`nombre` LIKE '%" . $sKeyword . "%' OR ";
	$sql .= "`apellido` LIKE '%" . $sKeyword . "%' OR ";
	$sql .= "`telefono` LIKE '%" . $sKeyword . "%' OR ";
	$sql .= "`pedido` LIKE '%" . $sKeyword . "%' OR ";
	$sql .= "`evento` LIKE '%" . $sKeyword . "%' OR ";
	if (substr($sql, -4) == " OR ") $sql = substr($sql, 0, strlen($sql)-4);
	return $sql;
}

// Return Basic Search Where based on search keyword and type
function BasicSearchWhere() {
	global $Security, $fotolibros;
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
		$fotolibros->setBasicSearchKeyword($sSearchKeyword);
		$fotolibros->setBasicSearchType($sSearchType);
	}
	return $sSearchStr;
}

// Clear all search parameters
function ResetSearchParms() {

	// Clear search where
	global $fotolibros;
	$sSrchWhere = "";
	$fotolibros->setSearchWhere($sSrchWhere);

	// Clear basic search parameters
	ResetBasicSearchParms();
}

// Clear all basic search parameters
function ResetBasicSearchParms() {

	// Clear basic search parameters
	global $fotolibros;
	$fotolibros->setBasicSearchKeyword("");
	$fotolibros->setBasicSearchType("");
}

// Restore all search parameters
function RestoreSearchParms() {
	global $sSrchWhere, $fotolibros;
	$sSrchWhere = $fotolibros->getSearchWhere();
}

// Set up Sort parameters based on Sort Links clicked
function SetUpSortOrder() {
	global $fotolibros;

	// Check for an Order parameter
	if (@$_GET["order"] <> "") {
		$fotolibros->CurrentOrder = ew_StripSlashes(@$_GET["order"]);
		$fotolibros->CurrentOrderType = @$_GET["ordertype"];

		// Field id
		$fotolibros->UpdateSort($fotolibros->id);

		// Field nombre
		$fotolibros->UpdateSort($fotolibros->nombre);

		// Field apellido
		$fotolibros->UpdateSort($fotolibros->apellido);

		// Field telefono
		$fotolibros->UpdateSort($fotolibros->telefono);

		// Field idVendedor
		$fotolibros->UpdateSort($fotolibros->idVendedor);

		// Field sena
		$fotolibros->UpdateSort($fotolibros->sena);

		// Field total
		$fotolibros->UpdateSort($fotolibros->total);

		// Field evento
		$fotolibros->UpdateSort($fotolibros->evento);
		$fotolibros->setStartRecordNumber(1); // Reset start position
	}
	$sOrderBy = $fotolibros->getSessionOrderBy(); // Get order by from Session
	if ($sOrderBy == "") {
		if ($fotolibros->SqlOrderBy() <> "") {
			$sOrderBy = $fotolibros->SqlOrderBy();
			$fotolibros->setSessionOrderBy($sOrderBy);
		}
	}
}

// Reset command based on querystring parameter cmd=
// - RESET: reset search parameters
// - RESETALL: reset search & master/detail parameters
// - RESETSORT: reset sort parameters
function ResetCmd() {
	global $sDbMasterFilter, $sDbDetailFilter, $nStartRec, $sOrderBy;
	global $fotolibros;

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
			$fotolibros->setSessionOrderBy($sOrderBy);
			$fotolibros->id->setSort("");
			$fotolibros->nombre->setSort("");
			$fotolibros->apellido->setSort("");
			$fotolibros->telefono->setSort("");
			$fotolibros->idVendedor->setSort("");
			$fotolibros->sena->setSort("");
			$fotolibros->total->setSort("");
			$fotolibros->evento->setSort("");
		}

		// Reset start position
		$nStartRec = 1;
		$fotolibros->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Set up Starting Record parameters based on Pager Navigation
function SetUpStartRec() {
	global $nDisplayRecs, $nStartRec, $nTotalRecs, $nPageNo, $fotolibros;
	if ($nDisplayRecs == 0) return;

	// Check for a START parameter
	if (@$_GET[EW_TABLE_START_REC] <> "") {
		$nStartRec = $_GET[EW_TABLE_START_REC];
		$fotolibros->setStartRecordNumber($nStartRec);
	} elseif (@$_GET[EW_TABLE_PAGE_NO] <> "") {
		$nPageNo = $_GET[EW_TABLE_PAGE_NO];
		if (is_numeric($nPageNo)) {
			$nStartRec = ($nPageNo-1)*$nDisplayRecs+1;
			if ($nStartRec <= 0) {
				$nStartRec = 1;
			} elseif ($nStartRec >= intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1) {
				$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1;
			}
			$fotolibros->setStartRecordNumber($nStartRec);
		} else {
			$nStartRec = $fotolibros->getStartRecordNumber();
		}
	} else {
		$nStartRec = $fotolibros->getStartRecordNumber();
	}

	// Check if correct start record counter
	if (!is_numeric($nStartRec) || $nStartRec == "") { // Avoid invalid start record counter
		$nStartRec = 1; // Reset start record counter
		$fotolibros->setStartRecordNumber($nStartRec);
	} elseif (intval($nStartRec) > intval($nTotalRecs)) { // Avoid starting record > total records
		$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to last page first record
		$fotolibros->setStartRecordNumber($nStartRec);
	} elseif (($nStartRec-1) % $nDisplayRecs <> 0) {
		$nStartRec = intval(($nStartRec-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to page boundary
		$fotolibros->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Load recordset
function LoadRecordset($offset = -1, $rowcnt = -1) {
	global $conn, $fotolibros;

	// Call Recordset Selecting event
	$fotolibros->Recordset_Selecting($fotolibros->CurrentFilter);

	// Load list page sql
	$sSql = $fotolibros->SelectSQL();
	if ($offset > -1 && $rowcnt > -1) $sSql .= " LIMIT $offset, $rowcnt";

	// Load recordset
	$conn->raiseErrorFn = 'ew_ErrorFn';	
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';

	// Call Recordset Selected event
	$fotolibros->Recordset_Selected($rs);
	return $rs;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $fotolibros;
	$sFilter = $fotolibros->SqlKeyFilter();
	if (!is_numeric($fotolibros->id->CurrentValue)) {
		return FALSE; // Invalid key, exit
	}
	$sFilter = str_replace("@id@", ew_AdjustSql($fotolibros->id->CurrentValue), $sFilter); // Replace key value

	// Call Row Selecting event
	$fotolibros->Row_Selecting($sFilter);

	// Load sql based on filter
	$fotolibros->CurrentFilter = $sFilter;
	$sSql = $fotolibros->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$fotolibros->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $fotolibros;
	$fotolibros->id->setDbValue($rs->fields('id'));
	$fotolibros->nombre->setDbValue($rs->fields('nombre'));
	$fotolibros->apellido->setDbValue($rs->fields('apellido'));
	$fotolibros->telefono->setDbValue($rs->fields('telefono'));
	$fotolibros->pedido->setDbValue($rs->fields('pedido'));
	$fotolibros->idVendedor->setDbValue($rs->fields('idVendedor'));
	$fotolibros->estado->setDbValue($rs->fields('estado'));
	$fotolibros->sena->setDbValue($rs->fields('sena'));
	$fotolibros->total->setDbValue($rs->fields('total'));
	$fotolibros->evento->setDbValue($rs->fields('evento'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $fotolibros;

	// Call Row Rendering event
	$fotolibros->Row_Rendering();

	// Common render codes for all row types
	// nombre

	$fotolibros->nombre->CellCssStyle = "";
	$fotolibros->nombre->CellCssClass = "";

	// apellido
	$fotolibros->apellido->CellCssStyle = "";
	$fotolibros->apellido->CellCssClass = "";

	// telefono
	$fotolibros->telefono->CellCssStyle = "";
	$fotolibros->telefono->CellCssClass = "";

	// idVendedor
	$fotolibros->idVendedor->CellCssStyle = "";
	$fotolibros->idVendedor->CellCssClass = "";

	// sena
	$fotolibros->sena->CellCssStyle = "";
	$fotolibros->sena->CellCssClass = "";

	// total
	$fotolibros->total->CellCssStyle = "";
	$fotolibros->total->CellCssClass = "";

	// evento
	$fotolibros->evento->CellCssStyle = "";
	$fotolibros->evento->CellCssClass = "";
	if ($fotolibros->RowType == EW_ROWTYPE_VIEW) { // View row

		// id
		$fotolibros->id->ViewValue = $fotolibros->id->CurrentValue;
		$fotolibros->id->CssStyle = "";
		$fotolibros->id->CssClass = "";
		$fotolibros->id->ViewCustomAttributes = "";

		// nombre
		$fotolibros->nombre->ViewValue = $fotolibros->nombre->CurrentValue;
		$fotolibros->nombre->CssStyle = "";
		$fotolibros->nombre->CssClass = "";
		$fotolibros->nombre->ViewCustomAttributes = "";

		// apellido
		$fotolibros->apellido->ViewValue = $fotolibros->apellido->CurrentValue;
		$fotolibros->apellido->CssStyle = "";
		$fotolibros->apellido->CssClass = "";
		$fotolibros->apellido->ViewCustomAttributes = "";

		// telefono
		$fotolibros->telefono->ViewValue = $fotolibros->telefono->CurrentValue;
		$fotolibros->telefono->CssStyle = "";
		$fotolibros->telefono->CssClass = "";
		$fotolibros->telefono->ViewCustomAttributes = "";

		// idVendedor
		if (!empty($fotolibros->idVendedor->CurrentValue)) {
			$sSqlWrk = "SELECT `Vendedor` FROM `vendedores` WHERE `id` = " . ew_AdjustSql($fotolibros->idVendedor->CurrentValue) . "";
			$rswrk = $conn->Execute($sSqlWrk);
			if ($rswrk) {
				if (!$rswrk->EOF) {
					$fotolibros->idVendedor->ViewValue = $rswrk->fields('Vendedor');
				}
				$rswrk->Close();
			} else {
				$fotolibros->idVendedor->ViewValue = $fotolibros->idVendedor->CurrentValue;
			}
		} else {
			$fotolibros->idVendedor->ViewValue = NULL;
		}
		$fotolibros->idVendedor->CssStyle = "";
		$fotolibros->idVendedor->CssClass = "";
		$fotolibros->idVendedor->ViewCustomAttributes = "";

		// sena
		$fotolibros->sena->ViewValue = $fotolibros->sena->CurrentValue;
		$fotolibros->sena->ViewValue = ew_FormatCurrency($fotolibros->sena->ViewValue, 2, -2, -2, -2);
		$fotolibros->sena->CssStyle = "";
		$fotolibros->sena->CssClass = "";
		$fotolibros->sena->ViewCustomAttributes = "";

		// total
		$fotolibros->total->ViewValue = $fotolibros->total->CurrentValue;
		$fotolibros->total->ViewValue = ew_FormatCurrency($fotolibros->total->ViewValue, 2, -2, -2, -2);
		$fotolibros->total->CssStyle = "";
		$fotolibros->total->CssClass = "";
		$fotolibros->total->ViewCustomAttributes = "";

		// evento
		$fotolibros->evento->ViewValue = $fotolibros->evento->CurrentValue;
		$fotolibros->evento->CssStyle = "";
		$fotolibros->evento->CssClass = "";
		$fotolibros->evento->ViewCustomAttributes = "";

		// nombre
		$fotolibros->nombre->HrefValue = "";

		// apellido
		$fotolibros->apellido->HrefValue = "";

		// telefono
		$fotolibros->telefono->HrefValue = "";

		// idVendedor
		$fotolibros->idVendedor->HrefValue = "";

		// sena
		$fotolibros->sena->HrefValue = "";

		// total
		$fotolibros->total->HrefValue = "";

		// evento
		$fotolibros->evento->HrefValue = "";
	} elseif ($fotolibros->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($fotolibros->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($fotolibros->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$fotolibros->Row_Rendered();
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
