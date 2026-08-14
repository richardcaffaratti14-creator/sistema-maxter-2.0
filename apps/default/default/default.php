<?
$folder = Http::getOverPost('f');
$current_page = Http::getOverPost('page');

$folder_raw = $folder;
$folder = base64_decode($folder);
$folder = $folder == '' ? PATH_ORIGINALS_ROOT : $folder . '/';

$breadcrumbs = explode('/', str_replace(PATH_ORIGINALS_ROOT, '', $folder));

$q = Http::getOverPost('q');

session_write_close();

//$files = File::getFilesForm($folder . '*.jpg');
$current_page = $current_page == '' ? 0 : $current_page;
$files_obj = File::getPaginatedFilesForm($folder, $current_page, MAX_FILES_PER_PAGE, $q);
?>
<style>
    .img-holder{
	width: <?= THUMB_MAX_W ?>px;
	height: <?= (THUMB_MAX_H + 30) ?>px;
	border: 1px solid #F14E23;
	padding: 5px;
	margin: 0 14px 8px 0;
	float: left;
    }

    .img-holder:hover {
	border: 1px solid #58585A;
	background-color: #D8D8D8;
    }

    .img-holder a{
	color: #F14E23;
	font-weight: bold;
	text-decoration: none;
    }

    .img-holder a:hover{
	text-decoration: underline;
    }

    .img-thumb{
	width: <?= THUMB_MAX_W ?>px;
	height: <?= THUMB_MAX_H ?>px;
	line-height: <?= (THUMB_MAX_H - 2) ?>px;
	text-align: center;
	vertical-align: middle;
	/*background: url(static/img/stripe.png)*/
    }

    .img-thumb img {
	margin: auto;
	vertical-align: middle;
    }

    .img-label {
	font-size: 0.8em;
	width: <?= THUMB_MAX_W ?>px;
	height: 15px;
	overflow: hidden;
	margin-top: 3px;
    }



</style>

<div class="box-text">
    <h1 style="width: 70%;" class="floatl">
	<a href="?f=<?= base64_encode(PATH_ORIGINALS_ROOT) ?>"><?= EVENT_NAME ?></a> \ <span id='breadcrumb_folder'></span>
	<?
	$path = PATH_ORIGINALS_ROOT;
	foreach ($breadcrumbs as $bread) {
	    if ($bread != '') {
		$path .= $bread . '/';
		?>
		<a href="?f=<?= base64_encode($path) ?>"><?= utf8_encode($bread) ?></a> \
		<?
	    }
	}
	?>
    </h1>

    <div id="search-bar" class="floatr">
		<form action="" method="get" id='_searchform' onsubmit="return dosearch()">
			<input id="q" type="text" name="q" value="<?= $q ?>" />
			<input id="qb" type="submit" value="Buscar" />
			<input type="hidden" name="f" id='_search_f' value="<?= $folder_raw ?>" />
			<a href="javascript:vertodo()">Ver Todos</a>
		</form>
    </div>

    <div class="clearb"></div>
</div>

<div id="minicart" class="bar"></div>

<div class="box-text">
    <div id="gallery" class="floatl">
<!--		<div id="gallery-inner">
			<?
			if (count($files_obj->files) > 0) {
			foreach ($files_obj->files as $file) {
				?>
				<?
				$im = str_replace(PATH_ORIGINALS_ROOT, '', $file);
				$im = utf8_encode($im);
				?>
				<div class="img-holder">
				<div class="img-thumb">
					<a href="javascript:openViewMedia('<?= base64_encode($im) ?>');" title="Seleccionar Copias">
					<? if (strtolower(pathinfo($im, PATHINFO_EXTENSION)) == 'mp4') { ?>
						<img src="static/img/video_icon_thumb.png" />
					<? } else { ?>
						<img src="<?= str_replace("images/fotos", "/maxterfotos", Img::crop('images/' . PATH_ORIGINALS . $im, THUMB_SIZE)) ?>" />
						<img src="<?= Img::crop('images/' . PATH_ORIGINALS . $im, THUMB_SIZE) ?>" />
					<? } ?>
					</a>
				</div>
				<div class="img-label">
					<?= basename($im) ?>
				</div>
				<a href="javascript:openViewMedia('<?= base64_encode($im) ?>');" title="Seleccionar Copias">Seleccionar Copias</a>

				</div>
			<? } ?>
				<div class="clearb"></div>
			<? } ?>
		</div>

		<? if (count($files_obj->files) > 0) { ?>

    	<ul class="paginator">
		<? if ($files_obj->pages_qty > 1) { ?>
		    <? if ($current_page == 0) { ?>

		    <? } else { ?>
	    	    <li><a href="?f=<?= $folder_raw ?>&page=<?= ($current_page - 1) ?>&q=<?= $q ?>">&larr;</a></li>
		    <? } ?>

		    <?
		    for ($i = 0; $i < $files_obj->pages_qty; $i++) {
			if ($current_page == $i) {
			    ?>
			    <li class="current"><?= ($i + 1) ?></li>
			<? } else { ?>
			    <li><a href="?f=<?= $folder_raw ?>&page=<?= $i ?>&q=<?= $q ?>"><?= ($i + 1) ?></a></li>
			    <?
			}
		    }
		    ?>

		    <? if ($current_page == $files_obj->pages_qty - 1) { ?>

		    <? } else { ?>
	    	    <li><a href="?f=<?= $folder_raw ?>&page=<?= ($current_page + 1) ?>&q=<?= $q ?>">&rarr;</a></li>
			<?
		    }
		}
		?>

    	</ul>
	<? } ?>
