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
 * Container control interface
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface sys_admin_interface_Container {

	/**
	 * Adds a control to the container and returns it
	 *
	 * @param sys_admin_Control $control
	 * @param string $name
	 * @return sys_admin_Control
	 */
	public function addControl(sys_admin_Control $control, $name);

	/**
	 * Returns the control specified by $name
	 *
	 * @param string $name
	 * @return sys_admin_Control
	 */
	public function getControl($name);

	/**
	 * Removes the specified control from the container
	 *
	 * @param string $name
	 */
	public function deleteControl($name);

	/**
	 * Returns the names of all controls in the container
	 *
	 * @return array
	 */
	public function getControlNames();

	/**
	 * Returns true if the specified control exists in this container
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function checkControlExists($name);

	/**
	 * Returns all controls as an associative array with the control names as the keys
	 *
	 * @return array
	 */
	public function getAllControls();

	/**
	 * Sets the background image for the container
	 *
	 * @param string $url
	 */
	public function setBackgroundImage($url);

	/**
	 * Sets the label for the container
	 *
	 * @param string $label
	 */
	public function setLabel($label);

	/**
	 * Returns the names of all the inputs defined in this container and it's sub containers
	 *
	 * @return array
	 */
	public function getInputNames();

	/**
	 * Returns the names and values of all inputs defined in this container and its sub containers
	 *
	 * @return array
	 */
	public function getInputValues();

	/**
	 * Sets the values of the inputs in the container and it's sub containers
	 *
	 * @param array $values
	 * @param boolean $load If true forces the value to be set without validation
	 * @return boolean True if every input validated correctly false on validation errors
	 */
	public function setInputValues(&$values, $load = false);

	/**
	 * Returns the input control specified by name or false if it's not found
	 *
	 * @param string $name
	 * @return sys_admin_interface_Input
	 */
	public function getInput($name);

	/**
	 * Returns an array with all input errors in the container and it' sub containers
	 *
	 * @return array
	 */
	public function getInputErrors();

	/**
	 * Checks if a given input exists in this container or it's sub containers
	 *
	 * @var string $name
	 * @return boolean
	 */
	public function checkInputExists($name);

	/**
	 * Returns true if the given name is valid for a control name
	 *
	 * @param string $name
	 */
	public function validateControlName($name);

	/**
	 * Retruns the xml containing the input values
	 *
	 * @return string
	 */
	public function getValuesXml();

	/**
	 * Sets if the panel should contain a form element too
	 *
	 * @param boolean $addForm
	 */
	public function setAddForm($addForm=true);

	/**
	 * Sets the current container as a list target at the specified name
	 *
	 * @param string $listTarget
	 */
	public function setListTarget($listTarget);
}
?>