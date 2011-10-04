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
 * RSS Module
 *
 * @arg			folderlist "folders" "Folders" 0 "" 0
 * @arg			text "doc_count" "Document count" 0 "10" 0
 * @package	YAPEP
 * @subpackage	BoxModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_box_Rss extends sys_BoxModule {
	protected function main() {
		$folderHandler = getPersistClass('Folder');
		if (!isset($_GET['id'])) {
			$cache = new sys_cache_FolderCacheManager();
			$folders = explode(',', $this->argArr['folders']);
			$folder = array();
			foreach($folders as $val) {
				$path = explode('/', trim($val));
				$folderInfo = $cache->getFolder($this->argArr['locale_id'], $path);
				$folder[] = $folderInfo['id'];
			}
		} else {
			$folderInfo = $folderHandler->getFullFolderInfoById((int)$_GET['id']);
			if (!$folderInfo) {
				return '';
			}
			$folder = array((int)$_GET['id']);
			$this->smarty->assign('folder', $folderInfo);
			if ($_GET['subfolders']) {
				$cache = new sys_cache_FolderCacheManager();
				$folders = $cache->getFolder($this->argArr['locale_id'], explode('/', $folderInfo['docpath']), sys_cache_FolderCacheManager::WITH_SUBFOLDER_RECURSIVE);
				foreach($folders['subfolders'] as $val) {
					$folder[] = $val['id'];
				}
			}
		}
		header('Content-type: application/xml; charset: utf-8');
		$docHandler = getPersistClass('Doc');
		$docs = $docHandler->getLatestDocs($this->argArr['locale_id'], $folder, $this->argArr['doc_count']);
		$this->smarty->assign('docs', $docs);
		$locale = getenv('LC_ALL');
		setlocale(LC_ALL, 'en_US.UTF-8');
		$output = $this->smartyFetch();
		setlocale(LC_ALL, $locale);
		return $output;
	}
}
?>