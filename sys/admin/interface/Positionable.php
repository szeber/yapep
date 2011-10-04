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
 * Positionable control interface
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface sys_admin_interface_Positionable {

	/**
	 * Sets the top coordinate and removes the bottom coordinate if it has been set.
	 *
	 * @param integer $coord
	 */
	public function setTop($coord);

	/**
	 * Sets the bottom coordinate and removes the top coordinate if it has been set
	 *
	 * @param integer $coord
	 */
	public function setBottom($coord);

	/**
	 * Sets the left coordinate and removes the right coordinate if it has been set
	 *
	 * @param integer $coord
	 */
	public function setLeft($coord);

	/**
	 * Sets the right coordinate and removes the left coordinate if it has been set
	 *
	 * @param integer coord
	 */
	public function setRight($coord);
}
?>