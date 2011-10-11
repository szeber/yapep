<?php

/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * URL handler class
 *
 * @package	YAPEP
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_UrlHandler {

	/**
	 * URL is a directory or a doc?
	 *
	 * @var boolean
	 */
	public $isDir = false;

	/**
	 * Virtual directory handler used?
	 *
	 * @var boolean
	 */
	public $isVirtual = false;

	/**
	 * Root URL for the CMS
	 *
	 * @var string
	 */
	public $rootUrl;

	/**
	 * Language code
	 *
	 * @var string
	 */
	public $language;

	/**
	 * Locale code
	 *
	 * @var string
	 */
	public $locale;

	/**
	 * Locale ID
	 *
	 * @var integer
	 */
	public $locale_id;

	/**
	 * Document path
	 *
	 * @var string
	 */
	public $docPath;

	/**
	 * Document name
	 *
	 * Only if !$isDir
	 *
	 * @var string
	 */
	public $docName;

	/**
	 * Parts of the requested path
	 *
	 * @var array
	 */
	public $pathParts;

	/**
	 * The document suffix
	 *
	 * @var string
	 */
	public $docSuffix;

	/**
	 * DB access
	 *
	 * @var module_db_generic_LangLocale
	 */
	private $db;

	/**
	 * Config data
	 *
	 * @var sys_IApplicationConfiguration
	 */
	private $config;

	/**
	 * Folder information for the given url
	 *
	 * @var array
	 */
	private $folderInfo;

	/**
	 * Parsed URL
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Constructor
	 *
	 * @param string $url
	 */
	public function __construct($url, $config = null) {
		if (is_null($config)) {
			$this->config = sys_ApplicationConfiguration::getInstance ();
		} else {
			$this->config = $config;
		}
		$this->db = getPersistClass ('LangLocale');
		$this->rootUrl = $this->config->getPath ('rootUrl');
		$this->url = $url;
		$this->docSuffix = $this->config->getOption ('docSuffix');
		$this->parseUrl ($url);
	}

	/**
	 * Parses the URL and sets the $folderInfo or throws a SiteException
	 *
	 * @param string $url
	 */
	private function parseUrl($url) {
		$parts = array ();
		$path = '';
		if (preg_match('@^'.$this->rootUrl.'admin(/|$)@', $url)) {
			throw new sys_exception_SiteException ('404 Not found', 404);
		}
		if ($url == $this->rootUrl) {
			$this->redirectToOpeningPage ();
		} elseif (preg_match ('@^' . $this->rootUrl . '([a-zA-Z0-9]{2})(/.*)?$@', $url, $parts)) {
			$lang = $this->db->getLanguageByCode ($parts [1]);
			if (!count ($lang)) {
				throw new sys_exception_SiteException ('404 Not found', 404);
			}
			$_SESSION['langLocale']=$parts[1];
			setcookie('langLocale', $parts[1], (time()+$this->config->getOption('siteLangCookieExpire')), $this->rootUrl);
			$this->language = $lang ['language_code'];
			$this->locale = $lang ['Locale'] ['locale_code'];
			$this->locale_id = $lang ['Locale'] ['id'];
			$path = (isset($parts[2]) ? $parts[2] : null);
		} elseif (preg_match ('@^' . $this->rootUrl . '/?([a-zA-Z0-9]{2,3})([-_][-_a-zA-Z0-9]+)(/.*)?$@', $url, $parts)) {
			$localeid = $parts [1] . str_replace ('-', '_', strtoupper ($parts [2]));
			$locale = $this->db->getLocaleByCode ($localeid);
			if (!count ($locale)) {
				throw new sys_exception_SiteException ('404 Not found', 404);
			}
			$_SESSION['langLocale']=$locale ['locale_code'];
			setcookie('langLocale', $locale ['locale_code'], (time()+$this->config->getOption('siteLangCookieExpire')), $this->rootUrl);
			$this->language = $locale ['locale_code'];
			$this->locale = $locale ['locale_code'];
			$this->locale_id = $locale['id'];
			$path = $parts [3];
		} else {
			throw new sys_exception_SiteException ('404 Not found', 404);
		}
		if ($this->config->getOption('useGettext')) {
			setupGettext($this->locale.'.UTF-8', $this->config->getOption('siteGettextDomain'), CACHE_DIR.'locale/');
		}
		if ($path == '') {
			$path = '/';
		}
		$suffixStart = -1 * strlen ($this->docSuffix);
		if (substr ($path, $suffixStart) == $this->docSuffix) {
			$path = substr ($path, 0, $suffixStart);
		}
		$pathLen = strlen ($path);
		if (!$pathLen || $pathLen > 510) {
			throw new sys_exception_SiteException ('404 Not found', 404);
		}
		$pathParts = explode ('/', $path);
		if (!end ($pathParts)) {
			array_pop ($pathParts);
		}
		$this->pathParts = $pathParts;
		$folderCacheManager = new sys_cache_FolderCacheManager ();
		$this->folderInfo = $folderCacheManager->getFolder ($this->locale_id, $pathParts);
		$this->docPath = $this->folderInfo ['docpath'];
		if ($this->folderInfo ['virtual_subfolders']) {
			$this->isVirtual = true;
		} elseif (empty($this->folderInfo ['doc_id'])) {
			$this->isDir = true;
		} else {
			$this->docName = array_pop ($pathParts);
		}
	}

	/**
	 * Returns the retrieved folder information
	 *
	 * @return array
	 */
	public function getFolderInfo() {
		return $this->folderInfo;
	}

	/**
	 * Redirects the user to the site's opening page.
	 *
	 * The opening page will be set to the user's first preferred language that's available.
	 * If none of her preferred languages are available, it's set to the default language
	 *
	 */
	private function redirectToOpeningPage() {
		$lang_opts = explode (',', $_SERVER ['HTTP_ACCEPT_LANGUAGE']);
		if (isset($_COOKIE['langLocale'])) {
			array_unshift($lang_opts, $_COOKIE['langLocale']);
		}
		if (isset($_SESSION['langLocale'])) {
			array_unshift($lang_opts, $_SESSION['langLocale']);
		}
		if (!$this->config->getOption('disableBrowserLangCheck')) {
    		// Limit the maximum language options to 10 to prevent potential DOS attacks
    		if (count ($lang_opts) > 10) {
    			$lang_opts = array_chunk ($lang_opts, 10);
    		}
    		foreach ( $lang_opts as $lang ) {
    			$regex = '';
    			if (preg_match ('/^(([a-zA-Z0-9]{2,3})(?:[-_][-_a-zA-Z0-9]+)?)/', $lang, $regex)) {
    				$langid = $regex [2];
    				if ($regex [1] == $regex [2]) {
    					$langdata = $this->db->getLanguageByCode ($langid);
    					if ($langdata && count ($langdata)) {
    						header ('Location: /' . $langdata ['language_code'] . '/');
    						exit ();
    					}
    				} else {
    					$localeid = $regex [1];
    					$locale = $this->db->getLocaleByCode ($localeid);
    					if ($locale && count ($locale)) {
    						header ('Location: /' . $locale ['locale_code'] . '/');
    						exit ();
    					}
    				}
    			}
    		}
		}
		header ('Location: /' . $this->config->getOption ('defaultLanguage') . '/');
		exit ();
	}

}
?>