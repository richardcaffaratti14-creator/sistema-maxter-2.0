<?
//error_reporting(E_ALL);
$pid = Http::getOverPost('pid');

$pedido_db = new pedidos();
$pedido_db->get($pid);

$pedido = unserialize($pedido_db->pedido);

if (!is_array($pedido)) {
    $pedido = array();
}

$fid = Http::getOverPost('fid');

if ($pid != '' && $fid != '') {
    $pedido = SessionManager::getValue("pedido");

    unset($pedido[$pid]['formats'][$fid]);

    if (count($pedido[$pid]['formats']) == 0) {
	unset($pedido[$pid]);
    }

    $pedido = array_values($pedido);

    if (is_array($pedido)) {
	for ($i = 0; $i < count($pedido); $i++) {
	    $pedido[$i]['formats'] = array_values($pedido[$i]['formats']);
	}
    }

    SessionManager::unsetValue("pedido");

    SessionManager::setValue('pedido', $pedido);
}

//Dump::d($pedido);

$preciototal = 0;
$precioaccesorios = 0;
$precio_desglose = array(
	'Fotos' => 0,
	'Videos' => 0
);
?>
<style>
    .pedido-thumb{
	width: <?= ((VIEW_MAX_W / 2) - 30) ?>px;
    }
</style>

