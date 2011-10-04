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
 * Browser list control
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_admin_control_BrowserList extends sys_admin_control_SubItemList {

	/**
	 * @see sys_admin_Control::setDefaults()
	 *
	 */
	protected function setDefaults() {
		parent::setDefaults();
		$this->nameField = 'id';
		$this->valueField = 'display';
	}

	public function setDataForm($formName) {
		$this->options['dataForm'] = $formName;
	}

	public function setBrowserForm($formName) {
		$this->options['browserForm'] = $formName;
	}

	public function setDisplayTemplate($template) {
		$this->options['displayTemplate'] = $template;
	}

	public function setDataField($field) {
		$this->options['dataField'] = $field;
	}
}
 ?>