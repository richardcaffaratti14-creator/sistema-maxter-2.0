<?php
define("EW_PAGE_ID", "add", TRUE); // Page ID
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

// Load key values from QueryString
$bCopy = TRUE;
if (@$_GET["UserLevelID"] != "") {
  $usuarioslevels->UserLevelID->setQueryStringValue($_GET["UserLevelID"]);
} else {
  $bCopy = FALSE;
}

// Create form object
$objForm = new cFormObj();

// Process form if post back
if (@$_POST["a_add"] <> "") {
  $usuarioslevels->CurrentAction = $_POST["a_add"]; // Get form action
  LoadFormValues(); // Load form values

  // Load values for user privileges
  $x_ewAllowAdd = @$_POST["x_ewAllowAdd"];
  if ($x_ewAllowAdd == "") $x_ewAllowAdd = 0;
  $x_ewAllowEdit = @$_POST["x_ewAllowEdit"];
  if ($x_ewAllowEdit == "") $x_ewAllowEdit = 0;
  $x_ewAllowDelete = @$_POST["x_ewAllowDelete"];
  if ($x_ewAllowDelete == "") $x_ewAllowDelete = 0;
  $x_ewAllowList = @$_POST["x_ewAllowList"];
  if ($x_ewAllowList == "") $x_ewAllowList = 0;
  if (defined("EW_USER_LEVEL_COMPAT")) {
    $x_ewPriv = intval($x_ewAllowAdd) + intval($x_ewAllowEdit) +
      intval($x_ewAllowDelete) + intval($x_ewAllowList);
  } else {
    $x_ewAllowView = @$_POST["x_ewAllowView"];
    if ($x_ewAllowView == "") $x_ewAllowView = 0;
    $x_ewAllowSearch = @$_POST["x_ewAllowSearch"];
    if ($x_ewAllowSearch == "") $x_ewAllowSearch = 0;
    $x_ewPriv = intval($x_ewAllowAdd) + intval($x_ewAllowEdit) +
      intval($x_ewAllowDelete) + intval($x_ewAllowList) +
      intval($x_ewAllowView) + intval($x_ewAllowSearch);
  }
} else { // Not post back
  if ($bCopy) {
    $usuarioslevels->CurrentAction = "C"; // Copy Record
  } else {
    $usuarioslevels->CurrentAction = "I"; // Display Blank Record
    LoadDefaultValues(); // Load default values
  }
}

// Perform action based on action code
switch ($usuarioslevels->CurrentAction) {
  case "I": // Blank record, no action required
		break;
  case "C": // Copy an existing record
   if (!LoadRow()) { // Load record based on key
      $_SESSION[EW_SESSION_MESSAGE] = "No se encontraron registros"; // No record found
      Page_Terminate($usuarioslevels->getReturnUrl()); // Clean up and return
    }
		break;
  case "A": // ' Add new record
		$usuarioslevels->SendEmail = TRUE; // Send email on add success
    if (AddRow()) { // Add successful
      $_SESSION[EW_SESSION_MESSAGE] = "Nuevo registro agregado satisfactoriamente"; // Set up success message
      Page_Terminate($usuarioslevels->KeyUrl($usuarioslevels->getReturnUrl())); // Clean up and return
    } else {
      RestoreFormValues(); // Add failed, restore form values
    }
}

// Render row based on row type
$usuarioslevels->RowType = EW_ROWTYPE_ADD;  // Render add type
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
		elm = fobj.elements["x" + infix + "_UserLevelName"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Por favor ingrese el campo requerido - Nivel de Usuario"))
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
<p><span class="phpmaker">Agregar TABLA: Niveles de Usuarios<br><br><a href="<?php echo $usuarioslevels->getReturnUrl() ?>">Volver atras</a></span></p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") { // Mesasge in Session, display
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
  $_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message in Session
}
?>
<form name="fusuarioslevelsadd" id="fusuarioslevelsadd" action="usuarioslevelsadd.php" method="post" onSubmit="return ew_ValidateForm(this);">
<p>
<input type="hidden" name="a_add" id="a_add" value="A">
<table class="ewTable">
  <tr class="ewTableRow">
    <td class="ewTableHeader">ID de Nivel de Usuario<span class='ewmsg'>&nbsp;*</span></td>
    <td<?php echo $usuarioslevels->UserLevelID->CellAttributes() ?>><span id="cb_x_UserLevelID">
