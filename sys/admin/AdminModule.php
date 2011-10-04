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
 * Admin module base class
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
abstract class sys_admin_AdminModule {

	const MODE_ADD = 0;

	const MODE_EDIT = 1;

	const MODE_FORM = 2;

	const MODE_DELETE = 3;

	/**
	 * Stores the current operating mode
	 *
	 * @var integer
	 */
	protected $mode;

	/**
	 * Stores the smarty instance
	 *
	 * @var Smarty
	 */
	protected $smarty;

	/**
	 * Stores the ID for the edited object
	 *
	 * @var integer
	 */
	protected $id;

	/**
	 * The site's locale
	 *
	 * @var string
	 */
	protected $locale;

	/**
	 * The site's locale ID
	 *
	 * @var integer
	 */
	protected $localeId;

	/**
	 * The admin manager that instantiated the class
	 *
	 * @var sys_admin_AdminManager
	 */
	protected $manager;

	/**
	 * Config instance
	 *
	 * @var sys_IApplicationConfiguration
	 */
	protected $config;

	/**
	 * Stores the panel control
	 *
	 * @var sys_admin_control_Panel
	 */
	protected $panel;

	/**
	 * Stores the admin options
	 *
	 * @var array
	 */
	protected $options = array ();

	/**
	 * Stores the database handler object
	 *
	 * @var module_db_interface_Admin
	 */
	private $dbHandler;

	/**
	 * Stores the warning messages
	 *
	 * @var array
	 */
	protected $warnings = array();

	/**
	 * Stores the logged in user's ID
	 *
	 * @var integer
	 */
	protected $userId;

	/**
	 * Stores the SimpleXMLElement object
	 *
	 * @var SimpleXMLElement
	 */
	protected $xml;

	/**
	 * Stores the submodule type identifiers
	 *
	 * This is used mainly in the Doc module
	 *
	 * @var array
	 */
	protected $subModule = array();

	/**
	 * Stores the data loaded by the module
	 *
	 * @var array
	 */
	protected $data = array ();

	/**
	 * Stores the attributes received with the data items
	 *
	 * @var array
	 */
	protected $dataAttrs = array ();

	/**
	 * Stores the array type data fields as arrays that are loaded from the XML
	 *
	 * @var array
	 */
	protected $arrData = array();

	/**
	 * Stores the name of the form that should be called in case of successful addition
	 *
	 * Should be set by descendant classes if needed
	 *
	 * @var string
	 */
	protected $newForm = null;

	/**
	 * Constructor
	 *
	 * @param sys_admin_AdminManager $manager
	 * @param sys_IApplicationConfiguration $config
	 */
	final public function __construct(sys_admin_AdminManager $manager, sys_IApplicationConfiguration $config = null) {
		if (is_null($config)) {
			$this->config = sys_ApplicationConfiguration::getInstance();
		} else {
			$this->config = $config;
		}
		$this->manager = $manager;
		$this->panel = new sys_admin_control_Panel ();
		$this->panel->setAddForm (true);
		$this->userId = $_SESSION ['LoggedInAdminData'] ['UserId'];
		preg_match ('/^module_admin_(.*)$/', get_class ($this), $name);
		$this->setName ($name [1]);
		$this->smarty = sys_LibFactory::getSmarty ();
		$this->init ();
		$this->setSaveBtnText (_ ('Save'));
		$this->setCloseBtnText (_ ('Close'));
		$this->setDeleteBtnText (_ ('Delete'));
		$this->setAddBtnText (_ ('New'));
	}

	/**
	 * Shortcut function to $this->panel->addControl
	 *
	 * @param sys_admin_Control $control
	 * @param string $name
	 * @return sys_admin_Control
	 * @see sys_admin_control_Panel::addControl()
	 */
	protected function addControl(sys_admin_Control $control, $name) {
		return $this->panel->addControl ($control, $name);
	}

