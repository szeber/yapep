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
 * Folder tree admin module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_folder_Tree extends sys_admin_AdminModule {

	/**
	 * Stores the available folder types
	 *
	 * @var array
	 */
	protected $folderTypes=array();

	/**
	 * Stores the root url for the images
	 *
	 * @var string
	 */
	protected $imageRoot = '/';

	/**
	 * Stores the ids of the open nodes
	 *
	 * @var array
	 * @todo make this adjustable
	 */
	protected $openNodes = array();

	/**
	 * @see sys_admin_AdminModule::buildForm()
	 *
	 */
	protected function buildForm() {
		$this->disableFormTag();
		$tree = $this->addControl(new sys_admin_control_TreeView(), 'treeControl');
		$tree->setTree($this->makeFolderTree());
		$tree->addListener('mainList');
		$tree->addListener('folderProp');

		$control = new sys_admin_control_PopupFormButton();
		$control->setLabel(_('New subfolder'));
		$control->setTargetForm('folder_Prop');
//		$control->setTargetSubFormField('id');
		$control->setWindowTitle(_('New subfolder'));
		$this->addControl($control, 'newSubfolderButton');

	}

	protected function makeFolderTree() {
		$folderHandler = getPersistClass('Folder');
		$localeHandler = getPersistClass('LangLocale');

		$folderTypes = $folderHandler->getFolderTypes();
		$this->folderTypes = array();
		if (is_object($folderTypes)) {
			$folderTypes = $folderTypes->toArray();
		}
		foreach($folderTypes as $type) {
			$this->folderTypes[$type['id']] = $type;
		}

		$localeData = $localeHandler->getLocaleByCode($this->locale);
		$folderData = $folderHandler->getAllFoldersByLocaleId($localeData['id']);
		$folders = array();
		if (is_object($folderData)) {
			$folderData = $folderData->toArray();
		}
		foreach($folderData as $folder) {
			if (is_null($folder['parent_id'])) {
				$node = array('name'=>$folder['name'], 'link'=>'Folder/'.$folder['id'], 'icon'=>$this->imageRoot.$this->folderTypes[$folder['folder_type_id']]['icon'], 'iconAct'=>$this->imageRoot.$this->folderTypes[$folder['folder_type_id']]['icon_act']);
				if (in_array($folder['id'], $this->openNodes)) {
					$node['isOpen'] = true;
				}
				if ($folder['virtual_subfolders']) {
					$node['subTree']  = $this->getVirtualSubTree($folder);
				} else {
					$node['subTree'] = $this->makeFolderSubtree($folderData, $folder['id']);
				}
				$folders[] = $node;
			}
		}
		return $folders;
	}

	protected function makeFolderSubtree($folderData,$parentId) {
		$subFolders = array();
		foreach($folderData as $folder) {
			if ($folder['parent_id'] == $parentId) {
				$node = array('name'=>$folder['name'], 'link'=>'Folder/'.$folder['id'], 'icon'=>$this->imageRoot.$this->folderTypes[$folder['folder_type_id']]['icon'], 'iconAct'=>$this->imageRoot.$this->folderTypes[$folder['folder_type_id']]['icon_act']);
				if (in_array($folder['id'], $this->openNodes)) {
					$node['isOpen'] = true;
				}
				if ($folder['virtual_subfolders']) {
					$node['subTree']  = $this->getVirtualSubTree($folder);
				} else {
					$node['subTree'] = $this->makeFolderSubtree($folderData, $folder['id']);
				}
				$subFolders[] = $node;
			}
		}
		return $subFolders;
	}

	protected function getVirtualSubTree($folderinfo) {
		$className = 'module_virtual_'.$folderinfo['virtual_handler'];
		if (!class_exists($className)) {
			return array();
		}
		$handler = new $className();
		if (!$handler instanceof sys_VirtualHandler) {
			return array();
		}
		return $handler->getAdminFolderTree($folderinfo['id'], $this->imageRoot);
	}

}
?>