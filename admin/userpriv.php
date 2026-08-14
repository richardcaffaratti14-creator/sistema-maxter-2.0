<?php
define("EW_PAGE_ID", "userpriv", TRUE); // Page ID
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

// Common page loading event (in userfn*.php)
Page_Loading();
?>
<?php

// Page load event, used in current page
Page_Load();
?>
<?php
if (!is_array($EW_USER_LEVEL_TABLE_NAME)) {
  $_SESSION["EW_SESSION_MESSAGE"] = "No se generaron tablas";
	Page_Terminate("usuarioslevelslist.php"); // Return to list
}
$arPriv = ew_InitArray(count($EW_USER_LEVEL_TABLE_NAME), 0);

// Get action
if (@$_POST["a_edit"] == "") {
	$usuarioslevels->CurrentAction = "I"; // Display with input box

	// Load key from QueryString
	if (@$_GET["UserLevelID"] <> "") {
		$usuarioslevels->UserLevelID->setQueryStringValue($_GET["UserLevelID"]);
	} else {
		Page_Terminate("usuarioslevelslist.php"); // Return to list
	}
	if ($usuarioslevels->UserLevelID->QueryStringValue == "-1") {
		$sDisabled = " disabled=\"true\"";
	} else {
		$sDisabled = "";
	}
} else {
	$usuarioslevels->CurrentAction = $_POST["a_edit"];

	// Get fields from form
	$usuarioslevels->UserLevelID->setFormValue($_POST["x_UserLevelID"]);
	for ($i = 0; $i < count($EW_USER_LEVEL_TABLE_NAME); $i++) {
		if (defined("EW_USER_LEVEL_COMPAT")) {
			$arPriv[$i] = intval(@$_POST["Add_" . $i]) + 
				intval(@$_POST["Delete_" . $i]) + intval(@$_POST["Edit_" . $i]) +
				intval(@$_POST["List_" . $i]);
		} else {
			$arPriv[$i] = intval(@$_POST["Add_" . $i]) +
				intval(@$_POST["Delete_" . $i]) + intval(@$_POST["Edit_" . $i]) +
				intval(@$_POST["List_" . $i]) + intval(@$_POST["View_" . $i]) +
				intval(@$_POST["Search_" . $i]);
		}
	}
}
switch ($usuarioslevels->CurrentAction) {
	case "I": // Display
		$Security->SetUpUserLevelEx(-2); // Get all User Level info
		break;
	case "U": // Update
		if (EditRow()) { // Update Record based on key
			$_SESSION[EW_SESSION_MESSAGE] = "Actualizacion satisfactoria"; // Set update success message

			// Alternatively, comment out the following line to go back to this page
			Page_Terminate("usuarioslevelslist.php"); // Return to list
		}
}
?>
<?php include "header.php" ?>
<script type="text/javascript">
<!--
var EW_PAGE_ID = "userpriv"; // Page id

//-->
</script>
<script type="text/javascript">
<!--
var firstrowoffset = 1; // First data row start at
var lastrowoffset = 0; // Last data row end at
var EW_LIST_TABLE_NAME = 'ewlistmain'; // Table name for list page
var rowclass = 'ewTableRow'; // Row class
var rowaltclass = 'ewTableAltRow'; // Row alternate class
var rowmoverclass = 'ewTableHighlightRow'; // Row mouse over class
var rowselectedclass = 'ewTableSelectRow'; // Row selected class
var roweditclass = 'ewTableEditRow'; // Row edit class

//-->
</script>
<script language="JavaScript" type="text/javascript">
<!--

// Write your client script here, no need to add script tags.
// To include another .js script, use:
// ew_ClientScriptInclude("my_javascript.js"); 
//-->

</script>
<script language="javascript">
<!--

function ew_SelectAll(sCtrl, bChecked)	{
	for (i=0; i<document.userpriv.elements.length; i++) {
		var elm = document.userpriv.elements[i];
		if (elm.type == "checkbox" && elm.name.substr(0, sCtrl.length+1) == sCtrl + "_") {
			elm.checked = bChecked;
		}
	}
}

