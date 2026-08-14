<?php

include_once SYP_PATH.'includes/App.php';

function __autoload($class)
{	
	if ( file_exists(App::getApplicationPath().'_includes/'.$class.'.php') )
		require_once(App::getApplicationPath().'_includes/'.$class.'.php');
	else if ( file_exists(App::getApplicationPath().'_includes/classes/'.$class.'.php') )
		require_once(App::getApplicationPath().'_includes/classes/'.$class.'.php');
	else if ( file_exists(SYP_PATH.'includes/'.$class.'.php') )
		require_once(SYP_PATH.'includes/'.$class.'.php');
	else if ( file_exists(SYP_PATH.'helpers/'.$class.'.php') )
		require_once(SYP_PATH.'helpers/'.$class.'.php');
	else if ( file_exists(SYP_PATH.'helpers/'.$class.'/'.$class.'.php') )
		require_once(SYP_PATH.'helpers/'.$class.'/'.$class.'.php');
	else if ( file_exists(SYP_PATH.'model/'.$class.'.php') )
		require_once(SYP_PATH.'model/'.$class.'.php');
	else
	{
		$includes = App::getIncludePaths();
		
		foreach($includes as $inc){
			$inc_path = $inc.$class.'.php';
			if (is_file($inc_path)){
				require_once($inc_path);
				return true;
			}
		}
		die('CLASS NOT FOUND: '.$class);
	}

	return true;
}
