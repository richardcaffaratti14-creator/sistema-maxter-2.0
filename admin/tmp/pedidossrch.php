<?php
define("EW_PAGE_ID", "search", TRUE); // Page ID
define("EW_TABLE_NAME", 'pedidos', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "pedidosinfo.php" ?>
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
$Security->LoadCurrentUserLevel('pedidos');
if (!$Security->IsLoggedIn()) {
	$Security->SaveLastUrl();
	Page_Terminate("login.php");
}
if (!$Security->CanSearch()) {
	$Security->SaveLastUrl();
	Page_Terminate("pedidoslist.php");
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
$pedidos->Export = @$_GET["export"]; // Get export parameter
$sExport = $pedidos->Export; // Get export parameter, used in header
$sExportFile = $pedidos->TableVar; // Get export file, used in header
?>
<?php

// Get action
$pedidos->CurrentAction = @$_POST["a_search"];
switch ($pedidos->CurrentAction) {
	case "S": // Get Search Criteria

		// Build search string for advanced search, remove blank field
		$sSrchStr = BuildAdvancedSearch();
		if ($sSrchStr <> "") {
			Page_Terminate("pedidoslist.php?" . $sSrchStr); // Go to list page
		}
		break;
	default: // Restore search settings
		LoadAdvancedSearch();
}

// Render row for search
$pedidos->RowType = EW_ROWTYPE_SEARCH;
RenderRow();
?>
<?php include "header.php" ?>
<script type="text/javascript">
<!--
var EW_PAGE_ID = "search"; // Page id

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
		elm = fobj.elements["x" + infix + "_id"];
		if (elm && !ew_CheckInteger(elm.value)) {
			if (!ew_OnError(elm, "Numero entero incorrecto - Número"))
				return false; 
		}
		elm = fobj.elements["x" + infix + "_idPresupuesto"];
		if (elm && !ew_CheckInteger(elm.value)) {
			if (!ew_OnError(elm, "Numero entero incorrecto - id Presupuesto"))
				return false; 
		}
		elm = fobj.elements["x" + infix + "_sena"];
		if (elm && !ew_CheckNumber(elm.value)) {
			if (!ew_OnError(elm, "Numero de punto flotante incorrecto - sena"))
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
<script language="JavaScript" type="text/javascript">
<!--

// Write your client script here, no need to add script tags.
// To include another .js script, use:
// ew_ClientScriptInclude("my_javascript.js"); 
//-->

</script>
<p><span class="phpmaker">Buscar TABLA: Pedidos<br><br><a href="pedidoslist.php">Volver a la lista</a></span></p>
<form name="fpedidossearch" id="fpedidossearch" action="pedidossrch.php" method="post" onSubmit="return ew_ValidateForm(this);">
<p>
<input type="hidden" name="a_search" id="a_search" value="S">
<table class="ewTable">
	<tr class="ewTableRow">
		<td class="ewTableHeader">Número</td>
		<td<?php echo $pedidos->id->CellAttributes() ?>><span class="ewSearchOpr">=<input type="hidden" name="z_id" id="z_id" value="="></span></td>
		<td<?php echo $pedidos->id->CellAttributes() ?>><span class="phpmaker">
<input type="text" name="x_id" id="x_id" title="" value="<?php echo $pedidos->id->EditValue ?>"<?php echo $pedidos->id->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Estado</td>
		<td<?php echo $pedidos->estado->CellAttributes() ?>><span class="ewSearchOpr">=<input type="hidden" name="z_estado" id="z_estado" value="="></span></td>
		<td<?php echo $pedidos->estado->CellAttributes() ?>><span class="phpmaker">
<select id="x_estado" name="x_estado"<?php echo $pedidos->estado->EditAttributes() ?>>
<!--option value="">Por favor seleccione</option-->
<?php
if (is_array($pedidos->estado->EditValue)) {
	$arwrk = $pedidos->estado->EditValue;
	$rowswrk = count($arwrk);
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($pedidos->estado->AdvancedSearch->SearchValue) == strval($arwrk[$rowcntwrk][0])) ? " selected" : "";	
?>
<option value="<?php echo ew_HtmlEncode($arwrk[$rowcntwrk][0]) ?>"<?php echo $selwrk ?>>
<?php echo $arwrk[$rowcntwrk][1] ?>
</option>
<?php
			}
}
?>
</select>
</span></td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">Pago</td>
		<td<?php echo $pedidos->ped_tarjeta->CellAttributes() ?>><span class="ewSearchOpr">=<input type="hidden" name="z_ped_tarjeta" id="z_ped_tarjeta" value="="></span></td>
		<td<?php echo $pedidos->ped_tarjeta->CellAttributes() ?>><span class="phpmaker">
<?php
$arwrk = $pedidos->ped_tarjeta->EditValue;
if (is_array($arwrk)) {
	$rowswrk = count($arwrk);
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($pedidos->ped_tarjeta->AdvancedSearch->SearchValue) == strval($arwrk[$rowcntwrk][0])) ? " checked" : "";
?>
<?php echo ew_RepeatColumnTable($rowswrk, $rowcntwrk, 1, 1) ?>
<input type="radio" name="x_ped_tarjeta" id="x_ped_tarjeta" title="" value="<?php echo ew_HtmlEncode($arwrk[$rowcntwrk][0]) ?>"<?php echo $selwrk ?><?php echo $pedidos->ped_tarjeta->EditAttributes() ?>>
<?php echo $arwrk[$rowcntwrk][1] ?>
<?php echo ew_RepeatColumnTable($rowswrk, $rowcntwrk, 1, 2) ?>
<?php
	}
}
?>
</span></td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Nombre</td>
		<td<?php echo $pedidos->nombre->CellAttributes() ?>><span class="ewSearchOpr">Contiene<input type="hidden" name="z_nombre" id="z_nombre" value="LIKE"></span></td>
		<td<?php echo $pedidos->nombre->CellAttributes() ?>><span class="phpmaker">
