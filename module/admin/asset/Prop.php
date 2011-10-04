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
 * Folder property form
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_asset_Prop extends sys_admin_AdminModule {

	/**
	 * Stores the folder database handler instance
	 *
	 * @var module_db_interface_Folder
	 */
	protected $folderHandler;

	/**
	 * @see sys_admin_AdminModule::buildForm()
	 */
	public function buildForm() {

		$this->setAddBtnDisabled ();

		$this->setDbHandler (getPersistClass ('AssetFolder'));

		$this->setTitle (_ ('Asset folder property editor'));

		$this->panel->refreshForm ('asset_Tree');

		if (!$this->subModule [0]) {
			$control = new sys_admin_control_PopupFormButton ();
			$control->setLabel (_ ('New subfolder'));
			$control->setTargetForm ('asset_Prop');
			$control->setTargetSubFormField ('id');
			$control->setWindowTitle (_ ('New subfolder'));
			$this->addControl ($control, 'newSubfolderButton');

			$control = new sys_admin_control_Label ();
			$control->setLabel (_ ('id'));
			$this->panel->addControl ($control, 'id');

			$control = new sys_admin_control_Label ();
			$control->setLabel (_ ('Folder path'));
			$this->addControl ($control, 'docpath');
		} else {
			$control = new sys_admin_control_Label ();
			$control->setLabel (_ ('id'));
			$this->panel->addControl ($control, 'id');
		}

		$control = new sys_admin_control_HiddenInput ();
		if ($this->subModule [0]) {
			$control->setDefaultValue ($this->subModule [0]);
			$control->setValue ($this->subModule [0], true);
			$control->setReadOnly ();
		}
		$this->addControl ($control, 'parent_id');

		$control = new sys_admin_control_TextInput ();
		$control->setLabel (_ ('Folder name'));
		$control->setRequired ();
		$this->addControl ($control, 'name');

		$control = new sys_admin_control_TextInput ();
		$control->setLabel (_ ('Folder short name'));
		$control->setRequired ();
		$control->setValidateMaxLength (32);
		$this->addControl ($control, 'short');

	}

	/**
	 * @see sys_admin_AdminModule::processSaveData()
	 *
	 */
	protected function processSaveData() {
		if ($this->mode == sys_admin_AdminModule::MODE_ADD && $this->data ['parent_id'] == 0) {
			unset ($this->data ['parent_id']);
		}
	}

}
?>