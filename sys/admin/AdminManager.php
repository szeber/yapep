<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * YAPEP
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_admin_AdminManager {

	/**
	 * Authentication
	 *
	 * @var sys_Auth
	 */
	private $auth;

	/**
	 * Smarty
	 *
	 * @var Smarty
	 */
	private $smarty;

	/**
	 * Configuration
	 *
	 * @var sys_IApplicationConfiguration
	 */
	private $config;

	/**
	 * The admin interface's locale
	 *
	 * @var string
	 */
	private $adminLocale;

	/**
	 * The site's locale
	 *
	 * @var string
	 */
	private $siteLocale;

	/**
	 * Array containing all of the listener objects
	 *
	 * @var array
	 */
	private $listeners = array ();

	/**
	 * The admin module
	 *
	 * @var sys_admin_AdminModule
	 */
	private $module;

    /**
     *
     * @var sys_Debugger
     */
    private $debugger;

	/**
	 * Constructor
	 *
	 * @param sys_IApplicationConfiguration $config
	 */
	public function __construct(sys_IApplicationConfiguration $config = null) {
		if (is_null($config)) {
			$this->config = sys_ApplicationConfiguration::getInstance();
		} else {
			$this->config = $config;
		}
        if(DEBUGGING) {
            $this->debugger = sys_Debugger::getInstance();
            $this->debugger->startTimer();
        }
		$this->auth = new sys_Auth ('LoggedInAdminData', 'AdminUser');
		$smartyProto = sys_LibFactory::getSmartyProto();
		$smartyProto->caching = false;
		$this->smarty = sys_LibFactory::getSmarty ();
	}

	/**
	 * Processes the request for the admin page and returns the XML or the loader
	 *
	 */
	public function getAdmin() {
		$this->runEvent ('preProcess', array ('xml' => (!empty($_POST ['admin_xml']) ? $_POST ['admin_xml'] : null)));
		if (empty($_POST ['admin_xml'])) {
			$this->getInterface ();
			return;
		}
		ob_start('ob_gzhandler');
		header ('Content-type: application/xml; charset: utf-8');
		try {
			$xml = @simplexml_load_string ($_POST ['admin_xml']);
			// Validation
			if (!is_object ($xml)) {
				throw new sys_exception_AdminException (_ ('XML not well formed'), sys_exception_AdminException::ERR_XML_NOT_WELL_FORMED);
			}
			if (!isset ($xml->adminData) || !isset ($xml->adminLocale) || !isset ($xml->siteLocale)) {
				throw new sys_exception_AdminException (_ ('Invalid XML received'), sys_exception_AdminException::ERR_INVALID_XML_RECEIVED);
			}
			// Check if user is logged in
			$loggedIn = $this->auth->checkLoggedIn ();
			if ($loggedIn) {
			    define('CURRENT_USER_ID', $_SESSION['LoggedInAdminData']['UserId']);
			}
			// Set locales
			$this->setAdminLocale ((string) $xml->adminLocale);
			$this->setSiteLocale ((string) $xml->siteLocale);
			// Check if the request is a list
			if ('messages' == trim((string)$xml->adminData->id)) {
				$this->getMessagesXml();
				$this->saveDebug();
				$this->runEvent ('postProcess', array ('xml' => $_POST ['admin_xml']));
				return;
			}
			if ('variables' == trim((string)$xml->adminData->id)) {
				$this->getVariablesXml();
				$this->saveDebug();
				$this->runEvent ('postProcess', array ('xml' => $_POST ['admin_xml']));
				return;
			}
			if (!$loggedIn) {
				$this->getLogin ();
			} else if ('list' == trim((string)$xml->adminData->id)) {
				if (!class_exists('module_admin_List')) {
					throw new sys_exception_AdminException (_ ('Admin module not found'), sys_exception_AdminException::ERR_ADMIN_MODULE_NOT_FOUND);
				}
				$this->module = new module_admin_List($this);
			} else {
				// Check if admin module exists, load and execute it
				preg_match('@^([-_a-zA-Z0-9]+)(/|$)@', urldecode((string)$xml->adminData->name), $name);
				$className = $name[1];
				$this->validateAdminClassName ($className);
				if (!class_exists ('module_admin_' . $className)) {
					throw new sys_exception_AdminException (_ ('Admin module not found'), sys_exception_AdminException::ERR_ADMIN_MODULE_NOT_FOUND);
				}
				$moduleName = 'module_admin_' . $className;
				$module =  new $moduleName ($this);
				if (! $this->module) {
					$this->module = $module;
				}
				unset($module);
			}
			if (!$this->module instanceof sys_admin_AdminModule) {
				throw new sys_exception_AdminException (_ ('Admin module not found'), sys_exception_AdminException::ERR_ADMIN_MODULE_NOT_FOUND);
			}
			$this->module->setLocale($this->siteLocale);
			$this->module->parseXml ($xml);
			echo $this->module->execute ();
			$this->saveDebug();
			$this->runEvent ('postProcess', array ('xml' => $_POST ['admin_xml']));
		} catch (sys_exception_AdminException $e) {
			$this->getError ($e->getMessage (), $e->getCode ());
			$this->saveDebug();
		}
	}

	/**
	 * Replaces the module to be run
	 *
	 * @param sys_admin_AdminModule $module
	 */
	public function replaceModule(sys_admin_AdminModule $module) {
		$this->module = $module;
	}

	/**
	 * Makes the login XML
	 *
	 */
	private function getLogin() {
		$this->replaceModule(new module_admin_Login($this));
	}

	/**
	 * Returns the HTML to hold the admin interface and load the required linked files
	 *
	 */
	private function getInterface() {
		header ('Content-type: text/html; charset: utf-8');
		$if = $this->config->getOption('adminWebInterface');
		$if = strtolower($if);
		switch($if) {
			case 'flex':
			case 'flash':
				$if = 'flex';
				break;
			case 'js':
			case 'ajax':
			default:
				$if = 'js';
				break;
		}
		$this->smarty->display ('yapep:admin/'.$if.'/index.tpl');
	}

	/**
	 * Makes the error message XML
	 *
	 * @param string $message
	 * @param integer $code
	 * @param string $description
	 */
	private function getError($message, $code, $description = '') {
		$this->smarty->assign ('message', $message);
		$this->smarty->assign ('code', $code);
		$this->smarty->assign ('description', $description);
		$this->smarty->display ('yapep:admin/error.tpl');
	}

	/**
	 * Sets the admin interface's locale
	 *
	 * @param string $locale
	 */
	private function setAdminLocale($locale) {
		if (!$locale) {
			if (isset($_SESSION['LoggedInAdminData']['Locale'])) {
				$locale = $_SESSION['LoggedInAdminData']['Locale']['locale_code'];
			} else {
				$locale = $this->config->getOption('defaultAdminLocale');
			}
		}
		if (!preg_match ('/^([a-zA-Z0-9]{2,3})([-_][-_a-zA-Z0-9]+)$/', $locale)) {
			throw new sys_exception_AdminException (_ ('Admin locale is invalid'), sys_exception_AdminException::ERR_ADMIN_LOCALE_IS_INVALID);
		}
		$this->adminLocale = trim($locale);
		$domain = 'YapepAdmin';
		if ($this->config->getOption ('adminGettextDomain')) {
			$domain = $this->config->getOption ('adminGettextDomain');
		}
		$path = SYS_PATH . 'locale/';
		if (is_dir(realpath (PROJECT_PATH . 'locale/' . $locale . '/LC_MESSAGES')) && file_exists (PROJECT_PATH . 'locale/' . $locale . '/LC_MESSAGES/' . $domain . '.mo')) {
			$path = PROJECT_PATH . 'locale/';
		}
		if (is_dir(realpath (CACHE_DIR . 'locale/' . $locale . '/LC_MESSAGES')) && file_exists (CACHE_DIR . 'locale/' . $locale . '/LC_MESSAGES/' . $domain . '.mo')) {
			$path = CACHE_DIR . 'locale/';
		}
		setupGettext ($locale.'.UTF-8', $domain, $path);
	}

	/**
	 * Set's the administered locale
	 *
	 * @param string $locale
	 */
	private function setSiteLocale($locale) {
		$locale = trim($locale);
		$db = getPersistClass ('LangLocale');
		if (!$locale) {
			$defaultLocale = $this->config->getOption ('defaultLanguage');
			if (!preg_match ('/^([a-zA-Z0-9]{2,3})([-_][-_a-zA-Z0-9]+)$/', $defaultLocale)) {
				// convert lang to locale
				$tmp = $db->getLanguageByCode ($defaultLocale);
				if (!$tmp) {
					throw new sys_exception_AdminException (_ ('Site default locale is invalid'), sys_exception_AdminException::ERR_INVALID_LOCALE);
				}
				$defaultLocale = $tmp ['locale_code'];
			}
			$this->siteLocale = $defaultLocale;
			return;
		}
		if (!preg_match ('/^([a-zA-Z0-9]{2,3})([-_][-_a-zA-Z0-9]+)$/', $locale)) {
			throw new sys_exception_AdminException (_ ('Site locale is invalid'), sys_exception_AdminException::ERR_INVALID_LOCALE);
		}
		$tmp = $db->getLocaleByCode ($locale);
		if (!$tmp) {
			throw new sys_exception_AdminException (_ ('Site locale is invalid'), sys_exception_AdminException::ERR_INVALID_LOCALE);
		}
		$this->siteLocale = $locale;
	}

	/**
	 * Checks if the provided class name is valid
	 *
	 * @param string $name
	 */
	private function validateAdminClassName($name) {
		if (!preg_match ('/[a-zA-Z][_a-zA-Z0-9]+/', $name)) {
			throw new sys_exception_AdminException (_ ('Invalid admin module name'), sys_exception_AdminException::ERR_INVALID_ADMIN_MODULE_NAME);
		}
	}

	/**
	 * Adds an event listener
	 *
	 * @param sys_admin_Listener $listener
	 */
	public function addListener(sys_admin_Listener $listener) {
		$this->listeners [] = $listener;
	}

	/**
	 * Removes an event listener
	 *
	 * @param sys_admin_Listener $listener
	 * @return boolean
	 */
	public function removeListener(sys_admin_Listener $listener) {
		$index = array_search ($listener, $this->listeners, true);
		if (false === $index) {
			return false;
		}
		unset ($this->listeners [$index]);
		return true;
	}

	private function getMessagesXml() {
		$this->smarty->display('yapep://admin/messages.tpl');
	}

	private function getVariablesXml() {
		$this->smarty->display('yapep://admin/variables.tpl');
	}

	private function saveDebug() {
		if (!DEBUGGING) {
			return;
		}
        $this->debugger->getAdminDebugInfo(get_class($this->module), $_POST['admin_xml'], ob_get_contents());
		if (file_exists(CACHE_DIR.'debug_sent_xml_9.xml')) {
			unlink(CACHE_DIR.'debug_sent_xml_9.xml');
		}
		if (file_exists(CACHE_DIR.'debug_rec_xml_9.xml')) {
			unlink(CACHE_DIR.'debug_rec_xml_9.xml');
		}
		$i=8;
		while($i>=0) {
			if (file_exists(CACHE_DIR.'debug_sent_xml_'.$i.'.xml')) {
				rename(CACHE_DIR.'debug_sent_xml_'.$i.'.xml', CACHE_DIR.'debug_sent_xml_'.($i+1).'.xml');
			}
			if (file_exists(CACHE_DIR.'debug_rec_xml_'.$i.'.xml')) {
				rename(CACHE_DIR.'debug_rec_xml_'.$i.'.xml', CACHE_DIR.'debug_rec_xml_'.($i+1).'.xml');
			}
			$i--;
		}
		file_put_contents(CACHE_DIR.'debug_sent_xml_0.xml', ob_get_contents());
		file_put_contents(CACHE_DIR.'debug_rec_xml_0.xml', $_POST['admin_xml']);
	}

	/**
	 * Returns an array containing all event listener objects
	 *
	 * @return array
	 */
	public function getListeners() {
		return $this->listeners;
	}

	/**
	 * Runs an event on all listeners
	 *
	 * @param string $name
	 * @param array $event
	 */
	public function runEvent($name, $event = array()) {
		if ('pre' != substr ($name, 0, 3) && 'post' != substr ($name, 0, 4)) {
			throw new sys_exception_AdminException (_ ('Invalid event name'), sys_exception_AdminException::ERR_INVALID_EVENT_NAME);
		}
		foreach ( $this->listeners as $listener ) {
			$listener->$name ($event, $this);
		}
	}

	/**
	 * Returns the currently set admin locale
	 *
	 * @return string
	 */
	public function getAdminLocale() {
		return $this->adminLocale;
	}

    /**
     * Returns the used admin module
     *
     * @return sys_admin_AdminModule
     */
    public function getModule() {
        return $this->module;
    }
}
?>