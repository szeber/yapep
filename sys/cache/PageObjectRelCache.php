<?php

/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	Cache
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Page-object relation cache
 *
 * @package	YAPEP
 * @subpackage	Cache
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_cache_PageObjectRelCache  {

	/**
	 * Cached data
	 *
	 * @var array
	 */
	private static $objectData = null;

	private static $defaultData = null;

	/**
	 *
	 * @var sys_db_Database
	 */
	private $db;

	/**
	 *
	 * @var sys_IApplicationConfiguration
	 */
	private $config;

	public function __construct(sys_IApplicationConfiguration $config = null) {
		if (is_null($config)) {
			$this->config = sys_ApplicationConfiguration::getInstance();
		} else {
			$this->config=$config;
		}
		$this->db = sys_LibFactory::getDbConnection('site');
		if (is_null(self::$objectData)) {
			$this->queryRels();
		}
	}

	/**
	 * Queries the database for the relations and saves the data in the cache
	 *
	 */
	private function queryRels() {
		$db = getPersistClass('Page');
		$relData = $db->getObjectRelData();
		self::$objectData = array();
		self::$defaultData = array();
		foreach($relData as $val) {
			if ('default_doc' == $val['object_type'] || 'home' == $val['object_type']) {
				self::$defaultData[$val['object_type']][$val['lang_id']][$val['cms_theme_id']] = $val['cms_page_id'];
			}
			self::$objectData[$val['object_type']][$val['object_id']][$val['cms_theme_id']]=$val['cms_page_id'];
		}
	}

	/**
	 * Returns the assigned pages for the object
	 *
	 * @param string $objectType
	 * @param integer $objectId
	 * @return array
	 */
	public function getPageForObject($objectType, $objectId, $langId) { 'a';
		if (is_null($objectId) && $objectType=='folder') {
			return self::$defaultData['home'][$langId];
		} elseif (isset(self::$objectData[$objectType][$objectId])) {
			return self::$objectData[$objectType][$objectId];
		} elseif ($objectType=='folder' && is_null($objectId)) {
			return self::$objectData['mainpage'][''];
		}
		switch($objectType) {
			case 'folderdoc':
				return self::$defaultData['default_doc'][$langId];
				break;
			default:
				return null;
				break;
		}
	}
}
?>