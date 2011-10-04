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
 * Object browser admin module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_object_Browser extends sys_admin_AdminModule {
	protected function buildForm() {

		$this->disableFormTag();

		$panel = new sys_admin_control_Panel();
		$panel->setDock (sys_admin_interface_Dockable::DOCK_LEFT);
		$panel->setWidth (200);
		$this->addControl($panel, 'treePanel');

		$module = new module_admin_folder_Tree($this->manager);
		$module->setLocale($this->locale);
		$module->parseXml($this->xml);
		$treePanel = $module->getPanel();
		$treePanel->setAddForm(false);
		$treePanel->setDock(sys_admin_interface_Dockable::DOCK_CLIENT);
		$tree = $treePanel->getControl('treeControl');
		$tree->removeAllListeners();
		$tree->addListener('BrowserListListener');
		$panel->addControl($tree, 'pageTree');

		$listPanel = new sys_admin_control_ListPanel ();
		$listPanel->setListenerName ('BrowserListListener');
		$listPanel->setTitle (_ ('Object list'));
		$listPanel->setTarget ('objectPanel');
		$this->addControl ($listPanel, 'list');

	}
}
?>