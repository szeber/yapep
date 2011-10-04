<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Attila Danch <da@scp.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Box database interface
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Attila Danch <da@scp.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface module_db_interface_Box extends module_db_interface_Admin {

	/**
	 * Returns a box's data
	 *
	 * @param integer $boxId
	 * @return array
	 */
	public function getBox($boxId);

	/**
	 * Updates a box's parameters
	 *
	 * @param integer $boxId
	 * @param array $params
	 * @param boolean $onlyInherited
	 */
	public function updateBoxParams($boxId, $params, $onlyInherited = false);
}
?>