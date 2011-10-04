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
 * Treeview control
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_admin_control_TreeView extends sys_admin_Control implements sys_admin_interface_Hierarchical, sys_admin_interface_EventDispatcher  {

	/**
	 * The tree structure
	 *
	 * @var array
	 */
	protected $tree=array();

	protected $listeners=array();

	/**
	 * @see sys_admin_interface_Control::getXml()
	 *
	 * @return string
	 */
	public function getXml() {
		$this->smarty->assign ('options', $this->options);
		$this->smarty->assign ('listeners', $this->listeners);
		$this->smarty->assign ('tree', $this->tree);
		return $this->fetchSmarty ();
	}

	/**
	 * Fetches the template from smarty
	 *
	 * @return string
	 */
	protected function fetchSmarty() {
		return $this->smarty->fetch ('yapep:admin/control/TreeView.tpl');
	}

	/**
	 * @see sys_admin_interface_Hierarchical::setTree()
	 *
	 * @param array $tree
	 */
	public function setTree($tree) {
		$this->tree = $tree;
	}

	/**
	 * @see sys_admin_interface_EventDispatcher::addListener()
	 *
	 * @param string $name
	 */
	public function addListener($name) {
		$this->listeners[]=$name;
	}

	/**
	 * @see sys_admin_interface_EventDispatcher::removeAllListeners()
	 *
	 */
	public function removeAllListeners() {
		$this->listeners = array();
	}

	/**
	 * @see sys_admin_interface_EventDispatcher::removeListener()
	 *
	 * @param string $name
	 */
	public function removeListener($name) {
		$key = array_search($name, $this->listeners);
		if (false !== $key) {
			unset($this->listeners[$key]);
		}
	}


}
?>