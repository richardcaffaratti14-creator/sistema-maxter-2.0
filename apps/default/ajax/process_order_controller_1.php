<?php
set_time_limit(15 * 60);

//sleep(5);

$n = Http::getOverPost('nombre');
$a = Http::getOverPost('apellido');
$t = Http::getOverPost('tel');

$preciototal = 0;
$desc = '';
$ped = SessionManager::getValue('pedido');

//	get data to generate order
foreach ($ped as $item) {
	foreach ($item['formats'] as $f) {

		if ($item['type'] == 'vid') {
			$format = new formato_video();
		} else {
			$format = new formato_imagen();
		}

		$format->get($f['format_id']);
		$sub = ((int) $f['qty'] * $format->precio);
		$preciototal += $sub;
		$desc .= $item['name'] . ' [' . $format->nombre . ' x ' . $f['qty'] . ' copias = ' . $sub . ']' . "\n";
	}
}

//	save order
$pedido = new pedidos();
$pedido->nombre = $n;
$pedido->apellido = $a;
$pedido->telefono = $t;
$pedido->descripcion = $desc;
$pedido->total = $preciototal;
$pedido->estado = "0";
$pedido_id = $pedido->save();

//Dump::dl($pedido_id);
//	generate order
$order_path = PATH_IMAGES_ROOT . PATH_ORDERS . $pedido_id . '/';
$source_path = PATH_IMAGES_ROOT . PATH_ORIGINALS;
File::mkdirs($order_path);

$pidx = 0;
foreach ($ped as $item) {
	$fidx = 0;
	foreach ($item['formats'] as $f) {

		if ($item['type'] == 'vid') {
			$format = new formato_video();
		} else {
			$format = new formato_imagen();
		}
		$format->get($f['format_id']);

		if ($item['type'] == 'vid') {

			//	video -> lo copio directamente a la carpeta del formato	--------------------------
			$tmp_vid_name = str_replace('.mp4', '.' . VIDEO_HQ_EXT, $item['name']);
			$tmp_src = $source_path . $item['name'];
			File::mkdirs($order_path . $format->carpeta . '/');
			$tmp_dst = $order_path . $format->carpeta . '/' . $tmp_vid_name;
			$tmp_src = str_replace('//', '/', $tmp_src);
			$tmp_dst = str_replace('//', '/', $tmp_dst);

			for ($i = 0; $i < $f['qty']; $i++) {
				copy($tmp_src, File::getNextAvailableFileName($tmp_dst));
			}
		} else {

			if ($f['processed'] != 1) {
				continue;
			}

			//	imagen -> a procesarla!!!!!	---------------------------------------------------------
			$tmp_src = $source_path . $item['name'];
			$tmp_src = str_replace('//', '/', $tmp_src);

			File::mkdirs($order_path . $format->carpeta . '/');
			$tmp_dst = $order_path . $format->carpeta . '/';
			$tmp_dst = str_replace('//', '/', $tmp_dst);

			//Dump::dl($tmp_src);
			//die;
			//	xRot = xCenter + cos(Angle) * (x - xCenter) - sin(Angle) * (y - yCenter)
			//	yRot = yCenter + sin(Angle) * (x - xCenter) + cos(Angle) * (y - yCenter)

			$img = WideImage::load($tmp_src);

			$imgw = $img->getWidth();
			$imgh = $img->getHeight();
			
			

			//	crop
			$ox1 = Utils::getValueBetweenRanges(
							$f['data']['crop']['x1'], array(0, $f['data']['crop']['vw']), array(0, $imgw)
			);
			$oy1 = Utils::getValueBetweenRanges(
							$f['data']['crop']['y1'], array(0, $f['data']['crop']['vh']), array(0, $imgh)
			);

			$ox2 = Utils::getValueBetweenRanges(
							$f['data']['crop']['x2'], array(0, $f['data']['crop']['vw']), array(0, $imgw)
			);
			$oy2 = Utils::getValueBetweenRanges(
							$f['data']['crop']['y2'], array(0, $f['data']['crop']['vh']), array(0, $imgh)
			);

			$dstw = $ox2 - $ox1;
			$dsth = $oy2 - $oy1;

			$img = $img->crop($ox1, $oy1, $dstw, $dsth);

			//	brightness & contrast
			//Dump::dl($f['data']['brightness'] . " : " . $f['data']['contrast']);
			//$img->bc($f['data']['brightness'], $f['data']['contrast']);

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


			$brightness = $f['data']['brightness'] == '' ? 0 : $f['data']['brightness'];
			$contrast = $f['data']['contrast'] == '' ? 0 : $f['data']['contrast'];

			$img = $img->applyFilter(IMG_FILTER_BRIGHTNESS, $brightness);
			$img = $img->applyFilter(IMG_FILTER_CONTRAST, $contrast_values[$contrast]);

			//	apply frame
			if ($f['data']['frame'] != '') {

				$tmp = substr($f['data']['frame'], strpos($f['data']['frame'], '?') + 1);
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
				$frame->saveToFile($tmp_dst . 'frame.png');
				$frame->destroy();
			}

			//	save processed image
			for ($i = 0; $i < $f['qty']; $i++) {
				$img->saveToFile(File::getNextAvailableFileName($tmp_dst . basename($item['name'])));
			}
			$img->destroy();
		}
	}
}

SessionManager::unsetValue('pedido');
?>
<h3>Su pedido es el #: <strong><?= $pedido_id ?></strong></h3>
<script>
	refreshCart();
	//$("#view-media").dialog("close");
</script>