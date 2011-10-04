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
 * Document box module
 *
 * @arg			doc "full_docpath" "Document path" 1 "full_docpath" 1
 * @arg			check "show_inactive" "Allow inactive documents" 0 "0" 0
 * @arg         check "is_printable" "Allow print view" 0 "1" 0
 * @arg			select "template" "Display template" 0 "" 0
 * @argvalue	"template" "" "Full document"
 * @package	YAPEP
 * @subpackage	BoxModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_box_Doc extends sys_BoxModule {

	/**
	 * Document ID
	 *
	 * @var integer
	 */
	protected $docId;

	/**
	 * Document type data
	 *
	 * @var array
	 */
	protected $docTypeData;

	/**
	 * Document
	 *
	 * @var sys_DocModule
	 */
	public $doc;

	public function __call($name, $args) {
		return $this->doc->$name($args);
	}

	protected function getSmartyBase($template) {
 		return 'doc/'.$template.'.tpl';
 	}

 	protected function init() {
 		parent::init();
 		$this->docTypeData=sys_DocFactory::getDocTypeByDocPath($this->argArr['locale_id'], $this->argArr['full_docpath']);
 		$this->moduleInfo['default_template']=$this->docTypeData['template_file'];
 	}

	protected function main() {
		$this->doc=sys_DocFactory::getDocByDocPath($this->argArr['locale_id'], $this->argArr['full_docpath'], $this->argArr['show_inactive']);
		if (!is_object($this->doc)) {
			return '';
		}
		$docData = $this->doc->getDocData();
		$this->pageManager->setTitle($docData['name'], true);
		$this->smarty->assign('docData', $docData);
		return $this->smartyFetch();
	}
}
?>