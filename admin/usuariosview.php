<?php
define("EW_PAGE_ID", "view", TRUE); // Page ID
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
if (!$Security->CanView()) {
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
if (@$_GET["idUsuario"] <> "") {
	$usuarios->idUsuario->setQueryStringValue($_GET["idUsuario"]);
} else {
	Page_Terminate("usuarioslist.php"); // Return to list page
}

// Get action
if (@$_POST["a_view"] <> "") {
	$usuarios->CurrentAction = $_POST["a_view"];
} else {
	$usuarios->CurrentAction = "I"; // Display form
}
switch ($usuarios->CurrentAction) {
	case "I": // Get a record to display
		if (!LoadRow()) { // Load record based on key
			$_SESSION[EW_SESSION_MESSAGE] = "No se encontraron registros"; // Set no record message
			Page_Terminate("usuarioslist.php"); // Return to list
		}
}

// Set return url
$usuarios->setReturnUrl("usuariosview.php");

// Render row
$usuarios->RowType = EW_ROWTYPE_VIEW;
RenderRow();
?>
<?php include "header.php" ?>
<script type="text/javascript">
<!--
var EW_PAGE_ID = "view"; // Page id

//-->
</script>
<script language="JavaScript" type="text/javascript">
<!--

// Write your client script here, no need to add script tags.
// To include another .js script, use:
// ew_ClientScriptInclude("my_javascript.js"); 
//-->

</script>
<p><span class="phpmaker">Vista TABLA: Usuarios
<br><br>
<a href="usuarioslist.php">Volver a la lista</a>&nbsp;
<?php if ($Security->CanAdd()) { ?>
<?php if (ShowOptionLink()) { ?>
<a href="usuariosadd.php">Agregar</a>&nbsp;
<?php } ?>
<?php } ?>
<?php if ($Security->CanEdit()) { ?>
<?php if (ShowOptionLink()) { ?>
<a href="<?php echo $usuarios->EditUrl() ?>">Editar</a>&nbsp;
<?php } ?>
<?php } ?>
<?php if ($Security->CanDelete()) { ?>
<?php if (ShowOptionLink()) { ?>
<a href="<?php echo $usuarios->DeleteUrl() ?>">Borrar</a>&nbsp;
<?php } ?>
<?php } ?>
<?php if ($Security->AllowList('usuarios_ingresos')) { ?>
<?php if (ShowOptionLink()) { ?>
<a href="usuarios_ingresoslist.php?<?php echo EW_TABLE_SHOW_MASTER ?>=usuarios&usuario=<?php echo urlencode(strval($usuarios->usuario->CurrentValue)) ?>">Logins...</a>
&nbsp;
<?php } ?>
<?php } ?>
</span>
</p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<p>
<form>
<table class="ewTable">
	<tr class="ewTableRow">
		<td class="ewTableHeader">Nombre de usuario</td>
		<td<?php echo $usuarios->usuario->CellAttributes() ?>>
<div<?php echo $usuarios->usuario->ViewAttributes() ?>><?php echo $usuarios->usuario->ViewValue ?></div>
</td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Clave</td>
		<td<?php echo $usuarios->Clave->CellAttributes() ?>>
<div<?php echo $usuarios->Clave->ViewAttributes() ?>><?php echo $usuarios->Clave->ViewValue ?></div>
</td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">Nombre</td>
		<td<?php echo $usuarios->Nombre->CellAttributes() ?>>
<div<?php echo $usuarios->Nombre->ViewAttributes() ?>><?php echo $usuarios->Nombre->ViewValue ?></div>
</td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">EMail</td>
		<td<?php echo $usuarios->EMail->CellAttributes() ?>>
<div<?php echo $usuarios->EMail->ViewAttributes() ?>><?php echo $usuarios->EMail->ViewValue ?></div>
</td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">Nivel</td>
		<td<?php echo $usuarios->idLevel->CellAttributes() ?>>
<div<?php echo $usuarios->idLevel->ViewAttributes() ?>><?php echo $usuarios->idLevel->ViewValue ?></div>
</td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Último Acceso</td>
		<td<?php echo $usuarios->UltimoAcceso->CellAttributes() ?>>
<div<?php echo $usuarios->UltimoAcceso->ViewAttributes() ?>><?php echo $usuarios->UltimoAcceso->ViewValue ?></div>
</td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">IP</td>
		<td<?php echo $usuarios->IP->CellAttributes() ?>>
<div<?php echo $usuarios->IP->ViewAttributes() ?>><?php echo $usuarios->IP->ViewValue ?></div>
</td>
	</tr>
</table>
</form>
<p>
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
	// usuario

	$usuarios->usuario->CellCssStyle = "";
	$usuarios->usuario->CellCssClass = "";

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

	// UltimoAcceso
	$usuarios->UltimoAcceso->CellCssStyle = "";
	$usuarios->UltimoAcceso->CellCssClass = "";

	// IP
	$usuarios->IP->CellCssStyle = "";
	$usuarios->IP->CellCssClass = "";
	if ($usuarios->RowType == EW_ROWTYPE_VIEW) { // View row

		// usuario
		$usuarios->usuario->ViewValue = $usuarios->usuario->CurrentValue;
		$usuarios->usuario->CssStyle = "";
		$usuarios->usuario->CssClass = "";
		$usuarios->usuario->ViewCustomAttributes = "";

		// Clave
		$usuarios->Clave->ViewValue = "********";
		$usuarios->Clave->CssStyle = "";
		$usuarios->Clave->CssClass = "";
		$usuarios->Clave->ViewCustomAttributes = "";

		// Nombre
		$usuarios->Nombre->ViewValue = $usuarios->Nombre->CurrentValue;
		$usuarios->Nombre->CssStyle = "";
		$usuarios->Nombre->CssClass = "";
		$usuarios->Nombre->ViewCustomAttributes = "";

		// EMail
		$usuarios->EMail->ViewValue = $usuarios->EMail->CurrentValue;
		$usuarios->EMail->CssStyle = "";
		$usuarios->EMail->CssClass = "";
		$usuarios->EMail->ViewCustomAttributes = "";

		// idLevel
		if ($Security->CanAdmin()) { // System admin
		if (!empty($usuarios->idLevel->CurrentValue)) {
			$sSqlWrk = "SELECT `UserLevelName` FROM `usuarioslevels` WHERE `UserLevelID` = " . ew_AdjustSql($usuarios->idLevel->CurrentValue) . "";
			$rswrk = $conn->Execute($sSqlWrk);
			if ($rswrk) {
				if (!$rswrk->EOF) {
					$usuarios->idLevel->ViewValue = $rswrk->fields('UserLevelName');
				}
				$rswrk->Close();
			} else {
				$usuarios->idLevel->ViewValue = $usuarios->idLevel->CurrentValue;
			}
		} else {
			$usuarios->idLevel->ViewValue = NULL;
		}
		} else {
			$usuarios->idLevel->ViewValue = "********";
		}
		$usuarios->idLevel->CssStyle = "";
		$usuarios->idLevel->CssClass = "";
		$usuarios->idLevel->ViewCustomAttributes = "";

		// UltimoAcceso
		$usuarios->UltimoAcceso->ViewValue = $usuarios->UltimoAcceso->CurrentValue;
		$usuarios->UltimoAcceso->ViewValue = ew_FormatDateTime($usuarios->UltimoAcceso->ViewValue, 7);
		$usuarios->UltimoAcceso->CssStyle = "";
		$usuarios->UltimoAcceso->CssClass = "";
		$usuarios->UltimoAcceso->ViewCustomAttributes = "";

		// IP
		$usuarios->IP->ViewValue = $usuarios->IP->CurrentValue;
		$usuarios->IP->CssStyle = "";
		$usuarios->IP->CssClass = "";
		$usuarios->IP->ViewCustomAttributes = "";

		// usuario
		$usuarios->usuario->HrefValue = "";

		// Clave
		$usuarios->Clave->HrefValue = "";

		// Nombre
		$usuarios->Nombre->HrefValue = "";

		// EMail
		$usuarios->EMail->HrefValue = "";

		// idLevel
		$usuarios->idLevel->HrefValue = "";

		// UltimoAcceso
		$usuarios->UltimoAcceso->HrefValue = "";

		// IP
		$usuarios->IP->HrefValue = "";
	} elseif ($usuarios->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($usuarios->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($usuarios->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$usuarios->Row_Rendered();
}
?>
<?php

// Show link optionally based on User ID
function ShowOptionLink() {
	global $Security, $usuarios;
	if ($Security->IsLoggedIn()) {
		if (!$Security->IsAdmin()) {
			return $Security->IsValidUserID($usuarios->idUsuario->CurrentValue);
		}
	}
	return TRUE;
}
?>
<?php

// Set up Starting Record parameters based on Pager Navigation
function SetUpStartRec() {
	global $nDisplayRecs, $nStartRec, $nTotalRecs, $nPageNo, $usuarios;
	if ($nDisplayRecs == 0) return;

	// Check for a START parameter
	if (@$_GET[EW_TABLE_START_REC] <> "") {
		$nStartRec = $_GET[EW_TABLE_START_REC];
		$usuarios->setStartRecordNumber($nStartRec);
	} elseif (@$_GET[EW_TABLE_PAGE_NO] <> "") {
		$nPageNo = $_GET[EW_TABLE_PAGE_NO];
		if (is_numeric($nPageNo)) {
			$nStartRec = ($nPageNo-1)*$nDisplayRecs+1;
			if ($nStartRec <= 0) {
				$nStartRec = 1;
			} elseif ($nStartRec >= intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1) {
				$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1;
			}
			$usuarios->setStartRecordNumber($nStartRec);
		} else {
			$nStartRec = $usuarios->getStartRecordNumber();
		}
	} else {
		$nStartRec = $usuarios->getStartRecordNumber();
	}

	// Check if correct start record counter
	if (!is_numeric($nStartRec) || $nStartRec == "") { // Avoid invalid start record counter
		$nStartRec = 1; // Reset start record counter
		$usuarios->setStartRecordNumber($nStartRec);
	} elseif (intval($nStartRec) > intval($nTotalRecs)) { // Avoid starting record > total records
		$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to last page first record
		$usuarios->setStartRecordNumber($nStartRec);
	} elseif (($nStartRec-1) % $nDisplayRecs <> 0) {
		$nStartRec = intval(($nStartRec-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to page boundary
		$usuarios->setStartRecordNumber($nStartRec);
	}
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
