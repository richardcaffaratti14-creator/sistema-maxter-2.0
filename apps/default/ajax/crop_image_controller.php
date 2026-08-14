<?

function sl($txt) {
    SessionManager::pushValue('__log__', $txt);
}

$fidx = Http::getOverPost('f');
$pid = Http::getOverPost('p');

$es_digital = Http::getOverPost('digital') == '1';

$pedido_db = new pedidos();
$pedido_db->get($pid);

$pedido = unserialize($pedido_db->pedido);

$media = $pedido[$fidx]['name'];
$notas = $pedido[$fidx]['formats'][0]['note'];
$qtySelected = $pedido[$fidx]['formats'][0]['qty'];

//echo "<xmp>";
//print_r($pedido[$fidx]); 
//echo "</xmp>";

$es_coreo = $pedido[$fidx]['type'] == 'coreo';

if ($es_coreo)
    $format = new formato_coreo();
else
    $format = new formato_imagen();

$format->get($pedido[$fidx]['formats'][0]['format_id']);


if (!$format->isAvailable()) {
    die("FORMATO NO DISPONIBLE");
}



if ($es_coreo) {
    //coreos copian toda la carpeta completa
    $order_path = PATH_IMAGES_ROOT . PATH_ORDERS . $pid . '/';
    File::mkdirs($order_path);

    File::mkdirs($order_path . $format->carpeta . '/');
    $tmp_dst = $order_path . $format->carpeta . '/';
    $tmp_dst = str_replace('//', '/', $tmp_dst);

    $folder = folderToFilename($media);
    $tmp_dst = $tmp_dst . $folder . "/";
    File::mkdirs($tmp_dst);

    $tmp_src = PATH_IMAGES_ROOT . PATH_ORIGINALS . $media . "/";

    foreach (glob($tmp_src . "*.jpg") as $f) {
	$parts = pathinfo($f);
	if (!file_exists($tmp_dst . $parts['basename']))
	    //@copy($f, $tmp_dst . $parts['basename']);
	    File::docopy($f, $tmp_dst . $parts['basename']);
    }
    foreach (glob($tmp_src . "*.JPG") as $f) {
	$parts = pathinfo($f);
	if (!file_exists($tmp_dst . $parts['basename']))
	    //@copy($f, $tmp_dst . $parts['basename']);
	    File::docopy($f, $tmp_dst . $parts['basename']);
    }
    ?>
    <script type="text/javascript">
        $('#pedido-thumb-<?= $fidx ?> ._processing_loading').hide();
        $('#pedido-thumb-<?= $fidx ?>').addClass('procesadook');
    </script>
    <?
    die();
}

//si es formato digital NO procesar, solo copiar luego de la orientación
if ($es_digital) {
    $order_path = PATH_IMAGES_ROOT . PATH_ORDERS . $pid . '/';
    File::mkdirs($order_path);

    $tmp_dst = $order_path . $format->carpeta . '/';
    //	es un fotolibro, asi que le cambio el nombre de la carpeta
    if (is_numeric($pedido_db->idFotolibro)) {
	$tmp_dst = $order_path . 'fotolibro/';
    }

    File::mkdirs($tmp_dst);
    $tmp_dst = str_replace('//', '/', $tmp_dst);

    $parts = pathinfo($media);
    $dirname = $parts['dirname'];
    $filename = folderToFilename($dirname) . $parts['basename']; //add folder to the filename
    //	saco esto para que no reemplaze el segundo archivo 
    /*
      if (file_exists($tmp_dst . $filename))
      @unlink($tmp_dst . $filename);
     */

    //	si la foto esta, la renombro como nombre-1.jpg
    $num = 2;
    $tmp_file = $filename;
    while (file_exists($tmp_dst . $filename)) {
	$partes_ruta = pathinfo($tmp_file);

	echo $partes_ruta['filename'], "\n";
	echo $partes_ruta['extension'], "\n";
	$filename = $partes_ruta['filename'] . "-$num." . $partes_ruta['extension'];
	$num++;
	//die($filename);
    }


    if (!is_dir(dirname($tmp_dst)))
	File::mkdirs(dirname($tmp_dst));

    //copy image
    //@copy(utf8_decode(PATH_IMAGES_ROOT . PATH_ORIGINALS . $media), $tmp_dst . $filename);
    File::docopy(utf8_decode(PATH_IMAGES_ROOT . PATH_ORIGINALS . $media), $tmp_dst . $filename);

    //	save copies
    $tmp = pathinfo($media);
    for ($i = 1; $i < (int) $pedido[$fidx]['formats'][0]['qty']; $i++) {
		$filenameCopy = $tmp['filename'] . "-{$i}." . $tmp['extension'];
		if (file_exists($tmp_dst . $filenameCopy))
			@unlink($tmp_dst . $filenameCopy);
		//@copy(utf8_decode(PATH_IMAGES_ROOT . PATH_ORIGINALS . $media), $tmp_dst . $filenameCopy);
		File::docopy(utf8_decode(PATH_IMAGES_ROOT . PATH_ORIGINALS . $media), $tmp_dst . $filenameCopy);
    }
    ?>
    <script type="text/javascript">
        $('#pedido-thumb-<?= $fidx ?> ._processing_loading').hide();
        $('#pedido-thumb-<?= $fidx ?>').addClass('procesadook');
    </script>
    <?
    die();
}




