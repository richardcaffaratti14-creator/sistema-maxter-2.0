<?php
define("EW_PAGE_ID", "view", TRUE); // Page ID
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
if (!$Security->CanView()) {
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
if (@$_GET["id"] <> "") {
	$pedidos->id->setQueryStringValue($_GET["id"]);
} else {
	Page_Terminate("pedidoslist.php"); // Return to list page
}

// Get action
if (@$_POST["a_view"] <> "") {
	$pedidos->CurrentAction = $_POST["a_view"];
} else {
	$pedidos->CurrentAction = "I"; // Display form
}
switch ($pedidos->CurrentAction) {
	case "I": // Get a record to display
		if (!LoadRow()) { // Load record based on key
			$_SESSION[EW_SESSION_MESSAGE] = "No se encontraron registros"; // Set no record message
			Page_Terminate("pedidoslist.php"); // Return to list
		}
}

// Set return url
$pedidos->setReturnUrl("pedidosview.php");

// Render row
$pedidos->RowType = EW_ROWTYPE_VIEW;
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
<p class="noprint"><span class="phpmaker">Vista TABLA: Pedidos
<br><br>
<a href="pedidoslist.php">Volver a la lista</a>&nbsp;
<?php if ($Security->CanEdit()) { ?>
<a href="<?php echo $pedidos->EditUrl() ?>">Editar</a>
<?php } ?>


&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="../ajax/pdf?id=<?php echo $pedidos->id->CurrentValue ?>" target="_blank">COMPROBANTE</a>&nbsp;

<a href="../?process_order=<?=$pedidos->id->CurrentValue?>" target="_blank">Procesar</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

<?php if ($Security->CanDelete()) { ?>
<a href="<?php echo $pedidos->DeleteUrl() ?>">Borrar</a>&nbsp;
<?php } ?>

<a href="javascript:window.print()">Imprimir</a>&nbsp;

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
		<td class="ewTableHeader">Número</td>
		<td<?php echo $pedidos->id->CellAttributes() ?>>
<div<?php echo $pedidos->id->ViewAttributes() ?>><?php echo $pedidos->id->ViewValue ?></div>
</td>
		<td class="ewTableHeader">Fecha</td>
		<td<?php echo $pedidos->Fecha->CellAttributes() ?>>
<div<?php echo $pedidos->Fecha->ViewAttributes() ?>><?php echo $pedidos->Fecha->ViewValue ?></div>
</td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Total</td>
		<td<?php echo $pedidos->total->CellAttributes() ?>>
<div>
<?if ($pedidos->Descuento->CurrentValue || $pedidos->sena->CurrentValue) {?>
	<div style="margin-bottom:4px;">
		<?php echo $pedidos->total->ViewValue ?> 
		
		<?if ($pedidos->Descuento->CurrentValue) {?>
		- <span style="color:#a00000" title="Descuento"><?= $pedidos->Descuento->ViewValue ?></span>
		<?}?>
		
		<?if ($pedidos->sena->CurrentValue) {?>
		- <span style="color:#00a000" title="Seña"><?= $pedidos->sena->ViewValue ?></span>
		<?}?>
		= 
	</div>
<?}?>
<span style="font-weight:bold; "><?php echo ew_FormatCurrency($pedidos->total->CurrentValue - $pedidos->Descuento->CurrentValue - $pedidos->sena->CurrentValue, 2, -2, -2, -2); ?></span>

</div>
</td>
		<td class="ewTableHeader">Vendedor</td>
		<td<?php echo $pedidos->idVendedor->CellAttributes() ?>>
<div<?php echo $pedidos->idVendedor->ViewAttributes() ?>><?php echo $pedidos->idVendedor->ViewValue ?></div>
</td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">Nombre</td>
		<td<?php echo $pedidos->nombre->CellAttributes() ?>>
<div<?php echo $pedidos->nombre->ViewAttributes() ?>><?php echo $pedidos->nombre->ViewValue ?></div>
</td>
		<td class="ewTableHeader">Apellido</td>
		<td<?php echo $pedidos->apellido->CellAttributes() ?>>
<div<?php echo $pedidos->apellido->ViewAttributes() ?>><?php echo $pedidos->apellido->ViewValue ?></div>
</td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Teléfono</td>
		<td<?php echo $pedidos->telefono->CellAttributes() ?>>
<div<?php echo $pedidos->telefono->ViewAttributes() ?>><?php echo $pedidos->telefono->ViewValue ?></div>
</td>
		<td class="ewTableHeader">Evento</td>
		<td<?php echo $pedidos->Evento->CellAttributes() ?>>
<div<?php echo $pedidos->Evento->ViewAttributes() ?>><?php echo $pedidos->Evento->ViewValue ?></div>
</td>
	</tr>
	
<?
$extra = array();
if (!empty($pedidos->extra->CurrentValue)) $extra = unserialize($pedidos->extra->CurrentValue);
?>	
	<tr class="ewTableRow">
		<td class="ewTableHeader">Retiro</td>
		<td<?php echo $pedidos->Evento->CellAttributes() ?>>
		<div><?php echo isset($extra['retiro']) ? $extra['retiro'] : "-" ?></div>
</td>

		<td class="ewTableHeader">Estado</td>
		<td<?php echo $pedidos->estado->CellAttributes() ?>>
<div<?php echo $pedidos->estado->ViewAttributes() ?>><?php echo $pedidos->estado->ViewValue ?>


<?php echo $pedidos->ped_tarjeta->ViewValue ?></div>

</td>

	</tr>

	
	
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Descripción</td>
		<td style="line-height: 22px;" colspan="3">
<div<?php echo $pedidos->descripcion->ViewAttributes() ?>><?php echo $pedidos->descripcion->ViewValue ?>
<br/>
<?

$tmp = getSiteInfo('filespath');
if (substr($tmp, -1,1) != '\\')
	$tmp .= "\\";
?>
<?=$tmp?>pedidos\<?= $pedidos->id->CurrentValue ?>\
</div>
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
	global $conn, $Security, $pedidos;
	$sFilter = $pedidos->SqlKeyFilter();
	if (!is_numeric($pedidos->id->CurrentValue)) {
		return FALSE; // Invalid key, exit
	}
	$sFilter = str_replace("@id@", ew_AdjustSql($pedidos->id->CurrentValue), $sFilter); // Replace key value

	// Call Row Selecting event
	$pedidos->Row_Selecting($sFilter);

	// Load sql based on filter
	$pedidos->CurrentFilter = $sFilter;
	$sSql = $pedidos->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$pedidos->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $pedidos;
	$pedidos->id->setDbValue($rs->fields('id'));
	$pedidos->Fecha->setDbValue($rs->fields('Fecha'));
	$pedidos->estado->setDbValue($rs->fields('estado'));
	$pedidos->ped_tarjeta->setDbValue($rs->fields('ped_tarjeta'));
	$pedidos->total->setDbValue($rs->fields('total'));
	$pedidos->Descuento->setDbValue($rs->fields('Descuento'));
	$pedidos->nombre->setDbValue($rs->fields('nombre'));
	$pedidos->apellido->setDbValue($rs->fields('apellido'));
	$pedidos->telefono->setDbValue($rs->fields('telefono'));
	$pedidos->idVendedor->setDbValue($rs->fields('idVendedor'));
	$pedidos->Evento->setDbValue($rs->fields('Evento'));
	$pedidos->descripcion->setDbValue($rs->fields('descripcion'));
	$pedidos->pedido->setDbValue($rs->fields('pedido'));
	$pedidos->extra->setDbValue($rs->fields('extra'));
	$pedidos->idPresupuesto->setDbValue($rs->fields('idPresupuesto'));
	$pedidos->sena->setDbValue($rs->fields('sena'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $pedidos;

	// Call Row Rendering event
	$pedidos->Row_Rendering();

	// Common render codes for all row types
	// id

	$pedidos->id->CellCssStyle = "";
	$pedidos->id->CellCssClass = "";

	// Fecha
	$pedidos->Fecha->CellCssStyle = "";
	$pedidos->Fecha->CellCssClass = "";

	// estado
	$pedidos->estado->CellCssStyle = "";
	$pedidos->estado->CellCssClass = "";

	// ped_tarjeta
	$pedidos->ped_tarjeta->CellCssStyle = "";
	$pedidos->ped_tarjeta->CellCssClass = "";

	// total
	$pedidos->total->CellCssStyle = "";
	$pedidos->total->CellCssClass = "";

	// nombre
	$pedidos->nombre->CellCssStyle = "";
	$pedidos->nombre->CellCssClass = "";

	// apellido
	$pedidos->apellido->CellCssStyle = "";
	$pedidos->apellido->CellCssClass = "";

	// telefono
	$pedidos->telefono->CellCssStyle = "";
	$pedidos->telefono->CellCssClass = "";

	// idVendedor
	$pedidos->idVendedor->CellCssStyle = "";
	$pedidos->idVendedor->CellCssClass = "";

	// Evento
	$pedidos->Evento->CellCssStyle = "";
	$pedidos->Evento->CellCssClass = "";

	// descripcion
	$pedidos->descripcion->CellCssStyle = "";
	$pedidos->descripcion->CellCssClass = "";

	// pedido
	$pedidos->pedido->CellCssStyle = "";
	$pedidos->pedido->CellCssClass = "";

	// extra
	$pedidos->extra->CellCssStyle = "";
	$pedidos->extra->CellCssClass = "";

	// idPresupuesto
	$pedidos->idPresupuesto->CellCssStyle = "";
	$pedidos->idPresupuesto->CellCssClass = "";

	// sena
	$pedidos->sena->CellCssStyle = "";
	$pedidos->sena->CellCssClass = "";
	if ($pedidos->RowType == EW_ROWTYPE_VIEW) { // View row

		// id
		$pedidos->id->ViewValue = $pedidos->id->CurrentValue;
		$pedidos->id->CssStyle = "";
		$pedidos->id->CssClass = "";
		$pedidos->id->ViewCustomAttributes = "";

		// Fecha
		$pedidos->Fecha->ViewValue = $pedidos->Fecha->CurrentValue;
		$pedidos->Fecha->ViewValue = ew_FormatDateTime($pedidos->Fecha->ViewValue, 7);
		$pedidos->Fecha->CssStyle = "";
		$pedidos->Fecha->CssClass = "";
		$pedidos->Fecha->ViewCustomAttributes = "";

		// estado
		if (!is_null($pedidos->estado->CurrentValue)) {
			switch ($pedidos->estado->CurrentValue) {
				case "0":
					$pedidos->estado->ViewValue = "Pendiente";
					break;
				case "1":
					$pedidos->estado->ViewValue = "Pagado";
					break;
				case "2":
					$pedidos->estado->ViewValue = "Cancelado";
					break;
				default:
					$pedidos->estado->ViewValue = $pedidos->estado->CurrentValue;
			}
		} else {
			$pedidos->estado->ViewValue = NULL;
		}
		$pedidos->estado->CssStyle = "";
		$pedidos->estado->CssClass = "";
		$pedidos->estado->ViewCustomAttributes = "";

		// ped_tarjeta
		if (!is_null($pedidos->ped_tarjeta->CurrentValue)) {
			switch ($pedidos->ped_tarjeta->CurrentValue) {
				case "0":
					$pedidos->ped_tarjeta->ViewValue = "Contado";
					break;
				case "1":
					$pedidos->ped_tarjeta->ViewValue = "TARJETA";
					break;
				default:
					$pedidos->ped_tarjeta->ViewValue = $pedidos->ped_tarjeta->CurrentValue;
			}
		} else {
			$pedidos->ped_tarjeta->ViewValue = NULL;
		}
		$pedidos->ped_tarjeta->CssStyle = "";
		$pedidos->ped_tarjeta->CssClass = "";
		$pedidos->ped_tarjeta->ViewCustomAttributes = "";

		// total
		$pedidos->total->ViewValue = $pedidos->total->CurrentValue;
		$pedidos->total->ViewValue = ew_FormatCurrency($pedidos->total->ViewValue, 2, -2, -2, -2);
		$pedidos->total->CssStyle = "";
		$pedidos->total->CssClass = "";
		$pedidos->total->ViewCustomAttributes = "";

		// Descuento
		$pedidos->Descuento->ViewValue = $pedidos->Descuento->CurrentValue;
		$pedidos->Descuento->ViewValue = ew_FormatCurrency($pedidos->Descuento->ViewValue, 2, -2, -2, -2);
		$pedidos->Descuento->CssStyle = "";
		$pedidos->Descuento->CssClass = "";
		$pedidos->Descuento->ViewCustomAttributes = "";

		// sena
		$pedidos->sena->ViewValue = $pedidos->sena->CurrentValue;
		$pedidos->sena->ViewValue = ew_FormatCurrency($pedidos->sena->ViewValue, 2, -2, -2, -2);
		$pedidos->sena->CssStyle = "";
		$pedidos->sena->CssClass = "";
		$pedidos->sena->ViewCustomAttributes = "";
		
		// nombre
		$pedidos->nombre->ViewValue = $pedidos->nombre->CurrentValue;
		$pedidos->nombre->CssStyle = "";
		$pedidos->nombre->CssClass = "";
		$pedidos->nombre->ViewCustomAttributes = "";

		// apellido
		$pedidos->apellido->ViewValue = $pedidos->apellido->CurrentValue;
		$pedidos->apellido->CssStyle = "";
		$pedidos->apellido->CssClass = "";
		$pedidos->apellido->ViewCustomAttributes = "";

		// telefono
		$pedidos->telefono->ViewValue = $pedidos->telefono->CurrentValue;
		$pedidos->telefono->CssStyle = "";
		$pedidos->telefono->CssClass = "";
		$pedidos->telefono->ViewCustomAttributes = "";

		// idVendedor
		if (!empty($pedidos->idVendedor->CurrentValue)) {
			$sSqlWrk = "SELECT `Vendedor` FROM `vendedores` WHERE `id` = " . ew_AdjustSql($pedidos->idVendedor->CurrentValue) . "";
			$rswrk = $conn->Execute($sSqlWrk);
			if ($rswrk) {
				if (!$rswrk->EOF) {
					$pedidos->idVendedor->ViewValue = $rswrk->fields('Vendedor');
				}
				$rswrk->Close();
			} else {
				$pedidos->idVendedor->ViewValue = $pedidos->idVendedor->CurrentValue;
			}
		} else {
			$pedidos->idVendedor->ViewValue = NULL;
		}
		$pedidos->idVendedor->CssStyle = "";
		$pedidos->idVendedor->CssClass = "";
		$pedidos->idVendedor->ViewCustomAttributes = "";

		// Evento
		$pedidos->Evento->ViewValue = $pedidos->Evento->CurrentValue;
		$pedidos->Evento->CssStyle = "";
		$pedidos->Evento->CssClass = "";
		$pedidos->Evento->ViewCustomAttributes = "";

		// descripcion
		$pedidos->descripcion->ViewValue = $pedidos->descripcion->CurrentValue;
		if (!is_null($pedidos->descripcion->ViewValue)) $pedidos->descripcion->ViewValue = str_replace("\n", "<br>", $pedidos->descripcion->ViewValue); 
		$pedidos->descripcion->CssStyle = "";
		$pedidos->descripcion->CssClass = "";
		$pedidos->descripcion->ViewCustomAttributes = "";

		// pedido
		$pedidos->pedido->ViewValue = $pedidos->pedido->CurrentValue;
		if (!is_null($pedidos->pedido->ViewValue)) $pedidos->pedido->ViewValue = str_replace("\n", "<br>", $pedidos->pedido->ViewValue); 
		$pedidos->pedido->CssStyle = "";
		$pedidos->pedido->CssClass = "";
		$pedidos->pedido->ViewCustomAttributes = "";

		// extra
		$pedidos->extra->ViewValue = $pedidos->extra->CurrentValue;
		if (!is_null($pedidos->extra->ViewValue)) $pedidos->extra->ViewValue = str_replace("\n", "<br>", $pedidos->extra->ViewValue); 
		$pedidos->extra->CssStyle = "";
		$pedidos->extra->CssClass = "";
		$pedidos->extra->ViewCustomAttributes = "";

		// idPresupuesto
		$pedidos->idPresupuesto->ViewValue = $pedidos->idPresupuesto->CurrentValue;
		$pedidos->idPresupuesto->CssStyle = "";
		$pedidos->idPresupuesto->CssClass = "";
		$pedidos->idPresupuesto->ViewCustomAttributes = "";

		// id
		$pedidos->id->HrefValue = "";

		// Fecha
		$pedidos->Fecha->HrefValue = "";

		// estado
		$pedidos->estado->HrefValue = "";

		// ped_tarjeta
		$pedidos->ped_tarjeta->HrefValue = "";

		// total
		$pedidos->total->HrefValue = "";

		// nombre
		$pedidos->nombre->HrefValue = "";

		// apellido
		$pedidos->apellido->HrefValue = "";

		// telefono
		$pedidos->telefono->HrefValue = "";

		// idVendedor
		$pedidos->idVendedor->HrefValue = "";

		// Evento
		$pedidos->Evento->HrefValue = "";

		// descripcion
		$pedidos->descripcion->HrefValue = "";

		// pedido
		$pedidos->pedido->HrefValue = "";

		// extra
		$pedidos->extra->HrefValue = "";

		// idPresupuesto
		$pedidos->idPresupuesto->HrefValue = "";

		// sena
		$pedidos->sena->HrefValue = "";
	} elseif ($pedidos->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($pedidos->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($pedidos->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$pedidos->Row_Rendered();
}
?>
<?php

// Set up Starting Record parameters based on Pager Navigation
function SetUpStartRec() {
	global $nDisplayRecs, $nStartRec, $nTotalRecs, $nPageNo, $pedidos;
	if ($nDisplayRecs == 0) return;

	// Check for a START parameter
	if (@$_GET[EW_TABLE_START_REC] <> "") {
		$nStartRec = $_GET[EW_TABLE_START_REC];
		$pedidos->setStartRecordNumber($nStartRec);
	} elseif (@$_GET[EW_TABLE_PAGE_NO] <> "") {
		$nPageNo = $_GET[EW_TABLE_PAGE_NO];
		if (is_numeric($nPageNo)) {
			$nStartRec = ($nPageNo-1)*$nDisplayRecs+1;
			if ($nStartRec <= 0) {
				$nStartRec = 1;
			} elseif ($nStartRec >= intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1) {
				$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1;
			}
			$pedidos->setStartRecordNumber($nStartRec);
		} else {
			$nStartRec = $pedidos->getStartRecordNumber();
		}
	} else {
		$nStartRec = $pedidos->getStartRecordNumber();
	}

	// Check if correct start record counter
	if (!is_numeric($nStartRec) || $nStartRec == "") { // Avoid invalid start record counter
		$nStartRec = 1; // Reset start record counter
		$pedidos->setStartRecordNumber($nStartRec);
	} elseif (intval($nStartRec) > intval($nTotalRecs)) { // Avoid starting record > total records
		$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to last page first record
		$pedidos->setStartRecordNumber($nStartRec);
	} elseif (($nStartRec-1) % $nDisplayRecs <> 0) {
		$nStartRec = intval(($nStartRec-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to page boundary
		$pedidos->setStartRecordNumber($nStartRec);
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