<input type="text" name="x_nombre" id="x_nombre" title="" size="30" maxlength="255" value="<?php echo $pedidos->nombre->EditValue ?>"<?php echo $pedidos->nombre->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">Apellido</td>
		<td<?php echo $pedidos->apellido->CellAttributes() ?>><span class="ewSearchOpr">Contiene<input type="hidden" name="z_apellido" id="z_apellido" value="LIKE"></span></td>
		<td<?php echo $pedidos->apellido->CellAttributes() ?>><span class="phpmaker">
<input type="text" name="x_apellido" id="x_apellido" title="" size="30" maxlength="255" value="<?php echo $pedidos->apellido->EditValue ?>"<?php echo $pedidos->apellido->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Vendedor</td>
		<td<?php echo $pedidos->idVendedor->CellAttributes() ?>><span class="ewSearchOpr">=<input type="hidden" name="z_idVendedor" id="z_idVendedor" value="="></span></td>
		<td<?php echo $pedidos->idVendedor->CellAttributes() ?>><span class="phpmaker">
<select id="x_idVendedor" name="x_idVendedor"<?php echo $pedidos->idVendedor->EditAttributes() ?>>
<!--option value="">Por favor seleccione</option-->
<?php
if (is_array($pedidos->idVendedor->EditValue)) {
	$arwrk = $pedidos->idVendedor->EditValue;
	$rowswrk = count($arwrk);
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($pedidos->idVendedor->AdvancedSearch->SearchValue) == strval($arwrk[$rowcntwrk][0])) ? " selected" : "";	
?>
<option value="<?php echo ew_HtmlEncode($arwrk[$rowcntwrk][0]) ?>"<?php echo $selwrk ?>>
<?php echo $arwrk[$rowcntwrk][1] ?>
</option>
<?php
			}
}
?>
</select>
</span></td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">Evento</td>
		<td<?php echo $pedidos->Evento->CellAttributes() ?>><span class="ewSearchOpr">Contiene<input type="hidden" name="z_Evento" id="z_Evento" value="LIKE"></span></td>
		<td<?php echo $pedidos->Evento->CellAttributes() ?>><span class="phpmaker">
<input type="text" name="x_Evento" id="x_Evento" title="" size="30" maxlength="255" value="<?php echo $pedidos->Evento->EditValue ?>"<?php echo $pedidos->Evento->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">id Presupuesto</td>
		<td<?php echo $pedidos->idPresupuesto->CellAttributes() ?>><span class="ewSearchOpr">=<input type="hidden" name="z_idPresupuesto" id="z_idPresupuesto" value="="></span></td>
		<td<?php echo $pedidos->idPresupuesto->CellAttributes() ?>><span class="phpmaker">
<input type="text" name="x_idPresupuesto" id="x_idPresupuesto" title="" size="30" value="<?php echo $pedidos->idPresupuesto->EditValue ?>"<?php echo $pedidos->idPresupuesto->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">sena</td>
		<td<?php echo $pedidos->sena->CellAttributes() ?>><span class="ewSearchOpr">=<input type="hidden" name="z_sena" id="z_sena" value="="></span></td>
		<td<?php echo $pedidos->sena->CellAttributes() ?>><span class="phpmaker">
<input type="text" name="x_sena" id="x_sena" title="" size="30" value="<?php echo $pedidos->sena->EditValue ?>"<?php echo $pedidos->sena->EditAttributes() ?>>
</span></td>
	</tr>
