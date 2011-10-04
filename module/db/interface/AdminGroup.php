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
interface module_db_interface_AdminGroup {

    const MODE_DOC = 1;
    const MODE_FOLDER = 2;

    public function getUsersForGroup($groupId);
    public function getGroupsForUser($userId);
    public function deleteUserGroupRel($id);
    public function addUserGroupRel($userId, $groupId);
    public function changeUserGroupRel($id, $userId, $groupId);
    public function listGroupUsers($groupId);
    public function listUserGroups($userId);
    public function loadUserGroupRel($id);
    public function getObjectList();
    public function insertAclObject($mode, $itemData);
    public function updateAclObject($mode, $itemId, $itemData);
    public function loadAclObject($mode, $itemId);
    public function deleteAclObject($mode, $itemId);
    public function loadObjectAcls($mode, $itemId);
    public function loadObjectAdminAcls($mode, $objectId, array $adminIds);
    public function loadObjectFullAcls($mode, $itemId);
}
?>