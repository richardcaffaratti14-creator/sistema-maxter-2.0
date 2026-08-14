<?php
define("EW_PAGE_ID", "delete", TRUE); // Page ID
define("EW_TABLE_NAME", 'presupuestos', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "presupuestosinfo.php" ?>
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
$Security->LoadCurrentUserLevel('presupuestos');
if (!$Security->IsLoggedIn()) {
	$Security->SaveLastUrl();
	Page_Terminate("login.php");
}
if (!$Security->CanDelete()) {
	$Security->SaveLastUrl();
	Page_Terminate("presupuestoslist.php");
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
$presupuestos->Export = @$_GET["export"]; // Get export parameter
$sExport = $presupuestos->Export; // Get export parameter, used in header
$sExportFile = $presupuestos->TableVar; // Get export file, used in header
?>
<?php

// Load Key Parameters
$sKey = "";
$bSingleDelete = TRUE; // Initialize as single delete
$arRecKeys = array();
$nKeySelected = 0; // Initialize selected key count
$sFilter = "";
if (@$_GET["id"] <> "") {
	$presupuestos->id->setQueryStringValue($_GET["id"]);
	if (!is_numeric($presupuestos->id->QueryStringValue)) {
		Page_Terminate($presupuestos->getReturnUrl()); // Prevent sql injection, exit
	}
	$sKey .= $presupuestos->id->QueryStringValue;
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
if ($nKeySelected <= 0) Page_Terminate($presupuestos->getReturnUrl()); // No key specified, exit

// Build filter
foreach ($arRecKeys as $sKey) {
	$sFilter .= "(";

	// Set up key field
	$sKeyFld = $sKey;
	if (!is_numeric($sKeyFld)) {
		Page_Terminate($presupuestos->getReturnUrl()); // Prevent sql injection, exit
	}
	$sFilter .= "`id`=" . ew_AdjustSql($sKeyFld) . " AND ";
	if (substr($sFilter, -5) == " AND ") $sFilter = substr($sFilter, 0, strlen($sFilter)-5) . ") OR ";
}
if (substr($sFilter, -4) == " OR ") $sFilter = substr($sFilter, 0, strlen($sFilter)-4);

// Set up filter (Sql Where Clause) and get Return Sql
// Sql constructor in presupuestos class, presupuestosinfo.php

$presupuestos->CurrentFilter = $sFilter;

// Get action
if (@$_POST["a_delete"] <> "") {
	$presupuestos->CurrentAction = $_POST["a_delete"];
} else {
	$presupuestos->CurrentAction = "I"; // Display record
}
switch ($presupuestos->CurrentAction) {
	case "D": // Delete
		$presupuestos->SendEmail = TRUE; // Send email on delete success
		if (DeleteRows()) { // delete rows
			$_SESSION[EW_SESSION_MESSAGE] = "Borrado satisfactorio"; // Set up success message
			Page_Terminate($presupuestos->getReturnUrl()); // Return to caller
		}
}

// Load records for display
$rs = LoadRecordset();
$nTotalRecs = $rs->RecordCount(); // Get record count
if ($nTotalRecs <= 0) { // No record found, exit
	$rs->Close();
	Page_Terminate($presupuestos->getReturnUrl()); // Return to caller
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
<p><span class="phpmaker">Borrar desde TABLA: Presupuestos<br><br><a href="<?php echo $presupuestos->getReturnUrl() ?>">Volver atrás</a></span></p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form action="presupuestosdelete.php" method="post">
<p>
<input type="hidden" name="a_delete" id="a_delete" value="D">
<?php foreach ($arRecKeys as $sKey) { ?>
<input type="hidden" name="key_m[]" id="key_m[]" value="<?php echo ew_HtmlEncode($sKey) ?>">
<?php } ?>
<table class="ewTable">
	<tr class="ewTableHeader">
		<td valign="top">Número</td>
		<td valign="top">Nombre</td>
		<td valign="top">Apellido</td>
		<td valign="top">Teléfono</td>
		<td valign="top">Vendedor</td>
		<td valign="top">Evento</td>
		<td valign="top">Seña</td>
		<td valign="top">Subtotal</td>
		<td valign="top">Descuento</td>
		<td valign="top">Total</td>
		<td valign="top">Estado</td>
		<td valign="top">Pago</td>
	</tr>
<?php
$nRecCount = 0;
$i = 0;
while (!$rs->EOF) {
	$nRecCount++;

	// Set row class and style
	$presupuestos->CssClass = "ewTableRow";
	$presupuestos->CssStyle = "";

	// Display alternate color for rows
	if ($nRecCount % 2 <> 1) {
		$presupuestos->CssClass = "ewTableAltRow";
	}

	// Get the field contents
	LoadRowValues($rs);

	// Render row value
	$presupuestos->RowType = EW_ROWTYPE_VIEW; // view
	RenderRow();
?>
	<tr<?php echo $presupuestos->DisplayAttributes() ?>>
		<td<?php echo $presupuestos->id->CellAttributes() ?>>
<div<?php echo $presupuestos->id->ViewAttributes() ?>><?php echo $presupuestos->id->ViewValue ?></div>
</td>
		<td<?php echo $presupuestos->nombre->CellAttributes() ?>>
<div<?php echo $presupuestos->nombre->ViewAttributes() ?>><?php echo $presupuestos->nombre->ViewValue ?></div>
</td>
		<td<?php echo $presupuestos->apellido->CellAttributes() ?>>
<div<?php echo $presupuestos->apellido->ViewAttributes() ?>><?php echo $presupuestos->apellido->ViewValue ?></div>
</td>
		<td<?php echo $presupuestos->telefono->CellAttributes() ?>>
<div<?php echo $presupuestos->telefono->ViewAttributes() ?>><?php echo $presupuestos->telefono->ViewValue ?></div>
</td>
		<td<?php echo $presupuestos->idVendedor->CellAttributes() ?>>
<div<?php echo $presupuestos->idVendedor->ViewAttributes() ?>><?php echo $presupuestos->idVendedor->ViewValue ?></div>
</td>
		<td<?php echo $presupuestos->evento->CellAttributes() ?>>
<div<?php echo $presupuestos->evento->ViewAttributes() ?>><?php echo $presupuestos->evento->ViewValue ?></div>
</td>
		<td<?php echo $presupuestos->sena->CellAttributes() ?>>
<div<?php echo $presupuestos->sena->ViewAttributes() ?>><?php echo $presupuestos->sena->ViewValue ?></div>
</td>
		<td<?php echo $presupuestos->subtotal->CellAttributes() ?>>
<div<?php echo $presupuestos->subtotal->ViewAttributes() ?>><?php echo $presupuestos->subtotal->ViewValue ?></div>
</td>
		<td<?php echo $presupuestos->descuento->CellAttributes() ?>>
<div<?php echo $presupuestos->descuento->ViewAttributes() ?>><?php echo $presupuestos->descuento->ViewValue ?></div>
</td>
		<td<?php echo $presupuestos->total->CellAttributes() ?>>
<div<?php echo $presupuestos->total->ViewAttributes() ?>><?php echo $presupuestos->total->ViewValue ?></div>
</td>
		<td<?php echo $presupuestos->estado->CellAttributes() ?>>
<div<?php echo $presupuestos->estado->ViewAttributes() ?>><?php echo $presupuestos->estado->ViewValue ?></div>
</td>
		<td<?php echo $presupuestos->presu_tarjeta->CellAttributes() ?>>
<div<?php echo $presupuestos->presu_tarjeta->ViewAttributes() ?>><?php echo $presupuestos->presu_tarjeta->ViewValue ?></div>
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
	global $conn, $Security, $presupuestos;
	$DeleteRows = TRUE;
	$sWrkFilter = $presupuestos->CurrentFilter;

	// Set up filter (Sql Where Clause) and get Return Sql
	// Sql constructor in presupuestos class, presupuestosinfo.php

	$presupuestos->CurrentFilter = $sWrkFilter;
	$sSql = $presupuestos->SQL();
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
			$DeleteRows = $presupuestos->Row_Deleting($row);
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
			$DeleteRows = $conn->Execute($presupuestos->DeleteSQL($row)); // Delete
			$conn->raiseErrorFn = '';
			if ($DeleteRows === FALSE)
				break;
			if ($sKey <> "") $sKey .= ", ";
			$sKey .= $sThisKey;
		}
	} else {

		// Set up error message
		if ($presupuestos->CancelMessage <> "") {
			$_SESSION[EW_SESSION_MESSAGE] = $presupuestos->CancelMessage;
			$presupuestos->CancelMessage = "";
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
			$presupuestos->Row_Deleted($row);
		}	
	}
	return $DeleteRows;
}
?>
<?php

// Load recordset
function LoadRecordset($offset = -1, $rowcnt = -1) {
	global $conn, $presupuestos;

	// Call Recordset Selecting event
	$presupuestos->Recordset_Selecting($presupuestos->CurrentFilter);

	// Load list page sql
	$sSql = $presupuestos->SelectSQL();
	if ($offset > -1 && $rowcnt > -1) $sSql .= " LIMIT $offset, $rowcnt";

	// Load recordset
	$conn->raiseErrorFn = 'ew_ErrorFn';	
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';

	// Call Recordset Selected event
	$presupuestos->Recordset_Selected($rs);
	return $rs;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $presupuestos;
	$sFilter = $presupuestos->SqlKeyFilter();
	if (!is_numeric($presupuestos->id->CurrentValue)) {
		return FALSE; // Invalid key, exit
	}
	$sFilter = str_replace("@id@", ew_AdjustSql($presupuestos->id->CurrentValue), $sFilter); // Replace key value

	// Call Row Selecting event
	$presupuestos->Row_Selecting($sFilter);

	// Load sql based on filter
	$presupuestos->CurrentFilter = $sFilter;
	$sSql = $presupuestos->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$presupuestos->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $presupuestos;
	$presupuestos->id->setDbValue($rs->fields('id'));
	$presupuestos->nombre->setDbValue($rs->fields('nombre'));
	$presupuestos->apellido->setDbValue($rs->fields('apellido'));
	$presupuestos->telefono->setDbValue($rs->fields('telefono'));
	$presupuestos->idVendedor->setDbValue($rs->fields('idVendedor'));
	$presupuestos->evento->setDbValue($rs->fields('evento'));
	$presupuestos->presupuesto->setDbValue($rs->fields('presupuesto'));
	$presupuestos->pedido->setDbValue($rs->fields('pedido'));
	$presupuestos->sena->setDbValue($rs->fields('sena'));
	$presupuestos->subtotal->setDbValue($rs->fields('subtotal'));
	$presupuestos->descuento->setDbValue($rs->fields('descuento'));
	$presupuestos->total->setDbValue($rs->fields('total'));
	$presupuestos->estado->setDbValue($rs->fields('estado'));
	$presupuestos->presu_tarjeta->setDbValue($rs->fields('presu_tarjeta'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $presupuestos;

	// Call Row Rendering event
	$presupuestos->Row_Rendering();

	// Common render codes for all row types
	// id

	$presupuestos->id->CellCssStyle = "";
	$presupuestos->id->CellCssClass = "";

	// nombre
	$presupuestos->nombre->CellCssStyle = "";
	$presupuestos->nombre->CellCssClass = "";

	// apellido
	$presupuestos->apellido->CellCssStyle = "";
	$presupuestos->apellido->CellCssClass = "";

	// telefono
	$presupuestos->telefono->CellCssStyle = "";
	$presupuestos->telefono->CellCssClass = "";

	// idVendedor
	$presupuestos->idVendedor->CellCssStyle = "";
	$presupuestos->idVendedor->CellCssClass = "";

	// evento
	$presupuestos->evento->CellCssStyle = "";
	$presupuestos->evento->CellCssClass = "";

	// sena
	$presupuestos->sena->CellCssStyle = "";
	$presupuestos->sena->CellCssClass = "";

	// subtotal
	$presupuestos->subtotal->CellCssStyle = "";
	$presupuestos->subtotal->CellCssClass = "";

	// descuento
	$presupuestos->descuento->CellCssStyle = "";
	$presupuestos->descuento->CellCssClass = "";

	// total
	$presupuestos->total->CellCssStyle = "";
	$presupuestos->total->CellCssClass = "";

	// estado
	$presupuestos->estado->CellCssStyle = "";
	$presupuestos->estado->CellCssClass = "";

	// presu_tarjeta
	$presupuestos->presu_tarjeta->CellCssStyle = "";
	$presupuestos->presu_tarjeta->CellCssClass = "";
	if ($presupuestos->RowType == EW_ROWTYPE_VIEW) { // View row

		// id
		$presupuestos->id->ViewValue = $presupuestos->id->CurrentValue;
		$presupuestos->id->CssStyle = "";
		$presupuestos->id->CssClass = "";
		$presupuestos->id->ViewCustomAttributes = "";

		// nombre
		$presupuestos->nombre->ViewValue = $presupuestos->nombre->CurrentValue;
		$presupuestos->nombre->CssStyle = "";
		$presupuestos->nombre->CssClass = "";
		$presupuestos->nombre->ViewCustomAttributes = "";

		// apellido
		$presupuestos->apellido->ViewValue = $presupuestos->apellido->CurrentValue;
		$presupuestos->apellido->CssStyle = "";
		$presupuestos->apellido->CssClass = "";
		$presupuestos->apellido->ViewCustomAttributes = "";

		// telefono
		$presupuestos->telefono->ViewValue = $presupuestos->telefono->CurrentValue;
		$presupuestos->telefono->CssStyle = "";
		$presupuestos->telefono->CssClass = "";
		$presupuestos->telefono->ViewCustomAttributes = "";

		// idVendedor
		if (!empty($presupuestos->idVendedor->CurrentValue)) {
			$sSqlWrk = "SELECT `Vendedor` FROM `vendedores` WHERE `id` = " . ew_AdjustSql($presupuestos->idVendedor->CurrentValue) . "";
			$rswrk = $conn->Execute($sSqlWrk);
			if ($rswrk) {
				if (!$rswrk->EOF) {
					$presupuestos->idVendedor->ViewValue = $rswrk->fields('Vendedor');
				}
				$rswrk->Close();
			} else {
				$presupuestos->idVendedor->ViewValue = $presupuestos->idVendedor->CurrentValue;
			}
		} else {
			$presupuestos->idVendedor->ViewValue = NULL;
		}
		$presupuestos->idVendedor->CssStyle = "";
		$presupuestos->idVendedor->CssClass = "";
		$presupuestos->idVendedor->ViewCustomAttributes = "";

		// evento
		$presupuestos->evento->ViewValue = $presupuestos->evento->CurrentValue;
		$presupuestos->evento->CssStyle = "";
		$presupuestos->evento->CssClass = "";
		$presupuestos->evento->ViewCustomAttributes = "";

		// sena
		$presupuestos->sena->ViewValue = $presupuestos->sena->CurrentValue;
		$presupuestos->sena->ViewValue = ew_FormatCurrency($presupuestos->sena->ViewValue, 2, -2, -2, -2);
		$presupuestos->sena->CssStyle = "";
		$presupuestos->sena->CssClass = "";
		$presupuestos->sena->ViewCustomAttributes = "";

		// subtotal
		$presupuestos->subtotal->ViewValue = $presupuestos->subtotal->CurrentValue;
		$presupuestos->subtotal->ViewValue = ew_FormatCurrency($presupuestos->subtotal->ViewValue, 2, -2, -2, -2);
		$presupuestos->subtotal->CssStyle = "";
		$presupuestos->subtotal->CssClass = "";
		$presupuestos->subtotal->ViewCustomAttributes = "";

		// descuento
		$presupuestos->descuento->ViewValue = $presupuestos->descuento->CurrentValue;
		$presupuestos->descuento->ViewValue = ew_FormatCurrency($presupuestos->descuento->ViewValue, 2, -2, -2, -2);
		$presupuestos->descuento->CssStyle = "";
		$presupuestos->descuento->CssClass = "";
		$presupuestos->descuento->ViewCustomAttributes = "";

		// total
		$presupuestos->total->ViewValue = $presupuestos->total->CurrentValue;
		$presupuestos->total->ViewValue = ew_FormatCurrency($presupuestos->total->ViewValue, 2, -2, -2, -2);
		$presupuestos->total->CssStyle = "";
		$presupuestos->total->CssClass = "";
		$presupuestos->total->ViewCustomAttributes = "";

		// estado
		if (!is_null($presupuestos->estado->CurrentValue)) {
			switch ($presupuestos->estado->CurrentValue) {
				case "0":
					$presupuestos->estado->ViewValue = "Pendiente";
					break;
				case "1":
					$presupuestos->estado->ViewValue = "Pagado";
					break;
				case "2":
					$presupuestos->estado->ViewValue = "Cancelado";
					break;
				default:
					$presupuestos->estado->ViewValue = $presupuestos->estado->CurrentValue;
			}
		} else {
			$presupuestos->estado->ViewValue = NULL;
		}
		$presupuestos->estado->CssStyle = "";
		$presupuestos->estado->CssClass = "";
		$presupuestos->estado->ViewCustomAttributes = "";

		// presu_tarjeta
		if (!is_null($presupuestos->presu_tarjeta->CurrentValue)) {
			switch ($presupuestos->presu_tarjeta->CurrentValue) {
				case "0":
					$presupuestos->presu_tarjeta->ViewValue = "Contado";
					break;
				case "1":
					$presupuestos->presu_tarjeta->ViewValue = "TARJETA";
					break;
				default:
					$presupuestos->presu_tarjeta->ViewValue = $presupuestos->presu_tarjeta->CurrentValue;
			}
		} else {
			$presupuestos->presu_tarjeta->ViewValue = NULL;
		}
		$presupuestos->presu_tarjeta->CssStyle = "";
		$presupuestos->presu_tarjeta->CssClass = "";
		$presupuestos->presu_tarjeta->ViewCustomAttributes = "";

		// id
		$presupuestos->id->HrefValue = "";

		// nombre
		$presupuestos->nombre->HrefValue = "";

		// apellido
		$presupuestos->apellido->HrefValue = "";

		// telefono
		$presupuestos->telefono->HrefValue = "";

		// idVendedor
		$presupuestos->idVendedor->HrefValue = "";

		// evento
		$presupuestos->evento->HrefValue = "";

		// sena
		$presupuestos->sena->HrefValue = "";

		// subtotal
		$presupuestos->subtotal->HrefValue = "";

		// descuento
		$presupuestos->descuento->HrefValue = "";

		// total
		$presupuestos->total->HrefValue = "";

		// estado
		$presupuestos->estado->HrefValue = "";

		// presu_tarjeta
		$presupuestos->presu_tarjeta->HrefValue = "";
	} elseif ($presupuestos->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($presupuestos->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($presupuestos->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$presupuestos->Row_Rendered();
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
