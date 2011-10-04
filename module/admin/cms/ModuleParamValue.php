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
 * Module parameter value administration module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_cms_ModuleParamValue extends sys_admin_AdminModule {
	protected function buildForm() {
        $this->requireSuperuser();

		$handler = getPersistClass('ModuleParamValue');

		$this->setDbHandler($handler);

		$control = new sys_admin_control_IdSelect();
		$control->addOptions($handler->getModuleParamValueList($this->subModule[0]));
		$control->setValue($this->id, true);
		$this->addControl($control, 'idSelect');

		$control = new sys_admin_control_Label();
		$control->setLabel(_('Module parameter ID'));
		$control->setValue($this->subModule[0], true);
		$control->setReadOnly(true);
		$this->addControl($control, 'module_param_id');

		$control = new sys_admin_control_TextInput();
 		$control->setLabel(_('Description'));
 		$control->setRequired();
 		$this->addControl($control, 'description');

 		$control = new sys_admin_control_TextArea();
 		$control->setLabel(_('value'));
 		$this->addControl($control, 'value');

	}
}
?>