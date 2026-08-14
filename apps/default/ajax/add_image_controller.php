<?
function sl($txt) {
    SessionManager::pushValue('__log__', $txt);
}

//  initialize presu
$presu_mode = Presu::getID() != -1;

$tmp = Presu::getCartNumbers();
$files_in_pedido = $tmp['img'];

$fids = Http::getOverPost('formats');
$media = Http::getOverPost('m');
$hash = Http::getOverPost('hash');


//	PREV - NEXT
$prev_next = File::getNextPrevFile($media);

$prev = str_replace(PATH_ORIGINALS_ROOT, '', $prev_next['prev']);
$next = str_replace(PATH_ORIGINALS_ROOT, '', $prev_next['next']);

$prev = base64_encode(utf8_encode($prev));
$next = base64_encode(utf8_encode($next));
//	------------------------




$qtySelected = 0;
$fmtSelected = Http::getOverPost('sel_format');
//$hash = '';
// no tiene hash
if ($hash == '') {

    //	guardar formatos
    $imager = array();
    $imager['name'] = $media;
    $imager['type'] = 'img';

    $presu_total_to_add = 0;
    foreach ($fids as $fid) {
		$fq = Http::getOverPost('f' . $fid);
		if (($fq > 0) && ($fmtSelected == $fid)) {
			$qtySelected = $fq;
			$presu_total_to_add += $fq;
			$imager['formats'][] = array(
				'format_id' => $fid,
				'note' => Http::getOverPost('notas'),
				'qty' => $fq,
				'processed' => false
			);
		}
    }

	
/*---------------------*/
//$pedido = SessionManager::getValue('pedido');
//echo "<xmp>MEDIA: ";
//print_r($media); 
//echo "\n\nQ: ";
//print_r($qtySelected); 
//echo "\n\nFIDS: ";
//print_r($fids); 
//echo "\n\nSEL F: ";
//print_r(Http::getOverPost('sel_format')); 
//echo "\n\n";
//print_r($pedido); 
//echo "</xmp>";
//die();
/*---------------------*/
	
	
    if ($presu_mode) {
		if ($files_in_pedido + $presu_total_to_add > Presu::$max_img) {
			$media = str_replace(PATH_ORIGINALS_ROOT, '', $media);
			$media = base64_encode(utf8_encode($media));

			echo '<script type="text/javascript" charset="utf-8"> 
			alert("Se exeder\u00E1 la cantidad de im\u00E1genes permitidas en el presupuesto.");
			show_dialog = 0;
			$("#view-media").dialog("close");
			openViewMedia(\'' . $media . '\');
		</script>';
			die;
		}
    }
    //$hash = $imager['hash'] = md5(serialize($imager));
    $hash = $imager['hash'] = time();

    //	ya tenemos el hash creado
    $pedido = SessionManager::getValue('pedido');

    $nombre_found = false;
    if (is_array($pedido)) {
		foreach ($pedido as $oneimg_ix => $oneimg) {
			//if the image is already added with the same format, increase the qty
			if (($oneimg['name'] == $media) && ($oneimg['type'] == 'img')) {
				foreach ($oneimg['formats'] as $fix => $img_format) {
					if ($img_format['format_id'] == $fmtSelected) {
						$pedido[$oneimg_ix]['formats'][$fix]['qty'] += (int)$qtySelected;
						SessionManager::setValue('pedido', $pedido);
					    $nombre_found = true;
					}
				}
			}
		}
    }

    if (!$nombre_found) {
		SessionManager::pushValue('pedido', $imager);
    }


} else {

    $pedido = SessionManager::getValue('pedido');
    if (is_array($pedido)) {
		$has_error = true;

		//sl($media ." -- ". $hash);

		for ($i = 0; $i < count($pedido); $i++) {

			if ($pedido[$i]['name'] == $media && $pedido[$i]['hash'] == $hash) {
			$has_error = false;

			$format_to_update = SessionManager::getValue("pedido/$i/formats");
			$format_id = Http::getOverPost('format_id');
			for ($j = 0; $j < count($format_to_update); $j++) {
				if ($format_to_update[$j]['format_id'] == $format_id) {

				//	guardar en el pedido
				//	crop
				SessionManager::setValue("pedido/$i/formats/$j/data/crop/x1", Http::getOverPost('x1'));
				SessionManager::setValue("pedido/$i/formats/$j/data/crop/y1", Http::getOverPost('y1'));
				SessionManager::setValue("pedido/$i/formats/$j/data/crop/x2", Http::getOverPost('x2'));
				SessionManager::setValue("pedido/$i/formats/$j/data/crop/y2", Http::getOverPost('y2'));
				SessionManager::setValue("pedido/$i/formats/$j/data/crop/vw", Http::getOverPost('view_w'));
				SessionManager::setValue("pedido/$i/formats/$j/data/crop/vh", Http::getOverPost('view_h'));
				//	contrast brightness
				SessionManager::setValue("pedido/$i/formats/$j/data/brightness", Http::getOverPost('brightness'));
				SessionManager::setValue("pedido/$i/formats/$j/data/contrast", Http::getOverPost('contrast'));

				//	frame
				SessionManager::setValue("pedido/$i/formats/$j/data/frame", Http::getOverPost('frame'));

				//	actualizo el pedido en session
				SessionManager::setValue("pedido/$i/formats/$j/processed", true);
				}
			}

			$pedido = SessionManager::getValue('pedido');
			//	tomo la imagen del pedido
			$imager = $pedido[$i];

			break;
			}
		}
    } else {
	//	no esta en el pedido error
		die("SE HA PRODUCIDO UN ERROR POR FAVOR SELECCIONE LA IMAGEN NUEVAMENTE [1]");
    }

    if ($has_error) {
		die("SE HA PRODUCIDO UN ERROR POR FAVOR SELECCIONE LA IMAGEN NUEVAMENTE [2]");
    }
}

//	mando el primer formato no procesado que encuentre
$format = new formato_imagen();
foreach ($imager['formats'] as $f) {
    if ($f['processed'] === false) {
		$fid = $f['format_id'];
		$format->get($fid);

		if (!$format->isAvailable()) {
			die("SE HA PRODUCIDO UN ERROR POR FAVOR SELECCIONE LA IMAGEN NUEVAMENTE [3]");
		} else {
			break;
		}
    }
}

PhotoBook::save();

if (!$format->isAvailable()) {
    //	no hay mas formatos que modificar
    echo '<script type="text/javascript" charset="utf-8"> 
		refreshCart();
		//$("#view-media").dialog("close");
		show_dialog = 1;
		openViewMedia(\'' . $next . '\');
	</script>';
    die;
}

//	modificación para que no haga el crop
echo '<script type="text/javascript" charset="utf-8"> 
		refreshCart();
		//$("#view-media").dialog("close");
		show_dialog = 1;
		openViewMedia(\'' . $next . '\');
	</script>';
die;
