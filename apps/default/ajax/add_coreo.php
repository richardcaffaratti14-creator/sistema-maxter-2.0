<?php
header('Content-Type: application/json');

$id = (int)Http::getOverPost('id');


//  initialize presu	---------------------------
$pid = Presu::getID();
$presu_mode = Presu::getID() != -1;



$fqty = Presu::getQtyCoreoFormat($id);
$cqty = Presu::getQtyCoreoFormatsInCart($id);

if ($presu_mode) {
if ($cqty >= $fqty) {
    $rtn = array(
	'error' => '1',
	'msg' => 'Se exedera la cantidad de coreos permitidas en el presupuesto.',
    );
    echo json_encode($rtn);
    die;
}
}

//die('WTF! :: '.$id .' : '. $fqty.' : '. $cqty);



$media = Http::getOverPost('m');
$folder = base64_decode($media);
$folder = str_replace(PATH_ORIGINALS_ROOT, "", $folder);

//	guardar formatos
$imager = array();
$imager['name'] = $folder;
$imager['type'] = 'coreo';

$imager['formats'][] = array(
	'format_id' => $id,
	'qty' => 1,
	'processed' => false
);

$pedido = SessionManager::getValue('pedido');

SessionManager::pushValue('pedido', $imager);

if ($presu_mode) {
    Presu::save();
}


$rtn = array(
	'error' => '0',
    );
    echo json_encode($rtn);
    die;

die();
