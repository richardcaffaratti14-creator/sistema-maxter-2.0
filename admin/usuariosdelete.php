<?php
define("EW_PAGE_ID", "delete", TRUE); // Page ID
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
if (!$Security->CanDelete()) {
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

// Load Key Parameters
$sKey = "";
$bSingleDelete = TRUE; // Initialize as single delete
$arRecKeys = array();
$nKeySelected = 0; // Initialize selected key count
$sFilter = "";
if (@$_GET["idUsuario"] <> "") {
	$usuarios->idUsuario->setQueryStringValue($_GET["idUsuario"]);
	if (!is_numeric($usuarios->idUsuario->QueryStringValue)) {
		Page_Terminate($usuarios->getReturnUrl()); // Prevent sql injection, exit
	}
	$sKey .= $usuarios->idUsuario->QueryStringValue;
} else {
	$bSingleDelete = FALSE;
}
if ($bSingleDelete) {
	$nKeySelected = 1; // Set up key selected count
	$arRecKeys[0] = $sKey;
} else {
	if (isset($_POST["key_m"])) { // Key in form
		$nKeySelected = count($_POST["key_m"]); // Set up key selected count
		$arRecKeys = ew_StripSlashes($_POST["key_m"]);
	}
}
if ($nKeySelected <= 0) Page_Terminate($usuarios->getReturnUrl()); // No key specified, exit

// Build filter
foreach ($arRecKeys as $sKey) {
	$sFilter .= "(";

	// Set up key field
	$sKeyFld = $sKey;
	if (!is_numeric($sKeyFld)) {
		Page_Terminate($usuarios->getReturnUrl()); // Prevent sql injection, exit
	}
	$sFilter .= "`idUsuario`=" . ew_AdjustSql($sKeyFld) . " AND ";
	if (substr($sFilter, -5) == " AND ") $sFilter = substr($sFilter, 0, strlen($sFilter)-5) . ") OR ";
}
if (substr($sFilter, -4) == " OR ") $sFilter = substr($sFilter, 0, strlen($sFilter)-4);

// Set up filter (Sql Where Clause) and get Return Sql
// Sql constructor in usuarios class, usuariosinfo.php

$usuarios->CurrentFilter = $sFilter;

// Get action
if (@$_POST["a_delete"] <> "") {
	$usuarios->CurrentAction = $_POST["a_delete"];
} else {
	$usuarios->CurrentAction = "I"; // Display record
}
switch ($usuarios->CurrentAction) {
	case "D": // Delete
		$usuarios->SendEmail = TRUE; // Send email on delete success
		if (DeleteRows()) { // delete rows
			$_SESSION[EW_SESSION_MESSAGE] = "Borrado satisfactorio"; // Set up success message
			Page_Terminate($usuarios->getReturnUrl()); // Return to caller
		}
}

// Load records for display
$rs = LoadRecordset();
$nTotalRecs = $rs->RecordCount(); // Get record count
if ($nTotalRecs <= 0) { // No record found, exit
	$rs->Close();
	Page_Terminate($usuarios->getReturnUrl()); // Return to caller
}
?>
<?php include "header.php" ?>
<script type="text/javascript">
<!--
var EW_PAGE_ID = "delete"; // Page id

//-->
</script>
<script language="JavaScript" type="text/javascript">
<!--

// Write your client script here, no need to add script tags.
// To include another .js script, use:
// ew_ClientScriptInclude("my_javascript.js"); 
//-->

</script>
<p><span class="phpmaker">Borrar desde TABLA: Usuarios<br><br><a href="<?php echo $usuarios->getReturnUrl() ?>">Volver atras</a></span></p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form action="usuariosdelete.php" method="post">
<p>
<input type="hidden" name="a_delete" id="a_delete" value="D">
<?php foreach ($arRecKeys as $sKey) { ?>
<input type="hidden" name="key_m[]" id="key_m[]" value="<?php echo ew_HtmlEncode($sKey) ?>">
<?php } ?>
<table class="ewTable">
	<tr class="ewTableHeader">
		<td valign="top">Nombre</td>
		<td valign="top">Nivel</td>
		<td valign="top">Ultimo Acceso</td>
		<td valign="top">IP</td>
	</tr>
<?php
$nRecCount = 0;
$i = 0;
while (!$rs->EOF) {
	$nRecCount++;

	// Set row class and style
	$usuarios->CssClass = "ewTableRow";
	$usuarios->CssStyle = "";

	// Display alternate color for rows
	if ($nRecCount % 2 <> 1) {
		$usuarios->CssClass = "ewTableAltRow";
	}

	// Get the field contents
	LoadRowValues($rs);

	// Render row value
	$usuarios->RowType = EW_ROWTYPE_VIEW; // view
	RenderRow();
?>
	<tr<?php echo $usuarios->DisplayAttributes() ?>>
		<td<?php echo $usuarios->Nombre->CellAttributes() ?>>
<div<?php echo $usuarios->Nombre->ViewAttributes() ?>><?php echo $usuarios->Nombre->ViewValue ?></div>
</td>
		<td<?php echo $usuarios->idLevel->CellAttributes() ?>>
<div<?php echo $usuarios->idLevel->ViewAttributes() ?>><?php echo $usuarios->idLevel->ViewValue ?></div>
</td>
		<td<?php echo $usuarios->UltimoAcceso->CellAttributes() ?>>
<div<?php echo $usuarios->UltimoAcceso->ViewAttributes() ?>><?php echo $usuarios->UltimoAcceso->ViewValue ?></div>
</td>
		<td<?php echo $usuarios->IP->CellAttributes() ?>>
<div<?php echo $usuarios->IP->ViewAttributes() ?>><?php echo $usuarios->IP->ViewValue ?></div>
</td>
	</tr>
<?php
	$rs->MoveNext();
}
$rs->Close();
?>
</table>
<p>
<input type="submit" name="Action" id="Action" value="Confirmación de Borrado">
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

// ------------------------------------------------
//  Function DeleteRows
//  - Delete Records based on current filter
function DeleteRows() {
	global $conn, $Security, $usuarios;
	$DeleteRows = TRUE;
	$sWrkFilter = $usuarios->CurrentFilter;
	if ($Security->CurrentUserID() <> "" && !$Security->IsAdmin()) { // Non system admin
		$sWrkFilter = $usuarios->AddUserIDFilter($sWrkFilter, $Security->CurrentUserID()); // Add User ID filter
	}

	// Set up filter (Sql Where Clause) and get Return Sql
	// Sql constructor in usuarios class, usuariosinfo.php

	$usuarios->CurrentFilter = $sWrkFilter;
	$sSql = $usuarios->SQL();
	$conn->raiseErrorFn = 'ew_ErrorFn';
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';
	if ($rs === FALSE) {
		return FALSE;
	} elseif ($rs->EOF) {
		$_SESSION[EW_SESSION_MESSAGE] = "No se encontraron registros"; // No record found
		$rs->Close();
		return FALSE;
	}
	$conn->BeginTrans();

	// Clone old rows
	$rsold = ($rs) ? $rs->GetRows() : array();
	if ($rs) $rs->Close();

	// Call row deleting event
	if ($DeleteRows) {
		foreach ($rsold as $row) {
			$DeleteRows = $usuarios->Row_Deleting($row);
			if (!$DeleteRows) break;
		}
	}
	if ($DeleteRows) {
		$sKey = "";
		foreach ($rsold as $row) {
			$sThisKey = "";
			if ($sThisKey <> "") $sThisKey .= EW_COMPOSITE_KEY_SEPARATOR;
			$sThisKey .= $row['idUsuario'];
			$conn->raiseErrorFn = 'ew_ErrorFn';
			$DeleteRows = $conn->Execute($usuarios->DeleteSQL($row)); // Delete
			$conn->raiseErrorFn = '';
			if ($DeleteRows === FALSE)
				break;
			if ($sKey <> "") $sKey .= ", ";
			$sKey .= $sThisKey;
		}
	} else {

		// Set up error message
		if ($usuarios->CancelMessage <> "") {
			$_SESSION[EW_SESSION_MESSAGE] = $usuarios->CancelMessage;
			$usuarios->CancelMessage = "";
		} else {
			$_SESSION[EW_SESSION_MESSAGE] = "Borrar cancelado";
		}
	}
	if ($DeleteRows) {
		$conn->CommitTrans(); // Commit the changes
	} else {
		$conn->RollbackTrans(); // Rollback changes
	}

	// Call recordset deleted event
	if ($DeleteRows) {
		foreach ($rsold as $row) {
			$usuarios->Row_Deleted($row);
		}	
	}
	return $DeleteRows;
}
?>
<?php

// Load recordset
function LoadRecordset($offset = -1, $rowcnt = -1) {
	global $conn, $usuarios;

	// Call Recordset Selecting event
	$usuarios->Recordset_Selecting($usuarios->CurrentFilter);

	// Load list page sql
	$sSql = $usuarios->SelectSQL();
	if ($offset > -1 && $rowcnt > -1) $sSql .= " LIMIT $offset, $rowcnt";

	// Load recordset
	$conn->raiseErrorFn = 'ew_ErrorFn';	
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';

	// Call Recordset Selected event
	$usuarios->Recordset_Selected($rs);
	return $rs;
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
	// Nombre

	$usuarios->Nombre->CellCssStyle = "";
	$usuarios->Nombre->CellCssClass = "";

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

		// Nombre
		$usuarios->Nombre->ViewValue = $usuarios->Nombre->CurrentValue;
		$usuarios->Nombre->CssStyle = "";
		$usuarios->Nombre->CssClass = "";
		$usuarios->Nombre->ViewCustomAttributes = "";

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

		// Nombre
		$usuarios->Nombre->HrefValue = "";

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

// Page Load event
function Page_Load() {

	//echo "Page Load";
}

// Page Unload event
function Page_Unload() {

	//echo "Page Unload";
}
?>
