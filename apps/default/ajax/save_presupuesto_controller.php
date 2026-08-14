<?php
$nombre = Http::getOverPost('nombre');
$apellido = Http::getOverPost('apellido');
$tel = Http::getOverPost('tel');
$vendedor = Http::getOverPost('vendedor');
$desc = Http::getOverPost('desc');
$aut = Http::getOverPost('aut');
$sena = Http::getOverPost('sena');
//
$coreo_id = Http::getOverPost('coreo_id');
$coreo_qty = Http::getOverPost('coreo_qty');
//
$video_id = Http::getOverPost('video_id');
$video_qty = Http::getOverPost('video_qty');

$evt_name = getSiteInfo('evento');

//--------------------------------------------------------------------
//  COREO TOTAL
$coreo_total = 0;
$presupuesto = array();
$presupuesto['coreo'] = array();
$presupuesto['video'] = array();
for ($i = 0; $i < count($coreo_qty); $i++) {
    $qty = $coreo_qty[$i];
    if ($qty > 0) {
	$cid = $coreo_id[$i];
	$coreo = new formato_coreo();
	$coreo->get($cid);
	$coreo_total += $coreo->Precio * $qty;
	$presupuesto['coreo'][] = array(
	    'id' => $cid,
	    'qty' => $qty,
	    'name' => $coreo->Nombre,
	    'unit_price' => $coreo->Precio,
	);
    }
}
//  VIDEO TOTAL
$video_total = 0;
for ($i = 0; $i < count($video_qty); $i++) {
    $qty = $video_qty[$i];
    if ($qty > 0) {
	$cid = $video_id[$i];
	$coreo = new formato_video();
	$coreo->get($cid);
	$video_total += $coreo->precio * $qty;
	$presupuesto['video'][] = array(
	    'id' => $cid,
	    'qty' => $qty,
	    'name' => $coreo->nombre,
	    'unit_price' => $coreo->precio,
	);
    }
}
//--------------------------------------------------------------------
$total = $video_total + $coreo_total;
$code = getSiteInfo('claveautorizacion');
if (!Presu::isValidDiscount($total, $desc) && $code != $aut) {
    ?>
    <script>
        $('#view-media').dialog({
    	title: "Autorizaci\u00F3n requerida",
    	buttons: {
    	    "Cerrar": function () {
    		$(this).dialog("close");
    		refreshCart();
    		$('#view-media').html("");
    	    }
    	}
        });

        $('#view-media').dialog({
    	width: "auto",
    	height: "auto",
    	position: ["center", "center"]
        })
    </script>
    <h3>Descuento no autorizado</h3>
    <?
    die;
}

//  die($coreo_total . " :: " . $video_total . " -$desc- " . ($video_total + $coreo_total));

//
$presu = new presupuestos();
$presu->nombre = $nombre;
$presu->apellido = $apellido;
$presu->telefono = $tel;
//  --- add presu JSON
$presu->presupuesto = json_encode($presupuesto);
$presu->subtotal = $total;
$presu->descuento = $desc;
$presu->total = $total;
$presu->sena = $sena;

$presu->evento = $evt_name;
$presu->estado = 0;

if ($desc > 0) {
    $presu->total = $total - ($total * ($desc / 100));
}

$presu->idVendedor = $vendedor;
$pid = $presu->save();
?>
<script>
    $('#view-media').dialog({
	title: "Presupuesto guardado",
	buttons: {
	    "Cerrar": function () {
		$(this).dialog("close");
		refreshCart();
		$('#view-media').html("");
	    }
	}
    });

    $('#view-media').dialog({
	width: "auto",
	height: "auto",
	position: ["center", "center"]
    })
</script>
<h3>Presupuesto guardado</h3>
<a href="ajax/pdf_presu?id=<?= $pid ?>" target="_blank">
    Imprimir Presupuesto
</a>