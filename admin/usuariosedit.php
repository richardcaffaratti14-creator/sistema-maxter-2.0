<?php
define("EW_PAGE_ID", "edit", TRUE); // Page ID
define("EW_TABLE_NAME", 'usuarios', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "usuariosinfo.php" ?>
<?php include "userfn50.php" ?>
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
$Security->LoadCurrentUserLevel('usuarios');
if (!$Security->IsLoggedIn()) {
	$Security->SaveLastUrl();
	Page_Terminate("login.php");
}
if (!$Security->CanEdit()) {
	$Security->SaveLastUrl();
	Page_Terminate("usuarioslist.php");
}
if ($Security->IsLoggedIn() && $Security->CurrentUserID() == "") {
	$_SESSION[EW_SESSION_MESSAGE] = "Usted no tiene permisos para visualizar esta página";
	Page_Terminate("login.php");
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
$usuarios->Export = @$_GET["export"]; // Get export parameter
$sExport = $usuarios->Export; // Get export parameter, used in header
$sExportFile = $usuarios->TableVar; // Get export file, used in header
?>
<?php

// Load key from QueryString
if (@$_GET["idUsuario"] <> "") {
	$usuarios->idUsuario->setQueryStringValue($_GET["idUsuario"]);
}

// Create form object
$objForm = new cFormObj();
if (@$_POST["a_edit"] <> "") {
	$usuarios->CurrentAction = $_POST["a_edit"]; // Get action code
	LoadFormValues(); // Get form values
} else {
	$usuarios->CurrentAction = "I"; // Default action is display
}

// Check if valid key
if ($usuarios->idUsuario->CurrentValue == "") Page_Terminate($usuarios->getReturnUrl()); // Invalid key, exit
switch ($usuarios->CurrentAction) {
	case "I": // Get a record to display
		if (!LoadRow()) { // Load Record based on key
			$_SESSION[EW_SESSION_MESSAGE] = "No se encontraron registros"; // No record found
			Page_Terminate($usuarios->getReturnUrl()); // Return to caller
		}
		break;
	Case "U": // Update
		$usuarios->SendEmail = TRUE; // Send email on update success
		if (EditRow()) { // Update Record based on key
			$_SESSION[EW_SESSION_MESSAGE] = "Actualización satisfactoria"; // Update success
			Page_Terminate($usuarios->getReturnUrl()); // Return to caller
		} else {
			RestoreFormValues(); // Restore form values if update failed
		}
}

// Render the record
$usuarios->RowType = EW_ROWTYPE_EDIT; // Render as edit
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
		elm = fobj.elements["x" + infix + "_Clave"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Por favor ingrese el campo requerido - Clave"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_Nombre"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Por favor ingrese el campo requerido - Nombre"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_EMail"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Por favor ingrese el campo requerido - EMail"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_EMail"];
		if (elm && !ew_CheckEmail(elm.value)) {
			if (!ew_OnError(elm, "E-mail incorrecto - EMail"))
				return false; 
		}
		elm = fobj.elements["x" + infix + "_idLevel"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Por favor ingrese el campo requerido - Nivel"))
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
var ew_MultiPagePage = "Pagina"; // multi-page Page Text
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
<p><span class="phpmaker">Editar TABLA: Usuarios<br><br><a href="<?php echo $usuarios->getReturnUrl() ?>">Volver atras</a></span></p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form name="fusuariosedit" id="fusuariosedit" action="usuariosedit.php" method="post" onSubmit="return ew_ValidateForm(this);">
<p>
<input type="hidden" name="a_edit" id="a_edit" value="U">
<table class="ewTable">
	<input type="hidden" name="x_idUsuario" id="x_idUsuario" value="<?php echo ew_HtmlEncode($usuarios->idUsuario->CurrentValue) ?>">
	<tr class="ewTableRow">
		<td class="ewTableHeader">Clave<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $usuarios->Clave->CellAttributes() ?>><span id="cb_x_Clave">
<input type="password" name="x_Clave" id="x_Clave" title=""  value="<?php echo $usuarios->Clave->EditValue ?>" size="30" maxlength="50"<?php echo $usuarios->Clave->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Nombre<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $usuarios->Nombre->CellAttributes() ?>><span id="cb_x_Nombre">
<input type="text" name="x_Nombre" id="x_Nombre" title="" size="50" maxlength="100" value="<?php echo $usuarios->Nombre->EditValue ?>"<?php echo $usuarios->Nombre->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">EMail<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $usuarios->EMail->CellAttributes() ?>><span id="cb_x_EMail">
<input type="text" name="x_EMail" id="x_EMail" title="" size="50" maxlength="255" value="<?php echo $usuarios->EMail->EditValue ?>"<?php echo $usuarios->EMail->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Nivel<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $usuarios->idLevel->CellAttributes() ?>><span id="cb_x_idLevel">
<?php if (!$Security->IsAdmin() && $Security->IsLoggedIn()) { // Non system admin ?>
<div<?php echo $usuarios->idLevel->ViewAttributes() ?>><?php echo $usuarios->idLevel->EditValue ?></div>
<?php } else { ?>
<select id="x_idLevel" name="x_idLevel"<?php echo $usuarios->idLevel->EditAttributes() ?>>
<!--option value="">Por favor seleccione</option-->
<?php
if (is_array($usuarios->idLevel->EditValue)) {
	$arwrk = $usuarios->idLevel->EditValue;
	$rowswrk = count($arwrk);
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($usuarios->idLevel->CurrentValue) == strval($arwrk[$rowcntwrk][0])) ? " selected" : "";	
?>
<option value="<?php echo ew_HtmlEncode($arwrk[$rowcntwrk][0]) ?>"<?php echo $selwrk ?>>
<?php echo $arwrk[$rowcntwrk][1] ?>
</option>
<?php
			}
}
?>
</select>
<?php } ?>
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
	global $objForm, $usuarios;
	$usuarios->idUsuario->setFormValue($objForm->GetValue("x_idUsuario"));
	$usuarios->Clave->setFormValue($objForm->GetValue("x_Clave"));
	$usuarios->Nombre->setFormValue($objForm->GetValue("x_Nombre"));
	$usuarios->EMail->setFormValue($objForm->GetValue("x_EMail"));
	$usuarios->idLevel->setFormValue($objForm->GetValue("x_idLevel"));
}

