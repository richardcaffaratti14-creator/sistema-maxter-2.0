<?php

class TrapResolver {
	
	/**
	 * This class will handle the 404 requests if the method returrn true
	 * the $APP will try to resolve the new app/mod/action if it cannot resolve
	 * will execute redirectTo404.
	 * Ex.
	 * if ( $urlParts[0] == 'appNotExistant' ){
			$urlParts[0] = 'default';
			$urlParts[1] = 'default';
			$urlParts[2] = 'default';
			return true;
		}
		else
			TrapResolver::redirectTo404 ();
	 * 
	 * in the sample we check if the first parameter is "appNotExistant"
	 * the site will show the default/default/default app/mod/action if not
	 * will redirect to 404
	 * 
	 * @param type $url 
	 */
	
	public function resolveTrap( &$urlParts ){
		Log::l( App::getApplicationName() . ' TRAP ');
		
		$tmp = "";
		for($i = 1 ; $i < count($urlParts) ; $i++){
			if ($i == 1)
				$tmp .= $urlParts[$i];
			else
				$tmp .= '/'.$urlParts[$i];
		}
		
		
		if ($urlParts[1] == 'enlarge') {
			$urlParts[2] = 'enlarge';
			$tmp = str_replace('enlarge/', '', $tmp);
		} elseif ($urlParts[1] == 'resize') {
			$urlParts[2] = 'resize';
			$tmp = str_replace('resize/', '', $tmp);
		} elseif ($urlParts[1] == 'firstpic') {
			$urlParts[2] = 'firstpic';
			$tmp = str_replace('firstpic/', '', $tmp);
		} else
			$urlParts[2] = 'default';
		
		$urlParts[0] = 'images';
		$urlParts[1] = 'default';
		$urlParts[3] = $tmp;
		
		return true;
	}
	
	public function redirectTo404(){
		Log::l('Default Trap Resolver: go 404');
		//Http::go('./');
	}
	
}
