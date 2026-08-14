<?php
define("EW_PAGE_ID", "view", TRUE); // Page ID
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
if (!$Security->CanView()) {
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
if (@$_GET["key"] <> "") {
    $information->key->setQueryStringValue($_GET["key"]);
} else {
    Page_Terminate("informationlist.php"); // Return to list page
}

// Get action
if (@$_POST["a_view"] <> "") {
    $information->CurrentAction = $_POST["a_view"];
} else {
    $information->CurrentAction = "I"; // Display form
}
switch ($information->CurrentAction) {
    case "I": // Get a record to display
	if (!LoadRow()) { // Load record based on key
	    $_SESSION[EW_SESSION_MESSAGE] = "No se encontraron registros"; // Set no record message
	    Page_Terminate("informationlist.php"); // Return to list
	}
}

// Set return url
$information->setReturnUrl("informationview.php");

// Render row
$information->RowType = EW_ROWTYPE_VIEW;
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
<p><span class="phpmaker">Vista TABLA: Configuración
	<br><br>
	<a href="informationlist.php">Volver a la lista</a>&nbsp;
	<?php if ($Security->CanEdit()) { ?>
    	<a href="<?php echo $information->EditUrl() ?>">Editar</a>&nbsp;
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
	    <td class="ewTableHeader">Configuración</td>
	    <td<?php echo $information->Title->CellAttributes() ?>>
		<div<?php echo $information->Title->ViewAttributes() ?>><?php echo $information->Title->ViewValue ?></div>
	    </td>
	</tr>
	<tr class="ewTableAltRow">
	    <td class="ewTableHeader">Valor</td>
	    <td<?php echo $information->Value->CellAttributes() ?>>
		<?php if ($information->textarea->CurrentValue == 2) { ?>
    		<table class="ewTable" width="100%" style="margin: 0;">
    		    <tr>
    			<td class="ewTableHeader">Desde</td>
    			<td class="ewTableHeader">Hasta</td>
    			<td class="ewTableHeader">Valor</td>
    		    </tr>
    		    <tbody id="descbody">
			    <?
			    $obj = json_decode($information->Value->DbValue);

			    for ($i = 0; $i < count($obj->dd); $i++) {
				echo '<tr><td>' . $obj->dd[$i] . '</td>'
					. '<td>' . $obj->dh[$i] . '</td>'
					. '<td>' . $obj->dm[$i] . '</td></tr> ';
			    }
			    ?>
    		    </tbody>

    		</table>

		<? } else { ?>
    		<div<?php echo $information->Value->ViewAttributes() ?>><?php echo $information->Value->ViewValue ?></div>
		<? } ?>
	    </td>
	</tr>
	<tr class="ewTableRow">
	    <td class="ewTableHeader">Ayuda</td>
	    <td<?php echo $information->HelpMessage->CellAttributes() ?>>
		<div<?php echo $information->HelpMessage->ViewAttributes() ?>><?php echo $information->HelpMessage->ViewValue ?></div>
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
	    // Title
	    $information->Title->ViewValue = $information->Title->CurrentValue;
	    $information->Title->CssStyle = "";
	    $information->Title->CssClass = "";
	    $information->Title->ViewCustomAttributes = "";

	    // Value
	    $information->Value->ViewValue = $information->Value->CurrentValue;
	    $information->Value->CssStyle = "";
	    $information->Value->CssClass = "";
	    $information->Value->ViewCustomAttributes = "";

	    // HelpMessage
	    $information->HelpMessage->ViewValue = $information->HelpMessage->CurrentValue;
	    if (!is_null($information->HelpMessage->ViewValue))
		$information->HelpMessage->ViewValue = str_replace("\n", "<br>", $information->HelpMessage->ViewValue);
	    $information->HelpMessage->CssStyle = "";
	    $information->HelpMessage->CssClass = "";
	    $information->HelpMessage->ViewCustomAttributes = "";

	    // Title
	    $information->Title->HrefValue = "";

	    // Value
	    $information->Value->HrefValue = "";

	    // HelpMessage
	    $information->HelpMessage->HrefValue = "";
	} elseif ($information->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($information->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($information->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$information->Row_Rendered();
    }
    ?>
    <?php

// Set up Starting Record parameters based on Pager Navigation
    function SetUpStartRec() {
	global $nDisplayRecs, $nStartRec, $nTotalRecs, $nPageNo, $information;
	if ($nDisplayRecs == 0)
	    return;

	// Check for a START parameter
	if (@$_GET[EW_TABLE_START_REC] <> "") {
	    $nStartRec = $_GET[EW_TABLE_START_REC];
	    $information->setStartRecordNumber($nStartRec);
	} elseif (@$_GET[EW_TABLE_PAGE_NO] <> "") {
	    $nPageNo = $_GET[EW_TABLE_PAGE_NO];
	    if (is_numeric($nPageNo)) {
		$nStartRec = ($nPageNo - 1) * $nDisplayRecs + 1;
		if ($nStartRec <= 0) {
		    $nStartRec = 1;
		} elseif ($nStartRec >= intval(($nTotalRecs - 1) / $nDisplayRecs) * $nDisplayRecs + 1) {
		    $nStartRec = intval(($nTotalRecs - 1) / $nDisplayRecs) * $nDisplayRecs + 1;
		}
		$information->setStartRecordNumber($nStartRec);
	    } else {
		$nStartRec = $information->getStartRecordNumber();
	    }
	} else {
	    $nStartRec = $information->getStartRecordNumber();
	}

	// Check if correct start record counter
	if (!is_numeric($nStartRec) || $nStartRec == "") { // Avoid invalid start record counter
	    $nStartRec = 1; // Reset start record counter
	    $information->setStartRecordNumber($nStartRec);
	} elseif (intval($nStartRec) > intval($nTotalRecs)) { // Avoid starting record > total records
	    $nStartRec = intval(($nTotalRecs - 1) / $nDisplayRecs) * $nDisplayRecs + 1; // Point to last page first record
	    $information->setStartRecordNumber($nStartRec);
	} elseif (($nStartRec - 1) % $nDisplayRecs <> 0) {
	    $nStartRec = intval(($nStartRec - 1) / $nDisplayRecs) * $nDisplayRecs + 1; // Point to page boundary
	    $information->setStartRecordNumber($nStartRec);
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
