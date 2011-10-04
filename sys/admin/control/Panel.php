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
 * Panel control
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_admin_control_Panel extends sys_admin_Control implements sys_admin_interface_Container, sys_admin_interface_Dockable, sys_admin_interface_Displayable, sys_admin_interface_Resizable {

	/**
	 * Control objects
	 *
	 * @var array
	 */
	protected $controls = array ();

	/**
	 * @see sys_admin_interface_Container::setAddForm()
	 *
	 * @param boolean $addForm
	 */
	public function setAddForm($addForm = true) {
		if ($addForm) {
			$this->options ['addForm'] = 1;
			return;
		}
		unset ($this->options ['addForm']);
	}

	/**
	 * @see sys_admin_interface_Container::getInput()
	 *
	 * @param string $name
	 * @return sys_admin_interface_Input
	 */
	public function getInput($name) {
		foreach ( $this->controls as $controlName => $control ) {
			if ($name == $controlName && $control instanceof sys_admin_interface_Input) {
				return $control;
			}
			if ($control instanceof sys_admin_interface_Container) {
				$found = $control->getInput ($name);
				if ($found) {
					return $found;
				}
			}
		}
		return false;
	}

	/**
	 * @see sys_admin_interface_Container::checkControlExists()
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function checkControlExists($name) {
		if (isset ($this->controls [$name])) {
			return true;
		}
		return false;
	}

	/**
	 * @see sys_admin_interface_Container::validateControlName()
	 *
	 * @param string $name
	 */
	public function validateControlName($name) {
		return (strlen ($name) >= 2 && strlen ($name) <= 100 && (bool) preg_match ('/^[-_a-zA-Z0-9]+$/', $name));
	}

	/**
	 * @see sys_admin_interface_Container::checkInputExists()
	 *
	 * @param unknown_type $name
	 * @return boolean
	 */
	public function checkInputExists($name) {
		foreach ( $this->controls as $controlName => $control ) {
			if (($controlName == $name && $control instanceof sys_admin_interface_Input) || ($control instanceof sys_admin_interface_Container && $control->checkInputExists ($name))) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @see sys_admin_interface_Container::addControl()
	 *
	 * @param sys_admin_Control $control
	 * @param string $name
	 * @return sys_admin_Control
	 */
	public function addControl(sys_admin_Control $control, $name) {
		if (!$this->validateControlName ($name)) {
			throw new sys_exception_AdminException ('Can\'t add control. Invalid control name: ' . $name, sys_exception_AdminException::ERR_CONTAINER_INVALID_CONTROL_NAME);
		}
		if (isset ($this->controls [$name])) {
			throw new sys_exception_AdminException ('Can\'t add control. Control already exists: ' . $name, sys_exception_AdminException::ERR_CONTAINER_CONTROL_EXISTS);
		}
		// check if the given input name exists in any sub container if unit testing or debugging
		if ($control instanceof sys_admin_interface_Input && (DEBUGGING || (defined ('UNIT_TEST') && UNIT_TEST))) {
			if ($this->checkInputExists ($name)) {
				throw new sys_exception_AdminException ('Can\'t add control. Input name already in use: ' . $name, sys_exception_AdminException::ERR_CONTAINER_INPUT_NAME_IN_USE);
			}
		}
		$this->controls [$name] = $control;
		return $control;
	}

	/**
	 * @see sys_admin_interface_Container::deleteControl()
	 *
	 * @param string $name
	 */
	public function deleteControl($name) {
		if (!isset ($this->controls [$name])) {
			throw new sys_exception_AdminException ('Can\'t delete control. Control doesn\'t exist: ' . $name, sys_exception_AdminException::ERR_CONTAINER_CONTROL_DOES_NOT_EXIST);
		}
		unset ($this->controls [$name]);
	}

	/**
	 * @see sys_admin_interface_Container::getAllControls()
	 *
	 * @return array
	 */
	public function getAllControls() {
		return $this->controls;
	}

	/**
	 * @see sys_admin_interface_Container::getControl()
	 *
	 * @param string $name
	 * @return sys_admin_Control
	 */
	public function getControl($name) {
		if (!isset ($this->controls [$name])) {
			throw new sys_exception_AdminException ('Can\'t find control. Control doesn\'t exist: ' . $name, sys_exception_AdminException::ERR_CONTAINER_CONTROL_DOES_NOT_EXIST);
		}
		return $this->controls [$name];
	}

	/**
	 * @see sys_admin_interface_Container::getControlNames()
	 *
	 * @return array
	 */
	public function getControlNames() {
		return array_keys ($this->controls);
	}

	/**
	 * @see sys_admin_interface_Container::getInputNames()
	 *
	 * @return array
	 */
	public function getInputNames() {
		$names = array ();
		foreach ( $this->controls as $name => $control ) {
			if ($control instanceof sys_admin_interface_Input) {
				$names [] = $name;
			} elseif ($control instanceof sys_admin_interface_Container) {
				$names = array_merge ($names, $control->getInputNames ());
			}
		}
		sort ($names);
		return $names;
	}

	/**
	 * @see sys_admin_interface_Container::getInputValues()
	 *
	 * @return array
	 */
	public function getInputValues($assignControls=false) {
		$values = array ();
		foreach ( $this->controls as $name => $control ) {
			if ($control instanceof sys_admin_interface_Input) {
				if ($assignControls) {
					$values [$name] = $control;
				} else {
					$values [$name] = $control->getValue ();
				}
			} elseif ($control instanceof sys_admin_interface_Container) {
				$values += $control->getInputValues ($assignControls);
			}
		}
		return $values;
	}

	/**
	 * @see sys_admin_interface_Container::setBackgroundImage()
	 *
	 * @param string $url
	 */
	public function setBackgroundImage($url) {
		$this->options ['backgroundImage'] = $url;
	}

	/**
	 * @see sys_admin_interface_Container::setLabel()
	 *
	 * @param string $label
	 */
	public function setLabel($label) {
		$this->options ['label'] = $label;
	}

	/**
	 * @see sys_admin_interface_Control::getXml()
	 *
	 * @return string
	 */
	public function getXml() {
		$this->smarty->assign ('options', $this->options);
		$this->smarty->assign ('controls', $this->controls);
		return $this->fetchSmarty ();
	}

	/**
	 * Fetches the XML from Smarty
	 *
	 * @return string
	 */
	protected function fetchSmarty() {
		return $this->smarty->fetch ('yapep:admin/control/Panel.tpl');
	}

	/**
	 * @see sys_admin_interface_Dockable::setDock()
	 *
	 * @param integer $dockMode
	 */
	public function setDock($dockMode) {
		if (is_null ($dockMode)) {
			unset ($this->options ['dockMode']);
			return;
		}
		$this->options ['dockMode'] = $dockMode;
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
	 * @see sys_admin_interface_Container::setInputValues()
	 *
	 * @param array $values
	 * @param boolean $load If true forces the value to be set without validation
	 *
	 */
	public function setInputValues(&$values, $load = false) {
		$success = true;
		foreach ( $this->controls as $name => $control ) {
			if ($control instanceof sys_admin_interface_Input && isset ($values [$name])) {
				$result = $control->setValue ($values [$name], $load);
				unset ($values [$name]);
			} else if ($control instanceof sys_admin_interface_Container) {
				$result = $control->setInputValues ($values, $load);
			} else {
				$result = true;
			}
			if (!$result) {
				$success = false;
			}
		}
		return $success;
	}

	/**
	 * @see sys_admin_interface_Resizable::setResizable()
	 *
	 * @param boolean $resizable
	 */
	public function setResizable($resizable = true) {
		if ($resizable) {
			$this->options ['resizable'] = 1;
			return;
		}
		unset ($this->options ['resizable']);
	}

	/**
	 * @see sys_admin_interface_Container::getValuesXml()
	 *
	 * @return string
	 */
	public function getValuesXml() {
		$this->smarty->assign ('valuesOnly', true);
		$this->smarty->assign ('values', $this->getInputValues (true));
		$this->smarty->assign ('inputErrors', $this->getInputErrors());
		return $this->getXml ();
	}

	/**
	 * @see sys_admin_Control::setDefaults()
	 */
	protected function setDefaults() {
		$this->options ['dockMode'] = sys_admin_control_Panel::DOCK_CLIENT;
	}

	/**
	 * @see sys_admin_interface_Container::getInputErrors()
	 *
	 * @return array
	 */
	public function getInputErrors() {
		$errors = array ();
		foreach ( $this->controls as $name => $control ) {
			if ($control instanceof sys_admin_interface_Input) {
				$error = $control->getError ();
				if ($error) {
					$errors [$name] = $error;
				}
			} elseif ($control instanceof sys_admin_interface_Container) {
				$errors += $control->getInputErrors ();
			}
		}
		return $errors;
	}

	/**
	 * @see sys_admin_interface_Container::setListTarget()
	 *
	 * @param string $listTarget
	 */
	public function setListTarget($listTarget) {
		if ($listTarget) {
			$this->options ['listTarget'] = $listTarget;
			return;
		}
		unsert ($this->options ['listTarget']);
	}

	public function setTitle($title) {
		if ($title) {
			$this->options ['title'] = $title;
			return;
		}
		unset ($this->options ['title']);
	}

	public function setTitleField($titleField) {
		if ($titleField) {
			$this->options ['titleField'] = $titleField;
			return;
		}
		unset ($this->options ['titleField']);
	}

	public function setMaximized($maximized = true) {
		if ($maximized) {
			$this->options['maximized'] = 1;
			return;
		}
		unset($this->options['maximized']);
	}

	public function refreshForm($formName) {
		$this->options ['refreshForm'] = $formName;
	}
}
?>