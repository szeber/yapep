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
 * Document lister document module
 *
 * @arg			text "docCount" "Dokumentumok száma" 0 "10" 0
 * @arg			folder "folder" "Mappa" 1 "full_docpath" 1
 * @arg			text "objectTypeId" "Objektum tipus id" 0 "" 0
 * @arg			text "template" "Hasznalt template neve" 0 "" 0
 * @arg			text "perPages" "Doc oldalanként lehetőségek" 0 "5,10,20,50" 0
 * @package	YAPEP
 * @subpackage	BoxModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_box_DocList extends sys_BoxModule {

	protected function main() {
		$folderHandler = getPersistClass('Folder');
		$folder = $folderHandler->getFolderByDocPath($this->argArr['locale_id'], $this->argArr['folder']);
		$this->smarty->assign('folder', $folder);

		$docHandler = getPersistClass('Doc');
		$docCount = $docHandler->getDocCountFromFolder($this->argArr['locale_id'], $this->argArr['folder'],  explode(',',$this->argArr['objectTypeId']));

		if ($_GET['docsPerPage']) {
			$actualDocPerPage = (int)$_GET['docsPerPage'];
		} else {
			$actualDocPerPage = $this->argArr['docCount'];
		}

		$maxPages = ceil($docCount / $actualDocPerPage);
		$currentPage = (int)$_GET['page'];
		if ($currentPage > $maxPages) {
			$currentPage = $maxPages;
		}
		if ($currentPage<=0) {
			$currentPage = 1;
		}
		$this->smarty->assign('docCount', $docCount);
		$this->smarty->assign('currentPage', $currentPage);
		$this->smarty->assign('maxPages', $maxPages);
		$this->smarty->assign('actualDocPerPage', $actualDocPerPage);

		if ($this->argArr['perPages']) {
			$docPerPageArray = explode(',',$this->argArr['perPages']);
			$this->smarty->assign('docPerPageArr', $docPerPageArray);
		}

		$offset = ($currentPage - 1) * $actualDocPerPage;
		$this->smarty->assign('docData', $docHandler->getDocFromFolder($this->argArr['locale_id'], $this->argArr['folder'],  explode(',',$this->argArr['objectTypeId']), module_db_interface_Doc::TYPE_NEWEST, $actualDocPerPage, $offset));
		return $this->smartyFetch();
	}
}
?>