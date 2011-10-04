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
 * Checkbox input control
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_admin_control_CheckBox extends sys_admin_control_TextInput {

	/**
	 * @see sys_admin_control_TextInput::setNormalValue()
	 *
	 * @param mixed $value
	 * @param boolean $load If true forces the value to be set without validation
	 */
	protected function setNormalValue($value, $load = false) {
		return parent::setNormalValue((bool)$value, $load);
	}

}
?>