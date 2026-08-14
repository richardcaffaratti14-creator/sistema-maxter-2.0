<?php

class ActionHelper {

	public static function thLink($name) {
		global $order, $dir, $q, $pid;

		if ($order == $name) {
			if ($dir == 'DESC') {
				$class = ' class="order_down"';
				$d = 'd';
			} else {
				$class = ' class="order_up"';
				$d = 'u';
			}
		}

		return '<a' . $class . ' href="' . App::getActionUrl() . '?order=' . $name . '&d=' . $d . '&q=' . $q . '&pid='.$pid.'">' . $name . '</a>';
	}

}