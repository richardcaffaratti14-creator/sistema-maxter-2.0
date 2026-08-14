<?php

App::setLayout('lay_blank.php');
$delete_request = Http::getOverPost('del');

if ($delete_request == 1){
	SessionManager::unsetValue('pedido');
	SessionManager::unsetValue('__log__');
	Http::goInMod('deb_ver_pedido?del=0');
}
?>
<a href="<?= App::getModuleUrl() ?>deb_ver_pedido?del=1">delete</a>
<br />
<div style="float: left; width: 500px;">
	<? Dump::d(SessionManager::getValue('pedido')); ?>
</div>
<div style="float: left; width: 500px;">
	<? Dump::d(SessionManager::getValue('__log__')); ?>	
</div>