<?php
define("EW_PAGE_ID", "report", TRUE); // Page ID
?>
<?php
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "userfn50.php" ?>
<?php include "usuariosinfo.php" ?>
<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // Always modified
header("Cache-Control: private, no-store, no-cache, must-revalidate"); // HTTP/1.1 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0

include '../sypfw/includes/SypDatabase.php';
include '../sypfw/includes/SypTable.php';

include '../sypfw/model/formato_imagen.php';
include '../sypfw/model/formato_coreo.php';
include '../sypfw/model/formato_video.php';

$DB = new SypDatabase(DB_USER, DB_PASS, DB_NAME);

?>
<?php @set_time_limit(999); // Set the maximum execution time (seconds)                 ?>
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
$Security->LoadCurrentUserLevel('pedidosvend');
if (!$Security->IsLoggedIn()) {
    $Security->SaveLastUrl();
    Page_Terminate("login.php");
}
if (!$Security->CanReport()) {
    $Security->SaveLastUrl();
    Page_Terminate("login.php");
}

$evt_name = array();
?>
<?php include "header.php" ?>
<p><span class="phpmaker">Pedidos por eventos y vendedor

	<form method="post">
	    Evento <select name="evt" style="font-size:14px; padding:3px 5px;">
		<option value=""></option>
		<?
		$_where_sel = '';
		if (!$Security->IsAdmin()) {
			//Usuarios no admin no puedan ver otros eventos
			$_evento = getSiteInfo('evento');
			$_where_sel .= "WHERE (Evento = '" . mysql_real_escape_string($_evento) . "')";
		}
		
		$_selevt = isset($_POST['evt']) ? $_POST['evt'] : "";
		$evtrs = mysql_query("select distinct Evento from pedidos {$_where_sel} order by Evento");
		while ($e = mysql_fetch_array($evtrs)) {
		    $tmp = trim(htmlentities($e['Evento']));
		    $evt_name[$e['Evento']] = $e['Evento'];
		    if (empty($tmp) || is_null($tmp))
			continue;
		    ?><option value="<?= htmlentities($e['Evento']) ?>" <?= $e['Evento'] == $_selevt ? "selected" : "" ?>><?= htmlentities($e['Evento']) ?></option><?
		}
		?>
	    </select> <input type="Submit" value="Aplicar" id="Submit" name="Submit">
	</form>
    </span>
</p>
<?

function d($txt) {
    echo '<pre>' . print_r($txt, true) . '</pre>';
}

$evt = $_POST['evt'];
if ($evt != '') {
    $evt = " WHERE Evento = '" . $evt_name[$evt] . "' ";
}
$sql = "SELECT *, vendedores.id AS vid, pedidos.id AS pid FROM pedidos "
	. " JOIN vendedores ON (pedidos.idVendedor = vendedores.id) "
	. " $evt ORDER BY Evento, vendedores.Vendedor, pid;";
$pedidos = $conn->Execute($sql);

$nombreVendedor = '';
$nombreEvento = '';

$lastEvt = '-1';
$lastVen = '-1';

$subTotalVen = 0;
$descVen = 0;
$totVen = 0;
$qtyVen = 0;

$subTotalEvt = 0;
$descEvt = 0;
$totEvt = 0;
$qtyEvt = 0;

$subTotalGT = 0;
$descGT = 0;
$totGT = 0;
$qtyGT = 0;

function fn($n) {
    return '$' . number_format($n, 2, ',', '.');
}
?>

<style>

    table.mytable {
	font-size: 14px;
	border: 0;
    }
    table.mytable a{
	text-decoration: none;
    }
    
    .lined {
	border-bottom: 1px solid #B5B5B5;
    }

    .cyes { background-color: green; color:white; padding: 2px 16px; }	
    .cno { background-color: red; color:white;padding: 2px 8px; }


</style>

