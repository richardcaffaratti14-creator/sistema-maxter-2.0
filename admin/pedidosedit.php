<?php
define("EW_PAGE_ID", "edit", TRUE); // Page ID
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
if (!$Security->CanEdit()) {
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

// Load key from QueryString
if (@$_GET["id"] <> "") {
	$pedidos->id->setQueryStringValue($_GET["id"]);
}

// Create form object
$objForm = new cFormObj();
if (@$_POST["a_edit"] <> "") {
	$pedidos->CurrentAction = $_POST["a_edit"]; // Get action code
	LoadFormValues(); // Get form values
} else {
	$pedidos->CurrentAction = "I"; // Default action is display
}

// Check if valid key
if ($pedidos->id->CurrentValue == "") Page_Terminate($pedidos->getReturnUrl()); // Invalid key, exit
switch ($pedidos->CurrentAction) {
	case "I": // Get a record to display
		if (!LoadRow()) { // Load Record based on key
			$_SESSION[EW_SESSION_MESSAGE] = "No se encontraron registros"; // No record found
			Page_Terminate($pedidos->getReturnUrl()); // Return to caller
		}
		break;
	Case "U": // Update
		$pedidos->SendEmail = TRUE; // Send email on update success
		if (EditRow()) { // Update Record based on key
			$_SESSION[EW_SESSION_MESSAGE] = "Actualización satisfactoria"; // Update success
			Page_Terminate($pedidos->getReturnUrl()); // Return to caller
		} else {
			RestoreFormValues(); // Restore form values if update failed
		}
}

// Render the record
$pedidos->RowType = EW_ROWTYPE_EDIT; // Render as edit
RenderRow();
?>
<?php include "header.php" ?>
<script type="text/javascript">
<!--
var EW_PAGE_ID = "edit"; // Page id

//-->
</script>
<script type="text/javascript">
<!--

function ew_ValidateForm(fobj) {
	if (fobj.a_confirm && fobj.a_confirm.value == "F")
		return true;
	var i, elm, aelm, infix;
	var rowcnt = (fobj.key_count) ? Number(fobj.key_count.value) : 1;
	for (i=0; i<rowcnt; i++) {
		infix = (fobj.key_count) ? String(i+1) : "";
		elm = fobj.elements["x" + infix + "_estado"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Por favor ingrese el campo requerido - Estado"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_ped_tarjeta"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Por favor ingrese el campo requerido - Pago"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_Descuento"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Por favor ingrese el campo requerido - Descuento"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_Descuento"];
		if (elm && !ew_CheckNumber(elm.value)) {
			if (!ew_OnError(elm, "Numero de punto flotante incorrecto - Descuento"))
				return false; 
		}
		elm = fobj.elements["x" + infix + "_nombre"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Por favor ingrese el campo requerido - Nombre"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_apellido"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Por favor ingrese el campo requerido - Apellido"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_telefono"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Por favor ingrese el campo requerido - Teléfono"))
				return false;
		}
	}
	return true;
}

//-->
</script>
<script type="text/javascript">
<!--
var ew_DHTMLEditors = [];

//-->
</script>
<script type="text/javascript">
<!--
var ew_MultiPagePage = "página"; // multi-page Page Text
var ew_MultiPageOf = "de"; // multi-page Of Text
var ew_MultiPagePrev = "Anterior"; // multi-page Prev Text
var ew_MultiPageNext = "Proximo"; // multi-page Next Text

//-->
</script>
<script language="JavaScript" type="text/javascript">
<!--

// Write your client script here, no need to add script tags.
// To include another .js script, use:
// ew_ClientScriptInclude("my_javascript.js"); 
//-->

</script>
<p><span class="phpmaker">Editar TABLA: Pedidos<br><br><a href="<?php echo $pedidos->getReturnUrl() ?>">Volver atrás</a></span></p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form name="fpedidosedit" id="fpedidosedit" action="pedidosedit.php" method="post" onSubmit="return ew_ValidateForm(this);">
<p>
<input type="hidden" name="a_edit" id="a_edit" value="U">
<table class="ewTable">
	<tr class="ewTableRow">
		<td class="ewTableHeader">Número</td>
		<td<?php echo $pedidos->id->CellAttributes() ?>><span id="cb_x_id">
<div<?php echo $pedidos->id->ViewAttributes() ?>><?php echo $pedidos->id->EditValue ?></div>
<input type="hidden" name="x_id" id="x_id" value="<?php echo ew_HtmlEncode($pedidos->id->CurrentValue) ?>">
</span></td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Fecha<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $pedidos->Fecha->CellAttributes() ?>><span id="cb_x_Fecha">
<div<?php echo $pedidos->Fecha->ViewAttributes() ?>><?php echo $pedidos->Fecha->EditValue ?></div>
<input type="hidden" name="x_Fecha" id="x_Fecha" value="<?php echo ew_HtmlEncode($pedidos->Fecha->CurrentValue) ?>">
</span></td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">Estado<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $pedidos->estado->CellAttributes() ?>><span id="cb_x_estado">
<select id="x_estado" name="x_estado"<?php echo $pedidos->estado->EditAttributes() ?>>
<!--option value="">Por favor seleccione</option-->
<?php
if (is_array($pedidos->estado->EditValue)) {
	$arwrk = $pedidos->estado->EditValue;
	$rowswrk = count($arwrk);
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($pedidos->estado->CurrentValue) == strval($arwrk[$rowcntwrk][0])) ? " selected" : "";	
?>
<option value="<?php echo ew_HtmlEncode($arwrk[$rowcntwrk][0]) ?>"<?php echo $selwrk ?>>
<?php echo $arwrk[$rowcntwrk][1] ?>
</option>
<?php
			}
}
?>
</select>
</span></td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Pago<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $pedidos->ped_tarjeta->CellAttributes() ?>><span id="cb_x_ped_tarjeta">
<?php
$arwrk = $pedidos->ped_tarjeta->EditValue;
if (is_array($arwrk)) {
	$rowswrk = count($arwrk);
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($pedidos->ped_tarjeta->CurrentValue) == strval($arwrk[$rowcntwrk][0])) ? " checked" : "";
?>
<?php echo ew_RepeatColumnTable($rowswrk, $rowcntwrk, 1, 1) ?>
<input type="radio" name="x_ped_tarjeta" id="x_ped_tarjeta" title="" value="<?php echo ew_HtmlEncode($arwrk[$rowcntwrk][0]) ?>"<?php echo $selwrk ?><?php echo $pedidos->ped_tarjeta->EditAttributes() ?>>
<?php echo $arwrk[$rowcntwrk][1] ?>
<?php echo ew_RepeatColumnTable($rowswrk, $rowcntwrk, 1, 2) ?>
<?php
	}
}
?>
</span></td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">Total<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $pedidos->total->CellAttributes() ?>><span id="cb_x_total">
<div<?php echo $pedidos->total->ViewAttributes() ?>><?php echo $pedidos->total->EditValue ?></div>
<input type="hidden" name="x_total" id="x_total" value="<?php echo ew_HtmlEncode($pedidos->total->CurrentValue) ?>">
</span></td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Descuento<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $pedidos->Descuento->CellAttributes() ?>><span id="cb_x_Descuento">
<input type="text" name="x_Descuento" id="x_Descuento" title="" size="10" maxlength="10" value="<?php echo $pedidos->Descuento->EditValue ?>"<?php echo $pedidos->Descuento->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">Nombre<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $pedidos->nombre->CellAttributes() ?>><span id="cb_x_nombre">
<input type="text" name="x_nombre" id="x_nombre" title="" size="30" maxlength="255" value="<?php echo $pedidos->nombre->EditValue ?>"<?php echo $pedidos->nombre->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Apellido<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $pedidos->apellido->CellAttributes() ?>><span id="cb_x_apellido">
<input type="text" name="x_apellido" id="x_apellido" title="" size="30" maxlength="255" value="<?php echo $pedidos->apellido->EditValue ?>"<?php echo $pedidos->apellido->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">Teléfono<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $pedidos->telefono->CellAttributes() ?>><span id="cb_x_telefono">
<input type="text" name="x_telefono" id="x_telefono" title="" size="30" maxlength="80" value="<?php echo $pedidos->telefono->EditValue ?>"<?php echo $pedidos->telefono->EditAttributes() ?>>
</span></td>
	</tr>
