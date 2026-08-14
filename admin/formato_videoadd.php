<?php
define("EW_PAGE_ID", "add", TRUE); // Page ID
define("EW_TABLE_NAME", 'formato_video', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "formato_videoinfo.php" ?>
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
$Security->LoadCurrentUserLevel('formato_video');
if (!$Security->IsLoggedIn()) {
	$Security->SaveLastUrl();
	Page_Terminate("login.php");
}
if (!$Security->CanAdd()) {
	$Security->SaveLastUrl();
	Page_Terminate("formato_videolist.php");
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
$formato_video->Export = @$_GET["export"]; // Get export parameter
$sExport = $formato_video->Export; // Get export parameter, used in header
$sExportFile = $formato_video->TableVar; // Get export file, used in header
?>
<?php

// Load key values from QueryString
$bCopy = TRUE;
if (@$_GET["id"] != "") {
  $formato_video->id->setQueryStringValue($_GET["id"]);
} else {
  $bCopy = FALSE;
}

// Create form object
$objForm = new cFormObj();

// Process form if post back
if (@$_POST["a_add"] <> "") {
  $formato_video->CurrentAction = $_POST["a_add"]; // Get form action
  LoadFormValues(); // Load form values
} else { // Not post back
  if ($bCopy) {
    $formato_video->CurrentAction = "C"; // Copy Record
  } else {
    $formato_video->CurrentAction = "I"; // Display Blank Record
    LoadDefaultValues(); // Load default values
  }
}

// Perform action based on action code
switch ($formato_video->CurrentAction) {
  case "I": // Blank record, no action required
		break;
  case "C": // Copy an existing record
   if (!LoadRow()) { // Load record based on key
      $_SESSION[EW_SESSION_MESSAGE] = "No se encontraron registros"; // No record found
      Page_Terminate($formato_video->getReturnUrl()); // Clean up and return
    }
		break;
  case "A": // ' Add new record
		$formato_video->SendEmail = TRUE; // Send email on add success
    if (AddRow()) { // Add successful
      $_SESSION[EW_SESSION_MESSAGE] = "Nuevo registro agregado satisfactoriamente"; // Set up success message
      Page_Terminate($formato_video->KeyUrl($formato_video->getReturnUrl())); // Clean up and return
    } else {
      RestoreFormValues(); // Add failed, restore form values
    }
}

// Render row based on row type
$formato_video->RowType = EW_ROWTYPE_ADD;  // Render add type
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
			if (!ew_OnError(elm, "Por favor ingrese el campo requerido - Formato"))
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
		elm = fobj.elements["x" + infix + "_carpeta"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Por favor ingrese el campo requerido - Carpeta"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_orden"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Por favor ingrese el campo requerido - Orden"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_orden"];
		if (elm && !ew_CheckInteger(elm.value)) {
			if (!ew_OnError(elm, "Numero entero incorrecto - Orden"))
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
<p><span class="phpmaker">Agregar TABLA: Formatos de videos<br><br><a href="<?php echo $formato_video->getReturnUrl() ?>">Volver atrás</a></span></p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") { // Mesasge in Session, display
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
  $_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message in Session
}
?>
<form name="fformato_videoadd" id="fformato_videoadd" action="formato_videoadd.php" method="post" onSubmit="return ew_ValidateForm(this);">
<p>
<input type="hidden" name="a_add" id="a_add" value="A">
<table class="ewTable">
  <tr class="ewTableRow">
    <td class="ewTableHeader">Formato<span class='ewmsg'>&nbsp;*</span></td>
    <td<?php echo $formato_video->nombre->CellAttributes() ?>><span id="cb_x_nombre">
<input type="text" name="x_nombre" id="x_nombre" title="" size="30" maxlength="255" value="<?php echo $formato_video->nombre->EditValue ?>"<?php echo $formato_video->nombre->EditAttributes() ?>>
</span></td>
  </tr>
  <tr class="ewTableAltRow">
    <td class="ewTableHeader">Precio<span class='ewmsg'>&nbsp;*</span></td>
    <td<?php echo $formato_video->precio->CellAttributes() ?>><span id="cb_x_precio">
<input type="text" name="x_precio" id="x_precio" title="" size="10" maxlength="10" value="<?php echo $formato_video->precio->EditValue ?>"<?php echo $formato_video->precio->EditAttributes() ?>>
</span></td>
  </tr>
  <tr class="ewTableRow">
    <td class="ewTableHeader">Carpeta<span class='ewmsg'>&nbsp;*</span></td>
    <td<?php echo $formato_video->carpeta->CellAttributes() ?>><span id="cb_x_carpeta">
<input type="text" name="x_carpeta" id="x_carpeta" title="" size="30" maxlength="30" value="<?php echo $formato_video->carpeta->EditValue ?>"<?php echo $formato_video->carpeta->EditAttributes() ?>>
</span></td>
  </tr>
  <tr class="ewTableAltRow">
    <td class="ewTableHeader">Orden<span class='ewmsg'>&nbsp;*</span></td>
    <td<?php echo $formato_video->orden->CellAttributes() ?>><span id="cb_x_orden">
<input type="text" name="x_orden" id="x_orden" title="" size="5" maxlength="5" value="<?php echo $formato_video->orden->EditValue ?>"<?php echo $formato_video->orden->EditAttributes() ?>>
</span></td>
  </tr>
  <tr class="ewTableRow">
    <td class="ewTableHeader">Sufijo</td>
    <td<?php echo $formato_video->Sufijo->CellAttributes() ?>><span id="cb_x_Sufijo">
<input type="text" name="x_Sufijo" id="x_Sufijo" title="" size="30" maxlength="20" value="<?php echo $formato_video->Sufijo->EditValue ?>"<?php echo $formato_video->Sufijo->EditAttributes() ?>>
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
	global $formato_video;
	$formato_video->orden->CurrentValue = getNextOrder(EW_TABLE_NAME);
}
?>
<?php

// Load form values
function LoadFormValues() {

	// Load from form
	global $objForm, $formato_video;
	$formato_video->nombre->setFormValue($objForm->GetValue("x_nombre"));
	$formato_video->precio->setFormValue($objForm->GetValue("x_precio"));
	$formato_video->carpeta->setFormValue($objForm->GetValue("x_carpeta"));
	$formato_video->orden->setFormValue($objForm->GetValue("x_orden"));
	$formato_video->Sufijo->setFormValue($objForm->GetValue("x_Sufijo"));
}

// Restore form values
function RestoreFormValues() {
	global $formato_video;
	$formato_video->nombre->CurrentValue = $formato_video->nombre->FormValue;
	$formato_video->precio->CurrentValue = $formato_video->precio->FormValue;
	$formato_video->carpeta->CurrentValue = $formato_video->carpeta->FormValue;
	$formato_video->orden->CurrentValue = $formato_video->orden->FormValue;
	$formato_video->Sufijo->CurrentValue = $formato_video->Sufijo->FormValue;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $formato_video;
	$sFilter = $formato_video->SqlKeyFilter();
	if (!is_numeric($formato_video->id->CurrentValue)) {
		return FALSE; // Invalid key, exit
	}
	$sFilter = str_replace("@id@", ew_AdjustSql($formato_video->id->CurrentValue), $sFilter); // Replace key value

	// Call Row Selecting event
	$formato_video->Row_Selecting($sFilter);

	// Load sql based on filter
	$formato_video->CurrentFilter = $sFilter;
	$sSql = $formato_video->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$formato_video->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $formato_video;
	$formato_video->id->setDbValue($rs->fields('id'));
	$formato_video->nombre->setDbValue($rs->fields('nombre'));
	$formato_video->precio->setDbValue($rs->fields('precio'));
	$formato_video->carpeta->setDbValue($rs->fields('carpeta'));
	$formato_video->orden->setDbValue($rs->fields('orden'));
	$formato_video->Sufijo->setDbValue($rs->fields('Sufijo'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $formato_video;

	// Call Row Rendering event
	$formato_video->Row_Rendering();

	// Common render codes for all row types
	// nombre

	$formato_video->nombre->CellCssStyle = "";
	$formato_video->nombre->CellCssClass = "";

	// precio
	$formato_video->precio->CellCssStyle = "";
	$formato_video->precio->CellCssClass = "";

	// carpeta
	$formato_video->carpeta->CellCssStyle = "";
	$formato_video->carpeta->CellCssClass = "";

	// orden
	$formato_video->orden->CellCssStyle = "";
	$formato_video->orden->CellCssClass = "";

	// Sufijo
	$formato_video->Sufijo->CellCssStyle = "";
	$formato_video->Sufijo->CellCssClass = "";
	if ($formato_video->RowType == EW_ROWTYPE_VIEW) { // View row
	} elseif ($formato_video->RowType == EW_ROWTYPE_ADD) { // Add row

		// nombre
		$formato_video->nombre->EditCustomAttributes = "";
		$formato_video->nombre->EditValue = ew_HtmlEncode($formato_video->nombre->CurrentValue);

		// precio
		$formato_video->precio->EditCustomAttributes = "";
		$formato_video->precio->EditValue = $formato_video->precio->CurrentValue;

		// carpeta
		$formato_video->carpeta->EditCustomAttributes = "";
		$formato_video->carpeta->EditValue = ew_HtmlEncode($formato_video->carpeta->CurrentValue);

		// orden
		$formato_video->orden->EditCustomAttributes = "";
		$formato_video->orden->EditValue = $formato_video->orden->CurrentValue;

		// Sufijo
		$formato_video->Sufijo->EditCustomAttributes = "";
		$formato_video->Sufijo->EditValue = ew_HtmlEncode($formato_video->Sufijo->CurrentValue);
	} elseif ($formato_video->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($formato_video->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$formato_video->Row_Rendered();
}
?>
<?php

// Add record
function AddRow() {
	global $conn, $Security, $formato_video;

	// Check for duplicate key
	$bCheckKey = TRUE;
	$sFilter = $formato_video->SqlKeyFilter();
	if (trim(strval($formato_video->id->CurrentValue)) == "") {
		$bCheckKey = FALSE;
	} else {
		$sFilter = str_replace("@id@", ew_AdjustSql($formato_video->id->CurrentValue), $sFilter); // Replace key value
	}
	if (!is_numeric($formato_video->id->CurrentValue)) {
		$bCheckKey = FALSE;
	}
	if ($bCheckKey) {
		$rsChk = $formato_video->LoadRs($sFilter);
		if ($rsChk && !$rsChk->EOF) {
			$_SESSION[EW_SESSION_MESSAGE] = "Valor duplicado para la llave primaria";
			$rsChk->Close();
			return FALSE;
		}
	}
	if ($formato_video->Sufijo->CurrentValue <> "") { // Check field with unique index
		$sFilter = "(`Sufijo` = '" . ew_AdjustSql($formato_video->Sufijo->CurrentValue) . "')";
		$rsChk = $formato_video->LoadRs($sFilter);
		if ($rsChk && !$rsChk->EOF) {
			$_SESSION[EW_SESSION_MESSAGE] = "Valor duplicado para el índice o la llave primaria -- `Sufijo`, Valor = " . $formato_video->Sufijo->CurrentValue;
			$rsChk->Close();
			return FALSE;
		}
	}
	$rsnew = array();

	// Field nombre
	$formato_video->nombre->SetDbValueDef($formato_video->nombre->CurrentValue, "");
	$rsnew['nombre'] =& $formato_video->nombre->DbValue;

	// Field precio
	$formato_video->precio->SetDbValueDef($formato_video->precio->CurrentValue, 0);
	$rsnew['precio'] =& $formato_video->precio->DbValue;

	// Field carpeta
	$formato_video->carpeta->SetDbValueDef($formato_video->carpeta->CurrentValue, "");
	$rsnew['carpeta'] =& $formato_video->carpeta->DbValue;

	// Field orden
	$formato_video->orden->SetDbValueDef($formato_video->orden->CurrentValue, 0);
	$rsnew['orden'] =& $formato_video->orden->DbValue;

	// Field Sufijo
	$formato_video->Sufijo->SetDbValueDef($formato_video->Sufijo->CurrentValue, NULL);
	$rsnew['Sufijo'] =& $formato_video->Sufijo->DbValue;

	// Call Row Inserting event
	$bInsertRow = $formato_video->Row_Inserting($rsnew);
	if ($bInsertRow) {
		$conn->raiseErrorFn = 'ew_ErrorFn';
		$AddRow = $conn->Execute($formato_video->InsertSQL($rsnew));
		$conn->raiseErrorFn = '';
	} else {
		if ($formato_video->CancelMessage <> "") {
			$_SESSION[EW_SESSION_MESSAGE] = $formato_video->CancelMessage;
			$formato_video->CancelMessage = "";
		} else {
			$_SESSION[EW_SESSION_MESSAGE] = "Insertar cancelado";
		}
		$AddRow = FALSE;
	}
	if ($AddRow) {
		$formato_video->id->setDbValue($conn->Insert_ID());
		$rsnew['id'] =& $formato_video->id->DbValue;

		// Call Row Inserted event
		$formato_video->Row_Inserted($rsnew);
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
