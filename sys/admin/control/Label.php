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
 * Label input control
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_admin_control_Label extends sys_admin_control_TextInput {

	/**
	 * @see sys_admin_control_TextInput::setNormalValue()
	 *
	 * @param mixed $value
	 * @param boolean $load
	 * @return boolean
	 */
	public function setNormalValue($value, $load = false) {
		if (!$load) {
			return true;
		}
		return parent::setNormalValue($value, $load);
	}

	/**
	 * @see sys_admin_Control::setDefaults()
	 *
	 */
	protected function setDefaults() {
		parent::setDefaults();
		$this->options['readOnly'] = 1;
	}
}
?>