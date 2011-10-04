<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

 /**
 * Folder type - object type relation administration module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_cms_FolderTypeObjectTypeRel extends sys_admin_AdminModule {

	/**
	 * Stores the relation handler instance
	 *
	 * @var module_db_interface_FolderType
	 */
	protected $relHandler;

	/**
	 * Stores the object type handler instance
	 *
	 * @var module_db_interface_ObjectType
	 */
	protected $objectTypeHandler;

	protected function buildForm() {
        $this->requireSuperuser();

		$this->relHandler = getPersistClass('FolderType');
		$this->objectTypeHandler = getPersistClass('ObjectType');

		$control = new sys_admin_control_IdSelect();
		$control->addOptions($this->relHandler->getRelList($this->subModule[0]));
		$control->setValue($this->id, true);
		$this->addControl($control, 'idSelect');

		$control = new sys_admin_control_Label();
		$control->setLabel(_('Folder type ID'));
		$control->setDefaultValue($this->subModule[0]);
		$control->setReadOnly(true);
		$this->addControl($control, 'folder_type_id');

 	 	$control = new sys_admin_control_SelectInput();
 		$control->setLabel(_('Object type'));
 		$control->setRequired();
 		$control->addOptions($this->objectTypeHandler->getObjectTypeList());
 		$this->addControl($control, 'object_type_id');

 	}

 	/**
	 * @see sys_admin_AdminModule::doDelete()
	 *
	 */
	protected function doDelete() {
		return $this->relHandler->deleteRelItem($this->subModule[0], $this->id);
	}

	/**
	 * @see sys_admin_AdminModule::doLoad()
	 *
	 * @return array;
	 */
	protected function doLoad() {
		return $this->relHandler->loadRelItem($this->subModule[0], $this->id);
	}

	/**
	 * @see sys_admin_AdminModule::doSave()
	 *
	 * @return boolean
	 */
	protected function doSave() {
		if ($this->mode == sys_admin_AdminModule::MODE_ADD) {
			$values = $this->panel->getInputValues ();
			$result = $this->relHandler->insertRelItem ($this->subModule[0], $values['object_type_id']);
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
			$result = _('Data modification disabled');
		}
		return $result;

	}

 }
?>