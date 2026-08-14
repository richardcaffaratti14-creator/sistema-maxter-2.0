<?
error_reporting(0);

session_start();

chdir('../');

include 'configuration.php';
include SYP_PATH.'includes/functions.php';
//	------------------------------------------
$cssBuilder_compactLevel = 2;
$debug = $_GET['debug'];
$split = $_GET['split'];
$site_code = $_GET['sc'];

if ($debug) {
	list($usec, $sec) = explode(" ", microtime());
	$start = ((float) $usec + (float) $sec);
}

$myBrowser[] = array(is => (strpos($_SERVER['HTTP_USER_AGENT'], "Firefox") > -1),
	browser => "FF"
);

$myBrowser[] = array(is => (strpos($_SERVER['HTTP_USER_AGENT'], "Chrome") > -1),
	browser => "CH"
);

$myBrowser[] = array(is => (strpos($_SERVER['HTTP_USER_AGENT'], "Firefox/3") > -1),
	browser => "FF3"
);

$myBrowser[] = array(is => (strpos($_SERVER['HTTP_USER_AGENT'], "Firefox/2") > -1),
	browser => "FF2"
);

$myBrowser[] = array(is => (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") > -1),
	browser => "IE"
);

$myBrowser[] = array(is => (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE 9") > -1),
	browser => "IE9"
);

$myBrowser[] = array(is => (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE 8") > -1),
	browser => "IE8"
);

$myBrowser[] = array(is => (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE 7") > -1),
	browser => "IE7"
);

$myBrowser[] = array(is => (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE 6") > -1),
	browser => "IE6"
);

$myBrowser[] = array(is => (strpos($_SERVER['HTTP_USER_AGENT'], "Safari") > -1),
	browser => "SAF"
);

$myBrowser[] = array(is => (strpos($_SERVER['HTTP_USER_AGENT'], "Opera") > -1),
	browser => "OP"
);

$cssContent = '';
$files = SessionManager::getValueFromSiteCode('__CSS__', $site_code);
if (is_array($files)) {
	foreach ($files as $cssFile) {
		if (file_exists($cssFile) ){
			if ( $split == 1 )
				$cssContent .= '/--'.$cssFile.'--/'.file_get_contents($cssFile)."\n";
			else 
				$cssContent .= file_get_contents($cssFile)."\n";

		}
		else 
			Log::l (basename (__FILE__) . " | CSS doesn't exist $cssFile");
	}
}

for ($i = 0; $i < count($myBrowser); $i++) {
	if (!$myBrowser[$i]['is']) {
		$cssContent = preg_replace('/\/\\*\\*' . $myBrowser[$i]['browser'] . '\\*\\*\/(.*?)\/\\*\\*\\*\\*\//s', '', $cssContent);
	}
}

switch ($cssBuilder_compactLevel) {
	case 1:
		$cssContent = preg_replace('/\/\\*(.+?)\\*\//s', '', $cssContent); //	Strip comments
		$search = array("\n", "\t", "\r");
		$replace = array("", "", "");
		$cssContent = str_replace($search, $replace, $cssContent);
		$cssContent = str_replace("}", "}\n", $cssContent);
		break;
	case 2:
		$cssContent = preg_replace('/(\/\\*.+?\\*\/)|(\\n)|(\\r)|(\\t)/s', '', $cssContent);
		break;

	default:
		$cssContent = preg_replace('/\/\\*(.+?)\\*\//s', '', $cssContent); //	Strip comments
		break;
}

if ( $split == 1 )
{
	$cssContent = preg_replace('%/--(.+?)--/%', "\n".'/*\1*/ ', $cssContent);
}

header("Content-type: text/css");
if ($debug) {
	list($usec, $sec) = explode(" ", microtime());
	$end = ((float) $usec + (float) $sec);
	$cssContent = "/* Build time:\n $end - $start = " . ($end - $start) . " */ \n" . $cssContent;
}

echo $cssContent;
?>