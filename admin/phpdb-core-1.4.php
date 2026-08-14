<?
/*
 * Changelog:
 *
 * 1.3
 * 14-12-09: New isValidEMail() function
 * ------------------------------------------
 * 1.4
 * 21-12-09: New getSelectGroupBy() function
 */

include_once("sanitize.php");

define("DB_GETTABLEVALUE_NORECORDS", "ERROR");

define("PHPDBDEBUGSQL" , 0);
define("PHPDBNOQUOTE" , "Noquote-setup");
define("PHPDBIN" , "In-setup");
define("PHPDBORDERDESC" , "Desc-setup");
define("PHPDBLIKE" , "Like-setup");

function db_exec_select($fields , $tables , $where="" , $order="" , $limit="" , $literalwhere = "") {

	$wheresql = "";
	$ordersql = "";

	if (is_array($where)) {
		foreach ($where as $k=>$v) {
			if (($k!==PHPDBLIKE) && ($k!==PHPDBIN) && ($k!==PHPDBNOQUOTE))
				if (is_array($where[PHPDBLIKE]) || is_array($where[PHPDBIN])) {
					if (isset($where[PHPDBLIKE][$k]))
						$wherefieldlist[] = addslashes($k) . " LIKE '%" . addslashes($v) . "%'";
					elseif (isset($where[PHPDBIN][$k])) {
						if (is_array($v)) {
							foreach ($v as $tmpkey=>$tmp)
								$v[$tmpkey] = "'".addslashes($tmp)."'";
								
							$wherefieldlist[] = addslashes($k) . " IN (" . implode("," , $v) . ")";
						}
						else 
							$wherefieldlist[] = addslashes($k) . " IN (" . addslashes($v) . ")";
					}
					else
						$wherefieldlist[] = addslashes($k) . "='" . addslashes($v) . "'";
				}
				else {
                    if (isset($where[PHPDBNOQUOTE][$k]))
    					$wherefieldlist[] = addslashes($k) . "=" . addslashes($v);
                    else
    					$wherefieldlist[] = addslashes($k) . "='" . addslashes($v) . "'";
                }
		}

		if (is_array($wherefieldlist)) {
			$wherefieldlist = implode(" AND " , $wherefieldlist);
			$wheresql = "WHERE " . $wherefieldlist;
		}
	}
	if (!empty ($literalwhere)) {
		if (empty ($wheresql))
			$wheresql = "WHERE ";
		$wheresql .= " ".$literalwhere;
	}

		
	if (is_array($order)) {
		foreach ($order as $k=>$v) {
			if ($k !== PHPDBORDERDESC) {
				if (is_array($order[PHPDBORDERDESC])) {
					if (isset($order[PHPDBORDERDESC][$v]))
						$orderfieldlist[] = addslashes($v) . " DESC";
					else
						$orderfieldlist[] = addslashes($v);
				}
				else {
					$orderfieldlist[] = addslashes($v);
				}
			}
		}
	
		if (is_array($orderfieldlist)) {
			$orderfieldlist = implode("," , $orderfieldlist);
			$ordersql = "ORDER BY " . $orderfieldlist;
		}
	}
	elseif (!empty($order)) die("Invalid ORDER parameter type");
		
	
	$query = "SELECT $fields FROM $tables $wheresql $ordersql $limit";

	if (PHPDBDEBUGSQL) {
		echo "<xmp>";
		echo $query;
		//die();
		echo "</xmp>";
	}
	
	return mysql_query($query);
}

