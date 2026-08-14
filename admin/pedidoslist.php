<?php
define("EW_PAGE_ID", "list", TRUE); // Page ID
define("EW_TABLE_NAME", 'pedidos', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "pedidosinfo.php" ?>
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
$Security->LoadCurrentUserLevel('pedidos');
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
$pedidos->Export = @$_GET["export"]; // Get export parameter
$sExport = $pedidos->Export; // Get export parameter, used in header
$sExportFile = $pedidos->TableVar; // Get export file, used in header
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

// Get search criteria for advanced search
$sSrchAdvanced = AdvancedSearchWhere();

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
	if ($sSrchAdvanced == "") ResetAdvancedSearchParms();
	$pedidos->setSearchWhere($sSrchWhere); // Save to Session
	$nStartRec = 1; // Reset start record counter
	$pedidos->setStartRecordNumber($nStartRec);
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
$pedidos->setSessionWhere($sFilter);
$pedidos->CurrentFilter = "";

// Set Up Sorting Order
SetUpSortOrder();

// Set Return Url
$pedidos->setReturnUrl("pedidoslist.php");
?>
<?php include "header.php" ?>
<?php if ($pedidos->Export == "") { ?>
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
<script type="text/javascript">
<!--

function ew_SelectKey(elem) {
	var f = elem.form;	
	if (!f.elements["key_m[]"]) return;
	if (f.elements["key_m[]"][0]) {
		for (var i=0; i<f.elements["key_m[]"].length; i++)
			f.elements["key_m[]"][i].checked = elem.checked;	
	} else {
		f.elements["key_m[]"].checked = elem.checked;	
	}
	ew_ClickAll(elem);
}

function ew_Selected(f) {
	if (!f.elements["key_m[]"]) return false;
	if (f.elements["key_m[]"][0]) {
		for (var i=0; i<f.elements["key_m[]"].length; i++)
			if (f.elements["key_m[]"][i].checked) return true;
	} else {
		return f.elements["key_m[]"].checked;
	}
	return false;
}

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
<?php if ($pedidos->Export == "") { ?>
<?php } ?>
<?php

// Load recordset
$bExportAll = (defined("EW_EXPORT_ALL") && $pedidos->Export <> "");
$bSelectLimit = ($pedidos->Export == "" && $pedidos->SelectLimit);
if (!$bSelectLimit) $rs = LoadRecordset();
$nTotalRecs = ($bSelectLimit) ? $pedidos->SelectRecordCount() : $rs->RecordCount();
$nStartRec = 1;
if ($nDisplayRecs <= 0) $nDisplayRecs = $nTotalRecs; // Display all records
if (!$bExportAll) SetUpStartRec(); // Set up start record position
if ($bSelectLimit) $rs = LoadRecordset($nStartRec-1, $nDisplayRecs);
?>
<?php if ($pedidos->Export == "") { ?>
<?php if ($Security->CanSearch()) { ?>
<form name="fpedidoslistsrch" id="fpedidoslistsrch" action="pedidoslist.php" >
<table class="ewBasicSearch" align="center">
	<tr>
		<td><span class="phpmaker">
			<input type="text" name="<?php echo EW_TABLE_BASIC_SEARCH ?>" id="<?php echo EW_TABLE_BASIC_SEARCH ?>" size="20" value="<?php echo $pedidos->getBasicSearchKeyword() ?>">
			<input type="Submit" name="Submit" id="Submit" value="Buscar (*)">&nbsp;
			<a href="pedidoslist.php?cmd=reset">Mostrar todo</a>&nbsp;
			<a href="pedidossrch.php">Búsqueda avanzada</a>&nbsp;
		</span></td>
	</tr>
	<tr>
	<td><span class="phpmaker"><input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="" <?php if ($pedidos->getBasicSearchType() == "") { ?>checked<?php } ?>>Frase exacta&nbsp;&nbsp;<input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="AND" <?php if ($pedidos->getBasicSearchType() == "AND") { ?>checked<?php } ?>>Todas las palabras&nbsp;&nbsp;<input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="OR" <?php if ($pedidos->getBasicSearchType() == "OR") { ?>checked<?php } ?>>Cualquier palabra</span></td>
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
<form method="post" name="fpedidoslist" id="fpedidoslist">
<?php if ($nTotalRecs > 0) { ?>
<div id="whiteback" style="display: inline-block; width:auto">
<?php if ($pedidos->Export == "") { ?>
<table class="subheader" align="center" style="width:100%">
	<tr><td><span class="phpmaker">
<?php if ($nTotalRecs > 0) { ?>
<?php if ($Security->CanDelete()) { ?>
<div class="button-delete"><a href="" onClick="if (!ew_Selected(document.fpedidoslist)) alert('No se seleccionaron registros'); else {document.fpedidoslist.action='pedidosdelete.php';document.fpedidoslist.encoding='application/x-www-form-urlencoded';document.fpedidoslist.submit();};return false;">Borrar</a></div>
<?php } ?>
<?php } ?>
<div class="tablername" style="margin-top:7px">Pedidos</div>
	</span></td></tr>
</table>
<?php } ?>
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
	$OptionCnt++; // multi select
}
?>
	<!-- Table header -->
	<tr class="ewTableHeader">
		<td valign="top">
<?php if ($pedidos->Export <> "") { ?>
Número
<?php } else { ?>
	<a href="pedidoslist.php?order=<?php echo urlencode('id') ?>&ordertype=<?php echo $pedidos->id->ReverseSort() ?>">Número<?php if ($pedidos->id->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($pedidos->id->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($pedidos->Export <> "") { ?>
Fecha
<?php } else { ?>
	<a href="pedidoslist.php?order=<?php echo urlencode('Fecha') ?>&ordertype=<?php echo $pedidos->Fecha->ReverseSort() ?>">Fecha<?php if ($pedidos->Fecha->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($pedidos->Fecha->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($pedidos->Export <> "") { ?>
Estado
<?php } else { ?>
	<a href="pedidoslist.php?order=<?php echo urlencode('estado') ?>&ordertype=<?php echo $pedidos->estado->ReverseSort() ?>">Estado<?php if ($pedidos->estado->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($pedidos->estado->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($pedidos->Export <> "") { ?>
Total
<?php } else { ?>
	<a href="pedidoslist.php?order=<?php echo urlencode('total') ?>&ordertype=<?php echo $pedidos->total->ReverseSort() ?>">Total<?php if ($pedidos->total->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($pedidos->total->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($pedidos->Export <> "") { ?>
Nombre
<?php } else { ?>
	<a href="pedidoslist.php?order=<?php echo urlencode('nombre') ?>&ordertype=<?php echo $pedidos->nombre->ReverseSort() ?>">Nombre&nbsp;(*)<?php if ($pedidos->nombre->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($pedidos->nombre->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($pedidos->Export <> "") { ?>
Apellido
<?php } else { ?>
	<a href="pedidoslist.php?order=<?php echo urlencode('apellido') ?>&ordertype=<?php echo $pedidos->apellido->ReverseSort() ?>">Apellido&nbsp;(*)<?php if ($pedidos->apellido->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($pedidos->apellido->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($pedidos->Export <> "") { ?>
Teléfono
<?php } else { ?>
	<a href="pedidoslist.php?order=<?php echo urlencode('telefono') ?>&ordertype=<?php echo $pedidos->telefono->ReverseSort() ?>">Teléfono&nbsp;(*)<?php if ($pedidos->telefono->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($pedidos->telefono->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($pedidos->Export <> "") { ?>
Vendedor
<?php } else { ?>
	<a href="pedidoslist.php?order=<?php echo urlencode('idVendedor') ?>&ordertype=<?php echo $pedidos->idVendedor->ReverseSort() ?>">Vendedor<?php if ($pedidos->idVendedor->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($pedidos->idVendedor->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($pedidos->Export <> "") { ?>
Evento
<?php } else { ?>
	<a href="pedidoslist.php?order=<?php echo urlencode('Evento') ?>&ordertype=<?php echo $pedidos->Evento->ReverseSort() ?>">Evento&nbsp;(*)<?php if ($pedidos->Evento->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($pedidos->Evento->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
<?php if ($pedidos->Export == "") { ?>
<?php if ($Security->CanView()) { ?>
<td nowrap>&nbsp;</td>
<?php } ?>
<?php if ($Security->CanEdit()) { ?>
<td nowrap width="1" style="padding:0">&nbsp;</td>
<?php } ?>
<td nowrap width="1" style="padding:0">&nbsp;</td>
<?php if ($Security->CanDelete()) { ?>
<td nowrap><input type="checkbox" class="phpmaker" onClick="ew_SelectKey(this);"></td>
<?php } ?>
<?php } ?>
	</tr>
<?php
if (defined("EW_EXPORT_ALL") && $pedidos->Export <> "") {
	$nStopRec = $nTotalRecs;
} else {
	$nStopRec = $nStartRec + $nDisplayRecs - 1; // Set the last record to display
}
$nRecCount = $nStartRec - 1;
if (!$rs->EOF) {
	$rs->MoveFirst();
	if (!$pedidos->SelectLimit) $rs->Move($nStartRec - 1); // Move to first record directly
}
$RowCnt = 0;
while (!$rs->EOF && $nRecCount < $nStopRec) {
	$nRecCount++;
	if (intval($nRecCount) >= intval($nStartRec)) {
		$RowCnt++;

	// Init row class and style
	$pedidos->CssClass = "ewTableRow";
	$pedidos->CssStyle = "";

	// Init row event
	$pedidos->RowClientEvents = "onmouseover='ew_MouseOver(this);' onmouseout='ew_MouseOut(this);' onclick='ew_Click(this);'";

	// Display alternate color for rows
	if ($RowCnt % 2 == 0) {
		$pedidos->CssClass = "ewTableAltRow";
	}
	LoadRowValues($rs); // Load row values
	$pedidos->RowType = EW_ROWTYPE_VIEW; // Render view
	RenderRow();
?>
	<!-- Table body -->
	<tr<?php echo $pedidos->DisplayAttributes() ?>>
		<!-- id -->
		<td<?php echo $pedidos->id->CellAttributes() ?>>
<div<?php echo $pedidos->id->ViewAttributes() ?> style="white-space: nowrap">
	<a href="../ajax/pdf?id=<?php echo $pedidos->id->ViewValue ?>" target="_blank"><?php echo $pedidos->id->ViewValue ?></a>
	
	<?if ($pedidos->idPresupuesto->ViewValue) {?>
		&nbsp;&nbsp;(<a target="_blank" href="..//ajax/pdf_presu?id=<?= $pedidos->idPresupuesto->ViewValue ?>">pres: <?= $pedidos->idPresupuesto->ViewValue ?></a>)
	<?}?>

	<?if ($pedidos->idFotolibro->ViewValue) {?>
		&nbsp;&nbsp;(<a target="_blank" href="..//ajax/pdf_photobook?id=<?= $pedidos->idFotolibro->ViewValue ?>">fotolibro: <?= $pedidos->idFotolibro->ViewValue ?></a>)
	<?}?>

	
</div>
</td>
		<!-- Fecha -->
		<td<?php echo $pedidos->Fecha->CellAttributes() ?>>
<div<?php echo $pedidos->Fecha->ViewAttributes() ?>><?php echo $pedidos->Fecha->ViewValue ?></div>
</td>
		<!-- estado -->
		<td<?php echo $pedidos->estado->CellAttributes() ?>>
<div<?php echo $pedidos->estado->ViewAttributes() ?>><?php echo $pedidos->estado->ViewValue ?></div>
	<?if ($pedidos->estado->CurrentValue == 1) {?>
<div><strong><?php echo $pedidos->ped_tarjeta->ViewValue ?></strong></div>
	<?}?>
</td>
		<!-- total -->
		<td style="text-align:right; white-space:nowrap">
<div>
<?if ($pedidos->Descuento->CurrentValue || $pedidos->sena->CurrentValue) {?>
	<div style="margin-bottom:4px;">
		<?php echo $pedidos->total->ViewValue ?> 
		
		<?if ($pedidos->Descuento->CurrentValue) {?>
		- <span style="color:#a00000" title="Descuento"><?= $pedidos->Descuento->ViewValue ?></span>
		<?}?>
		
		<?if ($pedidos->sena->CurrentValue) {?>
		- <span style="color:#00a000" title="Seña"><?= $pedidos->sena->ViewValue ?></span>
		<?}?>
		= 
	</div>
<?}?>
<div style="font-weight:bold; "><?php echo ew_FormatCurrency($pedidos->total->CurrentValue - $pedidos->Descuento->CurrentValue - $pedidos->sena->CurrentValue, 2, -2, -2, -2); ?></div>
</div>
</td>
		<!-- nombre -->
		<td<?php echo $pedidos->nombre->CellAttributes() ?>>
<div<?php echo $pedidos->nombre->ViewAttributes() ?>><?php echo $pedidos->nombre->ViewValue ?></div>
</td>
		<!-- apellido -->
		<td<?php echo $pedidos->apellido->CellAttributes() ?>>
<div<?php echo $pedidos->apellido->ViewAttributes() ?>><?php echo $pedidos->apellido->ViewValue ?></div>
</td>
		<!-- telefono -->
		<td<?php echo $pedidos->telefono->CellAttributes() ?>>
<div<?php echo $pedidos->telefono->ViewAttributes() ?>><?php echo $pedidos->telefono->ViewValue ?></div>
</td>
		<!-- idVendedor -->
		<td<?php echo $pedidos->idVendedor->CellAttributes() ?>>
<div<?php echo $pedidos->idVendedor->ViewAttributes() ?>><?php echo $pedidos->idVendedor->ViewValue ?></div>
</td>
		<!-- Evento -->
		<td<?php echo $pedidos->Evento->CellAttributes() ?>>
<div<?php echo $pedidos->Evento->ViewAttributes() ?>><?php echo $pedidos->Evento->ViewValue ?></div>
</td>
<?php if ($pedidos->Export == "") { ?>
<?php if ($Security->CanView()) { ?>
<td nowrap width="1" style="padding:0"><span class="phpmaker">
<div class="button-view"><a href="<?php echo $pedidos->ViewUrl() ?>">Vista</a></div>
</span></td>
<?php } ?>
<?php if ($Security->CanEdit()) { ?>
<td nowrap width="1" style="padding:0"><span class="phpmaker">
<div class="button-edit"><a href="<?php echo $pedidos->EditUrl() ?>">Editar</a></div>
</span></td>
<?php } ?>


<td nowrap width="1" style="padding:0"><span class="phpmaker">
<div class="button-edit"><a href="../?process_order=<?=$pedidos->id->CurrentValue?>" target="_blank">Procesar</a></div>
</span></td>

<?php if ($Security->CanDelete()) { ?>
<td nowrap><span class="phpmaker">
<input type="checkbox" name="key_m[]" id="key_m[]" value="<?php echo ew_HtmlEncode($pedidos->id->CurrentValue) ?>" class="phpmaker" onclick='ew_ClickMultiCheckbox(this);'>
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
<?php if ($pedidos->Export == "") { ?>
<form action="pedidoslist.php" name="ewpagerform" id="ewpagerform">
<table border="0" cellspacing="0" cellpadding="0" align="center" id="pagertable">
	<tr>
		<td nowrap align="center">
<span class="phpmaker">
<?php if (!isset($Pager)) $Pager = new cNumericPager($nStartRec, $nDisplayRecs, $nTotalRecs, $nRecRange) ?>
<?php if ($Pager->RecordCount > 0) { ?>
	<?php if ($Pager->FirstButton->Enabled) { ?>
	<a href="pedidoslist.php?start=<?php echo $Pager->FirstButton->Start ?>"><b>Primero</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->PrevButton->Enabled) { ?>
	<a href="pedidoslist.php?start=<?php echo $Pager->PrevButton->Start ?>"><b>Anterior</b></a>&nbsp;
	<?php } ?>
	<?php foreach ($Pager->Items as $PagerItem) { ?>
		<?php if ($PagerItem->Enabled) { ?><a href="pedidoslist.php?start=<?php echo $PagerItem->Start ?>"><?php } ?><b><?php echo $PagerItem->Text ?></b><?php if ($PagerItem->Enabled) { ?></a><?php } ?>&nbsp;
	<?php } ?>
	<?php if ($Pager->NextButton->Enabled) { ?>
	<a href="pedidoslist.php?start=<?php echo $Pager->NextButton->Start ?>"><b>Próximo</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->LastButton->Enabled) { ?>
	<a href="pedidoslist.php?start=<?php echo $Pager->LastButton->Start ?>"><b>Último</b></a>&nbsp;
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
<?php if ($pedidos->Export == "") { ?>
<?php } ?>
<?php if ($pedidos->Export == "") { ?>
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

// Return Advanced Search Where based on QueryString parameters
function AdvancedSearchWhere() {
	global $Security, $pedidos;
	$sWhere = "";
	if (!$Security->CanSearch()) return "";

	// Field id
	BuildSearchSql($sWhere, $pedidos->id, @$_GET["x_id"], @$_GET["z_id"], @$_GET["v_id"], @$_GET["y_id"], @$_GET["w_id"]);

	// Field estado
	BuildSearchSql($sWhere, $pedidos->estado, @$_GET["x_estado"], @$_GET["z_estado"], @$_GET["v_estado"], @$_GET["y_estado"], @$_GET["w_estado"]);

	// Field ped_tarjeta
	BuildSearchSql($sWhere, $pedidos->ped_tarjeta, @$_GET["x_ped_tarjeta"], @$_GET["z_ped_tarjeta"], @$_GET["v_ped_tarjeta"], @$_GET["y_ped_tarjeta"], @$_GET["w_ped_tarjeta"]);

	// Field nombre
	BuildSearchSql($sWhere, $pedidos->nombre, @$_GET["x_nombre"], @$_GET["z_nombre"], @$_GET["v_nombre"], @$_GET["y_nombre"], @$_GET["w_nombre"]);

	// Field apellido
	BuildSearchSql($sWhere, $pedidos->apellido, @$_GET["x_apellido"], @$_GET["z_apellido"], @$_GET["v_apellido"], @$_GET["y_apellido"], @$_GET["w_apellido"]);

	// Field idVendedor
	BuildSearchSql($sWhere, $pedidos->idVendedor, @$_GET["x_idVendedor"], @$_GET["z_idVendedor"], @$_GET["v_idVendedor"], @$_GET["y_idVendedor"], @$_GET["w_idVendedor"]);

	// Field Evento
	BuildSearchSql($sWhere, $pedidos->Evento, @$_GET["x_Evento"], @$_GET["z_Evento"], @$_GET["v_Evento"], @$_GET["y_Evento"], @$_GET["w_Evento"]);

	// Field idPresupuesto
	BuildSearchSql($sWhere, $pedidos->idPresupuesto, @$_GET["x_idPresupuesto"], @$_GET["z_idPresupuesto"], @$_GET["v_idPresupuesto"], @$_GET["y_idPresupuesto"], @$_GET["w_idPresupuesto"]);

	// Field sena
	BuildSearchSql($sWhere, $pedidos->sena, @$_GET["x_sena"], @$_GET["z_sena"], @$_GET["v_sena"], @$_GET["y_sena"], @$_GET["w_sena"]);

	//AdvancedSearchWhere = sWhere
	// Set up search parm

	if ($sWhere <> "") {

		// Field id
		SetSearchParm($pedidos->id, @$_GET["x_id"], @$_GET["z_id"], @$_GET["v_id"], @$_GET["y_id"], @$_GET["w_id"]);

		// Field estado
		SetSearchParm($pedidos->estado, @$_GET["x_estado"], @$_GET["z_estado"], @$_GET["v_estado"], @$_GET["y_estado"], @$_GET["w_estado"]);

		// Field ped_tarjeta
		SetSearchParm($pedidos->ped_tarjeta, @$_GET["x_ped_tarjeta"], @$_GET["z_ped_tarjeta"], @$_GET["v_ped_tarjeta"], @$_GET["y_ped_tarjeta"], @$_GET["w_ped_tarjeta"]);

		// Field nombre
		SetSearchParm($pedidos->nombre, @$_GET["x_nombre"], @$_GET["z_nombre"], @$_GET["v_nombre"], @$_GET["y_nombre"], @$_GET["w_nombre"]);

		// Field apellido
		SetSearchParm($pedidos->apellido, @$_GET["x_apellido"], @$_GET["z_apellido"], @$_GET["v_apellido"], @$_GET["y_apellido"], @$_GET["w_apellido"]);

		// Field idVendedor
		SetSearchParm($pedidos->idVendedor, @$_GET["x_idVendedor"], @$_GET["z_idVendedor"], @$_GET["v_idVendedor"], @$_GET["y_idVendedor"], @$_GET["w_idVendedor"]);

		// Field Evento
		SetSearchParm($pedidos->Evento, @$_GET["x_Evento"], @$_GET["z_Evento"], @$_GET["v_Evento"], @$_GET["y_Evento"], @$_GET["w_Evento"]);

		// Field idPresupuesto
		SetSearchParm($pedidos->idPresupuesto, @$_GET["x_idPresupuesto"], @$_GET["z_idPresupuesto"], @$_GET["v_idPresupuesto"], @$_GET["y_idPresupuesto"], @$_GET["w_idPresupuesto"]);

		// Field sena
		SetSearchParm($pedidos->sena, @$_GET["x_sena"], @$_GET["z_sena"], @$_GET["v_sena"], @$_GET["y_sena"], @$_GET["w_sena"]);
	}
	return $sWhere;
}

// Build search sql
function BuildSearchSql(&$Where, &$Fld, $FldVal, $FldOpr, $FldCond, $FldVal2, $FldOpr2) {
	$sWrk = "";
	$FldParm = substr($Fld->FldVar, 2);
	$FldVal = ew_StripSlashes($FldVal);
	if (is_array($FldVal)) $FldVal = implode(",", $FldVal);
	$FldVal2 = ew_StripSlashes($FldVal2);
	if (is_array($FldVal2)) $FldVal2 = implode(",", $FldVal2);
	$FldOpr = strtoupper(trim($FldOpr));
	if ($FldOpr == "") $FldOpr = "=";
	$FldOpr2 = strtoupper(trim($FldOpr2));
	if ($FldOpr2 == "") $FldOpr2 = "=";
	if ($Fld->FldDataType == EW_DATATYPE_BOOLEAN) {
		if ($FldVal <> "") $FldVal = ($FldVal == "1") ? $Fld->TrueValue : $Fld->FalseValue;
		if ($FldVal2 <> "") $FldVal2 = ($FldVal2 == "1") ? $Fld->TrueValue : $Fld->FalseValue;
	} elseif ($Fld->FldDataType == EW_DATATYPE_DATE) {
		if ($FldVal <> "") $FldVal = ew_UnFormatDateTime($FldVal, $Fld->FldDateTimeFormat);
		if ($FldVal2 <> "") $FldVal2 = ew_UnFormatDateTime($FldVal2, $Fld->FldDateTimeFormat);
	}
	if ($FldOpr == "BETWEEN") {
		$IsValidValue = (($Fld->FldDataType <> EW_DATATYPE_NUMBER) ||
			($Fld->FldDataType == EW_DATATYPE_NUMBER && is_numeric($FldVal) && is_numeric($FldVal2)));
		if ($FldVal <> "" && $FldVal2 <> "" && $IsValidValue) {
			$sWrk = $Fld->FldExpression . " BETWEEN " . ew_QuotedValue($FldVal, $Fld->FldDataType) .
				" AND " . ew_QuotedValue($FldVal2, $Fld->FldDataType);
		}
	} elseif ($FldOpr == "IS NULL" || $FldOpr == "IS NOT NULL") {
		$sWrk = $Fld->FldExpression . " " . $FldOpr;
	} else {
		$IsValidValue = (($Fld->FldDataType <> EW_DATATYPE_NUMBER) ||
			($Fld->FldDataType == EW_DATATYPE_NUMBER && is_numeric($FldVal)));
		if ($FldVal <> "" && $IsValidValue && ew_IsValidOpr($FldOpr, $Fld->FldDataType)) {
			$sWrk = $Fld->FldExpression . SearchString($FldOpr, $FldVal, $Fld->FldDataType);
		}
		$IsValidValue = (($Fld->FldDataType <> EW_DATATYPE_NUMBER) ||
			($Fld->FldDataType == EW_DATATYPE_NUMBER && is_numeric($FldVal2)));
		if ($FldVal2 <> "" && $IsValidValue && ew_IsValidOpr($FldOpr2, $Fld->FldDataType)) {
			if ($sWrk <> "") {
				$sWrk .= " " . (($FldCond=="OR")?"OR":"AND") . " ";
			}
			$sWrk .= $Fld->FldExpression . SearchString($FldOpr2, $FldVal2, $Fld->FldDataType);
		}
	}
	if ($sWrk <> "") {
		if ($Where <> "") $Where .= " AND ";
		$Where .= "(" . $sWrk . ")";
	}
}

// Return search string
function SearchString($FldOpr, $FldVal, $FldType) {
	if ($FldOpr == "LIKE" || $FldOpr == "NOT LIKE") {
		return " " . $FldOpr . " " . ew_QuotedValue("%" . $FldVal . "%", $FldType);
	} elseif ($FldOpr == "STARTS WITH") {
		return " LIKE " . ew_QuotedValue($FldVal . "%", $FldType);
	} else {
		return " " . $FldOpr . " " . ew_QuotedValue($FldVal, $FldType);
	}
}

// Set search parm
function SetSearchParm($Fld, $FldVal, $FldOpr, $FldCond, $FldVal2, $FldOpr2) {
	global $pedidos;
	$FldParm = substr($Fld->FldVar, 2);
	$FldVal = ew_StripSlashes($FldVal);
	if (is_array($FldVal)) $FldVal = implode(",", $FldVal);
	$FldVal2 = ew_StripSlashes($FldVal2);
	if (is_array($FldVal2)) $FldVal2 = implode(",", $FldVal2);
	$pedidos->setAdvancedSearch("x_" . $FldParm, $FldVal);
	$pedidos->setAdvancedSearch("z_" . $FldParm, $FldOpr);
	$pedidos->setAdvancedSearch("v_" . $FldParm, $FldCond);
	$pedidos->setAdvancedSearch("y_" . $FldParm, $FldVal2);
	$pedidos->setAdvancedSearch("w_" . $FldParm, $FldOpr2);
}

// Return Basic Search sql
function BasicSearchSQL($Keyword) {
	$sKeyword = ew_AdjustSql($Keyword);
	$sql = "";
	$sql .= "`nombre` LIKE '%" . $sKeyword . "%' OR ";
	$sql .= "`apellido` LIKE '%" . $sKeyword . "%' OR ";
	$sql .= "`telefono` LIKE '%" . $sKeyword . "%' OR ";
	$sql .= "`Evento` LIKE '%" . $sKeyword . "%' OR ";
	$sql .= "`descripcion` LIKE '%" . $sKeyword . "%' OR ";
	$sql .= "`pedido` LIKE '%" . $sKeyword . "%' OR ";
	$sql .= "`extra` LIKE '%" . $sKeyword . "%' OR ";
	if (substr($sql, -4) == " OR ") $sql = substr($sql, 0, strlen($sql)-4);
	return $sql;
}

// Return Basic Search Where based on search keyword and type
function BasicSearchWhere() {
	global $Security, $pedidos;
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
		$pedidos->setBasicSearchKeyword($sSearchKeyword);
		$pedidos->setBasicSearchType($sSearchType);
	}
	return $sSearchStr;
}

// Clear all search parameters
function ResetSearchParms() {

	// Clear search where
	global $pedidos;
	$sSrchWhere = "";
	$pedidos->setSearchWhere($sSrchWhere);

	// Clear basic search parameters
	ResetBasicSearchParms();

	// Clear advanced search parameters
	ResetAdvancedSearchParms();
}

// Clear all basic search parameters
function ResetBasicSearchParms() {

	// Clear basic search parameters
	global $pedidos;
	$pedidos->setBasicSearchKeyword("");
	$pedidos->setBasicSearchType("");
}

// Clear all advanced search parameters
function ResetAdvancedSearchParms() {

	// Clear advanced search parameters
	global $pedidos;
	$pedidos->setAdvancedSearch("x_id", "");
	$pedidos->setAdvancedSearch("x_estado", "");
	$pedidos->setAdvancedSearch("x_ped_tarjeta", "");
	$pedidos->setAdvancedSearch("x_nombre", "");
	$pedidos->setAdvancedSearch("x_apellido", "");
	$pedidos->setAdvancedSearch("x_idVendedor", "");
	$pedidos->setAdvancedSearch("x_Evento", "");
	$pedidos->setAdvancedSearch("x_idPresupuesto", "");
	$pedidos->setAdvancedSearch("x_sena", "");
}

// Restore all search parameters
function RestoreSearchParms() {
	global $sSrchWhere, $pedidos;
	$sSrchWhere = $pedidos->getSearchWhere();

	// Restore advanced search settings
	RestoreAdvancedSearchParms();
}

// Restore all advanced search parameters
function RestoreAdvancedSearchParms() {

	// Restore advanced search parms
	global $pedidos;
	 $pedidos->id->AdvancedSearch->SearchValue = $pedidos->getAdvancedSearch("x_id");
	 $pedidos->estado->AdvancedSearch->SearchValue = $pedidos->getAdvancedSearch("x_estado");
	 $pedidos->ped_tarjeta->AdvancedSearch->SearchValue = $pedidos->getAdvancedSearch("x_ped_tarjeta");
	 $pedidos->nombre->AdvancedSearch->SearchValue = $pedidos->getAdvancedSearch("x_nombre");
	 $pedidos->apellido->AdvancedSearch->SearchValue = $pedidos->getAdvancedSearch("x_apellido");
	 $pedidos->idVendedor->AdvancedSearch->SearchValue = $pedidos->getAdvancedSearch("x_idVendedor");
	 $pedidos->Evento->AdvancedSearch->SearchValue = $pedidos->getAdvancedSearch("x_Evento");
	 $pedidos->idPresupuesto->AdvancedSearch->SearchValue = $pedidos->getAdvancedSearch("x_idPresupuesto");
	 $pedidos->sena->AdvancedSearch->SearchValue = $pedidos->getAdvancedSearch("x_sena");
}

// Set up Sort parameters based on Sort Links clicked
function SetUpSortOrder() {
	global $pedidos;

	// Check for an Order parameter
	if (@$_GET["order"] <> "") {
		$pedidos->CurrentOrder = ew_StripSlashes(@$_GET["order"]);
		$pedidos->CurrentOrderType = @$_GET["ordertype"];

		// Field id
		$pedidos->UpdateSort($pedidos->id);

		// Field Fecha
		$pedidos->UpdateSort($pedidos->Fecha);

		// Field estado
		$pedidos->UpdateSort($pedidos->estado);

		// Field ped_tarjeta
		$pedidos->UpdateSort($pedidos->ped_tarjeta);

		// Field total
		$pedidos->UpdateSort($pedidos->total);

		// Field nombre
		$pedidos->UpdateSort($pedidos->nombre);

		// Field apellido
		$pedidos->UpdateSort($pedidos->apellido);

		// Field telefono
		$pedidos->UpdateSort($pedidos->telefono);

		// Field idVendedor
		$pedidos->UpdateSort($pedidos->idVendedor);

		// Field Evento
		$pedidos->UpdateSort($pedidos->Evento);

		// Field idPresupuesto
		$pedidos->UpdateSort($pedidos->idPresupuesto);

		// Field sena
		$pedidos->UpdateSort($pedidos->sena);
		$pedidos->setStartRecordNumber(1); // Reset start position
	}
	$sOrderBy = $pedidos->getSessionOrderBy(); // Get order by from Session
	if ($sOrderBy == "") {
		if ($pedidos->SqlOrderBy() <> "") {
			$sOrderBy = $pedidos->SqlOrderBy();
			$pedidos->setSessionOrderBy($sOrderBy);
			$pedidos->Fecha->setSort("DESC");
		}
	}
}

// Reset command based on querystring parameter cmd=
// - RESET: reset search parameters
// - RESETALL: reset search & master/detail parameters
// - RESETSORT: reset sort parameters
function ResetCmd() {
	global $sDbMasterFilter, $sDbDetailFilter, $nStartRec, $sOrderBy;
	global $pedidos;

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
			$pedidos->setSessionOrderBy($sOrderBy);
			$pedidos->id->setSort("");
			$pedidos->Fecha->setSort("");
			$pedidos->estado->setSort("");
			$pedidos->ped_tarjeta->setSort("");
			$pedidos->total->setSort("");
			$pedidos->nombre->setSort("");
			$pedidos->apellido->setSort("");
			$pedidos->telefono->setSort("");
			$pedidos->idVendedor->setSort("");
			$pedidos->Evento->setSort("");
			$pedidos->idPresupuesto->setSort("");
			$pedidos->sena->setSort("");
		}

		// Reset start position
		$nStartRec = 1;
		$pedidos->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Set up Starting Record parameters based on Pager Navigation
function SetUpStartRec() {
	global $nDisplayRecs, $nStartRec, $nTotalRecs, $nPageNo, $pedidos;
	if ($nDisplayRecs == 0) return;

	// Check for a START parameter
	if (@$_GET[EW_TABLE_START_REC] <> "") {
		$nStartRec = $_GET[EW_TABLE_START_REC];
		$pedidos->setStartRecordNumber($nStartRec);
	} elseif (@$_GET[EW_TABLE_PAGE_NO] <> "") {
		$nPageNo = $_GET[EW_TABLE_PAGE_NO];
		if (is_numeric($nPageNo)) {
			$nStartRec = ($nPageNo-1)*$nDisplayRecs+1;
			if ($nStartRec <= 0) {
				$nStartRec = 1;
			} elseif ($nStartRec >= intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1) {
				$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1;
			}
			$pedidos->setStartRecordNumber($nStartRec);
		} else {
			$nStartRec = $pedidos->getStartRecordNumber();
		}
	} else {
		$nStartRec = $pedidos->getStartRecordNumber();
	}

	// Check if correct start record counter
	if (!is_numeric($nStartRec) || $nStartRec == "") { // Avoid invalid start record counter
		$nStartRec = 1; // Reset start record counter
		$pedidos->setStartRecordNumber($nStartRec);
	} elseif (intval($nStartRec) > intval($nTotalRecs)) { // Avoid starting record > total records
		$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to last page first record
		$pedidos->setStartRecordNumber($nStartRec);
	} elseif (($nStartRec-1) % $nDisplayRecs <> 0) {
		$nStartRec = intval(($nStartRec-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to page boundary
		$pedidos->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Load recordset
function LoadRecordset($offset = -1, $rowcnt = -1) {
	global $conn, $pedidos;

	// Call Recordset Selecting event
	$pedidos->Recordset_Selecting($pedidos->CurrentFilter);

	// Load list page sql
	$sSql = $pedidos->SelectSQL();
	if ($offset > -1 && $rowcnt > -1) $sSql .= " LIMIT $offset, $rowcnt";

	// Load recordset
	$conn->raiseErrorFn = 'ew_ErrorFn';	
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';

	// Call Recordset Selected event
	$pedidos->Recordset_Selected($rs);
	return $rs;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $pedidos;
	$sFilter = $pedidos->SqlKeyFilter();
	if (!is_numeric($pedidos->id->CurrentValue)) {
		return FALSE; // Invalid key, exit
	}
	$sFilter = str_replace("@id@", ew_AdjustSql($pedidos->id->CurrentValue), $sFilter); // Replace key value

	// Call Row Selecting event
	$pedidos->Row_Selecting($sFilter);

	// Load sql based on filter
	$pedidos->CurrentFilter = $sFilter;
	$sSql = $pedidos->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$pedidos->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $pedidos;
	$pedidos->id->setDbValue($rs->fields('id'));
	$pedidos->Fecha->setDbValue($rs->fields('Fecha'));
	$pedidos->estado->setDbValue($rs->fields('estado'));
	$pedidos->ped_tarjeta->setDbValue($rs->fields('ped_tarjeta'));
	$pedidos->total->setDbValue($rs->fields('total'));
	$pedidos->Descuento->setDbValue($rs->fields('Descuento'));
	$pedidos->nombre->setDbValue($rs->fields('nombre'));
	$pedidos->apellido->setDbValue($rs->fields('apellido'));
	$pedidos->telefono->setDbValue($rs->fields('telefono'));
	$pedidos->idVendedor->setDbValue($rs->fields('idVendedor'));
	$pedidos->Evento->setDbValue($rs->fields('Evento'));
	$pedidos->descripcion->setDbValue($rs->fields('descripcion'));
	$pedidos->pedido->setDbValue($rs->fields('pedido'));
	$pedidos->extra->setDbValue($rs->fields('extra'));
	$pedidos->idPresupuesto->setDbValue($rs->fields('idPresupuesto'));
	$pedidos->idFotolibro->setDbValue($rs->fields('idFotolibro'));
	$pedidos->sena->setDbValue($rs->fields('sena'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $pedidos;

	// Call Row Rendering event
	$pedidos->Row_Rendering();

	// Common render codes for all row types
	// id

	$pedidos->id->CellCssStyle = "";
	$pedidos->id->CellCssClass = "";

	// Fecha
	$pedidos->Fecha->CellCssStyle = "";
	$pedidos->Fecha->CellCssClass = "";

	// estado
	$pedidos->estado->CellCssStyle = "";
	$pedidos->estado->CellCssClass = "";

	// ped_tarjeta
	$pedidos->ped_tarjeta->CellCssStyle = "";
	$pedidos->ped_tarjeta->CellCssClass = "";

	// total
	$pedidos->total->CellCssStyle = "";
	$pedidos->total->CellCssClass = "";

	// nombre
	$pedidos->nombre->CellCssStyle = "";
	$pedidos->nombre->CellCssClass = "";

	// apellido
	$pedidos->apellido->CellCssStyle = "";
	$pedidos->apellido->CellCssClass = "";

	// telefono
	$pedidos->telefono->CellCssStyle = "";
	$pedidos->telefono->CellCssClass = "";

	// idVendedor
	$pedidos->idVendedor->CellCssStyle = "";
	$pedidos->idVendedor->CellCssClass = "";

	// Evento
	$pedidos->Evento->CellCssStyle = "";
	$pedidos->Evento->CellCssClass = "";

	// idPresupuesto
	$pedidos->idPresupuesto->CellCssStyle = "";
	$pedidos->idPresupuesto->CellCssClass = "";

	// idFotolibro
	$pedidos->idFotolibro->CellCssStyle = "";
	$pedidos->idFotolibro->CellCssClass = "";

	// sena
	$pedidos->sena->CellCssStyle = "";
	$pedidos->sena->CellCssClass = "";
	if ($pedidos->RowType == EW_ROWTYPE_VIEW) { // View row

		// id
		$pedidos->id->ViewValue = $pedidos->id->CurrentValue;
		$pedidos->id->CssStyle = "";
		$pedidos->id->CssClass = "";
		$pedidos->id->ViewCustomAttributes = "";

		// Fecha
		$pedidos->Fecha->ViewValue = $pedidos->Fecha->CurrentValue;
		$pedidos->Fecha->ViewValue = ew_FormatDateTime($pedidos->Fecha->ViewValue, 7);
		$pedidos->Fecha->CssStyle = "";
		$pedidos->Fecha->CssClass = "";
		$pedidos->Fecha->ViewCustomAttributes = "";

		// estado
		if (!is_null($pedidos->estado->CurrentValue)) {
			switch ($pedidos->estado->CurrentValue) {
				case "0":
					$pedidos->estado->ViewValue = '<span style="color:#a00000">Pendiente</span>';
					break;
				case "1":
					$pedidos->estado->ViewValue = '<span style="color:#00a000">Pagado</span>';
					break;
				case "2":
					$pedidos->estado->ViewValue = "Cancelado";
					break;
				default:
					$pedidos->estado->ViewValue = $pedidos->estado->CurrentValue;
			}
		} else {
			$pedidos->estado->ViewValue = NULL;
		}
		$pedidos->estado->CssStyle = "";
		$pedidos->estado->CssClass = "";
		$pedidos->estado->ViewCustomAttributes = "";

		// ped_tarjeta
		if (!is_null($pedidos->ped_tarjeta->CurrentValue)) {
			switch ($pedidos->ped_tarjeta->CurrentValue) {
				case "0":
					$pedidos->ped_tarjeta->ViewValue = "Contado";
					break;
				case "1":
					$pedidos->ped_tarjeta->ViewValue = "TARJETA";
					break;
				default:
					$pedidos->ped_tarjeta->ViewValue = $pedidos->ped_tarjeta->CurrentValue;
			}
		} else {
			$pedidos->ped_tarjeta->ViewValue = NULL;
		}
		$pedidos->ped_tarjeta->CssStyle = "";
		$pedidos->ped_tarjeta->CssClass = "";
		$pedidos->ped_tarjeta->ViewCustomAttributes = "";

		// total
		$pedidos->total->ViewValue = $pedidos->total->CurrentValue;
		$pedidos->total->ViewValue = ew_FormatCurrency($pedidos->total->ViewValue, 2, -2, -2, -2);
		$pedidos->total->CssStyle = "";
		$pedidos->total->CssClass = "";
		$pedidos->total->ViewCustomAttributes = "";

		// Descuento
		$pedidos->Descuento->ViewValue = $pedidos->Descuento->CurrentValue;
		$pedidos->Descuento->ViewValue = ew_FormatCurrency($pedidos->Descuento->ViewValue, 2, -2, -2, -2);
		$pedidos->Descuento->CssStyle = "";
		$pedidos->Descuento->CssClass = "";
		$pedidos->Descuento->ViewCustomAttributes = "";

		// sena
		$pedidos->sena->ViewValue = $pedidos->sena->CurrentValue;
		$pedidos->sena->ViewValue = ew_FormatCurrency($pedidos->sena->ViewValue, 2, -2, -2, -2);
		$pedidos->sena->CssStyle = "";
		$pedidos->sena->CssClass = "";
		$pedidos->sena->ViewCustomAttributes = "";

		// nombre
		$pedidos->nombre->ViewValue = $pedidos->nombre->CurrentValue;
		$pedidos->nombre->CssStyle = "";
		$pedidos->nombre->CssClass = "";
		$pedidos->nombre->ViewCustomAttributes = "";

		// apellido
		$pedidos->apellido->ViewValue = $pedidos->apellido->CurrentValue;
		$pedidos->apellido->CssStyle = "";
		$pedidos->apellido->CssClass = "";
		$pedidos->apellido->ViewCustomAttributes = "";

		// telefono
		$pedidos->telefono->ViewValue = $pedidos->telefono->CurrentValue;
		$pedidos->telefono->CssStyle = "";
		$pedidos->telefono->CssClass = "";
		$pedidos->telefono->ViewCustomAttributes = "";

		// idVendedor
		if (!empty($pedidos->idVendedor->CurrentValue)) {
			$sSqlWrk = "SELECT `Vendedor` FROM `vendedores` WHERE `id` = " . ew_AdjustSql($pedidos->idVendedor->CurrentValue) . "";
			$rswrk = $conn->Execute($sSqlWrk);
			if ($rswrk) {
				if (!$rswrk->EOF) {
					$pedidos->idVendedor->ViewValue = $rswrk->fields('Vendedor');
				}
				$rswrk->Close();
			} else {
				$pedidos->idVendedor->ViewValue = $pedidos->idVendedor->CurrentValue;
			}
		} else {
			$pedidos->idVendedor->ViewValue = NULL;
		}
		$pedidos->idVendedor->CssStyle = "";
		$pedidos->idVendedor->CssClass = "";
		$pedidos->idVendedor->ViewCustomAttributes = "";

		// Evento
		$pedidos->Evento->ViewValue = $pedidos->Evento->CurrentValue;
		$pedidos->Evento->CssStyle = "";
		$pedidos->Evento->CssClass = "";
		$pedidos->Evento->ViewCustomAttributes = "";

		// idPresupuesto
		$pedidos->idPresupuesto->ViewValue = $pedidos->idPresupuesto->CurrentValue;
		$pedidos->idPresupuesto->CssStyle = "";
		$pedidos->idPresupuesto->CssClass = "";
		$pedidos->idPresupuesto->ViewCustomAttributes = "";

		// idFotolibro
		$pedidos->idFotolibro->ViewValue = $pedidos->idFotolibro->CurrentValue;
		$pedidos->idFotolibro->CssStyle = "";
		$pedidos->idFotolibro->CssClass = "";
		$pedidos->idFotolibro->ViewCustomAttributes = "";

		// id
		$pedidos->id->HrefValue = "";

		// Fecha
		$pedidos->Fecha->HrefValue = "";

		// estado
		$pedidos->estado->HrefValue = "";

		// ped_tarjeta
		$pedidos->ped_tarjeta->HrefValue = "";

		// total
		$pedidos->total->HrefValue = "";

		// nombre
		$pedidos->nombre->HrefValue = "";

		// apellido
		$pedidos->apellido->HrefValue = "";

		// telefono
		$pedidos->telefono->HrefValue = "";

		// idVendedor
		$pedidos->idVendedor->HrefValue = "";

		// Evento
		$pedidos->Evento->HrefValue = "";

		// idPresupuesto
		$pedidos->idPresupuesto->HrefValue = "";

		// idFotolibro
		$pedidos->idFotolibro->HrefValue = "";

		// sena
		$pedidos->sena->HrefValue = "";
	} elseif ($pedidos->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($pedidos->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($pedidos->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$pedidos->Row_Rendered();
}
?>
<?php

// Load advanced search
function LoadAdvancedSearch() {
	global $pedidos;
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
