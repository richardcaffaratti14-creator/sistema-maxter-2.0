<?php

class Log {

	const LOG_PATH = 'log.txt';

	public static $logActive = true;

	public static function init() {
		if (Log::$logActive)
			file_put_contents(Log::LOG_PATH, Date::getNow() . " | LOG START ---------------\n");
	}

	public static function l($txt) {
		if (Log::$logActive)
			file_put_contents(Log::LOG_PATH, Date::getNow() . " | " . $txt . "\n", FILE_APPEND);
	}

	public static function d($obj) {
		Log::l(print_r($obj, true));
	}

	public static function lp() {
		$numargs = func_num_args();
		$txt = "";
		for ($i = 0; $i < $numargs; $i++) {
			$txt .= Dump::printable(func_get_arg($i)) . " :: ";
		}

		Log::l($txt);
	}

}
