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
 * Inut control interface
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface sys_admin_interface_Input {

	const DISPLAY_BOTH = 0;
	const DISPLAY_ADD = 1;
	const DISPLAY_EDIT = 2;

	/**
	 * Sets the default value for the input
	 *
	 * @param mixed $value
	 */
	public function setDefaultValue($value=null);

	/**
	 * Sets the value for the input.
	 *
	 * If no default value is set before calling this also sets the default value
	 *
	 * @param mixed $value
	 * @param boolean $load If true forces the value to be set without validation
	 * @return boolean True on success, false on failed validation
	 */
	public function setValue($value, $load = false);

	/**
	 * Returns the current value of the input
	 *
	 * @return mixed
	 */
	public function getValue();

	/**
	 * Returns the current errormessage for the input or null if no error has occured
	 *
	 * @return string
	 */
	public function getError();

	/**
	 * Sets the input to read only
	 *
	 * @param boolean $readOnly
	 */
	public function setReadOnly($readOnly = true);

	/**
	 * Sets the input to disabled
	 *
	 * @param boolean $disabled
	 */
	public function setDisabled($disabled = true);

	/**
	 * Sets the label for the input
	 *
	 * @param string $label
	 */
	public function setLabel($label);

	/**
	 * Sets the description for tbe input
	 *
	 * @param string $description
	 */
	public function setDescription($description);

	/**
	 * If set the client should post the value of the input as a variable besides including it in the XML
	 *
	 * @param string $name
	 */
	public function setMapValue($name=null);

	/**
	 * If set to true the input field is required
	 *
	 * @param boolean $required
	 */
	public function setRequired($required=true);

	/**
	 * Sets if the field's values should be trimmed off whitespace on both ends before posting/saving
	 *
	 * If combined with the required option it disallows values with only whitespace to be saved
	 *
	 * @param boolean $trim
	 */
	public function setTrim($trim=true);

	public function setDisplayOn($display);
}
?>