<?php

App::setLayout('lay_blank.php');

$id = Http::getOverPost('id');

$p = new pedidos();
$p->get($id);

?>
<div style="float: left; width: 500px;">
	<? Dump::d(unserialize($p->pedido) ); ?>
</div>
<div style="float: left; width: 500px;">
	<? Dump::d(SessionManager::getValue('__log__')); ?>	
</div>