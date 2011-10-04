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
class module_admin_cms_AdminUserGroup extends sys_admin_AdminModule {
    
    /**
     *
     * @var module_db_interface_AdminGroup
     */
    protected $handler;

	/**
	 * @see sys_admin_AdminModule::buildForm()
	 *
	 */
	protected function buildForm() {
        $this->requireSuperuser();

        $this->handler = getPersistClass('AdminGroup');
        $genericHandler = getPersistClass('GenericAdmin');

        if ($this->subModule[0] == 'g') {
            $control = new sys_admin_control_IdSelect();
            $control->addOptions($this->handler->listGroupUsers($this->subModule[1]));
            $control->setValue($this->id, true);
            $this->addControl($control, 'idSelect');
            
            $control = new sys_admin_control_Label();
            $control->setLabel(_('Admin group ID'));
            $control->setDefaultValue($this->subModule[1]);
            $control->setReadOnly(true);
            $this->addControl($control, 'admin_group_id');

            $control = new sys_admin_control_SelectInput();
            $control->setLabel(_('Admin user'));
            $control->setRequired();
            $control->addOptions($genericHandler->getList('adminuser'));
            $this->addControl($control, 'admin_user_id');
        } else {
            $control = new sys_admin_control_IdSelect();
            $control->addOptions($this->handler->listUserGroups($this->subModule[1]));
            $control->setValue($this->id, true);
            $this->addControl($control, 'idSelect');

            $control = new sys_admin_control_Label();
            $control->setLabel(_('Admin user ID'));
            $control->setDefaultValue($this->subModule[1]);
            $control->setReadOnly(true);
            $this->addControl($control, 'admin_user_id');

            $control = new sys_admin_control_SelectInput();
            $control->setLabel(_('Admin group'));
            $control->setRequired();
            $control->addOptions($genericHandler->getList('admin_group'));
            $this->addControl($control, 'admin_group_id');
            
        }

    }

    protected function processLoadData() {
        $this->handler->loadUserGroupRel($this->id);
    }

    protected function doInsert() {
        return $this->handler->addUserGroupRel($this->data['admin_user_id'], $this->data['admin_group_id']);
    }

    protected function doUpdate() {
        return $this->handler->changeUserGroupRel($this->id, $this->data['admin_user_id'], $this->data['admin_group_id']);
    }

    protected function doLoad() {
        return $this->handler->loadUserGroupRel($this->id);
    }

    protected function doDelete() {
        return $this->handler->deleteUserGroupRel($this->id);
    }
}
?>