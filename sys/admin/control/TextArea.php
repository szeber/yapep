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
 * Text area control
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_admin_control_TextArea extends sys_admin_control_TextInput {

	/**
	 * Sets if the control should be a rich edit box
	 *
	 * @param boolean $richEdit
	 */
	public function setRichEdit($richEdit=true) {
		if (!$richEdit) {
			unset($this->options['richEdit']);
			return;
		}
		$this->options['richEdit'] = 1;
	}

}
?>