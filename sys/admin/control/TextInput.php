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
 * Text input control
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_admin_control_TextInput extends sys_admin_Control implements sys_admin_interface_Displayable, sys_admin_interface_Input, sys_admin_interface_Positionable {

	/**
	 * The input's value
	 *
	 * @var mixed
	 */
	protected $value;

	/**
	 * The input's label
	 *
	 * @var string
	 */
	protected $label = '';

	/**
	 * The input's description
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * Array containing validatior functions to be run
	 *
	 * @var array
	 */
	protected $validators = array ();

	/**
	 * Stores the current error message or null
	 *
	 * @var string
	 */
	protected $error = null;

	/**
	 * In BoxMode sets whether inheritance is enabled for this param
	 *
	 * @var boolean
	 */
	protected $boxIsInherited = false;

	/**
	 * In BoxMode sets if the value is inherited
	 *
	 * @var boolean
	 */
	protected $boxUseInherited = false;

	/**
	 * In BoxMode sets whether the value can be a variable
	 *
	 * @var boolean
	 */
	protected $boxAllowVariable = false;

	/**
	 * In BoxMode sets the value to a variable
	 *
	 * @var boolean
	 */
	protected $boxIsVariable = false;

	/**
	 * Enables or disables BoxMode
	 *
	 * Box mode is used for controls used by the Page editor admin module
	 *
	 * @var boolean
	 */
	protected $boxMode = false;

	/**
	 * @see sys_admin_interface_Input::setRequired()
	 *
	 * @param boolean $required
	 */
	public function setRequired($required = true) {
		if (!$required) {
			unset ($this->options ['required']);
			unset ($this->validators ['required']);
			return;
		}
		$this->options ['required'] = 1;
		$this->validators ['required'] = array ('class' => 'Text', 'func' => 'required', 'params' => array (), 'msg' => _ ('Field is required'));
	}

	/**
	 * @see sys_admin_interface_Input::setTrim()
	 *
	 * @param boolean $trim
	 */
	public function setTrim($trim = true) {
		if (!$trim) {
			unset ($this->options ['trim']);
			return;
		}
		$this->options ['trim'] = 1;
	}

	/**
	 * @see sys_admin_interface_Control::getXml()
	 *
	 * @return string
	 */
	public function getXml() {
		$this->smarty->assign ('options', $this->options);
		$this->smarty->assign ('value', $this->value);
		$this->smarty->assign ('label', $this->label);
		$this->smarty->assign ('description', $this->description);
		return $this->fetchSmarty ();
	}

	/**
	 * Fetches the template from smarty
	 *
	 * @return string
	 */
	protected function fetchSmarty() {
		return $this->smarty->fetch ('yapep:admin/control/TextInput.tpl');
	}

	/**
	 * @see sys_admin_interface_Input::getValue()
	 *
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @see sys_admin_interface_Input::setMapValue()
	 *
	 * @param string $name
	 */
	public function setMapValue($name = null) {
		if (is_null ($name)) {
			unset ($this->options ['mapValue']);
			return;
		}
		$this->options ['mapValue'] = $name;
	}

	/**
	 * @see sys_admin_interface_Input::setDefaultValue()
	 *
	 * @param mixed $value
	 */
	public function setDefaultValue($value = null) {
		if (is_null ($value)) {
			unset ($this->options ['defaultValue']);
			return;
		}
		if (is_null($this->value)) {
			$this->value = $value;
		}
		$this->options ['defaultValue'] = $value;
	}

	/**
	 * Enables BoxMode for the control
	 *
	 * The BoxMode should only be enabled if the control is going to be used as a box parameter in the page editor
	 */
	public function enableBoxMode() {
		$this->boxMode = true;
		$this->smarty->assign('boxMode', 1);
		$this->smarty->assign_by_ref('boxAllowVariable', $this->boxAllowVariable);
		$this->smarty->assign_by_ref('boxIsInherited', $this->boxIsInherited);
		$this->smarty->assign_by_ref('boxIsVariable', $this->boxIsVariable);
		$this->smarty->assign_by_ref('boxUseInherited', $this->boxUseInherited);
		$this->options['boxMode'] = 1;
	}

	/**
	 * @see sys_admin_interface_Input::setDisabled()
	 *
	 * @param boolean $disabled
	 */
	public function setDisabled($disabled = true) {
		if (!$disabled) {
			unset ($this->options ['disabled']);
			return;
		}
		$this->options ['disabled'] = true;
	}

	/**
	 * @see sys_admin_interface_Input::setLabel()
	 *
	 * @param string $label
	 */
	public function setLabel($label) {
		$this->label = $label;
	}

	/**
	 * @see sys_admin_interface_Input::setDescription()
	 *
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * @see sys_admin_interface_Input::setReadOnly()
	 *
	 * @param boolean $readOnly
	 */
	public function setReadOnly($readOnly = true) {
		if (!$readOnly) {
			unset ($this->options ['readOnly']);
			return;
		}
		$this->options ['readOnly'] = true;
	}

	/**
	 * Sets the value for BoxMode
	 *
	 * @param mixed $value
	 * @param boolean $load
	 * @return boolean
	 */
	public function setBoxValue($value, $load=false) {
	    $usedFields = array(
	        'isInherited',
	        'allowVariable',
	        'isVariable',
	        'useInherited',
	        'value'
	    );

	    foreach($usedFields as $field) {
	        if (!isset($value[$field])) {
	            $value[$field] = null;
	        }
	    }

		if ($load) {
			$this->boxIsInherited = (bool)$value['isInherited'];
			$this->boxAllowVariable = !(!isset($value['allowVariable']) || !(bool)$value['allowVariable']);
		}
		$this->boxIsVariable = (bool)$value['isVariable'];
		$this->boxUseInherited= (bool)$value['useInherited'];
		$result = $this->setValue($value['value'], $load);
		return $result;
	}

	/**
	 * @see sys_admin_interface_Input::setValue()
	 *
	 * @param mixed $value
	 * @param boolean $load If true forces the value to be set without validation
	 * @return boolean
	 * 	 */
	final public function setValue($value, $load = false) {
		return $this->setNormalValue($value, $load);
	}

	/**
	 * Returns the value for the control if BoxMode is disabled
	 *
	 * @param mixed $value
	 * @param boolean $load
	 * @return boolean
	 */
	protected function setNormalValue($value, $load = false) {
		if ($load) {
			$this->value = $value;
			return true;
		}
		if (isset($this->options ['readOnly']) && $this->options['readOnly']) {
			return true;
		}
		if (isset($this->options ['trim']) && $this->options ['trim']) {
			$value = trim ($value);
		}
		if ($this->validateValue ($value)) {
			$this->value = $value;
			return true;
		}
		return false;
	}

	protected function validateValue($value) {
		foreach ( $this->validators as $validator ) {
			$classname = 'sys_admin_validator_' . $validator ['class'];
			$funcname = $validator ['func'];
			if (!(call_user_func (array ($classname, $funcname), $value, $validator ['params']))) {
				$this->error = $validator ['msg'];
				return false;
			}
		}
		return true;
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
	 * @see sys_admin_interface_Displayable::setHeight()
	 *
	 * @param integer $height
	 */
	public function setHeight($height = null) {
		if (is_null ($height)) {
			unset ($this->options ['height']);
			return;
		}
		$this->options ['height'] = $height;
	}

	/**
	 * @see sys_admin_interface_Displayable::setHidden()
	 *
	 * @param boolean $hidden
	 */
	public function setHidden($hidden = true) {
		if ($hidden) {
			$this->options ['hidden'] = true;
		} else {
			unset ($this->options ['hidden']);
		}
	}

	/**
	 * @see sys_admin_interface_Displayable::setWidth()
	 *
	 * @param integer $width
	 */
	public function setWidth($width = null) {
		if (is_null ($width)) {
			unset ($this->options ['width']);
			return;
		}
		$this->options ['width'] = $width;
	}

	/**
	 * @see sys_admin_interface_Input::getError()
	 *
	 * @return string
	 */
	public function getError() {
		return $this->error;
	}

	public function setValidateNotEmpty($validate = true) {
		if (!$validate) {
			unset ($this->options ['validateNotEmpty']);
			unset ($this->validators ['notEmpty']);
			return;
		}
		$this->options ['validateNotEmpty'] = 1;
		$this->validators ['notEmpty'] = array ('class' => 'Text', 'func' => 'notEmpty', 'params' => array (), 'msg' => _ ('Field must not be empty'));
	}

	public function setValidateRegex($pattern) {
		if (!$pattern) {
			unset ($this->options ['validateRegex']);
			unset ($this->validators ['regex']);
			return;
		}
		$this->options ['validateRegex'] = $pattern;
		$this->validators ['regex'] = array ('class' => 'Text', 'func' => 'regex', 'params' => array ('pattern' => $pattern), 'msg' => _ ('Field value does not match required pattern'));
	}

	public function setValidateEmail($validate = true) {
		if (!$validate) {
			unset ($this->options ['validateNotEmpty']);
			unset ($this->validators ['email']);
			return;
		}
		$this->options ['validateRegex'] = $pattern;
		$this->validators ['email'] = array ('class' => 'Text', 'func' => 'email', 'params' => array (), 'msg' => _ ('Field value is not a valid e-mail address'));
	}

	public function setValidateNumeric($validate = true) {
		if (!$validate) {
			unset ($this->options ['validateNumeric']);
			unset ($this->validators ['numeric']);
			return;
		}
		$this->options ['validateNumeric'] = 1;
		$this->validators ['numeric'] = array ('class' => 'Text', 'func' => 'isNumeric', 'params' => array (), 'msg' => _ ('Field value is not numeric'));
	}

	public function setValidateMaxLength($length) {
		if (!$length) {
			unset ($this->options ['validateMaxLength']);
			unset ($this->validators ['maxLength']);
			return;
		}
		$this->options ['validateMaxLength'] = $length;
		$this->validators ['maxLength'] = array ('class' => 'Text', 'func' => 'maxLength', 'params' => array ('length' => $length), 'msg' => _ ('Field value is too long'));
	}

	public function setValidateMinLength($length) {
		if (!$length) {
			unset ($this->options ['validateMinLength']);
			unset ($this->validators ['minLength']);
			return;
		}
		$this->options ['validateMinLength'] = $length;
		$this->validators ['minLength'] = array ('class' => 'Text', 'func' => 'minLength', 'params' => array ('length' => $length), 'msg' => _ ('Field value is too short'));
	}

	public function setDefaultFocused($focused = true) {
		if (!$focused) {
			unset($this->options['defaultFocused']);
			return;
		}
		$this->options['defaultFocused'] = 1;
	}

	public function setDisplayOn($display) {
		$this->options['displayOn'] = (int)$display;
	}

	/**
	 * @see sys_admin_Control::setDefaults()
	 *
	 */
	protected function setDefaults() {
		$this->options['displayOn'] = sys_admin_interface_Input::DISPLAY_BOTH;
	}

	/**
	 * @return boolean
	 */
	public function getBoxAllowVariable() {
		return $this->boxAllowVariable;
	}

	/**
	 * @return boolean
	 */
	public function getBoxIsInherited() {
		return $this->boxIsInherited;
	}

	/**
	 * @return boolean
	 */
	public function getBoxIsVariable() {
		return $this->boxIsVariable;
	}

	/**
	 * @return boolean
	 */
	public function getBoxMode() {
		return $this->boxMode;
	}

	/**
	 * @return boolean
	 */
	public function getBoxUseInherited() {
		return $this->boxUseInherited;
	}


}
?>