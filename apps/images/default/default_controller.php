<?php

//error_reporting(E_ALL);

$IMAGESAPP_PATH = PATH_IMAGES_ROOT;
$IMAGESAPP_CACHE = PATH_THUMBS;
$IMAGESAPP_QUALITY = 95;
$ALLOW_ANLARGE = false;



//	----------------------------------------------------------------------------
$image_requested = App::getUrlPart(3);

//echo $image_requested;

$image_path = str_replace(basename($image_requested), '', $image_requested);
preg_match_all('/(.*?)_(\d*+x\d*+)(.*+)/s', basename($image_requested), $result, PREG_PATTERN_ORDER);

$image_name = $result[1][0];
$image_size = $result[2][0];
$image_ext = $result[3][0];

$inf = 99999999;

$tmp = explode("x", $image_size);
$image_width = $tmp[0];
$image_height = $tmp[1];



if ($image_width == '' && $image_height == '') {
    header("HTTP/1.0 404 Not Found");
    Log::l("IMAGES SIZE NOT FOUND: " . $image_requested);
} else {



//	----------------------------------------------------------------------------
//	----------------------------------------------------------------------------

    $image_to_crop = utf8_decode($IMAGESAPP_PATH . $image_path . $image_name . $image_ext);

    $tmp_filepath = utf8_decode($IMAGESAPP_CACHE . $image_requested);

	session_write_close();

    if (is_file($tmp_filepath)) { // check if in cache exists
		header("Content-Type: image/jpeg");
		header("Cache-Control: max-age=21600", true );	//6 hours of cache
		header("Expires: " . gmdate("D, d M Y H:i:s", time() + 21600) . " GMT", true);
		header_remove("Pragma");
		header("Cache-Control: max-age=21600", true );	//6 hours of cache
		readfile($tmp_filepath);
    } else {
		$exif = exif_read_data($image_to_crop);
		$ort = $exif['Orientation'];

		if ($ort == 6 || $ort == 8) {
			$image_width = $tmp[1];
			$image_height = $tmp[0];
		}

		//$thumb = PhpThumbFactory::create($image_to_crop);
		$thumb = WideImage::load($image_to_crop);
		$thumb = $thumb->resize($image_width, $image_height);
		File::mkdirs(utf8_decode($IMAGESAPP_CACHE . $image_path), 0777);

		//$exif = exif_read_data($image_to_crop);
		$ort = $exif['Orientation'];

		//Dump::dl($ort);
		//die;

		header( "Cache-Control: max-age=21600, public", true );	//6 hours of cache
		header("Expires: " . gmdate("D, d M Y H:i:s", time() + 21600) . " GMT", true);
		
		$thumb = $thumb->exifOrient($ort);
		$thumb->saveToFile(utf8_decode($IMAGESAPP_CACHE . $image_requested));
		$thumb->output('jpg');
		unset($thumb);
    }
	die();
}