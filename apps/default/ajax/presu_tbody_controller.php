<?php

$vid = Http::getOverPost('vid');

$sql = "SELECT presupuestos.id AS presupuestos_id, 
    presupuestos.nombre AS presupuestos_nombre, 
    presupuestos.apellido AS presupuestos_apellido, 
    presupuestos.telefono AS presupuestos_telefono, 
    presupuestos.presupuesto AS presupuestos_presupuesto, 
    presupuestos.pedido AS presupuestos_pedido, 
    presupuestos.subtotal AS presupuestos_subtotal, 
    presupuestos.descuento AS presupuestos_descuento, 
    presupuestos.total AS presupuestos_total, 
    presupuestos.sena AS presupuestos_sena, 
    presupuestos.idVendedor AS presupuestos_idVendedor 
FROM presupuestos
WHERE id NOT IN (SELECT idPresupuesto FROM pedidos 
WHERE idPresupuesto <> 0 AND idPresupuesto IS NOT NULL GROUP BY idPresupuesto )";

//MaxterHlp::d($sql);


$presus = new presupuestos();
//$presus->select();
$presus = $presus->query($sql, TRUE);
//MaxterHlp::d($presus);
//$DB->showLatestQueries();

$vendedoresObj = new vendedores();
$vendedoresObj->addCondition("Activo", 1);
$vendedores = $vendedoresObj->select();

$vhp = array();
foreach($vendedores as $v) {
    $vhp[$v->id] = $v;
}

/*print_r($vhp); */

foreach($presus as $p) { ?>
<tr>
    <td><?=  $p->id ?></td>
    <td><?=  $p->apellido.", ".$p->nombre ?></td>
    <td><?=  $vhp[$p->idVendedor]->Vendedor ?></td>
    <td><?= MaxterHlp::fn($p->total) ?></td>
    <td><a href="?presu_id=<?= base64_encode($p->id) ?>">RETOMAR</a> </td>
</tr> 
<? }