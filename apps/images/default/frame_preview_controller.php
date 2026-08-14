<?php

$IMAGESAPP_CACHE = PATH_THUMBS;
$DB = new SypDatabase(DB_USER, DB_PASS, DB_NAME);
//	----------------------------------------------------------------------------

$mw = VIEW_MAX_W;
$mh = VIEW_MAX_H;

$fid = Http::getOverPost('fid');
$name = Http::getOverPost('name');
$lay = Http::getOverPost('lay'); //	p->portrait // l->landscape

if ($fid == '') {
	Http::set404Header();
	die;
}
$format = new formato_imagen();
$format->get($fid);


$tmp_filepath = utf8_decode($IMAGESAPP_CACHE .'frame_view/'. $name);


if (is_file($tmp_filepath)) {
	$t = WideImage::load($tmp_filepath);
	$dw = $t->getWidth();
	$dh = $t->getHeight();
	$rotate = false;
	if ($dw > $dh && $lay == 'p') {
		$t->rotate(90)->output('png');
	} else if ($dw < $dh && $lay == 'l') {
		$t->rotate(90)->output('png');
	} else {
		$t->output('png');
	}
} else {
	$tmp_filepath2 = utf8_decode(PATH_IMAGES_ROOT . $name);
	File::mkdirs(dirname($tmp_filepath));

	$op = array('preserveAlpha' => true);
	//$t = PhpThumbFactory::create($tmp_filepath2, $op);
	$t = WideImage::load($tmp_filepath2);

	$dw = $t->getWidth();
	$dh = $t->getHeight();
	$rotate = false;
	if ($dw > $dh && $lay == 'p') {
		$rotate = true;
	} else if ($dw < $dh && $lay == 'l') {
		$rotate = true;
	}

	$t = $t->resize($mw, $mh);
	$t->saveToFile($tmp_filepath);
	//Dump::dl($rotate);
	if ($rotate) {
		$t->rotate(90)->output('png');
	} else {
		$t->output('png');
	}
}