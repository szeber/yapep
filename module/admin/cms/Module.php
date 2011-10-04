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
 * Module administration module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 * @todo		Inheritance
 */
class module_admin_cms_Module extends sys_admin_AdminModule {

	protected function buildForm() {

		// TODO Inheritance

        $this->requireSuperuser();

		$handler = getPersistClass ('Module');

		$this->setDbHandler ($handler);

		$control = new sys_admin_control_IdSelect ();
		$control->addOptions ($handler->getModuleList ());
		$this->addControl ($control, 'idSelect');

		$control = new sys_admin_control_TextInput ();
		$control->setLabel (_ ('Name'));
		$control->setRequired ();
		$this->addControl ($control, 'name');

		$control = new sys_admin_control_TextInput ();
		$control->setLabel (_ ('Description'));
		$control->setRequired ();
		$this->addControl ($control, 'description');

		$control = new sys_admin_control_SelectInput ();
		$control->setLabel (_ ('Caching type'));
		$control->setRequired ();
		$control->addOptions (array (sys_BoxModule::CACHE_ENABLED => _ ('Enabled'), sys_BoxModule::CACHE_DISABLED => _ ('Disabled'), sys_BoxModule::CACHE_VETO_PAGE => _ ('Disabled and veto page caching')));
		$this->addControl ($control, 'cache_type');

		$control = new sys_admin_control_TextInput ();
		$control->setLabel (_ ('Cache expiration time'));
		$control->setRequired ();
		$this->addControl ($control, 'cache_expire');

		$control = new sys_admin_control_SubItemList ();
		$control->setLabel (_ ('Parameters'));
		$control->setNameField ('id');
		$control->setValueField ('description');
		$control->setSubForm ('cms_ModuleParam');
		$control->setAddFieldLabel (_ ('New parameter'));
		$this->addControl ($control, 'Params');
	}

	protected function postSave() {
		if ($this->mode == self::MODE_ADD && $this->config->getOption('adminCreateFiles') && !class_exists('module_box_'.$this->data['name'])) {
			$directory = 'module/box';
			$tmp = explode('_', $this->data['name']);
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
			$smarty->assign('className', 'module_box_'.$this->data['name']);
			$smarty->assign('path', $path);
			$smarty->assign('directory', $directory);
			file_put_contents(PROJECT_PATH.$directory.$path.'/'.$fileName.'.php', $smarty->fetch('yapep:admin/module/cms_Module/boxmodule.tpl'));
			chmod(PROJECT_PATH.$directory.$path.'/'.$fileName.'.php', 0777);
		}
		if (!class_exists('module_box_'.$this->data['name'])) {
			$this->addWarning(_('The specified Box module does not exist'));
		} else {
			$class = new ReflectionClass('module_box_'.$this->data['name']);
			$comment = $class->getDocComment();
			if ($comment) {
				preg_match_all('/@arg\s+(\w+)\s+"([^"]+)"\s+"([^"]+)"\s+([01])\s+"([^"]*)"\s+([01])\s*$/mi', $class->getDocComment(), $args, PREG_SET_ORDER);
				preg_match_all('/@argvalue\s+"([^"]+)"\s+"([^"]+)"\s+"([^"]+)"\s*/mi', $class->getDocComment(), $argValues, PREG_SET_ORDER);
				$values = array();
				foreach($argValues as $value) {
					if (!isset($values[$value[1]])) {
						$values[$value[1]] = array();
					}
					$values[$value[1]][$value[2]] = $value[3];
				}
				if (count($args)) {
					foreach ($args as $arg) {
						if (!$arg[2]) {
							$this->addWarning(_('Name can\'t be empty. Skipping arg.'));
							continue;
						}
						if (!$arg[3]) {
							$arg[3] = $arg[2];
						}
						$param = array('module_id'=>$this->id, 'name'=>$arg[2], 'description'=>$arg[3], 'default_value'=>$arg[5], 'allow_variable'=>(bool)$arg[4], 'default_is_variable'=>(bool)$arg[6], 'Values'=>array());
						if (isset($values[$param['name']])) {
							$param['Values'] = $values[$param['name']];
						}
						$arg[1] = strtolower($arg[1]);
						switch($arg[1]) {
							case 'text':
							case 'string':
								$param['param_type_id'] = module_db_interface_ModuleParam::TYPE_TEXT;
								break;
							case 'longtext':
							case 'longstring':
								$param['param_type_id'] = module_db_interface_ModuleParam::TYPE_LONG_TEXT;
								break;
							case 'check':
							case 'checkbox':
								$param['param_type_id'] = module_db_interface_ModuleParam::TYPE_CHECK;
								break;
							case 'select':
							case 'combo':
							case 'combobox':
							case 'list':
								$param['param_type_id'] = module_db_interface_ModuleParam::TYPE_SELECT;
								break;
							case 'doc':
							case 'document':
								$param['param_type_id'] = module_db_interface_ModuleParam::TYPE_DOC;
								break;
							case 'doclist':
							case 'documentlist':
								$param['param_type_id'] = module_db_interface_ModuleParam::TYPE_DOC_LIST;
								break;
							case 'folder':
								$param['param_type_id'] = module_db_interface_ModuleParam::TYPE_FOLDER;
								break;
							case 'folderlist':
								$param['param_type_id'] = module_db_interface_ModuleParam::TYPE_FOLDER_LIST;
								break;
							default:
								$this->addWarning(_('Invalid type for argument:').' '.$this->arg[2]);
								continue 2;
						}
						$db = getPersistClass('ModuleParam');
						$db->importParam($param);
					}
				}
			}
		}
		$cache = new sys_cache_ModuleCacheManager ();
		$cache->recreateCache ();
	}

	protected function postDelete() {
		$cache = new sys_cache_ModuleCacheManager ();
		$cache->recreateCache ();
	}
}
?>