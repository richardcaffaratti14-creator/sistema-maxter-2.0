<?php
//error_reporting(E_ALL);

$IMAGESAPP_PATH = PATH_IMAGES_ROOT;
$IMAGESAPP_CACHE = PATH_THUMBS;

session_write_close();

$folder_requested = App::getUrlPart(3);

$image_path = str_replace(basename($folder_requested), '', $folder_requested);
preg_match_all('/(.*?)_(\d*+x\d*+)(.*+)/s', basename($folder_requested), $result, PREG_PATTERN_ORDER);


$tmp_filepath = utf8_decode($IMAGESAPP_PATH . 'fotos/' . $folder_requested);
$tmp_cache_filepath = utf8_decode($IMAGESAPP_CACHE . 'fotos/' . $folder_requested);

if (!is_dir($tmp_filepath)) { // check if folder exists
	header("Content-Type: image/jpeg");
	readfile('static/img/nodisp.jpg');
	die();
}


$first_pic = '';
$opendir = opendir($tmp_filepath);
while (($file = readdir($opendir)) !== false) {
	if ($file !== '.' && $file !== '..' && !is_dir($file)) {
		//  agrego esto, si no me toma los .db o .ini o cualquier 
		//  huevada que pone windows
		$extension = strtolower(File::getExtension($file));
		if ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png' || $extension == 'gif') {
			$filename = str_replace('.', '_500x400.', basename($file));
			$first_pic = $tmp_cache_filepath . '/' . $filename;
			break;
		}
	}
}
closedir($opendir);



if (is_file($first_pic)) { // check if in cache exists
	header("Content-Type: image/jpeg");
	readfile($first_pic);
} else {
	header("Content-Type: image/jpeg");
	readfile('static/img/nodisp.jpg');
}
die();
