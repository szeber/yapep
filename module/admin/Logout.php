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
 * Logout admin module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_Logout extends sys_admin_AdminModule {
	protected function init() {
		$auth = new sys_Auth('LoggedInAdminData', 'AdminUser');
		if (!is_array($_POST['Login']) && (!$_POST['username'] || !$_POST['password'])) {
			$auth->logout();
			$this->manager->replaceModule(new module_admin_Login($this->manager));
		} elseif ($auth->checkLoggedIn()) {
			$this->manager->replaceModule(new module_admin_Main($this->manager));
		} else {
			$this->manager->replaceModule(new module_admin_Login($this->manager));
		}
	}

	protected function buildForm() {}
}
?>