</table>
<p>
<input type="submit" name="Action" id="Action" value="  Buscar  ">
<input type="button" name="Reset" id="Reset" value=" Reinicar " onclick="ew_ClearForm(this.form);">
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

// Build advanced search
function BuildAdvancedSearch() {
	global $pedidos;
	$sSrchUrl = "";

	// Field id
	BuildSearchUrl($sSrchUrl, $pedidos->id, @$_POST["x_id"], @$_POST["z_id"], @$_POST["v_id"], @$_POST["y_id"], @$_POST["w_id"]);

	// Field estado
	BuildSearchUrl($sSrchUrl, $pedidos->estado, @$_POST["x_estado"], @$_POST["z_estado"], @$_POST["v_estado"], @$_POST["y_estado"], @$_POST["w_estado"]);

	// Field ped_tarjeta
	BuildSearchUrl($sSrchUrl, $pedidos->ped_tarjeta, @$_POST["x_ped_tarjeta"], @$_POST["z_ped_tarjeta"], @$_POST["v_ped_tarjeta"], @$_POST["y_ped_tarjeta"], @$_POST["w_ped_tarjeta"]);

	// Field nombre
	BuildSearchUrl($sSrchUrl, $pedidos->nombre, @$_POST["x_nombre"], @$_POST["z_nombre"], @$_POST["v_nombre"], @$_POST["y_nombre"], @$_POST["w_nombre"]);

	// Field apellido
	BuildSearchUrl($sSrchUrl, $pedidos->apellido, @$_POST["x_apellido"], @$_POST["z_apellido"], @$_POST["v_apellido"], @$_POST["y_apellido"], @$_POST["w_apellido"]);

	// Field idVendedor
	BuildSearchUrl($sSrchUrl, $pedidos->idVendedor, @$_POST["x_idVendedor"], @$_POST["z_idVendedor"], @$_POST["v_idVendedor"], @$_POST["y_idVendedor"], @$_POST["w_idVendedor"]);

	// Field Evento
	BuildSearchUrl($sSrchUrl, $pedidos->Evento, @$_POST["x_Evento"], @$_POST["z_Evento"], @$_POST["v_Evento"], @$_POST["y_Evento"], @$_POST["w_Evento"]);

	// Field idPresupuesto
	BuildSearchUrl($sSrchUrl, $pedidos->idPresupuesto, @$_POST["x_idPresupuesto"], @$_POST["z_idPresupuesto"], @$_POST["v_idPresupuesto"], @$_POST["y_idPresupuesto"], @$_POST["w_idPresupuesto"]);

	// Field sena
	BuildSearchUrl($sSrchUrl, $pedidos->sena, @$_POST["x_sena"], @$_POST["z_sena"], @$_POST["v_sena"], @$_POST["y_sena"], @$_POST["w_sena"]);
	return $sSrchUrl;
}