$cropFname = Img::crop(utf8_decode(PATH_THUMBS . PATH_ORIGINALS . $media), CROP_SIZE);
if (!file_exists($cropFname)) {
    $tmp = explode("x", CROP_SIZE);
    $image_width = $tmp[0];
    $image_height = $tmp[1];

    $image_orig = utf8_decode(PATH_IMAGES_ROOT . PATH_ORIGINALS . $media);
    $image_to_crop = Img::crop(utf8_decode(PATH_THUMBS . PATH_ORIGINALS . $media), CROP_SIZE);

    $exif = exif_read_data($image_orig);
    $ort = $exif['Orientation'];

    if ($ort == 6 || $ort == 8) {
	$image_width = $tmp[1];
	$image_height = $tmp[0];
    }

    $thumb = WideImage::load($image_orig);
    $thumb = $thumb->resize($image_width, $image_height);
    File::mkdirs(utf8_decode($IMAGESAPP_CACHE . $image_path), 0777);

//	$exif = exif_read_data($image_to_crop);
//	$ort = $exif['Orientation'];

    $thumb = $thumb->exifOrient($ort);
    $thumb->saveToFile(utf8_decode($cropFname));
    unset($thumb);
}
list($vimg_w, $vimg_h, $tipo, $atributos) = getimagesize($cropFname);


$max = max(array($format->alto, $format->ancho));
$min = min(array($format->alto, $format->ancho));



//setSelect:   [ 100, 100, 50, 50 ],
$setSelectMade = false;
$ratioSelected = 'H';
if ($format->alto == "0" || $format->ancho == "0") {
    $ratio = "0";
    $ratioH = $ratioV = "0";
    $setSelect = "[0,0,$vimg_w, $vimg_h]";
    $setSelectMade = true;
    $selection_h = $vimg_h;
    $selection_w = $vimg_w;
} else if ($vimg_w > $vimg_h) {
    $ratio = $ratioH = $max / $min;
    $ratioV = $min / $max;
    $ratioSelected = 'H';
} else {
    $ratio = $ratioV = $min / $max;
    $ratioH = $max / $min;
    $ratioSelected = 'V';
}
$preview_ratio = .7;

if (!$setSelectMade) {

    if ($vimg_h > $vimg_w) {
	$selection_h = $vimg_h;
	$selection_w = ($min * $selection_h) / $max;

	if ($selection_w > $vimg_w) {
	    $selection_w = $vimg_w;
	    $selection_h = ($max * $selection_w) / $min;
	}
    } else {
	$selection_w = $vimg_w;
	$selection_h = ($min * $selection_w) / $max;

	if ($selection_h > $vimg_h) {
	    $selection_h = $vimg_h;
	    $selection_w = ($max * $selection_h) / $min;
	}
    }

    if ($selection_h == $vimg_h) {

	//	sobra por costado
	$rest = ($vimg_w - $selection_w) / 2;
	$x1 = $rest;
	$y1 = 0;
	$x2 = $vimg_w - ($x1 * 2);
	$y2 = $vimg_h;
    } else {

	//	sobra por abajo
	$rest = ($vimg_h - $selection_h) / 2;
	$x1 = 0;
	$y1 = $rest;
	$x2 = $vimg_w;
	$y2 = $vimg_h - ($rest * 2);
    }

    $setSelect = "[$x1,$y1,$x2,$y2]";
}

