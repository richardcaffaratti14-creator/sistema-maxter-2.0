<?php
define("EW_PAGE_ID", "edit", TRUE); // Page ID
define("EW_TABLE_NAME", 'vendedores', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "vendedoresinfo.php" ?>
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
$Security->LoadCurrentUserLevel('vendedores');
if (!$Security->IsLoggedIn()) {
	$Security->SaveLastUrl();
	Page_Terminate("login.php");
}
if (!$Security->CanEdit()) {
	$Security->SaveLastUrl();
	Page_Terminate("vendedoreslist.php");
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
$vendedores->Export = @$_GET["export"]; // Get export parameter
$sExport = $vendedores->Export; // Get export parameter, used in header
$sExportFile = $vendedores->TableVar; // Get export file, used in header
?>
<?php

// Load key from QueryString
if (@$_GET["id"] <> "") {
	$vendedores->id->setQueryStringValue($_GET["id"]);
}

// Create form object
$objForm = new cFormObj();
if (@$_POST["a_edit"] <> "") {
	$vendedores->CurrentAction = $_POST["a_edit"]; // Get action code
	LoadFormValues(); // Get form values
} else {
	$vendedores->CurrentAction = "I"; // Default action is display
}

// Check if valid key
if ($vendedores->id->CurrentValue == "") Page_Terminate($vendedores->getReturnUrl()); // Invalid key, exit
switch ($vendedores->CurrentAction) {
	case "I": // Get a record to display
		if (!LoadRow()) { // Load Record based on key
			$_SESSION[EW_SESSION_MESSAGE] = "No se encontraron registros"; // No record found
			Page_Terminate($vendedores->getReturnUrl()); // Return to caller
		}
		break;
	Case "U": // Update
		$vendedores->SendEmail = TRUE; // Send email on update success
		if (EditRow()) { // Update Record based on key
			$_SESSION[EW_SESSION_MESSAGE] = "Actualización satisfactoria"; // Update success
			Page_Terminate($vendedores->getReturnUrl()); // Return to caller
		} else {
			RestoreFormValues(); // Restore form values if update failed
		}
}

// Render the record
$vendedores->RowType = EW_ROWTYPE_EDIT; // Render as edit
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
		elm = fobj.elements["x" + infix + "_Activo"];
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
<p><span class="phpmaker">Editar TABLA: vendedores<br><br><a href="<?php echo $vendedores->getReturnUrl() ?>">Volver atrás</a></span></p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form name="fvendedoresedit" id="fvendedoresedit" action="vendedoresedit.php" method="post" onSubmit="return ew_ValidateForm(this);">
<p>
<input type="hidden" name="a_edit" id="a_edit" value="U">
<table class="ewTable">
	<input type="hidden" name="x_id" id="x_id" value="<?php echo ew_HtmlEncode($vendedores->id->CurrentValue) ?>">
	<tr class="ewTableRow">
		<td class="ewTableHeader">Vendedor</td>
		<td<?php echo $vendedores->Vendedor->CellAttributes() ?>><span id="cb_x_Vendedor">
<input type="text" name="x_Vendedor" id="x_Vendedor" title="" size="30" maxlength="50" value="<?php echo $vendedores->Vendedor->EditValue ?>"<?php echo $vendedores->Vendedor->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Clave</td>
		<td<?php echo $vendedores->Clave->CellAttributes() ?>><span id="cb_x_Clave">
<input type="password" name="x_Clave" id="x_Clave" title=""  value="<?php echo $vendedores->Clave->EditValue ?>" size="30" maxlength="50"<?php echo $vendedores->Clave->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">Activo<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $vendedores->Activo->CellAttributes() ?>><span id="cb_x_Activo">
<?php
$arwrk = $vendedores->Activo->EditValue;
if (is_array($arwrk)) {
	$rowswrk = count($arwrk);
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($vendedores->Activo->CurrentValue) == strval($arwrk[$rowcntwrk][0])) ? " checked" : "";
?>
<?php echo ew_RepeatColumnTable($rowswrk, $rowcntwrk, 5, 1) ?>
<input type="radio" name="x_Activo" id="x_Activo" title="" value="<?php echo ew_HtmlEncode($arwrk[$rowcntwrk][0]) ?>"<?php echo $selwrk ?><?php echo $vendedores->Activo->EditAttributes() ?>>
<?php echo $arwrk[$rowcntwrk][1] ?>
<?php echo ew_RepeatColumnTable($rowswrk, $rowcntwrk, 5, 2) ?>
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
	global $objForm, $vendedores;
	$vendedores->id->setFormValue($objForm->GetValue("x_id"));
	$vendedores->Vendedor->setFormValue($objForm->GetValue("x_Vendedor"));
	$vendedores->Clave->setFormValue($objForm->GetValue("x_Clave"));
	$vendedores->Activo->setFormValue($objForm->GetValue("x_Activo"));
}

// Restore form values
function RestoreFormValues() {
	global $vendedores;
	$vendedores->id->CurrentValue = $vendedores->id->FormValue;
	$vendedores->Vendedor->CurrentValue = $vendedores->Vendedor->FormValue;
	$vendedores->Clave->CurrentValue = $vendedores->Clave->FormValue;
	$vendedores->Activo->CurrentValue = $vendedores->Activo->FormValue;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $vendedores;
	$sFilter = $vendedores->SqlKeyFilter();
	if (!is_numeric($vendedores->id->CurrentValue)) {
		return FALSE; // Invalid key, exit
	}
	$sFilter = str_replace("@id@", ew_AdjustSql($vendedores->id->CurrentValue), $sFilter); // Replace key value

	// Call Row Selecting event
	$vendedores->Row_Selecting($sFilter);

	// Load sql based on filter
	$vendedores->CurrentFilter = $sFilter;
	$sSql = $vendedores->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$vendedores->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $vendedores;
	$vendedores->id->setDbValue($rs->fields('id'));
	$vendedores->Vendedor->setDbValue($rs->fields('Vendedor'));
	$vendedores->Clave->setDbValue($rs->fields('Clave'));
	$vendedores->Activo->setDbValue($rs->fields('Activo'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $vendedores;

	// Call Row Rendering event
	$vendedores->Row_Rendering();

	// Common render codes for all row types
	// Vendedor

	$vendedores->Vendedor->CellCssStyle = "";
	$vendedores->Vendedor->CellCssClass = "";

	// Clave
	$vendedores->Clave->CellCssStyle = "";
	$vendedores->Clave->CellCssClass = "";

	// Activo
	$vendedores->Activo->CellCssStyle = "";
	$vendedores->Activo->CellCssClass = "";
	if ($vendedores->RowType == EW_ROWTYPE_VIEW) { // View row
	} elseif ($vendedores->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($vendedores->RowType == EW_ROWTYPE_EDIT) { // Edit row

		// Vendedor
		$vendedores->Vendedor->EditCustomAttributes = "";
		$vendedores->Vendedor->EditValue = ew_HtmlEncode($vendedores->Vendedor->CurrentValue);

		// Clave
		$vendedores->Clave->EditCustomAttributes = "";
		$vendedores->Clave->EditValue = $vendedores->Clave->CurrentValue;

		// Activo
		$vendedores->Activo->EditCustomAttributes = "";
		$arwrk = array();
		$arwrk[] = array("1", "Activo");
		$arwrk[] = array("0", "Inactivo");
		$vendedores->Activo->EditValue = $arwrk;
	} elseif ($vendedores->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$vendedores->Row_Rendered();
}
?>
<?php

// Update record based on key values
function EditRow() {
	global $conn, $Security, $vendedores;
	$sFilter = $vendedores->SqlKeyFilter();
	if (!is_numeric($vendedores->id->CurrentValue)) {
		return FALSE;
	}
	$sFilter = str_replace("@id@", ew_AdjustSql($vendedores->id->CurrentValue), $sFilter); // Replace key value
	$vendedores->CurrentFilter = $sFilter;
	$sSql = $vendedores->SQL();
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

		// Field Vendedor
		$vendedores->Vendedor->SetDbValueDef($vendedores->Vendedor->CurrentValue, NULL);
		$rsnew['Vendedor'] =& $vendedores->Vendedor->DbValue;

		// Field Clave
		$vendedores->Clave->SetDbValueDef($vendedores->Clave->CurrentValue, NULL);
		$rsnew['Clave'] =& $vendedores->Clave->DbValue;

		// Field Activo
		$vendedores->Activo->SetDbValueDef($vendedores->Activo->CurrentValue, 0);
		$rsnew['Activo'] =& $vendedores->Activo->DbValue;

		// Call Row Updating event
		$bUpdateRow = $vendedores->Row_Updating($rsold, $rsnew);
		if ($bUpdateRow) {
			$conn->raiseErrorFn = 'ew_ErrorFn';
			$EditRow = $conn->Execute($vendedores->UpdateSQL($rsnew));
			$conn->raiseErrorFn = '';
		} else {
			if ($vendedores->CancelMessage <> "") {
				$_SESSION[EW_SESSION_MESSAGE] = $vendedores->CancelMessage;
				$vendedores->CancelMessage = "";
			} else {
				$_SESSION[EW_SESSION_MESSAGE] = "Actualización cancelada";
			}
			$EditRow = FALSE;
		}
	}

	// Call Row Updated event
	if ($EditRow) {
		$vendedores->Row_Updated($rsold, $rsnew);
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
