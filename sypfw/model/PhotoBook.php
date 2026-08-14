<?php

class PhotoBook {

    static $pb_id = -1;

    public static function init() {

	self::$pb_id = Http::getOverPost('photobook_id');

	if (self::$pb_id == -1) {
	    SessionManager::unsetValue('pedido');
	    self::cancelPBMode();
	    return;
	}

	if (!isset(self::$pb_id)) {
	    self::$pb_id = self::getID();
	    if (self::$pb_id == -1) {
		self::cancelPBMode();
		return;
	    }
	}

	$pb = new fotolibros();
	$pb->get(self::$pb_id);

	if (!$pb->isAvailable()) {
	    self::cancelPBMode();
	    return;
	}

	$pedido = json_decode($pb->pedido, TRUE);
	SessionManager::setValue('pedido', $pedido);

	SessionManager::setValue('__photobook/id', self::$pb_id);
    }

    public static function getID() {
	self::$pb_id = SessionManager::getValue('__photobook/id');
	//  -----------
	if (is_numeric(self::$pb_id)) {
	    return self::$pb_id;
	}
	return -1;
    }

    public static function isPBMode() {
	self::$pb_id = self::getID();
	return self::$pb_id != -1;
    }

    public static function cancelPBMode() {
	self::$pb_id = -1;
	SessionManager::unsetValue('__photobook/id');
	//SessionManager::unsetValue('pedido');
    }

    public static function save() {

	$id = self::getID();

	if ($id == -1) {
	    return;
	}

	$presu = new fotolibros();
	$presu->get($id);

	$pedido = SessionManager::getValue('pedido');
	$precio_pb = floatval(getSiteInfo('photobook_image_price'));

	$rtn = 0;

	$ped = array();
	$imgqty = 0;
	if (is_array($pedido)) {
	    foreach ($pedido as $item) {
		if ($item['type'] == 'img') {
		    foreach ($item['formats'] as $f) {
			$imgqty += $f['qty'];
		    }

		    $ped[] = $item;
		}
	    }
	}
	$total = $imgqty * $precio_pb;
	//  -------------------------------------------
	//  -------------------------------------------
	$presu->total = $total;
	$presu->pedido = json_encode($ped);
	$presu->save();
    }

}
