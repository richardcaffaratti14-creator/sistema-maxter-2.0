<?php

// Global user functions
// Page Loading event
function Page_Loading() {

    //echo "Page Loading";
}

// Page Unloaded event
function Page_Unloaded() {

    //echo "Page Unloaded";
}

function getNextOrder($table, $w = "") {
    $rs = mysql_query("select ifnull(max(orden),0) + 1 as q from " . $table . " " . $w);
    if ($r = mysql_fetch_array($rs))
	return $r['q'];
    else
	return 1;
}

function sanitizeFancyURL($fancyURL) {
    $fancyURL = str_replace("  ", " ", trim($fancyURL));
    $fancyURL = str_replace(" ", "_", $fancyURL);
    return preg_replace("/[^a-z0-9_]/", "", strtolower($fancyURL));
}

/** ----------------------------------------------------------------------------------------
 * RANGO DE PRECIOS DE MARCAJE -------------------------------------------------------------
  ------------------------------------------------------------------------------------------- */
$_globalRangoPrecioEditorInitialized = false;

function getRangoPreciosEditor($arrayPrecios, $FieldName) {
    if ($arrayPrecios === false)
	$arrayPrecios = array();

    if (count($arrayPrecios) == 0) {
	//primer item siempre es HASTA (- de)
	$arrayPrecios[] = array(
	    'HASTA' => 1000,
	    'PRECIO' => 0.000
	);
    }
    if (count($arrayPrecios) == 1) {
	//segundo item en adelante siempre es DESDE (+ o igual de)
	$arrayPrecios[] = array(
	    'DESDE' => 1000,
	    'PRECIO' => 0.000
	);
    }
    foreach ($arrayPrecios as $ix => $data) {
	?>
	<div data-ix="<?= $ix ?>" id="<?= $FieldName ?>DIV_<?= $ix ?>" class="rangomargajecontainer">
	    <input type="hidden" name="<?= $FieldName ?>IX[]" value="<?= $ix ?>" />
	<? if ($ix > 1) { ?>
	        <a href="javascript:void(0)" data-ix="<?= $ix ?>" data-field="<?= $FieldName ?>" class="deleterangomarcaje">X</a>
	    <? } ?>

	    <strong><?= $ix == 0 ? "Menos de" : 'Desde' ?></strong><br/>
	    <? if ($ix == 0) { ?>
	        <span id="<?= $FieldName ?>DESDE" style="font-weight:bold;"><?= @$arrayPrecios[1]['DESDE'] ?></span> un.
	    <? } else {
		?>
	        <input style="width: 50px;" maxlength="7" type="text" name="<?= $FieldName ?>UN_<?= $ix ?>" id="<?= $FieldName ?>UN_<?= $ix ?>" value="<?= $ix == 0 ? $data['HASTA'] : $data['DESDE'] ?>"> un.
	    <? } ?>
	    <br/>

	    <input style="width: 50px;" maxlength="7" type="text" name="<?= $FieldName ?>PRECIO_<?= $ix ?>" id="<?= $FieldName ?>PRECIO_<?= $ix ?>" value="<?php echo number_format($data['PRECIO'], 4) ?>"> &euro;
	</div>
	<?
    }
    ?>
    <div style="float: left;" id="<?= $FieldName ?>NEW-DIV-CONT">
    </div>

    <div style="float: left; padding-top: 25px;" id="<?= $FieldName ?>NEW-BTN">
        <a href="javascript:void(0)" title="Nuevo rango de precio" data-ix="<?= count($arrayPrecios) ?>" data-field="<?= $FieldName ?>" class="addrangomarcaje">+</a>
    </div>
    <?
    global $_globalRangoPrecioEditorInitialized;
    if (!$_globalRangoPrecioEditorInitialized) {
	$_globalRangoPrecioEditorInitialized = true;
	?>
	<div style="display: none" id="rangopreciotemplate">
	    <div data-ix="%IX%" id="%FIELD%DIV_%IX%" class="rangomargajecontainer">
		<input type="hidden" name="%FIELD%IX[]" value="%IX%" />
		<a href="javascript:void(0)" data-ix="%IX%" data-field="%FIELD%" class="deleterangomarcaje">X</a>
		<strong>Desde</strong><br/>
		<input style="width: 50px;" maxlength="7" type="text" name="%FIELD%UN_%IX%" id="%FIELD%UN_%IX%" value=""> un.
		<br/>
		<input style="width: 50px;" maxlength="7" type="text" name="%FIELD%PRECIO_%IX%" id="%FIELD%PRECIO_%IX%" value=""> &euro;
	    </div>
	</div>

	<script type="text/javascript">
	    $(document).ready(function () {
		$(document).on("click", ".addrangomarcaje", function () {
		    var ix = parseInt($(this).attr('data-ix'));
		    if (isNaN(ix))
			ix = 0;
		    var f = $(this).attr('data-field');

		    $("#" + f + "NEW-DIV-CONT").append($("#rangopreciotemplate").html().replace(/\%IX\%/g, ix).replace(/\%FIELD\%/g, f));

		    ix++;
		    $(this).attr('data-ix', ix);
		});

		$(document).on("click", ".deleterangomarcaje", function () {
		    var ix = $(this).attr('data-ix');
		    var f = $(this).attr('data-field');

		    $("#" + f + "DIV_" + ix).remove();
		});

	    });
	</script>

	<?
    }
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
    	$("#<?= $FieldName ?>UN_1").change(function () {
    	    $("#<?= $FieldName ?>DESDE").html($(this).val());
    	})
        });
    </script>
    <?
}

function getRangoPreciosSerializadoParaDB($f) {
    $ret = array();

    $ixs = getParameterArrayOfInts($_POST, $f . 'IX');
    if (!is_array($ixs))
	return serialize($ret);

    $first = true;
    foreach ($ixs as $ix => $value) {
	if (!is_numeric($_POST[$f . 'PRECIO_' . $ix]) || (!$first && !is_numeric($_POST[$f . 'UN_' . $ix])))
	    continue;

	$ret[] = array(
	    ($first ? 'HASTA' : 'DESDE') => $first ? (int) @$_POST[$f . 'UN_1'] : $_POST[$f . 'UN_' . $ix],
	    "PRECIO" => $_POST[$f . 'PRECIO_' . $ix]
	);

	$first = false;
    }

    return serialize($ret);
}

function getRangoPreciosSerializadoParaVIEW($arraySerializado) {

    $a = @unserialize($arraySerializado);
    if ($a === false)
	return;

    echo "<table cellspacing='0'>";
    foreach ($a as $ix => $value) {
	echo "<tr><td>" . ($ix == 0 ? "< " . $value["HASTA"] : ">= " . $value["DESDE"]) . "</td><td style='text-align:right; font-weight:bold'>" . number_format($value["PRECIO"], 4) . " &euro;</td></tr>";
    }
    echo "</table>";
}

/** ----------------------------------------------------------------------------------------
 * RANGO DE PRECIOS DE MARCAJE -------------------------------------------------------------
 -------------------------------------------------------------------------------------------*/


