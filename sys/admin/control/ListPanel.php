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
 * List panel control
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_admin_control_ListPanel extends sys_admin_control_Panel implements sys_admin_interface_EventListener {

	/**
	 * @see sys_admin_interface_EventListener::setListenerName()
	 *
	 * @param string $name
	 */
	public function setListenerName($name) {
		if (!$name) {
			throw new sys_exception_AdminException(_('Listener name empty', sys_exception_AdminException::ERR_LISTENER_NAME_EMPTY));
		}
		$this->options['listenerName'] = $name;
	}

	public function setTarget($name) {
		$this->options['target'] = $name;
	}

	/**
	 * @see sys_admin_control_Panel::setDefaults()
	 *
	 */
	protected function setDefaults() {
		parent::setDefaults();
		$this->options['target']='objectPanel';
	}



}
?>