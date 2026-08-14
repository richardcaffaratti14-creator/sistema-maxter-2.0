<?php
define("EW_PAGE_ID", "view", TRUE); // Page ID
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
if (!$Security->CanView()) {
	$Security->SaveLastUrl();
	Page_Terminate("formato_videolist.php");
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
if (@$_GET["id"] <> "") {
	$formato_video->id->setQueryStringValue($_GET["id"]);
} else {
	Page_Terminate("formato_videolist.php"); // Return to list page
}

// Get action
if (@$_POST["a_view"] <> "") {
	$formato_video->CurrentAction = $_POST["a_view"];
} else {
	$formato_video->CurrentAction = "I"; // Display form
}
switch ($formato_video->CurrentAction) {
	case "I": // Get a record to display
		if (!LoadRow()) { // Load record based on key
			$_SESSION[EW_SESSION_MESSAGE] = "No se encontraron registros"; // Set no record message
			Page_Terminate("formato_videolist.php"); // Return to list
		}
}

// Set return url
$formato_video->setReturnUrl("formato_videoview.php");

// Render row
$formato_video->RowType = EW_ROWTYPE_VIEW;
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
<p><span class="phpmaker">Vista TABLA: Formatos de videos
<br><br>
<a href="formato_videolist.php">Volver a la lista</a>&nbsp;
<?php if ($Security->CanAdd()) { ?>
<a href="formato_videoadd.php">Agregar</a>&nbsp;
<?php } ?>
<?php if ($Security->CanEdit()) { ?>
<a href="<?php echo $formato_video->EditUrl() ?>">Editar</a>&nbsp;
<?php } ?>
<?php if ($Security->CanDelete()) { ?>
<a href="<?php echo $formato_video->DeleteUrl() ?>">Borrar</a>&nbsp;
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
		<td<?php echo $formato_video->nombre->CellAttributes() ?>>
<div<?php echo $formato_video->nombre->ViewAttributes() ?>><?php echo $formato_video->nombre->ViewValue ?></div>
</td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Precio</td>
		<td<?php echo $formato_video->precio->CellAttributes() ?>>
<div<?php echo $formato_video->precio->ViewAttributes() ?>><?php echo $formato_video->precio->ViewValue ?></div>
</td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">Carpeta</td>
		<td<?php echo $formato_video->carpeta->CellAttributes() ?>>
<div<?php echo $formato_video->carpeta->ViewAttributes() ?>><?php echo $formato_video->carpeta->ViewValue ?></div>
</td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Orden</td>
		<td<?php echo $formato_video->orden->CellAttributes() ?>>
<div<?php echo $formato_video->orden->ViewAttributes() ?>><?php echo $formato_video->orden->ViewValue ?></div>
</td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">Sufijo</td>
		<td<?php echo $formato_video->Sufijo->CellAttributes() ?>>
<div<?php echo $formato_video->Sufijo->ViewAttributes() ?>><?php echo $formato_video->Sufijo->ViewValue ?></div>
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

// Page Load event
function Page_Load() {

	//echo "Page Load";
}

// Page Unload event
function Page_Unload() {

	//echo "Page Unload";
}
?>