	/**
	 * Shortcut function to $this->panel->deleteControl
	 *
	 * @param sys_admin_Control $control
	 * @param string $name
	 * @see sys_admin_control_Panel::deleteControl()
	 */
	protected function deleteControl($name) {
		$this->panel->deleteControl ($name);
	}

	/**
	 * Shortcut function to $this->panel->getControl
	 *
	 * @param sys_admin_Control $control
	 * @param string $name
	 * @see sys_admin_control_Panel::getControl()
	 */
	protected function getControl($name) {
		return $this->panel->getControl ($name);
	}

	/**
	 * Shortcut function to $this->panel->getAllControls
	 *
	 * @param sys_admin_Control $control
	 * @param string $name
	 * @see sys_admin_control_Panel::getAllControls()
	 */
	protected function getAllControls() {
		return $this->panel->getAllControls ();
	}

	/**
	 * Sets the database handler object
	 *
	 * @param module_db_interface_Admin $handler
	 */
	final protected function setDbHandler(module_db_interface_Admin $handler) {
		$this->dbHandler = $handler;
	}

	/**
	 * Executes the module
	 */
	final public function execute() {
		$this->manager->runEvent ('preExecute', array ('userId' => $this->userId, 'module' => get_class ($this), 'mode' => $this->mode, 'id' => $this->id, 'subModule' => $this->subModule));
		$this->doExecute ();
		$xml = $this->getXml ();
		$this->manager->runEvent ('postExecute', array ('userId' => $this->userId));
		return $xml;
	}

	final protected function doExecute() {
		switch ( $this->mode) {
			case sys_admin_AdminModule::MODE_EDIT :
				$this->manager->runEvent ('preBuild');
				$this->buildForm ();
				$this->manager->runEvent ('postBuild');
				$this->loadValues ();
				$this->saveValues ();
				$this->loadValues ();
				break;
			case sys_admin_AdminModule::MODE_ADD :
				$this->manager->runEvent ('preBuild');
				$this->buildForm ();
				$this->manager->runEvent ('postBuild');
				$this->saveValues ();
				if ($this->id) {
					$this->loadValues ();
				}
				break;
			case sys_admin_AdminModule::MODE_FORM :
				$this->manager->runEvent ('preBuild');
				$this->buildForm ();
				$this->manager->runEvent ('postBuild');
				break;
			case sys_admin_AdminModule::MODE_DELETE :
				$this->manager->runEvent ('preBuild');
				$this->buildForm ();
				$this->manager->runEvent ('postBuild');
				$this->deleteItem();
				break;
			default :
				throw new sys_exception_AdminException (_ ('Module mode not set'), sys_exception_AdminException::ERR_MODULE_MODE_NOT_SET);
				break;
		}
	}

	/**
	 * Parses the XML data
	 *
	 * @param SimpleXMLElement $xml
	 */
	public function parseXml(SimpleXMLElement $xml) {
		$this->manager->runEvent ('preParse');
		preg_match ('@^([-_a-zA-Z0-9]+)(/(.*))?$@', urldecode ((string) $xml->adminData->name), $name);
		if ($name [3]) {
			$this->subModule = explode('/',$name [3]);
		}
		if (!isset ($xml->adminData->id) || !(string)$xml->adminData->id) {
			$this->options ['mode'] = 'Add';
			$this->mode = sys_admin_AdminModule::MODE_ADD;
		} else if (isset ($xml->adminData->delete)) {
			$this->options ['mode'] = 'Delete';
			$this->id = (int) $xml->adminData->id;
			$this->options ['id'] = $this->id;
			$this->mode = sys_admin_AdminModule::MODE_DELETE;
		} else if ('form' == $xml->adminData->id) {
			$this->options ['mode'] = 'Form';
			$this->mode = sys_admin_AdminModule::MODE_FORM;
		} else if ((int) $xml->adminData->id == (string) $xml->adminData->id) {
			$this->options ['mode'] = 'Edit';
			$this->id = (int) $xml->adminData->id;
			$this->options ['id'] = $this->id;
			$this->mode = sys_admin_AdminModule::MODE_EDIT;
		} else {
			throw new sys_exception_AdminException ('The requested ID is not found', sys_exception_AdminException::ERR_ID_NOT_FOUND);
		}
		$this->options ['formName'] = urldecode ((string) $xml->adminData->name);
		$this->xml = $xml;
		$this->manager->runEvent ('postParse');
	}

