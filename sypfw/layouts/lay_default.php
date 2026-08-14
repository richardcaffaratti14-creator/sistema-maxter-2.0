<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
	<base href="<?= SITE_ROOT ?>" />
	<!-- <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> -->
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
	<title><?= Page::getTitle(); ?></title>
	<?= Page::getJS(); ?>
	<?= Page::getCSS(); ?>
    </head>
    <body>
	<div id="wrapper">
	    <?
	    include App::getApplicationPath() . '_boxes/header.php';
	    echo '<div id="content">';
	    if (is_file(App::getActionPath()))
		include App::getActionPath();
	    else
		Dump::dlp('ACTION NOT DEFINED');
	    echo '</div>';
	    include App::getApplicationPath() . '_boxes/footer.php';
	    ?>
	</div>
    </body>
</html>