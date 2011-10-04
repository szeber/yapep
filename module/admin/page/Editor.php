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
 * Page editor module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_page_Editor extends sys_admin_AdminModule {

	/**
	 * Page database handler
	 *
	 * @var module_db_interface_Page
	 */
	private $pageHandler;

	/**
	 * Stores the page's data
	 *
	 * @var array
	 */
	private $pageData;

	public function init() {
		parent::init();
		$this->pageHandler = getPersistClass('Page');
	}

	public function buildForm() {

		if (! $this->subModule[0]) {
			throw new sys_exception_AdminException(_('No submodule set'), sys_exception_AdminException::ERR_NO_SUBMODULE_SET);
		}
		if (! $this->checkPageValid()) {
			throw new sys_exception_AdminException(_('Page not found'), sys_exception_AdminException::ERR_SUBMODULE_NOT_FOUND);
		}

		$smarty = sys_LibFactory::getSmarty();
		$boxplaces = $this->getBoxplaces();
		$smarty->assign('boxplaces', $this->getBoxplaceNames($boxplaces));
		$smarty->caching = false;

		$this->disableFormTag();

		$page = new sys_admin_control_Page();
		$page->setTemplateCode($smarty->fetch('page/'.$this->pageData['Template']['file']));
		$page->setListTarget('page_Box');
		$this->addControl($page, 'page');

		foreach($boxplaces as $boxplace) {
			$control = new sys_admin_control_BoxPlace();
			$control->setBoxplaceId($boxplace['id']);
			$boxes = $this->pageHandler->getBoxesByPageId($this->pageData['id'], $boxplace['id']);
			foreach($boxes as $box) {
				$control->addBox($box['id'], $box['name'], $box['Module']['name'], $box['active'], (bool)($box['status'] & module_admin_page_Box::STATUS_ACTIVE_INHERITED));
			}
			$page->addControl($control, $boxplace['boxplace']);
		}
	}

	protected function checkPageValid() {
		$data = $this->pageHandler->getPageData((int)$this->subModule[0]);
		if (is_object($data)) {
			$data = $data->toArray();
		}
		if (!$data || !count($data)) {
			return false;
		}
		$this->pageData = $data;
		if ($data['page_type'] && (sys_PageManager::TYPE_PAGE ==  $data['page_type'] || sys_PageManager::TYPE_DERIVED_PAGE == $data['page_type'])) {
			return true;
		}
		return false;
	}

	protected function getBoxplaces() {
		$data = $this->pageHandler->getBoxPlacesByTemplate($this->pageData['Template']['id']);
		if (is_object($data)) {
			$data = $data->toArray();
		}
		return $data;
	}

	protected function getBoxplaceNames($boxplaces) {
		$names = array();
		foreach($boxplaces as $val) {
			$names[$val['boxplace']] = '{boxplace.'.$val['boxplace'].'}';
		}
		return $names;
	}
}
?>