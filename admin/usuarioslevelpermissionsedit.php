<?php
define("EW_PAGE_ID", "edit", TRUE); // Page ID
define("EW_TABLE_NAME", 'usuarioslevelpermissions', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "usuarioslevelpermissionsinfo.php" ?>
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
$Security->LoadCurrentUserLevel('usuarioslevelpermissions');
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
$usuarioslevelpermissions->Export = @$_GET["export"]; // Get export parameter
$sExport = $usuarioslevelpermissions->Export; // Get export parameter, used in header
$sExportFile = $usuarioslevelpermissions->TableVar; // Get export file, used in header
?>
<?php

// Load key from QueryString
if (@$_GET["UserLevelID"] <> "") {
	$usuarioslevelpermissions->UserLevelID->setQueryStringValue($_GET["UserLevelID"]);
}
if (@$_GET["TableName"] <> "") {
	$usuarioslevelpermissions->TableName->setQueryStringValue($_GET["TableName"]);
}

// Create form object
$objForm = new cFormObj();
if (@$_POST["a_edit"] <> "") {
	$usuarioslevelpermissions->CurrentAction = $_POST["a_edit"]; // Get action code
	LoadFormValues(); // Get form values
} else {
	$usuarioslevelpermissions->CurrentAction = "I"; // Default action is display
}

// Check if valid key
if ($usuarioslevelpermissions->UserLevelID->CurrentValue == "") Page_Terminate($usuarioslevelpermissions->getReturnUrl()); // Invalid key, exit
if ($usuarioslevelpermissions->TableName->CurrentValue == "") Page_Terminate($usuarioslevelpermissions->getReturnUrl()); // Invalid key, exit
switch ($usuarioslevelpermissions->CurrentAction) {
	case "I": // Get a record to display
		if (!LoadRow()) { // Load Record based on key
			$_SESSION[EW_SESSION_MESSAGE] = "No se encontraron registros"; // No record found
			Page_Terminate($usuarioslevelpermissions->getReturnUrl()); // Return to caller
		}
		break;
	Case "U": // Update
		$usuarioslevelpermissions->SendEmail = TRUE; // Send email on update success
		if (EditRow()) { // Update Record based on key
			$_SESSION[EW_SESSION_MESSAGE] = "Actualizacion satisfactoria"; // Update success
			Page_Terminate($usuarioslevelpermissions->getReturnUrl()); // Return to caller
		} else {
			RestoreFormValues(); // Restore form values if update failed
		}
}

// Render the record
$usuarioslevelpermissions->RowType = EW_ROWTYPE_EDIT; // Render as edit
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
		elm = fobj.elements["x" + infix + "_UserLevelID"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Porfavor ingrese el campo requerido - User Level ID"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_UserLevelID"];
		if (elm && !ew_CheckInteger(elm.value)) {
			if (!ew_OnError(elm, "Numero entero incorrecto - User Level ID"))
				return false; 
		}
		elm = fobj.elements["x" + infix + "_TableName"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Porfavor ingrese el campo requerido - Table Name"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_Permission"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Porfavor ingrese el campo requerido - Permission"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_Permission"];
		if (elm && !ew_CheckInteger(elm.value)) {
			if (!ew_OnError(elm, "Numero entero incorrecto - Permission"))
				return false; 
		}
	}
	return true;
}

//-->
</script>
<script type="text/javascript">
<!--

// js for DHtml Editor
//-->

</script>
<script type="text/javascript">
<!--

// js for Popup Calendar
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
<p><span class="phpmaker">Editar TABLA: usuarioslevelpermissions<br><br><a href="<?php echo $usuarioslevelpermissions->getReturnUrl() ?>">Volver atras</a></span></p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form name="fusuarioslevelpermissionsedit" id="fusuarioslevelpermissionsedit" action="usuarioslevelpermissionsedit.php" method="post" onSubmit="return ew_ValidateForm(this);">
<p>
<input type="hidden" name="a_edit" id="a_edit" value="U">
<table class="ewTable">
	<tr class="ewTableRow">
		<td class="ewTableHeader">User Level ID<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $usuarioslevelpermissions->UserLevelID->CellAttributes() ?>><span id="cb_x_UserLevelID">
