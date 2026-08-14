<?php
define("EW_PAGE_ID", "edit", TRUE); // Page ID
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
if (!$Security->CanEdit()) {
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

// Load key from QueryString
if (@$_GET["id"] <> "") {
	$formato_coreo->id->setQueryStringValue($_GET["id"]);
}

// Create form object
$objForm = new cFormObj();
if (@$_POST["a_edit"] <> "") {
	$formato_coreo->CurrentAction = $_POST["a_edit"]; // Get action code
	LoadFormValues(); // Get form values
} else {
	$formato_coreo->CurrentAction = "I"; // Default action is display
}

// Check if valid key
if ($formato_coreo->id->CurrentValue == "") Page_Terminate($formato_coreo->getReturnUrl()); // Invalid key, exit
switch ($formato_coreo->CurrentAction) {
	case "I": // Get a record to display
		if (!LoadRow()) { // Load Record based on key
			$_SESSION[EW_SESSION_MESSAGE] = "No se encontraron registros"; // No record found
			Page_Terminate($formato_coreo->getReturnUrl()); // Return to caller
		}
		break;
	Case "U": // Update
		$formato_coreo->SendEmail = TRUE; // Send email on update success
		if (EditRow()) { // Update Record based on key
			$_SESSION[EW_SESSION_MESSAGE] = "Actualización satisfactoria"; // Update success
			Page_Terminate($formato_coreo->getReturnUrl()); // Return to caller
		} else {
			RestoreFormValues(); // Restore form values if update failed
		}
}

// Render the record
$formato_coreo->RowType = EW_ROWTYPE_EDIT; // Render as edit
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
		elm = fobj.elements["x" + infix + "_Nombre"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Por favor ingrese el campo requerido - Nombre"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_Precio"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Por favor ingrese el campo requerido - Precio"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_Precio"];
		if (elm && !ew_CheckNumber(elm.value)) {
			if (!ew_OnError(elm, "Numero de punto flotante incorrecto - Precio"))
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
<p><span class="phpmaker">Editar TABLA: Formato de coreos<br><br><a href="<?php echo $formato_coreo->getReturnUrl() ?>">Volver atrás</a></span></p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form name="fformato_coreoedit" id="fformato_coreoedit" action="formato_coreoedit.php" method="post" onSubmit="return ew_ValidateForm(this);">
<p>
<input type="hidden" name="a_edit" id="a_edit" value="U">
<table class="ewTable">
	<input type="hidden" name="x_id" id="x_id" value="<?php echo ew_HtmlEncode($formato_coreo->id->CurrentValue) ?>">
	<tr class="ewTableRow">
		<td class="ewTableHeader">Nombre<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $formato_coreo->Nombre->CellAttributes() ?>><span id="cb_x_Nombre">
<input type="text" name="x_Nombre" id="x_Nombre" title="" size="30" maxlength="100" value="<?php echo $formato_coreo->Nombre->EditValue ?>"<?php echo $formato_coreo->Nombre->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Precio<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $formato_coreo->Precio->CellAttributes() ?>><span id="cb_x_Precio">
<input type="text" name="x_Precio" id="x_Precio" title="" size="6" maxlength="7" value="<?php echo $formato_coreo->Precio->EditValue ?>"<?php echo $formato_coreo->Precio->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">Sufijo</td>
		<td<?php echo $formato_coreo->Sufijo->CellAttributes() ?>><span id="cb_x_Sufijo">
<input type="text" name="x_Sufijo" id="x_Sufijo" title="" size="30" maxlength="20" value="<?php echo $formato_coreo->Sufijo->EditValue ?>"<?php echo $formato_coreo->Sufijo->EditAttributes() ?>>
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
	global $objForm, $formato_coreo;
	$formato_coreo->id->setFormValue($objForm->GetValue("x_id"));
	$formato_coreo->Nombre->setFormValue($objForm->GetValue("x_Nombre"));
	$formato_coreo->Precio->setFormValue($objForm->GetValue("x_Precio"));
	$formato_coreo->Sufijo->setFormValue($objForm->GetValue("x_Sufijo"));
}

// Restore form values
function RestoreFormValues() {
	global $formato_coreo;
	$formato_coreo->id->CurrentValue = $formato_coreo->id->FormValue;
	$formato_coreo->Nombre->CurrentValue = $formato_coreo->Nombre->FormValue;
	$formato_coreo->Precio->CurrentValue = $formato_coreo->Precio->FormValue;
	$formato_coreo->Sufijo->CurrentValue = $formato_coreo->Sufijo->FormValue;
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
	} elseif ($formato_coreo->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($formato_coreo->RowType == EW_ROWTYPE_EDIT) { // Edit row

		// Nombre
		$formato_coreo->Nombre->EditCustomAttributes = "";
		$formato_coreo->Nombre->EditValue = ew_HtmlEncode($formato_coreo->Nombre->CurrentValue);

		// Precio
		$formato_coreo->Precio->EditCustomAttributes = "";
		$formato_coreo->Precio->EditValue = $formato_coreo->Precio->CurrentValue;

		// Sufijo
		$formato_coreo->Sufijo->EditCustomAttributes = "";
		$formato_coreo->Sufijo->EditValue = ew_HtmlEncode($formato_coreo->Sufijo->CurrentValue);
	} elseif ($formato_coreo->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$formato_coreo->Row_Rendered();
}
?>
<?php

// Update record based on key values
function EditRow() {
	global $conn, $Security, $formato_coreo;
	$sFilter = $formato_coreo->SqlKeyFilter();
	if (!is_numeric($formato_coreo->id->CurrentValue)) {
		return FALSE;
	}
	$sFilter = str_replace("@id@", ew_AdjustSql($formato_coreo->id->CurrentValue), $sFilter); // Replace key value
	if ($formato_coreo->Sufijo->CurrentValue <> "") { // Check field with unique index
		$sFilterChk = "(`Sufijo` = '" . ew_AdjustSql($formato_coreo->Sufijo->CurrentValue) . "')";
		$sFilterChk .= " AND NOT (" . $sFilter . ")";
		$formato_coreo->CurrentFilter = $sFilterChk;
		$sSqlChk = $formato_coreo->SQL();
		$conn->raiseErrorFn = 'ew_ErrorFn';
		$rsChk = $conn->Execute($sSqlChk);
		$conn->raiseErrorFn = '';
		if ($rsChk === FALSE) {
			return FALSE;
		} elseif (!$rsChk->EOF) {
			$_SESSION[EW_SESSION_MESSAGE] = "Valor duplicado para el índice o la llave primaria -- `Sufijo`, Valor = " . $formato_coreo->Sufijo->CurrentValue;
			$rsChk->Close();
			return FALSE;
		}
		$rsChk->Close();
	}
	$formato_coreo->CurrentFilter = $sFilter;
	$sSql = $formato_coreo->SQL();
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

		// Field Nombre
		$formato_coreo->Nombre->SetDbValueDef($formato_coreo->Nombre->CurrentValue, "");
		$rsnew['Nombre'] =& $formato_coreo->Nombre->DbValue;

		// Field Precio
		$formato_coreo->Precio->SetDbValueDef($formato_coreo->Precio->CurrentValue, 0);
		$rsnew['Precio'] =& $formato_coreo->Precio->DbValue;

		// Field Sufijo
		$formato_coreo->Sufijo->SetDbValueDef($formato_coreo->Sufijo->CurrentValue, NULL);
		$rsnew['Sufijo'] =& $formato_coreo->Sufijo->DbValue;

		// Call Row Updating event
		$bUpdateRow = $formato_coreo->Row_Updating($rsold, $rsnew);
		if ($bUpdateRow) {
			$conn->raiseErrorFn = 'ew_ErrorFn';
			$EditRow = $conn->Execute($formato_coreo->UpdateSQL($rsnew));
			$conn->raiseErrorFn = '';
		} else {
			if ($formato_coreo->CancelMessage <> "") {
				$_SESSION[EW_SESSION_MESSAGE] = $formato_coreo->CancelMessage;
				$formato_coreo->CancelMessage = "";
			} else {
				$_SESSION[EW_SESSION_MESSAGE] = "Actualización cancelada";
			}
			$EditRow = FALSE;
		}
	}

	// Call Row Updated event
	if ($EditRow) {
		$formato_coreo->Row_Updated($rsold, $rsnew);
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
