<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Attila Danch <da@scp.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Doctrine page database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Attila Danch <da@scp.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_Doctrine_Page extends module_db_DoctrineDbModule implements module_db_interface_Page, module_db_interface_Admin {

	/**
	 * Static array storing the default document ids
	 *
	 * @var array
	 */
	private static $defaultDocIds = array ();

	/**
	 * @see module_db_interface_Admin::deleteItem()
	 *
	 * @param integer $itemId
	 */
	public function deleteItem($itemId) {
		return $this->basicDelete ('CmsPageData', $itemId);
	}

	/**
	 * @see module_db_interface_Admin::insertItem()
	 *
	 * @param array $itemData
	 * @return string
	 */
	public function insertItem($itemData) {
		try {
			$ignoreFields = array ('path');
			$data = new CmsPageData ();
			if ($itemData ['parent_id']) {
				$parentData = $this->conn->queryOne ('SELECT path FROM CmsPageData WHERE id = ?', array ((int) $itemData ['parent_id']));
				$data ['path'] = $parentData ['path'] . '/' . $itemData ['short_name'];
			} else {
				$data ['path'] = $itemData ['short_name'];
				$ignoreFields [] = 'parent_id';
			}
			$this->modifyData ($data, $itemData, $ignoreFields);
			$data->save ();
			if ($data['page_type'] == sys_PageManager::TYPE_DERIVED_PAGE && $data['parent_id']) {
				$parent = $this->conn->query('FROM CmsPageBoxData b LEFT JOIN b.Params p WHERE b.page_id = ?', array($data['parent_id']));
				if ($parent) {
					foreach ($parent as $parentBox) {
						$box = new CmsPageBoxData();
						$box['page_id'] = $data['id'];
						$box['parent_id'] = $parentBox['id'];
						$box['boxplace_id'] = $parentBox['boxplace_id'];
						$box['module_id'] = $parentBox['module_id'];
						$box['name'] = $parentBox['name'];
						$box['box_order'] = $parentBox['box_order'];
						$box['active'] = $parentBox['active'];
						$box['status'] = module_admin_page_Box::STATUS_ACTIVE_INHERITED | module_admin_page_Box::STATUS_ORDER_INHERITED;
						$box->save();
						foreach($parentBox['Params'] as $parentParam) {
							$param = new CmsPageBoxParamData();
							$param['parent_id'] = $parentParam['id'];
							$param['module_param_id'] = $parentParam['module_param_id'];
							$param['page_box_id'] = $box['id'];
							$param['value'] = $parentParam['value'];
							$param['is_var'] = $parentParam['is_var'];
							$param['inherited'] = true;
							$param->save();
						}
					}
				}
			}
			return $data ['id'];
		} catch ( Doctrine_Exception $e ) {
			return $e->getMessage ();
		}
	}

	/**
	 * @see module_db_interface_Admin::loadItem()
	 *
	 * @param integer $itemId
	 * @return array
	 */
	public function loadItem($itemId) {
		$data = $this->conn->queryOne ('FROM CmsPageData p LEFT JOIN p.Rels r LEFT JOIN r.Object WHERE id = ?', array ($itemId));
		foreach ($data['Rels'] as $rel) {
			if (is_object($rel['Object'])) {
				sys_db_DoctrineHelper::mapFullObject($rel['Object']);
			}
		}
		return $data;
	}

	/**
	 * @see module_db_interface_Admin::updateItem()
	 *
	 * @param integer $itemId
	 * @param array $itemData
	 * @return string
	 */
	public function updateItem($itemId, $itemData) {
		try {
			$ignoreFields = array ('id', 'path', 'short_name', 'parent_id');
			$data = $this->conn->queryOne ('FROM CmsPageData WHERE id = ?', array ((int) $itemId));
			if (!is_object ($data)) {
				return 'Unable to update item because it was not found';
			}
			if (isset ($itemData ['short_name']) && (string) $itemData ['short_name'] != (string) $data ['short_name']) {
				$newPath = substr ($data ['path'], 0, (-1 * strlen ($data ['short_name']))) . $itemData ['short_name'];
				$data ['short_name'] = $itemData ['short_name'];
				$data ['path'] = $newPath;
				$this->updateSubpagePath ($data ['id'], $newPath);
			}
			$this->modifyData ($data, $itemData, $ignoreFields);
			$data->save ();
			return '';
		} catch ( Doctrine_Exception $e ) {
			return $e->getMessage ();
		}
	}

	/**
	 * Updates the path for a pages's subpages
	 *
	 * @param integer $pageId
	 * @param string $path
	 */
	public function updateSubpagePath($pageId, $path) {
		$folders = $this->conn->query ('FROM CmsPageData WHERE parent_id = ?', array ($pageId));
		if (!count ($folders)) {
			return;
		}
		foreach ( $folders as $folder ) {
			$folder ['path'] = $path . '/' . $folder ['short_name'];
			$folder->save ();
			$this->updateSubpagePath ($folder ['id'], $folder ['path']);
		}
	}

	/**
	 * Returns boxes for given cms_page_id and boxplace_id
	 *
	 * @param integer $pageId
	 * @param integer $boxplaceId
	 * @return array
	 */
	public function getBoxesByPageId($pageId, $boxplaceId) {
		return $this->conn->query ('FROM CmsPageBoxData p INNER JOIN p.Module WHERE page_id = ? AND boxplace_id = ? ORDER BY box_order ASC, name ASC', array ($pageId, $boxplaceId));
	}

	/**
	 * Returns params for given cms page box
	 *
	 * @param integer $boxId
	 * @return array
	 */
	public function getBoxParamByBoxId($boxId) {
		return $this->conn->query ('FROM CmsPageBoxParamData b INNER JOIN b.ModuleParam m WHERE b.page_box_id = ?', array ($boxId));
	}

	/**
	 * Returns pages and derived pages
	 *
	 * @return array
	 */
	public function getPages() {
		return $this->conn->query ('FROM CmsPageData p INNER JOIN p.Template t WHERE p.page_type IN (' . sys_PageManager::TYPE_PAGE . ', ' . sys_PageManager::TYPE_DERIVED_PAGE . ')');
	}

	/**
	 * Returns all pages, folders and derived pages by locale id
	 *
	 * @param integer $localeId
	 */
	public function getPagesByLocaleId($localeId) {
		return $this->conn->query ('FROM CmsPageData WHERE locale_id IS NULL OR locale_id = ? ORDER BY name ASC', array ($localeId));
	}

	/**
	 * Returns boxplaces
	 *
	 * @return array
	 */
	public function getBoxPlaces() {
		return $this->conn->query ('FROM CmsBoxplaceData');
	}

	/**
	 * Returns the boxplaces for a given template
	 *
	 * @param integer $templateId
	 * @return array
	 */
	public function getBoxPlacesByTemplate($templateId) {
		return $this->conn->query ('FROM CmsBoxplaceData WHERE template_id = ?', array ($templateId));
	}

	/**
	 * Returns the list of main page IDs for a given locale
	 *
	 * @param integer $localeId
	 * @return array
	 */
	public function getMainPageIdsForLocale($localeId) {
		return $this->conn->query ('SELECT page_id, theme_id FROM CmsPageObjectRel r INNER JOIN r.Page p WHERE relation_type = '.module_db_interface_Page::TYPE_MAIN_PAGE.' AND (p.locale_id = ? OR p.locale_id IS NULL)', array ($localeId));
	}

	/**
	 * Returns the default document page IDs for a given locale
	 *
	 * @var integer $localeId
	 * @return array
	 */
	public function getDefaultDocPageIdsForLocale($localeId) {
		if (!self::$defaultDocIds [$localeId]) {
			self::$defaultDocIds [$localeId] = $this->conn->query ('SELECT page_id, theme_id FROM CmsPageObjectRel r INNER JOIN r.Page p WHERE relation_type = '.module_db_interface_Page::TYPE_DEFAULT_DOC.' AND (p.locale_id = ? OR p.locale_id IS NULL)', array ($localeId))->toArray();
		}
		return self::$defaultDocIds [$localeId];
	}

	/**
	 * Returns the specified page's data
	 *
	 * @param integer $pageId
	 * @return array
	 */
	public function getPageData($pageId) {
		return $this->conn->queryOne ('FROM CmsPageData p INNER JOIN p.Template WHERE p.id = ?', array ($pageId));
	}

	/**
	 * @see module_db_interface_Page::getBoxPlaceById()
	 *
	 * @param integer $boxplaceId
	 * @return array
	 */
	public function getBoxPlaceById($boxplaceId) {
		return $this->conn->queryOne('FROM CmsBoxplaceData WHERE page_id = ?', array((int)$boxplaceId));
	}

	/**
	 * @see module_db_interface_Page::addDefaultPageToTheme()
	 *
	 * @param integer $themeId
	 * @param integer $pageId
	 * @param integer $type
	 * @return integer
	 */
	public function addDefaultPageToTheme($themeId, $pageId, $type) {
		if ($type != module_db_interface_Page::TYPE_DEFAULT_DOC && $type != module_db_interface_Page::TYPE_MAIN_PAGE) {
			return _('Bad type');
		}
		$rel = $this->conn->queryOne('FROM CmsPageObjectRel WHERE theme_id = ? AND relation_type = ?', array((int)$themeId, (int)$type));
		if (!$rel) {
			$rel = new CmsPageObjectRel();
			$rel['theme_id'] = (int)$themeId;
			$rel['relation_type'] = (int)$type;
		}
		$rel['page_id'] = (int)$pageId;
		$rel->save();
		return $rel['id'];
	}

	/**
	 * @see module_db_interface_Page::getDefaultPageThemes()
	 *
	 * @param integer $pageId
	 * @param integer $type
	 */
	public function getDefaultPageThemes($pageId, $type) {
		if ($type != module_db_interface_Page::TYPE_DEFAULT_DOC && $type != module_db_interface_Page::TYPE_MAIN_PAGE) {
			return array();
		}
		return $this->normalizeResults($this->conn->query('FROM CmsPageObjectRel r INNER JOIN r.Theme WHERE r.page_id = ? AND r.relation_type = ?', array((int)$pageId, (int)$type)));
	}

	/**
	 * @see module_db_interface_Page::getObjectRelById()
	 *
	 * @param integer $relId
	 * @return array
	 */
	public function getObjectRelById($relId) {
		return $this->normalizeResults($this->conn->queryOne('FROM CmsPageObjectRel WHERE id=?', array((int)$relId)));
	}

	/**
	 * @see module_db_interface_Page::deleteObjectRel()
	 *
	 * @param integer $relId
	 */
	public function deleteObjectRel($relId) {
		$rel = $this->conn->queryOne('FROM CmsPageObjectRel WHERE id = ?', array((int)$relId));
		if ($rel) {
			$rel->delete();
		}
	}

	/**
	 * @see module_db_interface_Page::deletePageObjects()
	 *
	 * @param string $type
	 * @param integer $pageId
	 * @param array $dontDeleteObjects
	 * @return string
	 */
	public function deletePageObjects($type, $pageId, $dontDeleteObjects = array()) {
		try {
			$extra = '';
			if (count ($dontDeleteObjects)) {
				$extra = ' AND object_id NOT IN (' . implode (', ', $dontDeleteObjects) . ')';
			}
			$this->conn->query ('DELETE FROM CmsPageObjectRel WHERE relation_type = ? AND page_id = ?' . $extra, array ((string) $type, (int) $pageId));
			return '';
		} catch ( Doctrine_Exception $e ) {
			return $e->getMessage ();
		}
	}

	/**
	 * @see module_db_interface_Page::savePageObject()
	 *
	 * @param integer $type
	 * @param integer $pageId
	 * @param integer $objectId
	 * @return boolean
	 */
	public function savePageObject($type, $pageId, $objectId) {
		$objectData = $this->conn->queryOne ('FROM ObjectData WHERE id = ?', array ((int) $objectId));
		if (!is_object ($objectData)) {
			return 'Object not found';
		}
		$pageData = $this->conn->queryOne ('FROM CmsPageData WHERE id = ?', array ((int) $pageId));
		$objectPage = $this->conn->queryOne ('FROM CmsPageObjectRel WHERE object_id = ? AND relation_type = ? AND theme_id = ?', array ((int) $objectId, (int) $type, (int) $pageData ['theme_id']));
		try {
			if (is_object ($objectPage)) {
				if ($objectPage ['page_id'] != (int) $pageId) {
					$objectPage ['page_id'] = (int) $pageId;
					$objectPage->save ();
				}
			} else {
				$objectPage = new CmsPageObjectRel ();
				$objectPage ['object_id'] = (int) $objectId;
				$objectPage ['page_id'] = (int) $pageId;
				$objectPage ['theme_id'] = (int) $pageData ['theme_id'];
				$objectPage ['relation_type'] = (int) $type;
				$objectPage->save ();
			}
			return '';
		} catch ( Doctrine_Exception $e ) {
			return $e->getMessage ();
		}
	}

	/**
	 * @see module_db_interface_Page::getPageIdForObject()
	 *
	 * @param integer $objectId
	 * @param integer $themeId
	 * @param integer $type
	 * @return integer
	 */
	public function getPageIdForObject($objectId, $themeId, $type) {
		$data = $this->conn->queryOne('FROM CmsPageObjectRel WHERE object_id = ? AND theme_id = ? AND relation_type = ?', array((int)$objectId, (int)$themeId, (int)$type));
		if ($data && count($data)) {
			return $data['page_id'];
		}
		return null;
	}

}
?>