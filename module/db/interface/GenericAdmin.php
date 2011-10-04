<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

 /**
 * Generic database interface for administration
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface module_db_interface_GenericAdmin extends module_db_interface_Admin {

	/**
	 * Sets the object type for the module
	 *
	 * The module will determine the used table based on this information
	 *
	 * @param string $type
	 */
	public function setObjType($type);

	/**
	 * Returns the objects for a given object type in list format (id=>name)
	 *
	 * @param string $type
	 * @param string $where
	 * @return array
	 */
	public function getList($type=null, $where = '');

}
?>