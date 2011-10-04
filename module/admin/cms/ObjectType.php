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
 * Object type administration module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_cms_ObjectType extends sys_admin_AdminModule {
	public function buildForm() {

        $this->requireSuperuser();

		$handler = getPersistClass('ObjectType');

		$this->setDbHandler($handler);

		$control = new sys_admin_control_IdSelect();
		$control->addOptions($handler->getObjectTypeList());
		$control->setValue($this->id, true);
		$this->addControl($control, 'idSelect');

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Name'));
		$control->setRequired();
		$this->addControl($control, 'name');

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Short name'));
		$control->setRequired();
		$this->addControl($control, 'short_name');

	 	$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Persistence class'));
		$control->setRequired();
		$this->addControl($control, 'persist_class');

	 	$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Handler class'));
		$control->setRequired();
		$this->addControl($control, 'handler_class');

	 	$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Admin class'));
		$control->setRequired();
		$this->addControl($control, 'admin_class');

	 	$control = new sys_admin_control_FileInput();
		$control->setLabel(_('Icon'));
		$this->addControl($control, 'icon');

		$control = new sys_admin_control_FileInput();
		$control->setLabel(_('Active icon'));
		$this->addControl($control, 'icon_act');

	 	$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Template file'));
		$this->addControl($control, 'template_file');

		if ($this->config->getOption('adminCreateFiles')) {
		 	$control = new sys_admin_control_CheckBox();
			$control->setLabel(_('Create doc module'));
			$this->addControl($control, 'createDoc');
		}

		$control = new sys_admin_control_SubItemList();
		$control->setLabel(_('Columns'));
		$control->setValueField('name');
		$control->setNameField('id');
		$control->setAddFieldLabel(_('New column'));
		$control->setSubForm('cms_ObjectTypeColumn');
		$this->addControl($control, 'Columns');
	}

	/**
	 * @see sys_admin_AdminModule::postSave()
	 *
	 */
	protected function postSave() {
		if (!$this->config->getOption('adminCreateFiles')) {
			return;
		}
		if ($this->data['persist_class']) {
			if (!class_exists($this->data['persist_class'])) {
				$this->makeFile('models', $this->data['persist_class'], 'doctrinemodel', array(), 'yml');
			}
		}
		if ($this->data['handler_class']) {
			$params = array('createDoc', $this->data['createDoc']);
			if (!interface_exists('module_db_interface_'.$this->data['handler_class'])) {
				$this->makeFile('', 'module_db_interface_'.$this->data['handler_class'], 'dbinterface', $params);
			}
			if ($this->data['createDoc']) {
				if (!class_exists('module_db_generic_'.$this->data['handler_class'])) {
					$this->makeFile('', 'module_doc_'.$this->data['handler_class'], 'docmodule', $params);
				}
			}
			if (!class_exists('module_db_generic_'.$this->data['handler_class'])) {
				$this->makeFile('', 'module_db_generic_'.$this->data['handler_class'], 'dbmodule', $params);
			}
			if (!class_exists('module_db_Doctrine_'.$this->data['handler_class'])) {
				$this->makeFile('', 'module_db_Doctrine_'.$this->data['handler_class'], 'doctrinemodule', $params);
			}
		}
		if ($this->data['admin_class']) {
			if (!class_exists('module_admin_'.$this->data['admin_class'])) {
				$this->makeFile('', 'module_admin_'.$this->data['admin_class'], 'adminmodule');
			}
		}
	}

	protected function makeFile($directory, $className, $template, $params=array(), $extension='php') {
		$tmp = explode('_', $className);
		$fileName = end($tmp);
		unset($tmp[(count($tmp)-1)]);
		$path = implode('/',$tmp);
		if ($path && $directory) {
			$path='/'.$path;
		}
		if (!is_dir(PROJECT_PATH.$directory.$path)) {
			if (!@mkdir(PROJECT_PATH.$directory.$path, 0777, true)) {
				$this->addWarning(_('Can\'t create directory:').' '.PROJECT_PATH.$directory.$path);
				return;
			}
			chmod (PROJECT_PATH.$directory.$path.'/', 0777);
		}
		if (!is_writeable(PROJECT_PATH.$directory.$path)) {
			$this->addWarning(_('Can\'t write directory:').' '.PROJECT_PATH.$directory.$path);
			return;
		}
		if (file_exists(PROJECT_PATH.$directory.$path.'/'.$fileName.'.php')) {
			$this->addWarning(_('File already exists:').' '.PROJECT_PATH.$directory.$path.'/'.$fileName.'.php');
			return;
		}
		$smarty = sys_LibFactory::getSmarty();
		$smarty->assign('fileName', $fileName);
		$smarty->assign('className', $className);
		$smarty->assign('path', $path);
		$smarty->assign('directory', $directory);
		$smarty->assign('params', $params);
		file_put_contents(PROJECT_PATH.$directory.$path.'/'.$fileName.'.'.$extension, $smarty->fetch('yapep:admin/module/cms_ObjectType/'.$template.'.tpl'));
		chmod(PROJECT_PATH.$directory.$path.'/'.$fileName.'.'.$extension, 0777);
	}

}
?>