function db_exec_insert($table , $dataarray) {
	
	foreach ($dataarray as $k=>$v) {
		if ($k!==PHPDBNOQUOTE) {
			$fieldlist[] = addslashes($k);
			if (isset($dataarray[PHPDBNOQUOTE][$k]))
				$valuelist[] = addslashes($v);
			else
				if ($v=="NULL")
					$valuelist[] = addslashes($v);
				else
					$valuelist[] = "'" . addslashes($v) . "'";
		}
	}
	
	$fieldlist = implode("," , $fieldlist);
	$valuelist = implode("," , $valuelist);
	
	$query ="INSERT INTO $table($fieldlist) VALUES($valuelist)";

	if (PHPDBDEBUGSQL) {
		echo "<xmp>";
		echo $query . "\n";
		//die();
		echo "</xmp>";
	}
	
	if (mysql_query($query))
		return getLastInsertedID();
	else
		return false;
}

function db_exec_update($table , $dataarray , $idarray) {
	
	foreach ($dataarray as $k=>$v) {
		if ($k!==PHPDBNOQUOTE) {
			if (isset($dataarray[PHPDBNOQUOTE][$k]))
				$fieldlist[] = addslashes($k) . " = " . addslashes($v);
			else
				if ($v=="NULL")
					$fieldlist[] = addslashes($k) . " = " . addslashes($v);
				else
					$fieldlist[] = addslashes($k) . " = '" . addslashes($v) . "'";
		}
	}
	
	foreach ($idarray as $k=>$v) {
		$idlist[] = addslashes($k) . " = '" . addslashes($v) . "'";
	}
	
	$fieldlist = implode(" , " , $fieldlist);
	$idlist = implode(" AND " , $idlist);
	
	$query ="UPDATE $table SET $fieldlist WHERE $idlist";
	
	if (PHPDBDEBUGSQL) {
		echo "<xmp>";
		echo $query;
		//die();
		echo "</xmp>";
	}
	
	return mysql_query($query);
}

function db_exec_delete($table , $idarray) {
	
	foreach ($idarray as $k=>$v) {
		$idlist[] = addslashes($k) . " = '" . addslashes($v) . "'";
	}
	
	$idlist = implode(" AND " , $idlist);
	
	$query ="DELETE FROM $table WHERE $idlist";
	
	if (PHPDBDEBUGSQL) {
		echo "<xmp>";
		echo $query;
		//die();
		echo "</xmp>";
	}
	
	return mysql_query($query);
}

function db_exec_query_ret_first_field($query) {
	$rs = mysql_query($query);
	if ($r=mysql_fetch_array($rs))
		return $r[0];
	else 
		return DB_GETTABLEVALUE_NORECORDS;
}





function getLastInsertedID() {
	$query = "select LAST_INSERT_ID() as lastid";
	$rs = mysql_query($query);	

	if ($r = mysql_fetch_array($rs))
		return $r['lastid'];
	else 
		return -1;
}







function gpig($name , $min=0,$max=0 , $zero_allowed_on_range = false) {
	return getParameterInt($_GET, $name , $min,$max, $zero_allowed_on_range);
}
function gpip($name , $min=0,$max=0 , $zero_allowed_on_range = false) {
	return getParameterInt($_POST, $name , $min,$max, $zero_allowed_on_range);
}


function getParameterInt($from, $name , $min=0,$max=0 , $zero_allowed_on_range = false) {
	
	if (!is_array($from)) return $min;
	if (!is_numeric($from[$name])) return $min;
	
	//If zero is allowed and the value is 0, return 0 and do not check range
	if ($zero_allowed_on_range && ($from[$name]==0)) return 0;
	
	//Range check
	if (!empty($min) && ($from[$name]<$min)) return $min;
	if (!empty($max) && ($from[$name]>$max)) return $max;
	
	return $from[$name];
	
}

function getParameterIntDef($from, $name , $min=0,$max=0 , $default=0 , $zero_allowed_on_range = false) {

	if (!is_array($from)) return $default;
	if (!is_numeric($from[$name])) return $default;

	//If zero is allowed and the value is 0, return 0 and do not check range
	if ($zero_allowed_on_range && ($from[$name]==0)) return 0;
	if (!$zero_allowed_on_range && ($from[$name]==0)) return $default;

	//Range check
	if (!empty($min) && ($from[$name]<$min)) return $min;
	if (!empty($max) && ($from[$name]>$max)) return $max;

	return $from[$name];

}

