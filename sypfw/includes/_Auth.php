<?php

class Auth {
	
	public static function isLoggedIn(){
		return true;
	}
	
	public static function isAuthorized(){
		
		if ( !SITE_REQUIRE_AUTH )
			return true;
		
		//	check if authorization is needed	-------------------------------
		if (App::getActionName() == SITE_AUTH_IN_APP_PAGE ){
			//	if is the login page let him enter
			return true;
		} else
			//	do the real authorizatio process
			return true;
	}
	
}