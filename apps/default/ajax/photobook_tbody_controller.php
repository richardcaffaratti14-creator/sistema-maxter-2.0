<?php

$vid = Http::getOverPost('vid');

$sql = "SELECT fotolibros.id AS fotolibros_id, 
    fotolibros.nombre AS fotolibros_nombre, 
    fotolibros.apellido AS fotolibros_apellido, 
    fotolibros.telefono AS fotolibros_telefono, 
    fotolibros.pedido AS fotolibros_pedido, 
    fotolibros.total AS fotolibros_total, 
    fotolibros.sena AS fotolibros_sena, 
    fotolibros.idVendedor AS fotolibros_idVendedor 
FROM fotolibros
WHERE id NOT IN (SELECT idFotolibro FROM pedidos 
WHERE idFotolibro <> 0 AND idFotolibro IS NOT NULL GROUP BY idFotolibro )";

//MaxterHlp::d($sql);


$presus = new fotolibros();
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


foreach($presus as $p) { ?>
<tr>
    <td><?=  $p->id ?></td>
    <td><?=  $p->apellido.", ".$p->nombre ?></td>
    <td><?=  $vhp[$p->idVendedor]->Vendedor ?></td>
    <td><?= MaxterHlp::fn($p->total) ?></td>
    <td><a href="?photobook_id=<?= ($p->id) ?>">RETOMAR</a> </td>
</tr> 
<? }