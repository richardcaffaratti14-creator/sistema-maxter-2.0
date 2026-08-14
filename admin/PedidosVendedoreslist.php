<?php
define("EW_PAGE_ID", "list", TRUE); // Page ID
define("EW_TABLE_NAME", 'PedidosVendedores', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "PedidosVendedoresinfo.php" ?>
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
$Security->LoadCurrentUserLevel('PedidosVendedores');
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
$PedidosVendedores->Export = @$_GET["export"]; // Get export parameter
$sExport = $PedidosVendedores->Export; // Get export parameter, used in header
$sExportFile = $PedidosVendedores->TableVar; // Get export file, used in header
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
	$PedidosVendedores->setSearchWhere($sSrchWhere); // Save to Session
	$nStartRec = 1; // Reset start record counter
	$PedidosVendedores->setStartRecordNumber($nStartRec);
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
$PedidosVendedores->setSessionWhere($sFilter);
$PedidosVendedores->CurrentFilter = "";

// Set Up Sorting Order
SetUpSortOrder();

// Set Return Url
$PedidosVendedores->setReturnUrl("PedidosVendedoreslist.php");
?>
<?php include "header.php" ?>
<?php if ($PedidosVendedores->Export == "") { ?>
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
<?php if ($PedidosVendedores->Export == "") { ?>
<?php } ?>
<?php

// Load recordset
$bExportAll = (defined("EW_EXPORT_ALL") && $PedidosVendedores->Export <> "");
$bSelectLimit = ($PedidosVendedores->Export == "" && $PedidosVendedores->SelectLimit);
if (!$bSelectLimit) $rs = LoadRecordset();
$nTotalRecs = ($bSelectLimit) ? $PedidosVendedores->SelectRecordCount() : $rs->RecordCount();
$nStartRec = 1;
if ($nDisplayRecs <= 0) $nDisplayRecs = $nTotalRecs; // Display all records
if (!$bExportAll) SetUpStartRec(); // Set up start record position
if ($bSelectLimit) $rs = LoadRecordset($nStartRec-1, $nDisplayRecs);
?>
<?php if ($PedidosVendedores->Export == "") { ?>
<?php if ($Security->CanSearch()) { ?>
<form name="fPedidosVendedoreslistsrch" id="fPedidosVendedoreslistsrch" action="PedidosVendedoreslist.php" >
<table class="ewBasicSearch" align="center">
	<tr>
		<td><span class="phpmaker">
			<input type="text" name="<?php echo EW_TABLE_BASIC_SEARCH ?>" id="<?php echo EW_TABLE_BASIC_SEARCH ?>" size="20" value="<?php echo $PedidosVendedores->getBasicSearchKeyword() ?>">
			<input type="Submit" name="Submit" id="Submit" value="Buscar (*)">&nbsp;
			<a href="PedidosVendedoreslist.php?cmd=reset">Mostrar todo</a>&nbsp;
		</span></td>
	</tr>
	<tr>
	<td><span class="phpmaker"><input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="" <?php if ($PedidosVendedores->getBasicSearchType() == "") { ?>checked<?php } ?>>Frase exacta&nbsp;&nbsp;<input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="AND" <?php if ($PedidosVendedores->getBasicSearchType() == "AND") { ?>checked<?php } ?>>Todas las palabras&nbsp;&nbsp;<input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="OR" <?php if ($PedidosVendedores->getBasicSearchType() == "OR") { ?>checked<?php } ?>>Cualquier palabra</span></td>
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
<form method="post" name="fPedidosVendedoreslist" id="fPedidosVendedoreslist">
<?php if ($PedidosVendedores->Export == "") { ?>
<table class="subheader" align="center">
	<tr><td><span class="phpmaker">
<div class="tablername" style="margin-top:7px">Pedidos Vendedores</div>
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
<?php if ($PedidosVendedores->Export <> "") { ?>
Evento
<?php } else { ?>
	<a href="PedidosVendedoreslist.php?order=<?php echo urlencode('Evento') ?>&ordertype=<?php echo $PedidosVendedores->Evento->ReverseSort() ?>">Evento&nbsp;(*)<?php if ($PedidosVendedores->Evento->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($PedidosVendedores->Evento->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($PedidosVendedores->Export <> "") { ?>
Vendedor
<?php } else { ?>
	<a href="PedidosVendedoreslist.php?order=<?php echo urlencode('Vendedor') ?>&ordertype=<?php echo $PedidosVendedores->Vendedor->ReverseSort() ?>">Vendedor&nbsp;(*)<?php if ($PedidosVendedores->Vendedor->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($PedidosVendedores->Vendedor->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($PedidosVendedores->Export <> "") { ?>
Número
<?php } else { ?>
	<a href="PedidosVendedoreslist.php?order=<?php echo urlencode('Num') ?>&ordertype=<?php echo $PedidosVendedores->Num->ReverseSort() ?>">Número<?php if ($PedidosVendedores->Num->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($PedidosVendedores->Num->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($PedidosVendedores->Export <> "") { ?>
Subtotal
<?php } else { ?>
	<a href="PedidosVendedoreslist.php?order=<?php echo urlencode('Subtotal') ?>&ordertype=<?php echo $PedidosVendedores->Subtotal->ReverseSort() ?>">Subtotal<?php if ($PedidosVendedores->Subtotal->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($PedidosVendedores->Subtotal->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($PedidosVendedores->Export <> "") { ?>
Descuento
<?php } else { ?>
	<a href="PedidosVendedoreslist.php?order=<?php echo urlencode('Descuento') ?>&ordertype=<?php echo $PedidosVendedores->Descuento->ReverseSort() ?>">Descuento<?php if ($PedidosVendedores->Descuento->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($PedidosVendedores->Descuento->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($PedidosVendedores->Export <> "") { ?>
Total
<?php } else { ?>
	<a href="PedidosVendedoreslist.php?order=<?php echo urlencode('Total') ?>&ordertype=<?php echo $PedidosVendedores->Total->ReverseSort() ?>">Total<?php if ($PedidosVendedores->Total->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($PedidosVendedores->Total->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
<?php if ($PedidosVendedores->Export == "") { ?>
<?php } ?>
	</tr>
<?php
if (defined("EW_EXPORT_ALL") && $PedidosVendedores->Export <> "") {
	$nStopRec = $nTotalRecs;
} else {
	$nStopRec = $nStartRec + $nDisplayRecs - 1; // Set the last record to display
}
$nRecCount = $nStartRec - 1;
if (!$rs->EOF) {
	$rs->MoveFirst();
	if (!$PedidosVendedores->SelectLimit) $rs->Move($nStartRec - 1); // Move to first record directly
}
$RowCnt = 0;
while (!$rs->EOF && $nRecCount < $nStopRec) {
	$nRecCount++;
	if (intval($nRecCount) >= intval($nStartRec)) {
		$RowCnt++;

	// Init row class and style
	$PedidosVendedores->CssClass = "ewTableRow";
	$PedidosVendedores->CssStyle = "";

	// Init row event
	$PedidosVendedores->RowClientEvents = "onmouseover='ew_MouseOver(this);' onmouseout='ew_MouseOut(this);' onclick='ew_Click(this);'";

	// Display alternate color for rows
	if ($RowCnt % 2 == 0) {
		$PedidosVendedores->CssClass = "ewTableAltRow";
	}
	LoadRowValues($rs); // Load row values
	$PedidosVendedores->RowType = EW_ROWTYPE_VIEW; // Render view
	RenderRow();
?>
	<!-- Table body -->
	<tr<?php echo $PedidosVendedores->DisplayAttributes() ?>>
		<!-- Evento -->
		<td<?php echo $PedidosVendedores->Evento->CellAttributes() ?>>
<div<?php echo $PedidosVendedores->Evento->ViewAttributes() ?>><?php echo $PedidosVendedores->Evento->ViewValue ?></div>
</td>
		<!-- Vendedor -->
		<td<?php echo $PedidosVendedores->Vendedor->CellAttributes() ?>>
<div<?php echo $PedidosVendedores->Vendedor->ViewAttributes() ?>><?php echo $PedidosVendedores->Vendedor->ViewValue ?></div>
</td>
		<!-- Num -->
		<td<?php echo $PedidosVendedores->Num->CellAttributes() ?>>
<div<?php echo $PedidosVendedores->Num->ViewAttributes() ?>><?php echo $PedidosVendedores->Num->ViewValue ?></div>
</td>
		<!-- Subtotal -->
		<td<?php echo $PedidosVendedores->Subtotal->CellAttributes() ?>>
<div<?php echo $PedidosVendedores->Subtotal->ViewAttributes() ?>><?php echo $PedidosVendedores->Subtotal->ViewValue ?></div>
</td>
		<!-- Descuento -->
		<td<?php echo $PedidosVendedores->Descuento->CellAttributes() ?>>
<div<?php echo $PedidosVendedores->Descuento->ViewAttributes() ?>><?php echo $PedidosVendedores->Descuento->ViewValue ?></div>
</td>
		<!-- Total -->
		<td<?php echo $PedidosVendedores->Total->CellAttributes() ?>>
<div<?php echo $PedidosVendedores->Total->ViewAttributes() ?>><?php echo $PedidosVendedores->Total->ViewValue ?></div>
</td>
<?php if ($PedidosVendedores->Export == "") { ?>
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
<?php if ($PedidosVendedores->Export == "") { ?>
<form action="PedidosVendedoreslist.php" name="ewpagerform" id="ewpagerform">
<table border="0" cellspacing="0" cellpadding="0" align="center" id="pagertable">
	<tr>
		<td nowrap align="center">
<span class="phpmaker">
<?php if (!isset($Pager)) $Pager = new cNumericPager($nStartRec, $nDisplayRecs, $nTotalRecs, $nRecRange) ?>
<?php if ($Pager->RecordCount > 0) { ?>
	<?php if ($Pager->FirstButton->Enabled) { ?>
	<a href="PedidosVendedoreslist.php?start=<?php echo $Pager->FirstButton->Start ?>"><b>Primero</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->PrevButton->Enabled) { ?>
	<a href="PedidosVendedoreslist.php?start=<?php echo $Pager->PrevButton->Start ?>"><b>Anterior</b></a>&nbsp;
	<?php } ?>
	<?php foreach ($Pager->Items as $PagerItem) { ?>
		<?php if ($PagerItem->Enabled) { ?><a href="PedidosVendedoreslist.php?start=<?php echo $PagerItem->Start ?>"><?php } ?><b><?php echo $PagerItem->Text ?></b><?php if ($PagerItem->Enabled) { ?></a><?php } ?>&nbsp;
	<?php } ?>
	<?php if ($Pager->NextButton->Enabled) { ?>
	<a href="PedidosVendedoreslist.php?start=<?php echo $Pager->NextButton->Start ?>"><b>Próximo</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->LastButton->Enabled) { ?>
	<a href="PedidosVendedoreslist.php?start=<?php echo $Pager->LastButton->Start ?>"><b>Último</b></a>&nbsp;
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
<?php if ($PedidosVendedores->Export == "") { ?>
<?php } ?>
<?php if ($PedidosVendedores->Export == "") { ?>
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
	$sql .= "pedidos.Evento LIKE '%" . $sKeyword . "%' OR ";
	$sql .= "vendedores.Vendedor LIKE '%" . $sKeyword . "%' OR ";
	if (substr($sql, -4) == " OR ") $sql = substr($sql, 0, strlen($sql)-4);
	return $sql;
}

// Return Basic Search Where based on search keyword and type
function BasicSearchWhere() {
	global $Security, $PedidosVendedores;
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
		$PedidosVendedores->setBasicSearchKeyword($sSearchKeyword);
		$PedidosVendedores->setBasicSearchType($sSearchType);
	}
	return $sSearchStr;
}

// Clear all search parameters
function ResetSearchParms() {

	// Clear search where
	global $PedidosVendedores;
	$sSrchWhere = "";
	$PedidosVendedores->setSearchWhere($sSrchWhere);

	// Clear basic search parameters
	ResetBasicSearchParms();
}

// Clear all basic search parameters
function ResetBasicSearchParms() {

	// Clear basic search parameters
	global $PedidosVendedores;
	$PedidosVendedores->setBasicSearchKeyword("");
	$PedidosVendedores->setBasicSearchType("");
}

// Restore all search parameters
function RestoreSearchParms() {
	global $sSrchWhere, $PedidosVendedores;
	$sSrchWhere = $PedidosVendedores->getSearchWhere();
}

// Set up Sort parameters based on Sort Links clicked
function SetUpSortOrder() {
	global $PedidosVendedores;

	// Check for an Order parameter
	if (@$_GET["order"] <> "") {
		$PedidosVendedores->CurrentOrder = ew_StripSlashes(@$_GET["order"]);
		$PedidosVendedores->CurrentOrderType = @$_GET["ordertype"];

		// Field Evento
		$PedidosVendedores->UpdateSort($PedidosVendedores->Evento);

		// Field Vendedor
		$PedidosVendedores->UpdateSort($PedidosVendedores->Vendedor);

		// Field Num
		$PedidosVendedores->UpdateSort($PedidosVendedores->Num);

		// Field Subtotal
		$PedidosVendedores->UpdateSort($PedidosVendedores->Subtotal);

		// Field Descuento
		$PedidosVendedores->UpdateSort($PedidosVendedores->Descuento);

		// Field Total
		$PedidosVendedores->UpdateSort($PedidosVendedores->Total);
		$PedidosVendedores->setStartRecordNumber(1); // Reset start position
	}
	$sOrderBy = $PedidosVendedores->getSessionOrderBy(); // Get order by from Session
	if ($sOrderBy == "") {
		if ($PedidosVendedores->SqlOrderBy() <> "") {
			$sOrderBy = $PedidosVendedores->SqlOrderBy();
			$PedidosVendedores->setSessionOrderBy($sOrderBy);
		}
	}
}

// Reset command based on querystring parameter cmd=
// - RESET: reset search parameters
// - RESETALL: reset search & master/detail parameters
// - RESETSORT: reset sort parameters
function ResetCmd() {
	global $sDbMasterFilter, $sDbDetailFilter, $nStartRec, $sOrderBy;
	global $PedidosVendedores;

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
			$PedidosVendedores->setSessionOrderBy($sOrderBy);
			$PedidosVendedores->Evento->setSort("");
			$PedidosVendedores->Vendedor->setSort("");
			$PedidosVendedores->Num->setSort("");
			$PedidosVendedores->Subtotal->setSort("");
			$PedidosVendedores->Descuento->setSort("");
			$PedidosVendedores->Total->setSort("");
		}

		// Reset start position
		$nStartRec = 1;
		$PedidosVendedores->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Set up Starting Record parameters based on Pager Navigation
function SetUpStartRec() {
	global $nDisplayRecs, $nStartRec, $nTotalRecs, $nPageNo, $PedidosVendedores;
	if ($nDisplayRecs == 0) return;

	// Check for a START parameter
	if (@$_GET[EW_TABLE_START_REC] <> "") {
		$nStartRec = $_GET[EW_TABLE_START_REC];
		$PedidosVendedores->setStartRecordNumber($nStartRec);
	} elseif (@$_GET[EW_TABLE_PAGE_NO] <> "") {
		$nPageNo = $_GET[EW_TABLE_PAGE_NO];
		if (is_numeric($nPageNo)) {
			$nStartRec = ($nPageNo-1)*$nDisplayRecs+1;
			if ($nStartRec <= 0) {
				$nStartRec = 1;
			} elseif ($nStartRec >= intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1) {
				$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1;
			}
			$PedidosVendedores->setStartRecordNumber($nStartRec);
		} else {
			$nStartRec = $PedidosVendedores->getStartRecordNumber();
		}
	} else {
		$nStartRec = $PedidosVendedores->getStartRecordNumber();
	}

	// Check if correct start record counter
	if (!is_numeric($nStartRec) || $nStartRec == "") { // Avoid invalid start record counter
		$nStartRec = 1; // Reset start record counter
		$PedidosVendedores->setStartRecordNumber($nStartRec);
	} elseif (intval($nStartRec) > intval($nTotalRecs)) { // Avoid starting record > total records
		$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to last page first record
		$PedidosVendedores->setStartRecordNumber($nStartRec);
	} elseif (($nStartRec-1) % $nDisplayRecs <> 0) {
		$nStartRec = intval(($nStartRec-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to page boundary
		$PedidosVendedores->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Load recordset
function LoadRecordset($offset = -1, $rowcnt = -1) {
	global $conn, $PedidosVendedores;

	// Call Recordset Selecting event
	$PedidosVendedores->Recordset_Selecting($PedidosVendedores->CurrentFilter);

	// Load list page sql
	$sSql = $PedidosVendedores->SelectSQL();
	if ($offset > -1 && $rowcnt > -1) $sSql .= " LIMIT $offset, $rowcnt";

	// Load recordset
	$conn->raiseErrorFn = 'ew_ErrorFn';	
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';

	// Call Recordset Selected event
	$PedidosVendedores->Recordset_Selected($rs);
	return $rs;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $PedidosVendedores;
	$sFilter = $PedidosVendedores->SqlKeyFilter();

	// Call Row Selecting event
	$PedidosVendedores->Row_Selecting($sFilter);

	// Load sql based on filter
	$PedidosVendedores->CurrentFilter = $sFilter;
	$sSql = $PedidosVendedores->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$PedidosVendedores->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $PedidosVendedores;
	$PedidosVendedores->Evento->setDbValue($rs->fields('Evento'));
	$PedidosVendedores->Vendedor->setDbValue($rs->fields('Vendedor'));
	$PedidosVendedores->Num->setDbValue($rs->fields('Num'));
	$PedidosVendedores->Subtotal->setDbValue($rs->fields('Subtotal'));
	$PedidosVendedores->Descuento->setDbValue($rs->fields('Descuento'));
	$PedidosVendedores->Total->setDbValue($rs->fields('Total'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $PedidosVendedores;

	// Call Row Rendering event
	$PedidosVendedores->Row_Rendering();

	// Common render codes for all row types
	// Evento

	$PedidosVendedores->Evento->CellCssStyle = "";
	$PedidosVendedores->Evento->CellCssClass = "";

	// Vendedor
	$PedidosVendedores->Vendedor->CellCssStyle = "";
	$PedidosVendedores->Vendedor->CellCssClass = "";

	// Num
	$PedidosVendedores->Num->CellCssStyle = "";
	$PedidosVendedores->Num->CellCssClass = "";

	// Subtotal
	$PedidosVendedores->Subtotal->CellCssStyle = "";
	$PedidosVendedores->Subtotal->CellCssClass = "";

	// Descuento
	$PedidosVendedores->Descuento->CellCssStyle = "";
	$PedidosVendedores->Descuento->CellCssClass = "";

	// Total
	$PedidosVendedores->Total->CellCssStyle = "";
	$PedidosVendedores->Total->CellCssClass = "";
	if ($PedidosVendedores->RowType == EW_ROWTYPE_VIEW) { // View row

		// Evento
		$PedidosVendedores->Evento->ViewValue = $PedidosVendedores->Evento->CurrentValue;
		$PedidosVendedores->Evento->CssStyle = "";
		$PedidosVendedores->Evento->CssClass = "";
		$PedidosVendedores->Evento->ViewCustomAttributes = "";

		// Vendedor
		$PedidosVendedores->Vendedor->ViewValue = $PedidosVendedores->Vendedor->CurrentValue;
		$PedidosVendedores->Vendedor->CssStyle = "";
		$PedidosVendedores->Vendedor->CssClass = "";
		$PedidosVendedores->Vendedor->ViewCustomAttributes = "";

		// Num
		$PedidosVendedores->Num->ViewValue = $PedidosVendedores->Num->CurrentValue;
		$PedidosVendedores->Num->CssStyle = "";
		$PedidosVendedores->Num->CssClass = "";
		$PedidosVendedores->Num->ViewCustomAttributes = "";

		// Subtotal
		$PedidosVendedores->Subtotal->ViewValue = $PedidosVendedores->Subtotal->CurrentValue;
		$PedidosVendedores->Subtotal->CssStyle = "";
		$PedidosVendedores->Subtotal->CssClass = "";
		$PedidosVendedores->Subtotal->ViewCustomAttributes = "";

		// Descuento
		$PedidosVendedores->Descuento->ViewValue = $PedidosVendedores->Descuento->CurrentValue;
		$PedidosVendedores->Descuento->CssStyle = "";
		$PedidosVendedores->Descuento->CssClass = "";
		$PedidosVendedores->Descuento->ViewCustomAttributes = "";

		// Total
		$PedidosVendedores->Total->ViewValue = $PedidosVendedores->Total->CurrentValue;
		$PedidosVendedores->Total->CssStyle = "";
		$PedidosVendedores->Total->CssClass = "";
		$PedidosVendedores->Total->ViewCustomAttributes = "";

		// Evento
		$PedidosVendedores->Evento->HrefValue = "";

		// Vendedor
		$PedidosVendedores->Vendedor->HrefValue = "";

		// Num
		$PedidosVendedores->Num->HrefValue = "";

		// Subtotal
		$PedidosVendedores->Subtotal->HrefValue = "";

		// Descuento
		$PedidosVendedores->Descuento->HrefValue = "";

		// Total
		$PedidosVendedores->Total->HrefValue = "";
	} elseif ($PedidosVendedores->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($PedidosVendedores->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($PedidosVendedores->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$PedidosVendedores->Row_Rendered();
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