//	frames
if ($vimg_w > $vimg_h) {
    //	landscape
    $frame_layout = 'l';
} else {
    //	portrait
    $frame_layout = 'p';
}

$frames_all = glob(PATH_IMAGES_ROOT . PATH_FRAMES . $format->carpeta . '/*.png');
$frames = array('l' => array(), 'p' => array()); //landscape and portraits frames
foreach ($frames_all as $frame_one) {
    list($frame_w, $frame_h, $tipo, $atributos) = getimagesize($frame_one);

    if ($frame_w > $frame_h) {
	$frames['l'][] = $frame_one;
    } elseif ($frame_w < $frame_h) {
	$frames['p'][] = $frame_one;
    }
}
?>
<form id="modify-frm"  action="<?= App::getApplicationUrl() ?>ajax/crop_image_final" method="post">
    <input type="hidden" name="process" value="1" />
    <input type="hidden" name="p" value="<?= $pid ?>" />
    <input type="hidden" name="f" value="<?= $fidx ?>" />
    <input type="hidden" name="m" value="<?= $media ?>" />
    <input type="hidden" name="hash" value="<?= $hash ?>" />
    <input type="hidden" name="format_id" value="<?= $format->id ?>" />
    <div style="width: <?= VIEW_WINDOW_W - 50 ?>px; ">
	<h3><? if ($qtySelected) { ?><?= $qtySelected ?> COPIA<?= $qtySelected > 1 ? "S" : "" ?> - <? } ?><?= /* basename($media) . " | " . */$format->nombre ?></h3>
	<div class="floatl">
	    <img id="imgcrop" src="<?= Img::crop('images/' . PATH_ORIGINALS . $media, CROP_SIZE) ?>" />
	    <? if ((int) $format->alto != 0 || (int) $format->ancho != 0) { ?>
    	    <br/>
    	    <a href="javascript:void(0)" data-val="<?= $ratioH ?>" id="ratioh" style="width: 30px; height: 20px; border: 1px dashed #999; background-color: #ccc; display: inline-block"></a>&nbsp;
    	    <a href="javascript:void(0)" data-val="<?= $ratioV ?>" id="ratiov" style="width: 12px; height: 20px; border: 1px dashed #999; background-color: #ccc; display: inline-block"></a>
	    <? } ?>
	</div>
	<div class="floatl formats-qty formats-qty-crop">
	    <div style="width:<?= $selection_w * $preview_ratio ?>px;height:<?= $selection_h * $preview_ratio ?>px;overflow:hidden;margin-left:5px;" id="preview-container">
		<img src="" id="frame-img" style="position:absolute; display: none; width:<?= $selection_w * $preview_ratio ?>px;height:<?= $selection_h * $preview_ratio ?>px;" />
		<img src="<?= Img::crop('images/' . PATH_ORIGINALS . $media, VIEW_SIZE) ?>" id="preview">
	    </div>
	    <input type="hidden" id="x" name="x1" /> <input type="hidden" id="y" name="y1" />
	    <input type="hidden" id="x2" name="x2" /> <input type="hidden" id="y2" name="y2" />
	    <input type="hidden" name="view_w" value="<?= $vimg_w ?>" />
	    <input type="hidden" name="view_h" value="<?= $vimg_h ?>" />
	    <input type="hidden" name="frame" value="" id="frame" />

	    <input type="hidden" id="brightness" name="brightness" />
	    <input type="hidden" id="contrast" name="contrast" />

	    <div id="image-effects">
		<strong>Brillo:</strong><br /><br /><div id="bright-slider"></div><br /><br />
		<strong>Contraste:</strong><br /><br /><div id="contrast-slider"></div>
		<br />
		<div class="floatr" style="display: none; padding-top: 25px; padding-right: 30px;" id="loadingcropping"><img src="static/img/loader.gif"/></div>
		<a href="javascript:sendModifications();" class="bigorange" id="continuarbtn">CONFIRMAR &rarr;</a>
		<a class="orange-button" href="javascript:;" id="reset" style="float: left; margin-top: 20px; ">Restablecer</a>

	    </div>

	</div>

	<? if (count($frames['l']) || count($frames['p'])) { ?>
    	<div id="frames" style="margin-right: 20px;">
    	    <strong>Marcos:</strong><br />

    	    <div id="frames_l" style="<?= $frame_layout == 'l' ? '' : 'display:none' ?>">
		    <?
		    foreach ($frames['l'] as $frame) {
			$frame = str_replace(PATH_IMAGES_ROOT, '', $frame);
			?>
			<div class="frame-bg">
			    <a href="javascript:setFrame('images/frame_preview?fid=<?= $format->id ?>&name=<?= $frame ?>&lay=l');">
				<img class="floatl" src="images/frame_mini?fid=<?= $format->id ?>&name=<?= $frame ?>&lay=l" />
			    </a>
			</div>
		    <? } ?>
    		<div class="clearb"></div>
    	    </div>

    	    <div id="frames_p" style="<?= $frame_layout == 'p' ? '' : 'display:none' ?>">
		    <?
		    foreach ($frames['p'] as $frame) {
			$frame = str_replace(PATH_IMAGES_ROOT, '', $frame);
			?>
			<div class="frame-bg">
			    <a href="javascript:setFrame('images/frame_preview?fid=<?= $format->id ?>&name=<?= $frame ?>&lay=p');">
				<img class="floatl" src="images/frame_mini?fid=<?= $format->id ?>&name=<?= $frame ?>&lay=p" />
			    </a>
			</div>
		    <? } ?>
    		<div class="clearb"></div>
    	    </div>

    	    <a class="orange-button" style="margin-left: 5px;" href="javascript:setFrame('');" id="reset">Quitar marco</a>
    	</div>
	<? } ?>
	<div class="floatl">
	    <strong>Notas:</strong></br>
	    <?= nl2br($notas) ?>
	</div>
	<div class="clearb"></div>
	<div class="clearb"></div>
    </div>
