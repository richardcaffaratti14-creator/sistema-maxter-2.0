<?
$folder = Http::getOverPost('f');
$current_page = Http::getOverPost('page');

$folder_raw = $folder;
$folder = base64_decode($folder);
$folder = $folder == '' ? PATH_ORIGINALS_ROOT : $folder . '/';
session_write_close();
$current_page = $current_page == '' ? 0 : $current_page;

$q = Http::getOverPost('q');

$files_obj = File::getPaginatedFilesForm($folder, $current_page, MAX_FILES_PER_PAGE, $q);
?>
<div id="gallery-inner">
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
				<!--<img src="<?= str_replace("images/fotos", "/maxterfotos", Img::crop('images/' . PATH_ORIGINALS . $im, THUMB_SIZE)) ?>" />-->
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
			<li><a href="javascript:_load_pictures('<?= $folder_raw ?>',<?= ($current_page - 1) ?>, '<?= $q ?>')">&larr;</a></li>
		<? } ?>

		<?
		for ($i = 0; $i < $files_obj->pages_qty; $i++) {
		if ($current_page == $i) {
			?>
			<li class="current"><?= ($i + 1) ?></li>
		<? } else { ?>
			<li><a href="javascript:_load_pictures('<?= $folder_raw ?>',<?= ($i) ?>, '<?= $q ?>')"><?= ($i + 1) ?></a></li>
			<?
		}
		}
		?>

		<? if ($current_page == $files_obj->pages_qty - 1) { ?>

		<? } else { ?>
			<li><a href="javascript:_load_pictures('<?= $folder_raw ?>',<?= ($current_page + 1) ?>, '<?= $q ?>')">&rarr;</a></li>
		<?
		}
	}
	?>

	</ul>
<? } ?>
