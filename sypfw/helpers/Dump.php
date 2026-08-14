<?php

class Dump {

	public static function d($o, $label = '', $tag = 'pre') {
		echo $label . '<' . $tag . ' style="font-size:12px; color:black; background-color:white; font-family:Arial">';
		print_r($o);
		echo '</' . $tag . '><hr />';
	}

	public static function dd($o, $label = '') {
		Dump::d($o, $label);
		die;
	}

	public static function dx($o, $label = '') {
		Dump::d($o, $label, 'xmp');
	}

	public static function dl($txt) {
		echo Dump::printable($txt) . "<br />\n";
	}

	public static function dlp() {
		$numargs = func_num_args();
		$txt = "";
		for ($i = 0; $i < $numargs; $i++) {
			if ($i == 0)
				$txt .= Dump::printable(func_get_arg($i));
			else
				$txt .= " :: " . Dump::printable(func_get_arg($i));
		}

		Dump::dl($txt);
	}

	public static function printable($txt, $return = true) {
		$rtn = '';
		if (is_null($txt))
			$rtn = "NULL";
		elseif ($txt === false)
			$rtn = "FALSE";
		elseif ($txt === true)
			$rtn = "TRUE";
		else
			$rtn = $txt;

		if ($return)
			return $rtn;
		else
			Dump::dl($rtn);
	}

}

?>