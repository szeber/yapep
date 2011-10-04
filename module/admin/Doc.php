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
 * Document administration module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_Doc extends sys_admin_AdminModule {

	/**
	 * @var sys_admin_AdminModule
	 */
	protected $module;

	/**
	 * @var integer
	 */
	protected $objectId;

	protected function buildForm() {

		$this->setDbHandler(getPersistClass('Doc'));

		if (! $this->subModule[0]) {
			throw new sys_exception_AdminException(_('No submodule set'), sys_exception_AdminException::ERR_NO_SUBMODULE_SET);
		}
		if (!class_exists('module_admin_'.$this->subModule[0])) {
			throw new sys_exception_AdminException(_('Submodule not found'), sys_exception_AdminException::ERR_SUBMODULE_NOT_FOUND);
		}

		$this->setTitleField('doctitle');

		$docPanel = new sys_admin_control_Panel();
		$docPanel->setDock(sys_admin_control_Panel::DOCK_NONE);
		$this->addControl($docPanel, 'docPanel');

		$control = new sys_admin_control_PreviewButton();
		$control->setLabel(_('Preview'));
		$control->setDisplayOn(sys_admin_interface_Input::DISPLAY_EDIT);
		$docPanel->addControl($control, 'PreviewButton');

		$control = new sys_admin_control_HiddenInput();
		$docPanel->addControl($control, 'docPath');

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Document title'));
		$docPanel->addControl($control, 'doctitle');

		$control = new sys_admin_control_HiddenInput();
		$control->setReadOnly();
		$docPanel->addControl($control, 'docFolderId');

		$control = new sys_admin_control_DateInput();
		$control->setShowDate();
		$control->setShowTime();
		$control->setLabel(_('Start date'));
		$control->setDefaultValue(date('Y-m-d H:i:s'));
		$docPanel->addControl($control, 'start_date');

		$control = new sys_admin_control_DateInput();
		$control->setShowDate();
		$control->setShowTime();
		$control->setLabel(_('End date'));
		$control->setDefaultValue((date('Y')+20).date('-m-d H:i:s'));
		$docPanel->addControl($control, 'end_date');

		$control = new sys_admin_control_SelectInput();
		$control->addOption(_('Active'), module_db_interface_Doc::STATUS_ACTIVE);
		$control->addOption(_('Inactive'), module_db_interface_Doc::STATUS_INACTIVE);
		$control->setLabel(_('Status'));
		$docPanel->addControl($control, 'status');

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Docname'));
		$docPanel->addControl($control, 'docname');

		$control = new sys_admin_control_HiddenInput();
		$control->setReadOnly();
		$docPanel->addControl($control, 'object_id');

		$moduleName = 'module_admin_'.$this->subModule[0];

		$this->module = new $moduleName($this->manager);
		$this->module->setLocale($this->locale);
		$this->module->parseXml($this->xml);
		$panel = $this->module->getPanel();
		$panel->setAddForm(false);
		$panel->setDock(sys_admin_control_Panel::DOCK_NONE);
		$this->addControl($panel, 'objectPanel');

		try {
			$control = $panel->getControl('name');
		} catch (sys_exception_AdminException $e) {
			$control = $docPanel->getControl('doctitle');
			$control->setRequired(true);
		}
	}

	protected function processSaveData() {
		$this->data['docLocale'] = $this->localeId;
		$this->objectId = $this->data['object_id'];
		unset($this->data['object_id']);
		if (!$this->data['doctitle']) {
			$this->data['doctitle'] = $this->data['name'];
		}
		if (!$this->data['docname']) {
			$this->data['docname'] = $this->data['doctitle'];

		}
		if (!$this->data['docFolderId']) {
			$this->data['docFolderId'] = $this->subModule[1];
		}
		$this->data['docname'] = makeValidDocnameFromString($this->localeId, $this->data['docname'], $this->data['docFolderId'], $this->id);
		$this->data['docObjectTypeName'] = $this->subModule[0];
		if ($this->mode == sys_admin_AdminModule::MODE_EDIT) {
			$this->module->setId($this->objectId);
		}
		$this->data = $this->module->externalSaveProcess($this->data);
	}

	/**
	 * @see sys_admin_AdminModule::postSave()
	 *
	 */
	protected function postSave() {
		$this->module->setId($this->objectId);
		$this->data = $this->module->externalPostSave($this->data);
	}

	/**
	 * @see sys_admin_AdminModule::processLoadData()
	 *
	 */
	protected function processLoadData() {
		$this->module->setId($this->data['object_id']);
		$this->data = $this->module->externalLoadProcess($this->data);
		if ($this->id) {
			$folderHandler = getPersistClass('Folder');
			$folderData = $folderHandler->getFullFolderInfoById($this->data['folder_id']);
			$this->data['PreviewButton'] = '/'.$this->locale.'/'.$folderData['docpath'].'/'.$this->data['docname'];
			$this->data['docPath'] = $folderData['docpath'];
		}
	}

}
?>