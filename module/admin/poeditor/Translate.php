<?php
/**
 *
 *
 * @version $Rev$
 */

/**
 *
 *
 * @version $Rev$
 */
class module_admin_poeditor_Translate extends sys_admin_AdminModule {

	/**
	 * @var module_db_interface_Poeditor
	 */
	protected $poeditorHandler;

	/**
	 * @see sys_admin_AdminModule::buildForm()
	 *
	 */
	protected function buildForm() {

		$this->poeditorHandler = getPersistClass('Poeditor');

		if (!$this->subModule[0]) {
			throw new sys_exception_AdminException(_('No locale set'), sys_exception_AdminException::ERR_NO_SUBMODULE_SET);
		}

		$control = new sys_admin_control_Label();
		$control->setReadOnly();
		$control->setLabel(_('Original text'));
		$this->addControl($control, 'text');

		$control = new sys_admin_control_TextArea();
		$control->setLabel(_('Translation'));
		$control->setRequired();
		$this->addControl($control, 'translation');

		$control = new sys_admin_control_CheckBox();
		$control->setLabel(_('Fuzzy'));
		$this->addControl($control, 'fuzzy');
	}

	/**
	 * @see sys_admin_AdminModule::doDelete()
	 *
	 */
	protected function doDelete() {
		$this->poeditorHandler->deleteTranslation($this->id, $this->subModule[0]);
	}

	/**
	 * @see sys_admin_AdminModule::doLoad()
	 *
	 * @return array;
	 */
	protected function doLoad() {
		return $this->poeditorHandler->loadTranslation($this->id, $this->subModule[0]);
	}

	/**
	 * @see sys_admin_AdminModule::doSave()
	 *
	 * @return string
	 */
	protected function doSave() {
		$this->poeditorHandler->saveTranslation($this->id, $this->subModule[0], $this->data['translation'], $this->data['fuzzy']);
		return '';
	}
}
?>