<div<?php echo $usuarioslevelpermissions->UserLevelID->ViewAttributes() ?>><?php echo $usuarioslevelpermissions->UserLevelID->EditValue ?></div>
<input type="hidden" name="x_UserLevelID" id="x_UserLevelID" value="<?php echo ew_HtmlEncode($usuarioslevelpermissions->UserLevelID->CurrentValue) ?>">
</span></td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Table Name<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $usuarioslevelpermissions->TableName->CellAttributes() ?>><span id="cb_x_TableName">
<div<?php echo $usuarioslevelpermissions->TableName->ViewAttributes() ?>><?php echo $usuarioslevelpermissions->TableName->EditValue ?></div>
<input type="hidden" name="x_TableName" id="x_TableName" value="<?php echo ew_HtmlEncode($usuarioslevelpermissions->TableName->CurrentValue) ?>">
</span></td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">Permission<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $usuarioslevelpermissions->Permission->CellAttributes() ?>><span id="cb_x_Permission">
<input type="text" name="x_Permission" id="x_Permission" title="" size="30" value="<?php echo $usuarioslevelpermissions->Permission->EditValue ?>"<?php echo $usuarioslevelpermissions->Permission->EditAttributes() ?>>
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
	global $objForm, $usuarioslevelpermissions;
	$usuarioslevelpermissions->UserLevelID->setFormValue($objForm->GetValue("x_UserLevelID"));
	$usuarioslevelpermissions->TableName->setFormValue($objForm->GetValue("x_TableName"));
	$usuarioslevelpermissions->Permission->setFormValue($objForm->GetValue("x_Permission"));
}