function gpbg($name) {
	return getParameterBool($_GET,$name);
}
function gpbp($name) {
	return getParameterBool($_POST,$name);
}
function getParameterBool($from, $name) {
	
	if (!is_array($from)) return 0;
	if (!is_numeric($from[$name])) return 0;
	else return ($from[$name]=="1"?1:0);
	
}

function getParameterArrayOfInts($from, $name) {
	
	if (!is_array($from[$name])) return false;
	$from = $from[$name];
	foreach ($from as $k=>$v)
		$from[$k] = getParameterInt($from,$k);
	
	return $from;
}

function getParameterArrayOfStringsSanitized($from, $name , $StringsMaxLength=0) {
	
	if (!is_array($from[$name])) return false;
	$from = $from[$name];
	foreach ($from as $k=>$v)
		$from[$k] = getParameterStringSanitized($from,$k,$StringsMaxLength);
	
	return $from;
}


function gpsg($name , $maxlen = 0) {
	return getParameterString($_GET, $name , $maxlen);
}
function gpsp($name , $maxlen = 0) {
	return getParameterString($_POST, $name , $maxlen);
}
function getParameterString($from, $name , $maxlen = 0) {
	
	if (!is_array($from)) return "";
	else {
        $ret = sanitize($from[$name],UTF8);
        $ret = stripslashes($ret);
		if ($maxlen>0) {
			if (strlen($ret)>$maxlen)
				$ret = substr($ret,0,$maxlen-1);
				
			return $ret;
		}
		else
			return sanitize($ret,UTF8);
	}
	
}


function gpssg($name , $maxlen = 0) {
	return getParameterStringSanitized($_GET, $name , $maxlen);
}
function gpssp($name , $maxlen = 0) {
	return getParameterStringSanitized($_POST, $name , $maxlen);
}
function getParameterStringSanitized($from, $name , $maxlen = 0) {
	
	if (!is_array($from)) return "";
	else {
        $ret = sanitize($from[$name],UTF8);
        $ret = htmlentities($ret);
        $ret = stripslashes($ret);
		if ($maxlen>0) {
			if (strlen($ret)>$maxlen)
				$ret = substr($ret,0,$maxlen);
				
			return $ret;
		}
		else
			return sanitize($ret,UTF8);
	}
	
}

function getParameterDate($from, $name) {
	$d = getParameterInt($from, $name."_d",1,31);
	$m = getParameterInt($from, $name."_m",1,12);
	$y = getParameterInt($from, $name."_y",0,9999);
	if (!empty($d) && !empty($m) && !empty($y))
		return $y . "-" . $m . "-" . $d;
	else
		return 0;
}

 
 
 
 
 
 
 
 
 
function sendRedirect($url) {
	header("Location: " . $url);
	exit();
}


function isValidEmail($email) {
	/*
	$apos=strpos($email,"@");
	$dotpos=strpos($email,"." , $apos);
	$space=strpos($email," ");
	if ($apos<1 || $dotpos===false || $space!==false)
		return false;
	else
		return true;
	 *
	 */
	// First, we check that there's one @ symbol,
	// and that the lengths are right.
	if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
		// Email invalid because wrong number of characters
		// in one section or wrong number of @ symbols.
		return false;
	}

	// Split it into sections to make life easier
	$email_array = explode("@", $email);
	$local_array = explode(".", $email_array[0]);
	for ($i = 0; $i < sizeof($local_array); $i++) {
		if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
		  return false;
		}
	}

	return true;
}

  
function isValidDate($date , $minYear = 1900 , $maxYear = 2050) {
	//Validates dates in m/d/y format
	$parts = split("/" , $date);
	if (count($parts)!=3) return false;
	
	if (!is_numeric($parts[0]) || !is_numeric($parts[1]) || !is_numeric($parts[2])) return false;

	if ($parts[2]<100) $parts[2] = $parts[2] + 2000;
	
	if (($parts[2] <= $minYear) || ($parts[2] >= $maxYear)) return false;
	
	$maxDays=array();
	$maxDays[1]=$maxDays[3]=$maxDays[5]=$maxDays[7]=$maxDays[8]=$maxDays[10]=$maxDays[12]=31;
	$maxDays[4]=$maxDays[6]=$maxDays[9]=$maxDays[11]=30;
	if (isLeapYear($parts[2]))
		$maxDays[2]=29;
	else
		$maxDays[2]=28;
	

	
	if (($parts[0] < 1) || ($parts[0] > 12)) return false;
	
	$parts[0] = (int)$parts[0];
	
	if (($parts[1] < 1) || ($parts[1] > $maxDays[$parts[0]])) return false;
	
	return true;
}

