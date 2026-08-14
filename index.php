<?php
error_reporting(0);

//runs DB update
include 'configuration.php';
include './sypfw/helpers/Updater.php';
Updater::runUpdate();

header('Content-Type: text/html; charset=utf-8');
include 'sypfw/includes/overseer.php';


Log::lp( basename(__FILE__), "App", App::getApplicationName(),"",
		"Mod", App::getModuleName(),"",
		"Act", App::getActionName(),""
	);

Log::lp( basename(__FILE__), "App", App::getApplicationPath(),"",
		"Mod", App::getModulePath(),"",
		"Act", App::getActionPath(),""
	);