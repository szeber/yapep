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
 * Folder listing box module
 *
 * @arg			folder "root_folder" "Gyökér mappa" 1 "" 0
 * @arg			text "template" "Hasznalt template neve" 0 "" 0
 * @package	YAPEP
 * @subpackage	BoxModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_box_FolderList extends sys_BoxModule {
	protected function main() {
		$folderParts = explode('/', $this->argArr['root_folder']);
		$folderHandler = new sys_cache_FolderCacheManager();
		$this->smarty->assign('folder', $folderHandler->getFolder($this->argArr['locale_id'], $folderParts, sys_cache_FolderCacheManager::WITH_SUBFOLDER_RECURSIVE));
		return $this->smartyFetch();
	}
}
?>