<input type="text" name="x_UserLevelID" id="x_UserLevelID" title="" value="<?php echo $usuarioslevels->UserLevelID->EditValue ?>"<?php echo $usuarioslevels->UserLevelID->EditAttributes() ?>>
</span></td>
  </tr>
  <tr class="ewTableAltRow">
    <td class="ewTableHeader">Nivel de Usuario<span class='ewmsg'>&nbsp;*</span></td>
    <td<?php echo $usuarioslevels->UserLevelName->CellAttributes() ?>><span id="cb_x_UserLevelName">
<input type="text" name="x_UserLevelName" id="x_UserLevelName" title="" size="30" maxlength="50" value="<?php echo $usuarioslevels->UserLevelName->EditValue ?>"<?php echo $usuarioslevels->UserLevelName->EditAttributes() ?>>
</span></td>
  </tr>
  <!-- row for permission values -->
  <tr class="ewTableRow">
    <td class="ewTableHeader">Permiso</td>
    <td>
<input type="checkbox" name="x_ewAllowAdd" id="Add" value="<?php echo EW_ALLOW_ADD ?>">Agregar/Copiar
<input type="checkbox" name="x_ewAllowDelete" id="Delete" value="<?php echo EW_ALLOW_DELETE ?>">Borrar
<input type="checkbox" name="x_ewAllowEdit" id="Edit" value="<?php echo EW_ALLOW_EDIT ?>">Editar
<?php if (defined("EW_USER_LEVEL_COMPAT")) { ?>
<input type="checkbox" name="x_ewAllowList" id="List" value="<?php echo EW_ALLOW_LIST ?>">Lista/Buscar/Vista
<?php } else { ?>
<input type="checkbox" name="x_ewAllowList" id="List" value="<?php echo EW_ALLOW_LIST ?>">Lista
<input type="checkbox" name="x_ewAllowView" id="View" value="<?php echo EW_ALLOW_VIEW ?>">Vista
<input type="checkbox" name="x_ewAllowSearch" id="Search" value="<?php echo EW_ALLOW_SEARCH ?>">Buscar
<?php } ?>
</td>
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
	global $usuarioslevels;
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

		// UserLevelID
		$usuarioslevels->UserLevelID->EditCustomAttributes = "";
		$usuarioslevels->UserLevelID->EditValue = $usuarioslevels->UserLevelID->CurrentValue;

		// UserLevelName
		$usuarioslevels->UserLevelName->EditCustomAttributes = "";
		$usuarioslevels->UserLevelName->EditValue = ew_HtmlEncode($usuarioslevels->UserLevelName->CurrentValue);
	} elseif ($usuarioslevels->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($usuarioslevels->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$usuarioslevels->Row_Rendered();
}
?>
<?php

// Add record
function AddRow() {
	global $conn, $Security, $usuarioslevels;
	if (trim(strval($usuarioslevels->UserLevelID->CurrentValue)) == "") {
		$_SESSION[EW_SESSION_MESSAGE] = "Falta el ID del nivel de usuario";
	} elseif (trim($usuarioslevels->UserLevelName->CurrentValue) == "") {
		$_SESSION[EW_SESSION_MESSAGE] = "Falta el nombre del nivel de usuario";
	} elseif (!is_numeric($usuarioslevels->UserLevelID->CurrentValue)) {
		$_SESSION[EW_SESSION_MESSAGE] = "El id del nivel de usuario debe ser un numero entero";
	} elseif (intval($usuarioslevels->UserLevelID->CurrentValue) < -1) {
		$_SESSION[EW_SESSION_MESSAGE] = "Nivel de usuario definido debe ser mayor que 0";
	} elseif (intval($usuarioslevels->UserLevelID->CurrentValue) == 0 && strtolower(trim($usuarioslevels->UserLevelName->CurrentValue)) <> "default") {
		$_SESSION[EW_SESSION_MESSAGE] = "El nombre del nivel de usuario para el nivel 0 debe ser 'Defecto'";
	} elseif (intval($usuarioslevels->UserLevelID->CurrentValue) == -1 && strtolower(trim($usuarioslevels->UserLevelName->CurrentValue)) <> "administrator") {
		$_SESSION[EW_SESSION_MESSAGE] = "Nombre del nivel de usuario para el nivel -1 debe ser 'Administrador'";
	} elseif (intval($usuarioslevels->UserLevelID->CurrentValue) > 0 && (strtolower(trim($usuarioslevels->UserLevelName->CurrentValue)) == "administrator" || strtolower(trim($usuarioslevels->UserLevelName->CurrentValue)) == "default")) {
		$_SESSION[EW_SESSION_MESSAGE] = "Nombre del nivel de usuario definido no puede ser 'Administrador' o 'Defecto'";
	}
	if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
		return FALSE;
	}

	// Check for duplicate key
	$bCheckKey = TRUE;
	$sFilter = $usuarioslevels->SqlKeyFilter();
	if (trim(strval($usuarioslevels->UserLevelID->CurrentValue)) == "") {
		$bCheckKey = FALSE;
	} else {
		$sFilter = str_replace("@UserLevelID@", ew_AdjustSql($usuarioslevels->UserLevelID->CurrentValue), $sFilter); // Replace key value
	}
	if (!is_numeric($usuarioslevels->UserLevelID->CurrentValue)) {
		$bCheckKey = FALSE;
	}
	if ($bCheckKey) {
		$rsChk = $usuarioslevels->LoadRs($sFilter);
		if ($rsChk && !$rsChk->EOF) {
			$_SESSION[EW_SESSION_MESSAGE] = "Valor duplicado para la llave primaria";
			$rsChk->Close();
			return FALSE;
		}
	}
	$rsnew = array();

	// Field UserLevelID
	$usuarioslevels->UserLevelID->SetDbValueDef($usuarioslevels->UserLevelID->CurrentValue, 0);
	$rsnew['UserLevelID'] =& $usuarioslevels->UserLevelID->DbValue;

	// Field UserLevelName
	$usuarioslevels->UserLevelName->SetDbValueDef($usuarioslevels->UserLevelName->CurrentValue, "");
	$rsnew['UserLevelName'] =& $usuarioslevels->UserLevelName->DbValue;

	// Call Row Inserting event
	$bInsertRow = $usuarioslevels->Row_Inserting($rsnew);
	if ($bInsertRow) {
		$conn->raiseErrorFn = 'ew_ErrorFn';
		$AddRow = $conn->Execute($usuarioslevels->InsertSQL($rsnew));
		$conn->raiseErrorFn = '';
	} else {
		if ($usuarioslevels->CancelMessage <> "") {
			$_SESSION[EW_SESSION_MESSAGE] = $usuarioslevels->CancelMessage;
			$usuarioslevels->CancelMessage = "";
		} else {
			$_SESSION[EW_SESSION_MESSAGE] = "Insertar cancelado";
		}
		$AddRow = FALSE;
	}
	if ($AddRow) {

		// Call Row Inserted event
		$usuarioslevels->Row_Inserted($rsnew);
	}

	// Add User Level priv
	if ($GLOBALS["x_ewPriv"] > 0 && is_array($GLOBALS["EW_USER_LEVEL_TABLE_NAME"])) {
		for ($i = 0; $i < count($GLOBALS["EW_USER_LEVEL_TABLE_NAME"]); $i++) {
			$sSql = "INSERT INTO " . EW_USER_LEVEL_PRIV_TABLE . " (" .
				EW_USER_LEVEL_PRIV_TABLE_NAME_FIELD . ", " .
				EW_USER_LEVEL_PRIV_USER_LEVEL_ID_FIELD . ", " .
				EW_USER_LEVEL_PRIV_PRIV_FIELD . ") VALUES ('" .
				ew_AdjustSql($GLOBALS["EW_USER_LEVEL_TABLE_NAME"][$i]) .
				"', " . $usuarioslevels->UserLevelID->CurrentValue . ", " . $GLOBALS["x_ewPriv"] . ")";
			$conn->Execute($sSql);
		}
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
