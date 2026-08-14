<?php

error_reporting(E_ALL ^ E_NOTICE);

$nombre = Http::getOverPost('nombre');
$apellido = Http::getOverPost('apellido');
$tel = Http::getOverPost('tel');
$vendedor = Http::getOverPost('vendedor');
$sena = Http::getOverPost('sena');
$evt_name = getSiteInfo('evento');

//--------------------------------------------------------------------

$presu = new fotolibros();
$presu->nombre = $nombre;
$presu->apellido = $apellido;
$presu->telefono = $tel;
$presu->subtotal = 0;
$presu->descuento = 0;
$presu->total = 0;
$presu->sena = $sena;

$presu->evento = $evt_name;
$presu->estado = 0;

$presu->idVendedor = $vendedor;

$pid = $presu->save();


?>
<script>
    $('#view-media').dialog({
	title: "FotoLibro guardado",
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
<h3>PhotoLibro guardado</h3>
<a href="ajax/pdf_photobook?id=<?= $pid ?>" target="_blank">
    Imprimir PhotoLibro
</a>