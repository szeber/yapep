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
 * Document factory class
 *
 * @package	YAPEP
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
final class sys_DocFactory {

	/**
	 * Persistance layer instance
	 *
	 * @var module_db_interface_DocFactory
	 */
	private static $persist;

	/**
	 * Configuration data
	 *
	 * @var sys_IApplicationConfiguration
	 */
	private static $config;

	final private function __construct() {}

	/**
	 * Initializes class variables
	 *
	 */
	static private function init() {
		if (!self::$config) {
			self::$config = sys_ApplicationConfiguration::getInstance ();
		}
		if (!self::$persist) {
			self::$persist = getPersistClass ('DocFactory');
		}
	}

	/**
	 * Sets the configuration instance for the class
	 *
	 * @param sys_IApplicationConfiguration $config
	 */
	static public function setConfig(sys_IApplicationConfiguration $config) {
		self::$config = $config;
	}

	/**
	 * Parses a document path into a document name and
	 *
	 * @param string $docpath
	 * @return array
	 */
	static private function parseDocPath($docpath) {
		self::init ();
		$suffix = trim(preg_quote (self::$config->getOption ('docSuffix')));
        if (strlen($suffix)) {
            $preg_suffix = '(?:'.$suffix.')?';
        } else {
            $preg_suffix = '';
        }
		preg_match ('<^/?(.*?)(/([^/]+' . $preg_suffix . '))?$>', $docpath, $parts);
		$path = array ('docpath' => $parts [1]);
		if (!isset ($parts [2])) {
			$path ['docname'] = 'index';
		} else {
			$path ['docname'] = $parts [3];
		}
		return $path;
	}

	static protected function checkAdminPreview() {
		if (defined('ADMIN_PREVIEW') && ADMIN_PREVIEW) {
			return true;
		}
		return false;
	}

	/**
	 * Returns the document id if the document specified by its language, path and name exists. Returns 0 otherwise.
	 *
	 * @param integer $localeId
	 * @param string $docpath
	 * @param string $docname
	 * @param boolean $inactive
	 * @return integer
	 */
	static public function docExists($localeId, $docpath, $docname, $inactive = false) {
		self::init ();
		if (self::checkAdminPreview()) {
			$inactive = true;
		}
		return (int) self::$persist->getDocIdByDocPath ($localeId, $docpath, $docname, $inactive);
	}

	/**
	 * Returns the document by its ID
	 *
	 * @param integer $id
	 * @param boolean $inactive If true returns the document even if it's inactive
	 * @return sys_DocModule
	 */
	static public function getDocByDocId($id, $inactive = false) {
		self::init ();
		$typeData = self::$persist->getDocTypeByDocId ((int) $id);
		if (is_object ($typeData)) {
			$typeData = $typeData->toArray ();
		}
		$typeData ['doc_id'] = (int) $id;
		return self::getDocObject ($typeData, $inactive);
	}

	/**
	 * Returns a document by its language and path
	 *
	 * @param string $lang
	 * @param string $docpath
	 * @param boolan $inactive If true returns the document even if it's inactive
	 * @return sys_DocModule
	 */
	static public function getDocByDocPath($localeId, $docpath, $inactive = false) {
		$docInfo = self::parseDocPath ($docpath);
		$doc = self::getDoc ($localeId, $docInfo ['docpath'], $docInfo ['docname'], $inactive);
		if (!$doc) {
			$doc = self::getDoc ($localeId, $docpath, 'index', $inactive);
		}
		return $doc;
	}

	/**
	 * Returns a document's type by its language and path
	 *
	 * @param string $lang
	 * @param string $docpath
	 * @return array
	 */
	static public function getDocTypeByDocPath($localeId, $docpath) {
		$docInfo = self::parseDocPath ($docpath);
		$docType = self::getDocTypeByDocPathName ($localeId, $docInfo ['docpath'], $docInfo ['docname'], false);
		if (!$docType) {
			$docType = self::getDocTypeByDocPathName ($localeId, $docpath, 'index', false);
		}
		return $docType;
	}

