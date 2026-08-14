<?
$presu_mode = Presu::getID() != -1;
$pb_mode = PhotoBook::isPBMode();

$formats = new formato_video();
$formats->orderBy('orden');
if ($presu_mode) {
    $formats->addCondition('Sufijo', '', '<>');
}
$formats = $formats_tmp = $formats->select();


//  filtro los formatos que no sean del id del presupuesto
if ($presu_mode) {
    $formats = array();
    foreach ($formats_tmp as $f) {

	if (Presu::isValidVideoFormat($f->id) && MaxterHlp::isFileSufix($media, $f->Sufijo)) {
	    $formats[] = $f;
	}
    }
}
?>
<form id="order-frm" method="post" action="<?= App::getApplicationUrl() ?>ajax/add_video">
    <div style=" width: <?= VIEW_WINDOW_W - 100 ?>px;">
	<div id="dlg-added">Foto / Video agregado con éxito.</div>
	<input type="hidden" name="m" value="<?= $media ?>" />
	<div class="floatl" id="videoplayercontainer" style="width: <?= VIDEO_VIEW_MAX_W ?>px; height: <?= VIDEO_VIEW_MAX_H ?>px;">
	    <video id="videoplayer" width="<?= VIDEO_VIEW_MAX_W ?>" height="<?= VIDEO_VIEW_MAX_H ?>" controls="controls" preload="none">
		<source type="video/mp4" src="videos?v=<?= base64_encode($media) ?>" />
	    </video>
	</div>
	<div class="floatl formats-qty" style="width: 440px; margin-left: 10px;">
	    <? if (count($formats) == 0) { ?>
    	    <strong>No hay formatos disponibles para este video.</strong>
	    <? } else if ($pb_mode) { ?>
    	    <strong>No se pueden elegir videos para los FotoLibros.</strong>
	    <? } else { ?>
    	    <strong>Ingrese la cantidad de copias según el formato </strong>
    	    <br /><br />

		<?
		$texto = getSiteInfo("textovideos");
		if (!empty($texto)) {
		    echo utf8_encode(nl2br($texto)) . "<br /><br />";
		}
		?>

    	    <table style="width: 100%;">
		    <?
		    //  saltea los formatos de videos que tienen sufijo, pero
		    //  no coinciden con este file
		    $parts = pathinfo($media);
		    $media_filename_noext = $parts['filename'];
		    foreach ($formats as $f) {
			if (($f->Sufijo != '') && !is_null($f->Sufijo)) {
			    $len = strlen($f->Sufijo);
			    $sufijo = substr($media_filename_noext, ($len * (-1)));
			    if (strtolower($sufijo) != strtolower($f->Sufijo))
				continue;
			}
			?>
			<tr>
			    <td style="font-weight:bold; font-size: 14px; padding-right: 8px;white-space: nowrap"><?= $f->nombre ?></td>
			    <td style="width: 100%;font-weight:bold; text-align: left; font-size: 14px;white-space: nowrap">$ <?= MaxterHlp::fn($f->precio) ?> c/u</td>
			</tr>
			<tr>
			    <td></td>
			    <td style="white-space: nowrap">
				<input type="hidden" name="formats[]" value="<?= $f->id ?>" />

				<a href="javascript:;" class="steeper-button" id="remove_<?= $f->id ?>">-</a>
				<input class="steeper-label" type="text" id="qty_<?= $f->id ?>" name="f<?= $f->id ?>" />
				<a href="javascript:;" class="steeper-button" id="add_<?= $f->id ?>">+</a>
			    </td>
			</tr>
			<tr>
			    <td colspan="2" style="height: 10px; border-bottom: 1px solid #ccc;"></td>
			</tr>
			<tr>
			    <td colspan="2" style="height: 10px;"></td>
			</tr>
		    <? } ?>
    		<tr>
    		    <td></td>
    		    <td><a href="javascript:agregarVideo();" class="bigorange" style="float: left; margin-top: 20px; margin-left: 25px;" id="continuarbtn">AGREGAR &rarr;</a></td>
    		</tr>
    	    </table>
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

    function agregarVideo() {
		var valid = false;
		$('.steeper-label').each(function (index) {
			if ($(this).val() != 0) {
			valid = true;
			return;
			}
		});

		if (valid) {
			$('#order-frm').submit();
		} else {
			showAlert("Error", "Debe seleccionar algun formato.")
		}
    }


    $('video,audio').mediaelementplayer({
	pluginPath: 'static/css/mediaelement/',
	//features: [],
	success: function (mediaElement, domObject) {

	    mediaElement.play();

	    CurrentTimeOut = setTimeout(function () {
		try {
		    mediaElement.pause();
		    CurrentTimeOut = 0;
		} catch (exception) {
		    if (console) {
			console.debug(exception);
		    }
		}
		$("#videoplayercontainer").html('&nbsp;').css('background', 'url("static/img/video_icon_thumb.png") center center no-repeat');
	    }, <?= VIDEO_MAX_PLAY_SECONDS ?> * 1000);
	}
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

    $('#order-frm').ajaxForm({target: "#view-media"});

<? foreach ($formats as $f) { ?>
        steeper(<?= $f->id ?>);
<? } ?>

    $('#view-media').dialog({
	title: "Encargar copias:",
	width:<?= VIEW_WINDOW_W ?>,
	height:<?= VIEW_WINDOW_H ?>,
	position: [25, 25],
	close: function () {
	    if (CurrentTimeOut != 0) {
		clearTimeout(CurrentTimeOut);
		CurrentTimeOut = 0;
	    }
		
		try {
			//$("#videoplayer").pause();
			$("#videoplayer source").prop('src', '');
			$("#videoplayer").empty().remove();
		} catch (err) {
			console.debug(err);
		}
		
	    $('#view-media').html("");
	},
	buttons: {
	    "Agregar a pedido": function () {
			agregarVideo();
	    },
	    "Cerrar": function () {
			$(this).dialog("close");
			$('#view-media').html("");
	    }
	}
    });

    if (show_dialog == 1) {
	$('#dlg-added').show();
	setTimeout(function () {
	    $('#dlg-added').hide();
	    show_dialog = 0;
	}, 1000);
    }

    /* setTimeout(function(){
     $('#view-media').dialog({width:"auto", 
     height:"auto",
     position:["center", "center"]
     });
     }, 100); */
</script>
