<?
$pedido = SessionManager::getValue('pedido');

if (!is_array($pedido)) {
    $pedido = array();
}

$pid = Http::getOverPost('pid');
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
?>
<style>
    .pedido-thumb {
		width: <?= ((VIEW_MAX_W / 2) - 30) ?>px;
    }
</style>
<div id="pedido-wrapper" style="width: <?= VIEW_MAX_W * 1.5 ?>px;">
    <? if (count($pedido) > 0) { ?>
        <h2>Mi Pedido</h2>
        <div id="pedido-thumbs">
	    <?
	    $pidx = 0;
	    foreach ($pedido as $item) {
			$fidx = 0;
			foreach ($item['formats'] as $f) {
				if ($item['type'] == 'vid') {
					$format = new formato_video();
				} elseif ($item['type'] == 'coreo') {
					$format = new formato_coreo();
					$printProcess = false;
				} elseif ($item['type'] == 'img') {
					$format = new formato_imagen();
				} else {
					continue;	//ignore, unknown type
				}
				$format->get($f['format_id']);
				//	if ($f['processed'] || $item['type'] == 'vid') {	// para proceso al final
				?>
				<div class="pedido-thumb floatl">
					<? if ($item['type'] == 'coreo') { ?>
						<img src="static/img/icono-coreo.png" width="60" class="floatl" />
					<? } ?>
					<? if ($item['type'] == 'vid') { ?>
						<img src="static/img/video_icon_thumb.png" width="60" class="floatl" />
					<? } ?>
					<? if ($item['type'] == 'img') { ?>
						<a href="<?= Img::crop('images/' . PATH_ORIGINALS . $item['name'], VIEW_SIZE) ?>" class="nofollow"><img src="<?= Img::crop('images/' . PATH_ORIGINALS . $item['name'], "80x90") ?>" class="floatl" /></a>
					<? } ?>

					<span class="pedido-name" title="<?= $item['name'] ?>"><?= $item['name'] ?></span><br />
					<span class="pedido-format"><?= $format->nombre ?><? if ($item['type'] != 'coreo') { ?> x <?= $f['qty'] ?> copias<? } ?></span><br />
					<br />
					<?
					$sub = ((int) $f['qty'] * $format->precio);
					$preciototal += $sub;
					?>
					<span><? if ($item['type'] != 'coreo') { ?><?= $f['qty'] ?> x $<?= MaxterHlp::fn($format->precio) ?> = <? } ?>$ <?= MaxterHlp::fn($sub) ?> </span><br />
					<a href="javascript:removeFromOrder(<?= $pidx ?>, <?= $fidx ?>);">Quitar del pedido</a>
				</div>
				<?
				$fidx++;
			}
			$pidx++;
	    }
	    ?>
        </div>
        <div class="clearb"></div>
		
		
		<?
		$accesorios = array();
		foreach ($pedido as $item) {
			if ($item['type'] == 'acc') {
				$accesorios[] = $item;
			}
		}
		if (count ($accesorios)) {?>
	        <h2>Accesorios</h2>
			<table cellpadding="3" class="highhover">
				<tr>
					<th style="text-align: left; padding-right: 20px; padding-left: 20px;">Accesorio</th>
					<th style="text-align: right; padding-right: 20px; padding-left: 20px;">Cantidad</th>
					<th style="text-align: right; padding-right: 20px; padding-left: 20px;">Unidad</th>
					<th style="text-align: right; padding-right: 20px; padding-left: 20px;">Total</th>
				</tr>
				<?foreach ($accesorios as $item) {?>
					<tr>
						<td style="padding-right: 20px; padding-left: 20px;"><?= $item['l'] ?></td>
						<td style="text-align: right; padding-right: 20px; padding-left: 20px;  white-space: nowrap"><?= $item['qty'] ?></td>
						<td style="text-align: right; padding-right: 20px; padding-left: 20px;  white-space: nowrap">$ <?= $item['amt'] ?></td>
						<td style="text-align: right; padding-right: 20px; padding-left: 20px;  white-space: nowrap">$ <?= number_format($item['amt'] * $item['qty'], 2) ?></td>
					</tr>
					<?
					$preciototal += $item['amt'] * $item['qty'];
				}?>
			</table>
		<?}?>
		
		
        <div class="clearb"></div>
        <div id="pedido-total">
    	Total: $<?= MaxterHlp::fn($preciototal) ?>
        </div>
    <? } else { ?>
        <h2>No hay fotos, videos o accesorios en su pedido.</h2>
        <script>

    	$("#view-media").dialog("option", "position", "center");

    	$('#view-media').dialog({
    	    width: "auto",
    	    height: "auto",
    	    position: ["center", "center"]
    	})


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
<script type="text/javascript" charset="utf-8">

    if ($('#pedido-thumbs a.nofollow').size() > 0) {


	$('#pedido-thumbs a.nofollow').imgPreview({
	    imgCSS: {
		height: 450
	    },
	    // When container is shown:
	    onShow: function (link) {
		// Reset image:
		$('img', this).stop().css({opacity: 0});
	    },
	    // When image has loaded:
	    onLoad: function () {
		// Animate image
		$(this).animate({opacity: 1}, 300);
	    },
	    // When container hides: 
	    onHide: function (link) {
	    }
	});
    }

    $('#pedido-thumbs a.nofollow').click(function (e) {
		e.preventDefault();
    });

    function removeFromOrder(pid, fid) {
		$('#view-media').load('ajax/view_order?pid=' + pid + '&fid=' + fid);
    }

    $("#view-media").dialog("option", "position", "center");

    $('#view-media').dialog({
		width: "auto",
		height: "auto",
		position: ["center", "center"]
    })

    $("#view-media").dialog("option", "position", "center");
</script>