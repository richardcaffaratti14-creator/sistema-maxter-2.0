<?php
set_time_limit(6000);

//  -----------------------------------------------
//  Presupuesto!!!
//  -----------------------------------------------
$presu_id = Presu::getID();
$presu_mode = $presu_id != -1;
$presu = new presupuestos();
$presu->get($presu_id);

//  -----------------------------------------------
//  FOTOLIBRO
//  -----------------------------------------------
$pb_mode = PhotoBook::isPBMode();
$pb_id = PhotoBook::getID();
$pb = new fotolibros();
$pb->get($pb_id);

//  -----------------------------------------------
//  AutorizaciÛn
//  -----------------------------------------------
$aut = Http::getOverPost('aut');
$code = getSiteInfo('claveautorizacion');
$maxventapedido = getSiteInfo('maxventapedido');


//  -----------------------------------------------


$n = Http::getOverPost('nombre');
$a = Http::getOverPost('apellido');
$t = Http::getOverPost('tel');
$vendedor = Http::getOverPost('vendedor');
if ($vendedor != '')
    $vendedor = (int) $vendedor;
$rfecha = Http::getOverPost('fecha');
$rhora = Http::getOverPost('hora');
$retiro = $rfecha . " " . $rhora;
$descuento = (float) Http::getOverPost('descuento');


$preciototal = 0;
$desc = '';
$desc_accesorios = '';
$ped = SessionManager::getValue('pedido');
$notas = '';

//	get data to generate order & get order total
foreach ($ped as $item) {
	if ($item['type'] == 'acc') {
		$sub = $item['qty'] * $item['amt'];
		$preciototal += $sub;
		$desc_accesorios .= $item['qty'] . ' x ' . $item['l'] . ' = $ ' . MaxterHlp::fn($sub) . "\n";
	}
	else {
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
			$preciototal += $sub;


			$itprice = '';
			if (!$presu_mode && !$pb_mode) {
				$itprice = ' = $ ' . MaxterHlp::fn($sub);
			}

			if ($item['type'] == 'coreo')
				$desc .= 'COREO [' . $format->nombre . ']: ' . $item['name'] . $itprice . "\n";
			else
				$desc .= $item['name'] . ' [' . $format->nombre . ' x ' . $f['qty'] . ' copias ' . $itprice . ']' . "\n";

			$notas .= $f['note'] . '~~~';
		}
	}
}

//Agregar detalle de accesorios
if (!empty($desc_accesorios)) {
	$desc .= "\n---- ACCESORIOS ----\n" . $desc_accesorios . "\n";
}

$extra = array();
$extra['retiro'] = $retiro;

if ($preciototal < $descuento) {
    $descuento = $preciototal;
}

if (!$presu_mode && !$pb_mode) {
    //  checkear si no se ha excedido el maximo solo en la venta individual, 
    //  no por presupuesto
    $maxventapedido = $maxventapedido == 0 ? 999999 : $maxventapedido;
    if ($preciototal - $descuento > $maxventapedido) {
	if ($aut != $code) {
	    ?>
	    <h3 style="color:red;">
	        Se ha excedido el l&iacute;mite de ventas de pedido. <br />
	        C&oacute;digo de autorizaci&oacute;n inv&aacute;lido
	    </h3>

	    <script>
	        refreshCart();
	        $("#view-media").dialog({
	    	buttons: {
	    	    //			"Cerrar":function() { $(this).dialog("close"); }
	    	}
	        });
	    </script>	
	    <?
	    die;
	}
    }
    //	-------------------------------------------------------
    //
    $invalid_discount = Http::getOverPost('invalid_discount');
    if ($invalid_discount == 1) {

	if ($aut != $code) {
	    ?>
	    <h3 style="color:red;">
	        Se ha excedido el el descuento permitido. <br />
	        C&oacute;digo de autorizaci&oacute;n inv&aacute;lido
	    </h3>

	    <script>
	        refreshCart();
	        $("#view-media").dialog({
	    	buttons: {
	    	    //			"Cerrar":function() { $(this).dialog("close"); }
	    	}
	        });
	    </script>	
	    <?
	    die;
	}
    }
}


//  autorizar descuento si es FotoLibro
if ($pb_mode) {
    $invalid_discount = Http::getOverPost('invalid_discount');
    $descuento = (float) Http::getOverPost('descuento');
    
    if ($descuento > 0) {
	
	$descuento = floatval($pb->total * $descuento / 100);
	
	if ($invalid_discount == 1) {
	    if ($aut != $code) {
		?>
		<h3 style="color:red;">
		    Se ha excedido el el descuento permitido. <br />
		    C&oacute;digo de autorizaci&oacute;n inv&aacute;lido
		</h3>

		<script>
		    refreshCart();
		    $("#view-media").dialog({
			buttons: {
			    //	"Cerrar":function() { $(this).dialog("close"); }
			}
		    });
		</script>	
		<?
		die;
	    }
	}
    }
}



//	save order
$pedido = new pedidos();
$pedido->nombre = $n;
$pedido->apellido = $a;
$pedido->telefono = $t;
$pedido->descripcion = $desc;
$pedido->Descuento = (float) $descuento;

if ($presu_mode) {
    $pedido->total = $presu->total;
    //	guardo la seÒa si es que fue cobrada
    if ($presu->estado == 1) {
	$pedido->sena = $presu->sena;
    }
} elseif ($pb_mode) {
    //	guardo la seÒa si es que fue cobrada
    if ($pb->estado == 1) {
	$pedido->sena = $pb->sena;
    }

    $pedido->total = $pb->total;
} else {
    $pedido->total = $preciototal;
}

$pedido->estado = "0";
$pedido->Evento = getSiteInfo('evento');
$pedido->notas = $notas;
$pedido->pedido = serialize($ped);
$pedido->extra = serialize($extra);

$pedido->idPresupuesto = $presu_id;
$pedido->idFotolibro = $pb_id == -1 ? NULL : $pb_id;

if ($presu_mode) {
    $pedido->idVendedor = $presu->idVendedor;
} elseif ($pb_mode) {
    $pedido->idVendedor = $pb->idVendedor;
} else {
    $pedido->idVendedor = $vendedor;
}

$pedido_id = $pedido->save();

//Dump::dl($pedido_id);
//	generate order

$_GET['id'] = $pedido_id;
$_pdf_file = PATH_IMAGES_ROOT . "pdf/" . $pedido_id . ".pdf";

if (!is_dir(PATH_IMAGES_ROOT . "pdf"))
    mkdir(PATH_IMAGES_ROOT . "pdf");
include 'pdf.php';

SessionManager::unsetValue('pedido');
Presu::cancelPresuMode();
PhotoBook::cancelPBMode();
?>
<h3>Su pedido es el #: <strong><?= $pedido_id ?></strong></h3>

<a href="?process_order=<?= $pedido_id ?>">Click aqu√≠ para procesar las fotos y videos seleccionados</a>

<script>
    refreshCart();
    $("#view-media").dialog({
	buttons: {
//			"Cerrar":function() { $(this).dialog("close"); }
	}
    });
</script>