<?php

$media = Http::getOverPost('m');
$media = base64_decode($media);
//echo $media;

$prev_next = File::getNextPrevFile($media);

$prev = str_replace(PATH_ORIGINALS_ROOT, '', $prev_next['prev']);
$next = str_replace(PATH_ORIGINALS_ROOT, '', $prev_next['next']);

$prev = base64_encode(utf8_encode($prev));
$next = base64_encode(utf8_encode($next));

//Dump::d($_REQUEST);

if (strtolower(pathinfo($media, PATHINFO_EXTENSION)) == 'mp4') {
	include App::getModulePath().'view_video.php';
} else {
	include App::getModulePath().'view_image.php';
}
