<?php

define( 'SYP_PATH'				, 'sypfw/' );
define( 'APPS_PATH'				, 'apps/' );
define( 'DEFAULT_APP_NAME'		, 'default' );
define( 'DEFAULT_MOD_NAME'		, 'default' );
define( 'DEFAULT_ACTION_NAME'	, 'default' );
define( 'DEFAULT_LAYOUT'		, 'default' );

//fix para cuando corre en el root de server windows
$tmpPath = dirname($_SERVER['PHP_SELF']);
if ($tmpPath == "\\")
	$tmpPath = "/";

define( 'SITE_ROOT', 'http://'.$_SERVER['SERVER_NAME'].$tmpPath.($tmpPath == '/' ? '' : '/'));

define( 'STATIC_PATH'			, 'static/' );
define( 'CSS_PATH'				, STATIC_PATH.'css/' );
define( 'JS_PATH'					, STATIC_PATH.'js/' );
define( 'IMG_PATH'				, STATIC_PATH.'img/' );
define( 'SWF_PATH'				, STATIC_PATH.'swf/' );

define( 'MODEL_PATH'				, SYP_PATH.'model/' );

define( 'DATE_FORMAT_SHORT'		, 'd-m-Y' );
define( 'DATE_TIME_FORMAT_SHORT'		, 'd-m-Y H:i:s' );
define( 'DATE_FORMAT_LONG'		, STATIC_PATH.'swf/' );

define( 'DB_USER', 'root');
define( 'DB_PASS', '');
define( 'DB_NAME', 'maxter');