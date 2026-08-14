<?
$presuid = Presu::getID();
$presu_mode = $presuid != -1;
$pedido = SessionManager::getValue('pedido');

$vids = 0;
$imgs = 0;
$coreos = 0;
$accesorios = 0;

if (is_array($pedido)) {
    foreach ($pedido as $item) {
		if ($item['type'] == 'acc') {
			$accesorios += $item['qty'];
		}
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

$formatoCoreos = new formato_coreo();
$formatoCoreos = $formatoCoreos->select();

//  --------------------------------------------------------------
//  --------------------------------------------------------------

$pb_mode = PhotoBook::isPBMode();
$pb_id = PhotoBook::$pb_id;
?>
<style>
    .presu_qty {

    }
</style>
<script>

    function parse_query_string(query) {
	var vars = query.split("&");
	var query_string = {};
	for (var i = 0; i < vars.length; i++) {
	    var pair = vars[i].split("=");
	    // If first entry with this name
	    if (typeof query_string[pair[0]] === "undefined") {
		query_string[pair[0]] = decodeURIComponent(pair[1]);
		// If second entry with this name
	    } else if (typeof query_string[pair[0]] === "string") {
		var arr = [query_string[pair[0]], decodeURIComponent(pair[1])];
		query_string[pair[0]] = arr;
		// If third or later entry with this name
	    } else {
		query_string[pair[0]].push(decodeURIComponent(pair[1]));
	    }
	}
	return query_string;
    }

    function deleteOrder() {
	$('#msg').html('Esta seguro que desea eliminar el pedido?');
	$('#msg').dialog({
	    title: "Eliminar Pedido?",
	    buttons: {
		"Eliminar Pedido": function () {
		    $(this).dialog("close");
		    $.ajax('ajax/delete_order');
		    refreshCart();
		    $('#msg').html('');
		},
		"Cerrar": function () {
		    $(this).dialog("close");
		    $('#view-media').html("");
		}
	    }
	});
    }

    function viewOrderPresu() {
	$('#view-media').load('ajax/view_order_presu').dialog({
	    title: "Su orden",
	    buttons: {
		"Confirmar Pedido": function () {
		    confirmOrderPresu();
		},
		"Cerrar": function () {
		    $(this).dialog("close");
		    refreshCart();
		    $('#view-media').html("");
		}
	    }
	});
    }
    function viewOrderPB() {
	$('#view-media').load('ajax/view_order_photobook').dialog({
	    title: "Su orden",
	    buttons: {
		"Confirmar Pedido": function () {
		    confirmOrderPB();
		},
		"Cerrar": function () {
		    $(this).dialog("close");
		    refreshCart();
		    $('#view-media').html("");
		}
	    }
	});
    }

    function addAllPics() {
	var query_string = window.location.search.replace('?', '');
	var parsed_qs = parse_query_string(query_string);
	//
	$('#view-media').load('ajax/add_folder?f=' + parsed_qs.f).dialog({
	    title: "Agregando fotos...",
	    buttons: {
	    }
	});
    }

    function viewOrder() {
	$('#view-media').load('ajax/view_order').dialog({
	    title: "Su orden",
	    buttons: {
		"Confirmar Pedido": function () {
		    confirmOrder();
		},
		"Cerrar": function () {
		    $(this).dialog("close");
		    refreshCart();
		    $('#view-media').html("");
		}
	    }
	});
    }

    function confirmOrderPresu() {
	$('#view-media').load('ajax/confirm_order_presu').dialog({
	    title: "Confirmar pedido",
	    buttons: {
		"Aceptar": function () {
		},
		"Cerrar": function () {
		    $(this).dialog("close");
		    refreshCart();
		    $('#view-media').html("");
		}
	    }
	});
    }
    function confirmOrderPB() {
	$('#view-media').load('ajax/confirm_order_photobook').dialog({
	    title: "Confirmar pedido",
	    buttons: {
		"Aceptar": function () {
		},
		"Cerrar": function () {
		    $(this).dialog("close");
		    refreshCart();
		    $('#view-media').html("");
		}
	    }
	});
    }

    function confirmOrderPB() {
	$('#view-media').load('ajax/confirm_order_photobook').dialog({
	    title: "Confirmar FotoLibro",
	    buttons: {
		"Aceptar": function () {
		},
		"Cerrar": function () {
		    $(this).dialog("close");
		    refreshCart();
		    $('#view-media').html("");
		}
	    }
	});
    }

    function confirmOrder() {
	$('#view-media').load('ajax/confirm_order').dialog({
	    title: "Confirmar pedido",
	    buttons: {
		"Aceptar": function () {
		},
		"Cerrar": function () {
		    $(this).dialog("close");
		    refreshCart();
		    $('#view-media').html("");
		}
	    }
	});
    }

    function addPresu() {
	$('#view-media').load('ajax/add_presupuesto').dialog({
	    title: "Agregar Presupuesto",
	    buttons: {
		"Aceptar": function () {
		},
		"Cerrar": function () {
		    $(this).dialog("close");
		    refreshCart();
		    $('#view-media').html("");
		}
	    }
	});
    }

    function addAccesorio() {
	$('#view-media').load('ajax/add_accesorio').dialog({
	    title: "Agregar Accesorios",
	    width: 650,
		height:750,
	    buttons: {
		"Aceptar": function () {
			var acc = {};
			$("input._accesorio_qty").each(function(ix, e) {
				e = $(e);
				acc[e.attr('data-id')] = {
					l: e.attr('data-n'),
					amt: e.attr('data-amt'),
					q: e.val(),
				};
			});
			var w = this;
			$.post('ajax/add_accesorio_do', {acc:acc}, function(ret) {
			    if (ret.error == 1) {
					alert(ret.msg);
			    }
				
				$(w).dialog("close");
				refreshCart();
				$('#view-media').html("");
			});
		},
		"Cancelar": function () {
		    $(this).dialog("close");
		    refreshCart();
		    $('#view-media').html("");
		}
	    }
	});
    }
	
	
    function addPB() {
	$('#view-media').load('ajax/add_photobook').dialog({
	    title: "Agregar Fotolibro",
	    buttons: {
		"Aceptar": function () {
		},
		"Cerrar": function () {
		    $(this).dialog("close");
		    refreshCart();
		    $('#view-media').html("");
		}
	    }
	});
    }


    function addCoreo() {
	$('#view-media').load('ajax/select_coreo', {m: _current_folder}).dialog({
	    title: "Seleccionar Formato",
	    width: 400,
	    buttons: {
		"Aceptar": function () {
		    var id = $("#add_formatocoreo").val();
		    if ((id == "") || (id == undefined))
			alert('Seleccione el formato');
		    else {
			var w = this;
			$.post("ajax/add_coreo", {id: id, m: _current_folder}, function (data) {

			    //console.info(data);
			    if (data.error == 1) {
				alert(data.msg);
			    }

			    $(w).dialog("close");
			    $('#view-media').html("");
			    refreshCart();
			});
		    }
		},
		"Cerrar": function () {
		    $(this).dialog("close");
		    $('#view-media').html("");
		}
	    }
	});
    }
</script>
<div id="inner-minicart">
    <? if ($presu_mode) { ?>


        <div class="floatl">
    	<h2>Presupuesto Nro: <?= $presuid ?></h2>
        </div>
        <table style="color: white; margin-top: 15px; float: left;">
    	<tr>
    	    <th>Videos:</th>

		<?
		$numbers = Presu::getPresuCartNumbers();
		foreach ($numbers['video'] as $it) {
		    ?>
		    <td>
			<?= $it->name ?>: <?= $it->cart_qty . '/' . $it->qty ?>
		    </td>
		    <?
		}
		?>
    	</tr>

    	<tr>
    	    <th>Coreos:</th>

		<?
		foreach ($numbers['coreo'] as $it) {
		    ?>
		    <td>
			<?= $it->name ?>:  <?= $it->cart_qty . '/' . $it->qty ?>
		    </td>
		    <?
		}

		//MaxterHlp::d($numbers);
		?>
    	</tr>
        </table>



    <? } else if ($pb_mode) { ?>

        <div class="floatl">
    	<h2>FotoLibro Nro: <?= $pb_id ?></h2>
        </div>

        <div class="floatl media-qty">
    	<span><?= $imgs ?></span>
    	<img src="static/img/ecom-fotos.jpg" />
        </div>

    <? } else { ?>
        <div class="floatl">
    	<h2>Mi Pedido</h2>
        </div>

        <div class="floatl media-qty">
    	<span>
		<?= $imgs ?>

    	</span>
    	<img src="static/img/ecom-fotos.jpg" />
        </div>
        <div class="floatl media-qty">
    	<span>
		<?= $vids ?>
    	</span>
    	<img src="static/img/ecom-videos.jpg" />
        </div>

        <div class="floatl media-qty">
    	<span><?= $coreos ?></span>
    	<img src="static/img/ecom-coreos.jpg" />
        </div>

        <div class="floatl media-qty">
    	<span><?= $accesorios ?></span>
    	<img src="static/img/ecom-accesorios.jpg" />
        </div>



    <? } ?>

    <div class="floatr button-holder">
	<? if (!$presu_mode && !$pb_mode) { ?>
    	<a href="javascript:addAccesorio();" class="cart-button">Accesorios</a>
    	<a href="javascript:addPresu();" class="cart-button">Presupuestos</a>
    	<a href="javascript:addPB();" class="cart-button">FotoLibros</a>
	<? } ?>

	<? if (count($formatoCoreos) && !$presu_mode && !$pb_mode) { ?>
    	<a href="javascript:addCoreo();" class="cart-button">Agregar coreo</a>
	<? } ?>

	<? if ($presu_mode) { ?>
    	<a href="javascript:addCoreo();" class="cart-button">Agregar coreo</a>
	    <? /* <a href="javascript:addAllPics();" class="cart-button">Agregar toda las fotos de la carpeta</a> */ ?>
    	<a href="javascript:viewOrderPresu();" class="cart-button">Ver presupuesto</a>
    	<a href="javascript:confirmOrderPresu();" class="cart-button">Confirmar presupuesto</a>
    	<a href="javascript:deleteOrder();" class="cart-button-red">Vaciar presupuesto</a>
    	<a href="?presu_id=-1" class="cart-button-red" class="cart-button">Salir de modo presupuesto</a>
	<? } else if ($pb_mode) { ?>
    	<a href="javascript:viewOrderPB();" class="cart-button">Ver FotoLibro</a>
    	<a href="javascript:confirmOrderPB();" class="cart-button">Confirmar FotoLibro</a>
    	<a href="javascript:deleteOrder();" class="cart-button-red">Vaciar FotoLibro</a>
    	<a href="?photobook_id=-1" class="cart-button-red" class="cart-button">Salir de modo FotoLibro</a>
	<? } else { ?>
    	<a href="javascript:viewOrder();" class="cart-button">Ver pedido</a>
    	<a href="javascript:confirmOrder();" class="cart-button">Confirmar pedido</a>
    	<a href="javascript:deleteOrder();" class="cart-button-red">Eliminar pedido</a>
	<? } ?>
    </div>
</div>