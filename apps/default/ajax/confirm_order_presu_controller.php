<?
//  ------------------------------------
$presu_id = Presu::getID();

$presu = new presupuestos();
$presu->get($presu_id);
//  -------------------------------------
//  -------------------------------------


$pedido = SessionManager::getValue('pedido');

if (!is_array($pedido)) {
    $pedido = array();
}

$vendedoresObj = new vendedores();
$vendedoresObj->addCondition("Activo", 1);
$vendedores = $vendedoresObj->select();


if (!count($pedido) > 0) {
    ?>
    <h2>No hay fotos o videos en su pedido.</h2>
    <script>
        $('#view-media').dialog({
    	title: "Confirmar pedido",
    	buttons: {
    	    "Cerrar": function () {
    		$(this).dialog("close");
    		refreshCart();
    		$('#view-media').html("");
    	    }
    	}
        });

        $('#view-media').dialog({
    	width: "auto",
    	height: "auto",
    	position: ["center", "center"]
        })
    </script>

    <?
} else {
    $hc = getSiteInfo("horacorte");
    $horasDelay = (int) getSiteInfo("horasretiro");
    $hdesde = getSiteInfo("horadesde");
    $limite = strtotime(date("Y-m-d") . " " . $hc);
    if (strtotime("+" . $horasDelay + " hour") < $limite) {
	$fechaRetiro = date("d/m/Y", strtotime("+" . $horasDelay . " hour"));
	$horaRetiro = date("H:i", strtotime("+" . $horasDelay . " hour"));
    } else {
	$retirotmp = strtotime("+1 day");
	$fechaRetiro = date("d/m/Y", $retirotmp);
	$horaRetiro = $hdesde;
    }


    //	get order total
    $preciototal = 0;
    foreach ($pedido as $item) {
	foreach ($item['formats'] as $f) {
	    if ($item['type'] == 'vid') {
		$format = new formato_video();
	    } elseif ($item['type'] == 'coreo') {
		$format = new formato_coreo();
	    } else {
		$format = new formato_imagen();
	    }

	    $format->get($f['format_id']);
	    $sub = ((int) $f['qty'] * $format->precio);
	    $preciototal += $sub;
	}
    }

    $totalpedido = number_format($preciototal, 2, ".", "");
    ?>
    <form id="confirm-frm" name="confirm-frm" action="ajax/save_order" method="post">
        <table cellpadding="3">
    	<tr>
    	    <td>Nombre:</td>
    	    <td><input type="text" name="nombre" id="nombre" value="<?= $presu->nombre ?>" /></td>
    	</tr>
    	<tr>
    	    <td>Apellido:</td>
    	    <td><input type="text" name="apellido" id="apellido" value="<?= $presu->apellido ?>" /></td>
    	</tr>
    	<tr>
    	    <td>Telefono:</td>
    	    <td><input type="text" name="tel" id="tel" value="<?= $presu->telefono ?>" /></td>
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
    		<div id="vendedor-clave" style="display: none"><input type="password" name="clave" id="clave" size="10" maxlength="20" value="" /> <a href="javascript:void(0)" id="clavedescuento">Aplicar</a></div>
    		<div id="vendedor-clave-ok" style="display: none; font-weight:bold;color: #00a000;">Clave correcta</div>
    	    </td>
    	</tr>
    	<tr>
    	    <td>Retiro:</td>
    	    <td><input type="text" name="fecha" id="fecha" value="<?= $fechaRetiro ?>" size="10" maxlength="10" /> <input type="text" name="hora" id="hora" value="<?= $horaRetiro ?>" size="5" maxlength="5" /></td>
    	</tr>
    	<!-- <tr>
    	    <td>&nbsp;</td>
    	    <td>&nbsp;</td>
    	</tr>
    	<tr id="descuento-aplicar" style="display: none">
    	    <td colspan="2" style="text-align: center;">
    		<div id="descuento-link"><a href="javascript:void(0)" id="aplicardto">Aplicar Descuento</a></div>
    		<div id="descuento-monto" style="display: none">Descuento<br/><br/>$ <input type="text" name="descuento" id="descuento" maxlength="10" size="6" value="" /> <a href="javascript:void(0)" id="descuento-aplicar">Aplicar</a>&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" id="descuento-cancel">Cancelar</a></div>

    	    </td>
    	</tr> -->
    	<tr>
    	    <td>&nbsp;</td>
    	    <td>&nbsp;</td>
    	</tr>
    	<tr>
    	    <td colspan="2" style="text-align: center;font-size: 20px;">
    		Total: <strong>$ <span id="total-pedido"><?= MaxterHlp::fn($presu->total) ?></span></strong>
    	    </td>
    	</tr>
    	<tr>
	    <td>Se&ntilde;a:</td>
	    <td>$ <?= MaxterHlp::fn($presu->sena) ?></td>
    	</tr>
    	<tr>
	    <td>Pendiente:</td>
	    <td>$ <?= MaxterHlp::fn($presu->total - $presu->sena) ?></td>
    	</tr>
        </table>
    </form>
    <script>
        var vendedor_verificado = false;

        $("#aplicardto").click(function () {
    	if (vendedor_verificado) {
    	    $("#descuento-link").hide();
    	    $("#descuento-monto").show();
    	    $("#descuento").focus();
    	}
        });


        $("#descuento-cancel").click(function () {
    	$("#descuento").val('');
    	$("#descuento-link").show();
    	$("#descuento-monto").hide();
    	$("#descuento-aplicar").show();
    	AplicarDescuento();
        });
        $("#clave").keydown(function (e) {
    	if (e.keyCode == 13) {
    	    e.preventDefault();
    	    validateDtoPass();
    	}
        })
        $("#descuento").keydown(function (e) {
    	if (e.keyCode == 13) {
    	    e.preventDefault();
    	    AplicarDescuento();
    	}
        })
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
	
	
	//  checkeo si es dueño del presupuesto	
	/* if (id != <?= $presu->idVendedor ?>) {
	    alert('Este presupuesto es de otro vendedor.');
	    return;
	} */
	
	
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
    	alert('Clave invÃ¡lida');
        }

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
    	AplicarDescuento();
        });

        $('#view-media').dialog({
    	title: "Confirmar pedido",
    	buttons: {
    	    "Aceptar": function () {

    <?
    //	chequea si el presupuesto esta completo
    /* if (!Presu::isValid()) { ?>
			alert('El presupuesto no esta completo');
			return;
    <? }  */?>

    		if (!vendedor_verificado) {
    		    alert('Seleccione vendedor');
    		    return;
    		}

    		if (($("#nombre").val() == '') || ($("#apellido").val() == '')) {
    		    alert('Ingrese nombre y apellido del cliente');
    		    return;
    		}


    		$('#confirm-frm').submit();
    		$('#view-media').dialog({buttons: null});
    		$('#view-media').html('<div style="text-align: center;">Procesando pedido...<br /><br /><img src="static/img/loader.gif" /></div>');
    	    },
    	    "Cerrar": function () {
    		$(this).dialog("close");
    		refreshCart();
    		$('#view-media').html("");
    	    }
    	}
        });

        $("#descuento-aplicar").click(function () {
    	AplicarDescuento();
        });

        function AplicarDescuento() {
    	if (vendedor_verificado) {
    	    var d = parseFloat($("#descuento").val());
    	    if (isNaN(d))
    		d = 0;
    	    var t = <?= $totalpedido ?> - d;
    	    if (t < 0)
    		t = 0;
    	    //$("#total-pedido").html(t.toFixed(2));
    	} else {
    	    $("#descuento").val("");
    	    var t = <?= $totalpedido ?>;
    	    //$("#total-pedido").html(t.toFixed(2));
    	}
        }

        $('#confirm-frm').ajaxForm({target: "#view-media"});

        $("#view-media").dialog("option", "position", "center");
    </script>
<? } ?>