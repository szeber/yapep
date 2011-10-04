<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

 /**
 * Admin login form
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_Login extends sys_admin_AdminModule {

	/**
	 * @see sys_admin_AdminModule::init()
	 *
	 */
	protected function init() {
		$auth = new sys_Auth('LoggedInAdminData', 'AdminUser');
		if ($auth->checkLoggedIn()) {
			$this->manager->replaceModule(new module_admin_Main($this->manager));
		}
	}


	/**
	 * @see sys_admin_AdminModule::buildForm()
	 *
	 */
	protected function buildForm() {

		$this->setDeleteBtnDisabled();
		$this->setAddBtnDisabled();

		$this->setReloadOnSave();

		$this->setRootForm();

		$window = new sys_admin_control_Window();
		$window->setTitle(_('Please log in'));
		$window->setWidth(400);
		$window->setHeight(200);
		$this->addControl($window, 'loginWindow');

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Username'));
		$control->setMapValue('username');
		$control->setRequired();
		$control->setDefaultFocused();
		$window->addControl($control, 'username');

		$control = new sys_admin_control_PasswordInput();
		$control->setLabel(_('Password'));
		$control->setMapValue('password');
		$control->setRequired();
		$window->addControl($control, 'password');

		$control = new sys_admin_control_SubmitButton();
		$control->setLabel(_('Login'));
		$window->addControl($control, 'loginButton');
	}

	protected function doSave() {
		return _('Login failed');
	}

	protected function doLoad() {
		throw new sys_exception_AdminException(_('Please log in'), sys_exception_AdminException::ERR_NOT_LOGGED_IN);
	}

}
?>