<?php
define("EW_PAGE_ID", "delete", TRUE); // Page ID
define("EW_TABLE_NAME", 'formato_coreo', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "formato_coreoinfo.php" ?>
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
$Security->LoadCurrentUserLevel('formato_coreo');
if (!$Security->IsLoggedIn()) {
	$Security->SaveLastUrl();
	Page_Terminate("login.php");
}
if (!$Security->CanDelete()) {
	$Security->SaveLastUrl();
	Page_Terminate("formato_coreolist.php");
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
$formato_coreo->Export = @$_GET["export"]; // Get export parameter
$sExport = $formato_coreo->Export; // Get export parameter, used in header
$sExportFile = $formato_coreo->TableVar; // Get export file, used in header
?>
<?php

// Load Key Parameters
$sKey = "";
$bSingleDelete = TRUE; // Initialize as single delete
$arRecKeys = array();
$nKeySelected = 0; // Initialize selected key count
$sFilter = "";
if (@$_GET["id"] <> "") {
	$formato_coreo->id->setQueryStringValue($_GET["id"]);
	if (!is_numeric($formato_coreo->id->QueryStringValue)) {
		Page_Terminate($formato_coreo->getReturnUrl()); // Prevent sql injection, exit
	}
	$sKey .= $formato_coreo->id->QueryStringValue;
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
if ($nKeySelected <= 0) Page_Terminate($formato_coreo->getReturnUrl()); // No key specified, exit

// Build filter
foreach ($arRecKeys as $sKey) {
	$sFilter .= "(";

	// Set up key field
	$sKeyFld = $sKey;
	if (!is_numeric($sKeyFld)) {
		Page_Terminate($formato_coreo->getReturnUrl()); // Prevent sql injection, exit
	}
	$sFilter .= "`id`=" . ew_AdjustSql($sKeyFld) . " AND ";
	if (substr($sFilter, -5) == " AND ") $sFilter = substr($sFilter, 0, strlen($sFilter)-5) . ") OR ";
}
if (substr($sFilter, -4) == " OR ") $sFilter = substr($sFilter, 0, strlen($sFilter)-4);

// Set up filter (Sql Where Clause) and get Return Sql
// Sql constructor in formato_coreo class, formato_coreoinfo.php

$formato_coreo->CurrentFilter = $sFilter;

// Get action
if (@$_POST["a_delete"] <> "") {
	$formato_coreo->CurrentAction = $_POST["a_delete"];
} else {
	$formato_coreo->CurrentAction = "I"; // Display record
}
switch ($formato_coreo->CurrentAction) {
	case "D": // Delete
		$formato_coreo->SendEmail = TRUE; // Send email on delete success
		if (DeleteRows()) { // delete rows
			$_SESSION[EW_SESSION_MESSAGE] = "Borrado satisfactorio"; // Set up success message
			Page_Terminate($formato_coreo->getReturnUrl()); // Return to caller
		}
}

// Load records for display
$rs = LoadRecordset();
$nTotalRecs = $rs->RecordCount(); // Get record count
if ($nTotalRecs <= 0) { // No record found, exit
	$rs->Close();
	Page_Terminate($formato_coreo->getReturnUrl()); // Return to caller
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
<p><span class="phpmaker">Borrar desde TABLA: Formato de coreos<br><br><a href="<?php echo $formato_coreo->getReturnUrl() ?>">Volver atrás</a></span></p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form action="formato_coreodelete.php" method="post">
<p>
<input type="hidden" name="a_delete" id="a_delete" value="D">
<?php foreach ($arRecKeys as $sKey) { ?>
<input type="hidden" name="key_m[]" id="key_m[]" value="<?php echo ew_HtmlEncode($sKey) ?>">
<?php } ?>
<table class="ewTable">
	<tr class="ewTableHeader">
		<td valign="top">Nombre</td>
		<td valign="top">Precio</td>
		<td valign="top">Sufijo</td>
	</tr>
<?php
$nRecCount = 0;
$i = 0;
while (!$rs->EOF) {
	$nRecCount++;

	// Set row class and style
	$formato_coreo->CssClass = "ewTableRow";
	$formato_coreo->CssStyle = "";

	// Display alternate color for rows
	if ($nRecCount % 2 <> 1) {
		$formato_coreo->CssClass = "ewTableAltRow";
	}

	// Get the field contents
	LoadRowValues($rs);

	// Render row value
	$formato_coreo->RowType = EW_ROWTYPE_VIEW; // view
	RenderRow();
?>
	<tr<?php echo $formato_coreo->DisplayAttributes() ?>>
		<td<?php echo $formato_coreo->Nombre->CellAttributes() ?>>
<div<?php echo $formato_coreo->Nombre->ViewAttributes() ?>><?php echo $formato_coreo->Nombre->ViewValue ?></div>
</td>
		<td<?php echo $formato_coreo->Precio->CellAttributes() ?>>
<div<?php echo $formato_coreo->Precio->ViewAttributes() ?>><?php echo $formato_coreo->Precio->ViewValue ?></div>
</td>
		<td<?php echo $formato_coreo->Sufijo->CellAttributes() ?>>
<div<?php echo $formato_coreo->Sufijo->ViewAttributes() ?>><?php echo $formato_coreo->Sufijo->ViewValue ?></div>
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
	global $conn, $Security, $formato_coreo;
	$DeleteRows = TRUE;
	$sWrkFilter = $formato_coreo->CurrentFilter;

	// Set up filter (Sql Where Clause) and get Return Sql
	// Sql constructor in formato_coreo class, formato_coreoinfo.php

	$formato_coreo->CurrentFilter = $sWrkFilter;
	$sSql = $formato_coreo->SQL();
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
			$DeleteRows = $formato_coreo->Row_Deleting($row);
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
			$DeleteRows = $conn->Execute($formato_coreo->DeleteSQL($row)); // Delete
			$conn->raiseErrorFn = '';
			if ($DeleteRows === FALSE)
				break;
			if ($sKey <> "") $sKey .= ", ";
			$sKey .= $sThisKey;
		}
	} else {

		// Set up error message
		if ($formato_coreo->CancelMessage <> "") {
			$_SESSION[EW_SESSION_MESSAGE] = $formato_coreo->CancelMessage;
			$formato_coreo->CancelMessage = "";
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
			$formato_coreo->Row_Deleted($row);
		}	
	}
	return $DeleteRows;
}
?>
<?php

// Load recordset
function LoadRecordset($offset = -1, $rowcnt = -1) {
	global $conn, $formato_coreo;

	// Call Recordset Selecting event
	$formato_coreo->Recordset_Selecting($formato_coreo->CurrentFilter);

	// Load list page sql
	$sSql = $formato_coreo->SelectSQL();
	if ($offset > -1 && $rowcnt > -1) $sSql .= " LIMIT $offset, $rowcnt";

	// Load recordset
	$conn->raiseErrorFn = 'ew_ErrorFn';	
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';

	// Call Recordset Selected event
	$formato_coreo->Recordset_Selected($rs);
	return $rs;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $formato_coreo;
	$sFilter = $formato_coreo->SqlKeyFilter();
	if (!is_numeric($formato_coreo->id->CurrentValue)) {
		return FALSE; // Invalid key, exit
	}
	$sFilter = str_replace("@id@", ew_AdjustSql($formato_coreo->id->CurrentValue), $sFilter); // Replace key value

	// Call Row Selecting event
	$formato_coreo->Row_Selecting($sFilter);

	// Load sql based on filter
	$formato_coreo->CurrentFilter = $sFilter;
	$sSql = $formato_coreo->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$formato_coreo->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $formato_coreo;
	$formato_coreo->id->setDbValue($rs->fields('id'));
	$formato_coreo->Nombre->setDbValue($rs->fields('Nombre'));
	$formato_coreo->Precio->setDbValue($rs->fields('Precio'));
	$formato_coreo->Sufijo->setDbValue($rs->fields('Sufijo'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $formato_coreo;

	// Call Row Rendering event
	$formato_coreo->Row_Rendering();

	// Common render codes for all row types
	// Nombre

	$formato_coreo->Nombre->CellCssStyle = "";
	$formato_coreo->Nombre->CellCssClass = "";

	// Precio
	$formato_coreo->Precio->CellCssStyle = "";
	$formato_coreo->Precio->CellCssClass = "";

	// Sufijo
	$formato_coreo->Sufijo->CellCssStyle = "";
	$formato_coreo->Sufijo->CellCssClass = "";
	if ($formato_coreo->RowType == EW_ROWTYPE_VIEW) { // View row

		// Nombre
		$formato_coreo->Nombre->ViewValue = $formato_coreo->Nombre->CurrentValue;
		$formato_coreo->Nombre->CssStyle = "";
		$formato_coreo->Nombre->CssClass = "";
		$formato_coreo->Nombre->ViewCustomAttributes = "";

		// Precio
		$formato_coreo->Precio->ViewValue = $formato_coreo->Precio->CurrentValue;
		$formato_coreo->Precio->CssStyle = "";
		$formato_coreo->Precio->CssClass = "";
		$formato_coreo->Precio->ViewCustomAttributes = "";

		// Sufijo
		$formato_coreo->Sufijo->ViewValue = $formato_coreo->Sufijo->CurrentValue;
		$formato_coreo->Sufijo->CssStyle = "";
		$formato_coreo->Sufijo->CssClass = "";
		$formato_coreo->Sufijo->ViewCustomAttributes = "";

		// Nombre
		$formato_coreo->Nombre->HrefValue = "";

		// Precio
		$formato_coreo->Precio->HrefValue = "";

		// Sufijo
		$formato_coreo->Sufijo->HrefValue = "";
	} elseif ($formato_coreo->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($formato_coreo->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($formato_coreo->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$formato_coreo->Row_Rendered();
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
