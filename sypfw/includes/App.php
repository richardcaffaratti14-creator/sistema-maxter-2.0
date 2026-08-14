<?php

class App {

	private static $_applicationName;
	private static $_applicationPath;
	private static $_moduleName;
	private static $_modulePath;
	private static $_actionName;
	private static $_actionPath;
	private static $_parameters;
	private static $_urlParts;
	private static $_layout;
	private static $_TRAP;
	private static $_include_paths = array();

	public static function INIT() {
		self::setUrlParts(Http::getUrlParams());
		self::initialize();

		$resolve = false;

		if (self::isModOrActionNull()) {

			$trapFile = APPS_PATH . self::getUrlPart(0) . '/TrapResolver.php';
			//echo APPS_PATH . self::getUrlPart(0) . '/TrapResolver.php';
			if (is_file($trapFile)) {
				if (is_file($trapFile)) {
					include $trapFile;
					$resolve = true;
				} else
					Http::goRoot();
			} else {
				$trapFile = APPS_PATH . DEFAULT_APP_NAME . '/TrapResolver.php';
				if (is_file($trapFile)) {
					include $trapFile;
					$resolve = true;
				}
				else
					Http::goRoot();
			}
		}
		
		if ($resolve) {
			self::$_TRAP = new TrapResolver();
			//Log::l('URL TRAPPED: ');
			//Log::d(self::$_urlParts);
			if (self::$_TRAP->resolveTrap(self::$_urlParts)) {
				self::initialize();
				if (self::isModOrActionNull())
					self::$_TRAP->redirectTo404();
			}
			else
				self::$_TRAP->redirectTo404();
		}
	}

	private static function isModOrActionNull() {
		if (	self::getApplicationName() === NULL ||
				self::getModuleName() === NULL ||
				self::getActionName() === NULL)
			return TRUE;
		else
			return FALSE;
	}