<div style="text-align: center; margin: 0 auto; width: 45%;">


    <table class="mytable" width="100%" border="0" cellpadding="3" cellspacing="0">
	<?
	while (!$pedidos->EOF) {

	    //  EVENTOS	--------------------------------------------------------
	    if ($pedidos->fields['Evento'] != $lastEvt) {
		$lastEvt = $pedidos->fields['Evento'];
		$lastVen = '-1';
		//
		$subTotalEvt = 0;
		$descEvt = 0;
		$totEvt = 0;
		$qtyEvt = 0;
		?>
		<tr>
		    <td colspan="6" class="ewGroupName ewGroupName_1">
			Evento <?= $pedidos->fields['Evento'] ?>
		    </td>
		</tr>
		<?
	    }

	    //  VENDEDORES	------------------------------------------------
	    if ($pedidos->fields['vid'] != $lastVen) {
		$lastVen = $pedidos->fields['vid'];
		$qtyVen = 0;
		$subTotalVen = 0;
		$descVen = 0;
		$totVen = 0;
		$nombreVendedor = $pedidos->fields['Vendedor'];
		?>
		<tr>
		    <td colspan="6" class="ewGroupField ewGroupField_2">
			Vendedor <?= $pedidos->fields['Vendedor'] ?>
		    </td>
		</tr>
		<tr>
		    <td class="ewGroupHeader">&nbsp;</td>
		    <td class="ewGroupHeader">Número</td>
		    <td class="ewGroupHeader">Subtotal</td>
		    <td class="ewGroupHeader">Descuento</td>
		    <td class="ewGroupHeader">Total</td>
		    <td class="ewGroupHeader">Estado</td>
		</tr>
		<?
	    }
	    //  PEDIDOS	--------------------------------------------------------
	    $subtot = $pedidos->fields['total'];
	    $desc = $pedidos->fields['Descuento'];
	    $tot = $subtot - $desc;
	    $estado = $pedidos->fields['estado'] == '1' ? '<span class="cyes">Pagado</span>' : '<span class="cno">Pendiente</span>';
	    echo '<tr>';
	    echo '<td></td>';
	    echo '<td><a target="_blank" href="pedidosview.php?id=' . $pedidos->fields['pid'] . '">'
	    . '' . $pedidos->fields['pid'] . '</a></td>';
	    echo '<td>' . fn($subtot) . '</td>';
	    echo '<td>' . fn($desc) . '</td>';
	    echo '<td><b>' . fn($tot) . '</b></td>';
	    echo '<td>' . $estado . '</td>';

	    echo '</tr>';

	    $imgs = 0;
	    $vids = 0;
	    $coreos = 0;
	    $order = unserialize($pedidos->fields['pedido']);
	    //	get data to generate order & get order total
	    foreach ($order as $item) {
		foreach ($item['formats'] as $f) {

		    if ($item['type'] == 'vid') {
			$format = new formato_video();
		    } elseif ($item['type'] == 'coreo') {
			$format = new formato_coreo();
		    } else {
			$format = new formato_imagen();
		    }

		    $format->get($f['format_id']);
		    $sub = ((int) $f['qty'] * $format->precio);		    
		    
		    if ($item['type'] == 'vid') {
			$vids += $sub;
		    } elseif ($item['type'] == 'coreo') {
			$coreos += $sub;
		    } else {
			$imgs += $sub;
		    }		    
		}
	    }
	    
	    //	----------------------------------------------------------------
	    //	----------------------------------------------------------------
	    echo '<tr><td></td><td></td><td></td>';
	    echo '<td>Imágenes:</td>';
	    echo '<td>'.fn($imgs).'</td>';
	    echo '</tr>';
	    
	    echo '<tr><td></td><td></td><td></td>';
	    echo '<td>Videos:</td>';
	    echo '<td>'.fn($vids).'</td>';
	    echo '</tr>';
	    
	    echo '<tr><td></td><td class="lined"></td><td class="lined"></td>';
	    echo '<td class="lined">Coreos:</td>';
	    echo '<td class="lined">'.fn($coreos).'</td>';
	    echo '<td class="lined"></td></tr>';
	    
	    //	debug	--------------------------------------------------------
	    //	echo '<tr><td><pre>' . print_r($ser, true) . '</pre></td></tr>';
	    



	    $qtyVen++;
	    $qtyEvt++;

	    $subTotalVen += $subtot;
	    $descVen += $desc;
	    $totVen += $tot;
	    //
	    $subTotalEvt += $subtot;
	    $descEvt += $desc;
	    $totEvt += $tot;
	    //
	    $subTotalGT += $subtot;
	    $descGT += $desc;
	    $totGT += $tot;
	    $qtyGT++;

	    //	MOVE NEXT   ----------------------------------------------------
	    $pedidos->MoveNext();

	    //  TOTAL VENDEDORES	----------------------------------------
	    if ($pedidos->fields['vid'] != $lastVen) {
		?>
		<tr>
		    <td colspan="6" class="ewGroupSummary">
			<?= $nombreVendedor ?>: <?= $qtyVen ?> pedidos
		    </td>
		</tr>
		<tr>
		    <td class="ewGroupAggregate">Totales:</td>
		    <td class="ewGroupAggregate">&nbsp;</td>
		    <td class="ewGroupAggregate_2nd"><?= fn($subTotalVen) ?></td>
		    <td class="ewGroupAggregate_2nd"><?= fn($descVen) ?></td>
		    <td class="ewGroupAggregate"><?= fn($totVen) ?></td>
		    <td class="ewGroupAggregate">&nbsp;</td>
		</tr>
		<tr>
		    <td colspan="6">&nbsp;</td>
		</tr>
		<?
	    }

	    //  TOTAL EVENTOS	------------------------------------------------
	    if ($pedidos->fields['Evento'] != $lastEvt) {
		?>
		<tr>
		    <td colspan="6" class="ewGroupSummary">
			Evento: <?= $lastEvt ?> - <?= $qtyEvt ?> pedidos
		    </td>
		</tr>
		<tr>
		    <td class="ewGroupAggregate">Totales:</td>
		    <td class="ewGroupAggregate">&nbsp;</td>
		    <td class="ewGroupAggregate_2nd"><?= fn($subTotalEvt) ?></td>
		    <td class="ewGroupAggregate_2nd"><?= fn($descEvt) ?></td>
		    <td class="ewGroupAggregate"><?= fn($totEvt) ?></td>
		    <td class="ewGroupAggregate">&nbsp;</td>
		</tr>
		<tr>
		    <td colspan="6">&nbsp;</td>
		</tr>
		<?
	    }
	}

	//  TOTAL EVENTOS	------------------------------------------------
	?>
	<tr>
	    <td colspan="6" class="ewGroupSummary">
		GRAN TOTAL: <?= $qtyGT ?> pedidos
	    </td>
	</tr>
	<tr>
	    <td class="ewGroupAggregate">Totales:</td>
	    <td class="ewGroupAggregate">&nbsp;</td>
	    <td class="ewGroupAggregate_2nd"><?= fn($subTotalGT) ?></td>
	    <td class="ewGroupAggregate_2nd"><?= fn($descGT) ?></td>
	    <td class="ewGroupAggregate"><?= fn($totGT) ?></td>
	    <td class="ewGroupAggregate">&nbsp;</td>
	</tr>
	<tr>
	    <td colspan="6">&nbsp;</td>
	</tr>

    </table>
</div>
<?php include "footer.php" ?>