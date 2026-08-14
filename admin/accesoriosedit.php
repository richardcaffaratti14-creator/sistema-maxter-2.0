<?php
define("EW_PAGE_ID", "edit", TRUE); // Page ID
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
if (!$Security->CanEdit()) {
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

// Load key from QueryString
if (@$_GET["id"] <> "") {
	$accesorios->id->setQueryStringValue($_GET["id"]);
}

// Create form object
$objForm = new cFormObj();
if (@$_POST["a_edit"] <> "") {
	$accesorios->CurrentAction = $_POST["a_edit"]; // Get action code
	LoadFormValues(); // Get form values
} else {
	$accesorios->CurrentAction = "I"; // Default action is display
}

// Check if valid key
if ($accesorios->id->CurrentValue == "") Page_Terminate($accesorios->getReturnUrl()); // Invalid key, exit
switch ($accesorios->CurrentAction) {
	case "I": // Get a record to display
		if (!LoadRow()) { // Load Record based on key
			$_SESSION[EW_SESSION_MESSAGE] = "No se encontraron registros"; // No record found
			Page_Terminate($accesorios->getReturnUrl()); // Return to caller
		}
		break;
	Case "U": // Update
		$accesorios->SendEmail = TRUE; // Send email on update success
		if (EditRow()) { // Update Record based on key
			$_SESSION[EW_SESSION_MESSAGE] = "Actualización satisfactoria"; // Update success
			Page_Terminate($accesorios->getReturnUrl()); // Return to caller
		} else {
			RestoreFormValues(); // Restore form values if update failed
		}
}

// Render the record
$accesorios->RowType = EW_ROWTYPE_EDIT; // Render as edit
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
		elm = fobj.elements["x" + infix + "_nombre"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Por favor ingrese el campo requerido - Nombre"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_precio"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Por favor ingrese el campo requerido - Precio"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_precio"];
		if (elm && !ew_CheckNumber(elm.value)) {
			if (!ew_OnError(elm, "Numero de punto flotante incorrecto - Precio"))
				return false; 
		}
		elm = fobj.elements["x" + infix + "_activo"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Por favor ingrese el campo requerido - Activo"))
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
<p><span class="phpmaker">Editar TABLA: Accesorios<br><br><a href="<?php echo $accesorios->getReturnUrl() ?>">Volver atrás</a></span></p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form name="faccesoriosedit" id="faccesoriosedit" action="accesoriosedit.php" method="post" onSubmit="return ew_ValidateForm(this);">
<p>
<input type="hidden" name="a_edit" id="a_edit" value="U">
<table class="ewTable">
	<input type="hidden" name="x_id" id="x_id" value="<?php echo ew_HtmlEncode($accesorios->id->CurrentValue) ?>">
	<tr class="ewTableRow">
		<td class="ewTableHeader">Nombre<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $accesorios->nombre->CellAttributes() ?>><span id="cb_x_nombre">
<input type="text" name="x_nombre" id="x_nombre" title="" size="30" maxlength="255" value="<?php echo $accesorios->nombre->EditValue ?>"<?php echo $accesorios->nombre->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Precio<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $accesorios->precio->CellAttributes() ?>><span id="cb_x_precio">
<input type="text" name="x_precio" id="x_precio" title="" size="10" maxlength="14" value="<?php echo $accesorios->precio->EditValue ?>"<?php echo $accesorios->precio->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">Activo<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $accesorios->activo->CellAttributes() ?>><span id="cb_x_activo">
<?php
$arwrk = $accesorios->activo->EditValue;
if (is_array($arwrk)) {
	$rowswrk = count($arwrk);
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($accesorios->activo->CurrentValue) == strval($arwrk[$rowcntwrk][0])) ? " checked" : "";
?>
<?php echo ew_RepeatColumnTable($rowswrk, $rowcntwrk, 1, 1) ?>
<input type="radio" name="x_activo" id="x_activo" title="" value="<?php echo ew_HtmlEncode($arwrk[$rowcntwrk][0]) ?>"<?php echo $selwrk ?><?php echo $accesorios->activo->EditAttributes() ?>>
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
	global $objForm, $accesorios;
	$accesorios->id->setFormValue($objForm->GetValue("x_id"));
	$accesorios->nombre->setFormValue($objForm->GetValue("x_nombre"));
	$accesorios->precio->setFormValue($objForm->GetValue("x_precio"));
	$accesorios->activo->setFormValue($objForm->GetValue("x_activo"));
}

// Restore form values
function RestoreFormValues() {
	global $accesorios;
	$accesorios->id->CurrentValue = $accesorios->id->FormValue;
	$accesorios->nombre->CurrentValue = $accesorios->nombre->FormValue;
	$accesorios->precio->CurrentValue = $accesorios->precio->FormValue;
	$accesorios->activo->CurrentValue = $accesorios->activo->FormValue;
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
	} elseif ($accesorios->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($accesorios->RowType == EW_ROWTYPE_EDIT) { // Edit row

		// nombre
		$accesorios->nombre->EditCustomAttributes = "";
		$accesorios->nombre->EditValue = ew_HtmlEncode($accesorios->nombre->CurrentValue);

		// precio
		$accesorios->precio->EditCustomAttributes = "";
		$accesorios->precio->EditValue = $accesorios->precio->CurrentValue;

		// activo
		$accesorios->activo->EditCustomAttributes = "";
		$arwrk = array();
		$arwrk[] = array("1", "Activo");
		$arwrk[] = array("0", "Inactivo");
		$accesorios->activo->EditValue = $arwrk;
	} elseif ($accesorios->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$accesorios->Row_Rendered();
}
?>
<?php

// Update record based on key values
function EditRow() {
	global $conn, $Security, $accesorios;
	$sFilter = $accesorios->SqlKeyFilter();
	if (!is_numeric($accesorios->id->CurrentValue)) {
		return FALSE;
	}
	$sFilter = str_replace("@id@", ew_AdjustSql($accesorios->id->CurrentValue), $sFilter); // Replace key value
	$accesorios->CurrentFilter = $sFilter;
	$sSql = $accesorios->SQL();
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

		// Field nombre
		$accesorios->nombre->SetDbValueDef($accesorios->nombre->CurrentValue, "");
		$rsnew['nombre'] =& $accesorios->nombre->DbValue;

		// Field precio
		$accesorios->precio->SetDbValueDef($accesorios->precio->CurrentValue, 0);
		$rsnew['precio'] =& $accesorios->precio->DbValue;

		// Field activo
		$accesorios->activo->SetDbValueDef($accesorios->activo->CurrentValue, 0);
		$rsnew['activo'] =& $accesorios->activo->DbValue;

		// Call Row Updating event
		$bUpdateRow = $accesorios->Row_Updating($rsold, $rsnew);
		if ($bUpdateRow) {
			$conn->raiseErrorFn = 'ew_ErrorFn';
			$EditRow = $conn->Execute($accesorios->UpdateSQL($rsnew));
			$conn->raiseErrorFn = '';
		} else {
			if ($accesorios->CancelMessage <> "") {
				$_SESSION[EW_SESSION_MESSAGE] = $accesorios->CancelMessage;
				$accesorios->CancelMessage = "";
			} else {
				$_SESSION[EW_SESSION_MESSAGE] = "Actualización cancelada";
			}
			$EditRow = FALSE;
		}
	}

	// Call Row Updated event
	if ($EditRow) {
		$accesorios->Row_Updated($rsold, $rsnew);
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
