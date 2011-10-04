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
class sys_admin_control_Button extends sys_admin_Control{

	/**
	 * The control's label
	 *
	 * @var string
	 */
	protected $label;

	/**
	 * The control's description
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * @see sys_admin_interface_Control::getXml()
	 *
	 * @return string
	 */
	public function getXml() {
		$this->smarty->assign ('options', $this->options);
		$this->smarty->assign ('label', $this->label);
		$this->smarty->assign ('description', $this->description);
		return $this->fetchSmarty();
	}

	/**
	 * Fetches the template from smarty
	 *
	 * @return string
	 */
	protected function fetchSmarty() {
		return $this->smarty->fetch ('yapep:admin/control/Button.tpl');
	}

	/**
	 * Sets the label for the control
	 *
	 * @param string $label
	 */
	public function setLabel($label) {
		$this->label = $label;
	}

	/**
	 * Sets the description for the control
	 *
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	public function setDisplayOn($display) {
		$this->options['displayOn'] = (int)$display;
	}

}
?>