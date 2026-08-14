<?php

$fids = Http::getOverPost('formats');
$media = Http::getOverPost('m');

$imager = array();
$imager['name'] = $media;
$imager['type'] = 'vid';

$add = false;

//  initialize presu	---------------------------
$presu_mode = Presu::getID() != -1;


foreach ($fids as $fid) {
    $fq = Http::getOverPost('f' . $fid);
    if ($fq > 0) {
	$add = true;
	//$presu_total_to_add += $fq;
	$imager['formats'][] = array(
	    'format_id' => $fid,
	    'qty' => $fq
	);
    }
}


if ($presu_mode) {



    //	check if the video can be added to the presu
    foreach ($imager['formats'] as $f) {
	$format_id = $f['format_id'];
	$videos_in_cart = Presu::getQtyVideoFormatsInCart($format_id);
	$max_videos = Presu::getQtyVideoFormat($format_id);
	
	//  si agrego lo que pide van a haber mas videos que los permitidos
	if ($videos_in_cart + $f['qty'] > $max_videos) {
	    $media = str_replace(PATH_ORIGINALS_ROOT, '', $media);
	    $media = base64_encode(utf8_encode($media));
	    echo '<script type="text/javascript" charset="utf-8">
		    alert("Se exeder\u00E1 la cantidad de videos permitidas en el presupuesto.");
		    show_dialog = 0;
		    $("#view-media").dialog("close");
		    openViewMedia(\'' . $media . '\');
		    </script>';
	    die;
	}
	/* MaxterHlp::d($videos_in_cart);
	  MaxterHlp::d($max_videos);
	  MaxterHlp::d($_SESSION); */
    }

    //die;

    /* if ($files_in_pedido + $presu_total_to_add > Presu::$max_vid) {

      $media = str_replace(PATH_ORIGINALS_ROOT, '', $media);
      $media = base64_encode(utf8_encode($media));
      echo '<script type="text/javascript" charset="utf-8">
      alert("Se exeder\u00E1 la cantidad de videos permitidas en el presupuesto.");
      show_dialog = 0;
      $("#view-media").dialog("close");
      openViewMedia(\'' . $media . '\');
      </script>';
      die;
      } */
}


/*
  $tmp = Presu::getCartNumbers();
  $files_in_pedido = $tmp['vid'];

  $presu_total_to_add = 0;
  foreach ($fids as $fid) {
  $fq = Http::getOverPost('f' . $fid);
  if ($fq > 0) {
  $add = true;
  $presu_total_to_add += $fq;
  $imager['formats'][] = array(
  'format_id' => $fid,
  'qty' => $fq
  );
  }
  }
 */

if ($add) {
    SessionManager::pushValue('pedido', $imager);
}

if ($presu_mode) {
    Presu::save();
}

$prev_next = File::getNextPrevFile($media);

$prev = str_replace(PATH_ORIGINALS_ROOT, '', $prev_next['prev']);
$next = str_replace(PATH_ORIGINALS_ROOT, '', $prev_next['next']);

$prev = base64_encode(utf8_encode($prev));
$next = base64_encode(utf8_encode($next));

echo '<script type="text/javascript" charset="utf-8"> 
	refreshCart();
	//$("#view-media").dialog("close"); 
	show_dialog = 1;
	openViewMedia(\'' . $next . '\');
</script>';
die;
