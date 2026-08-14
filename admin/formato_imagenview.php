<?php
define("EW_PAGE_ID", "view", TRUE); // Page ID
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
if (!$Security->CanView()) {
	$Security->SaveLastUrl();
	Page_Terminate("formato_imagenlist.php");
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
if (@$_GET["id"] <> "") {
	$formato_imagen->id->setQueryStringValue($_GET["id"]);
} else {
	Page_Terminate("formato_imagenlist.php"); // Return to list page
}

// Get action
if (@$_POST["a_view"] <> "") {
	$formato_imagen->CurrentAction = $_POST["a_view"];
} else {
	$formato_imagen->CurrentAction = "I"; // Display form
}
switch ($formato_imagen->CurrentAction) {
	case "I": // Get a record to display
		if (!LoadRow()) { // Load record based on key
			$_SESSION[EW_SESSION_MESSAGE] = "No se encontraron registros"; // Set no record message
			Page_Terminate("formato_imagenlist.php"); // Return to list
		}
}

// Set return url
$formato_imagen->setReturnUrl("formato_imagenview.php");

// Render row
$formato_imagen->RowType = EW_ROWTYPE_VIEW;
RenderRow();
?>
<?php include "header.php" ?>
<script type="text/javascript">
<!--
var EW_PAGE_ID = "view"; // Page id

//-->
</script>
<script language="JavaScript" type="text/javascript">
<!--

// Write your client script here, no need to add script tags.
// To include another .js script, use:
// ew_ClientScriptInclude("my_javascript.js"); 
//-->

</script>
<p><span class="phpmaker">Vista TABLA: Formatos de imágenes
<br><br>
<a href="formato_imagenlist.php">Volver a la lista</a>&nbsp;
<?php if ($Security->CanAdd()) { ?>
<a href="formato_imagenadd.php">Agregar</a>&nbsp;
<?php } ?>
<?php if ($Security->CanEdit()) { ?>
<a href="<?php echo $formato_imagen->EditUrl() ?>">Editar</a>&nbsp;
<?php } ?>
<?php if ($Security->CanDelete()) { ?>
<a href="<?php echo $formato_imagen->DeleteUrl() ?>">Borrar</a>&nbsp;
<?php } ?>
</span>
</p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<p>
<form>
<table class="ewTable">
	<tr class="ewTableRow">
		<td class="ewTableHeader">Formato</td>
		<td<?php echo $formato_imagen->nombre->CellAttributes() ?>>
<div<?php echo $formato_imagen->nombre->ViewAttributes() ?>><?php echo $formato_imagen->nombre->ViewValue ?></div>
</td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Precio</td>
		<td<?php echo $formato_imagen->precio->CellAttributes() ?>>
<div<?php echo $formato_imagen->precio->ViewAttributes() ?>><?php echo $formato_imagen->precio->ViewValue ?></div>
</td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">Ancho (cm)</td>
		<td<?php echo $formato_imagen->ancho->CellAttributes() ?>>
<div<?php echo $formato_imagen->ancho->ViewAttributes() ?>><?php echo $formato_imagen->ancho->ViewValue ?></div>
</td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Alto (cm)</td>
		<td<?php echo $formato_imagen->alto->CellAttributes() ?>>
<div<?php echo $formato_imagen->alto->ViewAttributes() ?>><?php echo $formato_imagen->alto->ViewValue ?></div>
</td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">Nombre carpeta</td>
		<td<?php echo $formato_imagen->carpeta->CellAttributes() ?>>
<div<?php echo $formato_imagen->carpeta->ViewAttributes() ?>><?php echo $formato_imagen->carpeta->ViewValue ?></div>
</td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Orden</td>
		<td<?php echo $formato_imagen->orden->CellAttributes() ?>>
<div<?php echo $formato_imagen->orden->ViewAttributes() ?>><?php echo $formato_imagen->orden->ViewValue ?></div>
</td>
	</tr>
</table>
</form>
<p>
<script language="JavaScript" type="text/javascript">
<!--

// Write your table-specific startup script here
// document.write("page loaded");
//-->

</script>
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

// Page Load event
function Page_Load() {

	//echo "Page Load";
}

// Page Unload event
function Page_Unload() {

	//echo "Page Unload";
}
?>