-->
    </div>
	
    <div id="folders" class="floatl">
		<ul id="folder-tree"><li><a href="javascript:_load_folder_pictures('<?= base64_encode(PATH_ORIGINALS_ROOT) ?>', -1,'')"><?= "PRINCIPAL"?></a> <a style="font-size: 10px; float: right; font-weight:normal;display: inline-block; margin-right: 10px;" href="?_clear_cache=1">refrescar carpetas</a>
			<?= Folder::createTree(PATH_ORIGINALS_ROOT, '?f=', '', base64_decode($folder_raw)); ?>
	    </li></ul>
    </div>
</div>
<div class="clearb"></div>
<div id="view-media" title="Seleccionar Copias" style="display: none; "></div>
<div id="msg" style="display: none;"></div>

<script type="text/javascript" charset="utf-8">

    var show_dialog = 0;
    var CurrentTimeOut = 0;

<?
$descuentos = getSiteInfo('descuentosmax');
$maxventapedido = getSiteInfo('maxventapedido');


?>
	function dosearch() {
		_load_pictures($("#search-bar #_search_f").val(), 0 , $("#search-bar #q").val());
		return false;
	}
	function vertodo() {
		$("#search-bar #q").val('');
		dosearch();
	}
	function _load_pictures( folder, page, q ) {
		$("#gallery").load('ajax/pagination_ajax_controller?f=' + folder + '&page=' + page + '&q=' + q);
	}
	function _load_folder_pictures(folder,_global_sidebar_folder_ix, folder_name ) {
		
		_current_folder = folder;	//global var that uses other modules like coreo
		
		$("#search-bar #_search_f").val(folder)
		
		$("#breadcrumb_folder").html(folder_name);
		
		_load_pictures(folder, 0, $("#search-bar #q").val());
		
		$("#folder-tree a._expand_folder").removeClass('_expanded');
		$("#folder-tree li").removeClass('selected');
		
		$("#folder-tree li#_li_ft_" + _global_sidebar_folder_ix).addClass('selected');
		$("#folder-tree li#_li_ft_" + _global_sidebar_folder_ix + ' > a._expand_folder').toggleClass('_expanded');
		$("#folder-tree ul#_ft_" + _global_sidebar_folder_ix).toggle();
	}
	$(document).ready(function() {
		_load_pictures('<?= $folder_raw ?>', 0, '<?= addslashes($q) ?>');
	});

//    $(document).ready(function () {
//		$("#folder-tree a._expand_folder").click(function (e) {
//			e.preventDefault();
//			var id = $(this).attr('data-id');
//			$("#folder-tree ul#_ft_" + id).toggle();
//			$(this).toggleClass('_expanded');
//		});
//    });

    this.imagePreview = function () {
	/* CONFIG */

	xOffset = 10;
	yOffset = -520;

	/* END CONFIG */
	$("a.fname").hover(function (e) {
	    this.t = this.title;
	    this.title = "";
	    //var c = (this.t != "") ? "<br/>" + this.t : "";
	    var c = "";
	    var src = $(this).attr('rel');
	    $("body").append("<div id='img_preview'><img src='" + src + "' />" + c + "</div>");
	    $("#img_preview img").css('margin-bottom', '-3px');

	    var wHeight = $(window).height();

	    var tmpYOff = 0;
	    if (e.pageY - xOffset + 400 > wHeight) {
		tmpYOff = e.pageY - xOffset + 400 - wHeight;
	    }

	    $("#img_preview")
		    .css("border", '4px solid #f04f21')
		    .css("padding", '0')
		    .css("margin", '0')
		    .css("background-color", '#f04f21')
		    .css("width", '500px')
		    .css("text-align", 'center')
		    .css("position", 'absolute')
		    .css("top", (e.pageY - xOffset - tmpYOff) + "px")
		    .css("left", (e.pageX + yOffset) + "px")
		    .fadeIn("fast");
	},
		function () {
		    this.title = this.t;
		    $("#img_preview").remove();
		});


	$("a.fname").mousemove(function (e) {

	    var wHeight = $(window).height();
	    var tmpYOff = 0;
	    if (e.pageY - xOffset + 400 > wHeight) {
		tmpYOff = e.pageY - xOffset + 400 - wHeight;
	    }

	    $("#img_preview")
		    .css("top", (e.pageY - xOffset) - tmpYOff + "px")
		    .css("left", (e.pageX + yOffset) + "px");
	});
    };











