<?php
$where = " WHERE pedidos.estado = 1 ";
$where .= " AND Evento = '" . $evt_name[$evt] . "' ";

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

$data = array();
$vendedores = array();

$escala = json_decode(getSiteInfo('comisionesevento'));

function getValueBetweenRange($total) {
    global $escala;

    for ($i = 0; $i < count($escala->dd); $i++) {
	$dd = $escala->dd[$i];
	$dh = $escala->dh[$i];
	//
	if ($total >= $dd && $total <= $dh) {
	    return $escala->dm[$i];
	}
    }

    return 0;
}

//  PRECALCULAR
while (!$pedidos->EOF) {

    //  EVENTOS	--------------------------------------------------------
    if ($pedidos->fields['Evento'] != $lastEvt) {
	$lastEvt = $pedidos->fields['Evento'];
	$lastVen = '-1';
	$subTotalEvt = 0;
	$descEvt = 0;
	$totEvt = 0;
	$qtyEvt = 0;
    }

    //  VENDEDORES	------------------------------------------------
    if ($pedidos->fields['vid'] != $lastVen) {
	$lastVen = $pedidos->fields['vid'];
	$qtyVen = 0;
	$subTotalVen = 0;
	$descVen = 0;
	$totVen = 0;
	$nombreVendedor = $pedidos->fields['Vendedor'];
    }
    //  PEDIDOS	--------------------------------------------------------
    $subtot = $pedidos->fields['total'];
    $desc = $pedidos->fields['Descuento'];
    $tot = $subtot - $desc;
    $estado = $pedidos->fields['estado'] == '1' ? '<span class="cyes">Pagado</span>' : '<span class="cno">Pendiente</span>';

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

    $pedidos->MoveNext();
    // AGRUPAR CALCULOS	--------------------------------------------------	
    //  TOTAL VENDEDORES	----------------------------------------
    if ($pedidos->fields['vid'] != $lastVen) {


	if (!is_array($vendedores[$nombreVendedor])) {
	    $vendedores[$nombreVendedor] = array(
		'name' => $nombreVendedor,
		'qty_pedidos' => 0,
		'subtotal' => 0,
		'descuento' => 0,
		'total' => 0,
	    );
	}

	$lv = $vendedores[$nombreVendedor];
	$lv['qty_pedidos'] += $qtyVen;
	$lv['subtotal'] += $subTotalVen;
	$lv['descuento'] += $descVen;
	$lv['total'] += $totVen;
	$vendedores[$nombreVendedor] = $lv;
    }

    //  TOTAL EVENTOS	------------------------------------------------
    if ($pedidos->fields['Evento'] != $lastEvt) {


	$comision_percent = getValueBetweenRange($totEvt);
	$comision = $totEvt * $comision_percent / 100;
	$data[] = array(
	    'name' => $lastEvt,
	    'qty_pedidos' => $qtyEvt,
	    'subtotal' => $subTotalEvt,
	    'descuento' => $descEvt,
	    'total' => $totEvt,
	    'comision_percent' => $comision_percent,
	    'comision' => $comision,
	    'vendedores' => $vendedores,
	);

	$vendedores = array();
    }
} //	END WHILE   ------------------------

/* */
//  GRAN TOTAL ------------------------------------------------
?>
<style>

    table.mytable {
	font-size: 14px;
	border: 0;
    }
    table.mytable a{
	text-decoration: none;
    }

    .tc {
	text-align: center;
    }

    .cyes { background-color: green; color:white; padding: 2px 16px; }	
    .cno { background-color: red; color:white;padding: 2px 8px; }


</style>
<div style="text-align: center; margin: 0 auto; width: 45%;">
    <table class="mytable" width="100%" border="0" cellpadding="3" cellspacing="0">


	<? foreach ($data as $evt) { ?>

    	<tr>
    	    <td colspan="3" class="ewGroupName ewGroupName_1">
    		Evento <?= $evt['name'] ?>
    	    </td>

    	    <td class="ewGroupName_1 tc">Total Vendido</td>
    	    <td class="ewGroupName_1 tc">% Comisión</td>
    	    <td class="ewGroupName_1 tc">Comisión</td>

    	</tr>
    	<tr>
    	    <td colspan="3"><br />
    		<b>Pedidos cobrados: </b><?= $evt['qty_pedidos'] ?><br /><br />
    		<b>Subtotal: </b>$<?= MaxterHlp::fn($evt['subtotal']) ?><br /><br />
    		<b>Descuento: </b>$<?= MaxterHlp::fn($evt['descuento']) ?>
    	    </td>

    	    <td class="ewGroupName ewGroupName_1 tc">
    		$<?= MaxterHlp::fn($evt['total']) ?>
    	    </td>
    	    <td class="ewGroupName ewGroupName_1 tc">
		    <?= $evt['comision_percent'] ?>%
    	    </td>
    	    <td class="ewGroupName ewGroupName_1 tc">
    		$<?= MaxterHlp::fn($evt['comision']) ?>
    	    </td>
    	</tr>
    	<tr>
    	    <td colspan="6">&nbsp;</td>
    	</tr>

    	<tr>
    	    <td class="ewGroupHeader">Vendedor</td>
    	    <td class="ewGroupHeader">Pedidos</td>
    	    <td class="ewGroupHeader">Subtotal</td>
    	    <td class="ewGroupHeader">Descuento</td>
    	    <td class="ewGroupHeader">Total</td>
    	    <td class="ewGroupHeader">Comisión</td>
    	</tr>
	    <?
	    //  VENDEDORES	--------------------------------------------------------
	    foreach ($evt['vendedores'] as $v) {
		?>

		<tr>
		    <td><?= $v['name'] ?></td>
		    <td><?= $v['qty_pedidos'] ?></td>
		    <td>$<?= MaxterHlp::fn($v['subtotal']) ?></td>
		    <td>$<?= MaxterHlp::fn($v['descuento']) ?></td>
		    <td>$<?= MaxterHlp::fn($v['total']) ?></td>
		    <td><b>$<?= MaxterHlp::fn($v['total'] * $evt['comision_percent'] / 100) ?></b></td>
		</tr>


	    <? } ?>


    	<tr>
    	    <td colspan="6">&nbsp;</td>
    	</tr>
	<? } ?>
	<tr>
	    <td colspan="6">&nbsp;</td>
	</tr>

    </table>
</div>
