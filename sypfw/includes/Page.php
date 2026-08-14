<?php

class Page {

    private static $_css = array();
    private static $_js = array();
    private static $_title = SITE_DEFAULT_TITLE;
    private static $_vars = array();

    public static function addCSS($path) {
	$cssfile = App::getModulePath() . $path;

	if (Utils::left($path, 5) == 'http:')
	    self::$_css[] = $path;
	elseif (file_exists($cssfile))
	    self::$_css[] = $cssfile;
	else {
	    $cssfile = CSS_PATH . $path;
	    if (file_exists($cssfile))
		self::$_css[] = $cssfile;
	    else {
		Log::l("CSS doesn't found: $path");
	    }
	}
    }

    public static function addCSSIfIs($path, $isBrowser) {
	if (strpos($_SERVER['HTTP_USER_AGENT'], $isBrowser) > -1) {
	    Page::addCSS($path);
	} else {
	    Log::l(basename(__FILE__) . " << Ignoring browser don't match: $path");
	}
    }

    public static function addJS($path) {
	if (Utils::left($path, 5) == 'http:' || Utils::left($path, 6) == 'https:')
	    self::$_js[] = $path;
	elseif (is_file($path)) {
	    self::$_js[] = $path;
	} else {
	    $jsfile = App::getModulePath() . $path;
	    if (is_file($jsfile)) {
		self::$_js[] = $jsfile;
	    } else {
		$jsfile = JS_PATH . $path;
		if (is_file($jsfile))
		    self::$_js[] = $jsfile;
		else {
		    Log::l(basename(__FILE__) . " << JS doesn't found: $path");
		}
	    }
	}
    }

    public static function getCSS() {
	SessionManager::unsetValue('__CSS__');
	$rtn = '';
	foreach (self::$_css as $file) {
	    if (Utils::left($file, 5) != 'http:')
		SessionManager::pushValue('__CSS__', $file, false);
	    else
		$rtn .= '<link media="all" rel="stylesheet" href="' . $file . '" type="text/css" />' . "\n";
	}
	$rtn .= '<link media="all" rel="stylesheet" href="' . STATIC_PATH . 'cssbuilder.php?sc=' . SITE_CODE . '" type="text/css" />';
	return $rtn;
    }

    public static function getJS() {
	SessionManager::unsetValue('__JS__');
	$embed = '';
	foreach (self::$_js as $file) {
	    if (Utils::left($file, 5) != 'http:' && Utils::left($file, 6) != 'https:')
		SessionManager::pushValue('__JS__', $file, false);
	    else
		$embed .= '<script type="text/javascript" language="JavaScript" src="' . $file . '"></script>' . "\n";
	}
	  $embed .= '<script type="text/javascript" language="JavaScript" src="' . STATIC_PATH . 'jsbuilder.php?sc=' . SITE_CODE . '"></script>';
	return $embed;
    }

    public static function setTitle($title) {
	self::$_title = $title;
    }

    public static function setTitleSuffix($suffix) {
	self::setTitle(self::$_title . $suffix);
    }

    public static function setTitlePrefix($prefix) {
	self::setTitle($prefix . self::$_title);
    }

    public static function getTitle() {
	return self::$_title;
    }

    public static function addVar($var, $value) {
	self::$_vars[$var] = $value;
    }

    public static function getVar($var) {
	return self::$_vars[$var];
    }

}
