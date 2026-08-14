<?php
define("EW_PAGE_ID", "view", TRUE); // Page ID
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
if (@$_GET["UserLevelID"] <> "") {
	$usuarioslevels->UserLevelID->setQueryStringValue($_GET["UserLevelID"]);
} else {
	Page_Terminate("usuarioslevelslist.php"); // Return to list page
}

// Get action
if (@$_POST["a_view"] <> "") {
	$usuarioslevels->CurrentAction = $_POST["a_view"];
} else {
	$usuarioslevels->CurrentAction = "I"; // Display form
}
switch ($usuarioslevels->CurrentAction) {
	case "I": // Get a record to display
		if (!LoadRow()) { // Load record based on key
			$_SESSION[EW_SESSION_MESSAGE] = "No se encontraron registros"; // Set no record message
			Page_Terminate("usuarioslevelslist.php"); // Return to list
		}
}

// Set return url
$usuarioslevels->setReturnUrl("usuarioslevelsview.php");

// Render row
$usuarioslevels->RowType = EW_ROWTYPE_VIEW;
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
<p><span class="phpmaker">Vista TABLA: Niveles de Usuarios
<br><br>
<a href="usuarioslevelslist.php">Volver a la lista</a>&nbsp;
<?php if ($Security->CanAdd()) { ?>
<a href="usuarioslevelsadd.php">Agregar</a>&nbsp;
<?php } ?>
<?php if ($Security->CanEdit()) { ?>
<a href="<?php echo $usuarioslevels->EditUrl() ?>">Editar</a>&nbsp;
<?php } ?>
<?php if ($Security->CanDelete()) { ?>
<a href="<?php echo $usuarioslevels->DeleteUrl() ?>">Borrar</a>&nbsp;
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
		<td class="ewTableHeader">ID de Nivel de Usuario</td>
		<td<?php echo $usuarioslevels->UserLevelID->CellAttributes() ?>>
<div<?php echo $usuarioslevels->UserLevelID->ViewAttributes() ?>><?php echo $usuarioslevels->UserLevelID->ViewValue ?></div>
</td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Nivel de Usuario</td>
		<td<?php echo $usuarioslevels->UserLevelName->CellAttributes() ?>>
<div<?php echo $usuarioslevels->UserLevelName->ViewAttributes() ?>><?php echo $usuarioslevels->UserLevelName->ViewValue ?></div>
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

// Page Load event
function Page_Load() {

	//echo "Page Load";
}

// Page Unload event
function Page_Unload() {

	//echo "Page Unload";
}
?>
