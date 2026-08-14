<?php

class Validate {

	public static function isEmail($email) {
		//email is not case sensitive make it lower case
		$email = strtolower($email);

		//check if email seems valid
		if (preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $email)) {
			return true;
		}
		return false;
	}

}