	/**
	 * Loads the values from the database
	 *
	 */
	final private function loadValues() {
		$this->manager->runEvent ('preLoad');
		if ($this->dbHandler) {
			$this->data = $this->dbHandler->loadItem ($this->id);
		} else {
			$this->data = $this->doLoad ();
		}
		if (is_object ($this->data)) {
			$this->data = $this->data->toArray ();
		}
		$this->processLoadData ();
		$this->panel->setInputValues ($this->data, true);
		$this->manager->runEvent ('postLoad');
	}

	/**
	 * Deletes an item from the database
	 *
	 */
	final private function deleteItem() {
		if ($this->options['deleteBtnDisable']) {
			return;
		}
		$this->manager->runEvent ('preDelete');
		if ($this->dbHandler) {
			$this->data = $this->dbHandler->deleteItem ($this->id);
		} else {
			$this->data = $this->doDelete ();
		}
		$this->options ['deleteSuccess'] = 1;
		$this->postDelete ();
		$this->manager->runEvent ('postDelete');
	}

	/**
	 * If receiving posted values tries to save them to the database
	 */
	final private function saveValues() {
		if (!count ($this->xml->data->value)) {
			return;
		}
		$this->data = array ();
		$this->dataAttrs = array();
		foreach ( $this->xml->data->value as $item ) {
			$attrs = array();
			foreach($item->attributes() as $key => $val) {
				$attrs[(string)$key] = (string)$val;
			}
			$this->dataAttrs[(string)$item['name']] = $attrs;
			if (count($item)) {
				$itemArr = array();
				foreach ($item as $val) {
					$itemArr[] = (string) $val;
				}
				$this->data [(string) $item ['name']] = implode(',', $itemArr);
			} else {
				$this->data [(string) $item ['name']] = (string)$item;
			}
		}
		$success = $this->panel->setInputValues ($this->data);
		$errorMessage = _('Validation failed');
		$this->manager->runEvent ('preSave');
		$this->data = $this->panel->getInputValues ();
		try {
			$this->processSaveData ();
		} catch (sys_exception_AdminException $e) {
			$success = false;
			$errorMessage = $e->getMessage();
		}
		if ($success) {
			if ($this->dbHandler) {
				if ($this->mode == sys_admin_AdminModule::MODE_ADD) {
					$result = $this->dbHandler->insertItem ($this->data);
					if ($result && is_numeric ($result)) {
						$this->options ['newId'] = $result;
						$this->id = $result;
						if ($this->newForm) {
							$this->options ['newForm'] = $this->newForm;
						}
						$result = '';
					} elseif (!$result) {
						$result = 'Insert error';
					}
				} else {
					$result = $this->dbHandler->updateItem ($this->id, $this->data);
				}
			} else {
				$result = $this->doSave ();
			}
			if ($result) {
				$this->options ['saveSuccess'] = 0;
				$this->options ['saveError'] = $result;
			} else {
				$this->options ['saveSuccess'] = 1;
				$this->postSave ();
			}
		} else {
			$this->options ['saveSuccess'] = 0;
			$this->options ['saveError'] = $errorMessage;
		}
		$this->manager->runEvent ('postSave');
	}

