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
 * Date input control
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_admin_control_DateInput extends sys_admin_control_TextInput {

	/**
	 * Sets if the control should require the user to verify their password input by typing it again
	 *
	 * @param boolean $retype
	 */
	public function setMinDate($date) {
		if (is_null($date)) {
			unset($this->options['minDate']);
			return;
		}
		$this->options['minDate'] = $date;
	}

	public function setMaxDate($date) {
		if (is_null($date)) {
			unset($this->options['maxDate']);
			return;
		}
		$this->options['maxDate'] = $date;
	}

	public function setShowDate($show = true) {
		if ($show) {
			$this->options['showDate'] = 1;
			return;
		}
		unset($this->options['showDate']);
	}

	public function setShowTime($show = true) {
		if ($show) {
			$this->options['showTime'] = 1;
			return;
		}
		unset($this->options['showTime']);

	}
}
?>