// Restore form values
function RestoreFormValues() {
	global $usuarios;
	$usuarios->idUsuario->CurrentValue = $usuarios->idUsuario->FormValue;
	$usuarios->Clave->CurrentValue = $usuarios->Clave->FormValue;
	$usuarios->Nombre->CurrentValue = $usuarios->Nombre->FormValue;
	$usuarios->EMail->CurrentValue = $usuarios->EMail->FormValue;
	$usuarios->idLevel->CurrentValue = $usuarios->idLevel->FormValue;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $usuarios;
	$sFilter = $usuarios->SqlKeyFilter();
	if (!is_numeric($usuarios->idUsuario->CurrentValue)) {
		return FALSE; // Invalid key, exit
	}
	$sFilter = str_replace("@idUsuario@", ew_AdjustSql($usuarios->idUsuario->CurrentValue), $sFilter); // Replace key value
	if ($Security->CurrentUserID() <> "" && !$Security->IsAdmin()) { // Non system admin
		$sFilter = $usuarios->AddUserIDFilter($sFilter, $Security->CurrentUserID()); // Add User ID filter
	}

	// Call Row Selecting event
	$usuarios->Row_Selecting($sFilter);

	// Load sql based on filter
	$usuarios->CurrentFilter = $sFilter;
	$sSql = $usuarios->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$usuarios->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $usuarios;
	$usuarios->idUsuario->setDbValue($rs->fields('idUsuario'));
	$usuarios->usuario->setDbValue($rs->fields('usuario'));
	$usuarios->Clave->setDbValue($rs->fields('Clave'));
	$usuarios->Nombre->setDbValue($rs->fields('Nombre'));
	$usuarios->EMail->setDbValue($rs->fields('EMail'));
	$usuarios->idLevel->setDbValue($rs->fields('idLevel'));
	$usuarios->UltimoAcceso->setDbValue($rs->fields('UltimoAcceso'));
	$usuarios->IP->setDbValue($rs->fields('IP'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $usuarios;

	// Call Row Rendering event
	$usuarios->Row_Rendering();

	// Common render codes for all row types
	// Clave

	$usuarios->Clave->CellCssStyle = "";
	$usuarios->Clave->CellCssClass = "";

	// Nombre
	$usuarios->Nombre->CellCssStyle = "";
	$usuarios->Nombre->CellCssClass = "";

	// EMail
	$usuarios->EMail->CellCssStyle = "";
	$usuarios->EMail->CellCssClass = "";

	// idLevel
	$usuarios->idLevel->CellCssStyle = "";
	$usuarios->idLevel->CellCssClass = "";
	if ($usuarios->RowType == EW_ROWTYPE_VIEW) { // View row
	} elseif ($usuarios->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($usuarios->RowType == EW_ROWTYPE_EDIT) { // Edit row

		// Clave
		$usuarios->Clave->EditCustomAttributes = "";
		$usuarios->Clave->EditValue = $usuarios->Clave->CurrentValue;

		// Nombre
		$usuarios->Nombre->EditCustomAttributes = "";
		$usuarios->Nombre->EditValue = ew_HtmlEncode($usuarios->Nombre->CurrentValue);

		// EMail
		$usuarios->EMail->EditCustomAttributes = "";
		$usuarios->EMail->EditValue = ew_HtmlEncode($usuarios->EMail->CurrentValue);

		// idLevel
		$usuarios->idLevel->EditCustomAttributes = "";
		if (!$Security->CanAdmin()) { // System admin
			$usuarios->idLevel->EditValue = "********";
		} else {
		$sSqlWrk = "SELECT `UserLevelID`, `UserLevelName` FROM `usuarioslevels`";
		$rswrk = $conn->Execute($sSqlWrk);
		$arwrk = ($rswrk) ? $rswrk->GetRows() : array();
		if ($rswrk) $rswrk->Close();
		array_unshift($arwrk, array("", "Por favor seleccione"));
		$usuarios->idLevel->EditValue = $arwrk;
		}
	} elseif ($usuarios->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$usuarios->Row_Rendered();
}
?>
<?php

// Update record based on key values
function EditRow() {
	global $conn, $Security, $usuarios;
	$sFilter = $usuarios->SqlKeyFilter();
	if (!is_numeric($usuarios->idUsuario->CurrentValue)) {
		return FALSE;
	}
	$sFilter = str_replace("@idUsuario@", ew_AdjustSql($usuarios->idUsuario->CurrentValue), $sFilter); // Replace key value
	if ($usuarios->usuario->CurrentValue <> "") { // Check field with unique index
		$sFilterChk = "(`usuario` = '" . ew_AdjustSql($usuarios->usuario->CurrentValue) . "')";
		$sFilterChk .= " AND NOT (" . $sFilter . ")";
		$usuarios->CurrentFilter = $sFilterChk;
		$sSqlChk = $usuarios->SQL();
		$conn->raiseErrorFn = 'ew_ErrorFn';
		$rsChk = $conn->Execute($sSqlChk);
		$conn->raiseErrorFn = '';
		if ($rsChk === FALSE) {
			return FALSE;
		} elseif (!$rsChk->EOF) {
			$_SESSION[EW_SESSION_MESSAGE] = "Valor duplicado para el índice o la llave primaria -- `usuario`, Valor = " . $usuarios->usuario->CurrentValue;
			$rsChk->Close();
			return FALSE;
		}
		$rsChk->Close();
	}
	if ($Security->CurrentUserID() <> "" && !$Security->IsAdmin()) { // Non system admin
		$sFilter = $usuarios->AddUserIDFilter($sFilter, $Security->CurrentUserID()); // Add User ID filter
		$usuarios->CurrentFilter = $sFilter;
	}
	$usuarios->CurrentFilter = $sFilter;
	$sSql = $usuarios->SQL();
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

		// Field Clave
		$usuarios->Clave->SetDbValueDef($usuarios->Clave->CurrentValue, "");
		$rsnew['Clave'] =& $usuarios->Clave->DbValue;

		// Field Nombre
		$usuarios->Nombre->SetDbValueDef($usuarios->Nombre->CurrentValue, "");
		$rsnew['Nombre'] =& $usuarios->Nombre->DbValue;

		// Field EMail
		$usuarios->EMail->SetDbValueDef($usuarios->EMail->CurrentValue, "");
		$rsnew['EMail'] =& $usuarios->EMail->DbValue;

		// Field idLevel
		if ($Security->CanAdmin()) { // System admin
		$usuarios->idLevel->SetDbValueDef($usuarios->idLevel->CurrentValue, 0);
		$rsnew['idLevel'] =& $usuarios->idLevel->DbValue;
		}

		// Call Row Updating event
		$bUpdateRow = $usuarios->Row_Updating($rsold, $rsnew);
		if ($bUpdateRow) {
			$conn->raiseErrorFn = 'ew_ErrorFn';
			$EditRow = $conn->Execute($usuarios->UpdateSQL($rsnew));
			$conn->raiseErrorFn = '';
		} else {
			if ($usuarios->CancelMessage <> "") {
				$_SESSION[EW_SESSION_MESSAGE] = $usuarios->CancelMessage;
				$usuarios->CancelMessage = "";
			} else {
				$_SESSION[EW_SESSION_MESSAGE] = "Actualización cancelada";
			}
			$EditRow = FALSE;
		}
	}

	// Call Row Updated event
	if ($EditRow) {
		$usuarios->Row_Updated($rsold, $rsnew);
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
