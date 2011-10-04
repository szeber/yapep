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
 * Panel with taskbar component
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_admin_control_TaskbarPanel extends sys_admin_control_Panel {

	const TASKBAR_POSITION_TOP = 0;
	const TASKBAR_POSITION_RIGHT = 1;
	const TASKBAR_POSITION_BOTTOM = 2;
	const TASKBAR_POSITION_LEFT = 3;

	/**
	 * @see sys_admin_Control::setDefaults()
	 *
	 */
	protected function setDefaults() {
		parent::setDefaults();
		$this->options['taskbarPosition'] = sys_admin_control_TaskbarPanel::TASKBAR_POSITION_BOTTOM;
	}


	/**
	 * Sets the position for the taskbar
	 *
	 * @param integer $position
	 */
	function setTaskbarPosition($position) {
		$this->options['taskbarPosition'] = $position;
	}
}
?>