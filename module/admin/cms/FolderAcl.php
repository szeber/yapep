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
class module_admin_cms_FolderAcl extends sys_admin_AdminModule {
    
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

        $control = new sys_admin_control_SelectInput();
        $control->addOptions($this->handler->getObjectList());
        $control->setValue($this->id, true);
        $control->setDisplayOn(sys_admin_interface_Input::DISPLAY_ADD);
        $control->setLabel(_('Users / groups'));
        $this->addControl($control, 'admin_object_id');

        $control = new sys_admin_control_Label();
        $control->setLabel(_('Folder ID'));
        $control->setDefaultValue($this->subModule[0]);
        $control->setReadOnly(true);
        $this->addControl($control, 'folder_id');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Can create documents'));
        $this->addControl($control, 'can_create');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Can delete documents'));
        $this->addControl($control, 'can_delete');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Can list contents'));
        $this->addControl($control, 'can_list');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Can edit folder properties'));
        $this->addControl($control, 'can_edit_props');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Can read documents'));
        $this->addControl($control, 'doc_can_read');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Can write documents'));
        $this->addControl($control, 'doc_can_write');

    }

    protected function processSaveData() {
        if ($this->mode == self::MODE_EDIT) {
            unset($this->data['admin_object_id']);
        }
    }

    protected function doInsert() {
        return $this->handler->insertAclObject(module_db_interface_AdminGroup::MODE_FOLDER, $this->data);
    }

    protected function doUpdate() {
        return $this->handler->updateAclObject(module_db_interface_AdminGroup::MODE_FOLDER, $this->id, $this->data);
    }

    protected function doLoad() {
        return $this->handler->loadAclObject(module_db_interface_AdminGroup::MODE_FOLDER, $this->id);
    }

    protected function doDelete() {
        return $this->handler->deleteAclObject(module_db_interface_AdminGroup::MODE_FOLDER, $this->id);
    }
}
?>