<?php
define("EW_PAGE_ID", "delete", TRUE); // Page ID
define("EW_TABLE_NAME", 'pedidos', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "pedidosinfo.php" ?>
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
$Security->LoadCurrentUserLevel('pedidos');
if (!$Security->IsLoggedIn()) {
	$Security->SaveLastUrl();
	Page_Terminate("login.php");
}
if (!$Security->CanDelete()) {
	$Security->SaveLastUrl();
	Page_Terminate("pedidoslist.php");
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
$pedidos->Export = @$_GET["export"]; // Get export parameter
$sExport = $pedidos->Export; // Get export parameter, used in header
$sExportFile = $pedidos->TableVar; // Get export file, used in header
?>
<?php

// Load Key Parameters
$sKey = "";
$bSingleDelete = TRUE; // Initialize as single delete
$arRecKeys = array();
$nKeySelected = 0; // Initialize selected key count
$sFilter = "";
if (@$_GET["id"] <> "") {
	$pedidos->id->setQueryStringValue($_GET["id"]);
	if (!is_numeric($pedidos->id->QueryStringValue)) {
		Page_Terminate($pedidos->getReturnUrl()); // Prevent sql injection, exit
	}
	$sKey .= $pedidos->id->QueryStringValue;
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
if ($nKeySelected <= 0) Page_Terminate($pedidos->getReturnUrl()); // No key specified, exit

// Build filter
foreach ($arRecKeys as $sKey) {
	$sFilter .= "(";

	// Set up key field
	$sKeyFld = $sKey;
	if (!is_numeric($sKeyFld)) {
		Page_Terminate($pedidos->getReturnUrl()); // Prevent sql injection, exit
	}
	$sFilter .= "`id`=" . ew_AdjustSql($sKeyFld) . " AND ";
	if (substr($sFilter, -5) == " AND ") $sFilter = substr($sFilter, 0, strlen($sFilter)-5) . ") OR ";
}
if (substr($sFilter, -4) == " OR ") $sFilter = substr($sFilter, 0, strlen($sFilter)-4);

// Set up filter (Sql Where Clause) and get Return Sql
// Sql constructor in pedidos class, pedidosinfo.php

$pedidos->CurrentFilter = $sFilter;

// Get action
if (@$_POST["a_delete"] <> "") {
	$pedidos->CurrentAction = $_POST["a_delete"];
} else {
	$pedidos->CurrentAction = "I"; // Display record
}
switch ($pedidos->CurrentAction) {
	case "D": // Delete
		$pedidos->SendEmail = TRUE; // Send email on delete success
		if (DeleteRows()) { // delete rows
			$_SESSION[EW_SESSION_MESSAGE] = "Borrado satisfactorio"; // Set up success message
			Page_Terminate($pedidos->getReturnUrl()); // Return to caller
		}
}

// Load records for display
$rs = LoadRecordset();
$nTotalRecs = $rs->RecordCount(); // Get record count
if ($nTotalRecs <= 0) { // No record found, exit
	$rs->Close();
	Page_Terminate($pedidos->getReturnUrl()); // Return to caller
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
<p><span class="phpmaker">Borrar desde TABLA: Pedidos<br><br><a href="<?php echo $pedidos->getReturnUrl() ?>">Volver atrás</a></span></p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form action="pedidosdelete.php" method="post">
<p>
<input type="hidden" name="a_delete" id="a_delete" value="D">
<?php foreach ($arRecKeys as $sKey) { ?>
<input type="hidden" name="key_m[]" id="key_m[]" value="<?php echo ew_HtmlEncode($sKey) ?>">
<?php } ?>
<table class="ewTable">
	<tr class="ewTableHeader">
		<td valign="top">Número</td>
		<td valign="top">Fecha</td>
		<td valign="top">Estado</td>
		<td valign="top">Pago</td>
		<td valign="top">Total</td>
		<td valign="top">Nombre</td>
		<td valign="top">Apellido</td>
		<td valign="top">Teléfono</td>
		<td valign="top">Vendedor</td>
		<td valign="top">Evento</td>
		<td valign="top">id Presupuesto</td>
		<td valign="top">sena</td>
	</tr>
<?php
$nRecCount = 0;
$i = 0;
while (!$rs->EOF) {
	$nRecCount++;

	// Set row class and style
	$pedidos->CssClass = "ewTableRow";
	$pedidos->CssStyle = "";

	// Display alternate color for rows
	if ($nRecCount % 2 <> 1) {
		$pedidos->CssClass = "ewTableAltRow";
	}

	// Get the field contents
	LoadRowValues($rs);

	// Render row value
	$pedidos->RowType = EW_ROWTYPE_VIEW; // view
	RenderRow();
?>
	<tr<?php echo $pedidos->DisplayAttributes() ?>>
		<td<?php echo $pedidos->id->CellAttributes() ?>>
<div<?php echo $pedidos->id->ViewAttributes() ?>><?php echo $pedidos->id->ViewValue ?></div>
</td>
		<td<?php echo $pedidos->Fecha->CellAttributes() ?>>
<div<?php echo $pedidos->Fecha->ViewAttributes() ?>><?php echo $pedidos->Fecha->ViewValue ?></div>
</td>
		<td<?php echo $pedidos->estado->CellAttributes() ?>>
<div<?php echo $pedidos->estado->ViewAttributes() ?>><?php echo $pedidos->estado->ViewValue ?></div>
</td>
		<td<?php echo $pedidos->ped_tarjeta->CellAttributes() ?>>
<div<?php echo $pedidos->ped_tarjeta->ViewAttributes() ?>><?php echo $pedidos->ped_tarjeta->ViewValue ?></div>
</td>
		<td<?php echo $pedidos->total->CellAttributes() ?>>
<div<?php echo $pedidos->total->ViewAttributes() ?>><?php echo $pedidos->total->ViewValue ?></div>
</td>
		<td<?php echo $pedidos->nombre->CellAttributes() ?>>
<div<?php echo $pedidos->nombre->ViewAttributes() ?>><?php echo $pedidos->nombre->ViewValue ?></div>
</td>
		<td<?php echo $pedidos->apellido->CellAttributes() ?>>
<div<?php echo $pedidos->apellido->ViewAttributes() ?>><?php echo $pedidos->apellido->ViewValue ?></div>
</td>
		<td<?php echo $pedidos->telefono->CellAttributes() ?>>
<div<?php echo $pedidos->telefono->ViewAttributes() ?>><?php echo $pedidos->telefono->ViewValue ?></div>
</td>
		<td<?php echo $pedidos->idVendedor->CellAttributes() ?>>
<div<?php echo $pedidos->idVendedor->ViewAttributes() ?>><?php echo $pedidos->idVendedor->ViewValue ?></div>
</td>
		<td<?php echo $pedidos->Evento->CellAttributes() ?>>
<div<?php echo $pedidos->Evento->ViewAttributes() ?>><?php echo $pedidos->Evento->ViewValue ?></div>
</td>
		<td<?php echo $pedidos->idPresupuesto->CellAttributes() ?>>
<div<?php echo $pedidos->idPresupuesto->ViewAttributes() ?>><?php echo $pedidos->idPresupuesto->ViewValue ?></div>
</td>
		<td<?php echo $pedidos->sena->CellAttributes() ?>>
<div<?php echo $pedidos->sena->ViewAttributes() ?>><?php echo $pedidos->sena->ViewValue ?></div>
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
	global $conn, $Security, $pedidos;
	$DeleteRows = TRUE;
	$sWrkFilter = $pedidos->CurrentFilter;

	// Set up filter (Sql Where Clause) and get Return Sql
	// Sql constructor in pedidos class, pedidosinfo.php

	$pedidos->CurrentFilter = $sWrkFilter;
	$sSql = $pedidos->SQL();
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
			$DeleteRows = $pedidos->Row_Deleting($row);
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
			$DeleteRows = $conn->Execute($pedidos->DeleteSQL($row)); // Delete
			$conn->raiseErrorFn = '';
			if ($DeleteRows === FALSE)
				break;
			if ($sKey <> "") $sKey .= ", ";
			$sKey .= $sThisKey;
		}
	} else {

		// Set up error message
		if ($pedidos->CancelMessage <> "") {
			$_SESSION[EW_SESSION_MESSAGE] = $pedidos->CancelMessage;
			$pedidos->CancelMessage = "";
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
			$pedidos->Row_Deleted($row);
		}	
	}
	return $DeleteRows;
}
?>
<?php

// Load recordset
function LoadRecordset($offset = -1, $rowcnt = -1) {
	global $conn, $pedidos;

	// Call Recordset Selecting event
	$pedidos->Recordset_Selecting($pedidos->CurrentFilter);

	// Load list page sql
	$sSql = $pedidos->SelectSQL();
	if ($offset > -1 && $rowcnt > -1) $sSql .= " LIMIT $offset, $rowcnt";

	// Load recordset
	$conn->raiseErrorFn = 'ew_ErrorFn';	
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';

	// Call Recordset Selected event
	$pedidos->Recordset_Selected($rs);
	return $rs;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $pedidos;
	$sFilter = $pedidos->SqlKeyFilter();
	if (!is_numeric($pedidos->id->CurrentValue)) {
		return FALSE; // Invalid key, exit
	}
	$sFilter = str_replace("@id@", ew_AdjustSql($pedidos->id->CurrentValue), $sFilter); // Replace key value

	// Call Row Selecting event
	$pedidos->Row_Selecting($sFilter);

	// Load sql based on filter
	$pedidos->CurrentFilter = $sFilter;
	$sSql = $pedidos->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$pedidos->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $pedidos;
	$pedidos->id->setDbValue($rs->fields('id'));
	$pedidos->Fecha->setDbValue($rs->fields('Fecha'));
	$pedidos->estado->setDbValue($rs->fields('estado'));
	$pedidos->ped_tarjeta->setDbValue($rs->fields('ped_tarjeta'));
	$pedidos->total->setDbValue($rs->fields('total'));
	$pedidos->Descuento->setDbValue($rs->fields('Descuento'));
	$pedidos->nombre->setDbValue($rs->fields('nombre'));
	$pedidos->apellido->setDbValue($rs->fields('apellido'));
	$pedidos->telefono->setDbValue($rs->fields('telefono'));
	$pedidos->idVendedor->setDbValue($rs->fields('idVendedor'));
	$pedidos->Evento->setDbValue($rs->fields('Evento'));
	$pedidos->descripcion->setDbValue($rs->fields('descripcion'));
	$pedidos->pedido->setDbValue($rs->fields('pedido'));
	$pedidos->extra->setDbValue($rs->fields('extra'));
	$pedidos->idPresupuesto->setDbValue($rs->fields('idPresupuesto'));
	$pedidos->sena->setDbValue($rs->fields('sena'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $pedidos;

	// Call Row Rendering event
	$pedidos->Row_Rendering();

	// Common render codes for all row types
	// id

	$pedidos->id->CellCssStyle = "";
	$pedidos->id->CellCssClass = "";

	// Fecha
	$pedidos->Fecha->CellCssStyle = "";
	$pedidos->Fecha->CellCssClass = "";

	// estado
	$pedidos->estado->CellCssStyle = "";
	$pedidos->estado->CellCssClass = "";

	// ped_tarjeta
	$pedidos->ped_tarjeta->CellCssStyle = "";
	$pedidos->ped_tarjeta->CellCssClass = "";

	// total
	$pedidos->total->CellCssStyle = "";
	$pedidos->total->CellCssClass = "";

	// nombre
	$pedidos->nombre->CellCssStyle = "";
	$pedidos->nombre->CellCssClass = "";

	// apellido
	$pedidos->apellido->CellCssStyle = "";
	$pedidos->apellido->CellCssClass = "";

	// telefono
	$pedidos->telefono->CellCssStyle = "";
	$pedidos->telefono->CellCssClass = "";

	// idVendedor
	$pedidos->idVendedor->CellCssStyle = "";
	$pedidos->idVendedor->CellCssClass = "";

	// Evento
	$pedidos->Evento->CellCssStyle = "";
	$pedidos->Evento->CellCssClass = "";

	// idPresupuesto
	$pedidos->idPresupuesto->CellCssStyle = "";
	$pedidos->idPresupuesto->CellCssClass = "";

	// sena
	$pedidos->sena->CellCssStyle = "";
	$pedidos->sena->CellCssClass = "";
	if ($pedidos->RowType == EW_ROWTYPE_VIEW) { // View row

		// id
		$pedidos->id->ViewValue = $pedidos->id->CurrentValue;
		$pedidos->id->CssStyle = "";
		$pedidos->id->CssClass = "";
		$pedidos->id->ViewCustomAttributes = "";

		// Fecha
		$pedidos->Fecha->ViewValue = $pedidos->Fecha->CurrentValue;
		$pedidos->Fecha->ViewValue = ew_FormatDateTime($pedidos->Fecha->ViewValue, 7);
		$pedidos->Fecha->CssStyle = "";
		$pedidos->Fecha->CssClass = "";
		$pedidos->Fecha->ViewCustomAttributes = "";

		// estado
		if (!is_null($pedidos->estado->CurrentValue)) {
			switch ($pedidos->estado->CurrentValue) {
				case "0":
					$pedidos->estado->ViewValue = "Pendiente";
					break;
				case "1":
					$pedidos->estado->ViewValue = "Pagado";
					break;
				case "2":
					$pedidos->estado->ViewValue = "Cancelado";
					break;
				default:
					$pedidos->estado->ViewValue = $pedidos->estado->CurrentValue;
			}
		} else {
			$pedidos->estado->ViewValue = NULL;
		}
		$pedidos->estado->CssStyle = "";
		$pedidos->estado->CssClass = "";
		$pedidos->estado->ViewCustomAttributes = "";

		// ped_tarjeta
		if (!is_null($pedidos->ped_tarjeta->CurrentValue)) {
			switch ($pedidos->ped_tarjeta->CurrentValue) {
				case "0":
					$pedidos->ped_tarjeta->ViewValue = "Contado";
					break;
				case "1":
					$pedidos->ped_tarjeta->ViewValue = "TARJETA";
					break;
				default:
					$pedidos->ped_tarjeta->ViewValue = $pedidos->ped_tarjeta->CurrentValue;
			}
		} else {
			$pedidos->ped_tarjeta->ViewValue = NULL;
		}
		$pedidos->ped_tarjeta->CssStyle = "";
		$pedidos->ped_tarjeta->CssClass = "";
		$pedidos->ped_tarjeta->ViewCustomAttributes = "";

		// total
		$pedidos->total->ViewValue = $pedidos->total->CurrentValue;
		$pedidos->total->ViewValue = ew_FormatCurrency($pedidos->total->ViewValue, 2, -2, -2, -2);
		$pedidos->total->CssStyle = "";
		$pedidos->total->CssClass = "";
		$pedidos->total->ViewCustomAttributes = "";

		// nombre
		$pedidos->nombre->ViewValue = $pedidos->nombre->CurrentValue;
		$pedidos->nombre->CssStyle = "";
		$pedidos->nombre->CssClass = "";
		$pedidos->nombre->ViewCustomAttributes = "";

		// apellido
		$pedidos->apellido->ViewValue = $pedidos->apellido->CurrentValue;
		$pedidos->apellido->CssStyle = "";
		$pedidos->apellido->CssClass = "";
		$pedidos->apellido->ViewCustomAttributes = "";

		// telefono
		$pedidos->telefono->ViewValue = $pedidos->telefono->CurrentValue;
		$pedidos->telefono->CssStyle = "";
		$pedidos->telefono->CssClass = "";
		$pedidos->telefono->ViewCustomAttributes = "";

		// idVendedor
		if (!empty($pedidos->idVendedor->CurrentValue)) {
			$sSqlWrk = "SELECT `Vendedor` FROM `vendedores` WHERE `id` = " . ew_AdjustSql($pedidos->idVendedor->CurrentValue) . "";
			$rswrk = $conn->Execute($sSqlWrk);
			if ($rswrk) {
				if (!$rswrk->EOF) {
					$pedidos->idVendedor->ViewValue = $rswrk->fields('Vendedor');
				}
				$rswrk->Close();
			} else {
				$pedidos->idVendedor->ViewValue = $pedidos->idVendedor->CurrentValue;
			}
		} else {
			$pedidos->idVendedor->ViewValue = NULL;
		}
		$pedidos->idVendedor->CssStyle = "";
		$pedidos->idVendedor->CssClass = "";
		$pedidos->idVendedor->ViewCustomAttributes = "";

		// Evento
		$pedidos->Evento->ViewValue = $pedidos->Evento->CurrentValue;
		$pedidos->Evento->CssStyle = "";
		$pedidos->Evento->CssClass = "";
		$pedidos->Evento->ViewCustomAttributes = "";

		// idPresupuesto
		$pedidos->idPresupuesto->ViewValue = $pedidos->idPresupuesto->CurrentValue;
		$pedidos->idPresupuesto->CssStyle = "";
		$pedidos->idPresupuesto->CssClass = "";
		$pedidos->idPresupuesto->ViewCustomAttributes = "";

		// sena
		$pedidos->sena->ViewValue = $pedidos->sena->CurrentValue;
		$pedidos->sena->CssStyle = "";
		$pedidos->sena->CssClass = "";
		$pedidos->sena->ViewCustomAttributes = "";

		// id
		$pedidos->id->HrefValue = "";

		// Fecha
		$pedidos->Fecha->HrefValue = "";

		// estado
		$pedidos->estado->HrefValue = "";

		// ped_tarjeta
		$pedidos->ped_tarjeta->HrefValue = "";

		// total
		$pedidos->total->HrefValue = "";

		// nombre
		$pedidos->nombre->HrefValue = "";

		// apellido
		$pedidos->apellido->HrefValue = "";

		// telefono
		$pedidos->telefono->HrefValue = "";

		// idVendedor
		$pedidos->idVendedor->HrefValue = "";

		// Evento
		$pedidos->Evento->HrefValue = "";

		// idPresupuesto
		$pedidos->idPresupuesto->HrefValue = "";

		// sena
		$pedidos->sena->HrefValue = "";
	} elseif ($pedidos->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($pedidos->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($pedidos->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$pedidos->Row_Rendered();
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
