<?
//  ------------------------------------
$presu_id = PhotoBook::getID();

$presu = new fotolibros();
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

$min_pics = getSiteInfo('photobook_min_pics');

$qtypedido = count($pedido);




if (!count($pedido) > 0) {
    ?>
    <h2>No hay fotos o videos en su pedido.</h2>
    <script>
        $('#view-media').dialog({
    	title: "Confirmar FotoLibro",
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
} elseif ($qtypedido < $min_pics) {
    ?>
    <h2>Debe seleccionar por lo menos <?= $min_pics ?> fotos.</h2>
    <script>
        $('#view-media').dialog({
    	title: "Confirmar FotoLibro",
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
    $imgqty = 0;
    foreach ($pedido as $item) {
	foreach ($item['formats'] as $f) {
	    if ($item['type'] == 'vid') {
		$format = new formato_video();
	    } elseif ($item['type'] == 'coreo') {
		$format = new formato_coreo();
	    } else {
		$format = new formato_imagen();
		
		$imgqty += $f['qty'];
	    }

	    $format->get($f['format_id']);
	    $sub = ((int) $f['qty'] * $format->precio);
	    $preciototal += $sub;
	}
    }

    //$totalpedido = number_format($preciototal, 2, ".", "");
    $precio_pb = floatval(getSiteInfo('photobook_image_price'));
    $totalpedido = $imgqty * $precio_pb;
    //die($imgqty .' - '. $precio_pb .' - '. $totalpedido);
    ?>
    <form id="confirm-frm" name="confirm-frm" action="ajax/save_order" method="post">
        <table cellpadding="3">
    	<tr>
    	    <td>Nombre:</td>
    	    <td>
		<input type="text" name="nombre" id="nombre" 
		       value="<?= $presu->nombre ?>" />
		<input type="hidden" name="invalid_discount" id="invalid_discount" 
		       value="0" />
	    </td>
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
    		<div id="vendedor-clave" style="display: none"><input 
    			type="password" name="clave" id="clave" size="10" 
    			maxlength="20" value="" /> <a href="javascript:void(0)" 
    			id="clavedescuento">Aplicar</a></div>
    		<div id="vendedor-clave-ok" 
    		     style="display: none; font-weight:bold;color: #00a000;">
    		    Clave correcta
    		</div>
    	    </td>
    	</tr>
    	<tr>
    	    <td>Retiro:</td>
    	    <td><input type="text" name="fecha" id="fecha" value="<?= $fechaRetiro ?>"
    		       size="10" maxlength="10" /> <input type="text" name="hora" 
    		       id="hora" value="<?= $horaRetiro ?>" size="5" maxlength="5" />
    	    </td>
    	</tr>
    	<tr>
    	    <td>Descuento:</td>
    	    <td><input type="text" name="descuento" maxlength="3" id="descuento" 
    		       autocomplete="false" value="0" /></td>
    	</tr>
    	<tr class="autorizacion">
    	    <td colspan="2">
    		<span style="color:red;">Descuento m&aacute;ximo excedido. Necesita 
    		    autorizaci&oacute;n</span>
    	    </td>
    	</tr>
    	<tr class="autorizacion">
    	    <td>Cod. Aut.:</td>
	    <td><input type="password" name="aut" id="aut" 
    		       autocomplete="false" /></td>
    	</tr>
    	<tr>
    	    <td>&nbsp;</td>
    	    <td>&nbsp;</td>
    	</tr>
    	<tr>
    	    <td colspan="2" style="text-align: center;font-size: 20px;">
    		Total: <strong>$ 
		    <span id="total-pedido"><?= MaxterHlp::fn($totalpedido) ?></span>
    		</strong>
    	    </td>
    	</tr>
    	<tr>
    	    <td>Se&ntilde;a:</td>
    	    <td>$ <?= MaxterHlp::fn($presu->sena) ?></td>
    	</tr>
    	<tr>
    	    <td>Descuento:</td>
	    <td>$ <span id="desc_pesos">0</span></td>
    	</tr>
    	<tr>
    	    <td>Pendiente:</td>
	    <td>$ <span id="pendiente"><?= MaxterHlp::fn($totalpedido - $presu->sena) ?></span></td>
    	</tr>
        </table>
    </form>
    <script>
        var vendedor_verificado = false;
	var sena = <?= $presu->sena ?>; 

        $("#aplicardto").click(function () {
    	if (vendedor_verificado) {
    	    $("#descuento-link").hide();
    	    $("#descuento-monto").show();
    	    $("#descuento").focus();
    	}
        });


        $("#clave").keydown(function (e) {
    	if (e.keyCode == 13) {
    	    e.preventDefault();
    	    validateDtoPass();
    	}
        });

        $("#descuento").keyup(function (e) {

    	AplicarDescuento();
        });

        $("#clavedescuento").click(function () {
    	validateDtoPass();
        });

        function AplicarDescuento() {

    	var desc = parseFloat($('#descuento').val());
	if (isNaN(desc)) {
	    desc = 0;
	}
	
    	var tot = <?= $totalpedido ?>;


    	if (!isValidPBDiscount(tot, desc)) {
    	    $('.autorizacion').show();
    	    $('#invalid_discount').val(1);
    	} else {
    	    $('.autorizacion').hide();
    	    $('#invalid_discount').val(0);
    	}

	//
	var descpesos = (tot * desc / 100);
		
	tot = tot - descpesos;
	$('#desc_pesos').html(descpesos);
	
	
	//console.info(tot);
	$('#total-pedido').html(tot);
	
	$('#pendiente').html(tot - sena);
	

        }   // AplicarDescuento	----------------------------------------------

        $('.autorizacion').hide();




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
    		return;
    	    }

    	vendedor_verificado = false;
    	alert('Clave inválida');
        }

        $("#vendedor").change(function () {
    	vendedor_verificado = false;

    	$("#vendedor-clave-ok").hide();
    	//$("#descuento-aplicar").hide();

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

        $('#confirm-frm').ajaxForm({target: "#view-media"}
        );

        $("#view-media").dialog("option", "position", "center");
    </script>
<? } ?>