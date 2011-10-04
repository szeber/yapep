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
 * System configuration adminstration module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_cms_SysConfig extends sys_admin_AdminModule {
	protected function buildForm() {
		$this->setTitle(_('System configuration editor'));

		$handler = getPersistClass('SysConfig');
		$this->setDbHandler($handler);

		$control = new sys_admin_control_IdSelect();
		$control->addOptions($handler->getConfigList());
		$control->setValue($this->id, true);
		$this->addControl($control, 'idSelect');

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Name'));
		$control->setRequired();
		$control->setValidateRegex('/^[-_A-Z0-9]+$/');
		$this->addControl($control, 'name');

		$control = new sys_admin_control_TextArea();
		$control->setLabel(_('Value'));
		$control->setRequired();
		$this->addControl($control, 'value');
	}

	protected function postSave() {
		$cache = new sys_cache_SysConfigCacheManager();
		$cache->recreateCache();
	}

	protected function postDelete() {
	    $this->recreateCache();
	}



}
?>