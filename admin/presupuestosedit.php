<?php
define("EW_PAGE_ID", "edit", TRUE); // Page ID
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
if (!$Security->CanEdit()) {
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

// Load key from QueryString
if (@$_GET["id"] <> "") {
	$presupuestos->id->setQueryStringValue($_GET["id"]);
}

// Create form object
$objForm = new cFormObj();
if (@$_POST["a_edit"] <> "") {
	$presupuestos->CurrentAction = $_POST["a_edit"]; // Get action code
	LoadFormValues(); // Get form values
} else {
	$presupuestos->CurrentAction = "I"; // Default action is display
}

// Check if valid key
if ($presupuestos->id->CurrentValue == "") Page_Terminate($presupuestos->getReturnUrl()); // Invalid key, exit
switch ($presupuestos->CurrentAction) {
	case "I": // Get a record to display
		if (!LoadRow()) { // Load Record based on key
			$_SESSION[EW_SESSION_MESSAGE] = "No se encontraron registros"; // No record found
			Page_Terminate($presupuestos->getReturnUrl()); // Return to caller
		}
		break;
	Case "U": // Update
		$presupuestos->SendEmail = TRUE; // Send email on update success
		if (EditRow()) { // Update Record based on key
			$_SESSION[EW_SESSION_MESSAGE] = "Actualización satisfactoria"; // Update success
			Page_Terminate($presupuestos->getReturnUrl()); // Return to caller
		} else {
			RestoreFormValues(); // Restore form values if update failed
		}
}

// Render the record
$presupuestos->RowType = EW_ROWTYPE_EDIT; // Render as edit
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
		elm = fobj.elements["x" + infix + "_sena"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Por favor ingrese el campo requerido - Seña"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_sena"];
		if (elm && !ew_CheckNumber(elm.value)) {
			if (!ew_OnError(elm, "Numero de punto flotante incorrecto - Seña"))
				return false; 
		}
		elm = fobj.elements["x" + infix + "_presu_tarjeta"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Por favor ingrese el campo requerido - Pago"))
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
<p><span class="phpmaker">Editar TABLA: Presupuestos<br><br><a href="<?php echo $presupuestos->getReturnUrl() ?>">Volver atrás</a></span></p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form name="fpresupuestosedit" id="fpresupuestosedit" action="presupuestosedit.php" method="post" onSubmit="return ew_ValidateForm(this);">
<p>
<input type="hidden" name="a_edit" id="a_edit" value="U">
<table class="ewTable">
	<tr class="ewTableRow">
		<td class="ewTableHeader">Número</td>
		<td<?php echo $presupuestos->id->CellAttributes() ?>><span id="cb_x_id">
<div<?php echo $presupuestos->id->ViewAttributes() ?>><?php echo $presupuestos->id->EditValue ?></div>
<input type="hidden" name="x_id" id="x_id" value="<?php echo ew_HtmlEncode($presupuestos->id->CurrentValue) ?>">
</span></td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Nombre<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $presupuestos->nombre->CellAttributes() ?>><span id="cb_x_nombre">
<div<?php echo $presupuestos->nombre->ViewAttributes() ?>><?php echo $presupuestos->nombre->EditValue ?></div>
<input type="hidden" name="x_nombre" id="x_nombre" value="<?php echo ew_HtmlEncode($presupuestos->nombre->CurrentValue) ?>">
</span></td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">Apellido<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $presupuestos->apellido->CellAttributes() ?>><span id="cb_x_apellido">
<div<?php echo $presupuestos->apellido->ViewAttributes() ?>><?php echo $presupuestos->apellido->EditValue ?></div>
<input type="hidden" name="x_apellido" id="x_apellido" value="<?php echo ew_HtmlEncode($presupuestos->apellido->CurrentValue) ?>">
</span></td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Seña<span class='ewmsg'>&nbsp;*</span></td>
		
<?
$idped = ExecReturnFirstField("select id from pedidos where idPresupuesto = " . $presupuestos->id->CurrentValue);
$pedido_exists = $idped!=false;
?>				
		
		
		<td<?php echo $presupuestos->sena->CellAttributes() ?>><span id="cb_x_sena">
<input <?= $pedido_exists ? "readonly" : "" ?> type="text" name="x_sena" id="x_sena" title="" size="10" value="<?php echo $presupuestos->sena->EditValue ?>"<?php echo $presupuestos->sena->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">Estado</td>
		<td<?php echo $presupuestos->estado->CellAttributes() ?>><span id="cb_x_estado">
				
				
<select <?= $pedido_exists ? "disabled" : "" ?> id="x_estado" name="x_estado"<?php echo $presupuestos->estado->EditAttributes() ?>>
<!--option value="">Por favor seleccione</option-->
<?php
if (is_array($presupuestos->estado->EditValue)) {
	$arwrk = $presupuestos->estado->EditValue;
	$rowswrk = count($arwrk);
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($presupuestos->estado->CurrentValue) == strval($arwrk[$rowcntwrk][0])) ? " selected" : "";	
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
		<td<?php echo $presupuestos->presu_tarjeta->CellAttributes() ?>><span id="cb_x_presu_tarjeta">
<?php
$arwrk = $presupuestos->presu_tarjeta->EditValue;
if (is_array($arwrk)) {
	$rowswrk = count($arwrk);
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($presupuestos->presu_tarjeta->CurrentValue) == strval($arwrk[$rowcntwrk][0])) ? " checked" : "";
?>
<?php echo ew_RepeatColumnTable($rowswrk, $rowcntwrk, 1, 1) ?>
<input type="radio" name="x_presu_tarjeta" id="x_presu_tarjeta" title="" value="<?php echo ew_HtmlEncode($arwrk[$rowcntwrk][0]) ?>"<?php echo $selwrk ?><?php echo $presupuestos->presu_tarjeta->EditAttributes() ?>>
<?php echo $arwrk[$rowcntwrk][1] ?>
<?php echo ew_RepeatColumnTable($rowswrk, $rowcntwrk, 1, 2) ?>
<?php
	}
}
?>
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
	global $objForm, $presupuestos;
	$presupuestos->id->setFormValue($objForm->GetValue("x_id"));
	$presupuestos->nombre->setFormValue($objForm->GetValue("x_nombre"));
	$presupuestos->apellido->setFormValue($objForm->GetValue("x_apellido"));
	$presupuestos->sena->setFormValue($objForm->GetValue("x_sena"));
	$presupuestos->estado->setFormValue($objForm->GetValue("x_estado"));
	$presupuestos->presu_tarjeta->setFormValue($objForm->GetValue("x_presu_tarjeta"));
}

