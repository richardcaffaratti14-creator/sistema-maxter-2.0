<?php
define("EW_PAGE_ID", "add", TRUE); // Page ID
define("EW_TABLE_NAME", 'formato_imagen', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "formato_imageninfo.php" ?>
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
$Security->LoadCurrentUserLevel('formato_imagen');
if (!$Security->IsLoggedIn()) {
	$Security->SaveLastUrl();
	Page_Terminate("login.php");
}
if (!$Security->CanAdd()) {
	$Security->SaveLastUrl();
	Page_Terminate("formato_imagenlist.php");
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
$formato_imagen->Export = @$_GET["export"]; // Get export parameter
$sExport = $formato_imagen->Export; // Get export parameter, used in header
$sExportFile = $formato_imagen->TableVar; // Get export file, used in header
?>
<?php

// Load key values from QueryString
$bCopy = TRUE;
if (@$_GET["id"] != "") {
  $formato_imagen->id->setQueryStringValue($_GET["id"]);
} else {
  $bCopy = FALSE;
}

// Create form object
$objForm = new cFormObj();

// Process form if post back
if (@$_POST["a_add"] <> "") {
  $formato_imagen->CurrentAction = $_POST["a_add"]; // Get form action
  LoadFormValues(); // Load form values
} else { // Not post back
  if ($bCopy) {
    $formato_imagen->CurrentAction = "C"; // Copy Record
  } else {
    $formato_imagen->CurrentAction = "I"; // Display Blank Record
    LoadDefaultValues(); // Load default values
  }
}

// Perform action based on action code
switch ($formato_imagen->CurrentAction) {
  case "I": // Blank record, no action required
		break;
  case "C": // Copy an existing record
   if (!LoadRow()) { // Load record based on key
      $_SESSION[EW_SESSION_MESSAGE] = "No se encontraron registros"; // No record found
      Page_Terminate($formato_imagen->getReturnUrl()); // Clean up and return
    }
		break;
  case "A": // ' Add new record
		$formato_imagen->SendEmail = TRUE; // Send email on add success
    if (AddRow()) { // Add successful
      $_SESSION[EW_SESSION_MESSAGE] = "Nuevo registro agregado satisfactoriamente"; // Set up success message
      Page_Terminate($formato_imagen->KeyUrl($formato_imagen->getReturnUrl())); // Clean up and return
    } else {
      RestoreFormValues(); // Add failed, restore form values
    }
}

// Render row based on row type
$formato_imagen->RowType = EW_ROWTYPE_ADD;  // Render add type
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
		elm = fobj.elements["x" + infix + "_ancho"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Por favor ingrese el campo requerido - Ancho (cm)"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_ancho"];
		if (elm && !ew_CheckInteger(elm.value)) {
			if (!ew_OnError(elm, "Numero entero incorrecto - Ancho (cm)"))
				return false; 
		}
		elm = fobj.elements["x" + infix + "_alto"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Por favor ingrese el campo requerido - Alto (cm)"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_alto"];
		if (elm && !ew_CheckInteger(elm.value)) {
			if (!ew_OnError(elm, "Numero entero incorrecto - Alto (cm)"))
				return false; 
		}
		elm = fobj.elements["x" + infix + "_carpeta"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Por favor ingrese el campo requerido - Nombre carpeta"))
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
<p><span class="phpmaker">Agregar TABLA: Formatos de imágenes<br><br><a href="<?php echo $formato_imagen->getReturnUrl() ?>">Volver atrás</a></span></p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") { // Mesasge in Session, display
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
  $_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message in Session
}
?>
<form name="fformato_imagenadd" id="fformato_imagenadd" action="formato_imagenadd.php" method="post" onSubmit="return ew_ValidateForm(this);">
<p>
<input type="hidden" name="a_add" id="a_add" value="A">
<table class="ewTable">
  <tr class="ewTableRow">
    <td class="ewTableHeader">Formato<span class='ewmsg'>&nbsp;*</span></td>
    <td<?php echo $formato_imagen->nombre->CellAttributes() ?>><span id="cb_x_nombre">
<input type="text" name="x_nombre" id="x_nombre" title="" size="30" maxlength="255" value="<?php echo $formato_imagen->nombre->EditValue ?>"<?php echo $formato_imagen->nombre->EditAttributes() ?>>
</span></td>
  </tr>
  <tr class="ewTableAltRow">
    <td class="ewTableHeader">Precio<span class='ewmsg'>&nbsp;*</span></td>
    <td<?php echo $formato_imagen->precio->CellAttributes() ?>><span id="cb_x_precio">
<input type="text" name="x_precio" id="x_precio" title="" size="10" maxlength="10" value="<?php echo $formato_imagen->precio->EditValue ?>"<?php echo $formato_imagen->precio->EditAttributes() ?>>
</span></td>
  </tr>
  <tr class="ewTableRow">
    <td class="ewTableHeader">Ancho (cm)<span class='ewmsg'>&nbsp;*</span></td>
    <td<?php echo $formato_imagen->ancho->CellAttributes() ?>><span id="cb_x_ancho">
<input type="text" name="x_ancho" id="x_ancho" title="" size="5" maxlength="5" value="<?php echo $formato_imagen->ancho->EditValue ?>"<?php echo $formato_imagen->ancho->EditAttributes() ?>>
</span></td>
  </tr>
  <tr class="ewTableAltRow">
    <td class="ewTableHeader">Alto (cm)<span class='ewmsg'>&nbsp;*</span></td>
    <td<?php echo $formato_imagen->alto->CellAttributes() ?>><span id="cb_x_alto">
<input type="text" name="x_alto" id="x_alto" title="" size="5" maxlength="5" value="<?php echo $formato_imagen->alto->EditValue ?>"<?php echo $formato_imagen->alto->EditAttributes() ?>>
</span>
<br/><br/>
<span style="font-style:italic">Ingresar 0 en ancho y alto para indicar formato digital</span>
</td>
  </tr>
  <tr class="ewTableRow">
    <td class="ewTableHeader">Nombre carpeta<span class='ewmsg'>&nbsp;*</span></td>
    <td<?php echo $formato_imagen->carpeta->CellAttributes() ?>><span id="cb_x_carpeta">
<input type="text" name="x_carpeta" id="x_carpeta" title="" size="30" maxlength="30" value="<?php echo $formato_imagen->carpeta->EditValue ?>"<?php echo $formato_imagen->carpeta->EditAttributes() ?>>
</span></td>
  </tr>
  <tr class="ewTableAltRow">
    <td class="ewTableHeader">Orden<span class='ewmsg'>&nbsp;*</span></td>
    <td<?php echo $formato_imagen->orden->CellAttributes() ?>><span id="cb_x_orden">
<input type="text" name="x_orden" id="x_orden" title="" size="5" maxlength="5" value="<?php echo $formato_imagen->orden->EditValue ?>"<?php echo $formato_imagen->orden->EditAttributes() ?>>
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
	global $formato_imagen;
	$formato_imagen->orden->CurrentValue = getNextOrder(EW_TABLE_NAME);
}
?>
<?php

// Load form values
function LoadFormValues() {

	// Load from form
	global $objForm, $formato_imagen;
	$formato_imagen->nombre->setFormValue($objForm->GetValue("x_nombre"));
	$formato_imagen->precio->setFormValue($objForm->GetValue("x_precio"));
	$formato_imagen->ancho->setFormValue($objForm->GetValue("x_ancho"));
	$formato_imagen->alto->setFormValue($objForm->GetValue("x_alto"));
	$formato_imagen->carpeta->setFormValue($objForm->GetValue("x_carpeta"));
	$formato_imagen->orden->setFormValue($objForm->GetValue("x_orden"));
}

// Restore form values
function RestoreFormValues() {
	global $formato_imagen;
	$formato_imagen->nombre->CurrentValue = $formato_imagen->nombre->FormValue;
	$formato_imagen->precio->CurrentValue = $formato_imagen->precio->FormValue;
	$formato_imagen->ancho->CurrentValue = $formato_imagen->ancho->FormValue;
	$formato_imagen->alto->CurrentValue = $formato_imagen->alto->FormValue;
	$formato_imagen->carpeta->CurrentValue = $formato_imagen->carpeta->FormValue;
	$formato_imagen->orden->CurrentValue = $formato_imagen->orden->FormValue;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $formato_imagen;
	$sFilter = $formato_imagen->SqlKeyFilter();
	if (!is_numeric($formato_imagen->id->CurrentValue)) {
		return FALSE; // Invalid key, exit
	}
	$sFilter = str_replace("@id@", ew_AdjustSql($formato_imagen->id->CurrentValue), $sFilter); // Replace key value

	// Call Row Selecting event
	$formato_imagen->Row_Selecting($sFilter);

	// Load sql based on filter
	$formato_imagen->CurrentFilter = $sFilter;
	$sSql = $formato_imagen->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$formato_imagen->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $formato_imagen;
	$formato_imagen->id->setDbValue($rs->fields('id'));
	$formato_imagen->nombre->setDbValue($rs->fields('nombre'));
	$formato_imagen->precio->setDbValue($rs->fields('precio'));
	$formato_imagen->ancho->setDbValue($rs->fields('ancho'));
	$formato_imagen->alto->setDbValue($rs->fields('alto'));
	$formato_imagen->carpeta->setDbValue($rs->fields('carpeta'));
	$formato_imagen->orden->setDbValue($rs->fields('orden'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $formato_imagen;

	// Call Row Rendering event
	$formato_imagen->Row_Rendering();

	// Common render codes for all row types
	// nombre

	$formato_imagen->nombre->CellCssStyle = "";
	$formato_imagen->nombre->CellCssClass = "";

	// precio
	$formato_imagen->precio->CellCssStyle = "";
	$formato_imagen->precio->CellCssClass = "";

	// ancho
	$formato_imagen->ancho->CellCssStyle = "";
	$formato_imagen->ancho->CellCssClass = "";

	// alto
	$formato_imagen->alto->CellCssStyle = "";
	$formato_imagen->alto->CellCssClass = "";

	// carpeta
	$formato_imagen->carpeta->CellCssStyle = "";
	$formato_imagen->carpeta->CellCssClass = "";

	// orden
	$formato_imagen->orden->CellCssStyle = "";
	$formato_imagen->orden->CellCssClass = "";
	if ($formato_imagen->RowType == EW_ROWTYPE_VIEW) { // View row
	} elseif ($formato_imagen->RowType == EW_ROWTYPE_ADD) { // Add row

		// nombre
		$formato_imagen->nombre->EditCustomAttributes = "";
		$formato_imagen->nombre->EditValue = ew_HtmlEncode($formato_imagen->nombre->CurrentValue);

		// precio
		$formato_imagen->precio->EditCustomAttributes = "";
		$formato_imagen->precio->EditValue = $formato_imagen->precio->CurrentValue;

		// ancho
		$formato_imagen->ancho->EditCustomAttributes = "";
		$formato_imagen->ancho->EditValue = $formato_imagen->ancho->CurrentValue;

		// alto
		$formato_imagen->alto->EditCustomAttributes = "";
		$formato_imagen->alto->EditValue = $formato_imagen->alto->CurrentValue;

		// carpeta
		$formato_imagen->carpeta->EditCustomAttributes = "";
		$formato_imagen->carpeta->EditValue = ew_HtmlEncode($formato_imagen->carpeta->CurrentValue);

		// orden
		$formato_imagen->orden->EditCustomAttributes = "";
		$formato_imagen->orden->EditValue = $formato_imagen->orden->CurrentValue;
	} elseif ($formato_imagen->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($formato_imagen->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$formato_imagen->Row_Rendered();
}
?>
<?php

// Add record
function AddRow() {
	global $conn, $Security, $formato_imagen;

	// Check for duplicate key
	$bCheckKey = TRUE;
	$sFilter = $formato_imagen->SqlKeyFilter();
	if (trim(strval($formato_imagen->id->CurrentValue)) == "") {
		$bCheckKey = FALSE;
	} else {
		$sFilter = str_replace("@id@", ew_AdjustSql($formato_imagen->id->CurrentValue), $sFilter); // Replace key value
	}
	if (!is_numeric($formato_imagen->id->CurrentValue)) {
		$bCheckKey = FALSE;
	}
	if ($bCheckKey) {
		$rsChk = $formato_imagen->LoadRs($sFilter);
		if ($rsChk && !$rsChk->EOF) {
			$_SESSION[EW_SESSION_MESSAGE] = "Valor duplicado para la llave primaria";
			$rsChk->Close();
			return FALSE;
		}
	}
	$rsnew = array();

	// Field nombre
	$formato_imagen->nombre->SetDbValueDef($formato_imagen->nombre->CurrentValue, "");
	$rsnew['nombre'] =& $formato_imagen->nombre->DbValue;

	// Field precio
	$formato_imagen->precio->SetDbValueDef($formato_imagen->precio->CurrentValue, 0);
	$rsnew['precio'] =& $formato_imagen->precio->DbValue;

	// Field ancho
	$formato_imagen->ancho->SetDbValueDef($formato_imagen->ancho->CurrentValue, 0);
	$rsnew['ancho'] =& $formato_imagen->ancho->DbValue;

	// Field alto
	$formato_imagen->alto->SetDbValueDef($formato_imagen->alto->CurrentValue, 0);
	$rsnew['alto'] =& $formato_imagen->alto->DbValue;

	// Field carpeta
	$formato_imagen->carpeta->SetDbValueDef($formato_imagen->carpeta->CurrentValue, "");
	$rsnew['carpeta'] =& $formato_imagen->carpeta->DbValue;

	// Field orden
	$formato_imagen->orden->SetDbValueDef($formato_imagen->orden->CurrentValue, 0);
	$rsnew['orden'] =& $formato_imagen->orden->DbValue;

	// Call Row Inserting event
	$bInsertRow = $formato_imagen->Row_Inserting($rsnew);
	if ($bInsertRow) {
		$conn->raiseErrorFn = 'ew_ErrorFn';
		$AddRow = $conn->Execute($formato_imagen->InsertSQL($rsnew));
		$conn->raiseErrorFn = '';
	} else {
		if ($formato_imagen->CancelMessage <> "") {
			$_SESSION[EW_SESSION_MESSAGE] = $formato_imagen->CancelMessage;
			$formato_imagen->CancelMessage = "";
		} else {
			$_SESSION[EW_SESSION_MESSAGE] = "Insertar cancelado";
		}
		$AddRow = FALSE;
	}
	if ($AddRow) {
		$formato_imagen->id->setDbValue($conn->Insert_ID());
		$rsnew['id'] =& $formato_imagen->id->DbValue;

		// Call Row Inserted event
		$formato_imagen->Row_Inserted($rsnew);
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