</form>

<script type="text/javascript" charset="utf-8">


    function sendModifications() {
	var w = parseInt($('#x2').val()) - parseInt($('#x').val());
	var h = parseInt($('#y2').val()) - parseInt($('#y').val());

	if (w < 15 || h < 15) {
	    showAlert("Error", "Por favor seleccione un area más grande de la imagen.");
	    return;
	}

	$("#continuarbtn").hide();
	$("#loadingcropping").show();

	$('#modify-frm').submit();
    }

    function setFrame(image) {
	if (image == '') {
	    $('#frame-img').hide();
	    $('#frame').val('');
	    $("#frames .frame-bg a").removeClass("frameon");
	} else {
	    $('#frame-img').attr('src', image);
	    $('#frame-img').show();
	    $('#frame').val(image);
	}
    }

    function showCoords(c)
    {
	if (typeof c == "undefined") {
	    return;
	}
	$('#x').val(c.x);
	$('#y').val(c.y);
	$('#x2').val(c.x2);
	$('#y2').val(c.y2);
	$('#w').val(c.w);
	$('#h').val(c.h);

	showPreview(c);
    }
    ;


    var currentCropOrient = initialCropOrient = '<?= $ratioSelected ?>';
    var currentPreviewW = '<?= $selection_w ?>';
    var currentPreviewH = '<?= $selection_h ?>';

    var rx = ry = 0;
    var previewX = <?= $selection_w * $preview_ratio ?>;
    var previewY = <?= $selection_h * $preview_ratio ?>;

    function showPreview(coords)
    {
	if (typeof coords == "undefined") {
	    return;
	}
	rx = currentPreviewW * <?= $preview_ratio ?> / coords.w;
	ry = currentPreviewH * <?= $preview_ratio ?> / coords.h;

	$('#preview').css({
	    width: Math.round(rx * <?= $vimg_w ?>) + 'px',
	    height: Math.round(ry * <?= $vimg_h ?>) + 'px',
	    marginLeft: '-' + Math.round(rx * coords.x) + 'px',
	    marginTop: '-' + Math.round(ry * coords.y) + 'px'
	});
    }

    function applyEffects() {
	var brillo = $('#bright-slider').slider("option", "value");
	var contraste = $('#contrast-slider').slider("option", "value");
	resetEffects(false);
	$('#preview').pixastic("brightness", {brightness: brillo, contrast: contraste, legacy: true});
	getSliderValues();
	$('#brightness').val(brillo);
	$('#contrast').val(contraste);
    }

    function realResetEffects() {
	var img = document.getElementById("preview");
	Pixastic.revert(img);
    }

    function resetEffects(updateSliders) {
	if (updateSliders) {
	    $('#bright-slider').slider({change: null});
	    $('#contrast-slider').slider({change: null});

	    $('#bright-slider').slider({value: 0});
	    $('#contrast-slider').slider({value: 0});

	    $('#bright-slider').slider({change: function (event, ui) {
		    applyEffects();
		}});
	    $('#contrast-slider').slider({change: function (event, ui) {
		    applyEffects();
		}});
	}
	realResetEffects();
    }

    function getSliderValues() {
	var brillo = $('#bright-slider').slider("option", "value");
	var contraste = $('#contrast-slider').slider("option", "value");
    }


    $('#bright-slider').slider({
	change: function (event, ui) {
	    applyEffects();
	},
	min: -150,
	max: 150,
	value: 0
    });
    $('#contrast-slider').slider({
	change: function (event, ui) {
	    applyEffects();
	},
	min: -1,
	max: 1,
	step: 0.1,
	value: 0
    });


    $("#frames .frame-bg a").click(function () {
	$("#frames .frame-bg a").removeClass("frameon");
	$(this).addClass("frameon");
    });


    $('#reset').click(function () {
	resetEffects(true);
    });

    $('#modify-frm').ajaxForm({target: "#crop-media"});

    /*
     $('#view-media').dialog({
     title:"Encargar copias",
     width:'<?= VIEW_WINDOW_W ?>', 
     height:"auto",
     position:["center", "center"],
     buttons: { 
     "Agregar al Pedido":sendModifications,
     "Cerrar":function() { $(this).dialog("close"); }
     }
     });
     */
    var jcrop_api;
    function initJCrop(ratio) {
	$('#imgcrop').Jcrop({
	    onSelect: showCoords,
	    onChange: showCoords,
	    bgColor: 'black',
	    bgOpacity: .4,
	    setSelect: <?= $setSelect ?>,
	    aspectRatio: ratio
	}, function () {
	    jcrop_api = this;
	});
    }

    initJCrop(<?= $ratio ?>);


    $("#ratioh").click(function () {
	if (currentCropOrient == 'V') {
	    currentCropOrient = 'H';
	    var tmp = currentPreviewW;
	    currentPreviewW = currentPreviewH;
	    currentPreviewH = tmp;

	    var newW = previewX;

	    if (initialCropOrient == 'V') {
		var tmp = newW * parseFloat($(this).attr('data-val'));
		var newH = newW;
		newW = tmp;
	    } else
		var newH = newW / parseFloat($(this).attr('data-val'));

	    $("#preview-container").css('width', newW + 'px');
	    $("#preview-container").css('height', newH + 'px');

	    $("#frame-img").css('width', newW + 'px');
	    $("#frame-img").css('height', newH + 'px');

	    $('#crop-media').dialog({position: ["center", "center"]});
	}
	setFrame('');
	$("#frames_p").hide();
	$("#frames_l").show();

	jcrop_api.destroy();
	initJCrop(parseFloat($(this).attr('data-val')))
    });
    $("#ratiov").click(function () {
	if (currentCropOrient == 'H') {
	    currentCropOrient = 'V';
	    var tmp = currentPreviewW;
	    currentPreviewW = currentPreviewH;
	    currentPreviewH = tmp;

	    var newW = previewX;

	    if (initialCropOrient == 'H') {
		var tmp = newW * parseFloat($(this).attr('data-val'));
		var newH = newW;
		newW = tmp;
	    } else
		var newH = newW / parseFloat($(this).attr('data-val'));

	    $("#preview-container").css('width', newW + 'px');
	    $("#preview-container").css('height', newH + 'px');

	    $("#frame-img").css('width', newW + 'px');
	    $("#frame-img").css('height', newH + 'px');

	    $('#crop-media').dialog({position: ["center", "center"]});
	}
	setFrame('');
	$("#frames_l").hide();
	$("#frames_p").show();

	jcrop_api.destroy();
	initJCrop(parseFloat($(this).attr('data-val')));
    });


</script>