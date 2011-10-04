<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Doctrine doc database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_Doctrine_Doc extends module_db_DoctrineDbModule implements module_db_interface_Admin, module_db_interface_AdminList, module_db_interface_Doc {

	/**
	 * @see module_db_interface_Admin::deleteItem()
	 *
	 * @param integer $itemId
	 */
	public function deleteItem($itemId) {
		$data = $this->conn->queryOne ( 'FROM DocData WHERE id = ?', array (( int ) $itemId ) );
		if (is_object ( $data )) {
			$data->delete ();
		}
	}

	/**
	 * @see module_db_Admin::insertItem()
	 *
	 * @param array $itemData
	 * @return string
	 */
	public function insertItem($itemData) {
		if ($itemData['docObjectTypeName'] == 'docCopy') {
			$objectId = $itemData['object_id'];
			$objTypeId = $itemData['ref_object_type_id'];
		} else {
			$objTypeData = $this->conn->queryOne('FROM ObjectTypeData WHERE name = ?', array($itemData['docObjectTypeName']));
			$persistClass = $objTypeData['persist_class'];
			$objectId = $this->basicInsert($persistClass, $itemData, array(), $objTypeData['id']);
			$objTypeId = $objTypeData['id'];
		}
		$doc = new DocData();
		$doc['object_type_id'] = $this->getObjectTypeIdByShortName('document');
		$doc['name'] = $itemData['doctitle'];
		$doc['object_id'] = $objectId;
		$doc['ref_object_type_id'] = $objTypeId;
		$doc['docname'] = $itemData['docname'];
		$doc['start_date'] = $itemData['start_date'];
		$doc['end_date'] = $itemData['end_date'];
		$doc['status'] = (int)$itemData['status'];
		$doc['folder_id'] = $itemData['docFolderId'];
		$doc['locale_id'] = $itemData['docLocale'];
		$doc->save();
		return $doc['id'];
	}

	/**
	 * @see module_db_Admin::listItems()
	 *
	 * @param integer $localeId
	 * @param integer $folder
	 * @param integer $limit
	 * @param integer $offset
	 * @param boolean $subFolders
	 * @param array $filter
	 * @return array
	 */
	public function listItems($localeId, $folder = null, $limit = null, $offset = null, $subFolders = false, $filter = null, $orderBy = null, $orderDir = null) {
		$filters = 'd.locale_id = ?';
		$filterArr = array((int)$localeId);
		if ($subFolders) {
			$folderData = $this->conn->queryOne('FROM FolderData WHERE id = ?', array($folder));
			$filters .= ' AND f.docpath LIKE ?';
			$filterArr[] = $folderData['docpath'].'%';
		} else {
			$filters .= ' AND folder_id = ?';
			$filterArr[] = $folder;
		}
		$extra = '';
		if (is_array($filter) && count($filter)) {
			$rec = new DocData();
			foreach($filter as $name=>$val) {
				if ($rec->contains($name)) {
					$filters .= ' AND '.$name.' LIKE ?';
					$filterArr[] = $val.'%';
				}
			}
		}
		if ((int)$limit) {
			if (!is_null($orderBy)) {
				$doc = new DocData();
				if (!isset($doc[$orderBy])) {
					$orderBy = 'name';
				}
				$extra .= ' ORDER BY '.$orderBy;
				switch (strtolower($orderDir)) {
					case 'desc':
					case '-':
						$extra .= ' DESC';
						break;
					default:
						$extra .= ' ASC';
						break;
				}
			}
			$extra .= ' LIMIT '.(int)$limit;
			if ((int)$offset<0) {
				$offset = 0;
			}
			$extra .= ' OFFSET '.(int)$offset;
		}
		return $this->conn->query('FROM DocData d INNER JOIN d.Folder f WHERE '.$filters.$extra, $filterArr);
	}

	/**
	 * Returns the count of items that match the filters in a folder
	 *
	 * @param integer $localeId
	 * @param integer $folder
	 * @param boolean $subFolders
	 * @param array $filter
	 * @return integer
	 */
	public function getListResultCount($localeId, $folder = null, $subFolders = false, $filter = null) {
		$filters = 'locale_id = ?';
		$filterArr = array((int)$localeId);
		if ($subFolders) {
			$folderData = $this->conn->queryOne('FROM FolderData WHERE id = ?', array($folder));
			$filters .= ' AND f.docpath LIKE ?';
			$filterArr[] = $folderData['docpath'].'%';
		} else {
			$filters .= ' AND folder_id = ?';
			$filterArr[] = $folder;
		}
		if (is_array($filter) && count($filter)) {
			$rec = new DocData();
			foreach($filter as $name=>$val) {
				if ($rec->contains($name)) {
					$filters .= ' AND '.$name.' LIKE ?';
					$filterArr[] = $val.'%';
				}
			}
		}
		$count = $this->conn->queryOne('SELECT COUNT(id) as itemCount FROM DocData d INNER JOIN d.Folder f WHERE '.$filters, $filterArr);
		return $count['itemCount'];
	}

	/**
	 * @see module_db_Admin::loadItem()
	 *
	 * @param integer $itemId
	 * @return array
	 */
	public function loadItem($itemId) {
		$docdata = $this->conn->queryOne('FROM DocData WHERE id = ?', array($itemId));
		if (is_object($docdata)) {
			$docdata = $docdata->toArray();
		}
		$docdata['doctitle'] = $docdata['name'];
		$docdata['docFolderId'] = $docdata['folder_id'];
		$objdata = $this->conn->queryOne('FROM ObjectData WHERE id = ?', array($docdata['object_id']));
		if ($objdata) {
			$objdata = sys_db_DoctrineHelper::getFullObject($objdata)->toArray();
			foreach($objdata as $key=>$val) {
				$docdata[$key] = $val;
			}
		}
		return $docdata;
	}

	/**
	 * @see module_db_Admin::updateItem()
	 *
	 * @param integer $itemId
	 * @param array $itemData
	 * @return string
	 */
	public function updateItem($itemId, $itemData) {
		try {
			$doc = $this->conn->queryOne('FROM DocData WHERE id = ?', array($itemId));
			if (isset($itemData['doctitle']))
				$doc['name'] = $itemData['doctitle'];
			if (isset($itemData['docname']))
				$doc['docname'] = $itemData['docname'];
			if (isset($itemData['doctitle']))
				$doc['start_date'] = $itemData['start_date'];
			if (isset($itemData['end_date']))
				$doc['end_date'] = $itemData['end_date'];
			if (isset($itemData['status']))
				$doc['status'] = (int)$itemData['status'];
			if (isset($itemData['docfolder_id'])) {
				$doc['folder_id'] = $itemData['docfolder_id'];
			}
			$doc->save();
			$object = sys_db_DoctrineHelper::getFullObject($this->conn->queryOne('FROM ObjectData WHERE id = ?', array($doc['object_id'])));
			foreach($object as $key=>$value) {
				if (array_key_exists($key, $itemData) && $value != $itemData[$key]) {
					$object[$key] = $itemData[$key];
				}
			}
			$object->save();
			return '';
		} catch (Doctrine_Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * Returns a document's data by its language, path and name
	 *
	 * @param integer $localeId
	 * @param string $docpath
	 * @param string $docname
	 * @param boolean $inactive
	 * @return array If true, returns the document even if it's inactive
	 */
	public function getDocByDocPath($localeId, $docpath, $docname, $inactive = false) {
		return $this->getDoc ('(f.locale_id IS NULL OR f.locale_id = ?) AND f.docpath = ? AND d.docname = ? AND d.locale_id=?', array ($localeId, $docpath, $docname, $localeId), $inactive);
	}

	/**
	 * Returns a document's data by its ID
	 *
	 * @param integer $docid
	 * @param boolean $inactive
	 * @return array If true, returns the document even if it's inactive
	 */
	public function getDocByDocId($docid, $inactive = false) {
		return $this->getDoc ('d.id = ?', array ($docid), $inactive);
	}

	/**
	 * Returns the object type's data
	 *
	 * @param integer $objectType
	 * @return array
	 */
	public function getObjectTypeData($objectType) {
		return $this->conn->queryOne ('FROM ObjectTypeData WHERE id = ?', array ($objectType));
	}

	/**
	 * Returns a doc's data
	 *
	 * @param string $filter SQL WHERE
	 * @param array $params
	 * @param boolean $inactive If true, returns the document even if it's inactive
	 * @return array
	 */
	protected function getDoc($filter, $params, $inactive = false) {
		$extra = '';
		if (!$inactive) {
			$extra .= ' AND start_date <= NOW() AND end_date >= NOW() AND status = '.module_db_interface_Doc::STATUS_ACTIVE;
		}
		$docData = $this->conn->queryOne ('FROM DocData d INNER JOIN d.Object o INNER JOIN d.Folder f WHERE ' . $filter . $extra, $params);
		if (!$docData) {
			return false;
		}
		$docData->mapValue ('FullObject', sys_db_DoctrineHelper::getFullObject($docData->Object));
		return $docData;
	}

	/**
	 * Returns a document from a folder
	 *
	 * @param integer $localeId
	 * @param string $docPath
	 * @param array $objectTypes
	 * @param integer $queryType
	 * @param integer $limit
	 * @param integer $offset
	 * @param boolean $inactive
	 * @return array
	 */
	public function getDocFromFolder($localeId, $docPath, $objectTypes, $queryType, $limit=1, $offset = 0, $inactive = false) {
		if (!$inactive) {
			$extra .= ' AND d.start_date <= NOW() AND d.end_date >= NOW() AND d.status = '.module_db_interface_Doc::STATUS_ACTIVE;
		}
		if (is_array ($objectTypes)) {
			$tmp = array ();
			foreach ( $objectTypes as $val ) {
				if (is_numeric($val)) {
					$tmp [] = (int) $val;
				} else {
					$tmp [] = (int)$this->getObjectTypeIdByShortName($val);
				}
			}
			$extra .= ' AND d.ref_object_type_id IN ( ' . implode (', ', $tmp) . ' )';
		} elseif ($objectTypes) {
			if (!is_numeric($objectTypes)) {
				$objectTypes = (int)$this->getObjectTypeIdByShortName($objectTypes);
			}
			$extra .= ' AND d.ref_object_type_id IN ( ' . $objectTypes . ' )';
		}
		switch ( $queryType) {
			case module_db_interface_Doc::TYPE_NAME:
				$order = 'd.name ASC, id ASC';
				break;
			case module_db_interface_Doc::TYPE_RANDOM:
				$order = 'RANDOM()';
				break;
			case module_db_interface_Doc::TYPE_NEWEST :
			default :
				$order = 'd.start_date DESC, id ASC';
				$order2 = 'id DESC';
				break;
		}
		if (!$order2) {
			$order2 = $order;
		}
		$query = 'FROM DocData d INNER JOIN d.Object o INNER JOIN d.Folder f WHERE f.docpath = ?' . $extra . ' ORDER BY ' . $order . ' LIMIT '.(int)$limit.' OFFSET '.(int)$offset;
		$docData = $this->conn->query ($query, array ($docPath));
		$query = 'FROM DocLinkData l INNER JOIN l.Folder f INNER JOIN l.Doc d INNER JOIN d.Object o INNER JOIN d.Folder df WHERE f.docpath = ?' . $extra . ' ORDER BY ' . $order2 . ' LIMIT '.(int)$limit.' OFFSET '.(int)$offset;
		$linkData = $this->conn->query ($query, array ($docPath));
		if (!$docData && !$linkData) {
			return false;
		}
		$docs = array();
		foreach ($docData as $doc) {
			$doc->mapValue ('FullObject', sys_db_DoctrineHelper::getFullObject($doc->Object));
			$docs[] = $doc->toArray();
		}
		foreach ($linkData as $link) {
			$link['Doc']->mapValue('FullObject', sys_db_DoctrineHelper::getFullObject($link['Doc']->Object));
			$docs[] = $link['Doc']->toArray();
		}
		return $docs;
	}

	/**
	 * @see module_db_interface_Doc::getDocsByRelation()
	 *
	 * @param integer $relationType
	 * @param array $relObjectIds
	 * @param array $objectTypes
	 * @param integer $queryType
	 * @param integer $limit
	 * @param integer $offset
	 * @param boolean $inactive
	 * @return array
	 */
	public function getDocsByRelation($relationType, $relObjectIds, $objectTypes, $queryType, $limit=1, $offset = 0, $inactive = false) {
		$extra = '';
		if (!is_array($relObjectIds)) {
			$relObjectIds = array($relObjectIds);
		}
		if (!count($relObjectIds)) {
			return array();
		}
		if (!$inactive) {
			$extra .= ' AND start_date <= NOW() AND end_date >= NOW() AND status = '.module_db_interface_Doc::STATUS_ACTIVE;
		}
		if (is_array ($objectTypes) ) {
			$tmp = array ();
			foreach ( $objectTypes as $val ) {
				if ((int) $val) {
					$tmp [] = (int) $val;
				}
			}
			if (count($tmp)) {
				$extra .= ' AND ref_object_type_id IN ('.implode (', ', $tmp).')';
			}
		}
		switch ( $queryType) {
			case module_db_interface_Doc::TYPE_NAME:
				$order = 'name ASC, id ASC';
				break;
			case module_db_interface_Doc::TYPE_RANDOM:
				$order = 'RANDOM()';
				break;
			case module_db_interface_Doc::TYPE_NEWEST :
			default :
				$order = 'start_date DESC, id ASC';
				break;
		}
		$parentQuery = 'SELECT r.child_id FROM ObjectObjectRel r WHERE r.parent_id IN ('.implode(', ', $relObjectIds).') AND r.relation_type = '.(int)$relationType;
		$childQuery = 'SELECT r2.parent_id FROM ObjectObjectRel r2 WHERE r2.child_id IN ('.implode(', ', $relObjectIds).') AND r2.relation_type = '.(int)$relationType;
		$query = 'FROM DocData d INNER JOIN d.Object o INNER JOIN d.Folder f WHERE (id IN ('.$parentQuery.') OR id IN ('.$childQuery.'))' . $extra . ' ORDER BY '.$order.' LIMIT '.(int)$limit.' OFFSET '.(int)$offset;
		$docData = $this->conn->query($query);
		if (!$docData) {
			return array();
		}
		$docs = array();
		foreach ($docData as $doc) {
			$doc->mapValue ('FullObject', sys_db_DoctrineHelper::getFullObject($doc->Object));
			$docs[] = $doc->toArray();
		}
		return $docs;
	}

	/**
	 * @see module_db_interface_Doc::getDocCountByRelation()
	 *
	 * @param integer $relationType
	 * @param integer $relObjectId
	 * @param array $objectTypes
	 * @param boolean $inactive
	 * @return integer
	 */
	public function getDocCountByRelation($relationType, $relObjectId, $objectTypes, $inactive = false) {
		$extra = '';
		if (!$inactive) {
			$extra .= ' AND start_date <= NOW() AND end_date >= NOW() AND status = '.module_db_interface_Doc::STATUS_ACTIVE;
		}
		if (is_array ($objectTypes) ) {
			$tmp = array ();
			foreach ( $objectTypes as $val ) {
				if (is_numeric($val)) {
					$tmp [] = (int) $val;
				} else {
					$tmp [] = (int)$this->getObjectTypeIdByShortName($val);
				}
			}
			if (count($tmp)) {
				$extra .= ' AND ref_object_type_id IN ('.implode (', ', $tmp).')';
			}
		} elseif (!is_numeric($objectTypes)) {
			$objectTypes = (int)$this->getObjectTypeIdByShortName($objectTypes);
		}
		$parentQuery = 'SELECT r.child_id FROM ObjectObjectRel r WHERE r.parent_id = '.(int)$relObjectId.' AND r.relation_type = '.(int)$relationType;
		$childQuery = 'SELECT r2.parent_id FROM ObjectObjectRel r2 WHERE r2.child_id = '.(int)$relObjectId.' AND r2.relation_type = '.(int)$relationType;
		$query = 'SELECT COUNT(id) as doc_count FROM DocData d INNER JOIN d.Folder f WHERE (id IN ('.$parentQuery.') OR id IN ('.$childQuery.'))' . $extra ;
		$docData = $this->conn->queryOne($query);
		return $docData['doc_count'];
	}

	/**
	 * @see module_db_interface_Doc::getDocCountFromFolder()
	 *
	 * @param integer $localeId
	 * @param unknown_type $docPath
	 * @param unknown_type $objectTypes
	 * @param unknown_type $inactive
	 * @return integer
	 */
	public function getDocCountFromFolder($localeId, $docPath, $objectTypes, $inactive = false) {
		if (!$inactive) {
			$extra .= ' AND d.start_date <= NOW() AND d.end_date >= NOW() AND d.status = '.module_db_interface_Doc::STATUS_ACTIVE;
		}
		if (is_array ($objectTypes)) {
			$tmp = array ();
			foreach ( $objectTypes as $val ) {
				if (is_numeric($val)) {
					$tmp [] = (int) $val;
				} else {
					$tmp [] = (int)$this->getObjectTypeIdByShortName($val);
				}
				$objectTypes = implode (', ', $tmp);
			}
		} elseif (!is_numeric($objectTypes)) {
			$objectTypes = (int)$this->getObjectTypeIdByShortName($objectTypes);
		}
		$query = 'SELECT COUNT(id) as doc_count FROM DocData d INNER JOIN d.Folder f WHERE f.docpath = ? AND d.ref_object_type_id IN ( ' . $objectTypes . ' )' . $extra ;
		$docData = $this->conn->queryOne ($query, array ($docPath));
		$query = 'SELECT COUNT(id) as doc_count FROM DocLinkData l INNER JOIN l.Folder f INNER JOIN l.Doc d INNER JOIN d.Object o INNER JOIN d.Folder df WHERE f.docpath = ? AND d.ref_object_type_id IN ( ' . $objectTypes . ' )' . $extra;
		$linkData = $this->conn->queryOne ($query, array ($docPath));
		return $docData['doc_count']+$linkData['doc_count'];
	}

	/**
	 * @see module_db_interface_Doc::findValidDocname()
	 *
	 * @param string $docName
	 * @param integer $folderId
	 * @param integer $excludeId
	 */
	public function findValidDocname($localeId, $docName, $folderId, $excludeId = 0) {
		$docNameList = $this->conn->queryOne('SELECT docname FROM DocData WHERE docname = ? AND folder_id = ? AND locale_id = ? AND id <> ?', array($docName, (int)$folderId, (int)$localeId, (int)$excludeId));
		if (!$docNameList) {
			return $docName;
		}
		$docNameList = $this->conn->query('SELECT docname FROM DocData WHERE docname LIKE ? AND folder_id = ? AND locale_id = ? AND id <> ? ORDER BY docname ASC', array($docName.'-%', (int)$folderId, (int)$localeId, (int)$excludeId));
		$docNameArr = array();
		foreach($docNameList as $val) {
			$docNameArr[] = $val['docname'];
		}
		unset($docNameList);
		$counter = 2;
		while(in_array($docName.'-'.$counter, $docNameArr)) {
			$counter++;
		}
		return $docName.'-'.$counter;
	}

	/**
	 * @see module_db_interface_Doc::moveFolderDocs()
	 *
	 * @param integer $localeId
	 * @param integer $srcFolder
	 * @param integer $trgtFolder
	 */
	public function moveFolderDocs($localeId, $srcFolder, $trgtFolder) {
		$data = $this->conn->query('FROM DocData WHERE folder_id = ? AND locale_id = ?', array((int)$srcFolder, $localeId));
		foreach($data as $doc) {
			$doc['folder_id'] = (int)$trgtFolder;
		}
		$data->save();
	}

	public function getLatestDocs($localeId, $folderIds, $limit) {
		if (!is_array($folderIds)) {
			$folderIds = array($folderIds);
		}
		foreach($folderIds as $key=>&$folder) {
			$folder = (int)$folder;
			if (!$folder) {
				unset($folderIds[$key]);
			}
		}
		if (!count($folderIds)) {
			return array();
		}
		$extra = $this->makeDocInactiveExtra(false, 'd.');
		$query = 'FROM DocData d INNER JOIN d.Folder WHERE d.locale_id = ? AND d.folder_id IN ('.implode(', ', $folderIds).')'.$extra.' ORDER BY d.start_date DESC LIMIT '.(int)$limit;
		$docData = $this->conn->query ($query, array((int)$localeId));
		$query = 'FROM DocLinkData l INNER JOIN l.Folder f INNER JOIN l.Doc d INNER JOIN d.Object o INNER JOIN d.Folder df WHERE d.locale_id = ? AND l.folder_id IN ('.implode(', ', $folderIds).')' . $extra . ' ORDER BY l.id DESC LIMIT '.(int)$limit.' OFFSET 0';
		$linkData = $this->conn->query ($query, array((int)$localeId));
		if (!$docData && !$linkData) {
			return false;
		}
		$docs = array();
		foreach ($docData as $doc) {
			$doc->mapValue ('FullObject', sys_db_DoctrineHelper::getFullObject($doc->Object));
			$docs[] = $doc->toArray();
		}
		foreach ($linkData as $link) {
			$link['Doc']->mapValue('FullObject', sys_db_DoctrineHelper::getFullObject($link['Doc']->Object));
			$docs[] = $link['Doc']->toArray();
		}
		return $docs;
	}
}
?>