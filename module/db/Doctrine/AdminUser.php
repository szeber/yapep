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
 * Admin user Doctrine database access module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_Doctrine_AdminUser extends module_db_DoctrineDbModule implements module_db_interface_AdminUser, module_db_interface_Admin {

	/**
	 * @see module_db_interface_Admin::deleteItem()
	 *
	 * @param integer $itemId
	 */
	public function deleteItem($itemId) {
		return $this->basicDelete('AdminUserData', $itemId);
	}

	/**
	 * @see module_db_interface_Admin::insertItem()
	 *
	 * @param array $itemData
	 * @return string
	 */
	public function insertItem($itemData) {
		return $this->basicInsert('AdminUserData', $itemData, array(), $this->getObjectTypeIdByShortName('adminuser'));
	}

	/**
	 * @see module_db_interface_Admin::loadItem()
	 *
	 * @param integer $itemId
	 * @return array
	 */
	public function loadItem($itemId) {
		return $this->conn->queryOne ( 'FROM AdminUserData WHERE id = ?', array (( int ) $itemId ) );
	}

	/**
	 * @see module_db_interface_Admin::updateItem()
	 *
	 * @param integer $itemId
	 * @param array $itemData
	 * @return string
	 */
	public function updateItem($itemId, $itemData) {
		try {
			$ignoreFields = array ('password' );
			$data = $this->conn->queryOne ( 'FROM AdminUserData WHERE id = ?', array (( int ) $itemId ) );
			if (isset ( $itemData ['password'] ) && ! is_null ( $itemData ['password'] ) && '' !== $itemData ['password']) {
				$data->password = $itemData ['password'];
			}
			$this->modifyData ( $data, $itemData, $ignoreFields );
			$data->save ();
			return '';
		} catch ( Doctrine_Exception $e ) {
			return $e->getMessage ();
		}
	}
	/**
	 *
	 * @param string $userName
	 * @return array
	 * @see module_db_UserAuth::getUserByUserName()
	 */
	public function getUserByUserName($userName) {
		$data = $this->conn->queryOne ( 'FROM AdminUserData u LEFT JOIN u.Locale l WHERE u.username = ?', array ($userName) );
		if (!is_object($data)) {
			return $data;
		}
		return $data->toArray();
	}

	/**
	 * Returns the list of admin users (array with id=>name format)
	 *
	 * @return array
	 */
	public function getUserList() {
		return $this->getBasicIdSelectList('AdminUserData');
	}
}
?>