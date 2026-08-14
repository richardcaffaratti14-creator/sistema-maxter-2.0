<?php
define("EW_PAGE_ID", "delete", TRUE); // Page ID
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
if (!$Security->CanDelete()) {
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

// Load Key Parameters
$sKey = "";
$bSingleDelete = TRUE; // Initialize as single delete
$arRecKeys = array();
$nKeySelected = 0; // Initialize selected key count
$sFilter = "";
if (@$_GET["id"] <> "") {
	$formato_video->id->setQueryStringValue($_GET["id"]);
	if (!is_numeric($formato_video->id->QueryStringValue)) {
		Page_Terminate($formato_video->getReturnUrl()); // Prevent sql injection, exit
	}
	$sKey .= $formato_video->id->QueryStringValue;
} else {
	$bSingleDelete = FALSE;
}
if ($bSingleDelete) {
	$nKeySelected = 1; // Set up key selected count
	$arRecKeys[0] = $sKey;
} else {
	if (isset($_POST["key_m"])) { // Key in form
		$nKeySelected = count($_POST["key_m"]); // Set up key selected count
		$arRecKeys = ew_StripSlashes($_POST["key_m"]);
	}
}
if ($nKeySelected <= 0) Page_Terminate($formato_video->getReturnUrl()); // No key specified, exit

// Build filter
foreach ($arRecKeys as $sKey) {
	$sFilter .= "(";

	// Set up key field
	$sKeyFld = $sKey;
	if (!is_numeric($sKeyFld)) {
		Page_Terminate($formato_video->getReturnUrl()); // Prevent sql injection, exit
	}
	$sFilter .= "`id`=" . ew_AdjustSql($sKeyFld) . " AND ";
	if (substr($sFilter, -5) == " AND ") $sFilter = substr($sFilter, 0, strlen($sFilter)-5) . ") OR ";
}
if (substr($sFilter, -4) == " OR ") $sFilter = substr($sFilter, 0, strlen($sFilter)-4);

// Set up filter (Sql Where Clause) and get Return Sql
// Sql constructor in formato_video class, formato_videoinfo.php

$formato_video->CurrentFilter = $sFilter;

// Get action
if (@$_POST["a_delete"] <> "") {
	$formato_video->CurrentAction = $_POST["a_delete"];
} else {
	$formato_video->CurrentAction = "I"; // Display record
}
switch ($formato_video->CurrentAction) {
	case "D": // Delete
		$formato_video->SendEmail = TRUE; // Send email on delete success
		if (DeleteRows()) { // delete rows
			$_SESSION[EW_SESSION_MESSAGE] = "Borrado satisfactorio"; // Set up success message
			Page_Terminate($formato_video->getReturnUrl()); // Return to caller
		}
}

// Load records for display
$rs = LoadRecordset();
$nTotalRecs = $rs->RecordCount(); // Get record count
if ($nTotalRecs <= 0) { // No record found, exit
	$rs->Close();
	Page_Terminate($formato_video->getReturnUrl()); // Return to caller
}
?>
<?php include "header.php" ?>
<script type="text/javascript">
<!--
var EW_PAGE_ID = "delete"; // Page id

//-->
</script>
<script language="JavaScript" type="text/javascript">
<!--

// Write your client script here, no need to add script tags.
// To include another .js script, use:
// ew_ClientScriptInclude("my_javascript.js"); 
//-->

</script>
<p><span class="phpmaker">Borrar desde TABLA: Formatos de videos<br><br><a href="<?php echo $formato_video->getReturnUrl() ?>">Volver atrás</a></span></p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form action="formato_videodelete.php" method="post">
<p>
<input type="hidden" name="a_delete" id="a_delete" value="D">
<?php foreach ($arRecKeys as $sKey) { ?>
<input type="hidden" name="key_m[]" id="key_m[]" value="<?php echo ew_HtmlEncode($sKey) ?>">
<?php } ?>
<table class="ewTable">
	<tr class="ewTableHeader">
		<td valign="top">Formato</td>
		<td valign="top">Precio</td>
		<td valign="top">Carpeta</td>
		<td valign="top">Orden</td>
		<td valign="top">Sufijo</td>
	</tr>
<?php
$nRecCount = 0;
$i = 0;
while (!$rs->EOF) {
	$nRecCount++;

	// Set row class and style
	$formato_video->CssClass = "ewTableRow";
	$formato_video->CssStyle = "";

	// Display alternate color for rows
	if ($nRecCount % 2 <> 1) {
		$formato_video->CssClass = "ewTableAltRow";
	}

	// Get the field contents
	LoadRowValues($rs);

	// Render row value
	$formato_video->RowType = EW_ROWTYPE_VIEW; // view
	RenderRow();
?>
	<tr<?php echo $formato_video->DisplayAttributes() ?>>
		<td<?php echo $formato_video->nombre->CellAttributes() ?>>
<div<?php echo $formato_video->nombre->ViewAttributes() ?>><?php echo $formato_video->nombre->ViewValue ?></div>
</td>
		<td<?php echo $formato_video->precio->CellAttributes() ?>>
<div<?php echo $formato_video->precio->ViewAttributes() ?>><?php echo $formato_video->precio->ViewValue ?></div>
</td>
		<td<?php echo $formato_video->carpeta->CellAttributes() ?>>
<div<?php echo $formato_video->carpeta->ViewAttributes() ?>><?php echo $formato_video->carpeta->ViewValue ?></div>
</td>
		<td<?php echo $formato_video->orden->CellAttributes() ?>>
<div<?php echo $formato_video->orden->ViewAttributes() ?>><?php echo $formato_video->orden->ViewValue ?></div>
</td>
		<td<?php echo $formato_video->Sufijo->CellAttributes() ?>>
<div<?php echo $formato_video->Sufijo->ViewAttributes() ?>><?php echo $formato_video->Sufijo->ViewValue ?></div>
</td>
	</tr>
<?php
	$rs->MoveNext();
}
$rs->Close();
?>
</table>
<p>
<input type="submit" name="Action" id="Action" value="Confirmación de Borrado">
</form>
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

// ------------------------------------------------
//  Function DeleteRows
//  - Delete Records based on current filter
function DeleteRows() {
	global $conn, $Security, $formato_video;
	$DeleteRows = TRUE;
	$sWrkFilter = $formato_video->CurrentFilter;

	// Set up filter (Sql Where Clause) and get Return Sql
	// Sql constructor in formato_video class, formato_videoinfo.php

	$formato_video->CurrentFilter = $sWrkFilter;
	$sSql = $formato_video->SQL();
	$conn->raiseErrorFn = 'ew_ErrorFn';
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';
	if ($rs === FALSE) {
		return FALSE;
	} elseif ($rs->EOF) {
		$_SESSION[EW_SESSION_MESSAGE] = "No se encontraron registros"; // No record found
		$rs->Close();
		return FALSE;
	}
	$conn->BeginTrans();

	// Clone old rows
	$rsold = ($rs) ? $rs->GetRows() : array();
	if ($rs) $rs->Close();

	// Call row deleting event
	if ($DeleteRows) {
		foreach ($rsold as $row) {
			$DeleteRows = $formato_video->Row_Deleting($row);
			if (!$DeleteRows) break;
		}
	}
	if ($DeleteRows) {
		$sKey = "";
		foreach ($rsold as $row) {
			$sThisKey = "";
			if ($sThisKey <> "") $sThisKey .= EW_COMPOSITE_KEY_SEPARATOR;
			$sThisKey .= $row['id'];
			$conn->raiseErrorFn = 'ew_ErrorFn';
			$DeleteRows = $conn->Execute($formato_video->DeleteSQL($row)); // Delete
			$conn->raiseErrorFn = '';
			if ($DeleteRows === FALSE)
				break;
			if ($sKey <> "") $sKey .= ", ";
			$sKey .= $sThisKey;
		}
	} else {

		// Set up error message
		if ($formato_video->CancelMessage <> "") {
			$_SESSION[EW_SESSION_MESSAGE] = $formato_video->CancelMessage;
			$formato_video->CancelMessage = "";
		} else {
			$_SESSION[EW_SESSION_MESSAGE] = "Borrar cancelado";
		}
	}
	if ($DeleteRows) {
		$conn->CommitTrans(); // Commit the changes
	} else {
		$conn->RollbackTrans(); // Rollback changes
	}

	// Call recordset deleted event
	if ($DeleteRows) {
		foreach ($rsold as $row) {
			$formato_video->Row_Deleted($row);
		}	
	}
	return $DeleteRows;
}
?>
<?php

// Load recordset
function LoadRecordset($offset = -1, $rowcnt = -1) {
	global $conn, $formato_video;

	// Call Recordset Selecting event
	$formato_video->Recordset_Selecting($formato_video->CurrentFilter);

	// Load list page sql
	$sSql = $formato_video->SelectSQL();
	if ($offset > -1 && $rowcnt > -1) $sSql .= " LIMIT $offset, $rowcnt";

	// Load recordset
	$conn->raiseErrorFn = 'ew_ErrorFn';	
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';

	// Call Recordset Selected event
	$formato_video->Recordset_Selected($rs);
	return $rs;
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

// Page Load event
function Page_Load() {

	//echo "Page Load";
}

// Page Unload event
function Page_Unload() {

	//echo "Page Unload";
}
?>
