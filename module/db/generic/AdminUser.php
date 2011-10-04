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
 * Admin user generic database access module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_generic_AdminUser extends module_db_DbModule implements module_db_interface_AdminUser,
    module_db_interface_Admin
{

    /**
     * @see module_db_interface_Admin::deleteItem()
     *
     * @param integer $itemId
     */
    public function deleteItem ($itemId)
    {
        return $this->basicDelete('admin_user_data', $itemId);
    }

    /**
     * @see module_db_interface_Admin::insertItem()
     *
     * @param array $itemData
     * @return string
     */
    public function insertItem ($itemData)
    {
        return $this->basicInsert('admin_user_data', $itemData, array(),
            $this->getObjectTypeIdByShortName('adminuser'));
    }

    /**
     * @see module_db_interface_Admin::loadItem()
     *
     * @param integer $itemId
     * @return array
     */
    public function loadItem ($itemId)
    {
        return $this->basicLoad('admin_user_data', $itemId);
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
        $queryArr = array('table'=>'admin_user_data', 'where'=>'id='.(int)$itemId);
        $data = $this->conn->selectFirst($queryArr);
        $ignoreFields = array('password');
        if (isset($itemData['password']) && ! is_null(
            $itemData['password']) && '' !== $itemData['password']) {
            $data['password'] = $itemData['password'];
        }
        $this->modifyData($data, $itemData, $ignoreFields);
        if ($this->conn->update($queryArr, $data)) {
            return '';
        }
        return $this->conn->getLastError();
    }

    /**
     *
     * @param string $userName
     * @return array
     * @see module_db_UserAuth::getUserByUserName()
     */
    public function getUserByUserName ($userName)
    {
        $data = $this->conn->selectFirst(array('table'=>'admin_user_data', 'where'=>'username='.$this->conn->quote($userName)));
        $data['Locale'] = $this->conn->selectFirst(array('table'=>'locale_data', 'where id='.$data['locale_id']));
        return $data;
    }

    /**
     * Returns the list of admin users (array with id=>name format)
     *
     * @return array
     */
    public function getUserList ()
    {
        return $this->getBasicIdSelectList('admin_user_data');
    }
}
?>