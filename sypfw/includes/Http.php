<?php

class Http {

	public static function go($url) {
		$u = SITE_ROOT . $url;
		Log::l("GO: " . $u);
		header('Location: ' . $u);
	}
	
	public static function goAbs($url) {
		header('Location: ' . $url);
	}

	public static function goInApp($url) {
		Log::l("goInApp");
		Http::go(App::getApplicationName() . "/" . $url);
	}

	public static function goInMod($url) {
		Log::l("goInMod");
		Http::go(App::getApplicationName() . "/" . App::getModuleName() . '/' . $url);
	}

	public static function goRoot() {
		Log::l("goRoot");
		Http::go("");
	}

	public static function getUrlParams() {
		$params = $_GET['params'];
		$params = trim(preg_replace("[^a-zA-Z0-9_/+.!*',()~-]", "", $params));
		$params = explode('/', $params);
		Utils::clearEmptyKeys($params);
		return $params;
	}

	public static function getOverPost($key) {
		$rtn = $_GET[$key];
		$rtn = $rtn == '' ? $_POST[$key] : $rtn;
		return $rtn == '' ? $_FILES[$key] : $rtn;
	}

	public static function getPage($url) {
		$ch = curl_init();
		//curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
		//	CHROME
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.202 Safari/535.1");
		curl_setopt($ch, CURLOPT_URL, $url);
		$cookie = tempnam("tmp", "CURLCOOKIE");
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($ch);
		return $output;
		curl_close($ch);
	}

	public static function set404Header(){
		header("HTTP/1.0 404 Not Found");
	}
	
	public static function setPNGHeader(){
		header("Content-Type: image/png");
	}
}
