<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

 /**
 * Module parameter administration module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_cms_ModuleParam extends sys_admin_AdminModule {
	protected function buildForm() {
        $this->requireSuperuser();

		$handler = getPersistClass('ModuleParam');

		$this->setDbHandler($handler);

		$control = new sys_admin_control_IdSelect();
		$control->addOptions($handler->getModuleParamList($this->subModule[0]));
		$control->setValue($this->id, true);
		$this->addControl($control, 'idSelect');

		$control = new sys_admin_control_Label();
		$control->setLabel(_('Module ID'));
		$control->setValue($this->subModule[0], true);
		$control->setReadOnly(true);
		$this->addControl($control, 'module_id');

 	 	$control = new sys_admin_control_SelectInput();
 		$control->setLabel(_('Type'));
 		$control->setRequired();
 		$control->addOptions($this->getTypeList());
 		$this->addControl($control, 'param_type_id');

		$control = new sys_admin_control_TextInput();
 		$control->setLabel(_('Name'));
 		$control->setRequired();
 		$this->addControl($control, 'name');

 		$control = new sys_admin_control_TextInput();
 		$control->setLabel(_('Description'));
 		$control->setRequired();
 		$this->addControl($control, 'description');

 		$control = new sys_admin_control_CheckBox();
 		$control->setLabel(_('Allow variable'));
 		$this->addControl($control, 'allow_variable');

 		$control = new sys_admin_control_TextInput();
 		$control->setLabel(_('Default value'));
 		$this->addControl($control, 'default_value');

 		$control = new sys_admin_control_CheckBox();
 		$control->setLabel(_('Default is variable'));
 		$this->addControl($control, 'default_is_variable');

 		$control = new sys_admin_control_SubItemList();
 		$control->setLabel(_('Parameter values'));
 		$control->setDescription(_('Only for combobox type parameters'));
 		$control->setNameField('id');
 		$control->setValueField('description');
 		$control->setDisplayField('description');
 		$control->setSubForm('cms_ModuleParamValue');
 		$control->setAddFieldLabel(_('New value'));
 		$this->addControl($control, 'Values');
 	}

 	protected function getTypeList() {
 		$types = array(
 			module_db_interface_ModuleParam::TYPE_TEXT			=>	_('Text'),
 			module_db_interface_ModuleParam::TYPE_LONG_TEXT		=>	_('Long text'),
 			module_db_interface_ModuleParam::TYPE_CHECK			=>	_('Checkbox'),
 			module_db_interface_ModuleParam::TYPE_SELECT		=>	_('Dropdown'),
 			module_db_interface_ModuleParam::TYPE_DOC			=>	_('Document'),
 			module_db_interface_ModuleParam::TYPE_DOC_LIST		=>	_('Document list'),
 			module_db_interface_ModuleParam::TYPE_FOLDER		=>	_('Folder'),
 			module_db_interface_ModuleParam::TYPE_FOLDER_LIST	=>	_('Folder list'),

 		);
 		asort($types);
 		return $types;
 	}

 	protected function postSave() {
 		$cache = new sys_cache_ModuleCacheManager();
 		$cache->recreateCache();
 	}

 	protected function postDelete() {
 	    $this->postSave();
 	}
}
?>