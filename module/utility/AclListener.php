<?php

class module_utility_AclListener extends sys_admin_Listener {

    protected $currentExecuteEvent;

    protected $module;

    protected $userId;

    protected $mode;

    protected $objId;

    protected $subModule;

    public function preExecute(array $event, sys_admin_AdminManager $manager) {
        $this->currentExecuteEvent = $event;
        $this->module = $event['module'];
        $this->userId = $event['userId'];
        $this->objId = $event['id'];
        $this->mode = $event['mode'];
        $this->subModule = $event['subModule'];
        if ($this->module == 'module_admin_List' && $this->subModule[0] == 'Folder') {
            $this->checkCanList($this->userId, $this->subModule[1]);
        }
        if ($this->mode == sys_admin_AdminModule::MODE_ADD) {
            switch ($this->currentExecuteEvent['module']) {
                case 'module_admin_Doc':
                    $this->checkCanCreate($this->userId, $this->subModule[1]);
                    break;
                case 'module_admin_folder_Prop':
                    $this->checkCanEditFolderProps($this->userId, $this->subModule[0]);
                    $this->checkCanCreate($this->userId, $this->subModule[0]);
                    break;
            }
        }
    }

    public function preLoad(array $event, sys_admin_AdminManager $manager) {
        parent::preLoad($event, $manager);
        switch ($this->currentExecuteEvent['module']) {
            case 'module_admin_Doc':
                if ($this->mode != sys_admin_AdminModule::MODE_ADD) {
                    $this->checkCanReadDoc($this->userId, $this->objId);
                }
                break;
            case 'module_admin_folder_Prop':
                if ($this->mode != sys_admin_AdminModule::MODE_ADD) {
                    $this->checkCanEditFolderProps($this->userId, $this->objId);
                }
                break;
        }
    }

    public function preSave(array $event, sys_admin_AdminManager $manager) {
        parent::preSave($event, $manager);
        switch ($this->module) {
            case 'module_admin_Doc':
                if ($this->mode == sys_admin_AdminModule::MODE_EDIT) {
                    $this->checkCanWriteDoc($this->userId, $this->objId);
                }
                break;
            case 'module_admin_folder_Prop':
                if ($this->mode == sys_admin_AdminModule::MODE_EDIT) {
                    $this->checkCanEditFolderProps($this->userId, $this->objId);
                }
                break;
        }
    }

    public function preDelete(array $event, sys_admin_AdminManager $manager) {
        parent::preDelete($event, $manager);
                switch ($this->module) {
            case 'module_admin_Doc':
                $this->checkCanDeleteDoc($this->userId, $this->objId);
                break;
            case 'module_admin_folder_Prop':
                $this->checkCanDeleteFolder($this->userId, $this->objId);
                break;
        }
    }

    protected function checkCanReadDoc($userId, $objId) {
        $this->checkDocPermission($userId, $objId, 'doc_def_read', 'can_read', 'doc_can_read');
    }

    protected function checkCanWriteDoc($userId, $objId) {
        $this->checkDocPermission($userId, $objId, 'doc_def_write', 'can_write', 'doc_can_write');
    }

    protected function checkCanEditFolderProps($userId, $objId) {
        $this->checkFolderPermission($userId, $objId, 'folder_def_edit_props', 'can_edit_props');
    }

    protected function checkCanCreate($userId, $objId) {
        $this->checkFolderPermission($userId, $objId, 'folder_def_create', 'can_create');
    }

    protected function checkCanList($userId, $objId) {
        $this->checkFolderPermission($userId, $objId, 'folder_def_list', 'can_list');
    }

    protected function checkCanDeleteDoc($userId, $objId) {
        $genericDb = getPersistClass('GenericAdmin');
        $genericDb->setObjType('document');
        $doc = $genericDb->loadItem($objId);
        if (!$doc['id']) {
            $this->throwException();
        }
        $this->checkFolderPermission($userId, $doc['folder_id'], 'folder_def_delete', 'can_delete');
    }

