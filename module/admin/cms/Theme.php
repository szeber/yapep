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
 * Site locale administration module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_cms_Theme extends sys_admin_AdminModule {

	protected function buildForm() {

        $this->requireSuperuser();

		$handler = getPersistClass('Theme');

		$this->setDbHandler($handler);

		$this->setTitle(_('Theme editor'));

		$control = new sys_admin_control_IdSelect();
		$control->addOptions($handler->getThemeList());
		$control->setValue($this->id, true);
		$this->addControl($control, 'idSelect');

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Theme name'));
		$this->addControl($control, 'name');

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Theme description'));
		$this->addControl($control, 'description');

	}

}
?>