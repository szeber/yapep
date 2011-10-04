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
 * Page tree admin module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_page_Tree extends sys_admin_AdminModule {

	/**
	 * Stores the available folder types
	 *
	 * @var array
	 */
	protected $pageImages=array();

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
		$tree = $this->addControl(new sys_admin_control_TreeView(), 'treeControl');
		$tree->setTree($this->makePageTree());
		$tree->addListener('pageEditor');
		$tree->addListener('pageProp');
	}

	protected function makePageTree() {
		$folderHandler = getPersistClass('Page');
		$localeHandler = getPersistClass('LangLocale');

		$localeData = $localeHandler->getLocaleByCode($this->locale);
		$pageData = $folderHandler->getPagesByLocaleId($localeData['id']);
		$pages = array();
		if (is_object($pageData)) {
			$pageData = $pageData->toArray();
		}
		foreach($pageData as $page) {
			if (is_null($page['parent_id'])) {
				$node = array('name'=>$page['name'], 'link'=>'Page/'.$page['id']);
				switch($page['page_type']) {
					case sys_PageManager::TYPE_PAGE:
						$node['type'] = 'page';
						break;
					case sys_PageManager::TYPE_DERIVED_PAGE:
						$node['type'] = 'derived';
						break;
					case sys_PageManager::TYPE_FOLDER:
					default:
						$node['type'] = 'folder';
						break;
				}
				if ($page['page_type'] == sys_PageManager::TYPE_FOLDER) {
					$node['icon'] = $this->imageRoot.'folder.png';
				} else {
					$node['icon'] = $this->imageRoot.'page.png';
				}
				$node['icon_act'] = $node['icon'];
				$node['subTree'] = $this->makePageSubtree($pageData, $page['id']);
				$pages[] = $node;
			}
		}
		return $pages;
	}

	protected function makePageSubtree($pageData,$parentId) {
		$pages = array();
		foreach($pageData as $page) {
			if ($page['parent_id'] == $parentId) {
				$node = array('name'=>$page['name'], 'link'=>'Page/'.$page['id']);
				switch($page['page_type']) {
					case sys_PageManager::TYPE_PAGE:
						$node['type'] = 'page';
						break;
					case sys_PageManager::TYPE_DERIVED_PAGE:
						$node['type'] = 'derived';
						break;
					case sys_PageManager::TYPE_FOLDER:
					default:
						$node['type'] = 'folder';
						break;
				}
				if ($page['page_type'] == sys_PageManager::TYPE_FOLDER) {
					$node['icon'] = $this->imageRoot.'folder.png';
				} else {
					$node['icon'] = $this->imageRoot.'page.png';
				}
				$node['icon_act'] = $node['icon'];
				$node['subTree'] = $this->makePageSubtree($pageData, $page['id']);
				$pages[] = $node;
			}
		}
		return $pages;
	}

}
?>