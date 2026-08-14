<?php
define("EW_PAGE_ID", "edit", TRUE); // Page ID
define("EW_TABLE_NAME", 'usuarioslevels', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "usuarioslevelsinfo.php" ?>
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
$Security->LoadCurrentUserLevel('usuarioslevels');
if (!$Security->CanAdmin()) {
	$Security->SaveLastUrl();
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
$usuarioslevels->Export = @$_GET["export"]; // Get export parameter
$sExport = $usuarioslevels->Export; // Get export parameter, used in header
$sExportFile = $usuarioslevels->TableVar; // Get export file, used in header
?>
<?php

// Load key from QueryString
if (@$_GET["UserLevelID"] <> "") {
	$usuarioslevels->UserLevelID->setQueryStringValue($_GET["UserLevelID"]);
}

// Create form object
$objForm = new cFormObj();
if (@$_POST["a_edit"] <> "") {
	$usuarioslevels->CurrentAction = $_POST["a_edit"]; // Get action code
	LoadFormValues(); // Get form values
} else {
	$usuarioslevels->CurrentAction = "I"; // Default action is display
}

// Check if valid key
if ($usuarioslevels->UserLevelID->CurrentValue == "") Page_Terminate($usuarioslevels->getReturnUrl()); // Invalid key, exit
switch ($usuarioslevels->CurrentAction) {
	case "I": // Get a record to display
		if (!LoadRow()) { // Load Record based on key
			$_SESSION[EW_SESSION_MESSAGE] = "No se encontraron registros"; // No record found
			Page_Terminate($usuarioslevels->getReturnUrl()); // Return to caller
		}
		break;
	Case "U": // Update
		$usuarioslevels->SendEmail = TRUE; // Send email on update success
		if (EditRow()) { // Update Record based on key
			$_SESSION[EW_SESSION_MESSAGE] = "Actualización satisfactoria"; // Update success
			Page_Terminate($usuarioslevels->getReturnUrl()); // Return to caller
		} else {
			RestoreFormValues(); // Restore form values if update failed
		}
}

// Render the record
$usuarioslevels->RowType = EW_ROWTYPE_EDIT; // Render as edit
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
		elm = fobj.elements["x" + infix + "_UserLevelName"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Por favor ingrese el campo requerido - Nivel de usuario"))
				return false;
		}
		elmId = fobj.elements["x" + infix + "_UserLevelID"];
		elmName = fobj.elements["x" + infix + "_UserLevelName"];
		if (elmId && elmName) {
			elmId.value = elmId.value.replace(/^\s+|\s+$/, '');
			elmName.value = elmName.value.replace(/^\s+|\s+$/, '');
			if (elmId && !ew_CheckInteger(elmId.value)) {
				if (!ew_OnError(elmId, "El id del nivel de usuario debe ser un numero entero"))
					return false;
			}
			var level = parseInt(elmId.value);
			if (level == 0) {
				if (elmName.value.toLowerCase() != "default") {
					if (!ew_OnError(elmName, "El nombre del nivel de usuario para el nivel 0 debe ser 'Defecto'"))
						return false;
				}
			} else if (level == -1) { 
				if (elmName.value.toLowerCase() != "administrator") {
					if (!ew_OnError(elmName, "Nombre del nivel de usuario para el nivel -1 debe ser 'Administrador'"))
						return false;
				}
			} else if (level < -1) {
				if (!ew_OnError(elmId, "Nivel de usuario definido debe ser mayor que 0"))
					return false;
			} else if (level > 0) { 
				if (elmName.value.toLowerCase() == "administrator" || elmName.value.toLowerCase() == "default") {
					if (!ew_OnError(elmName, "Nombre del nivel de usuario definido no puede ser 'Administrador' o 'Defecto'"))
						return false;
				}
			}
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
<p><span class="phpmaker">Editar TABLA: Niveles de Usuarios<br><br><a href="<?php echo $usuarioslevels->getReturnUrl() ?>">Volver atras</a></span></p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form name="fusuarioslevelsedit" id="fusuarioslevelsedit" action="usuarioslevelsedit.php" method="post" onSubmit="return ew_ValidateForm(this);">
<p>
<input type="hidden" name="a_edit" id="a_edit" value="U">
<table class="ewTable">
	<tr class="ewTableRow">
		<td class="ewTableHeader">ID de Nivel de Usuario<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $usuarioslevels->UserLevelID->CellAttributes() ?>><span id="cb_x_UserLevelID">
<div<?php echo $usuarioslevels->UserLevelID->ViewAttributes() ?>><?php echo $usuarioslevels->UserLevelID->EditValue ?></div>
<input type="hidden" name="x_UserLevelID" id="x_UserLevelID" value="<?php echo ew_HtmlEncode($usuarioslevels->UserLevelID->CurrentValue) ?>">
</span></td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Nivel de Usuario<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $usuarioslevels->UserLevelName->CellAttributes() ?>><span id="cb_x_UserLevelName">
<input type="text" name="x_UserLevelName" id="x_UserLevelName" title="" size="30" maxlength="50" value="<?php echo $usuarioslevels->UserLevelName->EditValue ?>"<?php echo $usuarioslevels->UserLevelName->EditAttributes() ?>>
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
	global $objForm, $usuarioslevels;
	$usuarioslevels->UserLevelID->setFormValue($objForm->GetValue("x_UserLevelID"));
	$usuarioslevels->UserLevelName->setFormValue($objForm->GetValue("x_UserLevelName"));
}

// Restore form values
function RestoreFormValues() {
	global $usuarioslevels;
	$usuarioslevels->UserLevelID->CurrentValue = $usuarioslevels->UserLevelID->FormValue;
	$usuarioslevels->UserLevelName->CurrentValue = $usuarioslevels->UserLevelName->FormValue;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $usuarioslevels;
	$sFilter = $usuarioslevels->SqlKeyFilter();
	if (!is_numeric($usuarioslevels->UserLevelID->CurrentValue)) {
		return FALSE; // Invalid key, exit
	}
	$sFilter = str_replace("@UserLevelID@", ew_AdjustSql($usuarioslevels->UserLevelID->CurrentValue), $sFilter); // Replace key value

	// Call Row Selecting event
	$usuarioslevels->Row_Selecting($sFilter);

	// Load sql based on filter
	$usuarioslevels->CurrentFilter = $sFilter;
	$sSql = $usuarioslevels->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$usuarioslevels->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $usuarioslevels;
	$usuarioslevels->UserLevelID->setDbValue($rs->fields('UserLevelID'));
	if (is_null($usuarioslevels->UserLevelID->CurrentValue)) {
		$usuarioslevels->UserLevelID->CurrentValue = 0;
	} else {
		$usuarioslevels->UserLevelID->CurrentValue = intval($usuarioslevels->UserLevelID->CurrentValue);
	}
	$usuarioslevels->UserLevelName->setDbValue($rs->fields('UserLevelName'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $usuarioslevels;

	// Call Row Rendering event
	$usuarioslevels->Row_Rendering();

	// Common render codes for all row types
	// UserLevelID

	$usuarioslevels->UserLevelID->CellCssStyle = "";
	$usuarioslevels->UserLevelID->CellCssClass = "";

	// UserLevelName
	$usuarioslevels->UserLevelName->CellCssStyle = "";
	$usuarioslevels->UserLevelName->CellCssClass = "";
	if ($usuarioslevels->RowType == EW_ROWTYPE_VIEW) { // View row
	} elseif ($usuarioslevels->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($usuarioslevels->RowType == EW_ROWTYPE_EDIT) { // Edit row

		// UserLevelID
		$usuarioslevels->UserLevelID->EditCustomAttributes = "";
		$usuarioslevels->UserLevelID->EditValue = $usuarioslevels->UserLevelID->CurrentValue;
		$usuarioslevels->UserLevelID->CssStyle = "";
		$usuarioslevels->UserLevelID->CssClass = "";
		$usuarioslevels->UserLevelID->ViewCustomAttributes = "";

		// UserLevelName
		$usuarioslevels->UserLevelName->EditCustomAttributes = "";
		$usuarioslevels->UserLevelName->EditValue = ew_HtmlEncode($usuarioslevels->UserLevelName->CurrentValue);
	} elseif ($usuarioslevels->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$usuarioslevels->Row_Rendered();
}
?>
<?php

// Update record based on key values
function EditRow() {
	global $conn, $Security, $usuarioslevels;
	$sFilter = $usuarioslevels->SqlKeyFilter();
	if (!is_numeric($usuarioslevels->UserLevelID->CurrentValue)) {
		return FALSE;
	}
	$sFilter = str_replace("@UserLevelID@", ew_AdjustSql($usuarioslevels->UserLevelID->CurrentValue), $sFilter); // Replace key value
	$usuarioslevels->CurrentFilter = $sFilter;
	$sSql = $usuarioslevels->SQL();
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

		// Field UserLevelID
		// Field UserLevelName

		$usuarioslevels->UserLevelName->SetDbValueDef($usuarioslevels->UserLevelName->CurrentValue, "");
		$rsnew['UserLevelName'] =& $usuarioslevels->UserLevelName->DbValue;

		// Call Row Updating event
		$bUpdateRow = $usuarioslevels->Row_Updating($rsold, $rsnew);
		if ($bUpdateRow) {
			$conn->raiseErrorFn = 'ew_ErrorFn';
			$EditRow = $conn->Execute($usuarioslevels->UpdateSQL($rsnew));
			$conn->raiseErrorFn = '';
		} else {
			if ($usuarioslevels->CancelMessage <> "") {
				$_SESSION[EW_SESSION_MESSAGE] = $usuarioslevels->CancelMessage;
				$usuarioslevels->CancelMessage = "";
			} else {
				$_SESSION[EW_SESSION_MESSAGE] = "Actualización cancelada";
			}
			$EditRow = FALSE;
		}
	}

	// Call Row Updated event
	if ($EditRow) {
		$usuarioslevels->Row_Updated($rsold, $rsnew);
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
