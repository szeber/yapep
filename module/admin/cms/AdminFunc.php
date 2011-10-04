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
 * Admin function administration module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_cms_AdminFunc extends sys_admin_AdminModule {
	protected function buildForm() {

        $this->requireSuperuser();

		$handler = getPersistClass('AdminFunc');

		$this->setDbHandler($handler);

		$control = new sys_admin_control_IdSelect();
		$control->addOptions($handler->getAdminFuncList());
		$control->setValue($this->id, true);
		$this->addControl($control, 'idSelect');

		$this->addDefaultObjectFields();

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Name'));
		$this->addControl($control, 'name');

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Admin module name'));
		$this->addControl($control, 'class');
	}
}
?>