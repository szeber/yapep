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
 * Sub item list control
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_admin_control_SubItemList extends sys_admin_control_TextInput {

	/**
	 * Stores the name field's name
	 *
	 * @var string
	 */
	protected $nameField;

	/**
	 * Stores the value field's name
	 *
	 * @var string
	 */
	protected $valueField;

	/**
	 * @see sys_admin_control_TextInput::setNormalValue()
	 *
	 * @param mixed $value
	 * @param boolean $load
	 * @return boolean
	 */
	protected function setNormalValue($value, $load = false) {
		if (!$load && get_class($this) == 'sys_admin_control_SubItemList') {
			return true;
		}
		if (!$this->nameField || !$this->valueField) {
			throw new sys_exception_AdminException(_('Required options not set for control'), sys_exception_AdminException::ERR_REQUIRED_OPTIONS_NOT_SET_FOR_CONTROL);
		}
		if (is_string($value) || is_null($value)) {
			$value2 = explode(',', $value);
			if (count($value2)) {
				$value = array();
				foreach($value2 as $val) {
					if (!$val) {
						continue;
					}
					$value[$val] = $val;
				}
			}
			return parent::setNormalValue($value, $load);
		} else {
			if (is_object($value)) {
				$value->toArray();
			}
			$value2 = array();
			foreach($value as $val) {
				$value2[$val[$this->nameField]] = $val[$this->valueField];
			}
			return parent::setNormalValue($value2, $load);
		}
	}

	/**
	 * Sets the name of the subform to use
	 *
	 * @param string $formname
	 */
	public function setSubForm($formname) {
		$this->options['subForm'] = $formname;
	}

	/**
	 * Sets the field that will set the name for the item
	 *
	 * This should be 'id' in most cases
	 *
	 * @param string $field
	 */
	public function setNameField($field) {
		$this->nameField = $field;
	}

	/**
	 * Sets the field that will set the value for the item
	 *
	 * This should be 'name' or 'description' or similar
	 *
	 * @param string $field
	 */
	public function setValueField($field) {
		$this->valueField = $field;
	}

	/**
	 * Sets the label for the add new item field
	 *
	 * @param string $label
	 */
	public function setAddFieldLabel($label) {
		$this->options['addFieldLabel'] = $label;
	}

	/**
	 * Sets if the list should only contain one item
	 *
	 * @param boolean $single
	 */
	public function setSingleItem($single = true) {
		if ($single) {
			$this->options['singleItem'] = true;
			return;
		}
		unset($this->options['singleItem']);
	}

	/**
	 * Sets the name of the field that provides the label for the items
	 *
	 * @param string $field
	 */
	public function setDisplayField($field) {
		$this->options['displayField'] = $field;
	}

	/**
	 * @see sys_admin_control_TextInput::setDefaults()
	 *
	 */
	protected function setDefaults() {
		parent::setDefaults();
		$this->setDisplayField('name');
	}

}
?>