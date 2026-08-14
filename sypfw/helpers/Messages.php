<?php

class Messages {
	public static function showError($title, $msg = ''){
		Messages::showMessage(2, $title, $msg);
	}
	
	public static function showMessage($priority, $title, $msg = ''){
		$priorities[0] = 'info';
		$priorities[1] = 'warning';
		$priorities[2] = 'error';
		
		$rtn = '<div id="msg-'.$priorities[$priority].'">';
		$rtn .= '<span id="title">'.$title.'</span>';
		$rtn .= '<span id="msg">'.$msg.'</span>';
		$rtn .= '</div>';
		
		echo $rtn;
	}
}
