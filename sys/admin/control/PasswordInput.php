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
 * Password input control
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_admin_control_PasswordInput extends sys_admin_control_TextInput {

	/**
	 * Sets if the control should require the user to verify their password input by typing it again
	 *
	 * @param boolean $retype
	 */
	public function setRetype($retype = true) {
		if ($retype) {
			$this->options['retype'] = 1;
			return;
		}
		unset($this->options['retype']);
	}
}
?>