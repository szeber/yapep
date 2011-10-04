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
 * Select input control
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_admin_control_SelectInput extends sys_admin_control_TextInput {

	/**
	 * Stores the options for the input
	 *
	 * @var array
	 */
	protected $valueOptions = array();

	/**
	 * Replaces the options for the input
	 *
	 * The array should be an array in the following format: array('value'=>'label', 'value2'=>'label2'...)
	 *
	 * @param array $options
	 */
	public function addOptions($options) {
		$this->valueOptions = $options;
	}

	/**
	 * Adds a single option to the input
	 *
	 * @param string $label
	 * @param string $value
	 */
	public function addOption($label, $value) {
		$this->valueOptions[$value] = $label;
	}

	/**
	 * Removes an option from the input
	 *
	 * @param string $value
	 */
	public function removeOption($value) {
		unset($this->valueOptions[$value]);
	}

	/**
	 * Clears the possible options for the input
	 *
	 */
	public function clearOptions() {
		$this->valueOptions = array();
	}

	/**
	 * @see sys_admin_control_TextInput::fetchSmarty()
	 *
	 * @return string
	 */
	protected function fetchSmarty() {
		return $this->smarty->fetch ('yapep:admin/control/SelectInput.tpl');
	}

	/**
	 * @see sys_admin_control_TextInput::getXml()
	 *
	 * @return string
	 */
	public function getXml() {
		$this->smarty->assign('valueOptions', $this->valueOptions);
		return parent::getXml();
	}

	/**
	 * Sets the label for the null value in the select
	 *
	 * @param string $label
	 */
	public function setNullValueLabel($label) {
		$this->options['nullValueLabel'] = $label;
	}

	/**
	 * @see sys_admin_control_TextInput::getValue()
	 *
	 * @return mixed
	 */
	public function getValue() {
		if (is_null($this->value) || $this->value == '') {
			return null;
		}
		return parent::getValue();
	}

	/**
	 * Sets the inplace added object type
	 *
	 * @param string $type
	 */
	public function setInplaceType($type) {
		$this->options['inplaceType'] = $type;
	}

}
?>