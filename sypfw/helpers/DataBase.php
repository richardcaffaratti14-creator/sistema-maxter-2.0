<?php

class DataBase {

	public static function recordExists($value, $table, $field, $valueExlcude = "", $fieldExclude = "") {
		//$conn = Database::Connect();
		global $DB;
		if ($valueExlcude != '' && $fieldExclude != '')
			$sql = "SELECT COUNT(*) AS QTY FROM $table WHERE `$field` = '$value' AND $fieldExclude <> '$valueExlcude'; ";
		else
			$sql = "SELECT COUNT(*) AS QTY FROM $table WHERE `$field` = '$value'; ";
		$cursor = $DB->query($sql);
		$row = $DB->read($cursor);
		if ($row['QTY'] > 0)
			return true;
		else
			return false;
	}

	public static function getNextAvailableValue($value, $table, $field, $valueExlcude, $fieldExclude, $maxchars = 255) {

		$c = 0;
		$newValue = substr($value, 0, $maxchars);

		while (DataBase::recordExists($newValue, $table, $field, $valueExlcude, $fieldExclude)) {
			$c++;
			$newValue = substr($value, 0, $maxchars - 2) . "-$c";
			//break;
		}
		/* */
		//Dump::dlp($c, $newValue);
		return $newValue;
	}

}