</table>
<p>
<input type="submit" name="btnAction" id="btnAction" value="  Editar  ">
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

// Load form values
function LoadFormValues() {

	// Load from form
	global $objForm, $pedidos;
	$pedidos->id->setFormValue($objForm->GetValue("x_id"));
	$pedidos->Fecha->setFormValue($objForm->GetValue("x_Fecha"));
	$pedidos->Fecha->CurrentValue = ew_UnFormatDateTime($pedidos->Fecha->CurrentValue, 7);
	$pedidos->estado->setFormValue($objForm->GetValue("x_estado"));
	$pedidos->ped_tarjeta->setFormValue($objForm->GetValue("x_ped_tarjeta"));
	$pedidos->total->setFormValue($objForm->GetValue("x_total"));
	$pedidos->Descuento->setFormValue($objForm->GetValue("x_Descuento"));
	$pedidos->nombre->setFormValue($objForm->GetValue("x_nombre"));
	$pedidos->apellido->setFormValue($objForm->GetValue("x_apellido"));
	$pedidos->telefono->setFormValue($objForm->GetValue("x_telefono"));
	$pedidos->pedido->setFormValue($objForm->GetValue("x_pedido"));
	$pedidos->extra->setFormValue($objForm->GetValue("x_extra"));
	$pedidos->idPresupuesto->setFormValue($objForm->GetValue("x_idPresupuesto"));
	$pedidos->sena->setFormValue($objForm->GetValue("x_sena"));
}

