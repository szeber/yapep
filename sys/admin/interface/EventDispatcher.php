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
 * Event listener admin interface
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface sys_admin_interface_EventDispatcher {

	/**
	 * Adds a new listener
	 *
	 * @param string $name
	 */
	public function addListener($name);

	/**
	 * Removes a listener
	 *
	 * @param string $name
	 */
	public function removeListener($name);

	/**
	 * Removes all listeners
	 *
	 */
	public function removeAllListeners();
}
?>