//-->
</script>
<p><span class="phpmaker">Permisos de los niveles de usuario<br><br><a href="usuarioslevelslist.php">Volver a la lista</a></span></p>
<p><span class="phpmaker">Nivel de usuario: <?php echo
 $Security->GetUserLevelName($usuarioslevels->UserLevelID->CurrentValue) ?>(<?php echo $usuarioslevels->UserLevelID->CurrentValue ?>)</span></p>
<form name="userpriv" id="userpriv" action="userpriv.php" method="post">
<input type="hidden" name="a_edit" id="a_edit" value="U">
<!-- hidden tag for User Level ID -->
<input type="hidden" name="x_UserLevelID" id="x_UserLevelID" value="<?php echo
 $usuarioslevels->UserLevelID->CurrentValue ?>">
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="phpmaker" style="color: red;"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<table id="ewlistmain" class="ewTable">
	<tr class="ewTableHeader">
		<td>Tablas/Vistas</td>
<?php if (defined("EW_USER_LEVEL_COMPAT")) { ?>
		<td>Lista/Buscar/Vista<input type="checkbox" value="" onClick="ew_SelectAll('List', this.checked);"<?php echo $sDisabled ?>></td>
<?php } else { ?>
		<td>Lista<input type="checkbox" value="" onClick="ew_SelectAll('List', this.checked);"<?php echo $sDisabled ?>></td>
		<td>Vista<input type="checkbox" value="" onClick="ew_SelectAll('View', this.checked);"<?php echo $sDisabled ?>></td>
		<td>Buscar<input type="checkbox" value="" onClick="ew_SelectAll('Search', this.checked);"<?php echo $sDisabled ?>></td>
<?php } ?>

		<td>Agregar/Copiar<input type="checkbox" value="" onClick="ew_SelectAll('Add', this.checked);"<?php echo $sDisabled ?>></td>
		<td>Borrar<input type="checkbox" value="" onClick="ew_SelectAll('Delete', this.checked);"<?php echo $sDisabled ?>></td>
		<td>Editar<input type="checkbox" value="" onClick="ew_SelectAll('Edit', this.checked);"<?php echo $sDisabled ?>></td>

	</tr>
