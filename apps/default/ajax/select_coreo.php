<?

$presu_mode = Presu::getID() != -1;

$media = Http::getOverPost('m');
$folder = base64_decode($media);

$formatoCoreos = new formato_coreo();
$formatoCoreos->orderBy('Nombre');
$formatoCoreos_tmp = $formatoCoreos = $formatoCoreos->select();

if ($presu_mode) {
    $formatoCoreos = array();
    foreach($formatoCoreos_tmp as $f) {
	if (Presu::isValidCoreoFormat($f->id)) {
	    $formatoCoreos[] = $f;
	}
    }
}

?>
<br/>
<div style="text-align: center;font-weight:bold;">Seleccionar el tipo y costo</div>
<table style="margin: 0 auto;">
    <tr>
	<td>&nbsp;</td>
    </tr>
    <tr>
	<td style="text-align: center;">
	    <select id="add_formatocoreo" name="add_formatocoreo" style="width: 100%; padding: 4px 7px; font-size: 16px;">
		<option value=""></option>
		<?
		foreach ($formatoCoreos as $v) {
		    if (($v->Sufijo != '') && !is_null($v->Sufijo)) {
			$len = strlen($v->Sufijo);
			$sufijo = substr($folder, ($len * (-1)));
			if (strtolower($sufijo) != strtolower($v->Sufijo))
			    continue;
		    }
		    ?><option value="<? echo($v->id) ?>"><? echo($v->Nombre) ?> - $ <? echo(number_format($v->Precio, 2)); ?></option> <?
		}
		?>
	    </select>
	</td>
    </tr>
    <tr>
	<td>&nbsp;</td>
    </tr>
</table>

