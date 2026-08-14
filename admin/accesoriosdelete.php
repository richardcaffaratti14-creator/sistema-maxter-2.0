<?php
define("EW_PAGE_ID", "delete", TRUE); // Page ID
define("EW_TABLE_NAME", 'accesorios', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "accesoriosinfo.php" ?>
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
$Security->LoadCurrentUserLevel('accesorios');
if (!$Security->IsLoggedIn()) {
	$Security->SaveLastUrl();
	Page_Terminate("login.php");
}
if (!$Security->CanDelete()) {
	$Security->SaveLastUrl();
	Page_Terminate("accesorioslist.php");
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
$accesorios->Export = @$_GET["export"]; // Get export parameter
$sExport = $accesorios->Export; // Get export parameter, used in header
$sExportFile = $accesorios->TableVar; // Get export file, used in header
?>
<?php

// Load Key Parameters
$sKey = "";
$bSingleDelete = TRUE; // Initialize as single delete
$arRecKeys = array();
$nKeySelected = 0; // Initialize selected key count
$sFilter = "";
if (@$_GET["id"] <> "") {
	$accesorios->id->setQueryStringValue($_GET["id"]);
	if (!is_numeric($accesorios->id->QueryStringValue)) {
		Page_Terminate($accesorios->getReturnUrl()); // Prevent sql injection, exit
	}
	$sKey .= $accesorios->id->QueryStringValue;
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
if ($nKeySelected <= 0) Page_Terminate($accesorios->getReturnUrl()); // No key specified, exit

// Build filter
foreach ($arRecKeys as $sKey) {
	$sFilter .= "(";

	// Set up key field
	$sKeyFld = $sKey;
	if (!is_numeric($sKeyFld)) {
		Page_Terminate($accesorios->getReturnUrl()); // Prevent sql injection, exit
	}
	$sFilter .= "`id`=" . ew_AdjustSql($sKeyFld) . " AND ";
	if (substr($sFilter, -5) == " AND ") $sFilter = substr($sFilter, 0, strlen($sFilter)-5) . ") OR ";
}
if (substr($sFilter, -4) == " OR ") $sFilter = substr($sFilter, 0, strlen($sFilter)-4);

// Set up filter (Sql Where Clause) and get Return Sql
// Sql constructor in accesorios class, accesoriosinfo.php

$accesorios->CurrentFilter = $sFilter;

// Get action
if (@$_POST["a_delete"] <> "") {
	$accesorios->CurrentAction = $_POST["a_delete"];
} else {
	$accesorios->CurrentAction = "I"; // Display record
}
switch ($accesorios->CurrentAction) {
	case "D": // Delete
		$accesorios->SendEmail = TRUE; // Send email on delete success
		if (DeleteRows()) { // delete rows
			$_SESSION[EW_SESSION_MESSAGE] = "Borrado satisfactorio"; // Set up success message
			Page_Terminate($accesorios->getReturnUrl()); // Return to caller
		}
}

// Load records for display
$rs = LoadRecordset();
$nTotalRecs = $rs->RecordCount(); // Get record count
if ($nTotalRecs <= 0) { // No record found, exit
	$rs->Close();
	Page_Terminate($accesorios->getReturnUrl()); // Return to caller
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
<p><span class="phpmaker">Borrar desde TABLA: Accesorios<br><br><a href="<?php echo $accesorios->getReturnUrl() ?>">Volver atrás</a></span></p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form action="accesoriosdelete.php" method="post">
<p>
<input type="hidden" name="a_delete" id="a_delete" value="D">
<?php foreach ($arRecKeys as $sKey) { ?>
<input type="hidden" name="key_m[]" id="key_m[]" value="<?php echo ew_HtmlEncode($sKey) ?>">
<?php } ?>
<table class="ewTable">
	<tr class="ewTableHeader">
		<td valign="top">Nombre</td>
		<td valign="top">Precio</td>
		<td valign="top">Activo</td>
	</tr>
<?php
$nRecCount = 0;
$i = 0;
while (!$rs->EOF) {
	$nRecCount++;

	// Set row class and style
	$accesorios->CssClass = "ewTableRow";
	$accesorios->CssStyle = "";

	// Display alternate color for rows
	if ($nRecCount % 2 <> 1) {
		$accesorios->CssClass = "ewTableAltRow";
	}

	// Get the field contents
	LoadRowValues($rs);

	// Render row value
	$accesorios->RowType = EW_ROWTYPE_VIEW; // view
	RenderRow();
?>
	<tr<?php echo $accesorios->DisplayAttributes() ?>>
		<td<?php echo $accesorios->nombre->CellAttributes() ?>>
<div<?php echo $accesorios->nombre->ViewAttributes() ?>><?php echo $accesorios->nombre->ViewValue ?></div>
</td>
		<td<?php echo $accesorios->precio->CellAttributes() ?>>
<div<?php echo $accesorios->precio->ViewAttributes() ?>><?php echo $accesorios->precio->ViewValue ?></div>
</td>
		<td<?php echo $accesorios->activo->CellAttributes() ?>>
<div<?php echo $accesorios->activo->ViewAttributes() ?>><?php echo $accesorios->activo->ViewValue ?></div>
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
	global $conn, $Security, $accesorios;
	$DeleteRows = TRUE;
	$sWrkFilter = $accesorios->CurrentFilter;

	// Set up filter (Sql Where Clause) and get Return Sql
	// Sql constructor in accesorios class, accesoriosinfo.php

	$accesorios->CurrentFilter = $sWrkFilter;
	$sSql = $accesorios->SQL();
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
			$DeleteRows = $accesorios->Row_Deleting($row);
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
			$DeleteRows = $conn->Execute($accesorios->DeleteSQL($row)); // Delete
			$conn->raiseErrorFn = '';
			if ($DeleteRows === FALSE)
				break;
			if ($sKey <> "") $sKey .= ", ";
			$sKey .= $sThisKey;
		}
	} else {

		// Set up error message
		if ($accesorios->CancelMessage <> "") {
			$_SESSION[EW_SESSION_MESSAGE] = $accesorios->CancelMessage;
			$accesorios->CancelMessage = "";
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
			$accesorios->Row_Deleted($row);
		}	
	}
	return $DeleteRows;
}
?>
<?php

// Load recordset
function LoadRecordset($offset = -1, $rowcnt = -1) {
	global $conn, $accesorios;

	// Call Recordset Selecting event
	$accesorios->Recordset_Selecting($accesorios->CurrentFilter);

	// Load list page sql
	$sSql = $accesorios->SelectSQL();
	if ($offset > -1 && $rowcnt > -1) $sSql .= " LIMIT $offset, $rowcnt";

	// Load recordset
	$conn->raiseErrorFn = 'ew_ErrorFn';	
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';

	// Call Recordset Selected event
	$accesorios->Recordset_Selected($rs);
	return $rs;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $accesorios;
	$sFilter = $accesorios->SqlKeyFilter();
	if (!is_numeric($accesorios->id->CurrentValue)) {
		return FALSE; // Invalid key, exit
	}
	$sFilter = str_replace("@id@", ew_AdjustSql($accesorios->id->CurrentValue), $sFilter); // Replace key value

	// Call Row Selecting event
	$accesorios->Row_Selecting($sFilter);

	// Load sql based on filter
	$accesorios->CurrentFilter = $sFilter;
	$sSql = $accesorios->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$accesorios->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $accesorios;
	$accesorios->id->setDbValue($rs->fields('id'));
	$accesorios->nombre->setDbValue($rs->fields('nombre'));
	$accesorios->precio->setDbValue($rs->fields('precio'));
	$accesorios->activo->setDbValue($rs->fields('activo'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $accesorios;

	// Call Row Rendering event
	$accesorios->Row_Rendering();

	// Common render codes for all row types
	// nombre

	$accesorios->nombre->CellCssStyle = "";
	$accesorios->nombre->CellCssClass = "";

	// precio
	$accesorios->precio->CellCssStyle = "";
	$accesorios->precio->CellCssClass = "";

	// activo
	$accesorios->activo->CellCssStyle = "";
	$accesorios->activo->CellCssClass = "";
	if ($accesorios->RowType == EW_ROWTYPE_VIEW) { // View row

		// nombre
		$accesorios->nombre->ViewValue = $accesorios->nombre->CurrentValue;
		$accesorios->nombre->CssStyle = "";
		$accesorios->nombre->CssClass = "";
		$accesorios->nombre->ViewCustomAttributes = "";

		// precio
		$accesorios->precio->ViewValue = $accesorios->precio->CurrentValue;
		$accesorios->precio->CssStyle = "";
		$accesorios->precio->CssClass = "";
		$accesorios->precio->ViewCustomAttributes = "";

		// activo
		if (!is_null($accesorios->activo->CurrentValue)) {
			switch ($accesorios->activo->CurrentValue) {
				case "1":
					$accesorios->activo->ViewValue = "Activo";
					break;
				case "0":
					$accesorios->activo->ViewValue = "Inactivo";
					break;
				default:
					$accesorios->activo->ViewValue = $accesorios->activo->CurrentValue;
			}
		} else {
			$accesorios->activo->ViewValue = NULL;
		}
		$accesorios->activo->CssStyle = "";
		$accesorios->activo->CssClass = "";
		$accesorios->activo->ViewCustomAttributes = "";

		// nombre
		$accesorios->nombre->HrefValue = "";

		// precio
		$accesorios->precio->HrefValue = "";

		// activo
		$accesorios->activo->HrefValue = "";
	} elseif ($accesorios->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($accesorios->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($accesorios->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$accesorios->Row_Rendered();
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
