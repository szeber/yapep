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
 * Page control
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_admin_control_Page extends sys_admin_control_Panel {

	/**
	 * @see sys_admin_control_Panel::addControl()
	 *
	 * @param sys_admin_Control $control
	 * @param string $name
	 * @return sys_admin_BoxPlace
	 */
	public function addControl(sys_admin_Control $control, $name) {
		if (!($control instanceof sys_admin_control_BoxPlace)) {
			throw new sys_exception_AdminException(_('Provided control is not a BoxPlace'), sys_exception_AdminException::ERR_BAD_CONTROL_TYPE);
		}
		return parent::addControl($control, $name);
	}

	/**
	 * Sets the template's HTML code
	 *
	 * @param string $templateCode
	 */
	public function setTemplateCode($templateCode) {
		$this->smarty->assign('templateCode', $templateCode);
	}

	/**
	 * Fetches the XML from Smarty
	 *
	 * @return string
	 */
	protected function fetchSmarty() {
		return $this->smarty->fetch ('yapep:admin/control/Page.tpl');
	}


}
?>