	private static function initialize() {

		//	www.site.com/application/module/action?parameters
		self::$_applicationName = null;
		self::$_moduleName = null;
		self::$_actionName = null;

		self::$_applicationPath = null;
		self::$_modulePath = null;
		self::$_actionPath = null;

		if (count(self::$_urlParts) == 0) {
			if (
					  file_exists(APPS_PATH . DEFAULT_APP_NAME . '/' . DEFAULT_MOD_NAME . '/' . DEFAULT_ACTION_NAME . '.php') ||
					  file_exists(APPS_PATH . DEFAULT_APP_NAME . '/' . DEFAULT_MOD_NAME . '/' . DEFAULT_ACTION_NAME . '_controller.php')
			) {
				self::$_applicationName = DEFAULT_APP_NAME;
				self::$_moduleName = DEFAULT_MOD_NAME;
				self::$_actionName = DEFAULT_ACTION_NAME;

				self::$_applicationPath = APPS_PATH . DEFAULT_APP_NAME . '/';
				self::$_modulePath = APPS_PATH . DEFAULT_APP_NAME . '/' . DEFAULT_MOD_NAME . '/';
				self::$_actionPath = APPS_PATH . DEFAULT_APP_NAME . '/' . DEFAULT_MOD_NAME . '/' . DEFAULT_ACTION_NAME . '.php';
			}
		} else if (count(self::$_urlParts) == 1) {
			//	1 url part
			if (
					  file_exists(APPS_PATH . self::getUrlPart(0) . '/' . DEFAULT_MOD_NAME . '/' . DEFAULT_ACTION_NAME . '.php') ||
					  file_exists(APPS_PATH . self::getUrlPart(0) . '/' . DEFAULT_MOD_NAME . '/' . DEFAULT_ACTION_NAME . '_controller.php') ||
					  file_exists(APPS_PATH . self::getUrlPart(0) . '/' . DEFAULT_MOD_NAME . '/' . APPS_PATH . self::getUrlPart(0) . '.php') ||
					  file_exists(APPS_PATH . self::getUrlPart(0) . '/' . DEFAULT_MOD_NAME . '/' . APPS_PATH . self::getUrlPart(0) . '_controller.php')
			) {
				self::$_applicationName = self::getUrlPart(0);
				self::$_moduleName = DEFAULT_MOD_NAME;
				self::$_actionName = DEFAULT_ACTION_NAME;

				self::$_applicationPath = APPS_PATH . self::getUrlPart(0) . '/';
				self::$_modulePath = APPS_PATH . self::getUrlPart(0) . '/' . DEFAULT_MOD_NAME . '/';

				$actionpath = APPS_PATH . self::getUrlPart(0) . '/' . DEFAULT_MOD_NAME . '/' . DEFAULT_ACTION_NAME . '.php';
				if (file_exists($actionpath))
					self::$_actionPath = $actionpath;
			}
			elseif (
					  file_exists(APPS_PATH . DEFAULT_APP_NAME . '/' . self::getUrlPart(0) . '/' . DEFAULT_ACTION_NAME . '.php') ||
					  file_exists(APPS_PATH . DEFAULT_APP_NAME . '/' . self::getUrlPart(0) . '/' . DEFAULT_ACTION_NAME . '_controller.php') ||
					  file_exists(APPS_PATH . DEFAULT_APP_NAME . '/' . self::getUrlPart(0) . '/' . self::getUrlPart(0) . '.php') ||
					  file_exists(APPS_PATH . DEFAULT_APP_NAME . '/' . self::getUrlPart(0) . '/' . self::getUrlPart(0) . '_controller.php')
			) {
				self::$_applicationName = DEFAULT_APP_NAME;
				self::$_moduleName = self::getUrlPart(0);
				self::$_actionName = DEFAULT_ACTION_NAME;

				self::$_applicationPath = APPS_PATH . DEFAULT_APP_NAME . '/';
				self::$_modulePath = APPS_PATH . DEFAULT_APP_NAME . '/' . self::getUrlPart(0) . '/';

				$actionpath = APPS_PATH . DEFAULT_APP_NAME . '/' . self::getUrlPart(0) . '/' . DEFAULT_ACTION_NAME . '.php';
				if (file_exists($actionpath))
					self::$_actionPath = $actionpath;
			}
			elseif (
					  file_exists(APPS_PATH . DEFAULT_APP_NAME . '/' . DEFAULT_MOD_NAME . '/' . self::getUrlPart(0) . '.php') ||
					  file_exists(APPS_PATH . DEFAULT_APP_NAME . '/' . DEFAULT_MOD_NAME . '/' . self::getUrlPart(0) . '_controller.php')
			) {
				self::$_applicationName = DEFAULT_APP_NAME;
				self::$_moduleName = DEFAULT_MOD_NAME;
				self::$_actionName = self::getUrlPart(0);

				self::$_applicationPath = APPS_PATH . DEFAULT_APP_NAME . '/';
				self::$_modulePath = APPS_PATH . DEFAULT_APP_NAME . '/' . DEFAULT_MOD_NAME . '/';

				$actionpath = APPS_PATH . DEFAULT_APP_NAME . '/' . DEFAULT_MOD_NAME . '/' . self::getUrlPart(0) . '.php';
				if (file_exists($actionpath))
					self::$_actionPath = $actionpath;
			}
		} else if (count(self::$_urlParts) == 2) {
			if (
					  file_exists(APPS_PATH . self::getUrlPart(0) . '/' . self::getUrlPart(1) . '/' . DEFAULT_ACTION_NAME . '.php') ||
					  file_exists(APPS_PATH . self::getUrlPart(0) . '/' . self::getUrlPart(1) . '/' . DEFAULT_ACTION_NAME . '_controller.php')
			) {
				self::$_applicationName = self::getUrlPart(0);
				self::$_moduleName = self::getUrlPart(1);
				self::$_actionName = DEFAULT_ACTION_NAME;

				self::$_applicationPath = APPS_PATH . self::getUrlPart(0) . '/';
				self::$_modulePath = APPS_PATH . self::getUrlPart(0) . '/' . self::getUrlPart(1) . '/';

				$actionpath = APPS_PATH . self::getUrlPart(0) . '/' . self::getUrlPart(1) . '/' . DEFAULT_ACTION_NAME . '.php';
				if (file_exists($actionpath))
					self::$_actionPath = $actionpath;
			} elseif (
					  file_exists(APPS_PATH . DEFAULT_APP_NAME . '/' . self::getUrlPart(0) . '/' . self::getUrlPart(1) . '.php') ||
					  file_exists(APPS_PATH . DEFAULT_APP_NAME . '/' . self::getUrlPart(0) . '/' . self::getUrlPart(1) . '_controller.php')
			) {
				self::$_applicationName = DEFAULT_APP_NAME;
				self::$_moduleName = self::getUrlPart(0);
				self::$_actionName = self::getUrlPart(1);

				self::$_applicationPath = APPS_PATH . DEFAULT_APP_NAME . '/';
				self::$_modulePath = APPS_PATH . DEFAULT_APP_NAME . '/' . self::getUrlPart(0) . '/';

				$actionpath = APPS_PATH . DEFAULT_APP_NAME . '/' . self::getUrlPart(0) . '/' . self::getUrlPart(1) . '.php';
				if (file_exists($actionpath))
					self::$_actionPath = $actionpath;
			} elseif (
					  file_exists(APPS_PATH . self::getUrlPart(0) . '/' . DEFAULT_MOD_NAME . '/' . self::getUrlPart(1) . '.php') ||
					  file_exists(APPS_PATH . self::getUrlPart(0) . '/' . DEFAULT_MOD_NAME . '/' . self::getUrlPart(1) . '_controller.php')
			) {
				self::$_applicationName = self::getUrlPart(0);
				self::$_moduleName = DEFAULT_MOD_NAME;
				self::$_actionName = self::getUrlPart(1);

				self::$_applicationPath = APPS_PATH . self::getUrlPart(0) . '/';
				self::$_modulePath = APPS_PATH . self::getUrlPart(0) . '/' . DEFAULT_MOD_NAME . '/';

				$actionpath = APPS_PATH . self::getUrlPart(0) . '/' . DEFAULT_MOD_NAME . '/' . self::getUrlPart(1) . '.php';
				if (file_exists($actionpath))
					self::$_actionPath = $actionpath;
			} elseif (
					  file_exists(APPS_PATH . self::getUrlPart(0))
			) {
				self::$_applicationName = self::getUrlPart(0);
				self::$_moduleName = NULL;
				self::$_actionName = NULL;
				
				self::$_applicationPath = APPS_PATH . self::getUrlPart(0) . '/';
			}
		} else if (count(self::$_urlParts) >= 3) {
			if (
					  file_exists(APPS_PATH . self::getUrlPart(0) . '/' . self::getUrlPart(1) . '/' . self::getUrlPart(2) . '.php') ||
					  file_exists(APPS_PATH . self::getUrlPart(0) . '/' . self::getUrlPart(1) . '/' . self::getUrlPart(2) . '_controller.php')
			) {
				self::$_applicationName = self::getUrlPart(0);
				self::$_moduleName = self::getUrlPart(1);
				self::$_actionName = self::getUrlPart(2);

				self::$_applicationPath = APPS_PATH . self::getUrlPart(0) . '/';
				self::$_modulePath = APPS_PATH . self::getUrlPart(0) . '/' . self::getUrlPart(1) . '/';

				$actionpath = APPS_PATH . self::getUrlPart(0) . '/' . self::getUrlPart(1) . '/' . self::getUrlPart(2) . '.php';
				if (file_exists($actionpath))
					self::$_actionPath = $actionpath;
			} elseif (
					  file_exists(APPS_PATH . self::getUrlPart(0))
			) {
				
				self::$_applicationName = self::getUrlPart(0);
				self::$_moduleName = NULL;
				self::$_actionName = NULL;
				
				self::$_applicationPath = APPS_PATH . self::getUrlPart(0) . '/';
			}
		}
	}