	/**
	 * Displays the XML
	 *
	 * @return string
	 */
	protected function getXml() {
		$this->manager->runEvent ('preXml');
		if (sys_admin_AdminModule::MODE_FORM == $this->mode) {
			$this->smarty->assign ('adminContent', $this->panel->getXml ());
		} else {
			$this->smarty->assign ('adminContent', $this->panel->getValuesXml ());
		}
		$this->smarty->clear_assign ('options');
		$this->smarty->assign ('options', $this->options);
		$this->smarty->assign('warnings', $this->warnings);
		$xml = $this->smarty->fetch ('yapep:admin/admin.tpl');
		$this->manager->runEvent ('postXml');
		return $xml;
	}

	/**
	 * Returns the panel object
	 *
	 * @return sys_admin_control_Panel
	 */
	public function getPanel() {
		$this->buildForm ();
		return $this->panel;
	}

	/**
	 * @param integer $id
	 */
	public function setId($id) {
		$this->id = (int) $id;
	}

	/**
	 * @param string $locale
	 */
	public function setLocale($locale) {
		$localeHandler = getPersistClass('LangLocale');
		$localeData = $localeHandler->getLocaleByCode($locale);
		$this->locale = $locale;
		$this->localeId = $localeData['id'];
	}

	/**
	 * @param integer $mode
	 */
	public function setMode($mode) {
		$this->mode = $mode;
	}

	/**
	 * Disables the form tag
	 *
	 */
	public function disableFormTag() {
		$this->panel->setAddForm (false);
	}

	/**
	 * Sets if the form should be reloaded on successful data save
	 *
	 * @param boolean $reload
	 */
	protected function setReloadOnSave($reload = true) {
		if ($reload) {
			$this->options ['reloadOnSave'] = 1;
			return;
		}
		unset ($this->options ['reloadOnSave']);
	}

	/**
	 * Sets the current form to be the root form
	 *
	 * This clears all cached forms on the client and forces everything to be reloaded
	 *
	 * @param boolean $root
	 */
	protected function setRootForm($root = true) {
		if ($root) {
			$this->options ['rootForm'] = 1;
			return;
		}
		unset ($this->options ['rootForm']);
	}

	/**
	 * Sets the name for this form
	 *
	 */
	protected function setName($name) {
		if ($name) {
			$this->options ['name'] = $name;
			return;
		}
		unset ($this->options ['name']);
	}

	protected function setTitle($title) {
		$this->panel->setTitle ($title);
	}

	protected function setTitleField($titleField) {
		$this->panel->setTitleField ($titleField);
	}

	/**
	 * Adds controls to the form
	 *
	 * To be implemented in child classes
	 */
	abstract protected function buildForm();

	/**
	 * Processes a form's loaded data
	 *
	 * To be implemented in descendant classes if needed
	 *
	 */
	protected function processLoadData() {}

	/**
	 * Processes a forms data before saving if
	 *
	 * To be implemented in descendant classes if needed
	 */
	protected function processSaveData() {}

	/**
	 * Saves the data if there is no dbHandler set
	 *
	 * @return string Empty string on success, or the error message on failure
	 * @throws sys_exception_AdminException
	 */
	protected function doSave() {
		if ($this->mode == sys_admin_AdminModule::MODE_ADD) {
			$result = $this->doInsert();
			if ($result && is_numeric ($result)) {
				$this->options ['newId'] = $result;
				$this->id = $result;
				if ($this->newForm) {
					$this->options ['newForm'] = $this->newForm;
				}
				$result = '';
			} elseif (!$result) {
				$result = 'Insert error';
			}
		} else {
			$result = $this->doUpdate();
		}
		return $result;
	}

    /**
     * Updates the data if there is no dbHandler set
     *
     * @return string Enpty string on success, or the error message on failure
     * @throws sys_exception_AdminException
     */
    protected function doUpdate() {
        throw new sys_exception_AdminException (_ ('Data updating not implemented'), sys_exception_AdminException::ERR_SAVING_NOT_IMPLEMENTED);
    }


    /**
     * Inserts the data if there is no dbHandler set
     *
     * @return string The inserted ID on success, or the error message on failure
     * @throws sys_exception_AdminException
     */
    protected function doInsert() {
        throw new sys_exception_AdminException (_ ('Data inserting not implemented'), sys_exception_AdminException::ERR_SAVING_NOT_IMPLEMENTED);
    }

