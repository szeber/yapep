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
 * Language and locale Doctrine db access module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_Doctrine_LangLocale extends module_db_DoctrineDbModule implements module_db_interface_LangLocale, module_db_interface_Admin  {

	/**
	 * @see module_db_interface_Admin::deleteItem()
	 *
	 * @param integer $itemId
	 */
	public function deleteItem($itemId) {
		return $this->basicDelete('LocaleData', $itemId);
	}

	/**
	 * Deletes a language item
	 *
	 * @see module_db_interface_Admin::deleteItem()
	 *
	 * @param integer $itemId
	 */
	public function deleteLangItem($itemId) {
		return $this->basicDelete('LanguageData', $itemId);
	}

	/**
	 * Deletes an admin locale item
	 *
	 * @see module_db_interface_Admin::deleteItem()
	 *
	 * @param integer $itemId
	 */
	public function deleteAdminItem($itemId) {
		return $this->basicDelete('AdminLocaleData', $itemId);
	}

	/**
	 * @see module_db_interface_Admin::insertItem()
	 *
	 * @param array $itemData
	 * @return string
	 */
	public function insertItem($itemData) {
		return $this->basicInsert('LocaleData', $itemData);
	}

	/**
	 * Inserts a language item
	 *
	 * @see module_db_interface_Admin::insertItem()
	 *
	 * @param array $itemData
	 * @return string
	 */
	public function insertLangItem($itemData) {
		return $this->basicInsert('LanguageData', $itemData);
	}

	/**
	 * Inserts an admin locale item
	 *
	 * @see module_db_interface_Admin::insertItem()
	 *
	 * @param array $itemData
	 * @return string
	 */
	public function insertAdminItem($itemData) {
		return $this->basicInsert('AdminLocaleData', $itemData);
	}

	/**
	 * @see module_db_interface_Admin::loadItem()
	 *
	 * @param integer $itemId
	 * @return array
	 */
	public function loadItem($itemId) {
		return $this->conn->queryOne('FROM LocaleData WHERE id = ?', array((int)$itemId));
	}

	/**
	 * Loads a language item
	 *
	 * @see module_db_interface_Admin::loadItem()
	 *
	 * @param integer $itemId
	 * @return array
	 */
	public function loadLangItem($itemId) {
		return $this->conn->queryOne('FROM LanguageData WHERE id = ?', array((int)$itemId));
	}

	/**
	 * Loads an admin locale item
	 *
	 * @see module_db_interface_Admin::loadItem()
	 *
	 * @param integer $itemId
	 * @return array
	 */
	public function loadAdminItem($itemId) {
		return $this->conn->queryOne('FROM AdminLocaleData WHERE id = ?', array((int)$itemId));
	}

	/**
	 * @see module_db_interface_Admin::updateItem()
	 *
	 * @param integer $itemId
	 * @param array $itemData
	 * @return string
	 */
	public function updateItem($itemId, $itemData) {
		return $this->basicUpdate('LocaleData', $itemId, $itemData);
	}

	/**
	 * Updates a language item
	 *
	 * @see module_db_interface_Admin::updateItem()
	 *
	 * @param integer $itemId
	 * @param array $itemData
	 * @return string
	 */
	public function updateLangItem($itemId, $itemData) {
		return $this->basicUpdate('LanguageData', $itemId, $itemData);
	}

	/**
	 * Updates an admin locale item
	 *
	 * @see module_db_interface_Admin::updateItem()
	 *
	 * @param integer $itemId
	 * @param array $itemData
	 * @return string
	 */
	public function updateAdminItem($itemId, $itemData) {
		return $this->basicUpdate('AdminLocaleData', $itemId, $itemData);
	}

	/**
	 * Returns the list of locales (array with id=>name format)
	 *
	 * @return array
	 */
	public function getLocaleList() {
		return $this->getBasicIdSelectList('LocaleData');
	}

	/**
	 * Returns the list of languages (array with id=>name format)
	 *
	 * @return array
	 */
	public function getLangList() {
		return $this->getBasicIdSelectList('LanguageData');
	}

	/**
	 * Returns the list of admin locales (array with id=>name format)
	 *
	 * @return array
	 */
	public function getAdminLocaleList() {
		return $this->getBasicIdSelectList('AdminLocaleData');
	}

/**
	 * Returns the language data for a given language id
	 *
	 * @param string $langCode
	 * @return array
	 */
	public function getLanguageByCode($langCode) {
		return $this->conn->queryOne ('FROM LanguageData la INNER JOIN la.Locale lo WHERE language_code=?', array ($langCode));
	}

	/**
	 * Returns the locale data for a given locale id
	 *
	 * @param string $localeCode
	 * @return array
	 */
	public function getLocaleByCode($localeCode) {
		return $this->conn->queryOne ('FROM LocaleData WHERE locale_code=?', array ($localeCode));
	}

	/**
	 * Returns the list of locales defined
	 *
	 * @return array
	 */
	public function getLocales() {
		return $this->conn->query ('FROM LocaleData ORDER BY name ASC');
	}

	/**
	 * @see module_db_interface_LangLocale::getAdminLocaleById()
	 *
	 * @param integer $localeId
	 * @return array
	 */
	public function getAdminLocaleById($localeId) {
		return $this->conn->queryOne ('FROM AdminLocaleData WHERE id = ?', array ((int)$localeId));
	}

	/**
	 * Returns the list of languages defined
	 *
	 * @return array
	 */
	public function getLanguages() {
		return $this->conn->query ('FROM LanguageData la INNER JOIN la.Locale lo ORDER BY la.name ASC');
	}

	public function getAdminLocales() {
		return $this->conn->query ('FROM AdminLocaleData ORDER BY name ASC');
	}
}
?>