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
 * Displayable control interface
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface sys_admin_interface_Displayable {

	/**
	 * Sets the height of the control
	 *
	 * @param boolean $hidden
	 */
	public function setHidden($hidden=true);

	/**
	 * Sets the width of the control
	 *
	 * @param integer $width
	 */
	public function setWidth($width=null);

	/**
	 * Sets the height of the control
	 *
	 * @param integer $height
	 */
	public function setHeight($height=null);

}
?>