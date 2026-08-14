<?php
define("EW_PAGE_ID", "delete", TRUE); // Page ID
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

// Load Key Parameters
$sKey = "";
$bSingleDelete = TRUE; // Initialize as single delete
$arRecKeys = array();
$nKeySelected = 0; // Initialize selected key count
$sFilter = "";
if (@$_GET["UserLevelID"] <> "") {
	$usuarioslevels->UserLevelID->setQueryStringValue($_GET["UserLevelID"]);
	if (!is_numeric($usuarioslevels->UserLevelID->QueryStringValue)) {
		Page_Terminate($usuarioslevels->getReturnUrl()); // Prevent sql injection, exit
	}
	$sKey .= $usuarioslevels->UserLevelID->QueryStringValue;
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
if ($nKeySelected <= 0) Page_Terminate($usuarioslevels->getReturnUrl()); // No key specified, exit

// Build filter
foreach ($arRecKeys as $sKey) {
	$sFilter .= "(";

	// Set up key field
	$sKeyFld = $sKey;
	if (!is_numeric($sKeyFld)) {
		Page_Terminate($usuarioslevels->getReturnUrl()); // Prevent sql injection, exit
	}
	$sFilter .= "`UserLevelID`=" . ew_AdjustSql($sKeyFld) . " AND ";
	if (substr($sFilter, -5) == " AND ") $sFilter = substr($sFilter, 0, strlen($sFilter)-5) . ") OR ";
}
if (substr($sFilter, -4) == " OR ") $sFilter = substr($sFilter, 0, strlen($sFilter)-4);

// Set up filter (Sql Where Clause) and get Return Sql
// Sql constructor in usuarioslevels class, usuarioslevelsinfo.php

$usuarioslevels->CurrentFilter = $sFilter;

// Get action
if (@$_POST["a_delete"] <> "") {
	$usuarioslevels->CurrentAction = $_POST["a_delete"];
} else {
	$usuarioslevels->CurrentAction = "I"; // Display record
}
switch ($usuarioslevels->CurrentAction) {
	case "D": // Delete
		$usuarioslevels->SendEmail = TRUE; // Send email on delete success
		if (DeleteRows()) { // delete rows
			$_SESSION[EW_SESSION_MESSAGE] = "Borrado satisfactorio"; // Set up success message
			Page_Terminate($usuarioslevels->getReturnUrl()); // Return to caller
		}
}

// Load records for display
$rs = LoadRecordset();
$nTotalRecs = $rs->RecordCount(); // Get record count
if ($nTotalRecs <= 0) { // No record found, exit
	$rs->Close();
	Page_Terminate($usuarioslevels->getReturnUrl()); // Return to caller
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
<p><span class="phpmaker">Borrar desde TABLA: Niveles de Usuarios<br><br><a href="<?php echo $usuarioslevels->getReturnUrl() ?>">Volver atras</a></span></p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form action="usuarioslevelsdelete.php" method="post">
<p>
<input type="hidden" name="a_delete" id="a_delete" value="D">
<?php foreach ($arRecKeys as $sKey) { ?>
<input type="hidden" name="key_m[]" id="key_m[]" value="<?php echo ew_HtmlEncode($sKey) ?>">
<?php } ?>
<table class="ewTable">
	<tr class="ewTableHeader">
		<td valign="top">ID de Nivel de Usuario</td>
		<td valign="top">Nivel de Usuario</td>
	</tr>
<?php
$nRecCount = 0;
$i = 0;
while (!$rs->EOF) {
	$nRecCount++;

	// Set row class and style
	$usuarioslevels->CssClass = "ewTableRow";
	$usuarioslevels->CssStyle = "";

	// Display alternate color for rows
	if ($nRecCount % 2 <> 1) {
		$usuarioslevels->CssClass = "ewTableAltRow";
	}

	// Get the field contents
	LoadRowValues($rs);

	// Render row value
	$usuarioslevels->RowType = EW_ROWTYPE_VIEW; // view
	RenderRow();
?>
	<tr<?php echo $usuarioslevels->DisplayAttributes() ?>>
		<td<?php echo $usuarioslevels->UserLevelID->CellAttributes() ?>>
<div<?php echo $usuarioslevels->UserLevelID->ViewAttributes() ?>><?php echo $usuarioslevels->UserLevelID->ViewValue ?></div>
</td>
		<td<?php echo $usuarioslevels->UserLevelName->CellAttributes() ?>>
<div<?php echo $usuarioslevels->UserLevelName->ViewAttributes() ?>><?php echo $usuarioslevels->UserLevelName->ViewValue ?></div>
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
	global $conn, $Security, $usuarioslevels;
	$DeleteRows = TRUE;
	$sWrkFilter = $usuarioslevels->CurrentFilter;

	// Set up filter (Sql Where Clause) and get Return Sql
	// Sql constructor in usuarioslevels class, usuarioslevelsinfo.php

	$usuarioslevels->CurrentFilter = $sWrkFilter;
	$sSql = $usuarioslevels->SQL();
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
			$DeleteRows = $usuarioslevels->Row_Deleting($row);
			if (!$DeleteRows) break;
		}
	}
	if ($DeleteRows) {
		$sKey = "";
		foreach ($rsold as $row) {
			$sThisKey = "";
			if ($sThisKey <> "") $sThisKey .= EW_COMPOSITE_KEY_SEPARATOR;
			$sThisKey .= $row['UserLevelID'];
			$x_UserLevelID = $row['UserLevelID']; // Get User Level id
			$conn->raiseErrorFn = 'ew_ErrorFn';
			$DeleteRows = $conn->Execute($usuarioslevels->DeleteSQL($row)); // Delete
			$conn->raiseErrorFn = '';
			if ($DeleteRows === FALSE)
				break;
			if ($sKey <> "") $sKey .= ", ";
			$sKey .= $sThisKey;
			if (!is_null($x_UserLevelID)) {
				$conn->Execute("DELETE FROM " . EW_USER_LEVEL_PRIV_TABLE . " WHERE " . EW_USER_LEVEL_PRIV_USER_LEVEL_ID_FIELD . " = " . $x_UserLevelID); // Delete user rights as well
			}
		}
	} else {

		// Set up error message
		if ($usuarioslevels->CancelMessage <> "") {
			$_SESSION[EW_SESSION_MESSAGE] = $usuarioslevels->CancelMessage;
			$usuarioslevels->CancelMessage = "";
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
			$usuarioslevels->Row_Deleted($row);
		}	
	}
	return $DeleteRows;
}
?>
<?php

