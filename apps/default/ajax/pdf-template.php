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
	<?
	$items = explode("\n", $pedido_db->descripcion);
	$items_vid = array();
	$items_fotos = array();
	foreach ($items as $i) {
	    if (strpos($i, ".mp4") > 0)
		$items_vid[] = $i;
	    else
		$items_fotos[] = $i;
	}

	switch ($pedido_db->descripcion) {
	    case 0: $estado = 'Pendiente';
		break;
	    case 1: $estado = 'Cobrado';
		break;
	    case 2: $estado = 'Cancelado';
		break;
	    default: $estado = '-';
		break;
	}
	?>

	<table>
	    <tr>
		<td colspan="4" class="bbr" style="text-align: center; padding: 10px;"><img src="static/img/maxter-pdf.jpg" /></td>
		<td class="bb" style="text-align: center;">
		    Pedido número<br/><br/>
		    <span style="font-size: 45px; font-weight:bold;"><?= $pedido_db->id ?></span>
		</td>
	    </tr>
	    <tr>
		<td class="bbr">Nombre</td>
		<td class="bbr">Teléfono</td>
		<td class="bbr">Retiro</td>
		<td class="bbr">Vendedor</td>
		<td class="bb" style="text-align: right;">Total</td>
	    </tr>
	    <tr>
		<th class="bbr"><?= $pedido_db->nombre ?> <?= $pedido_db->apellido ?></th>
		<th class="bbr"><?= $pedido_db->telefono ?></th>
		<th class="bbr"><?= $extra['retiro'] ?></th>
		<th class="bbr"><?= $vend->Vendedor ?></th>
		<th class="bb" style="text-align: right; font-size: 16px; width: 25%;">
		    <? if ($pedido_db->Descuento) { ?>
    		    <span style="font-size: 12px;">$ <?= number_format($pedido_db->total, 2) ?> - $ <?= number_format($pedido_db->Descuento, 2) ?> = </span>
		    <? } ?>

		    $ <?= number_format($pedido_db->total - $pedido_db->Descuento, 2) ?>
		</th>
	    </tr>
	    <tr>
		<td colspan="5" style="padding: 20px 10px; font-size: 12px;">
		    <?
		    if (count($items_vid) > 0) {
			echo "<h4>VIDEOS</h4>" . implode("<br/>", $items_vid) . "<br/><br/>";
		    }
		    if (count($items_fotos) > 0) {
			echo "<h4>FOTOS</h4>" . str_replace("---- ACCESORIOS ----", "<h4>ACCESORIOS</h4>", implode("<br/>", $items_fotos));
		    }
		    if ($pedido_db->idPresupuesto > 0) {
			echo "<br /><strong>Presupuesto n&uacute;mero: </strong>" . $pedido_db->idPresupuesto;
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
	    <td colspan="4" class="bbr" style="text-align: center; padding: 10px;"><img src="static/img/maxter-pdf.jpg" /></td>
	    <td class="bb" style="text-align: center;">
		Pedido número<br/><br/>
		<span style="font-size: 45px; font-weight:bold;"><?= $pedido_db->id ?></span>
	    </td>
	</tr>
	<tr>
	    <td class="bbr">Nombre</td>
	    <td class="bbr">Teléfono</td>
	    <td class="bbr">Retiro</td>
	    <td class="bbr">Vendedor</td>
	    <td class="bb" style="text-align: right;">Total</td>
	</tr>
	<tr>
	    <th class="bbr"><?= $pedido_db->nombre ?> <?= $pedido_db->apellido ?></th>
	    <th class="bbr"><?= $pedido_db->telefono ?></th>
	    <th class="bbr"><?= $extra['retiro'] ?></th>
	    <th class="bbr"><?= $vend->Vendedor ?></th>
	    <th class="bb" style="text-align: right; font-size: 16px; width: 25%;">
		<? if ($pedido_db->Descuento) { ?>
    		<span style="font-size: 12px;">$ <?= number_format($pedido_db->total, 2) ?> - $ <?= number_format($pedido_db->Descuento, 2) ?> = </span>
		<? } ?>

		$ <?= number_format($pedido_db->total - $pedido_db->Descuento, 2) ?>
	    </th>
	</tr>
	<tr>
	    <td colspan="5" style="padding: 20px 10px; font-size: 12px;">
		<?
		if (count($items_vid) > 0) {
		    echo "<h4>VIDEOS</h4>" . implode("<br/>", $items_vid) . "<br/><br/>";
		}
		if (count($items_fotos) > 0) {
			echo "<h4>FOTOS</h4>" . str_replace("---- ACCESORIOS ----", "<h4>ACCESORIOS</h4>", implode("<br/>", $items_fotos));
		}
		if ($pedido_db->idPresupuesto > 0) {
		    echo "<br /><strong>Presupuesto n&uacute;mero: </strong>" . $pedido_db->idPresupuesto;
		}
		?>
	    </td>
	</tr>
    </table>

</body>
</html>