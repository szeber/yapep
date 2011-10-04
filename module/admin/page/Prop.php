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
 * Page property form
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_page_Prop extends sys_admin_AdminModule {

	/**
	 * @var module_db_interface_Page
	 */
	protected $pageHandler;

	/**
	 * @see sys_admin_AdminModule::buildForm()
	 */
	public function buildForm() {

		$this->pageHandler = getPersistClass('Page');

		$this->setDbHandler($this->pageHandler);

		$this->setAddBtnDisabled();

		$this->setTitle(_('Page property editor'));

		$this->panel->refreshForm('page_Tree');

		if (!$this->subModule[0]) {
			$control = new sys_admin_control_PopupFormButton();
			$control->setLabel(_('New subpage'));
			$control->setTargetForm('page_Prop');
			$control->setTargetSubFormField('id');
			$control->setWindowTitle(_('New subpage'));
			$this->addControl($control, 'newSubpageButton');

			$control = new sys_admin_control_Label();
			$control->setLabel(_('Path'));
			$this->addControl($control, 'docpath');
		}

		$control = new sys_admin_control_HiddenInput();
		if ($this->subModule[0]) {
			$control->setDefaultValue($this->subModule[0]);
			$control->setValue($this->subModule[0], true);
			$control->setReadOnly();
		}
		$this->addControl($control, 'parent_id');

		$control = new sys_admin_control_Label();
		$control->setLabel(_('Page ID'));
		$this->addControl($control, 'id');

		$control = new sys_admin_control_Label();
		$control->setLabel(_('Page path'));
		$this->addControl($control, 'path');

		$control = new sys_admin_control_SelectInput();
		$control->setLabel(_('Type'));
		$control->setDisplayOn(sys_admin_interface_Input::DISPLAY_ADD);
		$control->addOptions(array(sys_PageManager::TYPE_FOLDER=>_('Folder'), sys_PageManager::TYPE_PAGE=>_('Page'), sys_PageManager::TYPE_DERIVED_PAGE=>_('Derived page')));
		$control->setRequired();
		$this->addControl($control, 'page_type');

		$templateHandler = getPersistClass('Template');
		$control = new sys_admin_control_SelectInput();
		$control->setLabel(_('Template'));
		$control->setDisplayOn(sys_admin_interface_Input::DISPLAY_ADD);
		$control->addOptions($templateHandler->getTemplateList());
		$control->setRequired();
		$this->addControl($control, 'template_id');

		$themeHandler = getPersistClass('Theme');
		$control = new sys_admin_control_SelectInput();
		$control->setLabel(_('Theme'));
		$control->setDisplayOn(sys_admin_interface_Input::DISPLAY_ADD);
		$control->addOptions($themeHandler->getThemeList());
		$control->setRequired();
		$this->addControl($control, 'theme_id');

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Page name'));
		$control->setRequired();
		$this->addControl($control, 'name');

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Short name'));
		$control->setRequired();
		$this->addControl($control, 'short_name');

		$control = new sys_admin_control_CheckBox();
		$control->setLabel(_('Default document page'));
		$control->setDisplayOn(sys_admin_interface_Input::DISPLAY_EDIT);
		$this->addControl($control, 'docPage');

		$control = new sys_admin_control_CheckBox();
		$control->setLabel(_('Main page'));
		$control->setDisplayOn(sys_admin_interface_Input::DISPLAY_EDIT);
		$this->addControl($control, 'mainPage');

		$control = new sys_admin_control_BrowserList ();
		$control->setLabel (_ ('Folder page'));
		$control->setDisplayOn(sys_admin_interface_Input::DISPLAY_EDIT);
		$control->setDataForm ('folder_Prop');
		$control->setBrowserForm ('folder_Browser');
		$control->setDisplayTemplate ('{$name} ({$docpath})');
		$control->setDataField ('id');
		$this->addControl ($control, 'FolderPage');

		$control = new sys_admin_control_BrowserList ();
		$control->setLabel (_ ('Folder document page'));
		$control->setDisplayOn(sys_admin_interface_Input::DISPLAY_EDIT);
		$control->setDataForm ('folder_Prop');
		$control->setBrowserForm ('folder_Browser');
		$control->setDisplayTemplate ('{$name} ({$docpath})');
		$control->setDataField ('id');
		$this->addControl ($control, 'FolderDocPage');

		$control = new sys_admin_control_BrowserList ();
		$control->setLabel (_ ('Document page'));
		$control->setDisplayOn(sys_admin_interface_Input::DISPLAY_EDIT);
		$control->setDataForm ('Doc/*');
		$control->setBrowserForm ('object_Browser');
		$control->setDisplayTemplate ('{$docPath}/{$docname}');
		$control->setDataField ('id');
		$this->addControl ($control, 'DocPage');
	}

	protected function postSave() {
		$data = $this->pageHandler->getPageData($this->id);
		if ($this->data['mainPage']) {
			$this->pageHandler->addDefaultPageToTheme($data['theme_id'], $data['id'], module_db_interface_Page::TYPE_MAIN_PAGE);
		}
		if ($this->data['docPage']) {
			$this->pageHandler->addDefaultPageToTheme($data['theme_id'], $data['id'], module_db_interface_Page::TYPE_DEFAULT_DOC);
		}

		$cache = new sys_cache_PageCacheManager();
		$cache->recreateCache();
		$cache = new sys_cache_FolderCacheManager ();
		$cache->recreateCache ();
	}

	protected function postDelete() {
		$cache = new sys_cache_PageCacheManager();
		$cache->recreateCache();
		$cache = new sys_cache_FolderCacheManager ();
		$cache->recreateCache ();
	}

	/**
	 * @see sys_admin_AdminModule::processSaveData()
	 *
	 */
	protected function processSaveData() {
		if (!$this->data['parent_id'] ) {
			unset($this->data['parent_id']);
		}
		if ($this->mode == sys_admin_AdminModule::MODE_EDIT) {
			unset($this->data['page_type']);
			unset($this->data['template_id']);
			unset($this->data['theme_id']);
		} else {
			if ($this->data['page_type'] != sys_PageManager::TYPE_PAGE) {
				if ($this->data['page_type'] == sys_PageManager::TYPE_DERIVED_PAGE) {
					if (!$this->data['parent_id']) {
						throw new sys_exception_AdminException(_('Can\'t create a derived page without a parent!'), sys_exception_AdminException::ERR_SAVE_ERROR);
					}
					$parent = $this->pageHandler->getPageData($this->data['parent_id']);
					if ($parent['page_type'] == sys_PageManager::TYPE_FOLDER && $this->data['page_type'] == sys_PageManager::TYPE_DERIVED_PAGE) {
						throw new sys_exception_AdminException(_('Can\'t create a derived page in a folder!'), sys_exception_AdminException::ERR_SAVE_ERROR);
					}
					$this->data['template_id'] = $parent['template_id'];
					$this->data['theme_id'] = $parent['theme_id'];
				}
			}
		}
		$this->savePageObjectRels($this->data['FolderPage'], module_db_interface_Page::TYPE_FOLDER);
		$this->savePageObjectRels($this->data['FolderDocPage'], module_db_interface_Page::TYPE_FOLDER_DOC);
        /*
         * HACK
         *
         * Flex admin sends the full docpath instead of the doc ID. Workaround to fix  it.
         */
        if(is_array($this->data['DocPage'])) {
            foreach($this->data['DocPage'] as $key=>$docPage) {
                if (is_numeric($docPage)) {
                    continue;
                }
                $doc = sys_DocFactory::getDocByDocPath($this->localeId, $docPage);
                if (!is_object($doc)) {
                    continue;
                }
                $doc = $doc->getDocData();
                $this->data['DocPage'][$key] = $doc['id'];
            }
        }
        // /HACK
		$this->savePageObjectRels($this->data['DocPage'], module_db_interface_Page::TYPE_DOC);
	}

	/**
	 * Saves page-object relations to the database
	 *
	 * @param array $objectArr
	 * @param integer $type
	 */
	protected function savePageObjectRels($objectArr, $type) {
		if ($objectArr) {
			if (is_array($objectArr)) {
				$objects = $objectArr;
			} else {
				$objects = explode (',', $objectArr);
			}
			foreach ( $objects as $key => $object ) {
				$object = trim ($object);
				if (!(int) $object) {
					unset ($objects [$key]);
					continue;
				}
				$objects [$key] = $object;
				$this->pageHandler->savePageObject ($type, $this->id, (int) $object);
			}
			$this->pageHandler->deletePageObjects ($type, $this->id, $objects);
		} else {
			$this->pageHandler->deletePageObjects ($type, $this->id);
		}
	}

	protected function processLoadData() {
		if ($this->mode == sys_admin_AdminModule::MODE_EDIT) {
			if (count($this->pageHandler->getDefaultPageThemes($this->id, module_db_interface_Page::TYPE_MAIN_PAGE))) {
				$this->data['mainPage'] = true;
			}
			if (count($this->pageHandler->getDefaultPageThemes($this->id, module_db_interface_Page::TYPE_DEFAULT_DOC))) {
				$this->data['docPage'] = true;
			}
		}
		$folderPage = $this->getControl ('FolderPage');
		$folder = array ();
		$folderDocPage = $this->getControl ('FolderDocPage');
		$folderDoc = array ();
		$docPage = $this->getControl ('DocPage');
		$doc = array ();
		foreach ( $this->data ['Rels'] as $rel ) {
			switch ( $rel ['relation_type']) {
				case module_db_interface_Page::TYPE_FOLDER :
					$folder [] = array ('id' => $rel ['Object'] ['id'], 'display' => $rel ['Object'] ['name'] . ' (' . $rel ['Object'] ['FullObject'] ['docpath'] . ')');
					break;
				case module_db_interface_Page::TYPE_FOLDER_DOC :
					$folderDoc [] = array ('id' => $rel ['Object'] ['id'], 'display' => $rel ['Object'] ['name'] . ' (' . $rel ['Object'] ['FullObject'] ['docpath'] . ')');
					break;
				case module_db_interface_Page::TYPE_DOC :
					$doc [] = array ('id' => $rel ['Object'] ['id'], 'display' => $rel ['Object'] ['name']);
					break;
			}
		}
		$folderPage->setValue ($folder, true);
		$folderDocPage->setValue ($folderDoc, true);
		$docPage->setValue ($doc, true);
	}


}
?>