function formatMySQLDate ($date) {
	//Formats dates in m/d/y to use on mySQL queries , assumes that the date is already validated
	$parts = split("/" , $date);
	
	return $parts[2] . "-" . $parts[0] . "-" . $parts[1];
}
   
 
function isLeapYear($year) {
    $isLeapYear = false;
    // Is div by 4?
    if (($year % 4) == 0) 
    {
        $isLeapYear = true;
    }
    // Is div by 100?
    if (($year % 100) == 0) 
    {
        $isLeapYear = false;
    }
    // Is div by 1000?
    if (($year % 1000) == 0) 
    {
        $isLeapYear = true;
    }
	return $isLeapYear;
}

 
function renderDateFieldSet($field,$selectedDay=0,$selectedMonth=0,$selectedYear=0 , $minYear=0, $maxYear=0 , $allownull = true , $attrs="") {
	$fmt = preg_split('//', "d/m/y", -1, PREG_SPLIT_NO_EMPTY);
	foreach ($fmt as $k=>$f) {
		switch ($f) {
			case "d":
				renderDay($field , $selectedDay , $allownull , $attrs);
				break;
			case "m":
				renderMonth($field , $selectedMonth , $allownull , $attrs);
				break;
			case "y":
				renderYear($field , $minYear , $maxYear , $selectedYear , $allownull , $attrs);
				break;
		}
	}
}

function renderMonth($field,$selected=0 , $allownull = true , $attrs="") {
	$months = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
  	?>
    <select name="<?=$field?>_m" id="<?=$field?>_m" <?= $attrs ?>>
    	<?if ($allownull) {
        	echo '<option value="0">-</option>'."\n";
    	}?>
    
        <? for ($i = 0 ; $i < count($months) ; $i++) {
            echo '<option value="'.($i+1).'"'.(($i+1)==$selected?" selected":"").'>'.$months[$i].'</option>'."\n";
        } ?>
    </select>
   <?
}
function renderDay($field,$selected=0 , $allownull = true, $attrs="") {
   ?>
    <select name="<?=$field?>_d" id="<?=$field?>_d" <?= $attrs ?>>
	<?if ($allownull) {
    	echo '<option value="0">-</option>'."\n";
	}?>
	
	<?for ($i = 1 ; $i < 32 ; $i++) {
		echo '<option value="'.$i.'"'.($i==$selected?" selected":"").'>'.$i.'</option>';
	}?>
    </select>
   <?
}
function renderYear($field , $min , $max , $selected=0 , $allownull = true , $attrs="") {
   ?>
    <input type="text" name="<?=$field?>_y" id="<?=$field?>_y" <?= $attrs ?> value="<?=$selected?>" size="4" maxlength="4">
   <?
}




