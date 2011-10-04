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
 * Control base class
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
abstract class sys_admin_Control implements sys_admin_interface_Control {

	/**
	 * Option list
	 *
	 * @var array
	 */
	protected $options = array ();

	/**
	 * Smarty
	 *
	 * @var Smarty
	 */
	protected $smarty;

	/**
	 * Constructor
	 *
	 */
	final public function __construct() {
		// FIXME IMPLEMENT
		$this->smarty = sys_LibFactory::getSmarty();
		$this->smarty->assign('CONTROL', $this);
		$this->init ();
		$this->setDefaults ();
	}

	/**
	 * Sets up default options
	 *
	 * Called from the constructor
	 */
	protected function setDefaults() {}

	/**
	 * Initialization
	 *
	 * Called from the constructor
	 */
	protected function init() {}

	/**
	 * Sets the class for the control
	 *
	 * @param string $className
	 */
	public function setClass($className) {
		$this->options ['class'] = $className;
	}

	/**
	 * Returns the value of the option or null if it's not set
	 *
	 * @return mixed
	 */
	public function getOption($option) {
		if (isset ($this->options [$option])) {
			return $this->options [$option];
		}
		return null;
	}

	/**
	 * Returns the control name for a given control element
	 *
	 * @param sys_admin_Control $className
	 * @return string
	 */
	public function getControlNameFromControl($control) {
		preg_match ('/_([A-Z][a-zA-Z0-9]+)$/', get_class($control), $regs);
		return $regs [1];
	}

}
?>