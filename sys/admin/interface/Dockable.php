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
 * Dockable component interface
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface sys_admin_interface_Dockable {

	const DOCK_NONE=null;
	const DOCK_CLIENT=0;
	const DOCK_TOP=1;
	const DOCK_RIGHT=2;
	const DOCK_BOTTOM=3;
	const DOCK_LEFT=4;

	/**
	 * Sets the dock mode for the control
	 *
	 * @param integer $dockMode
	 */
	public function setDock($dockMode);
}
?>