function uploadPicture($postfieldname , $picpath , $max_width , $max_height , $thumb_max_width , $thumb_max_height , $mid_max_width=0 , $mid_max_height=0) {
	if (is_uploaded_file($_FILES[$postfieldname]['tmp_name'])) {
		
		global  $globalErrorMessage;
		
		$path = pathinfo($_FILES[$postfieldname]['name']);
		$ext = strtolower(@$path['extension']);

		$tmpname = UniqueFilename($picpath , createFileName().".".$ext);
		
		if (!move_uploaded_file($_FILES[$postfieldname]['tmp_name'] , $picpath . $tmpname)) {
			$globalErrorMessage[] = "An internal error occurred, please try to upload the picture again, if the problem persists please contact us";
			return false;
		}
		else {
			include_once("admin/cropper/thumbnail.inc.php");
			//Generate the thumb and resize the pic to the max size
			
			//LARGE
			$thumb= new Thumbnail($picpath . $tmpname);
			if ($thumb->getCurrentWidth() > $max_width) {
				$thumb->resize($max_width);
			}
			if ($thumb->getCurrentHeight() > $max_height) {
				$thumb->resize(0,$max_height);
			}
			$final_filename = createFileName() . "." . $ext;
			$final_filename = UniqueFilename($picpath , $final_filename);
			$thumb->save( $picpath . $final_filename , 100); // 100 for quality
			

			

			//THUMBNAIL, only generate if any dimension is larger than 0
			$thumb_final_filename="";
			if (($thumb_max_width>0) || ($thumb_max_height)>0) {
				if ($thumb->getCurrentWidth() > $thumb_max_width) {
					$thumb->resize($thumb_max_width);
				}
				if ($thumb->getCurrentHeight() > $thumb_max_height) {
					$thumb->resize(0,$thumb_max_height);
				}
				$thumb_final_filename = PICTURE_THUMB_PREFIX . $final_filename;
				$thumb->save( $picpath . $thumb_final_filename , 100); // 100 for quality
			}

			$thumb->destruct();

			unlink($picpath . $tmpname);
			
			$ret = array();
			$ret[]=$final_filename;
			$ret[]=$thumb_final_filename;
			return $ret;
		}
	}
	else
		return false;
}


function uploadFile($postfieldname , $filepath) {
	if (is_uploaded_file($_FILES[$postfieldname]['tmp_name'])) {
		
		global  $globalErrorMessage;
		
		$path = pathinfo($_FILES[$postfieldname]['name']);
		$ext = strtolower(@$path['extension']);

		$tmpname = UniqueFilename($filepath , createFileName().".".$ext);
		
		if (!move_uploaded_file($_FILES[$postfieldname]['tmp_name'] , $filepath . $tmpname)) {
			$globalErrorMessage[] = "An internal error occurred, please try to upload the picture again, if the problem persists please contact us";
			return false;
		}
		else {
			return $tmpname;
		}
	}
	else
		return false;
}



function recordsetToArray($rs , $fieldToReturn="") {
	$ret = array();
	while ($r=mysql_fetch_array($rs))
		if (empty ($fieldToReturn))
			$ret[] = $r;
		else
			$ret[] = $r[$fieldToReturn];

	return $ret;
}


function getSiteInfo($key) {
	$rs = mysql_query("select * from information where `key` = '".addslashes($key)."'");
	if ($row = mysql_fetch_assoc($rs)) {
        $ret = $row['Value'];
	}
	return $ret;
}




function idRowExists($table, $idfield, $idvalue) {
	$rs = mysql_query("select * from $table where $idfield = '" . addslashes($idvalue) ."'");
	if (mysql_fetch_assoc($rs))
		return true;
	else
		return false;
}

function GetTableValue($table,$returnfield, $idfield, $idvalue) {
	$rs = mysql_query("select $returnfield from $table where $idfield = '" . addslashes($idvalue) ."'");

    if ($f = mysql_fetch_assoc($rs))
		return $f[$returnfield];
	else
		return DB_GETTABLEVALUE_NORECORDS;
}



