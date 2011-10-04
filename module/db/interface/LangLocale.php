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
 * Language and locale db access interface
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface module_db_interface_LangLocale {

	/**
	 * Returns the language data for a given language id
	 *
	 * @param string $langCode
	 * @return array
	 */
	public function getLanguageByCode($langCode);

	/**
	 * Returns the locale data for a given locale id
	 *
	 * @param string $localeCode
	 * @return array
	 */
	public function getLocaleByCode($localeCode);

	/**
	 * Returns the admin locale data for a given locale id
	 *
	 * @param integer $localeId
	 * @return array
	 */
	public function getAdminLocaleById($localeId);

	/**
	 * Returns full list of locales
	 *
	 * @return array
	 */
	public function getLocales();

	/**
	 * Returns full list of languages with their associated locales
	 *
	 * @return array
	 */
	public function getLanguages();

	/**
	 * Deletes a language item
	 *
	 * @see module_db_interface_Admin::deleteItem()
	 *
	 * @param integer $itemId
	 */
	public function deleteLangItem($itemId);

	/**
	 * Deletes an admin locale item
	 *
	 * @see module_db_interface_Admin::deleteItem()
	 *
	 * @param integer $itemId
	 */
	public function deleteAdminItem($itemId);

	/**
	 * Inserts a language item
	 *
	 * @see module_db_interface_Admin::insertItem()
	 *
	 * @param array $itemData
	 * @return string
	 */
	public function insertLangItem($itemData);

	/**
	 * Inserts an admin locale item
	 *
	 * @see module_db_interface_Admin::insertItem()
	 *
	 * @param array $itemData
	 * @return string
	 */
	public function insertAdminItem($itemData);

	/**
	 * Loads a language item
	 *
	 * @see module_db_interface_Admin::loadItem()
	 *
	 * @param integer $itemId
	 * @return array
	 */
	public function loadLangItem($itemId);

	/**
	 * Loads an admin locale item
	 *
	 * @see module_db_interface_Admin::loadItem()
	 *
	 * @param integer $itemId
	 * @return array
	 */
	public function loadAdminItem($itemId);

	/**
	 * Updates a language item
	 *
	 * @see module_db_interface_Admin::updateItem()
	 *
	 * @param integer $itemId
	 * @param array $itemData
	 * @return string
	 */
	public function updateLangItem($itemId, $itemData);

	/**
	 * Updates an admin locale item
	 *
	 * @see module_db_interface_Admin::updateItem()
	 *
	 * @param integer $itemId
	 * @param array $itemData
	 * @return string
	 */
	public function updateAdminItem($itemId, $itemData);

	/**
	 * Returns the list of locales (array with id=>name format)
	 *
	 * @return array
	 */
	public function getLocaleList();

	/**
	 * Returns the list of languages (array with id=>name format)
	 *
	 * @return array
	 */
	public function getLangList();

	/**
	 * Returns the list of admin locales (array with id=>name format)
	 *
	 * @return array
	 */
	public function getAdminLocaleList();

	/**
	 * Returns full list of admin locales
	 *
	 * @return array
	 */
	public function getAdminLocales();

}
?>