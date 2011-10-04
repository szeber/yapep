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
class module_admin_cms_Locale extends sys_admin_AdminModule {
	protected function buildForm() {

        $this->requireSuperuser();

		$handler = getPersistClass('LangLocale');
		$this->setDbHandler($handler);

		$this->setTitle(_('Locale editor'));

		$control = new sys_admin_control_IdSelect();
		$control->addOptions($handler->getLocaleList());
		$control->setValue($this->id, true);
		$this->addControl($control, 'idSelect');

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Locale code'));
		$this->addControl($control, 'locale_code');

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Locale name'));
		$this->addControl($control, 'name');
	}
}
?>