<?php
//
error_reporting(E_ALL);

include '../sypfw/includes/SypDatabase.php';
include '../sypfw/includes/SypTable.php';
//
include '../sypfw/model/formato_coreo.php';
include '../sypfw/model/formato_imagen.php';
include '../sypfw/model/formato_video.php';
include '../sypfw/model/presupuestos.php';
$DB = new SypDatabase(DB_USER, DB_PASS, DB_NAME, '127.0.0.1');

//  ESCALAS	----------------------------------------------------------------
$escalaaccesorios = json_decode(getSiteInfo('accesorios_escala_descuento'));

$escalavfotoind = json_decode(getSiteInfo('escalavfotoind'));
$escalavvideoind = json_decode(getSiteInfo('escalavvideoind'));

$escalavfotopresu = json_decode(getSiteInfo('escalavfotopresu'));
$escalavvideopresu = json_decode(getSiteInfo('escalavvideopresu'));

function getValueBetweenRange($total, $escala) {
	if (!isset($escala->dd)) return 0;
	
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

function getVideoComision($total) {
    global $escalavvideoind;
    return getValueBetweenRange($total, $escalavvideoind);
}

function getAccesorioComision($total) {
    global $escalaaccesorios;
    return getValueBetweenRange($total, $escalaaccesorios);
}

function getFotoComision($total) {
    global $escalavfotoind;
    return getValueBetweenRange($total, $escalavfotoind);
}

function getPresuComFoto($total) {
    global $escalavfotopresu;
    return getValueBetweenRange($total, $escalavfotopresu);
}

function getPresuComVideo($total) {
    global $escalavvideopresu;
    return getValueBetweenRange($total, $escalavvideopresu);
}

//  ----------------------------------------------------------------------------
//  TODOS LOS PEDIDOS INDIVIDUALES COBRADOS	--------------------------------
//  ----------------------------------------------------------------------------

//d($evt_name);

$event_name = $evt_name[$evt];

//d($event_name);


$where = " WHERE pedidos.estado = 1 ";
$where .= " AND Evento = '" . $event_name . "' ";
$where .= " AND (`idPresupuesto` = '' OR idPresupuesto IS NULL) ";

$sql = "SELECT *, vendedores.id AS vid, pedidos.id AS pid FROM pedidos "
	. " JOIN vendedores ON (pedidos.idVendedor = vendedores.id) "
	. " $where ORDER BY vendedores.Vendedor, pid;";

$pedidos = $conn->Execute($sql);

//  d($sql);

$ind_vend = array();
$ind_subtotal = 0;
$ind_desc = 0;

function defSeller() {
    return array(
		'descuento' => 0,
		'acc_subtotal' => 0,
		'img_subtotal' => 0,
		'vid_subtotal' => 0,
		'coreo_subtotal' => 0,
		'subtotal' => 0,
		'foto_subtotal' => 0,
		'foto_desc' => 0,
		'vid_desc' => 0,
		'acc_total' => 0,
		'foto_total' => 0,
		'vid_total' => 0,
		'per_com_acc' => 0,
		'per_com_foto' => 0,
		'per_com_vid' => 0,
		'total' => 0,
		'com_acc' => 0,
		'com_foto' => 0,
		'com_vid' => 0,
		'per_tot' => 0,
		'com_ind_total' => 0,
    );
}

while (!$pedidos->EOF) {
    $row = $pedidos->fields;

    //$evt_name = $pedidos->fields['Evento'];
    //	venta individual total 
    $ind_subtotal += $row['total'];
    $ind_desc += $row['Descuento'];

    //	venta individual por vendedor
    $ven_name = $row['Vendedor'];
    //------------------------------------------------------
    if (!isset($ind_vend[$ven_name])) {
	$ind_vend[$ven_name] = defSeller();
	$ind_vend[$ven_name]['name'] = $ven_name;

	/* $ind_vend[$ven_name] = array(
	  'name' => $ven_name,
	  'descuento' => 0,
	  'img_subtotal' => 0,
	  'vid_subtotal' => 0,
	  'coreo_subtotal' => 0,
	  'subtotal' => 0,
	  ); */
    }

    $ped = unserialize($row['pedido']);

    //	---------------------------------------------------------------------
    $tmp = $ind_vend[$ven_name];
    $tmp['subtotal'] += $row['total'];
    $tmp['descuento'] += $row['Descuento'];

    //d($row);

	$deb_total = 0;
	
	//fotolibro suma directo a subtotal de foto, porque el costo por foto difiere del costo del formato
	if (!empty($row['idFotolibro'])) {
		$tmp['img_subtotal'] += $row['total'];
		$deb_total += $row['total'];
	}
    else foreach ($ped as $p) {

	//d($row['pid']."-".$ven_name."-".$tmp['img_total'] ."-". $fmt->precio ."-". $qty);

		if ($p['type'] == 'coreo') {
			foreach ($p['formats'] as $f) {
			$fmt = new formato_coreo();
			$fmt->get($f['format_id']);
			//
			$qty = floatval($f['qty']);
			$tmp['coreo_subtotal'] += ($fmt->Precio * $qty);
			$deb_total += ($fmt->Precio * $qty);
			}
		} else if ($p['type'] == 'acc') {
			$qty = floatval($p['qty']);
			$tmp['acc_subtotal'] += ($p['amt'] * $qty);
			$deb_total += ($p['amt'] * $qty);
		} else if ($p['type'] == 'img') {
			if (isset($p['formats'])) {
				foreach ($p['formats'] as $f) {
					$fmt = new formato_imagen();
					$fmt->get($f['format_id']);
					//
					$qty = floatval($f['qty']);
					$tmp['img_subtotal'] += ($fmt->precio * $qty);
					$deb_total += ($fmt->precio * $qty);
				}
			}
		} else if ($p['type'] == 'vid') {
			foreach ($p['formats'] as $f) {
				$fmt = new formato_video();
				$fmt->get($f['format_id']);
				//
				$qty = floatval($f['qty']);
				$tmp['vid_subtotal'] += ($fmt->precio * $qty);
				$deb_total += ($fmt->precio * $qty);
			}
		}
    }
if ($deb_total != $row['total']) {
	?> <div style="color: #a00000; font-size: 12px; margin: 10px 0;">
		Pedido nro <?= $row['pid'] ?> (<?= $row['Vendedor'] ?>) no coinciden los totales: Calculado para comisionar $<?= $deb_total ?> - Total cobrado: $<?= $row['total'] ?> 
	</div> <?
}


    //	---------------------------------------------
    $ind_vend[$ven_name] = $tmp;
    $pedidos->MoveNext();
}

$ind_total = ($ind_subtotal - $ind_desc);

//  calculate individual comisions for each seller
foreach ($ind_vend as $v) {

    //	sumo coreo y fotos subtotales
    $v['foto_subtotal'] = $v['img_subtotal'] + $v['coreo_subtotal'];

    //	si vendio fotos Y videos el descuento se hace miti-miti
    $v['foto_desc'] = $v['vid_desc'] = 0;
    if ($v['vid_subtotal'] > 0 && $v['foto_subtotal'] > 0) {
		$v['foto_desc'] = $v['vid_desc'] = $v['descuento'] / 2;
    } else if ($v['vid_subtotal'] > 0) {
		$v['vid_desc'] = $v['descuento'];
    } else {
		$v['foto_desc'] = $v['descuento'];
    }

    //	seteo los totales
    $v['foto_total'] = $v['foto_subtotal'] - $v['foto_desc'];
    $v['vid_total'] = $v['vid_subtotal'] - $v['vid_desc'];
	
	// accesorios no aplican descuentos
    $v['acc_total'] = $v['acc_subtotal'];

    //	total de ventas
    $v['total'] = $v['subtotal'] - $v['descuento'];

    //	porcentaje de comisiones
	$v['per_com_acc'] = getAccesorioComision($v['acc_total']);
    $v['per_com_foto'] = getFotoComision($v['foto_total']);
    $v['per_com_vid'] = getVideoComision($v['vid_total']);

    //	comisiones de acuerdo al porcentaje
    $v['com_acc'] = $v['per_com_acc'] * $v['acc_total'] / 100;
    $v['com_foto'] = $v['per_com_foto'] * $v['foto_total'] / 100;
    $v['com_vid'] = $v['per_com_vid'] * $v['vid_total'] / 100;

    //	comisiones individual total
    $v['com_ind_total'] = $v['com_foto'] + $v['com_vid'] + $v['com_acc'];

    //	porcentaje de venta de este vendedor del total de la venta individual
    $v['per_tot'] = $v['total'] * 100 / $ind_total;


    //	-------------
    $ind_vend[$v['name']] = $v;
}





//  ----------------------------------------------------------------------------
//  TODOS LOS PEDIDOS PRESUPUESTADOS COBRADOS	--------------------------------
//  ----------------------------------------------------------------------------
/* */
$where = " WHERE pedidos.estado = 1 ";
$where .= " AND Evento = '" . $event_name . "' ";
$where .= " AND `idPresupuesto` <> '' AND idPresupuesto IS NOT NULL ";

$sql = "SELECT *, vendedores.id AS vid, pedidos.id AS pid FROM pedidos "
	. " JOIN vendedores ON (pedidos.idVendedor = vendedores.id) "
	. " $where ORDER BY vendedores.Vendedor, pid;";
$pedidos = $conn->Execute($sql);

//d($sql);
//d($ind_vend);

$venta_presu = array();
$venta_presu['total'] = 0;

$venta_presu['coreo_total'] = 0;
$venta_presu['video_total'] = 0;

while (!$pedidos->EOF) {
    $row = $pedidos->fields;

    $ven_name = $row['Vendedor'];
    //------------------------------------------------------
    if (!isset($ind_vend[$ven_name])) {
	$ind_vend[$ven_name] = defSeller();
	$ind_vend[$ven_name]['name'] = $ven_name;

	/* $ind_vend[$ven_name] = array(
	  'name' => $ven_name,
	  'descuento' => 0,
	  'img_subtotal' => 0,
	  'vid_subtotal' => 0,
	  'coreo_subtotal' => 0,
	  'subtotal' => 0,
	  'foto_subtotal ' => 0,
	  ); */
    }


    //	venta presupuesto total no se aplican descuentos, porque los mismos
    //	YA estan en los presupuestos y NO en el pedido
    $venta_presu['total'] += $row['total'];

    //	obtengo el subtotal de coreos(fotos) y videos a partir del presupuesto
    $presu = new presupuestos();
    $presu->get($row['idPresupuesto']);
    //d('Presu ID: ' . $presu->id);
    //d('DTO Presu: %' . $presu->descuento);

    $pobj = json_decode($presu->presupuesto);

    //	sumo los precios de las coreos
    $subtcoreos = 0;
	if (isset($pobj->coreo)) foreach ($pobj->coreo as $coreo) {
	$subtcoreos += $coreo->qty * $coreo->unit_price;
    }
    //d('Coreo sub: ' . $subtcoreos);
    //	le aplico el descuento del presupuesto
    $subtcoreos -= $subtcoreos * $presu->descuento / 100;
    //d('Coreo tot: ' . $subtcoreos);
    $venta_presu['coreo_total'] += $subtcoreos;

    //	sumo los precios de los videos
    $subtvid = 0;
	if (isset($pobj->video)) foreach ($pobj->video as $vid) {
	$subtvid += $vid->qty * $vid->unit_price;
    }
    //d('Video sub: ' . $subtvid);
    //	le aplico el descuento del presupuesto
    $subtvid -= $subtvid * $presu->descuento / 100;
    //d('Video tot: ' . $subtvid);
    $venta_presu['video_total'] += $subtvid;
    //d('<hr>');
    //	---------------------------------------------
    //$ind_vend[$ven_name] = $tmp;
    $pedidos->MoveNext();
}

//d($venta_presu);




$venta_presu['per_com_coreo'] = getPresuComFoto($venta_presu['coreo_total']);
$venta_presu['com_coreo'] = ($venta_presu['coreo_total'] * $venta_presu['per_com_coreo'] / 100);

$venta_presu['per_com_video'] = getPresuComVideo($venta_presu['video_total']);
$venta_presu['com_video'] = ($venta_presu['video_total'] * $venta_presu['per_com_video'] / 100);

$venta_presu['com_ind_total'] = $venta_presu['com_video'] + $venta_presu['com_coreo'];


//  calcular la comisión por presupuesto para cada vendedor
foreach ($ind_vend as $v) {

    //	default values	------------------
    //$v['per_tot'] = !isset($v['per_tot']) ? 0 : $v['per_tot'];
    //$v['com_ind_total'] = !isset($v['com_ind_total']) ? 0 : $v['com_ind_total'];
    
    //	-----------------------------------------------
    //	la comision de los presupuestos es el porcentaje correspondiente
    //	del monto total a distribuir ($venta_presu['com_ind_total']) de acuerdo
    //	a las ventas individuales
    $v['com_presu'] = $v['per_tot'] * $venta_presu['com_ind_total'] / 100;
    //	comision total (individual + presu)
    $v['com_total'] = $v['com_presu'] + $v['com_ind_total'];
    //	----------------------------------------------
    $ind_vend[$v['name']] = $v;
}



//d($venta_presu);

/* */
?>

<style>

    table.mytable {
	font-size: 14px;
	border: 0;
    }
    table.mytable a{
	text-decoration: none;
    }

    .tc { text-align: center; }
    .tl { text-align: left; }
    .tr { text-align: right; }

    .bggray { background-color: #C4C4C4; }

    .cyes { background-color: green; color:white; padding: 2px 16px; }	
    .cno { background-color: red; color:white;padding: 2px 8px; }

    .tot {
	background-color: #707070;
	color:white;
	font-size: 25px;
	font-weight: bold;
    }


</style>
<div style="text-align: center; margin: 0 auto; width: 45%;">
    <table class="mytable" width="100%" border="0" cellpadding="3" cellspacing="0">

	<tr>
	    <td colspan="6" class="ewGroupName ewGroupName_1">
		Evento <?= $event_name ?>
	    </td>
	</tr>

	<tr>
	    <td colspan="6">&nbsp;</td>
	</tr>

	<tr>
	    <th colspan="6" class="tl bggray">VENTA INDIVIDUAL</th>
	</tr>
	<tr>
	    <th>Subt. Individual</th>
	    <th class="">Descuento</th>
	    <th class="">TOTAL</th>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	</tr>

	<tr>
	    <td class="tc">$<?= MaxterHlp::fn($ind_subtotal) ?></td>
	    <td class="tc">$<?= MaxterHlp::fn($ind_desc) ?></td>
	    <td class="tc">$<?= MaxterHlp::fn($ind_total) ?></td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>

	</tr>

	<tr><td colspan="6">&nbsp;</td></tr>

	<tr>
	    <th colspan="6" class="tl bggray">VENTA POR PRESUPUESTOS</th>
	</tr>
	<tr>
	    <td>&nbsp;</td>
	    <th class="">Venta Total:</th>
	    <th class="">% Comision</th>
	    <th class="">Comision</th>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	</tr>

	<tr>
	    <td class="tr"><b>Coreo</b></td>
	    <td class="tc">$<?= MaxterHlp::fn($venta_presu['coreo_total']) ?></td>
	    <td class="tc"><?= MaxterHlp::fn($venta_presu['per_com_coreo']) ?>%</td>
	    <td class="tc">$<?= MaxterHlp::fn($venta_presu['com_coreo']) ?></td>	    
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	</tr>
	<tr>
	    <td class="tr"><b>Video</b></td>
	    <td class="tc">$<?= MaxterHlp::fn($venta_presu['video_total']) ?></td>
	    <td class="tc"><?= MaxterHlp::fn($venta_presu['per_com_video']) ?>%</td>
	    <td class="tc">$<?= MaxterHlp::fn($venta_presu['com_video']) ?></td>	    
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	</tr>
	<tr>
	    <td colspan="3" class="tr"><b>Monto total a comisionar:</b></td>
	    <td class="tc">$<?= MaxterHlp::fn($venta_presu['com_ind_total']) ?></td>	    
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	</tr>

	<tr><td colspan="6">&nbsp;</td></tr>

<?
//  ----------------------------------------------  VENDEDORES
foreach ($ind_vend as $v) {
    ?>
    	<tr>
    	    <td colspan="6" class="ewGroupField ewGroupField_2">
    		Vendedor <?= $v['name'] ?>
    	    </td>
    	</tr>

    	<tr>
    	    <th class="tr">Subt. Individual:</th>
    	    <td class="tl">$<?= MaxterHlp::fn($v['subtotal']) ?></td>
    	    <th class="tr">Descuento:</th>
    	    <td class="tl">$<?= MaxterHlp::fn($v['descuento']) ?></td>
    	    <th class="tr">TOTAL:</th>
    	    <td class="tl">$<?= MaxterHlp::fn($v['total']) ?></td>
    	</tr>
    	<tr>
    	    <th colspan="5" class="tr">Porcentaje de la venta total:</th>
    	    <td class="tl"><?= MaxterHlp::fn($v['per_tot']) ?>%</td>
    	</tr>

    	<tr><td colspan="6">&nbsp;</td></tr>

    	<tr>
    	    <td class="ewGroupHeader">&nbsp;</td>
    	    <td class="ewGroupHeader">Subt.</td>
    	    <td class="ewGroupHeader">Desc.</td>
    	    <td class="ewGroupHeader">TOTAL</td>
    	    <td class="ewGroupHeader">% Comision</td>
    	    <td class="ewGroupHeader">Comision</td>
    	</tr>
    	<tr>
    	    <td>Fotos: </td>
    	    <td>$<?= MaxterHlp::fn($v['foto_subtotal']) ?></td>
    	    <td>$<?= MaxterHlp::fn($v['foto_desc']) ?></td>
    	    <td>$<?= MaxterHlp::fn($v['foto_total']) ?></td>
    	    <td><?= $v['per_com_foto'] ?>%</td>
    	    <td>$<?= MaxterHlp::fn($v['com_foto']) ?></td>
    	</tr>
    	<tr>
    	    <td>Video: </td>
    	    <td>$<?= MaxterHlp::fn($v['vid_subtotal']) ?></td>
    	    <td>$<?= MaxterHlp::fn($v['vid_desc']) ?></td>
    	    <td>$<?= MaxterHlp::fn($v['vid_total']) ?></td>
    	    <td><?= $v['per_com_vid'] ?>%</td>
    	    <td>$<?= MaxterHlp::fn($v['com_vid']) ?></td>
    	</tr>
    	<tr>
    	    <td>Accesorios: </td>
    	    <td>$<?= MaxterHlp::fn($v['acc_subtotal']) ?></td>
    	    <td> - </td>
    	    <td>$<?= MaxterHlp::fn($v['acc_total']) ?></td>
    	    <td><?= $v['per_com_acc'] ?>%</td>
    	    <td>$<?= MaxterHlp::fn($v['com_acc']) ?></td>
    	</tr>
    	<tr>
    	    <td colspan="5" class="tr"><h3>Comisión de venta individual Total:</h3></td>
    	    <td><h3>$<?= MaxterHlp::fn($v['com_ind_total']) ?></h3></td>
    	</tr>
    	<tr>
    	    <td colspan="5" class="tr"><h3>Comisión de venta por presupuesto:</h3></td>
    	    <td><h3>$<?= MaxterHlp::fn($v['com_presu']) ?></h3></td>
    	</tr>
    	<tr>
    	    <td colspan="5" class="tot tr">Comisión TOTAL:</td>
    	    <td class="tot">$<?= MaxterHlp::fn($v['com_total']) ?></td>
    	</tr>
    	<tr><td colspan="6">&nbsp;</td></tr>
<? } ?>
    </table>
</div>


<?
/*
d('Venta individual subtotal: ' . $ind_subtotal);
d('Venta individual descuento: ' . $ind_desc);
d('Venta individual TOTAL: ' . $ind_total);

d('Venta individual por vendedor');
d($ind_vend);
*/