	public static function getApplicationControllerPath() {
		return self::$_applicationPath . 'controller.php';
	}

	public static function getApplicationConfigurationPath() {
		return self::$_applicationPath . 'app_configuration.php';
	}

	public static function getModuleControllerPath() {
		return self::$_modulePath . 'controller.php';
	}

	public static function getActionControllerPath() {
		return self::$_modulePath . self::$_actionName . '_controller.php';
	}

	public static function getLayoutPath() {
		return SYP_PATH . 'layouts/' . self::$_layout;
	}

	private static function removeDefault($url){
		return str_replace('default/', '', $url);
	}
	
	public static function getApplicationUrl() {
		return self::removeDefault( SITE_ROOT.self::$_applicationName . '/');
	}

	public static function getModuleUrl() {
		return self::removeDefault(SITE_ROOT.self::$_applicationName . '/' . self::$_moduleName . '/');
	}

	public static function getActionUrl() {
		return self::removeDefault(SITE_ROOT.self::$_applicationName . '/' . self::$_moduleName . '/' . self::$_actionName . '/');
	}

	//	-----------------------------------------------------------
	//	GETTERS / SETTERS
	//	-----------------------------------------------------------
	
	public static function addIncludePath($path){
		self::$_include_paths[] = $path;
	}
	
	public static function getIncludePaths(){
		return self::$_include_paths;
	}
	
	public static function getLayout() {
		return self::$_layout;
	}

	public static function setLayout($_layout) {
		self::$_layout = $_layout;
	}

	public static function getModuleName() {
		return self::$_moduleName;
	}

	public static function setModuleName($_currentModuleName) {
		self::$_moduleName = $_currentModuleName;
	}

	public static function getUrlParts() {
		return self::$_urlParts;
	}

	public static function getUrlPart($idx) {
		return self::$_urlParts[$idx];
	}

	private static function setUrlParts($_urlParts) {
		self::$_urlParts = $_urlParts;
	}

	public static function getModulePath() {
		return self::$_modulePath;
	}

	public static function setModulePath($_currentModulePath) {
		self::$_modulePath = $_currentModulePath;
	}

	public static function getApplicationName() {
		return self::$_applicationName;
	}

	public static function setApplicationName($_currentApplicationName) {
		self::$_applicationName = $_currentApplicationName;
	}

	public static function getApplicationPath() {
		return self::$_applicationPath;
	}

	public static function setApplicationPath($_currentApplicationPath) {
		self::$_applicationPath = $_currentApplicationPath;
	}

	public static function getParameters($idx) {
		return self::$_parameters[$idx];
	}

	public static function setParameters($_parameters) {
		self::$_parameters = $_parameters;
	}

	public static function getAllParameters() {
		return self::$_parameters;
	}

	public static function getActionName() {
		return self::$_actionName;
	}

	public static function setActionName($_currentActionName) {
		self::$_actionName = $_currentActionName;
	}

	public static function getActionPath() {
		return self::$_actionPath;
	}

	public static function setActionPath($_currentActionPath) {
		self::$_actionPath = $_currentActionPath;
	}

}

?>