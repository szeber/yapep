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
 * Sub item list control
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_admin_control_RelationList extends sys_admin_control_BrowserList {

	/**
	 * List of allowed object types
	 *
	 * @var array
	 */
	protected $objectTypes = array();

	/**
	 * Adds object types to the list of allowed object types for the control
	 *
	 * @param string $type
	 */
	public function addObjectType($type) {
		$this->objectTypes[] = $type;
	}

	/**
	 * @see sys_admin_control_TextInput::fetchSmarty()
	 *
	 * @return string
	 */
	protected function fetchSmarty() {
		return $this->smarty->fetch ('yapep:admin/control/RelationList.tpl');
	}

	/**
	 * @see sys_admin_control_TextInput::getXml()
	 *
	 * @return string
	 */
	public function getXml() {
		$this->smarty->assign ('objectTypes', $this->objectTypes);
		return parent::getXml();
	}

	/**
	 * Sets the inplace added object type
	 *
	 * @param string $type
	 */
	public function setInplaceType($type) {
		$this->options['inplaceType'] = $type;
	}

}
?>