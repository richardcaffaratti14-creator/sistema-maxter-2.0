<?php

require_once('mpdf/mpdf.php');
$obj_pdf = new mPDF('utf-8', 'A4');
$obj_pdf->keep_table_proportions = true;
$obj_pdf->shrink_tables_to_fit = 1;
$obj_pdf->SetAuthor('');
$obj_pdf->SetTitle("Presupuesto");
$obj_pdf->SetKeywords('');
$obj_pdf->SetSubject('');
$obj_pdf->SetMargins(0, 0, 0);
$obj_pdf->SetFont('arial', '', 12);


$pid = (int) $_GET['id'];

$pedido_db = new fotolibros();
$pedido_db->get($pid);
if (!$pedido_db->isAvailable()) {
    die('No existe ese FotoLibro.');
}

$vend = new vendedores();
$vend->get($pedido_db->idVendedor);


/*
  $pedido_db = new pedidos();
  $pedido_db->get($pid);

  $pedido = unserialize($pedido_db->pedido);
  $extra = unserialize($pedido_db->extra);
 */
//echo "<xmp>";
//print_r($pedido_db); 
//include 'pdf-template.php';
//die();

ob_start();
include 'pdf-template_photobook.php';
$obj_pdf->writeHTML(ob_get_clean());

if (isset($_pdf_file) && !empty($_pdf_file)) {
    $obj_pdf->Output($_pdf_file, 'F');
} else {
    $obj_pdf->Output('fotolibro ' . $pid . '.pdf', 'I');
}
