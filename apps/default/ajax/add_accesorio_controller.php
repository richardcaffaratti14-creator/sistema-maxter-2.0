<h2>Accesorios - $ <span id="_accesorios_list_total"></span></h2>

<?
$pedido = SessionManager::getValue('pedido');
if (!is_array($pedido)) {
    $pedido = array();
}
$exist = array();
foreach ($pedido as $item) {
	if ($item['type'] == 'acc') {
		$exist[$item['id']] = $item['qty'];
	}
}
$rs = mysql_query("select * from accesorios where Activo = 1 order by nombre");
?>
<div style="overflow-y: auto; max-height: 750px">
<table cellpadding="3" style="width: 100%;" class="highhover" id="accesorios_main_table">
	<tr>
		<th style="width: 70%; text-align: left;">Accesorio</th>
		<th style="text-align: right;">Precio</th>
		<th style="text-align: right;">Cantidad</th>
	</tr>
	<?while ( $r = mysql_fetch_array($rs) ) {?>
	<tr>
		<td><?= $r['nombre'] ?></td>
		<td style="text-align: right;  white-space: nowrap">$ <?= $r['precio'] ?></td>
		<td style="text-align: right; white-space: nowrap">
			<a href="javascript:;" class="steeper-button-acc stepdown" data-id="<?=$r['id']?>">-</a><input type="text" class="_accesorio_qty" name="q_<?=$r['id']?>" id="q_<?=$r['id']?>" data-id="<?=$r['id']?>" data-n="<?=addslashes($r['nombre'])?>" data-amt="<?= $r['precio'] ?>" maxlength="6" value="<?= isset($exist[$r['id']]) ? $exist[$r['id']] : "" ?>" style="width: 50px; text-align: center" /><a href="javascript:;" class="steeper-button-acc stepup" data-id="<?=$r['id']?>">+</a>
		</td>
	</tr>
	<?}?>
</table>
</div>

<script type="text/javascript">
	$("input._accesorio_qty").change(function() {
		var total = 0;
		$("input._accesorio_qty").each(function(index, e) {
			var q = parseInt($(e).val());
			if (!isNaN(q) && (q>0)) {
				var v = parseFloat($(e).attr('data-amt'));
				total += (v*q);
			}
		});
		
		$("#_accesorios_list_total").html( total.toFixed(2) );
	});
	$("input._accesorio_qty:first").change();
	
	$('#accesorios_main_table a.steeper-button-acc.stepdown').click(function () {
		var id = $(this).attr('data-id');
	    var val = parseInt($('#accesorios_main_table #q_' + id).val());
		if (isNaN(val)) val = 0;
	    if (val > 0) {
			$('#accesorios_main_table #q_' + id).val(val - 1);
			$("input._accesorio_qty:first").change();
		}
	});

	$('#accesorios_main_table a.steeper-button-acc.stepup').click(function () {
		var id = $(this).attr('data-id');
	    var val = parseInt($('#accesorios_main_table #q_' + id).val());
		if (isNaN(val)) val = 0;
	    if (val < 999999) {
			$('#accesorios_main_table #q_' + id).val(val + 1);
			$("input._accesorio_qty:first").change();
		}
	});
	
</script>