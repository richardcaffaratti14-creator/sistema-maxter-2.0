<?
$pedido = SessionManager::getValue('pedido');

if (!is_array($pedido)) {
    $pedido = array();
}

$vendedoresObj = new vendedores();
$vendedoresObj->addCondition("Activo", 1);
$vendedores = $vendedoresObj->select();


$clave_aut_sup = addslashes(getSiteInfo('claveautorizacion'));

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
		if ($item['type'] == 'acc') {
			$sub = $item['qty'] * $item['amt'];
			$preciototal += $sub;
		}
		else {
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
    }

    $totalpedido = number_format($preciototal, 2, ".", "");
    ?>
    <form id="confirm-frm" name="confirm-frm" action="ajax/save_order" method="post">
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
    		<div id="vendedor-clave" style="display: none"><input type="password" name="clave" id="clave" size="10" maxlength="20" value="" /> <a href="javascript:void(0)" id="clavedescuento">Aplicar</a></div>
    		<div id="vendedor-clave-ok" style="display: none; font-weight:bold;color: #00a000;">Clave correcta</div>
    	    </td>
    	</tr>
    	<tr>
    	    <td>Retiro:</td>
    	    <td><input type="text" name="fecha" id="fecha" value="<?= $fechaRetiro ?>" size="10" maxlength="10" /> <input type="text" name="hora" id="hora" value="<?= $horaRetiro ?>" size="5" maxlength="5" /></td>
    	</tr>
    	<tr>
    	    <td>&nbsp;</td>
    	    <td>&nbsp;</td>
    	</tr>
    	<tr id="descuento-aplicar" style="display: none">
    	    <td colspan="2" style="text-align: center;">
    		<div id="descuento-link"><a href="javascript:void(0)" id="aplicardto">Aplicar Descuento</a></div>
    		<div id="descuento-monto" style="display: none">
    		    Descuento<br/><br/>$ <input type="text" name="descuento" id="descuento" maxlength="10" size="6" value="" autocomplete="off" /> <a href="javascript:void(0)" id="descuento-aplicar">Aplicar</a>&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" id="descuento-cancel">Cancelar</a></div>

    	    </td>
    	</tr>

    	<tr>
    	    <td>&nbsp;</td>
    	    <td>&nbsp;</td>
    	</tr>
    	<tr class="autorizacion">
    	    <td colspan="2">
    		<span style="color:red;">Descuento m&aacute;ximo excedido. Necesita autorizaci&oacute;n del supervisor</span>
    	    </td>
    	</tr>

    	<!-- <tbody id="autsection"> -->

	<tr class="maxventaaut">
    	    <td colspan="2"><strong>M&aacute;ximo de venta excedido. <br />Necesita autorizaci&oacute;n del supervisor</strong></td>
    	</tr>
	<tr class="autorizacion autorizacionclave">
    	    <td>Clave Aut.:</td>
    	    <td><input type="password" name="aut" id="aut" value="" /></td>
    	</tr>
    	<!-- </tbody> -->
    	<tr>
    	    <td>&nbsp;</td>
    	    <td>&nbsp;</td>
    	</tr>
    	<tr>
    	    <td colspan="2" style="text-align: center;font-size: 20px;">Total: <strong>$ <span id="total-pedido"><?= $totalpedido ?></span></strong></td>
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

        $('.autorizacion').hide();


        $('#descuento').on('input', function (e) {
			e.preventDefault();
			//------------------------
			var d = parseFloat($(this).val());


			if (d > 0) {
				if (!isValidDiscount(<?= $preciototal ?>, d)) {
					$('.autorizacion').show();
					$('#invalid_discount').val(1);
				} else {
					$('.autorizacion').hide();
					$('#invalid_discount').val(0);

				}
			} else {
				$('.autorizacion').hide();
				$('#invalid_discount').val(0);
			}
        }); // oninput


        $("#descuento-cancel").click(function () {
			$("#descuento").val('');
			$("#descuento-link").show();
			$("#descuento-monto").hide();
			$(".autorizacion").hide();
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
    		/**/
    		if (!vendedor_verificado) {
    		    alert('Seleccione vendedor');
    		    return;
    		}

    		if (($("#nombre").val() == '') || ($("#apellido").val() == '')) {
    		    alert('Ingrese nombre y apellido del cliente');
    		    return;
    		}
    		/**/
			
			
			var dto = parseFloat($('#descuento').val());
			if (isNaN(dto)) dto = 0;
			if ((dto>0) && !isValidDiscount(<?= $preciototal ?>, dto) && ($('#aut').val() != '<?= $clave_aut_sup ?>')) {
    		    alert('El descuento necesita autorizaci\u00F3n del supervisor');
    		    return;
    		}
				
			
    		var t = parseFloat($("#total-pedido").html());
    		if (isMaxExceeded(t) && $('#aut').val() != '<?= $clave_aut_sup ?>') {
    		    alert('El monto total necesita autorizaci\u00F3n del supervisor');
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

				//	maximo descuento por pedido
				//	mas en default.php
				/* if (!isValidDiscount(<?= $totalpedido ?>, d)) {
				 alert('Se ha exedido el descuento maximo.');
				 //return;
				 t = <?= $totalpedido ?>;
				 $("#descuento").val(0);
				 } */

				$("#total-pedido").html(t.toFixed(2));
			} else {
				$("#descuento").val("");
				var t = <?= $totalpedido ?>;
				$("#total-pedido").html(t.toFixed(2));
			}

			maxExceeded(t);

        }

        function maxExceeded(tot) {
			if (isMaxExceeded(tot)) {
				$('.maxventaaut').show();
				$('.autorizacionclave').show();
			} else {
				var d = parseFloat($('#descuento').val());
				if (isNaN(d)) d = 0;
				if (isValidDiscount(<?= $preciototal ?>, d))
					$('.autorizacionclave').hide();
					
				$('.maxventaaut').hide();
			}

        }

        maxExceeded(<?= $totalpedido ?>);

        $('#confirm-frm').ajaxForm({target: "#view-media"});


        $("#view-media").dialog("option", "position", "center");
    </script>
<? } ?>