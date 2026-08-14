<?
//print_r($_GET);
//  deprecated
die;

$folder = Http::getOverPost('f');

//  get the first digital format
$df = new formato_imagen();
$df->orderBy('orden ASC');
$df->addCondition('ancho', '0');
$df->addCondition('alto', '0');
$df->limit(1);
$df->get();

if (!$df->isAvailable()) {
    echo '<h3>No hay formato digital disponible.</h3>';
    ?>
    <script>
        $('#view-media').dialog({
    	title: "No se agregaron im&aacute;genes.",
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
    die;
}

//print_r($df->id);
//print_r($df->nombre);


$folder = base64_decode($folder);
$folder = $folder == '' ? PATH_ORIGINALS_ROOT : $folder . '/';
//print_r($folder);
//echo '<br />';
//echo PATH_ORIGINALS_ROOT;
//
$files = File::getPaginatedFilesForm($folder, $current_page, 9999999, '');
$fjson = array();
for ($i = 0; $i < count($files->files); $i++) {
    $ext = strtolower(File::getExtension($files->files[$i]));
    if ($ext != 'mp4') {
	$fjson[] = str_replace(PATH_ORIGINALS_ROOT, '', $files->files[$i]);
    }
}

$imgqty = count($fjson);
$cn = Presu::getCartNumbers();
$pid = Presu::getID();

$presu = new presupuestos();
$presu->get($pid);

if ($imgqty + $cn['img'] > $presu->images_qty) {
    echo '<h3>Se exeder&aacute; la cantidad de im&aacute;genes permitidas en el presupuesto.</h3>';
    ?>
    <script>
        $('#view-media').dialog({
    	title: "No se agregaron im&aacute;genes.",
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
    die;
}

//print_r($files);
?>

<div id="add_folder_content">

</div>
<script>

    var current = 0;
    var imgs = $.parseJSON('<?= json_encode($fjson) ?>');
    //	console.info(imgs);

    function addNextImage() {
	if (current >= imgs.length) {
	    $('#add_folder_content').html('<br />Todas las im&aacute;genes agregadas.');
	    refreshCart();


	    $('#view-media').dialog({
		title: "No se agregaron im&aacute;genes.",
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


	    return;
	}

	//$('#add_folder_content').append(current + "/" + imgs.length);
	$('#add_folder_content').html("Agregando im&aacute;gen " + (current + 1) + "/" + imgs.length);
	//
	var url = 'ajax/add_image';
	//url = 'ajax/add_folder_each_image';
	var data = {};
	data.m = imgs[current];
	data.sel_format = <?= $df->id ?>;
	data.formats = [<?= $df->id ?>];
	data.f<?= $df->id ?> = 1;
	//
	$.post(url, data, function (data) {
	    //$('#add_folder_content').append(data);
	    current++;
	    addNextImage();
	});
    }

    addNextImage();


    //


</script>