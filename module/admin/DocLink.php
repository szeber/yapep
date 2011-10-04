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
 * Document link administration module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_DocLink extends sys_admin_AdminModule {
	public function buildForm() {

		$handler = getPersistClass('GenericAdmin');
		$handler->setObjType('DocLink');
		$this->setDbHandler($handler);

		$control = new sys_admin_control_RelationList ();
		$control->setLabel (_ ('Document'));
		$control->addObjectType ('Doc/*');
		$control->setDisplayTemplate ('{$name}');
		$control->setDataField ('id');
		$control->setNameField ('doc_id');
		$control->setValueField('name');
		$control->setSingleItem();
		$this->addControl ($control, 'doc_id');
	}

	/**
	 * @see sys_admin_AdminModule::processSaveData()
	 *
	 */
	protected function processSaveData() {
		if ($this->mode == sys_admin_AdminModule::MODE_ADD) {
			$this->data['folder_id'] = $this->subModule[0];
		}
		if (is_array($this->data['doc_id'])) {
			$this->data['doc_id'] = reset($this->data['doc_id']);
		}
		if (!$this->data['doc_id']) {
			unset($this->data['doc_id']);
		}
	}

}
?>