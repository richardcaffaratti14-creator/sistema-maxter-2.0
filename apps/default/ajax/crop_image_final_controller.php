<?php
$fidx = Http::getOverPost('f');
$pid = $pedido_id = Http::getOverPost('p');

$pedido_db = new pedidos();
$pedido_db->get($pid);

$pedido = unserialize($pedido_db->pedido);

$format = new formato_imagen();
$format->get($pedido[$fidx]['formats'][0]['format_id']);

if (!$format->isAvailable()){
	die("FORMATO O IMAGEN NO DISPONIBLE");
}

$item = $pedido[$fidx];
if ($item['type'] == 'vid') {
	die("EDICIÓN DE VIDEO NO SOPORTADA");
}

$order_path = PATH_IMAGES_ROOT . PATH_ORDERS . $pedido_id . '/';
$source_path = PATH_IMAGES_ROOT . PATH_ORIGINALS;
File::mkdirs($order_path);


//	imagen -> a procesarla!!!!!	---------------------------------------------------------
$tmp_src = $source_path . $item['name'];
$tmp_src = str_replace('//', '/', $tmp_src);
$tmp_src = utf8_decode($tmp_src);

File::mkdirs($order_path . $format->carpeta . '/');
$tmp_dst = $order_path . $format->carpeta . '/';
$tmp_dst = str_replace('//', '/', $tmp_dst);

$img = WideImage::load($tmp_src);

$imgw = $img->getWidth();
$imgh = $img->getHeight();

//	crop
$x1 = Http::getOverPost('x1');
$y1 = Http::getOverPost('y1');

$x2 = Http::getOverPost('x2');
$y2 = Http::getOverPost('y2');

$vw = Http::getOverPost('view_w');
$vh = Http::getOverPost('view_h');

//	get exif orientation
$exif = exif_read_data($tmp_src);
$ort = $exif['Orientation'];
//Dump::dlp($imgw, $imgh, $x1, $y1, $x2, $y2);

if ($ort == 6) {
	$img = $img->rotate(90);
	$imgw = $img->getWidth();
	$imgh = $img->getHeight();
} else if ($ort == 8){
	$img = $img->rotate(-90);
	$imgw = $img->getWidth();
	$imgh = $img->getHeight();
}

$ox1 = Utils::getValueBetweenRanges(
				$x1, array(0, $vw), array(0, $imgw)
);
$oy1 = Utils::getValueBetweenRanges(
				$y1, array(0, $vh), array(0, $imgh)
);

$ox2 = Utils::getValueBetweenRanges(
				$x2, array(0, $vw), array(0, $imgw)
);
$oy2 = Utils::getValueBetweenRanges(
				$y2, array(0, $vh), array(0, $imgh)
);

$dstw = abs($ox2 - $ox1);
$dsth = abs($oy2 - $oy1);


$img = $img->crop($ox1, $oy1, $dstw, $dsth);

$contrast_values = array(
	"0" => 0,
	"0.1" => -1,
	"0.2" => -10,
	"0.3" => -15,
	"0.4" => -20,
	"0.5" => -25,
	"0.6" => -30,
	"0.7" => -32,
	"0.8" => -35,
	"0.9" => -42,
	"1" => -45,
	"-0.1" => 1,
	"-0.2" => 10,
	"-0.3" => 15,
	"-0.4" => 20,
	"-0.5" => 25,
	"-0.6" => 35,
	"-0.7" => 45,
	"-0.8" => 55,
	"-0.9" => 70,
	"-1" => 95,
);


$brightness = (int)Http::getOverPost('brightness');
$contrast = (int)Http::getOverPost('contrast');

if ($brightness != 0)
	$img = $img->applyFilter(IMG_FILTER_BRIGHTNESS, $brightness);

if ($contrast != 0)
	$img = $img->applyFilter(IMG_FILTER_CONTRAST, $contrast_values[$contrast]);

//	apply frame
if (Http::getOverPost('frame') != '') {

	$tmp = substr(Http::getOverPost('frame'), strpos(Http::getOverPost('frame'), '?') + 1);
	$frame_parts = array();
	parse_str($tmp, $frame_parts);
	$flay = $frame_parts['lay'];

	$frame = WideImage::load(PATH_IMAGES_ROOT . $frame_parts['name']);
	//$frame = $frame->resize($dstw, $dsth);
	if ($frame->getWidth() > $frame->getHeight()) {
		//	frame landscape
		if ($flay == 'p') {
			$frame = $frame->resize((int) $dsth + 1, (int) $dstw + 1);
			$frame = $frame->rotate(90);
		} else {
			$frame = $frame->resize((int) $dstw + 1, (int) $dsth + 1);
		}
	} else {
		//	frame portrait
		if ($flay == 'l') {
			$frame = $frame->resize((int) $dsth + 1, (int) $dstw + 1);
			$frame = $frame->rotate(90);
		} else {
			$frame = $frame->resize((int) $dstw + 1, (int) $dsth + 1);
		}
	}

	//$frame = $frame->resize((int) $dstw + 1, (int) $dsth + 1);						
	$img = $img->merge($frame);
	//$frame->saveToFile($tmp_dst . 'frame.png');
	$frame->destroy();
}

if ($ort == 6) {
	$img = $img->rotate(-90);
} elseif ($ort == 8){
	$img = $img->rotate(90);
}



//	save processed image
$parts = pathinfo($item['name']);
$dirname = $parts['dirname'];
$basename = folderToFilename($dirname) . $parts['basename'];	//add folder to the filename

$filename = $basename;

if (file_exists($tmp_dst . $filename))
	@unlink($tmp_dst . $filename);

if (!is_dir(dirname($tmp_dst . $filename)))
	File::mkdirs( dirname($tmp_dst . $filename) );
			

$img->saveToFile($tmp_dst . $filename);
$img->destroy();

//	save copies
$tmp = pathinfo($item['name']);
for ($i = 1; $i < (int)$pedido[$fidx]['formats'][0]['qty']; $i++) {
	$filenameCopy = $tmp['filename'] . "-{$i}." . $tmp['extension'];
	if (file_exists($tmp_dst . $filenameCopy)) @unlink($tmp_dst . $filenameCopy);
	//@copy($tmp_dst . $filename , $tmp_dst . $filenameCopy);
	File::docopy($tmp_dst . $filename , $tmp_dst . $filenameCopy);
}
?>

<script type="text/javascript">
	$('#crop-media').dialog('close');
	$('#pedido-thumb-<?=$fidx?>').addClass('procesadook');
</script>