//	maximo descuento por pedido
    var tablaDescuentos = JSON.parse('<?= $descuentos ?>');
    function isValidDiscount(total, discount) {
	if ((discount == 0) || (total == 0)) {
	    return true;
	}
	
	// el descuento max está especificado en %, calcular que % significa el dto. en el total para ocmparar contra el máximo de la escala
	var percDto = discount * 100 / total;
	for (var i = 0; i < tablaDescuentos.dd.length; i++) {
	    if ((total >= tablaDescuentos.dd[i]) && (total <= tablaDescuentos.dh[i]) && (percDto <= tablaDescuentos.dm[i])) {
			return true;
	    }
	}
	return false;
    }
    //	maximo descuento por pedido

    var tablaDescuentosPresu = JSON.parse('<?= getSiteInfo('descuentosmaxpresu') ?>');
    function isValidPresuDiscount(total, discount) {
	if (discount == 0) {
	    return true;
	}
	for (var i = 0; i < tablaDescuentosPresu.dd.length; i++) {
	    if (total >= tablaDescuentosPresu.dd[i] && total <= tablaDescuentosPresu.dh[i] && discount <= tablaDescuentosPresu.dm[i]) {
		return true;
	    }
	}
	return false;
    }
    
    var tablaDescuentosFotolibro = JSON.parse('<?= getSiteInfo('photobook_escala_descuento') ?>');
    function isValidPBDiscount(total, discount) {
	if (discount == 0) {
	    return true;
	}
	for (var i = 0; i < tablaDescuentosFotolibro.dd.length; i++) {
	    if (total >= tablaDescuentosFotolibro.dd[i] && total <= tablaDescuentosFotolibro.dh[i] && discount <= tablaDescuentosFotolibro.dm[i]) {
		return true;
	    }
	}
	return false;
    }

    var maxventapedido = <?= $maxventapedido ?>;
    function isMaxExceeded(total) {
	if (maxventapedido == 0) {
	    return false;
	}
	return total > maxventapedido;
    }

    var _current_folder = "<?= $folder_raw ?>";


    $(document).ready(function () {
		imagePreview();
    });




    function closeViewMedia(event, ui) {
	$('#view-media').dialog("close");
	$('#view-media').html("");
    }

    function refreshCart() {
	//console.info("Refresh cart");
	$('#minicart').load('ajax/minicart');
    }

    function cropMedia(im) {
	var valid = false;
	$('.steeper-label').each(function (index) {
	    if ($(this).val() != 0) {
		valid = true;
		return;
	    }
	});

	if (valid) {
	    $('#order-frm').submit();
	} else {
	    showAlert("Error", "Debe seleccionar algun formato.")
	}

    }

    function showAlert(title, msg) {
	$('#msg').html(msg);
	$('#msg').dialog({
	    title: title,
	    modal: true,
	    buttons: {
		"Cerrar": function () {
		    $(this).dialog("close");
		}
	    }
	});
    }

    function openViewMedia(im) {
	if (CurrentTimeOut != 0) {
	    clearTimeout(CurrentTimeOut);
	    CurrentTimeOut = 0;
	}

	try {
	    $('#view-media').dialog('destroy');
	} catch (err) {	}

	$('#view-media').html("");
	$('#view-media').load('ajax/view_media?m=' + im).dialog({
	    title: "Encargar copia",
	    resizable: false,
	    modal: true,
	    close: function () {
			$('#view-media').html("");
	    },
	    buttons: {
		"Continuar": function () {
		    cropMedia(im);
		},
		"Cerrar": function () {
		    $(this).dialog("close");
		    $('#view-media').html("");
		}
	    }
	});
    }

<?
if ($process_order != '') {
    ?>
        function confirmFinalOrder() {

    	var pendientes = 0;
    	$('.pedido-thumb').each(function () {
    	    if (!$(this).hasClass('procesadook'))
    		pendientes++;
    	});

    	if (pendientes > 0)
    	    if (!confirm('Se han detectado ' + pendientes + ' fotos sin procesar, confirma finalizar el pedido?'))
    		return;

    	$('#view-media').dialog('close');
    	$('#view-media').html("<div style='text-align:center; padding-top:30px'><img src='static/img/loader.gif' width='24' height='24' /><br/><br/>Copiando archivos...</div>");
    	$('#view-media').dialog({
    	    title: "Confirmar pedido",
    	    width: 500,
    	    height: 230,
    	    position: ['center', 'center'],
    	    buttons: {
    		"Aceptar": function () {
    		    $(this).dialog('close');
    		}
    	    }
    	})
    	$('#view-media').load('ajax/confirm_final_order', {pid:<?= $process_order ?>});
        }
    <?
    echo "$('#view-media').load('ajax/view_process_order?pid=" . $process_order . "').dialog({
			title:\"Procesar pedido\",
			buttons: { 
				\"Confirmar Pedido\": function() { 
					confirmFinalOrder();
				},
				\"Cancelar\":function() { 
					$(this).dialog(\"close\"); 
					refreshCart();
					$('#view-media').html(\"\"); 
				}
			}
		});";
}
?>

    refreshCart();
</script>
<?
// MaxterHlp::d($_SESSION['maxter']['pedido']); ?>