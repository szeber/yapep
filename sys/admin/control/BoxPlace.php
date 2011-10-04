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
 * Boxplace control
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_admin_control_BoxPlace extends sys_admin_Control {

	/**
	 * Stores the boxes for the boxplace
	 *
	 * @var array
	 */
	private $boxes = array();

	/**
	 * @see sys_admin_interface_Control::getXml()
	 *
	 * @return string
	 */
	public function getXml() {
		$this->smarty->assign('options', $this->options);
		$this->smarty->assign('boxes', $this->boxes);
		return $this->fetchSmarty();
	}

	/**
	 * Fetches the XML from Smarty
	 *
	 * @return string
	 */
	protected function fetchSmarty() {
		return $this->smarty->fetch ('yapep:admin/control/BoxPlace.tpl');
	}

	/**
	 * Adds a new box to the boxplace
	 *
	 * @param integer $id
	 * @param string $name
	 * @param string $module
	 * @param integer $active
	 * @param boolean $inherited
	 */
	public function addBox($id, $name, $module, $active, $inherited) {
		$this->boxes[$id] = array('id'=>$id, 'name'=>$name, 'module'=>$module, 'active'=>$active, 'inherited'=>(int)$inherited);
	}

	/**
	 * Removes a box from the boxplace
	 *
	 * @param integer $id
	 */
	public function removeBox($id) {
		unset($this->boxes[$id]);
	}

	public function setBoxplaceId($id) {
		$this->options['boxplaceId'] = $id;
	}
}
?>