// Restore form values
function RestoreFormValues() {
	global $pedidos;
	$pedidos->id->CurrentValue = $pedidos->id->FormValue;
	$pedidos->Fecha->CurrentValue = $pedidos->Fecha->FormValue;
	$pedidos->Fecha->CurrentValue = ew_UnFormatDateTime($pedidos->Fecha->CurrentValue, 7);
	$pedidos->estado->CurrentValue = $pedidos->estado->FormValue;
	$pedidos->ped_tarjeta->CurrentValue = $pedidos->ped_tarjeta->FormValue;
	$pedidos->total->CurrentValue = $pedidos->total->FormValue;
	$pedidos->Descuento->CurrentValue = $pedidos->Descuento->FormValue;
	$pedidos->nombre->CurrentValue = $pedidos->nombre->FormValue;
	$pedidos->apellido->CurrentValue = $pedidos->apellido->FormValue;
	$pedidos->telefono->CurrentValue = $pedidos->telefono->FormValue;
	$pedidos->pedido->CurrentValue = $pedidos->pedido->FormValue;
	$pedidos->extra->CurrentValue = $pedidos->extra->FormValue;
	$pedidos->idPresupuesto->CurrentValue = $pedidos->idPresupuesto->FormValue;
	$pedidos->sena->CurrentValue = $pedidos->sena->FormValue;
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

	// Descuento
	$pedidos->Descuento->CellCssStyle = "";
	$pedidos->Descuento->CellCssClass = "";

	// nombre
	$pedidos->nombre->CellCssStyle = "";
	$pedidos->nombre->CellCssClass = "";

	// apellido
	$pedidos->apellido->CellCssStyle = "";
	$pedidos->apellido->CellCssClass = "";

	// telefono
	$pedidos->telefono->CellCssStyle = "";
	$pedidos->telefono->CellCssClass = "";

	// pedido
	$pedidos->pedido->CellCssStyle = "";
	$pedidos->pedido->CellCssClass = "";

	// extra
	$pedidos->extra->CellCssStyle = "";
	$pedidos->extra->CellCssClass = "";

	// idPresupuesto
	$pedidos->idPresupuesto->CellCssStyle = "";
	$pedidos->idPresupuesto->CellCssClass = "";

	// sena
	$pedidos->sena->CellCssStyle = "";
	$pedidos->sena->CellCssClass = "";
	if ($pedidos->RowType == EW_ROWTYPE_VIEW) { // View row
	} elseif ($pedidos->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($pedidos->RowType == EW_ROWTYPE_EDIT) { // Edit row

		// id
		$pedidos->id->EditCustomAttributes = "";
		$pedidos->id->EditValue = $pedidos->id->CurrentValue;
		$pedidos->id->CssStyle = "";
		$pedidos->id->CssClass = "";
		$pedidos->id->ViewCustomAttributes = "";

		// Fecha
		$pedidos->Fecha->EditCustomAttributes = "";
		$pedidos->Fecha->EditValue = $pedidos->Fecha->CurrentValue;
		$pedidos->Fecha->EditValue = ew_FormatDateTime($pedidos->Fecha->EditValue, 7);
		$pedidos->Fecha->CssStyle = "";
		$pedidos->Fecha->CssClass = "";
		$pedidos->Fecha->ViewCustomAttributes = "";

		// estado
		$pedidos->estado->EditCustomAttributes = "";
		$arwrk = array();
		$arwrk[] = array("0", "Pendiente");
		$arwrk[] = array("1", "Pagado");
		$arwrk[] = array("2", "Cancelado");
		array_unshift($arwrk, array("", "Por favor seleccione"));
		$pedidos->estado->EditValue = $arwrk;

		// ped_tarjeta
		$pedidos->ped_tarjeta->EditCustomAttributes = "";
		$arwrk = array();
		$arwrk[] = array("0", "Contado");
		$arwrk[] = array("1", "TARJETA");
		$pedidos->ped_tarjeta->EditValue = $arwrk;

		// total
		$pedidos->total->EditCustomAttributes = "";
		$pedidos->total->EditValue = $pedidos->total->CurrentValue;
		$pedidos->total->EditValue = ew_FormatCurrency($pedidos->total->EditValue, 2, -2, -2, -2);
		$pedidos->total->CssStyle = "";
		$pedidos->total->CssClass = "";
		$pedidos->total->ViewCustomAttributes = "";

		// Descuento
		$pedidos->Descuento->EditCustomAttributes = "";
		$pedidos->Descuento->EditValue = $pedidos->Descuento->CurrentValue;

		// nombre
		$pedidos->nombre->EditCustomAttributes = "";
		$pedidos->nombre->EditValue = ew_HtmlEncode($pedidos->nombre->CurrentValue);

		// apellido
		$pedidos->apellido->EditCustomAttributes = "";
		$pedidos->apellido->EditValue = ew_HtmlEncode($pedidos->apellido->CurrentValue);

		// telefono
		$pedidos->telefono->EditCustomAttributes = "";
		$pedidos->telefono->EditValue = ew_HtmlEncode($pedidos->telefono->CurrentValue);

		// pedido
		$pedidos->pedido->EditCustomAttributes = "";
		$pedidos->pedido->EditValue = $pedidos->pedido->CurrentValue;

		// extra
		$pedidos->extra->EditCustomAttributes = "";
		$pedidos->extra->EditValue = $pedidos->extra->CurrentValue;

		// idPresupuesto
		$pedidos->idPresupuesto->EditCustomAttributes = "";
		$pedidos->idPresupuesto->EditValue = $pedidos->idPresupuesto->CurrentValue;

		// sena
		$pedidos->sena->EditCustomAttributes = "";
		$pedidos->sena->EditValue = $pedidos->sena->CurrentValue;

		// id
		$pedidos->id->ViewValue = $pedidos->id->CurrentValue;
		$pedidos->id->CssStyle = "";
		$pedidos->id->CssClass = "";
		$pedidos->id->ViewCustomAttributes = "";
		$pedidos->id->HrefValue = "";

		// Fecha
		$pedidos->Fecha->ViewValue = $pedidos->Fecha->CurrentValue;
		$pedidos->Fecha->ViewValue = ew_FormatDateTime($pedidos->Fecha->ViewValue, 7);
		$pedidos->Fecha->CssStyle = "";
		$pedidos->Fecha->CssClass = "";
		$pedidos->Fecha->ViewCustomAttributes = "";
		$pedidos->Fecha->HrefValue = "";

		// total
		$pedidos->total->ViewValue = $pedidos->total->CurrentValue;
		$pedidos->total->ViewValue = ew_FormatCurrency($pedidos->total->ViewValue, 2, -2, -2, -2);
		$pedidos->total->CssStyle = "";
		$pedidos->total->CssClass = "";
		$pedidos->total->ViewCustomAttributes = "";
		$pedidos->total->HrefValue = "";
	} elseif ($pedidos->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$pedidos->Row_Rendered();
}
?>
<?php

// Update record based on key values
function EditRow() {
	global $conn, $Security, $pedidos;
	$sFilter = $pedidos->SqlKeyFilter();
	if (!is_numeric($pedidos->id->CurrentValue)) {
		return FALSE;
	}
	$sFilter = str_replace("@id@", ew_AdjustSql($pedidos->id->CurrentValue), $sFilter); // Replace key value
	$pedidos->CurrentFilter = $sFilter;
	$sSql = $pedidos->SQL();
	$conn->raiseErrorFn = 'ew_ErrorFn';
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';
	if ($rs === FALSE)
		return FALSE;
	if ($rs->EOF) {
		$EditRow = FALSE; // Update Failed
	} else {

		// Save old values
		$rsold =& $rs->fields;
		$rsnew = array();

		// Field estado
		$pedidos->estado->SetDbValueDef($pedidos->estado->CurrentValue, 0);
		$rsnew['estado'] =& $pedidos->estado->DbValue;

		// Field ped_tarjeta
		$pedidos->ped_tarjeta->SetDbValueDef($pedidos->ped_tarjeta->CurrentValue, 0);
		$rsnew['ped_tarjeta'] =& $pedidos->ped_tarjeta->DbValue;

		// Field Descuento
		$pedidos->Descuento->SetDbValueDef($pedidos->Descuento->CurrentValue, 0);
		$rsnew['Descuento'] =& $pedidos->Descuento->DbValue;

		// Field nombre
		$pedidos->nombre->SetDbValueDef($pedidos->nombre->CurrentValue, "");
		$rsnew['nombre'] =& $pedidos->nombre->DbValue;

		// Field apellido
		$pedidos->apellido->SetDbValueDef($pedidos->apellido->CurrentValue, "");
		$rsnew['apellido'] =& $pedidos->apellido->DbValue;

		// Field telefono
		$pedidos->telefono->SetDbValueDef($pedidos->telefono->CurrentValue, "");
		$rsnew['telefono'] =& $pedidos->telefono->DbValue;

		// Call Row Updating event
		$bUpdateRow = $pedidos->Row_Updating($rsold, $rsnew);
		if ($bUpdateRow) {
			$conn->raiseErrorFn = 'ew_ErrorFn';
			$EditRow = $conn->Execute($pedidos->UpdateSQL($rsnew));
			$conn->raiseErrorFn = '';
		} else {
			if ($pedidos->CancelMessage <> "") {
				$_SESSION[EW_SESSION_MESSAGE] = $pedidos->CancelMessage;
				$pedidos->CancelMessage = "";
			} else {
				$_SESSION[EW_SESSION_MESSAGE] = "Actualización cancelada";
			}
			$EditRow = FALSE;
		}
	}

	// Call Row Updated event
	if ($EditRow) {
		$pedidos->Row_Updated($rsold, $rsnew);
	}
	$rs->Close();
	return $EditRow;
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
