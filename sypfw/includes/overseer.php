<?php
session_start();
include 'configuration.php';
include SYP_PATH . 'includes/functions.php';
Log::$logActive = FALSE;
//Log::init();

//Dump::d(mysql_client_encoding());
//mysql_set_charset ('utf8');
//Dump::d(mysql_client_encoding());

Log::l("------------- PAGE VIEW INIT ----------------");

/*
  $urlParams = Http::getUrlParams();
  $subdirs = str_replace( join('/', $urlParams), '', $_SERVER['REQUEST_URI'] );
  $subdirs = str_replace('//', '/', $subdirs );
  if (strrpos($subdirs, '?') > -1)
  $subdirs = substr($subdirs, 0, strrpos($subdirs, '?'));
  define( 'SITE_ROOT', 'http://'.$_SERVER['SERVER_NAME'].$subdirs);
 */

Log::l("Site root: " . SITE_ROOT);
Log::l("Static path: " . STATIC_PATH);


App::INIT();

//	APP CONFIGURATION
if (is_file(App::getApplicationConfigurationPath()))
	include App::getApplicationConfigurationPath();

Log::l("Site Code: " . SITE_CODE );

if (is_file(App::getApplicationPath().'_includes/Auth.php') || is_file(App::getApplicationPath().'_includes/classes/Auth.php'))
	if (!Auth::isAuthorized())
		if (App::getActionName() != SITE_AUTH_IN_APP_PAGE && App::getModuleName() != SITE_AUTH_IN_APP_PAGE)
			Http::goInApp(SITE_AUTH_IN_APP_PAGE);


//	APP CONTROLLER
if (is_file(App::getApplicationControllerPath()))
	include App::getApplicationControllerPath();

//	MOD CONTROLLER
if (is_file(App::getModuleControllerPath()))
	include App::getModuleControllerPath();

//	ACTION CONTROLLER
if (is_file(App::getActionControllerPath()))
	include App::getActionControllerPath();

//session_start();
//	LAYOUT
if (is_file(App::getLayoutPath()))
	include App::getLayoutPath();
