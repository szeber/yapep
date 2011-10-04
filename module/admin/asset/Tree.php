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
class module_admin_asset_Tree extends sys_admin_AdminModule {

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
	 * @see sys_admin_AdminModule::buildForm()
	 *
	 */
	protected function buildForm() {
		$this->disableFormTag();
		$tree = $this->addControl(new sys_admin_control_TreeView(), 'assetTreeControl');
		$tree->setTree($this->makeFolderTree());
		$tree->addListener('assetList');
		$tree->addListener('assetProp');

		$control = new sys_admin_control_PopupFormButton();
		$control->setLabel(_('New subfolder'));
		$control->setTargetForm('asset_Prop');
//		$control->setTargetSubFormField('id');
		$control->setWindowTitle(_('New subfolder'));
		$this->addControl($control, 'newAssetSubfolderButton');
	}

	protected function makeFolderTree() {
		$assetHandler = getPersistClass('Asset');
		$folders = array();

		$assetTypes = $assetHandler->getTypes();
		$this->folderTypes = array();
		if (is_object($assetTypes)) {
			$assetTypes = $assetTypes->toArray();
		}
		$folderData = $assetHandler->getAllFolders();
		foreach($assetTypes as $type) {
			if (is_object($folderData)) {
				$folderData = $folderData->toArray();
			}
			$node=array('name'=>$type['name'], 'icon'=>$this->config->getOption('defaultFolderIcon'), 'iconAct'=>$this->config->getOption('defaultFolderIcon'), 'link'=>'');
			$node['subTree'] = $this->makeFolderSubtree($folderData, null, $type);;
			$folders[] = $node;
		}

		return $folders;
	}

	protected function makeFolderSubtree($folderData,$parentId,$type) {
		$subFolders = array();
		foreach($folderData as $key=>$folder) {
			if ($folder['parent_id'] == $parentId) {
				$node = array('name'=>$folder['name'], 'link'=>'Asset/'.$type['id'].'/'.$folder['id'], 'icon'=>$this->config->getOption('defaultFolderIcon'), 'iconAct'=>$this->config->getOption('defaultFolderIcon'));
				$node['subTree'] = $this->makeFolderSubtree($folderData, $folder['id'], $type);
				$subFolders[] = $node;
			}
		}
		return $subFolders;
	}

}
?>