	/**
	 * Loads the data if there is no dbHandler set
	 *
	 * To be implemented in descendant classes if needed
	 *
	 * @return array;
	 * @throws sys_exception_AdminException
	 */
	protected function doLoad() {
		throw new sys_exception_AdminException (_ ('Data loading not implemented'), sys_exception_AdminException::ERR_LOADING_NOT_IMPLEMENTED);
	}

	/**
	 * Deletes the item if there is no dbHandler set
	 *
	 * To be implemented in descendant classes if needed
	 *
	 * @throws sys_exception_AdminException
	 */
	protected function doDelete() {
		throw new sys_exception_AdminException (_ ('Data deleting not implemented'), sys_exception_AdminException::ERR_DELETING_NOT_IMPLEMENTED);
	}
	/**
	 * Initialization code
	 */
	protected function init() {}

	/**
	 * Called in case of a successful save
	 *
	 * Should be implemented in descendant classes if needed
	 */
	protected function postSave() {}

	/**
	 * Called in case of a successful delete
	 *
	 * Should be implemented in descendant classes if needed
	 */
	protected function postDelete() {}

	/**
	 * Sets the label for the save button
	 *
	 * @param string $text
	 */
	protected function setSaveBtnText($text) {
		$this->options ['saveBtnText'] = $text;
	}

	/**
	 * Sets the label for the close button
	 *
	 * @param string $text
	 */
	protected function setCloseBtnText($text) {
		$this->options ['closeBtnText'] = $text;
	}

	/**
	 * Sets the label for the delete button
	 *
	 * @param string $text
	 */
	protected function setDeleteBtnText($text) {
		$this->options ['deleteBtnText'] = $text;
	}

	/**
	 * Enables or disables the delete button
	 *
	 * @param boolean $disabled
	 */
	protected function setDeleteBtnDisabled($disabled = true) {
		if ($disabled) {
			$this->options ['deleteBtnDisabled'] = 1;
			return;
		}
		unset ($this->options ['deleteBtnDisabled']);
	}

	/**
	 * Sets the label for the delete button
	 *
	 * @param string $text
	 */
	protected function setAddBtnText($text) {
		$this->options ['addBtnText'] = $text;
	}

	/**
	 * Enables or disables the delete button
	 *
	 * @param boolean $disabled
	 */
	protected function setAddBtnDisabled($disabled = true) {
		if ($disabled) {
			$this->options ['addBtnDisabled'] = 1;
			return;
		}
		unset ($this->options ['addBtnDisabled']);
	}

	/**
	 * Adds the default object fields (id, creation date, etc...) to the form
	 *
	 */
	protected function addDefaultObjectFields() {
		$control = new sys_admin_control_Label ();
		$control->setLabel (_ ('id'));
		$this->panel->addControl ($control, 'id');

		$control = new sys_admin_control_Label ();
		$control->setLabel (_ ('Creation date'));
		$this->panel->addControl ($control, 'created_at');

		$control = new sys_admin_control_Label ();
		$control->setLabel (_ ('Last modification date'));
		$this->panel->addControl ($control, 'updated_at');
	}

	protected function requireSuperuser() {
		if (! $_SESSION['LoggedInAdminData']['superuser']) {
			throw new sys_exception_AdminException(_('Insufficient rights to view this module'), sys_exception_AdminException::ERR_INSUFFICIENT_RIGHTS);
		}
	}

	protected function addWarning($message) {
		$this->warnings[] = $message;
	}

	public function externalSaveProcess($data) {
		$this->data = $data;
		$this->processSaveData();
		return $this->data;
	}

	public function externalLoadProcess($data) {
		$this->data = $data;
		$this->processLoadData();
		return $this->data;
	}

	public function externalPostSave($data) {
		$this->data = $data;
		$this->postSave();
		return $this->data;
	}
}
?>