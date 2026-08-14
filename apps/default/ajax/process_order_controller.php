<?php
set_time_limit(15 * 60);

//sleep(5);

$n = Http::getOverPost('nombre');
$a = Http::getOverPost('apellido');
$t = Http::getOverPost('tel');

$preciototal = 0;
$desc = '';
$desc_accesorios = '';
$ped = SessionManager::getValue('pedido');

//	get data to generate order
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
			}
			elseif ($item['type'] == 'coreo') {
				$format = new formato_coreo();
			} else {
				$format = new formato_imagen();
			}

			$format->get($f['format_id']);
			$sub = ((int) $f['qty'] * $format->precio);
			$preciototal += $sub;
			if ($item['type'] == 'coreo')
				$desc .= 'COREO [' . $format->nombre . ']: ' . $item['name'] . ' = $ ' . MaxterHlp::fn($sub) . "\n";
			else
				$desc .= $item['name'] . ' [' . $format->nombre . ' x ' . $f['qty'] . ' copias = $ ' . MaxterHlp::fn($sub) . ']' . "\n";
		}
	}
}

//Agregar detalle de accesorios
if (!empty($desc_accesorios)) {
	$desc .= "\n---- ACCESORIOS ----\n" . $desc_accesorios . "\n";
}

//	save order
$pedido = new pedidos();
$pedido->nombre = $n;
$pedido->apellido = $a;
$pedido->telefono = $t;
$pedido->descripcion = $desc;
$pedido->total = $preciototal;
$pedido->estado = "0";
$pedido->Evento = getSiteInfo('evento');
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
		}
		elseif ($item['type'] == 'coreo') {
			$format = new formato_coreo();
		} else {
			$format = new formato_imagen();
		}
		$format->get($f['format_id']);

		if ($item['type'] == 'vid') {

			//	video -> lo copio directamente a la carpeta del formato	--------------------------
			$tmp_src_vid_name = str_replace('.' . File::getExtension($item['name']), '.' . strtolower(VIDEO_HQ_EXT), $item['name']);
			$tmp_src = $source_path . $tmp_src_vid_name;
			$tmp_src = str_replace('//', '/', $tmp_src);
			$tmp_src = utf8_decode($tmp_src);

			if (!is_file($tmp_src)) {
				$tmp_src_vid_name = str_replace('.' . File::getExtension($item['name']), '.' . strtoupper(VIDEO_HQ_EXT), $item['name']);
				$tmp_src = $source_path . $tmp_src_vid_name;
				$tmp_src = str_replace('//', '/', $tmp_src);
				$tmp_src = utf8_decode($tmp_src);
			}

			if (!is_file($tmp_src)) {
				continue;
			}

			//Dump::dlp($tmp_src, is_file($tmp_src));
			//die;

			File::mkdirs($order_path . $format->carpeta . '/');
			$tmp_dst = $order_path . $format->carpeta . '/' . basename($tmp_src_vid_name);
			$tmp_dst = str_replace('//', '/', $tmp_dst);

			if (!is_dir($tmp_dst))
				File::mkdirs( $tmp_dst );
			
			for ($i = 0; $i < $f['qty']; $i++) {
				//copy($tmp_src, File::getNextAvailableFileName($tmp_dst));
				File::docopy($tmp_src, File::getNextAvailableFileName($tmp_dst));
			}
			/* */
		}
		elseif ($item['type'] == 'coreo') {
			
			if ($f['processed'] != 1) {
				continue;
			}
			
			$tmp_src = $source_path . $item['name'] . "/";
			$tmp_src = str_replace('//', '/', $tmp_src);
			$tmp_src = utf8_decode($tmp_src);

			File::mkdirs($order_path . $format->carpeta . '/');
			$tmp_dst = $order_path . $format->carpeta . '/';
			$tmp_dst = str_replace('//', '/', $tmp_dst);

			$folder = folderToFilename($item['name']);
			File::mkdirs($order_path . $format->carpeta . '/' . $folder);
			
			foreach (glob($tmp_src . "*.jpg") as $f) {
				//if (!file_exists($tmp_dst . $f)) @copy($tmp_src . $f, $tmp_dst . $f);
				if (!file_exists($tmp_dst . $f)) File::docopy($tmp_src . $f, $tmp_dst . $f);
			}
			foreach (glob($tmp_src . "*.JPG") as $f) {
				//if (!file_exists($tmp_dst . $f)) @copy($tmp_src . $f, $tmp_dst . $f);
				if (!file_exists($tmp_dst . $f)) File::docopy($tmp_src . $f, $tmp_dst . $f);
			}
			
		} else {

			if ($f['processed'] != 1) {
				continue;
			}

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
			$x1 = $f['data']['crop']['x1'];
			$y1 = $f['data']['crop']['y1'];

			$x2 = $f['data']['crop']['x2'];
			$y2 = $f['data']['crop']['y2'];

			$vw = $f['data']['crop']['vw'];
			$vh = $f['data']['crop']['vh'];

			//	get exif orientation
			$exif = exif_read_data($tmp_src);
			$ort = $exif['Orientation'];
			//Dump::dlp($imgw, $imgh, $x1, $y1, $x2, $y2);

			if ($ort == 6) {
				/*
				  $xCenter = 0;
				  $yCenter = $imgw;

				  $x1Rot = $xCenter + cos(90) * ($x1 - $xCenter) - sin(90) * ($y1 - $yCenter);
				  $y1Rot = $yCenter + sin(90) * ($x1 - $xCenter) + cos(90) * ($y1 - $yCenter);

				  $x2Rot = $xCenter + cos(90) * ($x2 - $xCenter) - sin(90) * ($y2 - $yCenter);
				  $y2Rot = $yCenter + sin(90) * ($x2 - $xCenter) + cos(90) * ($y2 - $yCenter);

				  $x1 = $x1Rot;
				  $y1 = $y1Rot;
				  $x2 = $x2Rot;
				  $y2 = $y2Rot;
				  /* */
				/*
				  $tmp = $y2;
				  $y2 = $x1;
				  $x1 = $y1;
				  $y1 = $x2;
				  $x2 = $tmp;
				  /* */
				/*
				  $tmps = $vw;
				  $vw = $vh;
				  $vh = $tmps;
				 * 
				 */


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
			/*
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
			 */
			$dstw = abs($ox2 - $ox1);
			$dsth = abs($oy2 - $oy1);

			//Dump::dlp($imgw, $imgh, $x1, $y1, $x2, $y2);
			//Dump::dlp($ox1, $oy1, $dstw, $dsth);

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
				//$frame->saveToFile($tmp_dst . 'frame.png');
				$frame->destroy();
			}

			if ($ort == 6) {
				$img = $img->rotate(-90);
			} elseif ($ort == 8){
				$img = $img->rotate(90);
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
	$("#view-media").dialog({
		buttons: {
//			"Cerrar":function() { $(this).dialog("close"); }
		}
	});
</script>