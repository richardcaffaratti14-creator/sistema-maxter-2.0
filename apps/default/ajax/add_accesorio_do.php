<?php
header('Content-Type: application/json');

$acc = (array)Http::getOverPost('acc');


$pedido = SessionManager::getValue('pedido');
if (!is_array($pedido)) {
    $pedido = array();
}

//remove all accesories
foreach ($pedido as $ix => $item) {
	if ($item['type'] == 'acc') {
		unset($pedido[$ix]);
	}
}

foreach ($acc as $id => $a) {
    $q = (int)$a['q'];
	if ($q>0) {
		$tmp = array(
			'type'=>'acc',
			'id'=>$id,
			'qty'=>$q,
			'amt'=>$a['amt'],
			'l'=>$a['l'],
		);
		
		$pedido[] = $tmp;
	}
}

$pedido = array_values($pedido);	//re base the array

SessionManager::setValue('pedido', $pedido);


echo "[]";
die();
