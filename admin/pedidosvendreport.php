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
?>
<?php @set_time_limit(999); // Set the maximum execution time (seconds)                               ?>
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


function Page_Terminate($url = "") {
	global $conn;

	 // Close Connection
	$conn->Close();

	// Go to url if specified
	if ($url <> "") {
		ob_end_clean();
		header("Location: $url");
	}
	exit();
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

//  $where = " WHERE pedidos.estado = 1 ";    // solo pagados 

if ($evt != '') {
    //	$where .= " AND Evento = '" . $evt_name[$evt] . "' "; // solo pagados
	$where .= " WHERE Evento = '" . mysql_real_escape_string($evt_name[$evt]) . "' ";
}
else
	$where .= " WHERE Evento = '' ";
	

if (!$Security->IsAdmin()) {
	//Usuarios no admin no puedan ver otros eventos
	$_evento = getSiteInfo('evento');
	$where .= " AND (Evento = '" . mysql_real_escape_string($_evento) . "') ";
}


$sql = "SELECT *, vendedores.id AS vid, pedidos.id AS pid FROM pedidos "
	. " JOIN vendedores ON (pedidos.idVendedor = vendedores.id) "
	. " $where ORDER BY Evento, vendedores.Vendedor, pid;";
$pedidos = $conn->Execute($sql);

$nombreVendedor = '';
$nombreEvento = '';

$lastEvt = '-1';
$lastVen = '-1';

$subTotalVen = 0;
$descVen = 0;
$totVen = 0;
$totPaid = 0;
$totPend = 0;

$qtyVen = 0;

$subTotalEvt = 0;
$descEvt = 0;
$totEvt = 0;
$totEvt_ef = 0;
$totEvt_tc = 0;
$qtyEvt = 0;

$totEvtPaid = 0;
$totEvtPend = 0;

$subTotalGT = 0;
$descGT = 0;
$totGT = 0;
$qtyGT = 0;

$gran_total_presup_qty = 0;
$gran_total_presup_senas = 0;
$gran_total_presup_senas_ef = 0;
$gran_total_presup_senas_tc = 0;

$totEvtSena = 0;
$grandTotEvtSena = 0;

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

    .cyes { 
		color: #3c763d;
		background-color: #dff0d8;
		border: 1px solid #d6e9c6;
		padding: 3px 16px; 
		font-size: 13px; 
		font-weight:bold;
		display: inline-block;
	}
    .tarj { 
		color: #8a6d3b;
		background-color: #fcf8e3;
		border: 1px solid #faebcc;
		padding: 3px 3px; 
		font-size: 13px; 
		font-weight:bold;
		display: inline-block;
		margin-left: 5px;
	}
    .cont { 
		color: #31708f;
		background-color: #d9edf7;
		border: 1px solid #bce8f1;
		padding: 3px 3px; 
		font-size: 13px; 
		font-weight:bold;
		display: inline-block;
		margin-left: 5px;
	}
    .cno { 
		color: #a94442;
		background-color: #f2dede;
		border: 1px solid #ebccd1;
		padding: 3px 8px; 
		font-size: 13px; 
		font-weight:bold;
		display: inline-block;
	}
	.text-center {
		text-align: center;
	}
	.text-right {
		text-align: right;
	}
</style>

<div style="text-align: center; margin: 0 auto; width: 60%; max-width: 900px">


    <table class="mytable" width="100%" border="0" cellpadding="3" cellspacing="0">
	<?
	while (!$pedidos->EOF) {

	    //  EVENTOS TITULO	------------------------------------------------
	    if ($pedidos->fields['Evento'] != $lastEvt) {
		$lastEvt = $pedidos->fields['Evento'];
		$lastVen = '-1';
		//
		$subTotalEvt = 0;
		$descEvt = 0;
		$totEvt = 0;
		$qtyEvt = 0;
		$totEvtPaid = 0;
		$totEvtPend = 0;
		
		$totEvtSena = 0;
		
		?>
		<tr>
		    <td colspan="7" class="ewGroupName ewGroupName_1">
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
		$totSena = 0;
		$totPaid = $totPend = 0;

		$nombreVendedor = $pedidos->fields['Vendedor'];
		?>
		<tr>
		    <td colspan="7" class="ewGroupField ewGroupField_2">
			Vendedor <?= $pedidos->fields['Vendedor'] ?>
		    </td>
		</tr>
		<tr>
		    <td class="ewGroupHeader">Número</td>
		    <td class="ewGroupHeader text-right">Subtotal</td>
		    <td class="ewGroupHeader text-right">Descuento</td>
		    <td class="ewGroupHeader text-right">Subtotal 2</td>
		    <td class="ewGroupHeader text-right">Seña</td>
		    <td class="ewGroupHeader text-right">Saldo Total</td>
		    <td class="ewGroupHeader text-center">Estado</td>
		</tr>
		<?
	    }
	    //  PEDIDOS	--------------------------------------------------------
	    $subtot = $pedidos->fields['total'];
	    $desc = $pedidos->fields['Descuento'];
	    $tot = $subtot - $desc;
	    $estado = $pedidos->fields['estado'] == '1' ? '<span class="cyes">Pagado</span>' : '<span class="cno">Pendiente</span>';
	    $estado .= $pedidos->fields['ped_tarjeta'] == '1' ? ' <span class="tarj">TC</span>' : '<span class="cont">EF</span>';

	    if ($pedidos->fields['estado'] == '1') {
			$totEvtPaid += $tot - $sena;
			$totPaid += $tot - $sena;
			$totEvt_ef += $pedidos->fields['ped_tarjeta'] != '1' ? $tot : 0;
			$totEvt_tc += $pedidos->fields['ped_tarjeta'] == '1' ? $tot : 0;
	    } else {
			$totEvtPend += $tot - $sena;
			$totPend += $tot - $sena;
	    }

	    $sena = $pedidos->fields['sena'];


		$presu_link = '';
		if ($pedidos->fields['idPresupuesto'])
			$presu_link = '&nbsp;&nbsp;(<a target="_blank" href="..//ajax/pdf_presu?id='.$pedidos->fields['idPresupuesto'].'">pres: '.$pedidos->fields['idPresupuesto'].'</a>)';

	    //
	    echo '<tr>';
	    echo '<td><a target="_blank" href="pedidosview.php?id=' . $pedidos->fields['pid'] . '">'
	    . '' . $pedidos->fields['pid'] . '</a>'.$presu_link.'</td>';
	    echo '<td class="text-right">' . fn($subtot) . '</td>';
	    echo '<td class="text-right">' . fn($desc) . '</td>';
	    echo '<td class="text-right">' . fn($tot) . '</td>';
	    echo '<td class="text-right">' . fn($sena) . '</td>';
	    echo '<td class="text-right"><b>' . fn($tot - $sena) . '</b></td>';
	    echo '<td class="text-center" style="white-space:nowrap">' . $estado . '</td>';
	    echo '</tr>';
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
	    $totSena += $sena;

	    $totEvtSena += $sena;
	    $grandTotEvtSena += $sena;


	    $qtyGT++;

	    $pedidos->MoveNext();

	    //  TOTAL VENDEDORES	----------------------------------------
	    if ($pedidos->fields['vid'] != $lastVen) {?>
			<tr>
				<td colspan="7" class="ewGroupSummary">
				<?= $nombreVendedor ?>: <?= $qtyVen ?> pedidos
				</td>
			</tr>
			<tr>
				<td class="ewGroupAggregate">Totales:</td>
				<td class="ewGroupAggregate_2nd text-right"><?= fn($subTotalVen) ?></td>
				<td class="ewGroupAggregate_2nd text-right"><?= fn($descVen) ?></td>
				<td class="ewGroupAggregate_2nd text-right"><?= fn($totVen) ?></td>
				<td class="ewGroupAggregate_2nd text-right"><?= fn($totSena) ?></td>
				<td class="ewGroupAggregate text-right"><?= fn($totVen - $totSena) ?></td>
				<td class="ewGroupAggregate"></td>
			</tr>
			<tr>
				<td class="ewGroupAggregate">Total Cobrado:</td>
				<td class="ewGroupAggregate_2nd text-right"><?= fn($totPaid) ?></td>
				<td class="ewGroupAggregate">Total Pendiente:</td>
				<td class="ewGroupAggregate_2nd text-right"><?= fn($totPend) ?></td>
				<td class="ewGroupAggregate">&nbsp;</td>
				<td class="ewGroupAggregate">&nbsp;</td>
				<td class="ewGroupAggregate">&nbsp;</td>
			</tr>
			<tr><td colspan="7">&nbsp;</td></tr>
			<?
			//  PRESUPUESTOS CON SEÑAS COBRADAS estado=1  ------------------
			$conn = ew_Connect();
			$vid = $pedidos->fields['vid'];
			$sql = 'SELECT * FROM presupuestos WHERE estado = 1 AND '
				. 'idVendedor = ' . $vid . ' AND evento = "' . mysql_real_escape_string($lastEvt) . '" '
				. 'AND sena > 0 '
				//. 'AND id NOT IN (SELECT idPresupuesto FROM pedidos WHERE idPresupuesto > 0) '
				;
			$presus = $conn->Execute($sql);

			//die ($sql);


			if ($presus) {?>

				<tr>
					<td colspan="7"><b>Presupuestos con seña cobrada</b></td>
				</tr>
				<tr>
					<td class="ewGroupHeader"></td>
					<td class="ewGroupHeader"></td>
					<td class="ewGroupHeader"></td>
					<td class="ewGroupHeader"></td>
					<td class="ewGroupHeader">Número</td>
					<td class="ewGroupHeader text-right">Seña</td>
					<td class="ewGroupHeader"></td>
				</tr>

				<?
				$psub = $pdesc = $ptot = $psena = 0;

				while (!$presus->EOF) {
					$gran_total_presup_qty++;
					$gran_total_presup_senas += $presus->fields['sena'];
					$gran_total_presup_senas_tc += $presus->fields['presu_tarjeta'] == '1' ? $presus->fields['sena'] : 0;
					$gran_total_presup_senas_ef += $presus->fields['presu_tarjeta'] != '1' ? $presus->fields['sena'] : 0;

				    $pres_estado = $presus->fields['estado'] == '1' ? '<span class="cyes">Pagado</span>' : '<span class="cno">Pendiente</span>';
				    $pres_estado .= $presus->fields['presu_tarjeta'] == '1' ? ' <span class="tarj">TC</span>' : '<span class="cont">EF</span>';
					
					$psub += $presus->fields['subtotal'];
					$pdesc += $presus->fields['descuento'];
					$ptot += $presus->fields['total'];
					$psena += $presus->fields['sena'];
					?>
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td><a target="_blank" href="..//ajax/pdf_presu?id=<?= $presus->fields['id'] ?>"><?= $presus->fields['id'] ?></a></td>
						<td class="text-right"><?= fn($presus->fields['sena']) ?></td>
						<td><?= $pres_estado ?></td>
					</tr>
					<?
					//------------------
					$presus->moveNext();
				}
				?>
				<tr>
					<td class="ewGroupSummary ewGroupAggregate">Total:</td>
					<td class="ewGroupSummary ewGroupAggregate"></td>
					<td class="ewGroupSummary ewGroupAggregate"></td>
					<td class="ewGroupSummary ewGroupAggregate"></td>
					<td class="ewGroupSummary ewGroupAggregate"></td>
					<td class="ewGroupSummary ewGroupAggregate text-right"><?= fn($psena) ?></td>
					<td class="ewGroupSummary ewGroupAggregate"></td>
				</tr>
				<tr><td colspan="7">&nbsp;</td></tr>
				<?
			}
	    }

	    //  TOTAL EVENTOS	------------------------------------------------
	    if ($pedidos->fields['Evento'] != $lastEvt) {?>
			<tr>
				<td colspan="7" class="ewGroupSummary" style="background-color: #333; color: #fff; font-size: 18px; font-weight:bold;">
				TOTAL Evento <?= $lastEvt ?> - <?= $qtyEvt ?> pedidos - <?= $gran_total_presup_qty ?> presupuestos
				</td>
			</tr>
			<tr>
				<td class="ewGroupHeader"></td>
				<td class="ewGroupHeader text-right">Subtotal</td>
				<td class="ewGroupHeader text-right">Descuento</td>
				<td class="ewGroupHeader text-right">Subtotal 2</td>
				<td class="ewGroupHeader text-right">Seña</td>
				<td class="ewGroupHeader text-right">Saldo Total</td>
				<td class="ewGroupHeader text-center"></td>
			</tr>
			<tr>
				<td class="ewGroupAggregate">Totales:</td>
				<td class="ewGroupAggregate_2nd text-right"><?= fn($subTotalEvt) ?></td>
				<td class="ewGroupAggregate_2nd text-right"><?= fn($descEvt) ?></td>
				<td class="ewGroupAggregate text-right"><?= fn($totEvt) ?></td>
				<td class="ewGroupAggregate text-right"><?= fn($totEvtSena) ?></td>
				<td class="ewGroupAggregate text-right"><?= fn($totEvt - $totEvtSena) ?></td>
				<td class="ewGroupAggregate">&nbsp;</td>
			</tr>
			<tr><td colspan="7" style="border-bottom: 1px solid #000;">&nbsp;</td></tr>
			<tr>
				<td class="ewGroupAggregate">Total pedidos pendiente:</td>
				<td class="ewGroupAggregate_2nd text-right"><?= fn($totEvtPend) ?></td>
				<td class="ewGroupAggregate">&nbsp;</td>
				<td class="ewGroupAggregate">&nbsp;</td>
				<td class="ewGroupAggregate text-right">Efectivo</td>
				<td class="ewGroupAggregate text-right">Tarjeta</td>
				<td class="ewGroupAggregate">&nbsp;</td>
			</tr>
			<tr style="font-size: 20px; font-weight:bold;">
				<td class="ewGroupAggregate">Total cobrado pedidos:</td>
				<td class="ewGroupAggregate_2nd text-right"><?= fn($totEvtPaid) ?></td>
				<td class="ewGroupAggregate">&nbsp;</td>
				<td class="ewGroupAggregate">&nbsp;</td>
				<td class="ewGroupAggregate_2nd text-right"><?= fn($totEvt_ef) ?></td>
				<td class="ewGroupAggregate_2nd text-right"><?= fn($totEvt_tc) ?></td>
				<td class="ewGroupAggregate">&nbsp;</td>
			</tr>
			<tr style="font-size: 20px; font-weight:bold;">
				<td class="ewGroupAggregate">Total seña presupuestos:</td>
				<td class="ewGroupAggregate_2nd text-right"><?= fn($gran_total_presup_senas) ?></td>
				<td class="ewGroupAggregate">&nbsp;</td>
				<td class="ewGroupAggregate">&nbsp;</td>
				<td class="ewGroupAggregate_2nd text-right"><?= fn($gran_total_presup_senas_ef) ?></td>
				<td class="ewGroupAggregate_2nd text-right"><?= fn($gran_total_presup_senas_tc) ?></td>
				<td class="ewGroupAggregate">&nbsp;</td>
			</tr>
			<tr style="font-size: 24px; font-weight:bold;">
				<td style="background-color: #333; color: #fff; " class="ewGroupSummary">Total caja:</td>
				<td style="background-color: #333; color: #fff; " class="ewGroupSummary text-right"><?= fn($totEvtPaid + $gran_total_presup_senas) ?></td>
				<td style="background-color: #333; color: #fff; " class="ewGroupSummary text-right"></td>
				<td style="background-color: #333; color: #fff; " class="ewGroupSummary text-right"></td>
				<td style="background-color: #333; color: #fff; " class="ewGroupSummary text-right"><?= fn($totEvt_ef + $gran_total_presup_senas_ef) ?></td>
				<td style="background-color: #333; color: #fff; " class="ewGroupSummary text-right"><?= fn($totEvt_tc + $gran_total_presup_senas_tc) ?></td>
				<td style="background-color: #333; color: #fff; " class="ewGroupSummary text-right"></td>
			</tr>
			<tr>
				<td colspan="7" style="border-top: 1px solid #000;">&nbsp;</td>
			</tr>
		<?
	    }
	}

//  GRAND TOTAL EVENTOS	------------------------------------------------
	?>
	<!--
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
	    <td class="ewGroupAggregate">Total seña:</td>
	    <td class="ewGroupAggregate_2nd"><?= fn($grandTotEvtSena) ?></td>
	    <td class="ewGroupAggregate">&nbsp;</td>
	    <td class="ewGroupAggregate">&nbsp;</td>
	    <td class="ewGroupAggregate">&nbsp;</td>
	    <td class="ewGroupAggregate">&nbsp;</td>
	</tr>
	<tr>
	    <td colspan="6">&nbsp;</td>
	</tr>
	-->
    </table>
</div>
<?php include "footer.php" ?>