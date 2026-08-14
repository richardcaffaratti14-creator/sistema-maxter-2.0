<? 
$fidx = Http::getOverPost('f');
$pid = Http::getOverPost('p');

$pedido_db = new pedidos();
$pedido_db->get($pid);

$pedido = unserialize($pedido_db->pedido);

$format = new formato_imagen();
$format->get($pedido[$fidx]['formats'][0]['format_id']);

if ($format->isAvailable()){
	$order_path = PATH_IMAGES_ROOT . PATH_ORDERS . $pid . '/';
	$tmp_dst = $order_path . $format->carpeta . '/';
	$filename = $pedido[$fidx]['name'];
	if (file_exists($tmp_dst . $filename))
		@unlink($tmp_dst . $filename);
	
	//	delete copies
	if ($pedido[$fidx]['type'] == 'coreo') {
		$folder = folderToFilename($filename);
		foreach (glob($order_path . "_coreos/" . $folder . "/*.*") as $f) {
			@unlink($f);
		}
		@rmdir($order_path . "_coreos/" . $folder);
	}
	else {
		$tmp = pathinfo($filename);
		for ($i = 1; $i < (int)$pedido[$fidx]['formats'][0]['qty']; $i++) {
			$filenameCopy = $tmp['filename'] . "-{$i}." . $tmp['extension'];
			if (file_exists($tmp_dst . $filenameCopy)) @unlink($tmp_dst . $filenameCopy);
		}
	}
}

unset($pedido[$fidx]);

$pedido_db->pedido = serialize($pedido);
$pedido_db->update();