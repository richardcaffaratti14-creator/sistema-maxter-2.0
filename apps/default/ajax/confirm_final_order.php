<?php
$pid = $pedido_id = Http::getOverPost('pid');

$pedido_db = new pedidos();
$pedido_db->get($pid);

$pedido = unserialize($pedido_db->pedido);

if (!is_array($pedido)) {
	$pedido = array();
}

$order_path = PATH_IMAGES_ROOT . PATH_ORDERS . $pedido_id . '/';
$source_path = PATH_IMAGES_ROOT . PATH_ORIGINALS;


session_write_close();


foreach ($pedido as $pidx => $item) {
	$fidx = 0;
	foreach ($item['formats'] as $f) {
		if ($item['type'] == 'vid') {
			$format = new formato_video();
			$format->get($f['format_id']);
			
			$tmp_src_vid_name = str_replace('.' . File::getExtension($item['name']), '.' . strtolower(VIDEO_HQ_EXT), $item['name']);
			$tmp_src = $source_path . $tmp_src_vid_name;
			$tmp_src = str_replace('//', '/', $tmp_src);
			$tmp_src = utf8_decode($tmp_src);

			if (!is_file($tmp_src)) {
				$tmp_src_vid_name = str_replace('.' . File::getExtension($item['name']), '.' . strtoupper(VIDEO_HQ_EXT), $item['name']);
				$tmp_src = $source_path . $tmp_src_vid_name;
				$tmp_src = str_replace('//', '/', $tmp_src);
				$tmp_src = utf8_decode($tmp_src);
			}

			File::mkdirs($order_path . $format->carpeta . '/');
			$tmp_dst = $order_path . $format->carpeta . '/' . $pidx . '-' . basename($tmp_src_vid_name);
			$tmp_dst = str_replace('//', '/', $tmp_dst);

			/*QUE NO SOBREESCRIBA VIDEOS PORQUE PUEDE HABER VIDEOS CON EL MISMO NOMBRE EN DIFERENTES CARPETAS!*/
			if (file_exists($tmp_dst)) {
				@unlink($tmp_dst);
			}
			
			//@copy($tmp_src,$tmp_dst);
			File::docopy($tmp_src,$tmp_dst);
			
			for ($i = 1; $i < $f['qty']; $i++) {
				$tmp = pathinfo($tmp_src_vid_name);
				$filenameCopy = $pidx . '-' . $tmp['filename'] . "-{$i}." . $tmp['extension'];
				if (file_exists($order_path . $format->carpeta . '/' . $filenameCopy)) @unlink($order_path . $format->carpeta . '/' . $filenameCopy);
				//@copy($tmp_src, $order_path . $format->carpeta . '/' . $filenameCopy);
				File::docopy($tmp_src, $order_path . $format->carpeta . '/' . $filenameCopy);
			}
		}
	}
}
?>
<strong>
<br/>
El pedido ha sido guardado en la carpeta:<br/><br/>
<?= str_replace("/", "\\", $order_path) ?>
<br/>
<br/>
<br/>
<a href="ajax/pdf?id=<?= $pid ?>" target="_blank">Imprimir comprobante</a>
</strong>