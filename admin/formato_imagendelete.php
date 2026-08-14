<?php
define("EW_PAGE_ID", "delete", TRUE); // Page ID
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
if (!$Security->CanDelete()) {
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

// Load Key Parameters
$sKey = "";
$bSingleDelete = TRUE; // Initialize as single delete
$arRecKeys = array();
$nKeySelected = 0; // Initialize selected key count
$sFilter = "";
if (@$_GET["id"] <> "") {
	$formato_imagen->id->setQueryStringValue($_GET["id"]);
	if (!is_numeric($formato_imagen->id->QueryStringValue)) {
		Page_Terminate($formato_imagen->getReturnUrl()); // Prevent sql injection, exit
	}
	$sKey .= $formato_imagen->id->QueryStringValue;
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
if ($nKeySelected <= 0) Page_Terminate($formato_imagen->getReturnUrl()); // No key specified, exit

// Build filter
foreach ($arRecKeys as $sKey) {
	$sFilter .= "(";

	// Set up key field
	$sKeyFld = $sKey;
	if (!is_numeric($sKeyFld)) {
		Page_Terminate($formato_imagen->getReturnUrl()); // Prevent sql injection, exit
	}
	$sFilter .= "`id`=" . ew_AdjustSql($sKeyFld) . " AND ";
	if (substr($sFilter, -5) == " AND ") $sFilter = substr($sFilter, 0, strlen($sFilter)-5) . ") OR ";
}
if (substr($sFilter, -4) == " OR ") $sFilter = substr($sFilter, 0, strlen($sFilter)-4);

// Set up filter (Sql Where Clause) and get Return Sql
// Sql constructor in formato_imagen class, formato_imageninfo.php

$formato_imagen->CurrentFilter = $sFilter;

// Get action
if (@$_POST["a_delete"] <> "") {
	$formato_imagen->CurrentAction = $_POST["a_delete"];
} else {
	$formato_imagen->CurrentAction = "I"; // Display record
}
switch ($formato_imagen->CurrentAction) {
	case "D": // Delete
		$formato_imagen->SendEmail = TRUE; // Send email on delete success
		if (DeleteRows()) { // delete rows
			$_SESSION[EW_SESSION_MESSAGE] = "Borrado satisfactorio"; // Set up success message
			Page_Terminate($formato_imagen->getReturnUrl()); // Return to caller
		}
}

// Load records for display
$rs = LoadRecordset();
$nTotalRecs = $rs->RecordCount(); // Get record count
if ($nTotalRecs <= 0) { // No record found, exit
	$rs->Close();
	Page_Terminate($formato_imagen->getReturnUrl()); // Return to caller
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
<p><span class="phpmaker">Borrar desde TABLA: Formatos de imágenes<br><br><a href="<?php echo $formato_imagen->getReturnUrl() ?>">Volver atrás</a></span></p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form action="formato_imagendelete.php" method="post">
<p>
<input type="hidden" name="a_delete" id="a_delete" value="D">
<?php foreach ($arRecKeys as $sKey) { ?>
<input type="hidden" name="key_m[]" id="key_m[]" value="<?php echo ew_HtmlEncode($sKey) ?>">
<?php } ?>
<table class="ewTable">
	<tr class="ewTableHeader">
		<td valign="top">Formato</td>
		<td valign="top">Precio</td>
		<td valign="top">Ancho (cm)</td>
		<td valign="top">Alto (cm)</td>
		<td valign="top">Nombre carpeta</td>
		<td valign="top">Orden</td>
	</tr>
<?php
$nRecCount = 0;
$i = 0;
while (!$rs->EOF) {
	$nRecCount++;

	// Set row class and style
	$formato_imagen->CssClass = "ewTableRow";
	$formato_imagen->CssStyle = "";

	// Display alternate color for rows
	if ($nRecCount % 2 <> 1) {
		$formato_imagen->CssClass = "ewTableAltRow";
	}

	// Get the field contents
	LoadRowValues($rs);

	// Render row value
	$formato_imagen->RowType = EW_ROWTYPE_VIEW; // view
	RenderRow();
?>
	<tr<?php echo $formato_imagen->DisplayAttributes() ?>>
		<td<?php echo $formato_imagen->nombre->CellAttributes() ?>>
<div<?php echo $formato_imagen->nombre->ViewAttributes() ?>><?php echo $formato_imagen->nombre->ViewValue ?></div>
</td>
		<td<?php echo $formato_imagen->precio->CellAttributes() ?>>
<div<?php echo $formato_imagen->precio->ViewAttributes() ?>><?php echo $formato_imagen->precio->ViewValue ?></div>
</td>
		<td<?php echo $formato_imagen->ancho->CellAttributes() ?>>
<div<?php echo $formato_imagen->ancho->ViewAttributes() ?>><?php echo $formato_imagen->ancho->ViewValue ?></div>
</td>
		<td<?php echo $formato_imagen->alto->CellAttributes() ?>>
<div<?php echo $formato_imagen->alto->ViewAttributes() ?>><?php echo $formato_imagen->alto->ViewValue ?></div>
</td>
		<td<?php echo $formato_imagen->carpeta->CellAttributes() ?>>
<div<?php echo $formato_imagen->carpeta->ViewAttributes() ?>><?php echo $formato_imagen->carpeta->ViewValue ?></div>
</td>
		<td<?php echo $formato_imagen->orden->CellAttributes() ?>>
<div<?php echo $formato_imagen->orden->ViewAttributes() ?>><?php echo $formato_imagen->orden->ViewValue ?></div>
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
	global $conn, $Security, $formato_imagen;
	$DeleteRows = TRUE;
	$sWrkFilter = $formato_imagen->CurrentFilter;

	// Set up filter (Sql Where Clause) and get Return Sql
	// Sql constructor in formato_imagen class, formato_imageninfo.php

	$formato_imagen->CurrentFilter = $sWrkFilter;
	$sSql = $formato_imagen->SQL();
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
			$DeleteRows = $formato_imagen->Row_Deleting($row);
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
			$DeleteRows = $conn->Execute($formato_imagen->DeleteSQL($row)); // Delete
			$conn->raiseErrorFn = '';
			if ($DeleteRows === FALSE)
				break;
			if ($sKey <> "") $sKey .= ", ";
			$sKey .= $sThisKey;
		}
	} else {

		// Set up error message
		if ($formato_imagen->CancelMessage <> "") {
			$_SESSION[EW_SESSION_MESSAGE] = $formato_imagen->CancelMessage;
			$formato_imagen->CancelMessage = "";
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
			$formato_imagen->Row_Deleted($row);
		}	
	}
	return $DeleteRows;
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
