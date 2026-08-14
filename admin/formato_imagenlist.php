<?php
define("EW_PAGE_ID", "list", TRUE); // Page ID
define("EW_TABLE_NAME", 'formato_imagen', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "formato_imageninfo.php" ?>
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
$Security->LoadCurrentUserLevel('formato_imagen');
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
$formato_imagen->Export = @$_GET["export"]; // Get export parameter
$sExport = $formato_imagen->Export; // Get export parameter, used in header
$sExportFile = $formato_imagen->TableVar; // Get export file, used in header
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
$formato_imagen->setSessionWhere($sFilter);
$formato_imagen->CurrentFilter = "";

// Set Up Sorting Order
SetUpSortOrder();

// Set Return Url
$formato_imagen->setReturnUrl("formato_imagenlist.php");
?>
<?php include "header.php" ?>
<?php if ($formato_imagen->Export == "") { ?>
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
<?php if ($formato_imagen->Export == "") { ?>
<?php } ?>
<?php

// Load recordset
$bExportAll = (defined("EW_EXPORT_ALL") && $formato_imagen->Export <> "");
$bSelectLimit = ($formato_imagen->Export == "" && $formato_imagen->SelectLimit);
if (!$bSelectLimit) $rs = LoadRecordset();
$nTotalRecs = ($bSelectLimit) ? $formato_imagen->SelectRecordCount() : $rs->RecordCount();
$nStartRec = 1;
if ($nDisplayRecs <= 0) $nDisplayRecs = $nTotalRecs; // Display all records
if (!$bExportAll) SetUpStartRec(); // Set up start record position
if ($bSelectLimit) $rs = LoadRecordset($nStartRec-1, $nDisplayRecs);
?>
<?php if ($formato_imagen->Export == "") { ?>
<?php } ?>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form method="post" name="fformato_imagenlist" id="fformato_imagenlist">
<?php if ($formato_imagen->Export == "") { ?>
<table class="subheader" align="center">
	<tr><td><span class="phpmaker">
<?php if ($Security->CanAdd()) { ?>
<div class="button-add"><a href="formato_imagenadd.php">Agregar</a></div>
<?php } ?>
<div class="tablername" style="margin-top:7px">Formatos de imágenes</div>
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
<?php if ($formato_imagen->Export <> "") { ?>
Formato
<?php } else { ?>
	<a href="formato_imagenlist.php?order=<?php echo urlencode('nombre') ?>&ordertype=<?php echo $formato_imagen->nombre->ReverseSort() ?>">Formato<?php if ($formato_imagen->nombre->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($formato_imagen->nombre->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($formato_imagen->Export <> "") { ?>
Precio
<?php } else { ?>
	<a href="formato_imagenlist.php?order=<?php echo urlencode('precio') ?>&ordertype=<?php echo $formato_imagen->precio->ReverseSort() ?>">Precio<?php if ($formato_imagen->precio->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($formato_imagen->precio->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($formato_imagen->Export <> "") { ?>
Ancho (cm)
<?php } else { ?>
	<a href="formato_imagenlist.php?order=<?php echo urlencode('ancho') ?>&ordertype=<?php echo $formato_imagen->ancho->ReverseSort() ?>">Ancho (cm)<?php if ($formato_imagen->ancho->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($formato_imagen->ancho->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($formato_imagen->Export <> "") { ?>
Alto (cm)
<?php } else { ?>
	<a href="formato_imagenlist.php?order=<?php echo urlencode('alto') ?>&ordertype=<?php echo $formato_imagen->alto->ReverseSort() ?>">Alto (cm)<?php if ($formato_imagen->alto->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($formato_imagen->alto->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($formato_imagen->Export <> "") { ?>
Nombre carpeta
<?php } else { ?>
	<a href="formato_imagenlist.php?order=<?php echo urlencode('carpeta') ?>&ordertype=<?php echo $formato_imagen->carpeta->ReverseSort() ?>">Nombre carpeta<?php if ($formato_imagen->carpeta->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($formato_imagen->carpeta->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($formato_imagen->Export <> "") { ?>
Orden
<?php } else { ?>
	<a href="formato_imagenlist.php?order=<?php echo urlencode('orden') ?>&ordertype=<?php echo $formato_imagen->orden->ReverseSort() ?>">Orden<?php if ($formato_imagen->orden->getSort() == "ASC") { ?><img src="images/sortup.gif" width="11" height="9" border="0"><?php } elseif ($formato_imagen->orden->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="11" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
<?php if ($formato_imagen->Export == "") { ?>
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
if (defined("EW_EXPORT_ALL") && $formato_imagen->Export <> "") {
	$nStopRec = $nTotalRecs;
} else {
	$nStopRec = $nStartRec + $nDisplayRecs - 1; // Set the last record to display
}
$nRecCount = $nStartRec - 1;
if (!$rs->EOF) {
	$rs->MoveFirst();
	if (!$formato_imagen->SelectLimit) $rs->Move($nStartRec - 1); // Move to first record directly
}
$RowCnt = 0;
while (!$rs->EOF && $nRecCount < $nStopRec) {
	$nRecCount++;
	if (intval($nRecCount) >= intval($nStartRec)) {
		$RowCnt++;

	// Init row class and style
	$formato_imagen->CssClass = "ewTableRow";
	$formato_imagen->CssStyle = "";

	// Init row event
	$formato_imagen->RowClientEvents = "onmouseover='ew_MouseOver(this);' onmouseout='ew_MouseOut(this);' onclick='ew_Click(this);'";

	// Display alternate color for rows
	if ($RowCnt % 2 == 0) {
		$formato_imagen->CssClass = "ewTableAltRow";
	}
	LoadRowValues($rs); // Load row values
	$formato_imagen->RowType = EW_ROWTYPE_VIEW; // Render view
	RenderRow();
?>
	<!-- Table body -->
	<tr<?php echo $formato_imagen->DisplayAttributes() ?>>
		<!-- nombre -->
		<td<?php echo $formato_imagen->nombre->CellAttributes() ?>>
<div<?php echo $formato_imagen->nombre->ViewAttributes() ?>><?php echo $formato_imagen->nombre->ViewValue ?></div>
</td>
		<!-- precio -->
		<td<?php echo $formato_imagen->precio->CellAttributes() ?>>
<div<?php echo $formato_imagen->precio->ViewAttributes() ?>><?php echo $formato_imagen->precio->ViewValue ?></div>
</td>
		<!-- ancho -->
		<td<?php echo $formato_imagen->ancho->CellAttributes() ?>>
<div<?php echo $formato_imagen->ancho->ViewAttributes() ?>><?php echo $formato_imagen->ancho->ViewValue ?></div>
</td>
		<!-- alto -->
		<td<?php echo $formato_imagen->alto->CellAttributes() ?>>
<div<?php echo $formato_imagen->alto->ViewAttributes() ?>><?php echo $formato_imagen->alto->ViewValue ?></div>
</td>
		<!-- carpeta -->
		<td<?php echo $formato_imagen->carpeta->CellAttributes() ?>>
<div<?php echo $formato_imagen->carpeta->ViewAttributes() ?>><?php echo $formato_imagen->carpeta->ViewValue ?></div>
</td>
		<!-- orden -->
		<td<?php echo $formato_imagen->orden->CellAttributes() ?>>
<div<?php echo $formato_imagen->orden->ViewAttributes() ?>><?php echo $formato_imagen->orden->ViewValue ?></div>
</td>
<?php if ($formato_imagen->Export == "") { ?>
<?php if ($Security->CanView()) { ?>
<td nowrap width="1" style="padding:0"><span class="phpmaker">
<div class="button-view"><a href="<?php echo $formato_imagen->ViewUrl() ?>">Vista</a></div>
</span></td>
<?php } ?>
<?php if ($Security->CanEdit()) { ?>
<td nowrap width="1" style="padding:0"><span class="phpmaker">
<div class="button-edit"><a href="<?php echo $formato_imagen->EditUrl() ?>">Editar</a></div>
</span></td>
<?php } ?>
<?php if ($Security->CanDelete()) { ?>
<td nowrap width="1" style="padding:0"><span class="phpmaker">
<div class="button-delete"><a href="<?php echo $formato_imagen->DeleteUrl() ?>">Borrar</a></div>
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
<?php if ($formato_imagen->Export == "") { ?>
<form action="formato_imagenlist.php" name="ewpagerform" id="ewpagerform">
<table border="0" cellspacing="0" cellpadding="0" align="center" id="pagertable">
	<tr>
		<td nowrap align="center">
<span class="phpmaker">
<?php if (!isset($Pager)) $Pager = new cNumericPager($nStartRec, $nDisplayRecs, $nTotalRecs, $nRecRange) ?>
<?php if ($Pager->RecordCount > 0) { ?>
	<?php if ($Pager->FirstButton->Enabled) { ?>
	<a href="formato_imagenlist.php?start=<?php echo $Pager->FirstButton->Start ?>"><b>Primero</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->PrevButton->Enabled) { ?>
	<a href="formato_imagenlist.php?start=<?php echo $Pager->PrevButton->Start ?>"><b>Anterior</b></a>&nbsp;
	<?php } ?>
	<?php foreach ($Pager->Items as $PagerItem) { ?>
		<?php if ($PagerItem->Enabled) { ?><a href="formato_imagenlist.php?start=<?php echo $PagerItem->Start ?>"><?php } ?><b><?php echo $PagerItem->Text ?></b><?php if ($PagerItem->Enabled) { ?></a><?php } ?>&nbsp;
	<?php } ?>
	<?php if ($Pager->NextButton->Enabled) { ?>
	<a href="formato_imagenlist.php?start=<?php echo $Pager->NextButton->Start ?>"><b>Próximo</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->LastButton->Enabled) { ?>
	<a href="formato_imagenlist.php?start=<?php echo $Pager->LastButton->Start ?>"><b>Último</b></a>&nbsp;
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
<?php if ($formato_imagen->Export == "") { ?>
<?php } ?>
<?php if ($formato_imagen->Export == "") { ?>
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
	global $formato_imagen;

	// Check for an Order parameter
	if (@$_GET["order"] <> "") {
		$formato_imagen->CurrentOrder = ew_StripSlashes(@$_GET["order"]);
		$formato_imagen->CurrentOrderType = @$_GET["ordertype"];

		// Field nombre
		$formato_imagen->UpdateSort($formato_imagen->nombre);

		// Field precio
		$formato_imagen->UpdateSort($formato_imagen->precio);

		// Field ancho
		$formato_imagen->UpdateSort($formato_imagen->ancho);

		// Field alto
		$formato_imagen->UpdateSort($formato_imagen->alto);

		// Field carpeta
		$formato_imagen->UpdateSort($formato_imagen->carpeta);

		// Field orden
		$formato_imagen->UpdateSort($formato_imagen->orden);
		$formato_imagen->setStartRecordNumber(1); // Reset start position
	}
	$sOrderBy = $formato_imagen->getSessionOrderBy(); // Get order by from Session
	if ($sOrderBy == "") {
		if ($formato_imagen->SqlOrderBy() <> "") {
			$sOrderBy = $formato_imagen->SqlOrderBy();
			$formato_imagen->setSessionOrderBy($sOrderBy);
			$formato_imagen->orden->setSort("ASC");
		}
	}
}

// Reset command based on querystring parameter cmd=
// - RESET: reset search parameters
// - RESETALL: reset search & master/detail parameters
// - RESETSORT: reset sort parameters
function ResetCmd() {
	global $sDbMasterFilter, $sDbDetailFilter, $nStartRec, $sOrderBy;
	global $formato_imagen;

	// Get reset cmd
	if (@$_GET["cmd"] <> "") {
		$sCmd = $_GET["cmd"];

		// Reset Sort Criteria
		if (strtolower($sCmd) == "resetsort") {
			$sOrderBy = "";
			$formato_imagen->setSessionOrderBy($sOrderBy);
			$formato_imagen->nombre->setSort("");
			$formato_imagen->precio->setSort("");
			$formato_imagen->ancho->setSort("");
			$formato_imagen->alto->setSort("");
			$formato_imagen->carpeta->setSort("");
			$formato_imagen->orden->setSort("");
		}

		// Reset start position
		$nStartRec = 1;
		$formato_imagen->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Set up Starting Record parameters based on Pager Navigation
function SetUpStartRec() {
	global $nDisplayRecs, $nStartRec, $nTotalRecs, $nPageNo, $formato_imagen;
	if ($nDisplayRecs == 0) return;

	// Check for a START parameter
	if (@$_GET[EW_TABLE_START_REC] <> "") {
		$nStartRec = $_GET[EW_TABLE_START_REC];
		$formato_imagen->setStartRecordNumber($nStartRec);
	} elseif (@$_GET[EW_TABLE_PAGE_NO] <> "") {
		$nPageNo = $_GET[EW_TABLE_PAGE_NO];
		if (is_numeric($nPageNo)) {
			$nStartRec = ($nPageNo-1)*$nDisplayRecs+1;
			if ($nStartRec <= 0) {
				$nStartRec = 1;
			} elseif ($nStartRec >= intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1) {
				$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1;
			}
			$formato_imagen->setStartRecordNumber($nStartRec);
		} else {
			$nStartRec = $formato_imagen->getStartRecordNumber();
		}
	} else {
		$nStartRec = $formato_imagen->getStartRecordNumber();
	}

	// Check if correct start record counter
	if (!is_numeric($nStartRec) || $nStartRec == "") { // Avoid invalid start record counter
		$nStartRec = 1; // Reset start record counter
		$formato_imagen->setStartRecordNumber($nStartRec);
	} elseif (intval($nStartRec) > intval($nTotalRecs)) { // Avoid starting record > total records
		$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to last page first record
		$formato_imagen->setStartRecordNumber($nStartRec);
	} elseif (($nStartRec-1) % $nDisplayRecs <> 0) {
		$nStartRec = intval(($nStartRec-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to page boundary
		$formato_imagen->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Load recordset
function LoadRecordset($offset = -1, $rowcnt = -1) {
	global $conn, $formato_imagen;

	// Call Recordset Selecting event
	$formato_imagen->Recordset_Selecting($formato_imagen->CurrentFilter);

	// Load list page sql
	$sSql = $formato_imagen->SelectSQL();
	if ($offset > -1 && $rowcnt > -1) $sSql .= " LIMIT $offset, $rowcnt";

	// Load recordset
	$conn->raiseErrorFn = 'ew_ErrorFn';	
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';

	// Call Recordset Selected event
	$formato_imagen->Recordset_Selected($rs);
	return $rs;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $formato_imagen;
	$sFilter = $formato_imagen->SqlKeyFilter();
	if (!is_numeric($formato_imagen->id->CurrentValue)) {
		return FALSE; // Invalid key, exit
	}
	$sFilter = str_replace("@id@", ew_AdjustSql($formato_imagen->id->CurrentValue), $sFilter); // Replace key value

	// Call Row Selecting event
	$formato_imagen->Row_Selecting($sFilter);

	// Load sql based on filter
	$formato_imagen->CurrentFilter = $sFilter;
	$sSql = $formato_imagen->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$formato_imagen->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $formato_imagen;
	$formato_imagen->id->setDbValue($rs->fields('id'));
	$formato_imagen->nombre->setDbValue($rs->fields('nombre'));
	$formato_imagen->precio->setDbValue($rs->fields('precio'));
	$formato_imagen->ancho->setDbValue($rs->fields('ancho'));
	$formato_imagen->alto->setDbValue($rs->fields('alto'));
	$formato_imagen->carpeta->setDbValue($rs->fields('carpeta'));
	$formato_imagen->orden->setDbValue($rs->fields('orden'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $formato_imagen;

	// Call Row Rendering event
	$formato_imagen->Row_Rendering();

	// Common render codes for all row types
	// nombre

	$formato_imagen->nombre->CellCssStyle = "";
	$formato_imagen->nombre->CellCssClass = "";

	// precio
	$formato_imagen->precio->CellCssStyle = "";
	$formato_imagen->precio->CellCssClass = "";

	// ancho
	$formato_imagen->ancho->CellCssStyle = "";
	$formato_imagen->ancho->CellCssClass = "";

	// alto
	$formato_imagen->alto->CellCssStyle = "";
	$formato_imagen->alto->CellCssClass = "";

	// carpeta
	$formato_imagen->carpeta->CellCssStyle = "";
	$formato_imagen->carpeta->CellCssClass = "";

	// orden
	$formato_imagen->orden->CellCssStyle = "";
	$formato_imagen->orden->CellCssClass = "";
	if ($formato_imagen->RowType == EW_ROWTYPE_VIEW) { // View row

		// nombre
		$formato_imagen->nombre->ViewValue = $formato_imagen->nombre->CurrentValue;
		$formato_imagen->nombre->CssStyle = "";
		$formato_imagen->nombre->CssClass = "";
		$formato_imagen->nombre->ViewCustomAttributes = "";

		// precio
		$formato_imagen->precio->ViewValue = $formato_imagen->precio->CurrentValue;
		$formato_imagen->precio->ViewValue = ew_FormatCurrency($formato_imagen->precio->ViewValue, 2, -2, -2, -2);
		$formato_imagen->precio->CssStyle = "";
		$formato_imagen->precio->CssClass = "";
		$formato_imagen->precio->ViewCustomAttributes = "";

		// ancho
		$formato_imagen->ancho->ViewValue = $formato_imagen->ancho->CurrentValue;
		$formato_imagen->ancho->CssStyle = "";
		$formato_imagen->ancho->CssClass = "";
		$formato_imagen->ancho->ViewCustomAttributes = "";

		// alto
		$formato_imagen->alto->ViewValue = $formato_imagen->alto->CurrentValue;
		$formato_imagen->alto->CssStyle = "";
		$formato_imagen->alto->CssClass = "";
		$formato_imagen->alto->ViewCustomAttributes = "";

		// carpeta
		$formato_imagen->carpeta->ViewValue = $formato_imagen->carpeta->CurrentValue;
		$formato_imagen->carpeta->CssStyle = "";
		$formato_imagen->carpeta->CssClass = "";
		$formato_imagen->carpeta->ViewCustomAttributes = "";

		// orden
		$formato_imagen->orden->ViewValue = $formato_imagen->orden->CurrentValue;
		$formato_imagen->orden->CssStyle = "";
		$formato_imagen->orden->CssClass = "";
		$formato_imagen->orden->ViewCustomAttributes = "";

		// nombre
		$formato_imagen->nombre->HrefValue = "";

		// precio
		$formato_imagen->precio->HrefValue = "";

		// ancho
		$formato_imagen->ancho->HrefValue = "";

		// alto
		$formato_imagen->alto->HrefValue = "";

		// carpeta
		$formato_imagen->carpeta->HrefValue = "";

		// orden
		$formato_imagen->orden->HrefValue = "";
	} elseif ($formato_imagen->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($formato_imagen->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($formato_imagen->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$formato_imagen->Row_Rendered();
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
