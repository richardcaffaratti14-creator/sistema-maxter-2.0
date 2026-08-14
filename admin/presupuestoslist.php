<?php
define("EW_PAGE_ID", "list", TRUE); // Page ID
define("EW_TABLE_NAME", 'presupuestos', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "presupuestosinfo.php" ?>
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
$Security->LoadCurrentUserLevel('presupuestos');
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
$presupuestos->Export = @$_GET["export"]; // Get export parameter
$sExport = $presupuestos->Export; // Get export parameter, used in header
$sExportFile = $presupuestos->TableVar; // Get export file, used in header
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
	$presupuestos->setSearchWhere($sSrchWhere); // Save to Session
	$nStartRec = 1; // Reset start record counter
	$presupuestos->setStartRecordNumber($nStartRec);
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
$presupuestos->setSessionWhere($sFilter);
$presupuestos->CurrentFilter = "";

// Set Up Sorting Order
SetUpSortOrder();

// Set Return Url
$presupuestos->setReturnUrl("presupuestoslist.php");
?>
<?php include "header.php" ?>
<?php if ($presupuestos->Export == "") { ?>
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
<?php if ($presupuestos->Export == "") { ?>
<?php } ?>
<?php

// Load recordset
$bExportAll = (defined("EW_EXPORT_ALL") && $presupuestos->Export <> "");
$bSelectLimit = ($presupuestos->Export == "" && $presupuestos->SelectLimit);
if (!$bSelectLimit) $rs = LoadRecordset();
$nTotalRecs = ($bSelectLimit) ? $presupuestos->SelectRecordCount() : $rs->RecordCount();
$nStartRec = 1;
if ($nDisplayRecs <= 0) $nDisplayRecs = $nTotalRecs; // Display all records
if (!$bExportAll) SetUpStartRec(); // Set up start record position
if ($bSelectLimit) $rs = LoadRecordset($nStartRec-1, $nDisplayRecs);
?>
<?php if ($presupuestos->Export == "") { ?>
<?php if ($Security->CanSearch()) { ?>
<form name="fpresupuestoslistsrch" id="fpresupuestoslistsrch" action="presupuestoslist.php" >
<table class="ewBasicSearch" align="center">
	<tr>
		<td><span class="phpmaker">
			<input type="text" name="<?php echo EW_TABLE_BASIC_SEARCH ?>" id="<?php echo EW_TABLE_BASIC_SEARCH ?>" size="20" value="<?php echo $presupuestos->getBasicSearchKeyword() ?>">
			<input type="Submit" name="Submit" id="Submit" value="Buscar (*)">&nbsp;
			<a href="presupuestoslist.php?cmd=reset">Mostrar todo</a>&nbsp;
		</span></td>
	</tr>
	<tr>
	<td><span class="phpmaker"><input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="" <?php if ($presupuestos->getBasicSearchType() == "") { ?>checked<?php } ?>>Frase exacta&nbsp;&nbsp;<input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="AND" <?php if ($presupuestos->getBasicSearchType() == "AND") { ?>checked<?php } ?>>Todas las palabras&nbsp;&nbsp;<input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="OR" <?php if ($presupuestos->getBasicSearchType() == "OR") { ?>checked<?php } ?>>Cualquier palabra</span></td>
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
<form method="post" name="fpresupuestoslist" id="fpresupuestoslist">
<?php if ($nTotalRecs > 0) { ?>
<div id="whiteback" style="display: inline-block; width:auto">
<table class="subheader" align="center" style="width:100%">
	<tr><td><span class="phpmaker">
<div class="tablername" style="margin-top:7px">Presupuestos</div>
	</span></td></tr>
</table>
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
<?php if ($presupuestos->Export <> "") { ?>
Número
<?php } else { ?>
	<a href="presupuestoslist.php?order=<?php echo urlencode('id') ?>&ordertype=<?php echo $presupuestos->id->ReverseSort() ?>">Número<?php if ($presupuestos->id->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($presupuestos->id->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($presupuestos->Export <> "") { ?>
Nombre
<?php } else { ?>
	<a href="presupuestoslist.php?order=<?php echo urlencode('nombre') ?>&ordertype=<?php echo $presupuestos->nombre->ReverseSort() ?>">Nombre&nbsp;(*)<?php if ($presupuestos->nombre->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($presupuestos->nombre->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($presupuestos->Export <> "") { ?>
Apellido
<?php } else { ?>
	<a href="presupuestoslist.php?order=<?php echo urlencode('apellido') ?>&ordertype=<?php echo $presupuestos->apellido->ReverseSort() ?>">Apellido&nbsp;(*)<?php if ($presupuestos->apellido->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($presupuestos->apellido->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($presupuestos->Export <> "") { ?>
Teléfono
<?php } else { ?>
	<a href="presupuestoslist.php?order=<?php echo urlencode('telefono') ?>&ordertype=<?php echo $presupuestos->telefono->ReverseSort() ?>">Teléfono&nbsp;(*)<?php if ($presupuestos->telefono->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($presupuestos->telefono->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($presupuestos->Export <> "") { ?>
Vendedor
<?php } else { ?>
	<a href="presupuestoslist.php?order=<?php echo urlencode('idVendedor') ?>&ordertype=<?php echo $presupuestos->idVendedor->ReverseSort() ?>">Vendedor<?php if ($presupuestos->idVendedor->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($presupuestos->idVendedor->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($presupuestos->Export <> "") { ?>
Evento
<?php } else { ?>
	<a href="presupuestoslist.php?order=<?php echo urlencode('evento') ?>&ordertype=<?php echo $presupuestos->evento->ReverseSort() ?>">Evento&nbsp;(*)<?php if ($presupuestos->evento->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($presupuestos->evento->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($presupuestos->Export <> "") { ?>
Seña
<?php } else { ?>
	<a href="presupuestoslist.php?order=<?php echo urlencode('sena') ?>&ordertype=<?php echo $presupuestos->sena->ReverseSort() ?>">Seña<?php if ($presupuestos->sena->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($presupuestos->sena->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($presupuestos->Export <> "") { ?>
Subtotal
<?php } else { ?>
	<a href="presupuestoslist.php?order=<?php echo urlencode('subtotal') ?>&ordertype=<?php echo $presupuestos->subtotal->ReverseSort() ?>">Subtotal<?php if ($presupuestos->subtotal->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($presupuestos->subtotal->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($presupuestos->Export <> "") { ?>
Descuento
<?php } else { ?>
	<a href="presupuestoslist.php?order=<?php echo urlencode('descuento') ?>&ordertype=<?php echo $presupuestos->descuento->ReverseSort() ?>">Descuento<?php if ($presupuestos->descuento->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($presupuestos->descuento->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($presupuestos->Export <> "") { ?>
Total
<?php } else { ?>
	<a href="presupuestoslist.php?order=<?php echo urlencode('total') ?>&ordertype=<?php echo $presupuestos->total->ReverseSort() ?>">Total<?php if ($presupuestos->total->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($presupuestos->total->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($presupuestos->Export <> "") { ?>
Estado
<?php } else { ?>
	<a href="presupuestoslist.php?order=<?php echo urlencode('estado') ?>&ordertype=<?php echo $presupuestos->estado->ReverseSort() ?>">Estado<?php if ($presupuestos->estado->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($presupuestos->estado->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
<?php if ($presupuestos->Export == "") { ?>
<?php if ($Security->CanDelete()) { ?>
<td nowrap>&nbsp;</td>
<?php } ?>
<?php } ?>
	</tr>
<?php
if (defined("EW_EXPORT_ALL") && $presupuestos->Export <> "") {
	$nStopRec = $nTotalRecs;
} else {
	$nStopRec = $nStartRec + $nDisplayRecs - 1; // Set the last record to display
}
$nRecCount = $nStartRec - 1;
if (!$rs->EOF) {
	$rs->MoveFirst();
	if (!$presupuestos->SelectLimit) $rs->Move($nStartRec - 1); // Move to first record directly
}
$RowCnt = 0;
while (!$rs->EOF && $nRecCount < $nStopRec) {
	$nRecCount++;
	if (intval($nRecCount) >= intval($nStartRec)) {
		$RowCnt++;

	// Init row class and style
	$presupuestos->CssClass = "ewTableRow";
	$presupuestos->CssStyle = "";

	// Init row event
	$presupuestos->RowClientEvents = "onmouseover='ew_MouseOver(this);' onmouseout='ew_MouseOut(this);' onclick='ew_Click(this);'";

	// Display alternate color for rows
	if ($RowCnt % 2 == 0) {
		$presupuestos->CssClass = "ewTableAltRow";
	}
	LoadRowValues($rs); // Load row values
	$presupuestos->RowType = EW_ROWTYPE_VIEW; // Render view
	RenderRow();
?>
	<!-- Table body -->
	<tr<?php echo $presupuestos->DisplayAttributes() ?>>
		<!-- id -->
		<td<?php echo $presupuestos->id->CellAttributes() ?>>
<div style="white-space: nowrap">
	<a target="_blank" href="../?presu_id=<?= base64_encode($presupuestos->id->ViewValue) ?>"><?php echo $presupuestos->id->ViewValue ?></a>
	
	<?
	$idped = ExecReturnFirstField("select id from pedidos where idPresupuesto = " . $presupuestos->id->CurrentValue);
	if ($idped) {
		?>&nbsp;&nbsp;&nbsp;<a href="../ajax/pdf?id=<?php echo $idped ?>" target="_blank">pedido: <?php echo $idped ?></a><?
	}
	?>
	
</div>
</td>
		<!-- nombre -->
		<td<?php echo $presupuestos->nombre->CellAttributes() ?>>
<div<?php echo $presupuestos->nombre->ViewAttributes() ?>><?php echo $presupuestos->nombre->ViewValue ?></div>
</td>
		<!-- apellido -->
		<td<?php echo $presupuestos->apellido->CellAttributes() ?>>
<div<?php echo $presupuestos->apellido->ViewAttributes() ?>><?php echo $presupuestos->apellido->ViewValue ?></div>
</td>
		<!-- telefono -->
		<td<?php echo $presupuestos->telefono->CellAttributes() ?>>
<div<?php echo $presupuestos->telefono->ViewAttributes() ?>><?php echo $presupuestos->telefono->ViewValue ?></div>
</td>
		<!-- idVendedor -->
		<td<?php echo $presupuestos->idVendedor->CellAttributes() ?>>
<div<?php echo $presupuestos->idVendedor->ViewAttributes() ?>><?php echo $presupuestos->idVendedor->ViewValue ?></div>
</td>
		<!-- evento -->
		<td<?php echo $presupuestos->evento->CellAttributes() ?>>
<div<?php echo $presupuestos->evento->ViewAttributes() ?>><?php echo $presupuestos->evento->ViewValue ?></div>
</td>
		<!-- sena -->
		<td<?php echo $presupuestos->sena->CellAttributes() ?>>
<div<?php echo $presupuestos->sena->ViewAttributes() ?>><?php echo $presupuestos->sena->ViewValue ?></div>
</td>
		<!-- subtotal -->
		<td<?php echo $presupuestos->subtotal->CellAttributes() ?>>
<div<?php echo $presupuestos->subtotal->ViewAttributes() ?>><?php echo $presupuestos->subtotal->ViewValue ?></div>
</td>
		<!-- descuento -->
		<td<?php echo $presupuestos->descuento->CellAttributes() ?>>
<div<?php echo $presupuestos->descuento->ViewAttributes() ?>><?php echo $presupuestos->descuento->ViewValue ?></div>
</td>
		<!-- total -->
		<td<?php echo $presupuestos->total->CellAttributes() ?>>
<div<?php echo $presupuestos->total->ViewAttributes() ?>><?php echo $presupuestos->total->ViewValue ?></div>
</td>
		<!-- estado -->
		<td<?php echo $presupuestos->estado->CellAttributes() ?>>
<div<?php echo $presupuestos->estado->ViewAttributes() ?>><?php echo $presupuestos->estado->ViewValue ?></div>
	<?if ($presupuestos->estado->CurrentValue == 1) {?>
<div><strong><?php echo $presupuestos->presu_tarjeta->ViewValue ?></strong></div>
	<?}?>
</td>

<?php if ($presupuestos->Export == "") { ?>
<?php if ($Security->CanDelete()) { ?>
<td nowrap width="1" style="padding:0"><span class="phpmaker">

<?php if ($Security->CanEdit()) { ?>
<div class="button-edit" style="margin: 5px 0 0"><a href="<?php echo $presupuestos->EditUrl() ?>">Editar</a></div>
<?php } ?>

<div class="button-delete"><a href="<?php echo $presupuestos->DeleteUrl() ?>">Borrar</a></div>
<div style="margin: 5px 0 10px; text-align: center;"><a target="_blank" href="..//ajax/pdf_presu?id=<?= $presupuestos->id->ViewValue ?>">IMPRIMIR PDF</a></div>
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
<?php if ($presupuestos->Export == "") { ?>
<form action="presupuestoslist.php" name="ewpagerform" id="ewpagerform">
<table border="0" cellspacing="0" cellpadding="0" align="center" id="pagertable">
	<tr>
		<td nowrap align="center">
<span class="phpmaker">
<?php if (!isset($Pager)) $Pager = new cNumericPager($nStartRec, $nDisplayRecs, $nTotalRecs, $nRecRange) ?>
<?php if ($Pager->RecordCount > 0) { ?>
	<?php if ($Pager->FirstButton->Enabled) { ?>
	<a href="presupuestoslist.php?start=<?php echo $Pager->FirstButton->Start ?>"><b>Primero</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->PrevButton->Enabled) { ?>
	<a href="presupuestoslist.php?start=<?php echo $Pager->PrevButton->Start ?>"><b>Anterior</b></a>&nbsp;
	<?php } ?>
	<?php foreach ($Pager->Items as $PagerItem) { ?>
		<?php if ($PagerItem->Enabled) { ?><a href="presupuestoslist.php?start=<?php echo $PagerItem->Start ?>"><?php } ?><b><?php echo $PagerItem->Text ?></b><?php if ($PagerItem->Enabled) { ?></a><?php } ?>&nbsp;
	<?php } ?>
	<?php if ($Pager->NextButton->Enabled) { ?>
	<a href="presupuestoslist.php?start=<?php echo $Pager->NextButton->Start ?>"><b>Próximo</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->LastButton->Enabled) { ?>
	<a href="presupuestoslist.php?start=<?php echo $Pager->LastButton->Start ?>"><b>Último</b></a>&nbsp;
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
<?php if ($presupuestos->Export == "") { ?>
<?php } ?>
<?php if ($presupuestos->Export == "") { ?>
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
	$sql .= "`apellido` LIKE '%" . $sKeyword . "%' OR ";
	$sql .= "`telefono` LIKE '%" . $sKeyword . "%' OR ";
	$sql .= "`evento` LIKE '%" . $sKeyword . "%' OR ";
	$sql .= "`presupuesto` LIKE '%" . $sKeyword . "%' OR ";
	$sql .= "`pedido` LIKE '%" . $sKeyword . "%' OR ";
	if (substr($sql, -4) == " OR ") $sql = substr($sql, 0, strlen($sql)-4);
	return $sql;
}

// Return Basic Search Where based on search keyword and type
function BasicSearchWhere() {
	global $Security, $presupuestos;
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
		$presupuestos->setBasicSearchKeyword($sSearchKeyword);
		$presupuestos->setBasicSearchType($sSearchType);
	}
	return $sSearchStr;
}

// Clear all search parameters
function ResetSearchParms() {

	// Clear search where
	global $presupuestos;
	$sSrchWhere = "";
	$presupuestos->setSearchWhere($sSrchWhere);

	// Clear basic search parameters
	ResetBasicSearchParms();
}

// Clear all basic search parameters
function ResetBasicSearchParms() {

	// Clear basic search parameters
	global $presupuestos;
	$presupuestos->setBasicSearchKeyword("");
	$presupuestos->setBasicSearchType("");
}

// Restore all search parameters
function RestoreSearchParms() {
	global $sSrchWhere, $presupuestos;
	$sSrchWhere = $presupuestos->getSearchWhere();
}

// Set up Sort parameters based on Sort Links clicked
function SetUpSortOrder() {
	global $presupuestos;

	// Check for an Order parameter
	if (@$_GET["order"] <> "") {
		$presupuestos->CurrentOrder = ew_StripSlashes(@$_GET["order"]);
		$presupuestos->CurrentOrderType = @$_GET["ordertype"];

		// Field id
		$presupuestos->UpdateSort($presupuestos->id);

		// Field nombre
		$presupuestos->UpdateSort($presupuestos->nombre);

		// Field apellido
		$presupuestos->UpdateSort($presupuestos->apellido);

		// Field telefono
		$presupuestos->UpdateSort($presupuestos->telefono);

		// Field idVendedor
		$presupuestos->UpdateSort($presupuestos->idVendedor);

		// Field evento
		$presupuestos->UpdateSort($presupuestos->evento);

		// Field sena
		$presupuestos->UpdateSort($presupuestos->sena);

		// Field subtotal
		$presupuestos->UpdateSort($presupuestos->subtotal);

		// Field descuento
		$presupuestos->UpdateSort($presupuestos->descuento);

		// Field total
		$presupuestos->UpdateSort($presupuestos->total);

		// Field estado
		$presupuestos->UpdateSort($presupuestos->estado);

		// Field presu_tarjeta
		$presupuestos->UpdateSort($presupuestos->presu_tarjeta);
		$presupuestos->setStartRecordNumber(1); // Reset start position
	}
	$sOrderBy = $presupuestos->getSessionOrderBy(); // Get order by from Session
	if ($sOrderBy == "") {
		if ($presupuestos->SqlOrderBy() <> "") {
			$sOrderBy = $presupuestos->SqlOrderBy();
			$presupuestos->setSessionOrderBy($sOrderBy);
			$presupuestos->id->setSort("DESC");
		}
	}
}

// Reset command based on querystring parameter cmd=
// - RESET: reset search parameters
// - RESETALL: reset search & master/detail parameters
// - RESETSORT: reset sort parameters
function ResetCmd() {
	global $sDbMasterFilter, $sDbDetailFilter, $nStartRec, $sOrderBy;
	global $presupuestos;

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
			$presupuestos->setSessionOrderBy($sOrderBy);
			$presupuestos->id->setSort("");
			$presupuestos->nombre->setSort("");
			$presupuestos->apellido->setSort("");
			$presupuestos->telefono->setSort("");
			$presupuestos->idVendedor->setSort("");
			$presupuestos->evento->setSort("");
			$presupuestos->sena->setSort("");
			$presupuestos->subtotal->setSort("");
			$presupuestos->descuento->setSort("");
			$presupuestos->total->setSort("");
			$presupuestos->estado->setSort("");
			$presupuestos->presu_tarjeta->setSort("");
		}

		// Reset start position
		$nStartRec = 1;
		$presupuestos->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Set up Starting Record parameters based on Pager Navigation
function SetUpStartRec() {
	global $nDisplayRecs, $nStartRec, $nTotalRecs, $nPageNo, $presupuestos;
	if ($nDisplayRecs == 0) return;

	// Check for a START parameter
	if (@$_GET[EW_TABLE_START_REC] <> "") {
		$nStartRec = $_GET[EW_TABLE_START_REC];
		$presupuestos->setStartRecordNumber($nStartRec);
	} elseif (@$_GET[EW_TABLE_PAGE_NO] <> "") {
		$nPageNo = $_GET[EW_TABLE_PAGE_NO];
		if (is_numeric($nPageNo)) {
			$nStartRec = ($nPageNo-1)*$nDisplayRecs+1;
			if ($nStartRec <= 0) {
				$nStartRec = 1;
			} elseif ($nStartRec >= intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1) {
				$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1;
			}
			$presupuestos->setStartRecordNumber($nStartRec);
		} else {
			$nStartRec = $presupuestos->getStartRecordNumber();
		}
	} else {
		$nStartRec = $presupuestos->getStartRecordNumber();
	}

	// Check if correct start record counter
	if (!is_numeric($nStartRec) || $nStartRec == "") { // Avoid invalid start record counter
		$nStartRec = 1; // Reset start record counter
		$presupuestos->setStartRecordNumber($nStartRec);
	} elseif (intval($nStartRec) > intval($nTotalRecs)) { // Avoid starting record > total records
		$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to last page first record
		$presupuestos->setStartRecordNumber($nStartRec);
	} elseif (($nStartRec-1) % $nDisplayRecs <> 0) {
		$nStartRec = intval(($nStartRec-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to page boundary
		$presupuestos->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Load recordset
function LoadRecordset($offset = -1, $rowcnt = -1) {
	global $conn, $presupuestos;

	// Call Recordset Selecting event
	$presupuestos->Recordset_Selecting($presupuestos->CurrentFilter);

	// Load list page sql
	$sSql = $presupuestos->SelectSQL();
	if ($offset > -1 && $rowcnt > -1) $sSql .= " LIMIT $offset, $rowcnt";

	// Load recordset
	$conn->raiseErrorFn = 'ew_ErrorFn';	
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';

	// Call Recordset Selected event
	$presupuestos->Recordset_Selected($rs);
	return $rs;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $presupuestos;
	$sFilter = $presupuestos->SqlKeyFilter();
	if (!is_numeric($presupuestos->id->CurrentValue)) {
		return FALSE; // Invalid key, exit
	}
	$sFilter = str_replace("@id@", ew_AdjustSql($presupuestos->id->CurrentValue), $sFilter); // Replace key value

	// Call Row Selecting event
	$presupuestos->Row_Selecting($sFilter);

	// Load sql based on filter
	$presupuestos->CurrentFilter = $sFilter;
	$sSql = $presupuestos->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$presupuestos->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $presupuestos;
	$presupuestos->id->setDbValue($rs->fields('id'));
	$presupuestos->nombre->setDbValue($rs->fields('nombre'));
	$presupuestos->apellido->setDbValue($rs->fields('apellido'));
	$presupuestos->telefono->setDbValue($rs->fields('telefono'));
	$presupuestos->idVendedor->setDbValue($rs->fields('idVendedor'));
	$presupuestos->evento->setDbValue($rs->fields('evento'));
	$presupuestos->presupuesto->setDbValue($rs->fields('presupuesto'));
	$presupuestos->pedido->setDbValue($rs->fields('pedido'));
	$presupuestos->sena->setDbValue($rs->fields('sena'));
	$presupuestos->subtotal->setDbValue($rs->fields('subtotal'));
	$presupuestos->descuento->setDbValue($rs->fields('descuento'));
	$presupuestos->total->setDbValue($rs->fields('total'));
	$presupuestos->estado->setDbValue($rs->fields('estado'));
	$presupuestos->presu_tarjeta->setDbValue($rs->fields('presu_tarjeta'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $presupuestos;

	// Call Row Rendering event
	$presupuestos->Row_Rendering();

	// Common render codes for all row types
	// id

	$presupuestos->id->CellCssStyle = "";
	$presupuestos->id->CellCssClass = "";

	// nombre
	$presupuestos->nombre->CellCssStyle = "";
	$presupuestos->nombre->CellCssClass = "";

	// apellido
	$presupuestos->apellido->CellCssStyle = "";
	$presupuestos->apellido->CellCssClass = "";

	// telefono
	$presupuestos->telefono->CellCssStyle = "";
	$presupuestos->telefono->CellCssClass = "";

	// idVendedor
	$presupuestos->idVendedor->CellCssStyle = "";
	$presupuestos->idVendedor->CellCssClass = "";

	// evento
	$presupuestos->evento->CellCssStyle = "";
	$presupuestos->evento->CellCssClass = "";

	// sena
	$presupuestos->sena->CellCssStyle = "";
	$presupuestos->sena->CellCssClass = "";

	// subtotal
	$presupuestos->subtotal->CellCssStyle = "";
	$presupuestos->subtotal->CellCssClass = "";

	// descuento
	$presupuestos->descuento->CellCssStyle = "";
	$presupuestos->descuento->CellCssClass = "";

	// total
	$presupuestos->total->CellCssStyle = "";
	$presupuestos->total->CellCssClass = "";

	// estado
	$presupuestos->estado->CellCssStyle = "";
	$presupuestos->estado->CellCssClass = "";

	// presu_tarjeta
	$presupuestos->presu_tarjeta->CellCssStyle = "";
	$presupuestos->presu_tarjeta->CellCssClass = "";
	if ($presupuestos->RowType == EW_ROWTYPE_VIEW) { // View row

		// id
		$presupuestos->id->ViewValue = $presupuestos->id->CurrentValue;
		$presupuestos->id->CssStyle = "";
		$presupuestos->id->CssClass = "";
		$presupuestos->id->ViewCustomAttributes = "";

		// nombre
		$presupuestos->nombre->ViewValue = $presupuestos->nombre->CurrentValue;
		$presupuestos->nombre->CssStyle = "";
		$presupuestos->nombre->CssClass = "";
		$presupuestos->nombre->ViewCustomAttributes = "";

		// apellido
		$presupuestos->apellido->ViewValue = $presupuestos->apellido->CurrentValue;
		$presupuestos->apellido->CssStyle = "";
		$presupuestos->apellido->CssClass = "";
		$presupuestos->apellido->ViewCustomAttributes = "";

		// telefono
		$presupuestos->telefono->ViewValue = $presupuestos->telefono->CurrentValue;
		$presupuestos->telefono->CssStyle = "";
		$presupuestos->telefono->CssClass = "";
		$presupuestos->telefono->ViewCustomAttributes = "";

		// idVendedor
		if (!empty($presupuestos->idVendedor->CurrentValue)) {
			$sSqlWrk = "SELECT `Vendedor` FROM `vendedores` WHERE `id` = " . ew_AdjustSql($presupuestos->idVendedor->CurrentValue) . "";
			$rswrk = $conn->Execute($sSqlWrk);
			if ($rswrk) {
				if (!$rswrk->EOF) {
					$presupuestos->idVendedor->ViewValue = $rswrk->fields('Vendedor');
				}
				$rswrk->Close();
			} else {
				$presupuestos->idVendedor->ViewValue = $presupuestos->idVendedor->CurrentValue;
			}
		} else {
			$presupuestos->idVendedor->ViewValue = NULL;
		}
		$presupuestos->idVendedor->CssStyle = "";
		$presupuestos->idVendedor->CssClass = "";
		$presupuestos->idVendedor->ViewCustomAttributes = "";

		// evento
		$presupuestos->evento->ViewValue = $presupuestos->evento->CurrentValue;
		$presupuestos->evento->CssStyle = "";
		$presupuestos->evento->CssClass = "";
		$presupuestos->evento->ViewCustomAttributes = "";

		// sena
		$presupuestos->sena->ViewValue = $presupuestos->sena->CurrentValue;
		$presupuestos->sena->ViewValue = ew_FormatCurrency($presupuestos->sena->ViewValue, 2, -2, -2, -2);
		$presupuestos->sena->CssStyle = "";
		$presupuestos->sena->CssClass = "";
		$presupuestos->sena->ViewCustomAttributes = "";

		// subtotal
		$presupuestos->subtotal->ViewValue = $presupuestos->subtotal->CurrentValue;
		$presupuestos->subtotal->ViewValue = ew_FormatCurrency($presupuestos->subtotal->ViewValue, 2, -2, -2, -2);
		$presupuestos->subtotal->CssStyle = "";
		$presupuestos->subtotal->CssClass = "";
		$presupuestos->subtotal->ViewCustomAttributes = "";

		// descuento
		$presupuestos->descuento->ViewValue = $presupuestos->descuento->CurrentValue;
		$presupuestos->descuento->ViewValue = ew_FormatCurrency($presupuestos->descuento->ViewValue, 2, -2, -2, -2);
		$presupuestos->descuento->CssStyle = "";
		$presupuestos->descuento->CssClass = "";
		$presupuestos->descuento->ViewCustomAttributes = "";

		// total
		$presupuestos->total->ViewValue = $presupuestos->total->CurrentValue;
		$presupuestos->total->ViewValue = ew_FormatCurrency($presupuestos->total->ViewValue, 2, -2, -2, -2);
		$presupuestos->total->CssStyle = "";
		$presupuestos->total->CssClass = "";
		$presupuestos->total->ViewCustomAttributes = "";

		// estado
		if (!is_null($presupuestos->estado->CurrentValue)) {
			switch ($presupuestos->estado->CurrentValue) {
				case "0":
					$presupuestos->estado->ViewValue = "Pendiente";
					break;
				case "1":
					$presupuestos->estado->ViewValue = "Pagado";
					break;
				case "2":
					$presupuestos->estado->ViewValue = "Cancelado";
					break;
				default:
					$presupuestos->estado->ViewValue = $presupuestos->estado->CurrentValue;
			}
		} else {
			$presupuestos->estado->ViewValue = NULL;
		}
		$presupuestos->estado->CssStyle = "";
		$presupuestos->estado->CssClass = "";
		$presupuestos->estado->ViewCustomAttributes = "";

		// presu_tarjeta
		if (!is_null($presupuestos->presu_tarjeta->CurrentValue)) {
			switch ($presupuestos->presu_tarjeta->CurrentValue) {
				case "0":
					$presupuestos->presu_tarjeta->ViewValue = "Contado";
					break;
				case "1":
					$presupuestos->presu_tarjeta->ViewValue = "TARJETA";
					break;
				default:
					$presupuestos->presu_tarjeta->ViewValue = $presupuestos->presu_tarjeta->CurrentValue;
			}
		} else {
			$presupuestos->presu_tarjeta->ViewValue = NULL;
		}
		$presupuestos->presu_tarjeta->CssStyle = "";
		$presupuestos->presu_tarjeta->CssClass = "";
		$presupuestos->presu_tarjeta->ViewCustomAttributes = "";

		// id
		$presupuestos->id->HrefValue = "";

		// nombre
		$presupuestos->nombre->HrefValue = "";

		// apellido
		$presupuestos->apellido->HrefValue = "";

		// telefono
		$presupuestos->telefono->HrefValue = "";

		// idVendedor
		$presupuestos->idVendedor->HrefValue = "";

		// evento
		$presupuestos->evento->HrefValue = "";

		// sena
		$presupuestos->sena->HrefValue = "";

		// subtotal
		$presupuestos->subtotal->HrefValue = "";

		// descuento
		$presupuestos->descuento->HrefValue = "";

		// total
		$presupuestos->total->HrefValue = "";

		// estado
		$presupuestos->estado->HrefValue = "";

		// presu_tarjeta
		$presupuestos->presu_tarjeta->HrefValue = "";
	} elseif ($presupuestos->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($presupuestos->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($presupuestos->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$presupuestos->Row_Rendered();
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
