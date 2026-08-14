<?php

class Utils {

	public static function cutString($str, $amount = 1, $dir = "right") {
		if (($n = strlen($str)) > 0) {
			if ($dir == "right") {
				$start = 0;
				$end = $n - $amount;
			} elseif ($dir == "left") {
				$start = $amount;
				$end = $n;
			}

			return substr($str, $start, $end);
		} else
			return false;
	}

	public static function left($str, $amount) {
		return substr($str, 0, $amount);
	}

	public static function right($str, $amount) {
		return substr($str, strlen($str) - 5);
	}

	public static function clearEmptyAndNullKeys(&$my) {
		self::clearEmptyKeys($my);
		self::clearNullKeys($my);
	}

	public static function clearEmptyKeys(&$my) {
		foreach ($my as $key => $value) {
			if (is_null($value) || trim($value) == '') {
				unset($my[$key]);
			}
		}
	}

	public static function clearNullKeys(&$my) {
		foreach ($my as $key => $value) {
			if (is_null($value)) {
				unset($my[$key]);
			}
		}
	}

	public static function isInArray($value, $array) {
		if (is_array($array))
			if (in_array($value, $array))
				return true;
		return false;
	}

	public static function randomString($qty = 6) {
		$valid = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$rtn = '';
		for ($i = 0; $i < $qty; $i++) {
			$rtn .= $valid{mt_rand(0, strlen($valid) - 1)};
		}
		return $rtn;
	}

	public static function getValueGiven2Strings($str_return, $str_given, $val_given) {

		$arr_return = explode('|', $str_return);
		$arr_given = explode('|', $str_given);

		return Utils::getValueGiven2Arrays($arr_return, $arr_given, $val_given);
	}

	public static function getValueGiven2Arrays($arr_return, $arr_given, $val_given) {

		for ($i = 0; $i < count($arr_return); $i++) {
			if ($val_given == $arr_given[$i]) {
				$idx = $i;
			}
		}

		return $arr_return[$idx];
	}

	public static function padWithZeros($number, $zeroQty) {
		return sprintf("%0" . $zeroQty . "d", $number);
	}

	public static function getImageFromValues($images, $values, $labels, $path, $value) {
		$img_arr = explode('|', $images);
		$val_arr = explode('|', $values);
		$lab_arr = explode('|', $labels);

		for ($i = 0; $i < count($val_arr); $i++) {
			if ($value == $val_arr[$i]) {
				$idx = $i;
				break;
			}
		}

		//return '<a href="" title="' . $lab_arr[$idx] . '"><img src="' . $path . '' . $img_arr[$idx] . '" /></a>';
		return '<img title="' . $lab_arr[$idx] . '" src="' . $path . '' . $img_arr[$idx] . '" />';
	}

	public static function getValueBetweenRanges($valueSource, $rangeSource, $rangeDestination) {
		/* Dump::dlp($valueSource);
		Dump::d($rangeSource);
		Dump::d($rangeDestination); */
		
		$rwDiff = $rangeSource[1] - $rangeSource[0];
		$pxDiff = $rangeDestination[1] - $rangeDestination[0];
		$perRW = (($valueSource - $rangeSource[0]) * 100) / $rwDiff;
		$pxSize = (($perRW * $pxDiff) / 100) + $rangeDestination[0];
		return $pxSize;
	}

	public static function replaceHtml2SChars($string) {

		$search = array(
			'&aacute;', '&Aacute;',
			'&eacute;', '&Eacute;',
			'&iacute;', '&Iacute;',
			'&oacute;', '&Oacute;',
			'&uacute;', '&Uacute;',
			'&uuml;', '&Uuml;',
			'&ntilde;', '&Ntilde;',
			'&iquest;', '&iexcl;',
			'&ordm;', '&deg;', '&ordf;',
			'&#8220;', '&#8221;',
			'&#8230;'
		);
		$replace = array(
			'á', 'Á',
			'é', 'É',
			'í', 'Í',
			'ó', 'Ó',
			'ú', 'Ú',
			'ü', 'Ü',
			'ñ', 'Ñ',
			'¿', '¡',
			'º', 'º', 'ª',
			'"' . '"',
			'...'
		);

		return str_replace($search, $replace, $string);
	}

	public static function transformUrlSafe($val) {

		$val = str_replace('  ', ' ', $val);
		$val = str_replace('  ', ' ', $val);
		$val = str_replace('  ', ' ', $val);

		$search = array(" ", "á", "é", "í", "ó", "ú", "ü", "Á", "É", "Í", "Ó", "Ú", "Ü", "ñ", "Ñ", "ç", "Ç", '¿', '?', 'º', 'ª', '“', '”', '¡', '!', '/', '\\', '–', '.');
		$replac = array("-", "a", "e", "i", "o", "u", "u", "A", "E", "I", "O", "U", "U", "n", "N", "c", "C", '', '?', '', '', '', '', '', '', '', '', '-', '');
		$temp = str_replace($search, $replac, $val);  //	known chars
		$temp = preg_replace('%[^.^/^-\w\s]%', '', $temp);  //	unknown chars

		return strtolower($temp);
	}

	public static function is_utf8($W) {
		return(preg_match('~~u', $W) && !preg_match('~[\\0-\\x8\\xB\\xC\\xE-\\x1F]~', $W));
	}

	public static function createLinks($subject, $attrs = 'target="_blank"') {
		$result = preg_replace('/(<a.*href="((https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])".*>.+<\/a>)|((https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/i', '<a href="\4" ' . $attrs . '>\4</a>\1', $subject);
		$result = str_replace('<a href="" ' . $attrs . '></a>', '', $result);
		return $result;
	}

	public static function extractEmails($string) {
		preg_match_all("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $string, $matches);
		return $matches[0];
	}

	public static function strToInt($str) {
		return $str == '' ? "0" : $str;
	}

}

?>