<div id="pedido-wrapper" style="width: <?= VIEW_MAX_W * 1.5 ?>px;">
    <? if (count($pedido) > 0) { ?>

        <div style="float: right; "><a href="javascript:void(0)" id="_procesar_alldig">Procesar digitales</a></div>
        <h2>Procesar pedido #<?= $pid ?> - (<?= $pedido_db->nombre . " " . $pedido_db->apellido ?>)</h2>
        <div id="pedido-thumbs">
	    <?
		foreach ($pedido as $pidx => $item) {
			
			if ($item['type'] == 'acc') {
				//sumar accesorios al precio total
				$sub = $item['qty'] * $item['amt'];
				$precioaccesorios += $sub;
				$preciototal += $sub;
				continue;
			}
			
			$fidx = 0;
			foreach ($item['formats'] as $f) {
				
				// sumar por defecto a fotos
				$precio_desglose_key = 'Fotos';
				
				if ($item['type'] == 'vid') {
					// sumar a videos
					$precio_desglose_key = 'Videos';
					$format = new formato_video();
					$printProcess = false;
				} elseif ($item['type'] == 'coreo') {
					$format = new formato_coreo();
					$printProcess = true;
				} else {
					$format = new formato_imagen();
					$printProcess = true;
				}

				$format->get($f['format_id']);

				if (!$printProcess)
					$procesado = true;
				else {
					if ($item['type'] == 'coreo') {
						$basename = folderToFilename($item['name']); //add folder to the filename
						$procesado = file_exists(str_replace('//', '/', PATH_IMAGES_ROOT . PATH_ORDERS . $pid . '/' . $format->carpeta . "/" . $basename));
					} else {
						$parts = pathinfo($item['name']);
						$dirname = $parts['dirname'];
						$basename = folderToFilename($dirname) . $parts['basename']; //add folder to the filename

						$procesado = file_exists(str_replace('//', '/', PATH_IMAGES_ROOT . PATH_ORDERS . $pid . '/' . $format->carpeta . '/' . $basename));
					}
				}


				$procesar_digital = $printProcess ? ($format->ancho == 0) && ($format->alto == 0) : false;

				//	if ($f['processed'] || $item['type'] == 'vid') {	// para proceso al final
				?>
				<div class="pedido-thumb pedido-thumb-4cols floatl <?= $procesado ? "procesadook" : "" ?> <?= $procesar_digital ? "_es_digital" : "" ?>" data-id="<?= $pidx ?>" id="pedido-thumb-<?= $pidx ?>">
				<? if ($item['type'] == 'coreo') { ?>
					<img src="static/img/icono-coreo.png" width="60" class="floatl" />
				<? } ?>
				<? if ($item['type'] == 'vid') { ?>
					<img src="static/img/video_icon_thumb.png" width="60" class="floatl" />
				<? } ?>
				<? if ($printProcess && ($item['type'] == 'img')) { ?>
					<a href="javascript:process(<?= $pid ?>, <?= $pidx ?>, 0);"><img src="<?= Img::crop('images/' . PATH_ORIGINALS . $item['name'], "80x90") ?>" class="floatl" /></a>
				<? } ?>


					<span class="pedido-name" title="<?= $item['name'] ?>"><?= $item['name'] ?></span><br />
					<span class="pedido-format"><?= $format->nombre ?><? if ($item['type'] != 'coreo') { ?> x <?= $f['qty'] ?> copias<? } ?></span><br />
					<span><strong>Notas:</strong> <?= $f['note'] ?></span>
					<br />
				<?
				$sub = ((int) $f['qty'] * $format->precio);
				$precio_desglose[$precio_desglose_key] += $sub;
				$preciototal += $sub;
				?>
					<span><? if ($item['type'] != 'coreo') { ?><?= $f['qty'] ?> x $<?= MaxterHlp::fn($format->precio) ?> = <? } ?>$ <?= MaxterHlp::fn($sub) ?> </span>
				<? if ($printProcess && ($item['type'] != 'coreo')) { ?>
					<br />

					<? if (!is_numeric($pedido_db->idFotolibro)) { ?>
						<a href="javascript:process(<?= $pid ?>, <?= $pidx ?>, 0);">Procesar</a>
					<? } ?>


					<? if ($procesar_digital) { ?>
					<? if (!is_numeric($pedido_db->idFotolibro)) { ?>
						&nbsp;&nbsp;|&nbsp;&nbsp;
					<? } ?>
						<a href="javascript:process(<?= $pid ?>, <?= $pidx ?>, 1);">Procesar Digital</a><? } ?>
				<? } ?>


				<? if ($item['type'] == 'coreo') { ?>
					<br />
					<a href="javascript:process(<?= $pid ?>, <?= $pidx ?>, 1);">Procesar Coreo</a>
				<? } ?>
			<!--						&nbsp;&nbsp;|&nbsp;&nbsp;<a style="color: #a00000;" href="javascript:delete_item(<?= $pid ?>, <?= $pidx ?>);">Eliminar</a>-->

					<div style="float: right; display: none;" class="_processing_loading"><img src="static/img/loader.gif" /></div>
				</div>
				<?
				$fidx++;
			}
	    }
		
		
		//--------------- PRECIO TOTAL FOTOLIBROS --------------
		//Si el pedido es fotolibro, tomar directamente el total ya que las fotos tienen un precio precalculado!
		if ($pedido_db->idFotolibro) {
			$preciototal = $pedido_db->total;
			$precio_desglose = array();	// no hay desglose
		}
		
		
	    ?>
        </div>
        <div class="clearb"></div>
        <div id="pedido-total">
			<span style="font-weight: normal; font-size: 12px">
				Detalle <?if ($pedido_db->Descuento > 0) {?>(incluye descuento)<?}?><br/>
				<?
				$printSep = false;
				foreach ($precio_desglose as $pdes_label => $pdes_val) {
					if (empty($pdes_val)) continue;
					if ($printSep) echo ' - ';
					if (($pedido_db->Descuento > 0) && $preciototal) {
						// aplicar el descuento de manera proporcional al total
						$porcentajeSobreTotal = round($pdes_val * 100 / $preciototal);
						$pdes_val = $pdes_val - ($pedido_db->Descuento * $porcentajeSobreTotal / 100);
					}
					?>
					<?= $pdes_label ?>: <strong>$<?= MaxterHlp::fn($pdes_val) ?></strong>
					<?
					$printSep = true;
				}?>
				<?if ($precioaccesorios) {
					if ($printSep) echo ' - ';
					$precioaccesoriosPrint = $precioaccesorios;
					if (($pedido_db->Descuento > 0) && $preciototal) {
						// aplicar el descuento de manera proporcional al total
						$porcentajeSobreTotal = round($precioaccesoriosPrint * 100 / $preciototal);
						$precioaccesoriosPrint = $precioaccesoriosPrint - ($pedido_db->Descuento * $porcentajeSobreTotal / 100);
					}
					?>
					Accesorios: <strong>$<?= MaxterHlp::fn($precioaccesoriosPrint) ?></strong>
				<?}?>
				<br/><br/>
			</span>
			
			<? if ($pedido_db->Descuento > 0) { ?>
				<span style="font-size: 12px">
					Subtotal: $<?= MaxterHlp::fn($preciototal) ?> - 
					Descuento: $<?= MaxterHlp::fn($pedido_db->Descuento) ?>
					<br/><br/>
				</span>
				Total: $<?= MaxterHlp::fn($preciototal - $pedido_db->Descuento) ?>
			<? } else { ?>
				Total: $<?= MaxterHlp::fn($preciototal) ?>
			<? } ?>
        </div>
    <? } else { ?>
        <h2>No hay fotos o videos en su pedido.</h2>
        <script>
    	$('#view-media').dialog({
    	    title: "Confirmar pedido",
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
    <? } ?>
</div>

<div id="crop-media" style="display: none" title="Recortar imagen"></div>

<script type="text/javascript" charset="utf-8">

    $("#_procesar_alldig").click(function () {
	$("._es_digital").each(function () {
	    $("._processing_loading", this).show();
	    var id = $(this).attr('data-id');
	    process(<?= $pid ?>, id, 1);
	});
    });

    function delete_item(pid, fidx) {
	if (confirm('Confirma eliminar el elemento seleccionado?')) {
	    $('#crop-media').load("ajax/delete_item?p=" + pid + "&f=" + fidx, {}, function () {
		$("#pedido-thumb-" + fidx).remove();
	    });
	}
    }
    function process(pid, fidx, digital) {
	if (digital) {
	    $('#crop-media').load("ajax/crop_image?digital=1&p=" + pid + "&f=" + fidx);
	} else {
	    $('#crop-media').load("ajax/crop_image?p=" + pid + "&f=" + fidx, {}, function () {
		$('#crop-media').dialog({
		    title: "Procesar imagen",
		    width: "auto",
		    height: "auto",
		    position: ["75px", "50px"],
		    buttons: {
			"Cancelar": function () {
			    $(this).dialog("close");
			    //refreshCart();
			    $('#crop-media').html("");
			}
			//					,"Procesar":function() { 
			//						$(this).dialog("close"); 
			//						refreshCart();
			//						$('#crop-media').html(""); 
			//					}
		    }
		});

	    });
	}

    }

    $('#view-media').dialog({
	width: "auto",
	height: "auto",
	position: ["center", "center"]
    })

    $("#view-media").dialog("option", "position", "center");
</script>