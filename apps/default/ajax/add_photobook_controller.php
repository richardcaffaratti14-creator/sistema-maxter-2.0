<?
$vendedoresObj = new vendedores();
$vendedoresObj->addCondition("Activo", 1);
$vendedores = $vendedoresObj->select();


?>
<style>

    .left { float: left; }
    .right { float: right; }

</style>


<form id="confirm-frm" name="confirm-frm" action="ajax/save_photobook" method="post">

    <table cellpadding="3">
	<tr>
	    <td>Nombre:</td>
	    <td><input type="text" name="nombre" id="nombre" value="" /></td>
	</tr>
	<tr>
	    <td>Apellido:</td>
	    <td><input type="text" name="apellido" id="apellido" value="" /></td>
	</tr>
	<tr>
	    <td>Telefono:</td>
	    <td><input type="text" name="tel" id="tel" value="" /></td>
	</tr>
	<tr>
	    <td>Vendedor:</td>
	    <td>
		<select id="vendedor" name="vendedor" style="width: 100%; padding: 2px 5px;">
		    <option value=""></option>
		    <?
		    foreach ($vendedores as $v) {
			?><option value="<? echo($v->id) ?>"><? echo($v->Vendedor) ?></option> <?
		    }
		    ?>
		</select>
	    </td>
	</tr>
	<tr id="vendedor-clave-cont">
	    <td>Clave</td>
	    <td>
		<div id="vendedor-clave" style="display: none;">
		    <input type="password" name="clave" id="clave" size="10" 
			   maxlength="20" value="" /> 
		    <a href="javascript:void(0)" id="clavedescuento">Aplicar</a>
		</div>
		<div id="vendedor-clave-ok" 
		     style="display: none; font-weight:bold;color: #00a000;">
		    Clave correcta
		</div>
	    </td>
	</tr>

	<tr>
	    <td>Se&ntilde;a:</td>
	    <td><input type="number" name="sena" id="sena" min="0" value="0" /></td>
	</tr>

    </table>

</form>
<script>

    var vendedor_verificado = false;

    $("#vendedor-clave-cont").hide();

    $("#vendedor").change(function () {
	vendedor_verificado = false;

	$("#vendedor-clave-ok").hide();
	$("#descuento-aplicar").hide();
	$("#descuento-monto").hide();
	$("#descuento-link").show();
	$("#vendedor-clave").show();

	if ($(this).val() == '') {
	    $("#vendedor-clave-cont").hide();
	} else {
	    $("#vendedor-clave-cont").show();
	    $("#clave").focus();
	}

	$("#clave").val("");
	$("#descuento").val("");

    });

    $("#clavedescuento").click(function () {
	validateDtoPass();
    });

    function validateDtoPass() {
	var pass = {};
<? foreach ($vendedores as $v) { ?>
    	pass.v_<?= $v->id ?> = '<?= addslashes($v->Clave) ?>';
    <?
}
?>
	var id = $("#vendedor").val();
	if (pass['v_' + id] != undefined)
	    if (pass['v_' + id] == $("#clave").val()) {
		vendedor_verificado = true;

		$("#vendedor-clave").hide();
		$("#vendedor-clave-ok").show();

		$("#descuento-aplicar").show();
		$("#descuento-link").show();
		return;
	    }

	vendedor_verificado = false;
	alert('Clave inválida');
    }

    $('#view-media').dialog({
	title: "Agregar FotoLibro",
	buttons: {
	    "Retomar FotoLibro": function () {


		$('#view-media').load('ajax/retomar_photobook').dialog({
		    title: "Retomar FotoLibro",
		    buttons: {
			"Cerrar": function () {
			    $(this).dialog("close");
			    refreshCart();
			    $('#view-media').html("");
			}
		    }
		});



	    },
	    "Aceptar": function () {
		/* */
		if (!vendedor_verificado) {
		    alert('Seleccione vendedor');
		    return;
		}

		if (($("#nombre").val() == '') || ($("#apellido").val() == '')) {
		    alert('Ingrese nombre y apellido del cliente');
		    return;
		}
		/* */

		var total = getTotal();
		var desc = parseFloat($('#desc').val());

		if (!isValidPresuDiscount(total, desc) && $('#aut').val() == '') {
		    alert('Debe autorizar el descuento.');
		    return;
		}

		//return;
		$('#confirm-frm').submit();
		$('#view-media').dialog({buttons: null});
		$(this).dialog("close");
		refreshCart();
		$('#view-media').html("");
	    },
	    "Cerrar": function () {
		$(this).dialog("close");
		refreshCart();
		$('#view-media').html("");
	    }
	}
    });

    $('#view-media').find(".ui-button:first") // the first button
	    .addClass("custom");


    $('.video_qty').on('input', function (e) {
	calculateTotal();
    });
    $('.coreo_qty').on('input', function (e) {
	calculateTotal();
    });
    $('#desc').on('input', function (e) {
	calculateTotal();
    });

    function getTotal() {
	var total = 0;
	$('.coreo_qty').each(function (index, input) {
	    var $input = $(input);
	    var qty = $input.val();
	    if (qty > 0) {
		var p = $input.parent().find('.coreo_price').val();
		total += (p * qty);
	    }
	});
	$('.video_qty').each(function (index, input) {
	    var $input = $(input);
	    var qty = $input.val();
	    if (qty > 0) {
		var p = $input.parent().find('.video_price').val();
		total += (p * qty);
	    }
	});
	return total;
    }

    function calculateTotal() {
	var total = getTotal();
	//
	var desc = parseFloat($('#desc').val());

	if (!isValidPresuDiscount(total, desc)) {
	    $('#clave_aut').show();
	} else {
	    $('#clave_aut').hide();
	}

	if (desc > 0) {
	    total -= total * (desc / 100);
	}

	total = total < 0 ? 0 : total;

	$('#total_presu').html(total);
    }

    calculateTotal();

    $('#confirm-frm').ajaxForm({target: "#view-media"});

    $("#view-media").dialog("option", "width", "auto");
    $("#view-media").dialog("option", "position", "center");



</script>
