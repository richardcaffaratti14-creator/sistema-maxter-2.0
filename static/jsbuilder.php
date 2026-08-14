<?
error_reporting(0);

session_start();

chdir('../');

include 'configuration.php';
include SYP_PATH.'includes/functions.php';
//	------------------------------------------
$split = $_GET['split'];
$site_code = $_GET['sc'];

$jsContent = '';
$files = SessionManager::getValueFromSiteCode('__JS__', $site_code);
if (is_array($files)) {
	foreach ($files as $jsFile) {
		
		if (file_exists($jsFile)) {
			if ($split == 1)
				$jsContent .= '/--' . $jsFile . '--/' . trim(file_get_contents($jsFile)) . "\n";
			else
				$jsContent .= trim(file_get_contents($jsFile)) . "\n";
		} else {
			Log::l(basename(__FILE__) . " | JS doesn't exist $jsFile");
		}
	}
}

if ($split == 1) {
	$jsContent = preg_replace('%/--(.+?)--/%', "\n" . '/*\1*/ ', $jsContent);
}

header("Content-type: text/javascript");
if ($debug) {
	list($usec, $sec) = explode(" ", microtime());
	$end = ((float) $usec + (float) $sec);
	$jsContent = "/* Build time:\n $end - $start = " . ($end - $start) . " */ \n" . $jsContent;
}

echo $jsContent;
?>