    protected function checkCanDeleteFolder($userId, $objId) {
        $this->checkCanEditFolderProps($userId, $objId);
        // check if user exists
        $genericDb = getPersistClass('GenericAdmin');
        $genericDb->setObjType('adminuser');
        $user = $genericDb->loadItem($userId);
        if (!$user['id']) {
            $this->throwException();
        }
        // check if the user is a superuser
        if ($user['superuser']) {
            return;
        }
        // check if the folder exists, and if it's not in the root folder.
        // direct descendants of the root folder can only be deleted by superusers
        $genericDb->setObjType('folder');
        $folder = $genericDb->loadItem($objId);
        if (!$folder['id'] || !$folder['parent_id']) {
            $this->throwException();
        }
        // check group has permission to all folders
        $groupDb = getPersistClass('AdminGroup');
        $groups = $groupDb->getGroupsForUser($userId);
        $groupIds = array();
        foreach($groups as $group) {
            if ($group['folder_def_delete']) {
                return;
            }
            $groupIds[] = $group['id'];
        }
        // check rights for the folder
        $acls = $groupDb->loadObjectAdminAcls(module_db_interface_AdminGroup::MODE_FOLDER, $folder['parent_id'], array_merge(array($userId), $groupIds));
        foreach($acls as $acl) {
            if ($acl['can_delete']) {
                return;
            }
        }
        // throw exception
        $this->throwException();
    }

    protected function checkDocPermission($userId, $objId, $defField, $docField, $folderField) {
        // check if user exists
        $genericDb = getPersistClass('GenericAdmin');
        $genericDb->setObjType('adminuser');
        $user = $genericDb->loadItem($userId);
        if (!$user['id']) {
            $this->throwException();
        }
        // check if the user is a superuser
        if ($user['superuser']) {
            return;
        }
        // check group has permission to all docs
        $groupDb = getPersistClass('AdminGroup');
        $groups = $groupDb->getGroupsForUser($userId);
        $groupIds = array();
        foreach($groups as $group) {
            if ($group[$defField]) {
                return;
            }
            $groupIds[] = $group['id'];
        }
        // check rights for the doc
        $genericDb->setObjType('document');
        $doc = $genericDb->loadItem($objId);
        if (!$doc['id']) {
            $this->throwException();
        }
        $acls = $groupDb->loadObjectAdminAcls(module_db_interface_AdminGroup::MODE_DOC, $doc['id'], array_merge(array($userId), $groupIds));
        foreach($acls as $acl) {
            if ($acl[$docField]) {
                return;
            }
        }
        // check rights for the containing folder
        $acls = $groupDb->loadObjectAdminAcls(module_db_interface_AdminGroup::MODE_FOLDER, $doc['folder_id'], array_merge(array($userId), $groupIds));
        foreach($acls as $acl) {
            if ($acl[$folderField]) {
                return;
            }
        }
        // throw exception
        $this->throwException();

    }

    protected function checkFolderPermission($userId, $objId, $defField, $folderField) {
        // check if user exists
        $genericDb = getPersistClass('GenericAdmin');
        $genericDb->setObjType('adminuser');
        $user = $genericDb->loadItem($userId);
        if (!$user['id']) {
            $this->throwException();
        }
        // check if the user is a superuser
        if ($user['superuser']) {
            return;
        }
        // check group has permission to all docs
        $groupDb = getPersistClass('AdminGroup');
        $groups = $groupDb->getGroupsForUser($userId);
        $groupIds = array();
        foreach($groups as $group) {
            if ($group[$defField]) {
                return;
            }
            $groupIds[] = $group['id'];
        }
        // check rights for the doc
        $genericDb->setObjType('folder');
        $folder = $genericDb->loadItem($objId);
        if (!$folder['id']) {
            $this->throwException();
        }
        $acls = $groupDb->loadObjectAdminAcls(module_db_interface_AdminGroup::MODE_FOLDER, $folder['id'], array_merge(array($userId), $groupIds));
        foreach($acls as $acl) {
            if ($acl[$folderField]) {
                return;
            }
        }
        // throw exception
        $this->throwException();
    }

    protected function throwException($message=null, $code=null) {
        if (!$message) {
            $message = _("Insufficient rights to perform this operation");
        }
        if (!(int)$code) {
            $code = sys_exception_AdminException::ERR_NOT_AUTHORIZED;
        }
        throw new sys_exception_AdminException($message, $code);
    }

}