<?php
/**
 *
 *
 * @version $Rev: $
 */

/**
 *
 *
 * @version $Rev: $
 */
class module_db_generic_AdminGroup extends module_db_DbModule implements module_db_interface_AdminGroup {

    public function getUsersForGroup($groupId) {
        return $this->conn->select(
            array(
                'table'=>'admin_user_group_rel r JOIN admin_user_data u ON u.id=r.admin_user_id',
                'fields'=>'u.*',
                'where'=>'r.admin_group_id='.(int)$groupId,
            )
        );
    }

    public function getGroupsForUser($userId) {
        return $this->conn->select(
            array(
                'table'=>'admin_user_group_rel r JOIN admin_group_data g ON g.id=r.admin_group_id',
                'fields'=>'g.*',
                'where'=>'r.admin_user_id='.(int)$userId,
            )
        );

    }

    public function deleteUserGroupRel($id) {
        return $this->conn->delete('admin_user_group_rel', 'id='.(int)$id);
    }

    public function addUserGroupRel($userId, $groupId) {
        return $this->basicInsert('admin_user_group_rel', array('admin_user_id'=>(int)$userId, 'admin_group_id'=>(int)$groupId));
    }

    public function changeUserGroupRel($id, $userId, $groupId) {
        return $this->basicUpdate('admin_user_group_rel', $id, array('admin_user_id'=>$userId, 'admin_group_id'=>$groupId));
    }

    public function listGroupUsers($groupId) {
        return $this->getBasicIdSelectList('admin_user_group_rel r JOIN admin_user_data u ON u.id=r.admin_user_id', 'u.id', 'u.name', 'r.admin_group_id='.(int)$groupId);
    }

    public function listUserGroups($userId) {
        return $this->getBasicIdSelectList('admin_user_group_rel r JOIN admin_group_data g ON g.id=r.admin_group_id', 'g.id', 'g.name', 'r.admin_user_id='.(int)$userId);
    }

    public function loadUserGroupRel($id) {
        return $this->conn->selectFirst(
            array(
                'table'=>'admin_group_rel',
                'where'=>'id='.(int)$id
            )
        );
    }

    public function getObjectList() {
        $groupId = $this->getObjectTypeIdByShortName('admin_group');
        $userId = $this->getObjectTypeIdByShortName('adminuser');
        $query = array(
            'table'=>'object_data',
            'orderBy'=>'name',
            'where'=>'object_type_id IN ('.$groupId.', '.$userId.')',
        );
        $tmp = $this->conn->select($query);
        $data = array();
        foreach($tmp as $key=>$row) {
            if ($row['object_type_id'] == $groupId) {
                $row['name'] .= ' *';
            }
            $data[$row['id']] = $row['name'];
        }
        return $data;
    }

    public function insertAclObject($mode, $itemData) {
        switch ($mode) {
            case self::MODE_DOC:
                $tableName = 'doc_acl_data';
                break;
            case self::MODE_FOLDER:
                $tableName = 'folder_acl_data';
                break;
            default:
                throw new sys_exception_DatabaseException('Invalid mode', sys_exception_DatabaseException::ERR_CUSTOM_ERROR_1);
        }
        return $this->basicInsert($tableName, $itemData);
    }

    public function updateAclObject($mode, $itemId, $itemData) {
        switch ($mode) {
            case self::MODE_DOC:
                $tableName = 'doc_acl_data';
                break;
            case self::MODE_FOLDER:
                $tableName = 'folder_acl_data';
                break;
            default:
                throw new sys_exception_DatabaseException('Invalid mode', sys_exception_DatabaseException::ERR_CUSTOM_ERROR_1);
        }
        return $this->basicUpdate($tableName, $itemId, $itemData, array('admin_object_id'));
    }

    public function deleteAclObject($mode, $itemId) {
        switch ($mode) {
            case self::MODE_DOC:
                $tableName = 'doc_acl_data';
                break;
            case self::MODE_FOLDER:
                $tableName = 'folder_acl_data';
                break;
            default:
                throw new sys_exception_DatabaseException('Invalid mode', sys_exception_DatabaseException::ERR_CUSTOM_ERROR_1);
        }
        return $this->basicDelete($tableName, $itemId);
    }

    public function loadAclObject($mode, $itemId) {
        switch ($mode) {
            case self::MODE_DOC:
                $tableName = 'doc_acl_data';
                break;
            case self::MODE_FOLDER:
                $tableName = 'folder_acl_data';
                break;
            default:
                throw new sys_exception_DatabaseException('Invalid mode', sys_exception_DatabaseException::ERR_CUSTOM_ERROR_1);
        }
        return $this->basicLoad($tableName, $itemId);
    }

    public function loadObjectAcls($mode, $itemId) {
        switch ($mode) {
            case self::MODE_DOC:
                $tableName = 'doc_acl_data';
                $objectField = 'doc_id';
                break;
            case self::MODE_FOLDER:
                $tableName = 'folder_acl_data';
                $objectField = 'folder_id';
                break;
            default:
                throw new sys_exception_DatabaseException('Invalid mode', sys_exception_DatabaseException::ERR_CUSTOM_ERROR_1);
        }
        return $this->conn->select(
            array(
                'table'=>$tableName.' a JOIN object_data o ON o.id=a.admin_object_id',
                'fields'=>'a.id, o.name',
                'where'=>'a.'.$objectField.'='.(int)$itemId,
                'orderBy'=>'o.name ASC'
            )
        );
    }

    public function loadObjectAdminAcls($mode, $objectId, array $adminIds) {
        if (!count($adminIds)) {
            return array();
        }
        foreach($adminIds as $key=>$adminId) {
            $adminIds[$key] = (int)$adminId;
        }
        switch ($mode) {
            case self::MODE_DOC:
                $tableName = 'doc_acl_data';
                $objectField = 'doc_id';
                break;
            case self::MODE_FOLDER:
                $tableName = 'folder_acl_data';
                $objectField = 'folder_id';
                break;
            default:
                throw new sys_exception_DatabaseException('Invalid mode', sys_exception_DatabaseException::ERR_CUSTOM_ERROR_1);
        }
        return $this->conn->select(
            array(
                'table'=>$tableName,
                'fields'=>'*',
                'where'=>$objectField.'='.(int)$objectId.' AND admin_object_id IN ('.implode(', ', $adminIds).')',
            )
        );

    }

    public function loadObjectFullAcls($mode, $itemId) {
        switch ($mode) {
            case self::MODE_DOC:
                $tableName = 'doc_acl_data';
                $objectField = 'doc_id';
                break;
            case self::MODE_FOLDER:
                $tableName = 'folder_acl_data';
                $objectField = 'folder_id';
                break;
            default:
                throw new sys_exception_DatabaseException('Invalid mode', sys_exception_DatabaseException::ERR_CUSTOM_ERROR_1);
        }
        return $this->conn->select(
            array(
                'table'=>$tableName,
                'where'=>$objectField.'='.(int)$itemId,
            )
        );
    }


}
?>