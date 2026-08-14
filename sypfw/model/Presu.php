<?php

class Presu {

    static $presu_id = -1;
    static $presupuesto = '';

    public static function init() {

	self::$presu_id = Http::getOverPost('presu_id');
	//
	if (self::$presu_id == '-1') {
	    SessionManager::unsetValue('__presupuesto');
	    SessionManager::unsetValue('pedido');
	    self::$presu_id = -1;
	    return;
	}
	//
	$nuevo = FALSE;
	//
	if (self::$presu_id == '') {
	    self::$presu_id = SessionManager::getValue('__presupuesto/id');
	} else {
	    //	no es -1 y tampoco es "" y es por $_GET
	    self::$presu_id = base64_decode(self::$presu_id);
	    if (!is_numeric(self::$presu_id)) {
		SessionManager::unsetValue('__presupuesto');
		SessionManager::unsetValue('pedido');
		self::$presu_id = -1;
		return;
	    }
	    
	    //	----------------------------
	    $nuevo = TRUE;
	    
	}

	if (self::$presu_id != '') {
	    $presu = new presupuestos();
	    $presu->get(self::$presu_id);

	    if ($presu->isAvailable()) {
		SessionManager::setValue('__presupuesto/id', self::$presu_id);
		//
		self::$presu_id = $presu_id;
		self::$presupuesto = json_decode($presu->presupuesto);
		
		if ($nuevo) {
		    $pedido = json_decode($presu->pedido, TRUE);
		    SessionManager::setValue('pedido', $pedido);
		}
	    }
	} else {
	    self::$presu_id = -1;
	}
    }
    
    public static function cancelPresuMode() {
	SessionManager::unsetValue('__presupuesto/id');
    }

    public static function isValidDiscount($total, $discount) {
	$tablaDescuentosPresu = json_decode(getSiteInfo('descuentosmaxpresu'));
	if ($discount == 0) {
	    return TRUE;
	}
	for ($i = 0; $i < count($tablaDescuentosPresu->dd); $i++) {
	    if ($total >= $tablaDescuentosPresu->dd[$i] && $total <= $tablaDescuentosPresu->dh[$i] && $discount <= $tablaDescuentosPresu->dm[$i]) {
		return TRUE;
	    }
	}
	return FALSE;
    }

    public static function save() {

	$id = self::getID();

	if ($id == -1) {
	    return;
	}

	$presu = new presupuestos();
	$presu->get($id);

	$pedido = SessionManager::getValue('pedido');
	$rtn = 0;

	$ped = array();
	if (is_array($pedido)) {
	    foreach ($pedido as $item) {
		if ($item['type'] != 'img') {
		    $ped[] = $item;
		}
	    }
	}
	//  -------------------------------------------
	$presu->pedido = json_encode($ped);
	$presu->save();
    }

    //	deprecated
    public static function isValid() {
	//  just to be sure
	self::getID();

	$nums = self::getCartNumbers();
	if ($nums['img'] == self::$max_img && $nums['vid'] == self::$max_vid) {
	    return TRUE;
	}
	return FALSE;
    }

    /**
     * return -1 if is not in presupuesto mode
     */
    public static function getID() {

	self::$presu_id = SessionManager::getValue('__presupuesto/id');

	if (self::$presu_id != '') {
	    $presu = new presupuestos();
	    $presu->get(self::$presu_id);
	    if ($presu->isAvailable()) {
		self::$presupuesto = json_decode($presu->presupuesto);
		return self::$presu_id;
	    }
	}
	return -1;
    }

    /**
     * returns the qty and max of videos and coreos from de pedido and presu
     */
    public static function getPresuCartNumbers() {
	$id = self::getID();
	if (self::$presupuesto == NULL) {
	    return FALSE;
	}

	$p = self::$presupuesto;

	$rtn = array();
	$rtn['video'] = array();
	$rtn['coreo'] = array();
	foreach ($p->video as $it) {
	    $q = self::getQtyVideoFormatsInCart($it->id);
	    $it->cart_qty = $q;
	    $rtn['video'][] = $it;
	}

	foreach ($p->coreo as $it) {
	    $q = self::getQtyCoreoFormatsInCart($it->id);
	    $it->cart_qty = $q;
	    $rtn['coreo'][] = $it;
	}

	return $rtn;
    }

