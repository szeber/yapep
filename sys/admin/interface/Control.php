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
 * Control base interface
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface sys_admin_interface_Control {

	/**
	 * Sets the class for the control
	 *
	 * @param string $className
	 */
	public function setClass($className);

	/**
	 * Returns the generated XML
	 *
	 * @return string
	 */
	public function getXml();

	/**
	 * Returns the value of the option or null if it's not set
	 *
	 * @return mixed
	 */
	public function getOption($option);
}
?>