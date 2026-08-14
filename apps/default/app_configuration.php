<?php
$DB = new SypDatabase(DB_USER, DB_PASS, DB_NAME, '127.0.0.1');

include 'site_configuration.php';

//	create default folders
File::mkdirs(PATH_IMAGES_ROOT);
File::mkdirs(PATH_IMAGES_ROOT.PATH_ORIGINALS);
File::mkdirs(PATH_IMAGES_ROOT.PATH_FRAMES);
File::mkdirs(PATH_IMAGES_ROOT.PATH_ORDERS);
File::mkdirs(PATH_THUMBS);

define('THUMB_SIZE', THUMB_MAX_W."x".THUMB_MAX_H);
define('VIEW_SIZE', VIEW_MAX_W."x".VIEW_MAX_H);
define('CROP_SIZE', IMGCROP_MAX_W."x".IMGCROP_MAX_H);
define('SITE_CODE'				, 'maxter' );
define('PATH_ORIGINALS_ROOT', PATH_IMAGES_ROOT.PATH_ORIGINALS );


