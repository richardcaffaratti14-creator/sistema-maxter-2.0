<?php

class SessionManager {

	public static function setValue($name, $value = '', $site_code = SITE_CODE) {
		$name_array = explode('/', $name);
		$ses = &$_SESSION[$site_code];
		foreach ($name_array as $key) {
			if (!isset($ses[$key]))
				$ses[$key] = '';
			$ses = &$ses[$key];
		}
		$ses = $value;
	}

	public static function pushValue($name, $value, $allowDuplicate = true, $site_code = SITE_CODE) {
		$name_array = explode('/', $name);
		$ses = &$_SESSION[$site_code];
		if ($allowDuplicate) {
			foreach ($name_array as $key) {
				if (!isset($ses[$key]))
					$ses[$key] = '';
				$ses = &$ses[$key];
			}

			$ses[] = $value;
		} else {
			foreach ($name_array as $key) {
				if (!isset($ses[$key]))
					$ses[$key] = '';
				$ses = &$ses[$key];
			}
			if (!Utils::isInArray($value, $ses))
				$ses[] = $value;
		}
	}

	public static function getValue($name) {
		$name_array = explode('/', $name);
		$ses = &$_SESSION[SITE_CODE];
		foreach ($name_array as $key) {
			if (!isset($ses[$key]))
				$ses[$key] = '';
			$ses = &$ses[$key];
		}

		return $ses;
	}

	public static function getValueFromSiteCode($name, $site_code) {
		$name_array = explode('/', $name);
		$ses = &$_SESSION[$site_code];
		foreach ($name_array as $key) {
			if (!isset($ses[$key]))
				$ses[$key] = '';
			$ses = &$ses[$key];
		}

		return $ses;
	}

	public static function getSiteSession() {
		return $_SESSION[SITE_CODE];
	}

	public static function unsetValue($name, $site_code = SITE_CODE) {
		$name_array = explode('/', $name);
		$ses = &$_SESSION[$site_code];
		for ($i = 0; $i < count($name_array) - 1 ; $i++){
			$key = $name_array[$i];
			if (!isset($ses[$key]))
				$ses[$key] = '';
			$ses = &$ses[$key];
		}
		unset( $ses[$name_array[count($name_array)-1]] );
	}

	public static function destroy() {
		unset($_SESSION[SITE_CODE]);
	}

}