// Restore form values
function RestoreFormValues() {
	global $usuarioslevelpermissions;
	$usuarioslevelpermissions->UserLevelID->CurrentValue = $usuarioslevelpermissions->UserLevelID->FormValue;
	$usuarioslevelpermissions->TableName->CurrentValue = $usuarioslevelpermissions->TableName->FormValue;
	$usuarioslevelpermissions->Permission->CurrentValue = $usuarioslevelpermissions->Permission->FormValue;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $usuarioslevelpermissions;
	$sFilter = $usuarioslevelpermissions->SqlKeyFilter();
	if (!is_numeric($usuarioslevelpermissions->UserLevelID->CurrentValue)) {
		return FALSE; // Invalid key, exit
	}
	$sFilter = str_replace("@UserLevelID@", ew_AdjustSql($usuarioslevelpermissions->UserLevelID->CurrentValue), $sFilter); // Replace key value
	$sFilter = str_replace("@TableName@", ew_AdjustSql($usuarioslevelpermissions->TableName->CurrentValue), $sFilter); // Replace key value

	// Call Row Selecting event
	$usuarioslevelpermissions->Row_Selecting($sFilter);

	// Load sql based on filter
	$usuarioslevelpermissions->CurrentFilter = $sFilter;
	$sSql = $usuarioslevelpermissions->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$usuarioslevelpermissions->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $usuarioslevelpermissions;
	$usuarioslevelpermissions->UserLevelID->setDbValue($rs->fields('UserLevelID'));
	$usuarioslevelpermissions->TableName->setDbValue($rs->fields('TableName'));
	$usuarioslevelpermissions->Permission->setDbValue($rs->fields('Permission'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $usuarioslevelpermissions;

	// Call Row Rendering event
	$usuarioslevelpermissions->Row_Rendering();

	// Common render codes for all row types
	// UserLevelID

	$usuarioslevelpermissions->UserLevelID->CellCssStyle = "";
	$usuarioslevelpermissions->UserLevelID->CellCssClass = "";

	// TableName
	$usuarioslevelpermissions->TableName->CellCssStyle = "";
	$usuarioslevelpermissions->TableName->CellCssClass = "";

	// Permission
	$usuarioslevelpermissions->Permission->CellCssStyle = "";
	$usuarioslevelpermissions->Permission->CellCssClass = "";
	if ($usuarioslevelpermissions->RowType == EW_ROWTYPE_VIEW) { // View row
	} elseif ($usuarioslevelpermissions->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($usuarioslevelpermissions->RowType == EW_ROWTYPE_EDIT) { // Edit row

		// UserLevelID
		$usuarioslevelpermissions->UserLevelID->EditCustomAttributes = "";
		$usuarioslevelpermissions->UserLevelID->EditValue = $usuarioslevelpermissions->UserLevelID->CurrentValue;
		$usuarioslevelpermissions->UserLevelID->CssStyle = "";
		$usuarioslevelpermissions->UserLevelID->CssClass = "";
		$usuarioslevelpermissions->UserLevelID->ViewCustomAttributes = "";

		// TableName
		$usuarioslevelpermissions->TableName->EditCustomAttributes = "";
		$usuarioslevelpermissions->TableName->EditValue = $usuarioslevelpermissions->TableName->CurrentValue;
		$usuarioslevelpermissions->TableName->CssStyle = "";
		$usuarioslevelpermissions->TableName->CssClass = "";
		$usuarioslevelpermissions->TableName->ViewCustomAttributes = "";

		// Permission
		$usuarioslevelpermissions->Permission->EditCustomAttributes = "";
		$usuarioslevelpermissions->Permission->EditValue = $usuarioslevelpermissions->Permission->CurrentValue;
	} elseif ($usuarioslevelpermissions->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$usuarioslevelpermissions->Row_Rendered();
}
?>
<?php

// Update record based on key values
function EditRow() {
	global $conn, $Security, $usuarioslevelpermissions;
	$sFilter = $usuarioslevelpermissions->SqlKeyFilter();
	if (!is_numeric($usuarioslevelpermissions->UserLevelID->CurrentValue)) {
		return FALSE;
	}
	$sFilter = str_replace("@UserLevelID@", ew_AdjustSql($usuarioslevelpermissions->UserLevelID->CurrentValue), $sFilter); // Replace key value
	$sFilter = str_replace("@TableName@", ew_AdjustSql($usuarioslevelpermissions->TableName->CurrentValue), $sFilter); // Replace key value
	$usuarioslevelpermissions->CurrentFilter = $sFilter;
	$sSql = $usuarioslevelpermissions->SQL();
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
		// Field TableName
		// Field Permission

		$usuarioslevelpermissions->Permission->SetDbValueDef($usuarioslevelpermissions->Permission->CurrentValue, 0);
		$rsnew['Permission'] =& $usuarioslevelpermissions->Permission->DbValue;

		// Call Row Updating event
		$bUpdateRow = $usuarioslevelpermissions->Row_Updating($rsold, $rsnew);
		if ($bUpdateRow) {
			$conn->raiseErrorFn = 'ew_ErrorFn';
			$EditRow = $conn->Execute($usuarioslevelpermissions->UpdateSQL($rsnew));
			$conn->raiseErrorFn = '';
		} else {
			if ($usuarioslevelpermissions->CancelMessage <> "") {
				$_SESSION[EW_SESSION_MESSAGE] = $usuarioslevelpermissions->CancelMessage;
				$usuarioslevelpermissions->CancelMessage = "";
			} else {
				$_SESSION[EW_SESSION_MESSAGE] = "Actualizacion cancelada";
			}
			$EditRow = FALSE;
		}
	}

	// Call Row Updated event
	if ($EditRow) {
		$usuarioslevelpermissions->Row_Updated($rsold, $rsnew);
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
