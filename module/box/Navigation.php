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
 * Navigation bar module
 *
 * @arg			doc "full_docpath" "Document path" 1 "full_docpath" 1
 * @arg			select "style" "Display style" 0 "" 0
 * @argvalue	"style" "" "Basic"
 * @package	YAPEP
 * @subpackage	BoxModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_box_Navigation extends sys_BoxModule {

	/**
	 * Executes the module
	 *
	 * @return string
	 */
	protected function main() {
		$path = trim($this->argArr['full_docpath']);
		$pathArr = explode('/', $path);
		if (!reset($pathArr)) {
			array_shift($pathArr);
		}
		if (!end($pathArr)) {
			array_pop($pathArr);
		}
		$folderCache = new sys_cache_FolderCacheManager();
		$navArr=array();
		while(count($pathArr)) {
			$path2Arr=$pathArr;
			$folder = $folderCache->getFolder($this->argArr['locale_id'], $path2Arr);
			if ($folder['doc_id']) {
				$docData=sys_DocFactory::getDocByDocId($folder['doc_id'])->getDocData();
				$navArr[]=array('docpath'=>$docData['Folder']['docpath'].'/'.$docData['docname'], 'name'=>$docData['name'], 'locale_id'=>$docData['Folder']['locale_id']);
				array_pop($pathArr);
			} else {
				$navArr[]=array('docpath'=>$folder['docpath'], 'locale_id'=>$folder['locale_id'], 'name'=>$folder['name']);
				array_pop($pathArr);
			}
		}
		$navArr=array_reverse($navArr);
		$this->smarty->assign('navArr', $navArr);
		return $this->smartyFetch();
	}
}
?>