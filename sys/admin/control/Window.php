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
 * Window control
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_admin_control_Window extends sys_admin_control_Panel implements sys_admin_interface_Positionable {

	// CONSTANTS
	const MODALITY_NONE = 0;

	const MODALITY_MODAL = 1;

	const MODALITY_FULL = 2;

	/**
	 * @see sys_admin_Control::setDefaults()
	 *
	 */
	protected function setDefaults() {
		parent::setDefaults();
		$this->setModality(sys_admin_control_Window::MODALITY_NONE);
	}

	/**
	 * Sets the modality of the window
	 *
	 * @param integer $modality
	 */
	public function setModality($modality) {
		$this->options ['modality'] = $modality;
	}

	/**
	 * @see sys_admin_interface_Positionable::setBottom()
	 *
	 * @param integer $coord
	 */
	public function setBottom($coord) {
		$this->setCoord ('bottom', $coord);
	}

	/**
	 * @see sys_admin_interface_Positionable::setLeft()
	 *
	 * @param integer $coord
	 */
	public function setLeft($coord) {
		$this->setCoord ('left', $coord);
	}

	/**
	 * @see sys_admin_interface_Positionable::setRight()
	 *
	 * @param unknown_type $coord
	 */
	public function setRight($coord) {
		$this->setCoord ('right', $coord);
	}

	/**
	 * @see sys_admin_interface_Positionable::setTop()
	 *
	 * @param integer $coord
	 */
	public function setTop($coord) {
		$this->setCoord ('top', $coord);
	}

	/**
	 * Sets the specified coordniate and clears the opposite one
	 *
	 * @param string $type
	 * @param integer $coord
	 */
	protected function setCoord($type, $coord) {
		$this->checkValidCoord ($coord);
		switch ( $type) {
			case 'bottom' :
				$clear = 'top';
				break;
			case 'top' :
				$clear = 'bottom';
				break;
			case 'left' :
				$clear = 'right';
				break;
			case 'right' :
				$clear = 'left';
				break;
		}
		if (isset ($this->options [$clear])) {
			unset ($this->options [$clear]);
		}
		$this->options [$type] = $coord;

	}

	/**
	 * Checks if the given coordinate is valid
	 *
	 * Throws and exception if the coordinate is not valid
	 *
	 * @param integer $coord
	 * @return boolean
	 * @throws
	 */
	protected function checkValidCoord(&$coord) {
		$coord = (int) $coord;
		if ($coord < 0) {
			throw new sys_exception_AdminException ('Invalid coordinate: ' . $coord, sys_exception_AdminException::ERR_POSITIONABLE_INVALID_COORD);
		}
		return true;
	}
}
?>