// Restore form values
function RestoreFormValues() {
	global $presupuestos;
	$presupuestos->id->CurrentValue = $presupuestos->id->FormValue;
	$presupuestos->nombre->CurrentValue = $presupuestos->nombre->FormValue;
	$presupuestos->apellido->CurrentValue = $presupuestos->apellido->FormValue;
	$presupuestos->sena->CurrentValue = $presupuestos->sena->FormValue;
	$presupuestos->estado->CurrentValue = $presupuestos->estado->FormValue;
	$presupuestos->presu_tarjeta->CurrentValue = $presupuestos->presu_tarjeta->FormValue;
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

	// sena
	$presupuestos->sena->CellCssStyle = "";
	$presupuestos->sena->CellCssClass = "";

	// estado
	$presupuestos->estado->CellCssStyle = "";
	$presupuestos->estado->CellCssClass = "";

	// presu_tarjeta
	$presupuestos->presu_tarjeta->CellCssStyle = "";
	$presupuestos->presu_tarjeta->CellCssClass = "";
	if ($presupuestos->RowType == EW_ROWTYPE_VIEW) { // View row
	} elseif ($presupuestos->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($presupuestos->RowType == EW_ROWTYPE_EDIT) { // Edit row

		// id
		$presupuestos->id->EditCustomAttributes = "";
		$presupuestos->id->EditValue = $presupuestos->id->CurrentValue;
		$presupuestos->id->CssStyle = "";
		$presupuestos->id->CssClass = "";
		$presupuestos->id->ViewCustomAttributes = "";

		// nombre
		$presupuestos->nombre->EditCustomAttributes = "";
		$presupuestos->nombre->EditValue = $presupuestos->nombre->CurrentValue;
		$presupuestos->nombre->CssStyle = "";
		$presupuestos->nombre->CssClass = "";
		$presupuestos->nombre->ViewCustomAttributes = "";

		// apellido
		$presupuestos->apellido->EditCustomAttributes = "";
		$presupuestos->apellido->EditValue = $presupuestos->apellido->CurrentValue;
		$presupuestos->apellido->CssStyle = "";
		$presupuestos->apellido->CssClass = "";
		$presupuestos->apellido->ViewCustomAttributes = "";

		// sena
		$presupuestos->sena->EditCustomAttributes = "";
		$presupuestos->sena->EditValue = $presupuestos->sena->CurrentValue;

		// estado
		$presupuestos->estado->EditCustomAttributes = "";
		$arwrk = array();
		$arwrk[] = array("0", "Pendiente");
		$arwrk[] = array("1", "Pagado");
		$arwrk[] = array("2", "Cancelado");
		array_unshift($arwrk, array("", "Por favor seleccione"));
		$presupuestos->estado->EditValue = $arwrk;

		// presu_tarjeta
		$presupuestos->presu_tarjeta->EditCustomAttributes = "";
		$arwrk = array();
		$arwrk[] = array("0", "Contado");
		$arwrk[] = array("1", "TARJETA");
		$presupuestos->presu_tarjeta->EditValue = $arwrk;

		// id
		$presupuestos->id->ViewValue = $presupuestos->id->CurrentValue;
		$presupuestos->id->CssStyle = "";
		$presupuestos->id->CssClass = "";
		$presupuestos->id->ViewCustomAttributes = "";
		$presupuestos->id->HrefValue = "";

		// nombre
		$presupuestos->nombre->ViewValue = $presupuestos->nombre->CurrentValue;
		$presupuestos->nombre->CssStyle = "";
		$presupuestos->nombre->CssClass = "";
		$presupuestos->nombre->ViewCustomAttributes = "";
		$presupuestos->nombre->HrefValue = "";

		// apellido
		$presupuestos->apellido->ViewValue = $presupuestos->apellido->CurrentValue;
		$presupuestos->apellido->CssStyle = "";
		$presupuestos->apellido->CssClass = "";
		$presupuestos->apellido->ViewCustomAttributes = "";
		$presupuestos->apellido->HrefValue = "";
	} elseif ($presupuestos->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$presupuestos->Row_Rendered();
}
?>
<?php

// Update record based on key values
function EditRow() {
	global $conn, $Security, $presupuestos;
	$sFilter = $presupuestos->SqlKeyFilter();
	if (!is_numeric($presupuestos->id->CurrentValue)) {
		return FALSE;
	}
	$sFilter = str_replace("@id@", ew_AdjustSql($presupuestos->id->CurrentValue), $sFilter); // Replace key value
	$presupuestos->CurrentFilter = $sFilter;
	$sSql = $presupuestos->SQL();
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


		$idped = ExecReturnFirstField("select id from pedidos where idPresupuesto = " . $presupuestos->id->CurrentValue);
		$pedido_exists = $idped!=false;
		
		if (!$pedido_exists) {
			// Field sena
			$presupuestos->sena->SetDbValueDef($presupuestos->sena->CurrentValue, 0);
			$rsnew['sena'] =& $presupuestos->sena->DbValue;

			// Field estado
			$presupuestos->estado->SetDbValueDef($presupuestos->estado->CurrentValue, NULL);
			$rsnew['estado'] =& $presupuestos->estado->DbValue;

		}
		
		// Field presu_tarjeta
		$presupuestos->presu_tarjeta->SetDbValueDef($presupuestos->presu_tarjeta->CurrentValue, 0);
		$rsnew['presu_tarjeta'] =& $presupuestos->presu_tarjeta->DbValue;


		// Call Row Updating event
		$bUpdateRow = $presupuestos->Row_Updating($rsold, $rsnew);
		if ($bUpdateRow) {
			$conn->raiseErrorFn = 'ew_ErrorFn';
			$EditRow = $conn->Execute($presupuestos->UpdateSQL($rsnew));
			$conn->raiseErrorFn = '';
		} else {
			if ($presupuestos->CancelMessage <> "") {
				$_SESSION[EW_SESSION_MESSAGE] = $presupuestos->CancelMessage;
				$presupuestos->CancelMessage = "";
			} else {
				$_SESSION[EW_SESSION_MESSAGE] = "Actualización cancelada";
			}
			$EditRow = FALSE;
		}
	}

	// Call Row Updated event
	if ($EditRow) {
		$presupuestos->Row_Updated($rsold, $rsnew);
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