<?php
for ($i = 0; $i < count($EW_USER_LEVEL_TABLE_NAME); $i++) {
	$TempPriv = $Security->GetUserLevelPrivEx($EW_USER_LEVEL_TABLE_NAME[$i], $usuarioslevels->UserLevelID->CurrentValue);

	// Set css class and style
	$usuarioslevels->CssClass = "ewTableRow";
	$usuarioslevels->CssStyle = "";
	$usuarioslevels->RowClientEvents = "onmouseover='ew_MouseOver(this);' onmouseout='ew_MouseOut(this);' onclick='ew_Click(this);'";

	// Display alternate color for rows
	if ($i % 2 == 1) {
		$usuarioslevels->CssClass = "ewTableAltRow";
	}
?>
	<tr<?php echo $usuarioslevels->DisplayAttributes() ?>>
		<td><span class="phpmaker"><?php echo $EW_USER_LEVEL_TABLE_NAME_FRIEND[$i] ?></span></td>

<?php if (defined("EW_USER_LEVEL_COMPAT")) { ?>
		<td align="center"><input type="checkbox" name="List_<?php echo $i ?>" id="List_<?php echo $i ?>" value="8" <?php if (($TempPriv & EW_ALLOW_LIST) == EW_ALLOW_LIST) { ?>checked<?php } ?><?php echo $sDisabled ?>></td>
<?php } else { ?>
		<td align="center"><input type="checkbox" name="List_<?php echo $i ?>" id="List_<?php echo $i ?>" value="8" <?php if (($TempPriv & EW_ALLOW_LIST) == EW_ALLOW_LIST) { ?>checked<?php } ?><?php echo $sDisabled ?>></td>
		<td align="center"><input type="checkbox" name="View_<?php echo $i ?>" id="View_<?php echo $i ?>" value="32" <?php if (($TempPriv & EW_ALLOW_VIEW) == EW_ALLOW_VIEW) { ?>checked<?php } ?><?php echo $sDisabled ?>></td>
		<td align="center"><input type="checkbox" name="Search_<?php echo $i ?>" id="Search_<?php echo $i ?>" value="64" <?php if (($TempPriv & EW_ALLOW_SEARCH) == EW_ALLOW_SEARCH) { ?>checked<?php } ?><?php echo $sDisabled ?>></td>
<?php } ?>

		<td align="center"><input type="checkbox" name="Add_<?php echo $i ?>" id="Add_<?php echo $i ?>" value="1" <?php if (($TempPriv & EW_ALLOW_ADD) == EW_ALLOW_ADD) { ?>checked<?php } ?><?php echo $sDisabled ?>></td>
		<td align="center"><input type="checkbox" name="Delete_<?php echo $i ?>" id="Delete_<?php echo $i ?>" value="2" <?php if (($TempPriv & EW_ALLOW_DELETE) == EW_ALLOW_DELETE) { ?>checked<?php } ?><?php echo $sDisabled ?>></td>
		<td align="center"><input type="checkbox" name="Edit_<?php echo $i ?>" id="Edit_<?php echo $i ?>" value="4" <?php if (($TempPriv & EW_ALLOW_EDIT) == EW_ALLOW_EDIT) { ?>checked<?php } ?><?php echo $sDisabled ?>></td>

	</tr>
<?php } ?>				
</table>	
<p>
<input type="submit" name="btnSubmit" id="btnSubmit" value="Actualizar"<?php echo $sDisabled ?>>
</form>
<script language="JavaScript" type="text/javascript">
<!--

// Write your startup script here
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

// Update privileges
function EditRow() {
	global $conn, $arPriv, $EW_USER_LEVEL_TABLE_NAME, $usuarioslevels;
	for ($i = 0; $i < count($EW_USER_LEVEL_TABLE_NAME); $i++) {
		$Sql = "SELECT * FROM " . EW_USER_LEVEL_PRIV_TABLE . " WHERE " . 
			EW_USER_LEVEL_PRIV_TABLE_NAME_FIELD . " = '" . ew_AdjustSql($EW_USER_LEVEL_TABLE_NAME[$i]) . "' AND " .
			EW_USER_LEVEL_PRIV_USER_LEVEL_ID_FIELD . " = " . $usuarioslevels->UserLevelID->CurrentValue;
		$rs = $conn->Execute($Sql);
		if ($rs && !$rs->EOF) {
			$Sql = "UPDATE " . EW_USER_LEVEL_PRIV_TABLE . " SET " . EW_USER_LEVEL_PRIV_PRIV_FIELD . " = " . $arPriv[$i] . " WHERE " .
				EW_USER_LEVEL_PRIV_TABLE_NAME_FIELD . " = '" . ew_AdjustSql($EW_USER_LEVEL_TABLE_NAME[$i]) . "' AND " .
				EW_USER_LEVEL_PRIV_USER_LEVEL_ID_FIELD . " = " . $usuarioslevels->UserLevelID->CurrentValue;
			$conn->Execute($Sql);
		} else {
			$Sql = "INSERT INTO " . EW_USER_LEVEL_PRIV_TABLE . " (" . EW_USER_LEVEL_PRIV_TABLE_NAME_FIELD . ", " . EW_USER_LEVEL_PRIV_USER_LEVEL_ID_FIELD . ", " . EW_USER_LEVEL_PRIV_PRIV_FIELD . ") VALUES ('" . ew_AdjustSql($EW_USER_LEVEL_TABLE_NAME[$i]) . "', " . $usuarioslevels->UserLevelID->CurrentValue . ", " . $arPriv[$i] . ")";
			$conn->Execute($Sql);
		}
		if ($rs) $rs->Close();
	}
	return TRUE;
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