    /**
     * returns if the $format_id is in the presupuesto
     * 
     * @param int $format_id
     */
    public static function isValidVideoFormat($format_id) {
	$id = self::getID();
	if (self::$presupuesto == NULL) {
	    return FALSE;
	}

	foreach (self::$presupuesto->video as $v) {
	    if ($v->id == $format_id) {
		return TRUE;
	    }
	}

	return FALSE;
    }

    /**
     * returns if the $format_id is in the presupuesto
     * 
     * @param int $format_id
     */
    public static function isValidCoreoFormat($format_id) {
	$id = self::getID();
	if (self::$presupuesto == NULL) {
	    return FALSE;
	}

	foreach (self::$presupuesto->coreo as $v) {
	    if ($v->id == $format_id) {
		return TRUE;
	    }
	}

	return FALSE;
    }

    /**
     * regresa la cantidad de videos maximo de $format_id
     * 
     * @param int $format_id
     */
    public static function getQtyVideoFormat($format_id) {
	$id = self::getID();
	if (self::$presupuesto == NULL) {
	    return 0;
	}

	foreach (self::$presupuesto->video as $v) {
	    if ($v->id == $format_id) {
		return $v->qty;
	    }
	}

	return 0;
    }

    /**
     * regresa la cantidad de coreo maximo de $format_id
     * 
     * @param int $format_id
     */
    public static function getQtyCoreoFormat($format_id) {
	$id = self::getID();
	if (self::$presupuesto == NULL) {
	    return 0;
	}

	foreach (self::$presupuesto->coreo as $v) {
	    if ($v->id == $format_id) {
		return $v->qty;
	    }
	}

	return 0;
    }

    /**
     * regresa la cantidad de videos con el formato id $vid
     * 
     * @param int $vid
     * @return bool
     */
    public static function getQtyVideoFormatsInCart($vid) {
	$pedido = SessionManager::getValue('pedido');
	$rtn = 0;

	if (is_array($pedido)) {
	    foreach ($pedido as $item) {
		if ($item['type'] == 'vid') {
		    foreach ($item['formats'] as $f) {
			if ($f['format_id'] == $vid) {
			    $rtn += $f['qty'];
			}
		    }
		}
	    }
	}

	return $rtn;
    }

    /**
     * regresa la cantidad de core con el formato id $vid
     * 
     * @param int $vid
     * @return bool
     */
    public static function getQtyCoreoFormatsInCart($vid) {
	$pedido = SessionManager::getValue('pedido');
	$rtn = 0;

	if (is_array($pedido)) {
	    foreach ($pedido as $item) {
		if ($item['type'] == 'coreo') {
		    foreach ($item['formats'] as $f) {
			if ($f['format_id'] == $vid) {
			    $rtn += $f['qty'];
			}
		    }
		}
	    }
	}

	return $rtn;
    }

    /**
     * regresa la cantidad de videos imagenes y coreos que hay en el pedido
     * 
     */
    //	esto no es de los presupuestos, si no de la cantidad de cosas que
    //	hay en el carro


    public static function getCartNumbers() {
	$pedido = SessionManager::getValue('pedido');
	$vids = 0;
	$imgs = 0;
	$coreos = 0;

	if (is_array($pedido)) {
	    foreach ($pedido as $item) {
		if ($item['type'] == 'vid') {
		    foreach ($item['formats'] as $f) {
			$vids += $f['qty'];
		    }
		}
		if ($item['type'] == 'img') {
		    foreach ($item['formats'] as $f) {
			$imgs += $f['qty'];
		    }
		}
		if ($item['type'] == 'coreo') {
		    foreach ($item['formats'] as $f) {
			$coreos++;
		    }
		}
	    }
	}

	return array('img' => $imgs, 'vid' => $vids, 'coreo' => $coreos);
    }

}
