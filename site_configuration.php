<?php
ini_set('memory_limit','512M');

include 'admin/phpdb-core-1.4.php';

define('SITE_DEFAULT_TITLE', 'Maxter Producciones');
define('EVENT_NAME', utf8_encode(getSiteInfo('evento')));

$tmp = str_replace("\\", "/", getSiteInfo('filespath'));
if (substr($tmp, -1,1) != '/')
	$tmp .= "/";
define('PATH_IMAGES_ROOT', utf8_encode($tmp));
define('PATH_ORIGINALS', 'fotos/');
define('PATH_FRAMES', 'marcos/');
define('PATH_ORDERS', 'pedidos/');
//define('PATH_THUMBS', STATIC_PATH . 'img_cache/');
define('PATH_THUMBS', PATH_IMAGES_ROOT . 'img_cache/');

define('VIDEO_MAX_PLAY_SECONDS', (int)getSiteInfo("previewseconds"));

define('VIDEO_HQ_EXT', 'mp4');	//extensión del video que se copiará a la carpeta del pedido


define('THUMB_MAX_W', '180');
define('THUMB_MAX_H', '190');

define('VIEW_MAX_W', '1200');
define('VIEW_MAX_H', '800');

define('VIDEO_VIEW_MAX_W', '1280');
define('VIDEO_VIEW_MAX_H', '720');

define('IMGCROP_MAX_W', '800');
define('IMGCROP_MAX_H', '650');


//	ventana ver imagen / video
define('VIEW_WINDOW_W', '1850');
define('VIEW_WINDOW_H', '900');


define('MAX_FILES_PER_PAGE', 21);