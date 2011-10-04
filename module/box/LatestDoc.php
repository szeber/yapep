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
 * Displays the latest documents from a given folder
 *
 * @arg			text "docCount" "Document count" 0 "1" 0
 * @arg			folder "folder" "Folder" 1 "" 0
 * @arg			text "objectTypeId" "Object type ID" 0 "" 0
 * @arg			text "template" "Template used" 0 "" 0
 * @package	YAPEP
 * @subpackage	BoxModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_box_LatestDoc extends sys_BoxModule {

	/**
	 * Persistance class
	 *
	 * @var module_db_interface_Doc
	 */
	protected $db;

	protected function getDocs() {
		return $this->db->getDocFromFolder($this->argArr['locale_id'], $this->argArr['folder'], explode(',',$this->argArr['objectTypeId']), module_db_interface_Doc::TYPE_NEWEST, $this->argArr['docCount']);
	}

	protected function main() {
		$this->db = getPersistClass('Doc');
		$docs = $this->getDocs();
		$this->smarty->assign('docs', $docs);
		return $this->smartyFetch();
	}
}
?>