function ExecReturnFirstField($sql) {
    $rs = mysql_query($sql);
    if ($f = mysql_fetch_array($rs))
        return $f[0];
    else
        return false;
}


function ExecReturnFirstRow($sql) {
    $rs = mysql_query($sql);
    if ($f = mysql_fetch_array($rs))
        return $f;
    else
        return false;
}


function GetTableRow($table, $idfield, $idvalue , $extrawhere="") {
	$rs = mysql_query("select * from $table where $idfield = '" . addslashes($idvalue) ."'" . ($extrawhere!=""?" AND " . $extrawhere:""));
	if ($r = mysql_fetch_assoc($rs))
		return $r;
	else
		return false;
}


function getSelect($table, $id, $desc, $selectname, $selected, $order = "", $where = "", $tabindex="" , $firstItemArr = "" , $events="" , $class = "") {
	$rs = mysql_query("select $id as id, $desc as det from $table $where " . ($order!=""?"order by " . $order:""));

	if ($tabindex!="") $tabindex=' tabindex="'.$tabindex.'"';
	if ($class!="") $class=' class="'.$class.'"';

	$ret = "<select $class name='$selectname' id='$selectname' size='1'$tabindex $events>\n";

	if (is_array($firstItemArr)) {
		$ret .= "<option value='".$firstItemArr[0]."'>".$firstItemArr[1]."\n";
	}

	while ($r = mysql_fetch_assoc($rs)) {
		$ret .= "<option value='".$r['id']."'".($r['id']==$selected?" selected":"").">".$r['det']."\n";
	}
	$ret .= "</select>\n";
	return $ret;
}



function getSelectGroupBy($table, $id, $desc, $selectname, $selected, $GroupByField , $order = "", $where = "", $tabindex="" , $firstItemArr = "" , $events="" , $class = "") {
	//Ensure to order the RS by $GroupByField first
	
	$rs = mysql_query("select $id as id, $desc as det , $GroupByField as gbf from $table $where " . ($order!=""?"order by " . $order:""));

	if ($tabindex!="") $tabindex=' tabindex="'.$tabindex.'"';
	if ($class!="") $class=' class="'.$class.'"';


	$lastItem="";

	$ret = "<select $class name='$selectname' id='$selectname' size='1'$tabindex $events>\n";


	if (is_array($firstItemArr)) {
		$ret .= "<option value='".$firstItemArr[0]."'>".$firstItemArr[1]."\n";
	}

	while ($r = mysql_fetch_assoc($rs)) {
		if ($lastItem != $r['gbf']) {
			$lastItem = $r['gbf'];
			if (!empty ($lastItem)) $ret .= "</optgroup>";
			$ret .= "<optgroup label=\"".addslashes($lastItem)."\">";
		}
		$ret .= "<option value='".$r['id']."'".($r['id']==$selected?" selected":"").">".$r['det']."\n";
	}
	if (!empty ($lastItem)) $ret .= "</optgroup>";
	$ret .= "</select>\n";
	return $ret;
}


function getSelectFromArray($array, $selectname, $selected, $tabindex="" , $events="" , $class = "") {
	/*
	Takes an array of bi-dimensional arrays and render a select box:
	array (
		"value" => "display text",
		"value" => "display text",
		"value" => "display text"....
	)
	*/
	if ($tabindex!="") $tabindex=' tabindex="'.$tabindex.'"';
	if ($class!="") $class=' class="'.$class.'"';

	$ret = "<select $class name='$selectname' id='$selectname' size='1'$tabindex $events>\n";


	foreach ($array as $k=>$ar) {
		$ret .= "<option value='".$k."'".($k==$selected?" selected":"").">".$ar."\n";
	}
	$ret .= "</select>\n";
	return $ret;
}

