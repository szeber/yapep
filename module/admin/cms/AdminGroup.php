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
class module_admin_cms_AdminGroup extends sys_admin_AdminModule {

	/**
	 * @see sys_admin_AdminModule::buildForm()
	 *
	 */
	protected function buildForm() {
        $this->requireSuperuser();

        $handler = getPersistClass('GenericAdmin');
        $handler->setObjType('admin_group');
        $this->setDbHandler($handler);

 		$control = new sys_admin_control_IdSelect();
 		$control->addOptions($handler->getList('admin_group'));
 		$control->setValue($this->id, true);
 		$this->addControl($control, 'idSelect');

        $control = new sys_admin_control_TextInput();
        $control->setLabel(_('Name'));
        $control->setRequired();
        $this->addControl($control, 'name');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Can list all folders'));
        $this->addControl($control, 'folder_def_list');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Can create documents in all folders'));
        $this->addControl($control, 'folder_def_create');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Can delete documents in all folders'));
        $this->addControl($control, 'folder_def_delete');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Can edit all folder\'s properties'));
        $this->addControl($control, 'folder_def_edit_props');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Can read all documents'));
        $this->addControl($control, 'doc_def_read');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Can modify all documents'));
        $this->addControl($control, 'doc_def_write');

		$control = new sys_admin_control_SubItemList();
		$control->setLabel(_('Users'));
		$control->setValueField('name');
		$control->setNameField('id');
		$control->setAddFieldLabel(_('New user'));
		$control->setSubForm('cms_AdminUserGroup/g');
		$this->addControl($control, 'Users');
    }

    protected function processLoadData() {
        parent::processLoadData();
        $handler = getPersistClass('AdminGroup');
        $this->data['Users'] = $handler->getUsersForGroup($this->id);
    }
}
?>