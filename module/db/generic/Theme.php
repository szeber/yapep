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
 * Theme generic database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_generic_Theme extends module_db_DbModule implements module_db_interface_Theme, module_db_interface_Admin
{

    /**
     * @see module_db_interface_Admin::deleteItem()
     *
     * @param integer $itemId
     */
    public function deleteItem ($itemId)
    {
        return $this->basicDelete('cms_theme_data', $itemId);
    }

    /**
     * @see module_db_interface_Admin::insertItem()
     *
     * @param array $itemData
     * @return string
     */
    public function insertItem ($itemData)
    {
        return $this->basicInsert('cms_theme_data', $itemData);
    }

    /**
     * @see module_db_interface_Admin::loadItem()
     *
     * @param integer $itemId
     * @return array
     */
    public function loadItem ($itemId)
    {
        return $this->basicLoad('cms_theme_data', $itemId);
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
        return $this->basicUpdate('cms_theme_data', $itemId, $itemData);
    }

    /**
     * Returns the list of themes (array with id=>name format)
     *
     * @return array
     */
    public function getThemeList ()
    {
        return $this->getBasicIdSelectList('cms_theme_data');
    }

    /**
     * Returns the default theme ID
     *
     * @return integer
     */
    public function getDefaultTheme ()
    {
        // TODO Implement default theme setting
        $themeData = $this->conn->selectFirst(array('table'=>'cms_theme_data', 'orderBy'=>'id'));
        return $themeData['id'];
    }

    /**
     * Returns true if the provided theme id exists
     *
     * @param integer $themeId
     * @return boolean
     */
    public function checkThemeExists ($themeId)
    {
        if (count($this->conn->selectFirst(array('table'=>'cms_theme_data', 'fields'=>'id', 'where'=>'id='.(int)$themeId)))) {
            return true;
        }
        return false;
    }
}
?>