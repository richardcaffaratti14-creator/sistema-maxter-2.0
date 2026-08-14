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
		if ( $urlParts[0] == 'addo' )
		{
			$urlParts[0] = 'default';
			$urlParts[1] = 'default';
			$urlParts[2] = 'default';
			return true;
		}
		else
			$this->redirectTo404();
	}
	
	public function redirectTo404(){
		header('Location: 404');
	}
	
}
