<?php
//	DATE AND TIME
define('DATE_TIME_FULL_EN', 'l jS \of F Y h:i:s A');
define('DATE_TIME_FULL_ES', 'DATE_TIME_FULL_ES');

define('DATE_TIME_LONG_EN', 'F j, Y, g:i a');
define('DATE_TIME_LONG_ES', 'DATE_TIME_LONG_ES');

define('DATE_TIME_SHORT_EN', 'M j Y g:i a');
define('DATE_TIME_SHORT_ES', 'DATE_TIME_SHORT_ES');

define('DATE_TIME_MIN_EN', 'm-d-y g:i');
define('DATE_TIME_MIN_ES', 'd-m-y h:i');

//	ONLY DATE
define('DATE_FULL_EN', 'l jS \of F Y');
define('DATE_FULL_ES', 'DATE_FULL_ES');

define('DATE_LONG_EN', 'F j, Y');
define('DATE_LONG_ES', 'DATE_LONG_ES');

define('DATE_SHORT_EN', 'M j Y');
define('DATE_SHORT_ES', 'DATE_SHORT_ES');

define('DATE_MIN_EN', 'm-d-y');
define('DATE_MIN_ES', 'd-m-y');

//	ONLY HOUR
define('TIME_FULL_EN', 'g:i:s a');
define('TIME_FULL_ES', 'H:m:s');

define('TIME_EN', 'g:i a');
define('TIME_ES', 'H:m');

//	DATEPICKER
define('DATE_PICKER_EN', 'm-d-Y g:i');
define('DATE_PICKER_ES', 'd-m-Y h:i');
class Date {

	public static function getNow($lang = 'es') {
		if ($lang == 'es')
			return Date::getDateTime (date('Y-m-d H:i', time()) , DATE_TIME_MIN_ES);
		else
			return Date::getDateTime (date('Y-m-d H:i', time()), DATE_TIME_MIN_EN);			
	}
	
	public static function getNowDateTime($lang = 'es') {
		if ($lang == 'es')
			return Date::getDateTime (date('Y-m-d H:i', time()) , DATE_TIME_SHORT_ES);
		else
			return Date::getDateTime (date('Y-m-d H:i', time()), DATE_TIME_SHORT_EN);			
	}
	
	public static function getNowTime() {
		return date('H:i:s', time());
	}

	public static function getDateTime($date_time, $format = DATE_TIME_MIN_EN ){
		
		if ((int)$date_time == '0' )
			return "";
		
		$dias = array('','Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo');
		$meses = array('','Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre');
		$meses_cortos = array('', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Dic');
		
		$time = strtotime($date_time);
		
		$day_name_number = date('N', $time);
		$day_name = $dias[$day_name_number];
		$day_numumber = date('d', $time);
		
		$month_number = date('m', $time);
		//echo $month_number;
		$mes = $meses[(int)$month_number];
		$mes_corto = $meses_cortos[(int)$month_number];
		
		$ano = date('Y', $time);
		
		//$hora = date('H:i:s', $time);
		$hora = date('H:i', $time);
		
		if (
			$format == DATE_TIME_MIN_EN || 
			$format == DATE_TIME_SHORT_EN || 
			$format == DATE_TIME_LONG_EN ||
			$format == DATE_TIME_FULL_EN ||
			$format == DATE_FULL_EN ||
			$format == DATE_LONG_EN ||
			$format == DATE_SHORT_EN ||
			$format == DATE_MIN_EN ||
			$format == TIME_FULL_EN ||
			$format == TIME_EN ||
			$format == DATE_PICKER_EN ||
			$format == DATE_TIME_MIN_ES ||
			$format == DATE_MIN_ES ||
			$format == TIME_ES ||
			$format == TIME_FULL_ES ||
			$format == DATE_PICKER_ES
			){
				return date($format, strtotime($date_time));
		} else if ($format == DATE_TIME_FULL_ES){
			//	Domingo 22 de Octubre de 2011 a las 19:09:40
			return "$day_name $day_numumber de $mes de $ano a las $hora";
			
		} else if ($format == DATE_TIME_LONG_ES){
			//	22 de Octubre de 2011 a las 19:09:40
			return "$day_numumber de $mes, $ano a las $hora";
			
		} else if ($format == DATE_TIME_SHORT_ES){
			//	23 Oct 2011, 19:09
			return "$day_numumber $mes_corto $ano $hora";
			
		} else if ($format == DATE_FULL_ES){
			//	Sábado 22 de Octubre de 2011
			return "$day_name $day_numumber de $mes de $ano";
			
		} else if ($format == DATE_LONG_ES){
			//	Sábado 22 de Octubre de 2011
			return "$day_numumber de $mes de $ano";
			
		} else if ($format == DATE_SHORT_ES){
			//	Sábado 22 de Octubre de 2011
			return "$day_numumber $mes_corto $ano";
			
		} else {
			return 'FORMATO NO RECONOCIDO';
		}
		
	}

	public static function convertToDB($date_orig, $format = 'es'){
		//2015-12-01 22:09
		if ($format == 'es'){
			//01-12-2015 22:09
			$datetime_parts = explode(' ', $date_orig);
			$date_parts = explode('-', $datetime_parts[0]);
			return $date_parts[2].'-'.$date_parts[1].'-'.$date_parts[0].' '.$datetime_parts[1];
		} else {
			//	12-01-2015 11:09
			$datetime_parts = explode(' ', $date_orig);
			$date_parts = explode('-', $datetime_parts[0]);
			return $date_parts[2].'-'.$date_parts[0].'-'.$date_parts[1].' '.$datetime_parts[1];
		}
	}
}
