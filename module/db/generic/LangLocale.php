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
 * Language and locale generic db access module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_generic_LangLocale extends module_db_DbModule implements module_db_interface_LangLocale, module_db_interface_Admin
{

    /**
     * @see module_db_interface_Admin::deleteItem()
     *
     * @param integer $itemId
     */
    public function deleteItem ($itemId)
    {
        return $this->basicDelete('locale_data', $itemId);
    }

    /**
     * Deletes a language item
     *
     * @see module_db_interface_Admin::deleteItem()
     *
     * @param integer $itemId
     */
    public function deleteLangItem ($itemId)
    {
        return $this->basicDelete('language_data', $itemId);
    }

    /**
     * Deletes an admin locale item
     *
     * @see module_db_interface_Admin::deleteItem()
     *
     * @param integer $itemId
     */
    public function deleteAdminItem ($itemId)
    {
        return $this->basicDelete('admin_locale_data', $itemId);
    }

    /**
     * @see module_db_interface_Admin::insertItem()
     *
     * @param array $itemData
     * @return string
     */
    public function insertItem ($itemData)
    {
        return $this->basicInsert('locale_data', $itemData);
    }

    /**
     * Inserts a language item
     *
     * @see module_db_interface_Admin::insertItem()
     *
     * @param array $itemData
     * @return string
     */
    public function insertLangItem ($itemData)
    {
        return $this->basicInsert('language_data', $itemData);
    }

    /**
     * Inserts an admin locale item
     *
     * @see module_db_interface_Admin::insertItem()
     *
     * @param array $itemData
     * @return string
     */
    public function insertAdminItem ($itemData)
    {
        return $this->basicInsert('admin_locale_data', $itemData);
    }

    /**
     * @see module_db_interface_Admin::loadItem()
     *
     * @param integer $itemId
     * @return array
     */
    public function loadItem ($itemId)
    {
        return $this->basicLoad('locale_data', $itemId);
    }

    /**
     * Loads a language item
     *
     * @see module_db_interface_Admin::loadItem()
     *
     * @param integer $itemId
     * @return array
     */
    public function loadLangItem ($itemId)
    {
        return $this->basicLoad('language_data', $itemId);
    }

    /**
     * Loads an admin locale item
     *
     * @see module_db_interface_Admin::loadItem()
     *
     * @param integer $itemId
     * @return array
     */
    public function loadAdminItem ($itemId)
    {
        return $this->basicLoad('admin_locale_data', $itemId);
    }

    /**
     * @see module_db_interface_Admin::updateItem()
     *
     * @param integer $itemId
     * @param array $itemData
     * @return string
     */
    public function updateItem ($itemId, $itemData)
    {
        return $this->basicUpdate('locale_data', $itemId, $itemData);
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
    public function updateLangItem ($itemId, $itemData)
    {
        return $this->basicUpdate('language_data', $itemId, $itemData);
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
    public function updateAdminItem ($itemId, $itemData)
    {
        return $this->basicUpdate('admin_locale_data', $itemId, $itemData);
    }

    /**
     * Returns the list of locales (array with id=>name format)
     *
     * @return array
     */
    public function getLocaleList ()
    {
        return $this->getBasicIdSelectList('locale_data');
    }

    /**
     * Returns the list of languages (array with id=>name format)
     *
     * @return array
     */
    public function getLangList ()
    {
        return $this->getBasicIdSelectList('language_data');
    }

    /**
     * Returns the list of admin locales (array with id=>name format)
     *
     * @return array
     */
    public function getAdminLocaleList ()
    {
        return $this->getBasicIdSelectList('admin_locale_data');
    }

    /**
     * Returns the language data for a given language id
     *
     * @param string $langCode
     * @return array
     */
    public function getLanguageByCode ($langCode)
    {
        $langData = $this->conn->selectFirst(array('table' => 'language_data' ,
            'where' => 'language_code=' . $this->conn->quote($langCode)));
        if (count($langData)) {
            $langData['Locale'] = $this->conn->selectFirst(array('table' => 'locale_data' ,
                'where' => 'id=' . $langData['locale_id']));
        }
        return $langData;
    }

    /**
     * Returns the locale data for a given locale id
     *
     * @param string $localeCode
     * @return array
     */
    public function getLocaleByCode ($localeCode)
    {
        return $this->conn->selectFirst(array('table' => 'locale_data' ,
            'where' => 'locale_code=' . $this->conn->quote($localeCode)));
    }

    /**
     * Returns the list of locales defined
     *
     * @return array
     */
    public function getLocales ()
    {
        return $this->conn->select(array('table' => 'locale_data' ,
            'orderBy' => 'name ASC'));
    }

    /**
     * @see module_db_interface_LangLocale::getAdminLocaleById()
     *
     * @param integer $localeId
     * @return array
     */
    public function getAdminLocaleById ($localeId)
    {
        return $this->conn->selectFirst(array('table' => 'admin_locale_data' ,
            'where' => 'id=' . (int) $localeId));
    }

    /**
     * Returns the list of languages defined
     *
     * @return array
     */
    public function getLanguages ()
    {
        $langData = $this->conn->selectFirst(array('table' => 'language_data' ,
            'orderBy' => 'name ASC'));
        $langData['Locale'] = $this->conn->selectFirst(array('table' => 'locale_data' ,
            'where' => 'id=' . $langData['locale_id']));
        return $langData;
    }

    public function getAdminLocales ()
    {
        return $this->conn->select(array('table' => 'admin_locale_data' ,
            'orderBy' => 'name ASC'));
    }
}
?>