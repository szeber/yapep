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
 * Folder property form
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_folder_Prop extends sys_admin_AdminModule {

	/**
	 * Stores the folder database handler instance
	 *
	 * @var module_db_interface_Folder
	 */
	protected $folderHandler;

	/**
	 * @see sys_admin_AdminModule::buildForm()
	 */
	public function buildForm() {

		$this->folderHandler = getPersistClass ('Folder');

		$this->setAddBtnDisabled ();

		$this->setDbHandler ($this->folderHandler);

		$this->setTitle (_ ('Folder property editor'));

		$this->panel->refreshForm ('folder_Tree');

		if (empty($this->subModule [0])) {
			$control = new sys_admin_control_PopupFormButton ();
			$control->setLabel (_ ('New subfolder'));
			$control->setTargetForm ('folder_Prop');
			$control->setTargetSubFormField ('id');
			$control->setWindowTitle (_ ('New subfolder'));
			$control->setDisplayOn(sys_admin_interface_Input::DISPLAY_EDIT);
			$this->addControl ($control, 'newSubfolderButton');

			$this->addDefaultObjectFields ();

			$control = new sys_admin_control_Label ();
			$control->setLabel (_ ('Folder path'));
			$this->addControl ($control, 'docpath');
		} else {
			$this->addDefaultObjectFields ();
		}

		$control = new sys_admin_control_HiddenInput ();
		if (!empty($this->subModule [0])) {
			$control->setDefaultValue ($this->subModule [0]);
			$control->setValue ($this->subModule [0], true);
			$control->setReadOnly ();
		}
		$this->addControl ($control, 'parent_id');

		$control = new sys_admin_control_TextInput ();
		$control->setLabel (_ ('Folder name'));
		$control->setRequired ();
		$this->addControl ($control, 'name');

		$control = new sys_admin_control_TextInput ();
		$control->setLabel (_ ('Folder short name'));
		$control->setValidateMaxLength (32);
		$this->addControl ($control, 'short');

		$control = new sys_admin_control_SelectInput ();
		$control->setLabel (_ ('Folder type'));
		$control->addOptions ($this->getFolderTypes ());
		$control->setNullValueLabel(_('--- Please select ---'));
		$control->setRequired ();
		$this->addControl ($control, 'folder_type_id');

		$control = new sys_admin_control_CheckBox ();
		$control->setLabel (_ ('Active in all languages'));
		$this->addControl ($control, 'allLanguages');

		$control = new sys_admin_control_CheckBox ();
		$control->setLabel (_ ('Visible'));
		$this->addControl ($control, 'visible');

		$control = new sys_admin_control_CheckBox ();
		$control->setLabel (_ ('In sitemap'));
		$this->addControl ($control, 'sitemap');

		$control = new sys_admin_control_CheckBox ();
		$control->setLabel (_ ('Sitemap link'));
		$this->addControl ($control, 'sitemap_link');

		$control = new sys_admin_control_TextInput ();
		$control->setLabel (_ ('Sitemap description'));
		$this->addControl ($control, 'sitemap_desc');

		$control = new sys_admin_control_TextInput ();
		$control->setLabel (_ ('Folder order'));
		$control->setRequired ();
		$control->setDefaultValue(10);
		$this->addControl ($control, 'folder_order');

		$control = new sys_admin_control_BrowserList ();
		$control->setLabel (_ ('Folder page'));
		$control->setDataForm ('page_Prop');
		$control->setBrowserForm ('page_Browser');
		$control->setDisplayTemplate ('{$name} ({$path})');
		$control->setDataField ('id');
		$this->addControl ($control, 'FolderPage');

		$control = new sys_admin_control_BrowserList ();
		$control->setLabel (_ ('Document page'));
		$control->setDataForm ('page_Prop');
		$control->setBrowserForm ('page_Browser');
		$control->setDisplayTemplate ('{$name} ({$path})');
		$control->setDataField ('id');
		$this->addControl ($control, 'DocPage');

		$control = new sys_admin_control_CheckBox ();
		$control->setLabel (_ ('Virtual subfolders'));
		$this->addControl ($control, 'virtual_subfolders');

		$control = new sys_admin_control_TextInput ();
		$control->setLabel (_ ('Virtual handler'));
		$this->addControl ($control, 'virtual_handler');

        if ($_SESSION['LoggedInAdminData']['superuser']) {
            $control = new sys_admin_control_SubItemList();
            $control->setLabel(_('Access control'));
            $control->setValueField('name');
            $control->setNameField('id');
            $control->setAddFieldLabel(_('New user or group'));
            $control->setSubForm('cms_FolderAcl');
            $this->addControl($control, 'FolderAcl');
        }
	}

	protected function getFolderTypes() {
		$types = $this->folderHandler->getFolderTypes ();
		$folderTypes = array ();
		foreach ( $types as $type ) {
			$folderTypes [$type ['id']] = $type ['description'];
		}
		return $folderTypes;
	}

	protected function postSave() {
		$cache = new sys_cache_FolderCacheManager ();
		$cache->recreateCache ();
	}

	protected function postDelete() {
		$this->postSave();
	}

	/**
	 * @see sys_admin_AdminModule::processLoadData()
	 *
	 */
	protected function processLoadData() {
        $aclHandler = getPersistClass('AdminGroup');
        $this->data['FolderAcl'] = $aclHandler->loadObjectAcls(module_db_interface_AdminGroup::MODE_FOLDER, $this->id);
		$allLang = $this->getControl ('allLanguages');
		if ($this->data ['locale_id']) {
			$allLang->setValue (false, true);
		} else {
			$allLang->setValue (true, true);
		}
		if (!count ($this->data ['Pages'])) {
			return;
		}
		$folderPage = $this->getControl ('FolderPage');
		$folder = array ();
		$docPage = $this->getControl ('DocPage');
		$doc = array ();
		foreach ( $this->data ['Pages'] as $page ) {
			switch ( $page ['relation_type']) {
				case module_db_interface_Page::TYPE_FOLDER :
					$folder [] = array ('id' => $page ['Page'] ['id'], 'display' => $page ['Page'] ['name'] . ' (' . $page ['Page'] ['path'] . ')');
					break;
				case module_db_interface_Page::TYPE_FOLDER_DOC :
					$doc [] = array ('id' => $page ['Page'] ['id'], 'display' => $page ['Page'] ['name'] . ' (' . $page ['Page'] ['path'] . ')');
					break;
			}
		}
		$folderPage->setValue ($folder, true);
		$docPage->setValue ($doc, true);
	}

	/**
	 * @see sys_admin_AdminModule::processSaveData()
	 *
	 */
	protected function processSaveData() {
		if ($this->mode == sys_admin_AdminModule::MODE_ADD && $this->data ['parent_id'] == 0) {
			unset ($this->data ['parent_id']);
		}
		// TODO exception on errors
		if ($this->data ['allLanguages']) {
			$this->data ['locale_id'] = null;
		} else {
			$localeHandler = getPersistClass ('LangLocale');
			$locale = $localeHandler->getLocaleByCode ($this->locale);
			$this->data ['locale_id'] = $locale ['id'];
		}
		if ($this->data['short']) {
			$this->data['short'] = convertStringToDocname($this->data['short']);
		} else {
			$this->data['short'] = convertStringToDocname($this->data['name']);
		}
		if ($this->mode != sys_admin_AdminModule::MODE_EDIT) {
			return;
		}
		if ($this->data ['FolderPage']) {
			if (is_array($this->data['FolderPage'])) {
				$pages = $this->data['FolderPage'];
			} else {
				$pages = explode (',', $this->data ['FolderPage']);
			}
			foreach ( $pages as $key => &$page ) {
				$page = trim ($page);
				if (!(int) $page) {
					unset ($pages [$key]);
					continue;
				}
				$this->folderHandler->saveFolderPage (module_db_interface_Page::TYPE_FOLDER, $this->id, (int) $page);
			}
			$this->folderHandler->deleteFolderPages (module_db_interface_Page::TYPE_FOLDER, $this->id, $pages);
		} else {
			$this->folderHandler->deleteFolderPages (module_db_interface_Page::TYPE_FOLDER, $this->id);
		}
		if ($this->data ['DocPage']) {
			if (is_array($this->data['DocPage'])) {
				$pages = $this->data['DocPage'];
			} else {
				$pages = explode (',', $this->data ['DocPage']);
			}
			foreach ( $pages as $key => &$page ) {
				$page = trim ($page);
				if (!(int) $page) {
					unset ($pages [$key]);
					continue;
				}
				$this->folderHandler->saveFolderPage (module_db_interface_Page::TYPE_FOLDER_DOC, $this->id, (int) $page);
			}
			$this->folderHandler->deleteFolderPages (module_db_interface_Page::TYPE_FOLDER_DOC, $this->id, $pages);
		} else {
			$this->folderHandler->deleteFolderPages (module_db_interface_Page::TYPE_FOLDER_DOC, $this->id);
		}
	}

}
?>