function getSelectFromRs($rs, $selectname, $selected, $idField , $DetailField , $firstItemArr="", $tabindex="" , $events="" , $class = "") {
	if ($tabindex!="") $tabindex=' tabindex="'.$tabindex.'"';
	if ($class!="") $class=' class="'.$class.'"';

	$ret = "<select $class name='$selectname' id='$selectname' size='1'$tabindex $events>\n";

	if (is_array($firstItemArr)) {
		$ret .= "<option value='".$firstItemArr[0]."'>".$firstItemArr[1]."</option>\n";
	}

	while ($r = mysql_fetch_array($rs)) {
		$ret .= "<option value='".$r[$idField]."'".($r[$idField]==$selected?" selected":"").">".$r[$DetailField]."\n";
	}
	$ret .= "</select>\n";
	return $ret;
}




// function to generate an unique file name (filename(n).ext)
function UniqueFilename($folder, $oriFilename) {
	$oriFilename = str_replace(" ", "_", $oriFilename);
	$oriFilename = strtolower(basename($oriFilename));
	$destFullPath = $folder . $oriFilename;
	$newFilename = $oriFilename;

	if (!file_exists($folder)) {
		die("(UniqueFilename) Folder does not exist: " . $folder);
	}
	while (file_exists($destFullPath)) {
		$file_extension  = strtolower(strrchr($oriFilename, "."));
		$file_name = createFileName();
		$newFilename = $file_name . $file_extension;
		$destFullPath = $folder . $newFilename;
   	}
	return $newFilename;
}



function createFileName() {
    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    srand((double)microtime()*1000000);
    $i = 0;
    $pass = '' ;

    while ($i <= 14) {
        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    }

    return $pass;
}


# convert a date to special format
# $date is like 2000-01-01 00:00:00
# $format : refer to strftime function
function convert_date($date,$format) {
    if($date=='0000-00-00 00:00:00' OR $date=='0000-00-00' OR $date=='' OR $date==NULL) {
        return '';
    }
    else {
        $year=substr($date,0,4);
        if(phpversion() < 5.0 AND $year < 1970) {

            $new_date=substr_replace($date,'1980',0,4); # we replace the year by a year after 1970
            $new_format=eregi_replace('%a|%A|%u','',$format); # we remove days information from the format because they would be wrong
            $new_date=strftime($new_format,strtotime($new_date)); # we convert the date
            $new_date=eregi_replace('1980',$year,$new_date); # we put back the real year
            return $new_date;
        }
        else {
            return strftime($format,strtotime($date));
        }
    }
}



function previewLongText($string , $maxlen = 220) {
	if (strlen($string)>$maxlen) {
		$ar = split("[ ]",$string);
		$tmp = 0;
		$ret="";
		foreach ($ar as $k=>$v) {
			$tmp += strlen($v);
			if ($tmp < $maxlen)
				$ret .= $v . " ";
			else {
                //Prevent empty string, if the first word exceeds the maxlen
                if (empty ($ret) && !empty ($v))
                    $ret .= substr($v, 0 , $maxlen);

				break;
            }
		}
		return $ret . "...";
	}
	else {
		return $string;
	}
}



function humanReadableSize($size) {
	if (!is_numeric($size)) return 0;

	$unit="bytes";
	if ($size>1024) {
		$size = $size / 1024;
		$unit = "KB";
	}
	if ($size>1024) {
		$size = $size / 1024;
		$unit = "MB";
	}
	if ($size>1024) {
		$size = $size / 1024;
		$unit = "GB";
	}
	return number_format($size) . " " . $unit;
}



function cutText($string , $maxlen = 220) {
	if (strlen($string)>$maxlen) {
		return substr($string, 0 , $maxlen) . "...";
	}
	else {
		return $string;
	}
}


/**
 * Convierte una ruta de caroeta a un nombre utilizable en un archivo, ej:  "coreo 1/Num:2/grupo 3" => "coreo_1_Num_2_grupo_3"
 */
function folderToFilename( $f ) {
	return str_replace(array("/","\\","*",":","<",">","\"","|","?"), "_", $f) . "__";
}
?>