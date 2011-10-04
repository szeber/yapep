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
 * Button control to open a form in a popup window
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_admin_control_PopupFormButton extends sys_admin_control_Button {

	/**
	 * Sets the displayed form's name
	 *
	 * @param string $formName
	 */
	public function setTargetForm($formName) {
		$this->options['targetForm'] = $formName;
	}

	/**
	 * Sets the displayed form's id
	 *
	 * @param string $id
	 */
	public function setTargetId($id) {
		$this->options['targetId'] = $id;
	}

	/**
	 * Sets the field that should be bound as the submodule for the target
	 *
	 * @param string $field
	 */
	public function setTargetSubFormField($field) {
		if ($field) {
			$this->options['targetSubFormField'] = $field;
			return;
		}
		unset($this->options['targetSubFormField']);
	}

	/**
	 * Sets the popup window's title
	 *
	 * @param string $title
	 */
	public function setWindowTitle($title) {
		$this->options['windowTitle'] = $title;
	}
}
?>