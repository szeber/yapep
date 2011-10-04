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
 * Object type administration module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_cms_ObjectTypeColumn extends sys_admin_AdminModule {

	/**
	 * Stores the database handler instance
	 *
	 * @var module_db_interface_ObjectType
	 */
	protected $handler;

 	public function buildForm() {

        $this->requireSuperuser();

		$this->handler = getPersistClass('ObjectType');

		$control = new sys_admin_control_IdSelect();
		$control->addOptions($this->handler->getObjectTypeColumnList($this->subModule[0]));
		$control->setValue($this->id, true);
		$this->addControl($control, 'idSelect');

		$control = new sys_admin_control_Label();
		$control->setLabel(_('Object type ID'));
		$control->setValue($this->subModule[0], true);
		$control->setReadOnly(true);
		$this->addControl($control, 'object_type_id');

 		$control = new sys_admin_control_TextInput();
 		$control->setLabel(_('Column name'));
 		$control->setRequired();
 		$this->addControl($control, 'name');

 		$control = new sys_admin_control_TextInput();
 		$control->setLabel(_('Displayed name'));
 		$control->setRequired();
 		$this->addControl($control, 'title');

 	 	$control = new sys_admin_control_TextInput();
 		$control->setLabel(_('Column number'));
 		$control->setRequired();
 		$control->setValidateNumeric();
 		$this->addControl($control, 'column_number');

 	 	$control = new sys_admin_control_CheckBox();
 		$control->setLabel(_('Displayed in list'));
 		$this->addControl($control, 'in_list');

 	 	$control = new sys_admin_control_CheckBox();
 		$control->setLabel(_('Displayed in export'));
 		$this->addControl($control, 'in_export');
  	}

  	protected function doLoad() {
		return $this->handler->loadColumnItem ($this->id);
	}

	protected function doSave() {
		if ($this->mode == sys_admin_AdminModule::MODE_ADD) {
			$result = $this->handler->insertColumnItem ($this->panel->getInputValues ());
			if ($result && is_numeric ($result)) {
				$this->options ['newId'] = $result;
				$this->id = $result;
				if ($this->newForm) {
					$this->options ['newForm'] = $this->newForm;
				}
				$result = '';
			} elseif (!$result) {
				$result = 'Insert error';
			}
		} else {
			$result = $this->handler->updateColumnItem ($this->id, $this->panel->getInputValues ());
		}
		return $result;
	}

	protected function doDelete() {
		return $this->handler->deleteColumnItem($this->id);
	}

}
?>