// Load recordset
function LoadRecordset($offset = -1, $rowcnt = -1) {
	global $conn, $usuarioslevels;

	// Call Recordset Selecting event
	$usuarioslevels->Recordset_Selecting($usuarioslevels->CurrentFilter);

	// Load list page sql
	$sSql = $usuarioslevels->SelectSQL();
	if ($offset > -1 && $rowcnt > -1) $sSql .= " LIMIT $offset, $rowcnt";

	// Load recordset
	$conn->raiseErrorFn = 'ew_ErrorFn';	
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';

	// Call Recordset Selected event
	$usuarioslevels->Recordset_Selected($rs);
	return $rs;
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

		// UserLevelID
		$usuarioslevels->UserLevelID->ViewValue = $usuarioslevels->UserLevelID->CurrentValue;
		$usuarioslevels->UserLevelID->CssStyle = "";
		$usuarioslevels->UserLevelID->CssClass = "";
		$usuarioslevels->UserLevelID->ViewCustomAttributes = "";

		// UserLevelName
		$usuarioslevels->UserLevelName->ViewValue = $usuarioslevels->UserLevelName->CurrentValue;
		$usuarioslevels->UserLevelName->CssStyle = "";
		$usuarioslevels->UserLevelName->CssClass = "";
		$usuarioslevels->UserLevelName->ViewCustomAttributes = "";

		// UserLevelID
		$usuarioslevels->UserLevelID->HrefValue = "";

		// UserLevelName
		$usuarioslevels->UserLevelName->HrefValue = "";
	} elseif ($usuarioslevels->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($usuarioslevels->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($usuarioslevels->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$usuarioslevels->Row_Rendered();
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
