<?php
define("EW_PAGE_ID", "add", TRUE); // Page ID
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
if (!$Security->CanAdd()) {
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

// Load key values from QueryString
$bCopy = TRUE;
if (@$_GET["id"] != "") {
  $accesorios->id->setQueryStringValue($_GET["id"]);
} else {
  $bCopy = FALSE;
}

// Create form object
$objForm = new cFormObj();

// Process form if post back
if (@$_POST["a_add"] <> "") {
  $accesorios->CurrentAction = $_POST["a_add"]; // Get form action
  LoadFormValues(); // Load form values
} else { // Not post back
  if ($bCopy) {
    $accesorios->CurrentAction = "C"; // Copy Record
  } else {
    $accesorios->CurrentAction = "I"; // Display Blank Record
    LoadDefaultValues(); // Load default values
  }
}

// Perform action based on action code
switch ($accesorios->CurrentAction) {
  case "I": // Blank record, no action required
		break;
  case "C": // Copy an existing record
   if (!LoadRow()) { // Load record based on key
      $_SESSION[EW_SESSION_MESSAGE] = "No se encontraron registros"; // No record found
      Page_Terminate($accesorios->getReturnUrl()); // Clean up and return
    }
		break;
  case "A": // ' Add new record
		$accesorios->SendEmail = TRUE; // Send email on add success
    if (AddRow()) { // Add successful
      $_SESSION[EW_SESSION_MESSAGE] = "Nuevo registro agregado satisfactoriamente"; // Set up success message
      Page_Terminate($accesorios->KeyUrl($accesorios->getReturnUrl())); // Clean up and return
    } else {
      RestoreFormValues(); // Add failed, restore form values
    }
}

// Render row based on row type
$accesorios->RowType = EW_ROWTYPE_ADD;  // Render add type
RenderRow();
?>
<?php include "header.php" ?>
<script type="text/javascript">
<!--
var EW_PAGE_ID = "add"; // Page id

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
<p><span class="phpmaker">Agregar TABLA: Accesorios<br><br><a href="<?php echo $accesorios->getReturnUrl() ?>">Volver atrás</a></span></p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") { // Mesasge in Session, display
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
  $_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message in Session
}
?>
<form name="faccesoriosadd" id="faccesoriosadd" action="accesoriosadd.php" method="post" onSubmit="return ew_ValidateForm(this);">
<p>
<input type="hidden" name="a_add" id="a_add" value="A">
<table class="ewTable">
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
<input type="submit" name="btnAction" id="btnAction" value="  Agregar  ">
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

// Load default values
function LoadDefaultValues() {
	global $accesorios;
	$accesorios->activo->CurrentValue = 1;
}
?>
<?php

// Load form values
function LoadFormValues() {

	// Load from form
	global $objForm, $accesorios;
	$accesorios->nombre->setFormValue($objForm->GetValue("x_nombre"));
	$accesorios->precio->setFormValue($objForm->GetValue("x_precio"));
	$accesorios->activo->setFormValue($objForm->GetValue("x_activo"));
}

// Restore form values
function RestoreFormValues() {
	global $accesorios;
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
	} elseif ($accesorios->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($accesorios->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$accesorios->Row_Rendered();
}
?>
<?php

// Add record
function AddRow() {
	global $conn, $Security, $accesorios;

	// Check for duplicate key
	$bCheckKey = TRUE;
	$sFilter = $accesorios->SqlKeyFilter();
	if (trim(strval($accesorios->id->CurrentValue)) == "") {
		$bCheckKey = FALSE;
	} else {
		$sFilter = str_replace("@id@", ew_AdjustSql($accesorios->id->CurrentValue), $sFilter); // Replace key value
	}
	if (!is_numeric($accesorios->id->CurrentValue)) {
		$bCheckKey = FALSE;
	}
	if ($bCheckKey) {
		$rsChk = $accesorios->LoadRs($sFilter);
		if ($rsChk && !$rsChk->EOF) {
			$_SESSION[EW_SESSION_MESSAGE] = "Valor duplicado para la llave primaria";
			$rsChk->Close();
			return FALSE;
		}
	}
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

	// Call Row Inserting event
	$bInsertRow = $accesorios->Row_Inserting($rsnew);
	if ($bInsertRow) {
		$conn->raiseErrorFn = 'ew_ErrorFn';
		$AddRow = $conn->Execute($accesorios->InsertSQL($rsnew));
		$conn->raiseErrorFn = '';
	} else {
		if ($accesorios->CancelMessage <> "") {
			$_SESSION[EW_SESSION_MESSAGE] = $accesorios->CancelMessage;
			$accesorios->CancelMessage = "";
		} else {
			$_SESSION[EW_SESSION_MESSAGE] = "Insertar cancelado";
		}
		$AddRow = FALSE;
	}
	if ($AddRow) {
		$accesorios->id->setDbValue($conn->Insert_ID());
		$rsnew['id'] =& $accesorios->id->DbValue;

		// Call Row Inserted event
		$accesorios->Row_Inserted($rsnew);
	}
	return $AddRow;
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
