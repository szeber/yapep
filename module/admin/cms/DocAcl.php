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
class module_admin_cms_DocAcl extends sys_admin_AdminModule {
    
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
        $control->setLabel(_('Document ID'));
        $control->setDefaultValue($this->subModule[0]);
        $control->setReadOnly(true);
        $this->addControl($control, 'doc_id');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Can read'));
        $this->addControl($control, 'can_read');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Can write'));
        $this->addControl($control, 'can_write');

    }

    protected function processSaveData() {
        if ($this->mode == self::MODE_EDIT) {
            unset($this->data['admin_object_id']);
        }
    }

    protected function doInsert() {
        return $this->handler->insertAclObject(module_db_interface_AdminGroup::MODE_DOC, $this->data);
    }

    protected function doUpdate() {
        return $this->handler->updateAclObject(module_db_interface_AdminGroup::MODE_DOC, $this->id, $this->data);
    }

    protected function doLoad() {
        return $this->handler->loadAclObject(module_db_interface_AdminGroup::MODE_DOC, $this->id);
    }

    protected function doDelete() {
        return $this->handler->deleteAclObject(module_db_interface_AdminGroup::MODE_DOC, $this->id);
    }
}
?>