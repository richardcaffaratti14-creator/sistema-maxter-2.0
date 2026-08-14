<?php
define("EW_PAGE_ID", "edit", TRUE); // Page ID
define("EW_TABLE_NAME", 'information', TRUE);
?>
<?php
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "informationinfo.php" ?>
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
if (!$Security->IsLoggedIn())
    $Security->AutoLogin();
$Security->LoadCurrentUserLevel('information');
if (!$Security->IsLoggedIn()) {
    $Security->SaveLastUrl();
    Page_Terminate("login.php");
}
if (!$Security->CanEdit()) {
    $Security->SaveLastUrl();
    Page_Terminate("informationlist.php");
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
$information->Export = @$_GET["export"]; // Get export parameter
$sExport = $information->Export; // Get export parameter, used in header
$sExportFile = $information->TableVar; // Get export file, used in header
?>
<?php
// Load key from QueryString
if (@$_GET["key"] <> "") {
    $information->key->setQueryStringValue($_GET["key"]);
}

// Create form object
$objForm = new cFormObj();
if (@$_POST["a_edit"] <> "") {
    $information->CurrentAction = $_POST["a_edit"]; // Get action code
    LoadFormValues(); // Get form values
} else {
    $information->CurrentAction = "I"; // Default action is display
}

// Check if valid key
if ($information->key->CurrentValue == "")
    Page_Terminate($information->getReturnUrl()); // Invalid key, exit
switch ($information->CurrentAction) {
    case "I": // Get a record to display
	if (!LoadRow()) { // Load Record based on key
	    $_SESSION[EW_SESSION_MESSAGE] = "No se encontraron registros"; // No record found
	    Page_Terminate($information->getReturnUrl()); // Return to caller
	}
	break;
    Case "U": // Update
	$information->SendEmail = TRUE; // Send email on update success
	if (EditRow()) { // Update Record based on key
	    $_SESSION[EW_SESSION_MESSAGE] = "Actualización satisfactoria"; // Update success
	    Page_Terminate($information->getReturnUrl()); // Return to caller
	} else {
	    RestoreFormValues(); // Restore form values if update failed
	}
}

// Render the record
$information->RowType = EW_ROWTYPE_EDIT; // Render as edit
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
	for (i = 0; i < rowcnt; i++) {
	    infix = (fobj.key_count) ? String(i + 1) : "";
	    elm = fobj.elements["x" + infix + "_Value"];
	    if (elm && !ew_HasValue(elm)) {
		if (!ew_OnError(elm, "Por favor ingrese el campo requerido - Valor"))
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
<p><span class="phpmaker">Editar TABLA: Configuración<br><br><a href="<?php echo $information->getReturnUrl() ?>">Volver atras</a></span></p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
    ?>
    <p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
    <?php
    $_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form name="finformationedit" id="finformationedit" action="informationedit.php" method="post" onSubmit="return ew_ValidateForm(this);">
    <p>
	<input type="hidden" name="a_edit" id="a_edit" value="U">
    <table class="ewTable">
	<input type="hidden" name="x_key" id="x_key" value="<?php echo ew_HtmlEncode($information->key->CurrentValue) ?>">
	<tr class="ewTableRow">
	    <td class="ewTableHeader">Configuración<span class='ewmsg'>&nbsp;*</span></td>
	    <td<?php echo $information->Title->CellAttributes() ?>><span id="cb_x_Title">
		    <div<?php echo $information->Title->ViewAttributes() ?>><?php echo $information->Title->EditValue ?></div>
		    <input type="hidden" name="x_Title" id="x_Title" value="<?php echo ew_HtmlEncode($information->Title->CurrentValue) ?>">
		</span></td>
	</tr>
	<tr class="ewTableAltRow">
	    <td class="ewTableHeader">Valor<span class='ewmsg'>&nbsp;*</span></td>
	    <td<?php echo $information->Value->CellAttributes() ?>>
		<?
		$ckEditor = $_GET['key'] == 'textovideos';
		if ($information->textarea->CurrentValue == 1) {

		    if ($ckEditor) {
			?>		
			<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
		    <? } ?>
    		<span id="cb_x_Value">
    		    <textarea <?= $ckEditor ? ' class="ckeditor" ' : '' ?> name="x_Value" id="x_Value" style="width:500px; height:140px; font-size:14px"><?php echo $information->Value->EditValue ?></textarea>
    		</span>
		<? } else if ($information->textarea->CurrentValue == 2) { ?>

    		<table class="ewTable" width="100%" style="margin: 0;">
    		    <tr>
    			<td class="ewTableHeader">Desde</td>
    			<td class="ewTableHeader">Hasta</td>
    			<td class="ewTableHeader">Valor (%)</td>
    			<td class="ewTableHeader">
    			    <a href="#" id="addrow">Agregar</a>
    			</td>
    		    </tr>
    		    <tbody id="descbody">

    		    </tbody>

    		</table>
			<input type="hidden" name="x_Value" id="x_Value" title="" size="50" maxlength="255" value="<?php echo $information->Value->EditValue ?>"<?php echo $information->Value->EditAttributes() ?>>
		<? } else { ?>
    		<span id="cb_x_Value">
    		    <input type="text" name="x_Value" id="x_Value" title="" size="50" maxlength="255" value="<?php echo $information->Value->EditValue ?>"<?php echo $information->Value->EditAttributes() ?>>
    		</span>
		<? } ?>
	    </td>
	</tr>
	<tr class="ewTableRow">
	    <td class="ewTableHeader">Ayuda</td>
	    <td<?php echo $information->HelpMessage->CellAttributes() ?>><span id="cb_x_HelpMessage">
		    <div<?php echo $information->HelpMessage->ViewAttributes() ?>><?php echo $information->HelpMessage->EditValue ?></div>
		    <input type="hidden" name="x_HelpMessage" id="x_HelpMessage" value="<?php echo ew_HtmlEncode($information->HelpMessage->CurrentValue) ?>">
		</span></td>
	</tr>
    </table>

    <script>

    function generateJSON() {
	var obj = {};
	obj.dd = [];
	obj.dh = [];
	obj.dm = [];
	$('.dd').each(function (index, value) {
	    obj.dd.push($(value).val());
	});
	$('.dh').each(function (index, value) {
	    obj.dh.push($(value).val());
	});
	$('.dm').each(function (index, value) {
	    obj.dm.push($(value).val());
	});

	//console.info(obj);

	$('#x_Value').val(JSON.stringify(obj));
    }

    function addRow(dd, dh, dm) {
	var row = '<tr>';
	row += '<td><input type="number" step="0.1" class="di dd" value="' + dd + '" /></td>';
	row += '<td><input type="number" step="0.1" class="di dh" value="' + dh + '" /></td>';
	row += '<td><input type="number" step="0.1" class="di dm" value="' + dm + '" /></td>';
	row += '<td><a href="#" class="remrow">Quitar</a></td>';
	row += '</tr>';
	$('#descbody').append(row);
    }

    $('#addrow').on('click', function (e) {
	e.preventDefault();
	//
	addRow(0, 0, 0);
	generateJSON();
    });

    $('body').on('focusout', '.di', function (e) {
	generateJSON();
    });
    $('body').on('click', '.remrow', function (e) {
	e.preventDefault();
	//
	$(this).parent().parent().remove();
	generateJSON();
    });


<?php
$obj = json_decode($information->Value->DbValue);

for ($i = 0; $i < count($obj->dd); $i++) {
    echo 'addRow(' . $obj->dd[$i] . ', ' . $obj->dh[$i] . ', ' . $obj->dm[$i] . '); ';
}
?>

    </script>
    <style>

	#addrow {
	    padding: 5px 8px;
	    background-color: #1A66F4;
	    color: white;
	}

	.remrow {
	    padding: 5px 8px;
	    background-color: #FB0000;
	    color: white;
	}

	.di {
	    width: 65px;
	    padding: 3px;
	}	

    </style>

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
    global $objForm, $information;
    $information->key->setFormValue($objForm->GetValue("x_key"));
    $information->Title->setFormValue($objForm->GetValue("x_Title"));
    $information->Value->setFormValue($objForm->GetValue("x_Value"));
    $information->HelpMessage->setFormValue($objForm->GetValue("x_HelpMessage"));
}

// Restore form values
function RestoreFormValues() {
    global $information;
    $information->key->CurrentValue = $information->key->FormValue;
    $information->Title->CurrentValue = $information->Title->FormValue;
    $information->Value->CurrentValue = $information->Value->FormValue;
    $information->HelpMessage->CurrentValue = $information->HelpMessage->FormValue;
}
?>
<?php

// Load row based on key values
function LoadRow() {
    global $conn, $Security, $information;
    $sFilter = $information->SqlKeyFilter();
    $sFilter = str_replace("@key@", ew_AdjustSql($information->key->CurrentValue), $sFilter); // Replace key value
    // Call Row Selecting event
    $information->Row_Selecting($sFilter);

    // Load sql based on filter
    $information->CurrentFilter = $sFilter;
    $sSql = $information->SQL();
    if ($rs = $conn->Execute($sSql)) {
	if ($rs->EOF) {
	    $LoadRow = FALSE;
	} else {
	    $LoadRow = TRUE;
	    $rs->MoveFirst();
	    LoadRowValues($rs); // Load row values
	    // Call Row Selected event
	    $information->Row_Selected($rs);
	}
	$rs->Close();
    } else {
	$LoadRow = FALSE;
    }
    return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
    global $information;
    $information->key->setDbValue($rs->fields('key'));
    $information->Title->setDbValue($rs->fields('Title'));
    $information->Value->setDbValue($rs->fields('Value'));
    $information->textarea->setDbValue($rs->fields('textarea'));
    $information->HelpMessage->setDbValue($rs->fields('HelpMessage'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
    global $conn, $Security, $information;

    // Call Row Rendering event
    $information->Row_Rendering();

    // Common render codes for all row types
    // Title

    $information->Title->CellCssStyle = "";
    $information->Title->CellCssClass = "";

    // Value
    $information->Value->CellCssStyle = "";
    $information->Value->CellCssClass = "";

    // HelpMessage
    $information->HelpMessage->CellCssStyle = "";
    $information->HelpMessage->CellCssClass = "";
    if ($information->RowType == EW_ROWTYPE_VIEW) { // View row
    } elseif ($information->RowType == EW_ROWTYPE_ADD) { // Add row
    } elseif ($information->RowType == EW_ROWTYPE_EDIT) { // Edit row
	// Title
	$information->Title->EditCustomAttributes = "";
	$information->Title->EditValue = $information->Title->CurrentValue;
	$information->Title->CssStyle = "";
	$information->Title->CssClass = "";
	$information->Title->ViewCustomAttributes = "";

	// Value
	$information->Value->EditCustomAttributes = "";
	$information->Value->EditValue = ew_HtmlEncode($information->Value->CurrentValue);

	// HelpMessage
	$information->HelpMessage->EditCustomAttributes = "";
	$information->HelpMessage->EditValue = $information->HelpMessage->CurrentValue;
	if (!is_null($information->HelpMessage->EditValue))
	    $information->HelpMessage->EditValue = str_replace("\n", "<br>", $information->HelpMessage->EditValue);
	$information->HelpMessage->CssStyle = "";
	$information->HelpMessage->CssClass = "";
	$information->HelpMessage->ViewCustomAttributes = "";

	// Title
	$information->Title->ViewValue = $information->Title->CurrentValue;
	$information->Title->CssStyle = "";
	$information->Title->CssClass = "";
	$information->Title->ViewCustomAttributes = "";
	$information->Title->HrefValue = "";

	// HelpMessage
	$information->HelpMessage->ViewValue = $information->HelpMessage->CurrentValue;
	if (!is_null($information->HelpMessage->ViewValue))
	    $information->HelpMessage->ViewValue = str_replace("\n", "<br>", $information->HelpMessage->ViewValue);
	$information->HelpMessage->CssStyle = "";
	$information->HelpMessage->CssClass = "";
	$information->HelpMessage->ViewCustomAttributes = "";
	$information->HelpMessage->HrefValue = "";
    } elseif ($information->RowType == EW_ROWTYPE_SEARCH) { // Search row
    }

    // Call Row Rendered event
    $information->Row_Rendered();
}
?>
<?php

// Update record based on key values
function EditRow() {
    global $conn, $Security, $information;
    $sFilter = $information->SqlKeyFilter();
    $sFilter = str_replace("@key@", ew_AdjustSql($information->key->CurrentValue), $sFilter); // Replace key value
    $information->CurrentFilter = $sFilter;
    $sSql = $information->SQL();
    $conn->raiseErrorFn = 'ew_ErrorFn';
    $rs = $conn->Execute($sSql);
    $conn->raiseErrorFn = '';
    if ($rs === FALSE)
	return FALSE;
    if ($rs->EOF) {
	$EditRow = FALSE; // Update Failed
    } else {

	// Save old values
	$rsold = & $rs->fields;
	$rsnew = array();

	// Field Value
	$information->Value->SetDbValueDef($information->Value->CurrentValue, "");
	$rsnew['Value'] = & $information->Value->DbValue;

	// Call Row Updating event
	$bUpdateRow = $information->Row_Updating($rsold, $rsnew);
	if ($bUpdateRow) {
	    $conn->raiseErrorFn = 'ew_ErrorFn';
	    $EditRow = $conn->Execute($information->UpdateSQL($rsnew));
	    $conn->raiseErrorFn = '';
	} else {
	    if ($information->CancelMessage <> "") {
		$_SESSION[EW_SESSION_MESSAGE] = $information->CancelMessage;
		$information->CancelMessage = "";
	    } else {
		$_SESSION[EW_SESSION_MESSAGE] = "Actualización cancelada";
	    }
	    $EditRow = FALSE;
	}
    }

    // Call Row Updated event
    if ($EditRow) {
	$information->Row_Updated($rsold, $rsnew);
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
