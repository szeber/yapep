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
 * Administrator user module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_cms_AdminUser extends sys_admin_AdminModule {

	protected function buildForm() {

		$this->requireSuperuser();

		$this->setTitle(_('Administrator user editor'));

		$handler = getPersistClass('AdminUser');
		$this->setDbHandler($handler);

		$control = new sys_admin_control_IdSelect();
		$control->addOptions($handler->getUserList());
		$control->setValue($this->id, true);
		$this->addControl($control, 'idSelect');

		$this->addDefaultObjectFields();

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Full name'));
		$control->setRequired();
		$this->addControl($control, 'name');

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Username'));
		$control->setRequired();
		$this->addControl($control, 'username');

		$control = new sys_admin_control_PasswordInput();
		$control->setLabel(_('Password'));
		$control->setRetype();
		$this->addControl($control, 'password');

		$control = new sys_admin_control_CheckBox();
		$control->setLabel(_('Superuser'));
		$this->addControl($control, 'superuser');

		$control = new sys_admin_control_SelectInput();
		$control->setLabel(_('Default locale'));
		$control->setRequired();
		$control->addOptions($this->getLocales());
		$this->addControl($control, 'locale_id');
	}

	protected function getLocales() {
		$localeHandler = getPersistClass('LangLocale');
		$locales=array();
		$data = $localeHandler->getLocales();
		foreach($data as $locale) {
			$locales[$locale['id']] = $locale['name'];
		}
		return $locales;
	}

	/**
	 * @see sys_admin_AdminModule::processLoadData()
	 *
	 */
	protected function processLoadData() {
		$this->data['password'] = '';
	}

	/**
	 * @see sys_admin_AdminModule::processSaveData()
	 *
	 */
	protected function processSaveData() {
		$control = $this->panel->getInput('password');
		$pass = $control->getValue();
		if ($pass || 0 === $pass) {
			$this->data['password'] = sys_Auth::hashPassword($pass);
		} elseif ($this->mode == sys_admin_AdminModule::MODE_ADD) {
			$control->setRequired(true);
			$control->setValue('');
			throw new sys_exception_AdminException(_('Validation failed'));
		}
	}

}
?>