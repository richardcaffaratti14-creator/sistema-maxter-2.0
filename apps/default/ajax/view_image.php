<?
$presu_mode = Presu::getID() != -1;
$pb_mode = PhotoBook::isPBMode();

$xcopias = 0;
if ($pb_mode) {
    $xcopias = getSiteInfo('photobook_min_pics');
}

if (isset($media) && (substr($media, 0,1)!='/')) $media = "/".$media;

$pedidosQty = '';
$ped = SessionManager::getValue('pedido');
if (is_array($ped)) {
    foreach ($ped as $item) {
		if ($item['name'] == $media) {
			foreach ($item['formats'] as $f) {
			$formatsObj = new formato_imagen();
			$formatsObj->orderBy('orden');
			$formats = $formatsObj->select();
			$formatsObj->get((int) $f['format_id']);
			if ($pb_mode) {
				$formatsObj->nombre = 'Fotolibro m&iacute;nimo '.$xcopias.' copias';
			}
			$pedidosQty .= '<tr><td>' . $formatsObj->nombre . '</td><td style="text-align:right; padding-left: 30px">' . $f['qty'] . "</td></tr>";
			}
		}
    }
}

$formatsObj = new formato_imagen();
$formatsObj->orderBy('orden');
$formats = $formatsObj->select();

if ($pb_mode) {

    $precio = getSiteInfo('photobook_image_price');

    $ffs = $formats;
    $formats = array();
    foreach ($ffs as $f) {
	if ($f->ancho == 0 && $f->alto == 0) {
	    $f->precio = $precio;
	    $f->nombre = "FotoLibro";
	    $formats[] = $f;
	}
    }
}


//Dump::dlp(base64_decode($prev), base64_decode($next));
?>
<form id="order-frm" method="post" action="<?= App::getApplicationUrl() ?>ajax/add_image">
    <div style="height: <?= VIEW_WINDOW_H ?>px; width: <?= VIEW_WINDOW_W ?>px;">

	<div id="dlg-added">Foto / Video agregado con éxito.</div>

	<div class="floatl">
	    <img src="<?= Img::crop('images/' . PATH_ORIGINALS . $media, VIEW_SIZE) ?>" />
	    <input type="hidden" name="m" value="<?= $media ?>" />
	</div>
	<div class="floatl formats-qty">

	    <? if ($presu_mode) { ?>
    	    <strong>Selecciones individuales no permitidas en presupuestos</strong>
	    <? } else { ?>
    	    <strong>Seleccione el formato y cantidad de copias.</strong>
    	    <br /><br />
    	    <table style="border-spacing: 0" id="imageformatstable">
		    <? foreach ($formats as $f) { ?>
			<tr>
			    <td style="height: 70px"><input class="group_select" type="radio" name="sel_format" value="<?= $f->id ?>" id="op_<?= $f->id ?>" /></td>
			    <td><label class="imageformatstablelabel" for="op_<?= $f->id ?>" style="padding-right: 5px;"><?= $f->nombre ?></label></td>
			    <td><label class="imageformatstablelabel" for="op_<?= $f->id ?>" style="font-weight:bold;">$ <?= MaxterHlp::fn($f->precio) ?> c/u</label></td>
			    <td style="padding-right: 8px;">&nbsp;</td>
			    <td>
				<?
				if ($pb_mode) {
				  echo '<div style="display:none;">';
				}
				?>
				<label <?= $st ?> id="group_<?= $f->id ?>" class="one_group" for="op_<?= $f->id ?>">
				    <input type="hidden" name="formats[]" value="<?= $f->id ?>" />
				    <a href="javascript:;" class="steeper-button" id="remove_<?= $f->id ?>">-</a>
				    <input class="steeper-label" type="text" id="qty_<?= $f->id ?>" name="f<?= $f->id ?>" />
				    <a href="javascript:;" class="steeper-button" id="add_<?= $f->id ?>">+</a>
				</label>
				<?
				if ($pb_mode) {
				  echo '</div>';
				}
				?>

			    </td>
			</tr>
		<? } ?>
    	</table>
		<? if (!empty($pedidosQty)) { ?>
				<div style="border: 1px solid #999; padding: 15px; font-weight:bold;">
				<h3 style="padding-top: 0px; margin-top: 0px;">Copias actualmente encargadas:</h3>
				<table>
					<tr><th>Formato</th><th style="text-align: right; padding-left: 30px;">Copias</th></tr>
					<?= $pedidosQty ?>
				</table>
				</div>
		<? } ?>
		<div id="blk-notas">
			<h3>Notas:</h3>
			<textarea id="notas" name="notas"></textarea>
		</div>

   	    <a href="javascript:cropMedia('<?= $media ?>');" class="bigorange" id="continuarbtn" style="display: none">Agregar &rarr;</a>
<? } ?>
	</div>
	<div class="clearb"></div>
	<div style="text-align: center;">
	    <a href="javascript:openViewMedia('<?= $prev ?>');" class="bigorange-nextprev" style="float: none" title="Ver anterior">&larr; Anterior</a>
	    <a href="javascript:openViewMedia('<?= $next ?>');" class="bigorange-nextprev" style="margin-left: 10px; float: none " title="Ver siguiente">Siguiente &rarr;</a>
	</div>
    </div>
</form>
<script type="text/javascript" charset="utf-8">

    $("#blk-notas").hide();
    $(".group_select").change(function () {
	if ($(this).is(":checked"))
	    $("#continuarbtn").show();
	$("#blk-notas").show();
    });

    function steeper(id) {
	$('#qty_' + id).val(0);
	$('#remove_' + id).click(function () {
	    var val = parseInt($('#qty_' + id).val());
	    if (val > 0) {
		$('#qty_' + id).val(val - 1);
	    }
	});

	$('#add_' + id).click(function () {
	    var val = parseInt($('#qty_' + id).val());
	    if (val < 999) {
		$('#qty_' + id).val(val + 1);
	    }
	});
    }

<? foreach ($formats as $f) { ?>
        steeper(<?= $f->id ?>);
<? } ?>

    $('.group_select').click(function () {
	//	set 0 to all qtys
	$('.one_group .steeper-label').each(function (idx) {
	    $(this).val(1);
	})
	//	hide all steppers
	$('.one_group').hide();
	// get the id
	var id = $(this).val();
	//	show the selected id
	$('#group_' + id).show();

	$("#imageformatstable label").removeClass("imageformatstable-on");
	$("#imageformatstable label[for=op_" + id + "]").addClass("imageformatstable-on");
    })

    //$('#qty_2').val(1);

    $('#view-media').dialog({width: "auto", height: "auto"});
    $('#view-media').dialog({position: ["center", "center"]});

    $('#order-frm').ajaxForm({target: "#view-media"});

    $('.one_group').hide();

    if (show_dialog == 1) {
	$('#dlg-added').show();
	setTimeout(function () {
	    $('#dlg-added').hide();
	    show_dialog = 0;
	}, 1000);
    }


<? if ($pb_mode) { ?>
        $('.group_select').click();
<? } ?>

    /* */
</script>