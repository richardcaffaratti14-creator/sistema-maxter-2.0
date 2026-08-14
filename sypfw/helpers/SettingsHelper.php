<?php

/* 
 * selec options
 * value|label
 * value2|label2
 */

class SettingsHelper {

	public static function getLabelFromSelectOptions($options, $value) {
		$ops = explode("\n", $options);
		foreach ($ops as $op) {
			$tmp = explode('|', $op);
			if ($tmp[0] == $value)
				return $tmp[1];
		}

		return '';
	}

	public static function getSelectFormOptions($options, $value = '', $select_name = 'value', $attrs = ''){
		
		$rtn = '<select '.$attrs.' name="'.$select_name.'">';
		$ops = explode("\n", $options);
		foreach ($ops as $op) {
			$tmp = explode('|', $op);
			$rtn .= '<option value="'.$tmp[0].'"';
			if ($tmp[0] == $value)
				$rtn .= ' selected="selected"';
			$rtn .= '>'.$tmp[1].'</option>';
		}

		return $rtn;	
	}
	
	public static function getTextfield($value = '', $text_name = 'value', $attrs = ''){
		return '<input type="text" name="'.$text_name.'" '.$attrs.' value="'.$value.'" />';
	}
	
	public static function getTextArea($value = '', $text_name = 'value', $attrs = ''){
		return '<textarea name="'.$text_name.'" '.$attrs.'>'.$value.'</textarea>';
	}

	public static function getRender(settings $set, $input_name = 'value', $attrs = ''){
		
		if ($set->type == 'select'){
			return SettingsHelper::getSelectFormOptions($set->options, $set->value, $input_name, $attrs);
		} elseif (($set->type == 'text')) {
			return self::getTextfield($set->value, $input_name, $attrs);
		} elseif (($set->type == 'textarea')) {
			return self::getTextArea($set->value, $input_name, $attrs);
		} else {
			return 'NOT DEFINED!';
		}
	}
	
	public static function generateFile($out = ''){
		if ($out == '') {
			$out = App::getModulePath().'settings_const.php';
		}
		
		$sets = new settings();
		$sets->orderBy('key');
		$sets = $sets->select();
		$cont = "<?\n";
		foreach($sets as $set){
			$cont .= "define('".$set->key."' , '".$set->value."');\n";
		}
		
		file_put_contents($out, $cont);
	}
}