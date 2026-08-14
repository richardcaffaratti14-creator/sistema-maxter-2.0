<?php
$DB = new SypDatabase(DB_USER, DB_PASS, DB_NAME, '127.0.0.1');

include 'site_configuration.php';

define( 'SITE_CODE'				, 'images_app' );
//define( 'SITE_DEFAULT_TITLE'	, '..:: IMAGES ::..' );

//	Security	-------------------------------------
define( 'SITE_REQUIRE_AUTH'		, false );
define( 'SITE_AUTH_IN_APP_PAGE'	, 'login' );