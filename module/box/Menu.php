<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	BoxModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Static menu module
 *
 * Creates a static menu based on the folders and documents received as parameters
 *
 * @arg			doc "curr_docpath" "Current document path" 1 "full_docpath" 1
 * @arg			folderlist "docpath_list" "Document list" 0 "" 0
 * @arg			select "type" "Menu type" 0 "" 0
 * @argvalue	"type" "" "Basic"
 * @arg			check "get_submenu" "Get subfolders" 0 "0" 0
 * @arg			select "template" "Template" 0 "" 0
 * @argvalue    "template" "" "Basic"
 * @package	YAPEP
 * @subpackage	BoxModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_box_Menu extends sys_BoxModule {

	/**
	 * Array containin the submenu items
	 *
	 * @var array
	 */
	private $submenu_arr=array();

	/**
	 * Array containing the menu items
	 *
	 * @var array
	 */
	private $menu_arr=array();

	/**
	 * Folder persistance class
	 *
	 * @var module_db_generic_Folder
	 */
	private $folderPersist;

	/**
	 * Subfolder sorting method for usort()
	 *
	 * @param array $a
	 * @param array $b
	 * @return integer
	 */
	public static function sortSubfolders($a, $b) {
		if ($a['folder_order'] == $b['folder_order']) {
			if ($a['name'] == $b['name']) {
				return 0;
			} else {
				return ($a['name'] < $b['name']) ? -1 : 1;
			}
		}
		return ($a['folder_order'] < $b['folder_order']) ? -1 : 1;
	}

	/**
	 * Executes the module
	 *
	 * @return string
	 */
	protected function main() {
		$this->folderPersist = getPersistClass('Folder');
		$fold_arr=$this->argArr['docpath_list'] ? explode(',',$this->argArr['docpath_list']) : array();
		$curr_docpath = $this->argArr['curr_docpath'];
		$curr_docpath = substr($curr_docpath, -1) == '/' ? substr($curr_docpath, 0, strlen($curr_docpath)-1) : $curr_docpath;
		$curr_docpath = substr($curr_docpath, 1) == '/' ? substr($curr_docpath, 1) : $curr_docpath;
		$this->menu_arr=array();
		$this->submenu_arr=array();
		$folderCache = new sys_cache_FolderCacheManager();
		if ($this->argArr['get_submenu']) {
			$subfolders=sys_cache_FolderCacheManager::WITH_SUBFOLDER;
		} else {
			$subfolders=sys_cache_FolderCacheManager::WITHOUT_SUBFOLDER;
		}
		foreach($fold_arr as $fold) {
			if ('/index' == substr($fold, -6)) {
				$fold = substr($fold, 0, -6);
			}
			$fold=explode('/', $fold);
			if (!end($fold)) {
				array_pop($fold);
			}
			try {
				$tmp = $folderCache->getFolder($this->argArr['locale_id'], $fold, $subfolders);
			} catch (sys_exception_SiteException $e) {
				continue;
			}
			if (!$tmp['doc_id']) {
				if ($this->argArr['get_submenu'] && is_array($tmp['subfolders']) && count($tmp['subfolders'])>1) {
					$tmp['has_submenu']=1;
					usort($tmp['subfolders'], array(get_class($this), 'sortSubfolders'));
					$this->submenu_arr[$tmp['id']]=$tmp['subfolders'];
					unset($tmp['subfolders']);
				}
			}

			$this->menu_arr[]=$tmp;
		}
		usort($this->menu_arr, array(get_class($this), 'sortSubfolders'));
		$this->smarty->assign('curr_docpath', $curr_docpath);
		$this->smarty->assign('menu', $this->menu_arr);
		$this->smarty->assign('submenu', $this->submenu_arr);
		return $this->smartyFetch();
	}
}
?>