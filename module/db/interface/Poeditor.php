<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Poeditor generic database interface
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface module_db_interface_Poeditor {

	const TARGET_BOTH = 0;
	const TARGET_ADMIN = 1;
	const TARGET_SITE = 2;

	/**
	 * Returns the list of texts found in the database that should be translated
	 *
	 * It should return all name fields from the admin_func_data and object_type_data tables
	 *
	 * @return array
	 */
	public function scanDbTexts();

	/**
	 * Returns all texts from the database
	 *
	 * @return array
	 */
	public function loadTexts();

	/**
	 * Adds a new text to the database with the given type and returns it's ID
	 *
	 * @param string $text
	 * @param integer $type
	 * @return integer
	 */
	public function addText($text, $type);

	/**
	 * Deletes the texts from the database with their translations for the given IDs
	 *
	 * @param array $obsoleteIds
	 */
	public function deleteObsoleteTexts(array $obsoleteIds);

	/**
	 * Returns the number of translations matching the filters
	 *
	 * @param integer $type
	 * @param string $localeCode
	 * @param array $filter
	 * @return integer
	 */
	public function getListCount($type, $localeCode, $filter = null);

	/**
	 * Returns the translations matching the filters
	 *
	 * @param integer $type
	 * @param string $localeCode
	 * @param integer $limit
	 * @param integer $offset
	 * @param array $filter
	 * @param string $orderBy
	 * @param string $orderDir
	 * 	 * @return array
	 */
	public function loadList($type, $localeCode, $limit = null, $offset = null, $filter = null, $orderBy = null, $orderDir = null);

	/**
	 * Updates or inserts a translation
	 *
	 * @param integer $textId
	 * @param string $localeCode
	 * @param string $translation
	 * @param boolean $fuzzy
	 */
	public function saveTranslation($textId, $localeCode, $translation, $fuzzy);

	/**
	 * Loads a translation
	 *
	 * @param integer $textId
	 * @param string $localeCode
	 * @return array
	 */
	public function loadTranslation($textId, $localeCode);

	/**
	 * Deletes a translation
	 *
	 * @param integer $textId
	 * @param string $localeCode
	 */
	public function deleteTranslation($textId, $localeCode);

	/**
	 * Returns all texts that have translations
	 *
	 * @param integer $target
	 * @param string $localeCode
	 * @return array
	 */
	public function getTranslations($target, $localeCode);

}
?>