	/**
	 * Returns a document's type by its language, path and name
	 *
	 * @param string $lang
	 * @param string $docpath
	 * @param string $docname
	 * @return array
	 */
	static public function getDocTypeByDocPathName($localeId, $docpath, $docname) {
		return self::$persist->getDocTypeByDocPathName ($localeId, $docpath, $docname);
	}

	/**
	 * Returns a document's type by its id
	 *
	 * @param integer $id
	 * @return array
	 */
	static public function getDocTypeByDocId($id) {
		return self::$persist->getDocTypeByDocId ($id);
	}

	/**
	 * Returns a document by its language, path and namem
	 *
	 * @param string $lang
	 * @param string $docpath
	 * @param string $docname
	 * @param boolean $inactive If true returns the document even if it's inactive
	 * @return sys_DocModule
	 */
	static public function getDoc($localeId, $docpath, $docname, $inactive = false) {
		if (self::checkAdminPreview()) {
			$inactive = true;
		}
		$docType = self::$persist->getDocTypeByDocPathName ($localeId, $docpath, $docname);
		if (is_object ($docType)) {
			$docType = $docType->toArray ();
		}
		$docType ['doc_id'] = self::$persist->getDocIdByDocPath ($localeId, $docpath, $docname, $inactive);
		if (!$docType['doc_id']) {
			return array();
		}
		return self::getDocObject ($docType, $inactive);
	}

	/**
	 * Returns a document object
	 *
	 * @param array $type
	 * @param booelan $inactive
	 * @return sys_DocModule
	 */
	static private function getDocObject($type, $inactive) {
		if (!count ($type)) {
			return false;
		}
		if (self::checkAdminPreview()) {
			$inactive = true;
		}
		$docTypeClass = 'module_doc_' . $type ['handler_class'];
		if (!class_exists ($docTypeClass)) {
			throw new sys_exception_ModuleException ('Missing document module: ' . $docTypeClass, 500);
		}
		$doc = new $docTypeClass (getPersistClass ($type ['handler_class']), $type ['id']);
		$doc->loadInactive = $inactive;
		$doc->loadDoc ($type ['doc_id']);
		return $doc;
	}

	/**
	 * Returns an array with the documents that contain the given $text
	 *
	 * @param string $lang
	 * @param string $startPath The path we should start searching at
	 * @param array $doctypes Array containing the document types to search for
	 * @param string $text The text to search for
	 * @param array $includeFolders Folder IDs or docpaths that should be included in the search
	 * @param array $excludeFolders Folder IDs or docpaths that should be excluded from the search
	 * @param boolean $inactive If true the search includes inactive documents
	 * 	 * @return sys_DocModule[] 2 dimensional array containing the docs found. First key is the doctype
	 */
	static public function findDoc($localeId, $startPath, $doctypes, $text, $limit = -1, $offset = 0, $includeFolders = array(), $excludeFolders = array(), $inactive = false) {
        self::init ();
		if (is_string ($doctypes)) {
			$doctypes = explode (',', $doctypes);
			foreach($doctypes as $key=>$doctype) {
				if (!$doctype) {
					unset ($doctypes[$key]);
				} else {
					$doctypes[$key] = trim($doctype);
				}
			}
		}
		if (!count ($doctypes)) {
			return array ();
		}
		$doctypes = array_unique ($doctypes);
		$doctype_data = self::$persist->getDoctypeList ();
		$found = array ();
		foreach ( $doctypes as $doctype ) {
			if (!is_string ($doctype)) {
				continue;
			}
			if (isset($doctype_data[$doctype])) {
				$doctype = $doctype_data[$doctype];
			} else {
				$doctype = self::$persist->getDocTypeIdByShortName($doctype);
			}
			if (!$doctype || !$doctype['persist_class'] || !$doctype['handler_class']) {
				continue;
			}
			$docTypeClass = 'module_doc_' . $doctype_data [$doctype] ['handler_class'];
			$docClass = new $docTypeClass (getPersistClass ($doctype_data [$doctype] ['handler_class']), $doctype);
			$found [$doctype] = $docClass->findDoc ($localeId, $startPath, $text, $limit, $offset, $includeFolders, $excludeFolders, $inactive);
		}
		return $found;
	}
}
?>