// Function to build search URL
function BuildSearchUrl(&$Url, &$Fld, $FldVal, $FldOpr, $FldCond, $FldVal2, $FldOpr2) {
	$sWrk = "";
	$FldParm = substr($Fld->FldVar, 2);
	$FldVal = ew_StripSlashes($FldVal);
	if (is_array($FldVal)) $FldVal = implode(",", $FldVal);
	$FldVal2 = ew_StripSlashes($FldVal2);
	if (is_array($FldVal2)) $FldVal2 = implode(",", $FldVal2);
	$FldOpr = strtoupper(trim($FldOpr));
	if ($FldOpr == "BETWEEN") {
		$IsValidValue = ($Fld->FldDataType <> EW_DATATYPE_NUMBER) ||
			($Fld->FldDataType == EW_DATATYPE_NUMBER && is_numeric($FldVal) && is_numeric($FldVal2));
		if ($FldVal <> "" && $FldVal2 <> "" && $IsValidValue) {
			$sWrk = "x_" . $FldParm . "=" . urlencode($FldVal) .
				"&y_" . $FldParm . "=" . urlencode($FldVal2) .
				"&z_" . $FldParm . "=" . urlencode($FldOpr);
		}
	} elseif ($FldOpr == "IS NULL" || $FldOpr == "IS NOT NULL") {
		$sWrk = "x_" . $FldParm . "=" . urlencode($FldVal) .
			"&z_" . $FldParm . "=" . urlencode($FldOpr);
	} else {
		$IsValidValue = ($Fld->FldDataType <> EW_DATATYPE_NUMBER) ||
			($Fld->FldDataType = EW_DATATYPE_NUMBER && is_numeric($FldVal));
		if ($FldVal <> "" && $IsValidValue && ew_IsValidOpr($FldOpr, $Fld->FldDataType)) {
			$sWrk = "x_" . $FldParm . "=" . urlencode($FldVal) .
				"&z_" . $FldParm . "=" . urlencode($FldOpr);
		}
		$IsValidValue = ($Fld->FldDataType <> EW_DATATYPE_NUMBER) ||
			($Fld->FldDataType = EW_DATATYPE_NUMBER && is_numeric($FldVal2));
		if ($FldVal2 <> "" && $IsValidValue && ew_IsValidOpr($FldOpr2, $Fld->FldDataType)) {
			if ($sWrk <> "") $sWrk .= "&v_" . $FldParm . "=" . urlencode($FldCond) . "&";
			$sWrk .= "&y_" . $FldParm . "=" . urlencode($FldVal2) .
				"&w_" . $FldParm . "=" . urlencode($FldOpr2);
		}
	}
	if ($sWrk <> "") {
		if ($Url <> "") $Url .= "&";
		$Url .= $sWrk;
	}
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $pedidos;

	// Call Row Rendering event
	$pedidos->Row_Rendering();

	// Common render codes for all row types
	if ($pedidos->RowType == EW_ROWTYPE_VIEW) { // View row
	} elseif ($pedidos->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($pedidos->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($pedidos->RowType == EW_ROWTYPE_SEARCH) { // Search row

		// id
		$pedidos->id->EditCustomAttributes = "";
		$pedidos->id->EditValue = $pedidos->id->AdvancedSearch->SearchValue;

		// estado
		$pedidos->estado->EditCustomAttributes = "";
		$arwrk = array();
		$arwrk[] = array("0", "Pendiente");
		$arwrk[] = array("1", "Pagado");
		$arwrk[] = array("2", "Cancelado");
		array_unshift($arwrk, array("", "Por favor seleccione"));
		$pedidos->estado->EditValue = $arwrk;

		// ped_tarjeta
		$pedidos->ped_tarjeta->EditCustomAttributes = "";
		$arwrk = array();
		$arwrk[] = array("0", "Contado");
		$arwrk[] = array("1", "TARJETA");
		$pedidos->ped_tarjeta->EditValue = $arwrk;

		// nombre
		$pedidos->nombre->EditCustomAttributes = "";
		$pedidos->nombre->EditValue = ew_HtmlEncode($pedidos->nombre->AdvancedSearch->SearchValue);

		// apellido
		$pedidos->apellido->EditCustomAttributes = "";
		$pedidos->apellido->EditValue = ew_HtmlEncode($pedidos->apellido->AdvancedSearch->SearchValue);

		// idVendedor
		$pedidos->idVendedor->EditCustomAttributes = "";
		$sSqlWrk = "SELECT `id`, `Vendedor` FROM `vendedores`";
		$rswrk = $conn->Execute($sSqlWrk);
		$arwrk = ($rswrk) ? $rswrk->GetRows() : array();
		if ($rswrk) $rswrk->Close();
		array_unshift($arwrk, array("", "Por favor seleccione"));
		$pedidos->idVendedor->EditValue = $arwrk;

		// Evento
		$pedidos->Evento->EditCustomAttributes = "";
		$pedidos->Evento->EditValue = ew_HtmlEncode($pedidos->Evento->AdvancedSearch->SearchValue);

		// idPresupuesto
		$pedidos->idPresupuesto->EditCustomAttributes = "";
		$pedidos->idPresupuesto->EditValue = $pedidos->idPresupuesto->AdvancedSearch->SearchValue;

		// sena
		$pedidos->sena->EditCustomAttributes = "";
		$pedidos->sena->EditValue = $pedidos->sena->AdvancedSearch->SearchValue;
	}

	// Call Row Rendered event
	$pedidos->Row_Rendered();
}
?>
<?php

// Load advanced search
function LoadAdvancedSearch() {
	global $pedidos;
	$pedidos->id->AdvancedSearch->SearchValue = $pedidos->getAdvancedSearch("x_id");
	$pedidos->estado->AdvancedSearch->SearchValue = $pedidos->getAdvancedSearch("x_estado");
	$pedidos->ped_tarjeta->AdvancedSearch->SearchValue = $pedidos->getAdvancedSearch("x_ped_tarjeta");
	$pedidos->nombre->AdvancedSearch->SearchValue = $pedidos->getAdvancedSearch("x_nombre");
	$pedidos->apellido->AdvancedSearch->SearchValue = $pedidos->getAdvancedSearch("x_apellido");
	$pedidos->idVendedor->AdvancedSearch->SearchValue = $pedidos->getAdvancedSearch("x_idVendedor");
	$pedidos->Evento->AdvancedSearch->SearchValue = $pedidos->getAdvancedSearch("x_Evento");
	$pedidos->idPresupuesto->AdvancedSearch->SearchValue = $pedidos->getAdvancedSearch("x_idPresupuesto");
	$pedidos->sena->AdvancedSearch->SearchValue = $pedidos->getAdvancedSearch("x_sena");
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
