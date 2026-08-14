<html>
    <head>
        <style type="text/css">
	    @page {
		margin:2mm 6mm;
		font-family: arial;
	    }
	    * {
		font-family: arial;
	    }
	    table {
		width: 100%;
		border: 1px solid #000;
		border-collapse: collapse;
	    }
	    td {
		font-size: 14px;
		vertical-align: top;
	    }
	    th {	
		font-size: 14px;
		font-weight:bold;
		vertical-align: top;
		text-align: left;
	    }
	    td, th {
		padding: 5px;
		font-family: arial;
	    }

	    .bbr {
		border-bottom: 1px solid #000;
		border-right: 1px solid #000;
	    }
	    .bb {
		border-bottom: 1px solid #000;
	    }
	    .br {
		border-right: 1px solid #000;
	    }
	    .noborder {
		border: 1px solid #fff;
	    }
	</style>
    </head>
    <body>
	<table>
	    <tr>
		<td colspan="3" class="bbr" style="text-align: center; padding: 10px;"><img src="static/img/maxter-pdf.jpg" /></td>
		<td class="bb" style="text-align: center;" colspan="3">
		    Presupuesto número<br/><br/>
		    <span style="font-size: 45px; font-weight:bold;"><?= $pedido_db->id ?></span>
		</td>
	    </tr>
	    <tr>
		<td class="bbr">Nombre</td>
		<td class="bbr">Teléfono</td>
		<td class="bbr">Vendedor</td>
		<td class="bbr" style="text-align: right;">SubTotal</td>
		<td class="bbr" style="text-align: right;">Descuento</td>
		<td class="bb" style="text-align: right;">Total</td>
	    </tr>
	    <tr>
		<th class="bbr"><?= $pedido_db->nombre ?> <?= $pedido_db->apellido ?></th>
		<th class="bbr"><?= $pedido_db->telefono ?></th>
		<th class="bbr"><?= $vend->Vendedor ?></th>
		<th class="bbr" style="text-align: right; font-size: 16px;">
		    $ <?= number_format($pedido_db->subtotal, 2) ?>
		</th>
		<th class="bbr" style="text-align: right; font-size: 16px;">
		    <?= number_format($pedido_db->descuento, 2) ?>%
		</th>
		<th class="bb" style="text-align: right; font-size: 16px;">
		    $ <?= number_format($pedido_db->total, 2) ?>
		</th>
	    </tr>
	    <tr>
		<td colspan="6" style="padding: 20px 10px; font-size: 12px;">
		    <?
		    $presupuesto = json_decode($pedido_db->presupuesto);
		    //	COREOS
		    echo '<strong>Coreos</strong><br/>';
		    foreach($presupuesto->coreo as $p) {
			$item = new formato_coreo();
			$item->get($p->id);
			echo $p->name." x ".$p->qty." = $".MaxterHlp::fn($p->unit_price*$p->qty)."<br />";
		    }
		    //	VIDEOS
		    echo '<br/><strong>Videos</strong><br/>';
		    foreach($presupuesto->video as $p) {
			$item = new formato_video();
			$item->get($p->id);
			echo $p->name." x ".$p->qty." = $".MaxterHlp::fn($p->unit_price*$p->qty)."<br />";
		    }
		    
		    if ($pedido_db->sena > 0) {
			echo '<br /><hr />Se&ntilde;a: $'.MaxterHlp::fn($pedido_db->sena);
		    }
		    
		    ?>
		</td>
	    </tr>
	</table>

	<? if ((count($items) > 18)) { ?>
        <pagebreak></pagebreak>
    <? } else { ?>
        <div style="margin-top: 30px; margin-bottom: 15px; border-top: 1px dashed #aaa; height: 1px;"></div>
    <? } ?>


    <table>
	<tr>
	    <td colspan="3" class="bbr" style="text-align: center; padding: 10px;"><img src="static/img/maxter-pdf.jpg" /></td>
	    <td class="bb" style="text-align: center;" colspan="3">
		Presupuesto número<br/><br/>
		<span style="font-size: 45px; font-weight:bold;"><?= $pedido_db->id ?></span>
	    </td>
	</tr>
	<tr>
		<td class="bbr">Nombre</td>
		<td class="bbr">Teléfono</td>
		<td class="bbr">Vendedor</td>
		<td class="bbr" style="text-align: right;">SubTotal</td>
		<td class="bbr" style="text-align: right;">Descuento</td>
		<td class="bb" style="text-align: right;">Total</td>
	    </tr>
	    <tr>
		<th class="bbr"><?= $pedido_db->nombre ?> <?= $pedido_db->apellido ?></th>
		<th class="bbr"><?= $pedido_db->telefono ?></th>
		<th class="bbr"><?= $vend->Vendedor ?></th>
		<th class="bbr" style="text-align: right; font-size: 16px;">
		    $ <?= number_format($pedido_db->subtotal, 2) ?>
		</th>
		<th class="bbr" style="text-align: right; font-size: 16px;">
		    <?= number_format($pedido_db->descuento, 2) ?>%
		</th>
		<th class="bb" style="text-align: right; font-size: 16px;">
		    $ <?= number_format($pedido_db->total, 2) ?>
		</th>
	    </tr>
	    <tr>
		<td colspan="6" style="padding: 20px 10px; font-size: 12px;">
		   <?
		    $presupuesto = json_decode($pedido_db->presupuesto);
		    //	COREOS
		    echo '<strong>Coreos</strong><br/>';
		    foreach($presupuesto->coreo as $p) {
			$item = new formato_coreo();
			$item->get($p->id);
			echo $p->name." x ".$p->qty." = $".MaxterHlp::fn($p->unit_price*$p->qty)."<br />";
		    }
		    //	VIDEOS
		    echo '<br/><strong>Videos</strong><br/>';
		    foreach($presupuesto->video as $p) {
			$item = new formato_video();
			$item->get($p->id);
			echo $p->name." x ".$p->qty." = $".MaxterHlp::fn($p->unit_price*$p->qty)."<br />";
		    }
		    
		    if ($pedido_db->sena > 0) {
			echo '<br /><hr />Se&ntilde;a: $'.MaxterHlp::fn($pedido_db->sena);
		    }
		    ?>
		</td>
	    </tr>
    </table>

</body>
</html>