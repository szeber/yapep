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
 * Theme Doctrine database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_Doctrine_Theme extends module_db_DoctrineDbModule implements module_db_interface_Theme, module_db_interface_Admin  {

	/**
	 * @see module_db_interface_Admin::deleteItem()
	 *
	 * @param integer $itemId
	 */
	public function deleteItem($itemId) {
		return $this->basicDelete('CmsThemeData', $itemId);
	}

	/**
	 * @see module_db_interface_Admin::insertItem()
	 *
	 * @param array $itemData
	 * @return string
	 */
	public function insertItem($itemData) {
		return $this->basicInsert('CmsThemeData', $itemData);
	}

	/**
	 * @see module_db_interface_Admin::loadItem()
	 *
	 * @param integer $itemId
	 * @return array
	 */
	public function loadItem($itemId) {
		return $this->conn->queryOne('FROM CmsThemeData WHERE id = ?', array((int)$itemId));
	}

	/**
	 * @see module_db_interface_Admin::updateItem()
	 *
	 * @param integer $itemId
	 * @param array $itemData
	 * @return string
	 */
	public function updateItem($itemId, $itemData) {
		return $this->basicUpdate('CmsThemeData', $itemId, $itemData);
	}

	/**
	 * Returns the list of themes (array with id=>name format)
	 *
	 * @return array
	 */
	public function getThemeList() {
		return $this->getBasicIdSelectList('CmsThemeData');
	}

	/**
	 * Returns the default theme ID
	 *
	 * @return integer
	 */
	public function getDefaultTheme() {
		// TODO Implement default theme setting
		$themeData = $this->conn->queryOne ('FROM CmsThemeData ORDER BY id ASC LIMIT 1');
		return $themeData ['id'];
	}

	/**
	 * Returns true if the provided theme id exists
	 *
	 * @param integer $themeId
	 * @return boolean
	 */
	public function checkThemeExists($themeId) {
		if ($this->conn->queryOne ('SELECT id FROM CmsThemeData WHERE id = ?', array ($themeId))) {
			return true;
		}
		return false;
	}
}
?>