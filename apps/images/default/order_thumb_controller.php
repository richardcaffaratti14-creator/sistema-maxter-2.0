<?php
$IMAGESAPP_CACHE = PATH_THUMBS . PATH_ORIGINALS;
$DB = new SypDatabase(DB_USER, DB_PASS, DB_NAME);
//	----------------------------------------------------------------------------

$cart_mw = 80;
$cart_mh = 90;

$idx = Http::getOverPost('idx');
$fidx = Http::getOverPost('fidx');

$pedido = SessionManager::getValueFromSiteCode('pedido', 'maxter');

if ($idx == '' || $fidx == '') {
	Http::set404Header();
	die;
}

if ($pedido[$idx]['type'] == 'vid') {
	$thumb = PhpThumbFactory::create('static/img/video_icon_thumb.png');
	$thumb->resize($cart_mw, $cart_mh);
	$thumb->show();
	die;
}

if (!isset($pedido[$idx]['formats'][$fidx])) {
	Http::set404Header();
	die;
}

$img_path = $pedido[$idx]['name'];

$format_order = $pedido[$idx]['formats'][$fidx];
$format_id = $format_order['format_id'];
$format = new formato_imagen();
$format->get($format_id);

if (!$format->isAvailable()) {
	Http::set404Header();
	die;
}

$thumb = PhpThumbFactory::create(Img::crop(utf8_decode($IMAGESAPP_CACHE . $img_path), VIEW_MAX_W . 'x' . VIEW_MAX_H));
$thumb->crop(
	$format_order['data']['crop']['x1'], $format_order['data']['crop']['y1'], $format_order['data']['crop']['x2'] - $format_order['data']['crop']['x1'], $format_order['data']['crop']['y2'] - $format_order['data']['crop']['y1']
);
$thumb->resize($cart_mw, $cart_mh);
//$thumb->bc($format_order['data']['brightness'], $format_order['data']['contrast']);
$thumb->show();
