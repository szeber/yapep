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
 * Folder browser admin module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_folder_Browser extends sys_admin_AdminModule {
	protected function buildForm() {

		$this->disableFormTag();

		$module = new module_admin_folder_Tree($this->manager);
		$module->setLocale($this->locale);
		$module->parseXml($this->xml);
		$treePanel = $module->getPanel();
		$treePanel->setAddForm(false);
		$treePanel->setDock(sys_admin_interface_Dockable::DOCK_CLIENT);
		$tree = $treePanel->getControl('treeControl');
		$tree->removeAllListeners();
		$tree->addListener('BrowserListener');
		$this->addControl($tree, 'pageTree');

	}
}
?>