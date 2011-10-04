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
 * Sitemap module
 *
 * @package	YAPEP
 * @subpackage	BoxModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_box_Sitemap extends sys_BoxModule {

	/**
	 * Removes folders from the $folderList that shouldn't appear in the sitemap
	 *
	 * @var array $folderList
	 */
	protected function cleanupFolders(&$folderList) {
		foreach($folderList as $key=>$folder) {
			if (!$folder['sitemap']) {
				unset($folderList[$key]);
			} elseif(count($folder['subfolders'])) {
				$this->cleanupFolders($folder['subfolders']);
			}
		}
	}

	/**
	 * Executes the module
	 *
	 * @return string
	 */
	protected function main() {
		$cache = new sys_cache_FolderCacheManager();
		$path=array();
		$folderList = $cache->getFolder($this->argArr['locale_id'], $path, sys_cache_FolderCacheManager::WITH_SUBFOLDER_RECURSIVE);
		$folderList=$folderList['subfolders'];
		$this->cleanupFolders($folderList);
		$this->smarty->assign('folderList', $folderList);
		return $this->smartyFetch();
	}
}
?>