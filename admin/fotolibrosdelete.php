<?php
define("EW_PAGE_ID", "delete", TRUE); // Page ID
define("EW_TABLE_NAME", 'fotolibros', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "fotolibrosinfo.php" ?>
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
$Security->LoadCurrentUserLevel('fotolibros');
if (!$Security->IsLoggedIn()) {
	$Security->SaveLastUrl();
	Page_Terminate("login.php");
}
if (!$Security->CanDelete()) {
	$Security->SaveLastUrl();
	Page_Terminate("fotolibroslist.php");
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
$fotolibros->Export = @$_GET["export"]; // Get export parameter
$sExport = $fotolibros->Export; // Get export parameter, used in header
$sExportFile = $fotolibros->TableVar; // Get export file, used in header
?>
<?php

// Load Key Parameters
$sKey = "";
$bSingleDelete = TRUE; // Initialize as single delete
$arRecKeys = array();
$nKeySelected = 0; // Initialize selected key count
$sFilter = "";
if (@$_GET["id"] <> "") {
	$fotolibros->id->setQueryStringValue($_GET["id"]);
	if (!is_numeric($fotolibros->id->QueryStringValue)) {
		Page_Terminate($fotolibros->getReturnUrl()); // Prevent sql injection, exit
	}
	$sKey .= $fotolibros->id->QueryStringValue;
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
if ($nKeySelected <= 0) Page_Terminate($fotolibros->getReturnUrl()); // No key specified, exit

// Build filter
foreach ($arRecKeys as $sKey) {
	$sFilter .= "(";

	// Set up key field
	$sKeyFld = $sKey;
	if (!is_numeric($sKeyFld)) {
		Page_Terminate($fotolibros->getReturnUrl()); // Prevent sql injection, exit
	}
	$sFilter .= "`id`=" . ew_AdjustSql($sKeyFld) . " AND ";
	if (substr($sFilter, -5) == " AND ") $sFilter = substr($sFilter, 0, strlen($sFilter)-5) . ") OR ";
}
if (substr($sFilter, -4) == " OR ") $sFilter = substr($sFilter, 0, strlen($sFilter)-4);

// Set up filter (Sql Where Clause) and get Return Sql
// Sql constructor in fotolibros class, fotolibrosinfo.php

$fotolibros->CurrentFilter = $sFilter;

// Get action
if (@$_POST["a_delete"] <> "") {
	$fotolibros->CurrentAction = $_POST["a_delete"];
} else {
	$fotolibros->CurrentAction = "I"; // Display record
}
switch ($fotolibros->CurrentAction) {
	case "D": // Delete
		$fotolibros->SendEmail = TRUE; // Send email on delete success
		if (DeleteRows()) { // delete rows
			$_SESSION[EW_SESSION_MESSAGE] = "Borrado satisfactorio"; // Set up success message
			Page_Terminate($fotolibros->getReturnUrl()); // Return to caller
		}
}

// Load records for display
$rs = LoadRecordset();
$nTotalRecs = $rs->RecordCount(); // Get record count
if ($nTotalRecs <= 0) { // No record found, exit
	$rs->Close();
	Page_Terminate($fotolibros->getReturnUrl()); // Return to caller
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
<p><span class="phpmaker">Borrar desde TABLA: Fotolibros<br><br><a href="<?php echo $fotolibros->getReturnUrl() ?>">Volver atrás</a></span></p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form action="fotolibrosdelete.php" method="post">
<p>
<input type="hidden" name="a_delete" id="a_delete" value="D">
<?php foreach ($arRecKeys as $sKey) { ?>
<input type="hidden" name="key_m[]" id="key_m[]" value="<?php echo ew_HtmlEncode($sKey) ?>">
<?php } ?>
<table class="ewTable">
	<tr class="ewTableHeader">
		<td valign="top">Nombre</td>
		<td valign="top">Apellido</td>
		<td valign="top">Telefono</td>
		<td valign="top">Vendedor</td>
		<td valign="top">Seña</td>
		<td valign="top">Total</td>
		<td valign="top">Evento</td>
	</tr>
<?php
$nRecCount = 0;
$i = 0;
while (!$rs->EOF) {
	$nRecCount++;

	// Set row class and style
	$fotolibros->CssClass = "ewTableRow";
	$fotolibros->CssStyle = "";

	// Display alternate color for rows
	if ($nRecCount % 2 <> 1) {
		$fotolibros->CssClass = "ewTableAltRow";
	}

	// Get the field contents
	LoadRowValues($rs);

	// Render row value
	$fotolibros->RowType = EW_ROWTYPE_VIEW; // view
	RenderRow();
?>
	<tr<?php echo $fotolibros->DisplayAttributes() ?>>
		<td<?php echo $fotolibros->nombre->CellAttributes() ?>>
<div<?php echo $fotolibros->nombre->ViewAttributes() ?>><?php echo $fotolibros->nombre->ViewValue ?></div>
</td>
		<td<?php echo $fotolibros->apellido->CellAttributes() ?>>
<div<?php echo $fotolibros->apellido->ViewAttributes() ?>><?php echo $fotolibros->apellido->ViewValue ?></div>
</td>
		<td<?php echo $fotolibros->telefono->CellAttributes() ?>>
<div<?php echo $fotolibros->telefono->ViewAttributes() ?>><?php echo $fotolibros->telefono->ViewValue ?></div>
</td>
		<td<?php echo $fotolibros->idVendedor->CellAttributes() ?>>
<div<?php echo $fotolibros->idVendedor->ViewAttributes() ?>><?php echo $fotolibros->idVendedor->ViewValue ?></div>
</td>
		<td<?php echo $fotolibros->sena->CellAttributes() ?>>
<div<?php echo $fotolibros->sena->ViewAttributes() ?>><?php echo $fotolibros->sena->ViewValue ?></div>
</td>
		<td<?php echo $fotolibros->total->CellAttributes() ?>>
<div<?php echo $fotolibros->total->ViewAttributes() ?>><?php echo $fotolibros->total->ViewValue ?></div>
</td>
		<td<?php echo $fotolibros->evento->CellAttributes() ?>>
<div<?php echo $fotolibros->evento->ViewAttributes() ?>><?php echo $fotolibros->evento->ViewValue ?></div>
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
	global $conn, $Security, $fotolibros;
	$DeleteRows = TRUE;
	$sWrkFilter = $fotolibros->CurrentFilter;

	// Set up filter (Sql Where Clause) and get Return Sql
	// Sql constructor in fotolibros class, fotolibrosinfo.php

	$fotolibros->CurrentFilter = $sWrkFilter;
	$sSql = $fotolibros->SQL();
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
			$DeleteRows = $fotolibros->Row_Deleting($row);
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
			$DeleteRows = $conn->Execute($fotolibros->DeleteSQL($row)); // Delete
			$conn->raiseErrorFn = '';
			if ($DeleteRows === FALSE)
				break;
			if ($sKey <> "") $sKey .= ", ";
			$sKey .= $sThisKey;
		}
	} else {

		// Set up error message
		if ($fotolibros->CancelMessage <> "") {
			$_SESSION[EW_SESSION_MESSAGE] = $fotolibros->CancelMessage;
			$fotolibros->CancelMessage = "";
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
			$fotolibros->Row_Deleted($row);
		}	
	}
	return $DeleteRows;
}
?>
<?php

// Load recordset
function LoadRecordset($offset = -1, $rowcnt = -1) {
	global $conn, $fotolibros;

	// Call Recordset Selecting event
	$fotolibros->Recordset_Selecting($fotolibros->CurrentFilter);

	// Load list page sql
	$sSql = $fotolibros->SelectSQL();
	if ($offset > -1 && $rowcnt > -1) $sSql .= " LIMIT $offset, $rowcnt";

	// Load recordset
	$conn->raiseErrorFn = 'ew_ErrorFn';	
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';

	// Call Recordset Selected event
	$fotolibros->Recordset_Selected($rs);
	return $rs;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $fotolibros;
	$sFilter = $fotolibros->SqlKeyFilter();
	if (!is_numeric($fotolibros->id->CurrentValue)) {
		return FALSE; // Invalid key, exit
	}
	$sFilter = str_replace("@id@", ew_AdjustSql($fotolibros->id->CurrentValue), $sFilter); // Replace key value

	// Call Row Selecting event
	$fotolibros->Row_Selecting($sFilter);

	// Load sql based on filter
	$fotolibros->CurrentFilter = $sFilter;
	$sSql = $fotolibros->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$fotolibros->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $fotolibros;
	$fotolibros->id->setDbValue($rs->fields('id'));
	$fotolibros->nombre->setDbValue($rs->fields('nombre'));
	$fotolibros->apellido->setDbValue($rs->fields('apellido'));
	$fotolibros->telefono->setDbValue($rs->fields('telefono'));
	$fotolibros->pedido->setDbValue($rs->fields('pedido'));
	$fotolibros->idVendedor->setDbValue($rs->fields('idVendedor'));
	$fotolibros->estado->setDbValue($rs->fields('estado'));
	$fotolibros->sena->setDbValue($rs->fields('sena'));
	$fotolibros->total->setDbValue($rs->fields('total'));
	$fotolibros->evento->setDbValue($rs->fields('evento'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $fotolibros;

	// Call Row Rendering event
	$fotolibros->Row_Rendering();

	// Common render codes for all row types
	// nombre

	$fotolibros->nombre->CellCssStyle = "";
	$fotolibros->nombre->CellCssClass = "";

	// apellido
	$fotolibros->apellido->CellCssStyle = "";
	$fotolibros->apellido->CellCssClass = "";

	// telefono
	$fotolibros->telefono->CellCssStyle = "";
	$fotolibros->telefono->CellCssClass = "";

	// idVendedor
	$fotolibros->idVendedor->CellCssStyle = "";
	$fotolibros->idVendedor->CellCssClass = "";

	// sena
	$fotolibros->sena->CellCssStyle = "";
	$fotolibros->sena->CellCssClass = "";

	// total
	$fotolibros->total->CellCssStyle = "";
	$fotolibros->total->CellCssClass = "";

	// evento
	$fotolibros->evento->CellCssStyle = "";
	$fotolibros->evento->CellCssClass = "";
	if ($fotolibros->RowType == EW_ROWTYPE_VIEW) { // View row

		// nombre
		$fotolibros->nombre->ViewValue = $fotolibros->nombre->CurrentValue;
		$fotolibros->nombre->CssStyle = "";
		$fotolibros->nombre->CssClass = "";
		$fotolibros->nombre->ViewCustomAttributes = "";

		// apellido
		$fotolibros->apellido->ViewValue = $fotolibros->apellido->CurrentValue;
		$fotolibros->apellido->CssStyle = "";
		$fotolibros->apellido->CssClass = "";
		$fotolibros->apellido->ViewCustomAttributes = "";

		// telefono
		$fotolibros->telefono->ViewValue = $fotolibros->telefono->CurrentValue;
		$fotolibros->telefono->CssStyle = "";
		$fotolibros->telefono->CssClass = "";
		$fotolibros->telefono->ViewCustomAttributes = "";

		// idVendedor
		if (!empty($fotolibros->idVendedor->CurrentValue)) {
			$sSqlWrk = "SELECT `Vendedor` FROM `vendedores` WHERE `id` = " . ew_AdjustSql($fotolibros->idVendedor->CurrentValue) . "";
			$rswrk = $conn->Execute($sSqlWrk);
			if ($rswrk) {
				if (!$rswrk->EOF) {
					$fotolibros->idVendedor->ViewValue = $rswrk->fields('Vendedor');
				}
				$rswrk->Close();
			} else {
				$fotolibros->idVendedor->ViewValue = $fotolibros->idVendedor->CurrentValue;
			}
		} else {
			$fotolibros->idVendedor->ViewValue = NULL;
		}
		$fotolibros->idVendedor->CssStyle = "";
		$fotolibros->idVendedor->CssClass = "";
		$fotolibros->idVendedor->ViewCustomAttributes = "";

		// sena
		$fotolibros->sena->ViewValue = $fotolibros->sena->CurrentValue;
		$fotolibros->sena->ViewValue = ew_FormatCurrency($fotolibros->sena->ViewValue, 2, -2, -2, -2);
		$fotolibros->sena->CssStyle = "";
		$fotolibros->sena->CssClass = "";
		$fotolibros->sena->ViewCustomAttributes = "";

		// total
		$fotolibros->total->ViewValue = $fotolibros->total->CurrentValue;
		$fotolibros->total->ViewValue = ew_FormatCurrency($fotolibros->total->ViewValue, 2, -2, -2, -2);
		$fotolibros->total->CssStyle = "";
		$fotolibros->total->CssClass = "";
		$fotolibros->total->ViewCustomAttributes = "";

		// evento
		$fotolibros->evento->ViewValue = $fotolibros->evento->CurrentValue;
		$fotolibros->evento->CssStyle = "";
		$fotolibros->evento->CssClass = "";
		$fotolibros->evento->ViewCustomAttributes = "";

		// nombre
		$fotolibros->nombre->HrefValue = "";

		// apellido
		$fotolibros->apellido->HrefValue = "";

		// telefono
		$fotolibros->telefono->HrefValue = "";

		// idVendedor
		$fotolibros->idVendedor->HrefValue = "";

		// sena
		$fotolibros->sena->HrefValue = "";

		// total
		$fotolibros->total->HrefValue = "";

		// evento
		$fotolibros->evento->HrefValue = "";
	} elseif ($fotolibros->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($fotolibros->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($fotolibros->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$fotolibros->Row_Rendered();
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
