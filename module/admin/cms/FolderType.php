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
 * Folder type administration module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_cms_FolderType extends sys_admin_AdminModule {
 	public function buildForm() {

        $this->requireSuperuser();

 		$this->setTitle(_('Folder type editor'));

 		$handler = getPersistClass('FolderType');
 		$this->setDbHandler($handler);

 		$control = new sys_admin_control_IdSelect();
 		$control->addOptions($handler->getFolderTypeList());
 		$control->setValue($this->id, true);
 		$this->addControl($control, 'idSelect');

 		$control = new sys_admin_control_TextInput();
 		$control->setLabel(_('Name'));
 		$control->setRequired();
 		$this->addControl($control, 'name');

 		$control = new sys_admin_control_TextInput();
 		$control->setLabel(_('Description'));
 		$control->setRequired();
 		$this->addControl($control, 'description');

 		$control = new sys_admin_control_FileInput();
 		$control->setLabel(_('Icon'));
 		$control->setRequired();
 		$this->addControl($control, 'icon');

 		$control = new sys_admin_control_FileInput();
 		$control->setLabel(_('Active icon'));
 		$control->setRequired();
 		$this->addControl($control, 'icon_act');

 		$control = new sys_admin_control_CheckBox();
 		$control->setLabel(_('Not document holder'));
 		$this->addControl($control, 'non_doc');

 		$control = new sys_admin_control_CheckBox();
 		$control->setLabel(_('Don\'t allow creating new documents'));
 		$this->addControl($control, 'no_new_doc');

 		$control = new sys_admin_control_SubItemList();
 		$control->setLabel(_('Object types'));
 		$control->setAddFieldLabel(_('New object type'));
 		$control->setNameField('id');
 		$control->setValueField('name');
 		$control->setSubForm('cms_FolderTypeObjectTypeRel');
 		$this->addControl($control, 'ObjectTypes');
 	}
}
?>