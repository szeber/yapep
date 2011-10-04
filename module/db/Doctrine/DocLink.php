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
 * Document link Doctrine Database Module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_Doctrine_DocLink extends module_db_DoctrineDbModule implements module_db_interface_DocLink, module_db_interface_AdminList {

	/**
	 * @see module_db_interface_AdminList::getListResultCount()
	 *
	 * @param integer $localeId
	 * @param integer $folder
	 * @param boolean $subFolders
	 * @param array $filter
	 * @return array
	 */
	public function getListResultCount($localeId, $folder = null, $subFolders = false, $filter = null) {
		$filters = '';
		$filterArr = array();
		if ($subFolders) {
			$folderData = $this->conn->queryOne('FROM FolderData WHERE id = ?', array($folder));
			$filters = ' f.docpath LIKE ?';
			$filterArr[] = $folderData['docpath'].'%';
		} else {
			$filters = ' folder_id = ?';
			$filterArr[] = $folder;
		}
		if (is_array($filter) && count($filter)) {
			$rec = new DocData();
			foreach($filter as $name=>$val) {
				if ($name == 'name') {
					$filters .= ' AND d.name LIKE ?';
					$filterArr[] = $val.'%';
				} elseif ($name == 'docpath') {
					$filters .= ' AND df.docpath LIKE ?';
					$filterArr[] = $val.'%';
				} elseif ($name == 'docname') {
					$filters .= ' AND d.docname LIKE ?';
					$filterArr[] = $val.'%';
				} elseif ($rec->contains($name)) {
									$filters .= ' AND '.$name.' LIKE ?';
					$filterArr[] = $val.'%';
				}
			}
		}
		$count = $this->conn->queryOne('SELECT COUNT(id) as itemCount FROM DocLinkData l INNER JOIN l.Folder f INNER JOIN l.Doc d INNER JOIN d.Folder df WHERE'.$filters, $filterArr);
		return $count['itemCount'];
	}

	/**
	 * @see module_db_interface_AdminList::listItems()
	 *
	 * @param integer $localeId
	 * @param integer $folder
	 * @param integer $limit
	 * @param integer $offset
	 * @param boolean $subFolders
	 * @param array $filter
	 * @param string $orderBy
	 * @param string $orderDir
	 * @return array
	 */
	public function listItems($localeId, $folder = null, $limit = null, $offset = null, $subFolders = false, $filter = null, $orderBy = null, $orderDir = null) {
		$filters = '';
		$filterArr = array();
		if ($subFolders) {
			$folderData = $this->conn->queryOne('FROM FolderData WHERE id = ?', array($folder));
			$filters = ' f.docpath LIKE ?';
			$filterArr[] = $folderData['docpath'].'%';
		} else {
			$filters = ' folder_id = ?';
			$filterArr[] = $folder;
		}
		$extra = '';
		if (is_array($filter) && count($filter)) {
			$rec = new DocLinkData();
			foreach($filter as $name=>$val) {
				if ($name == 'name') {
					$filters .= ' AND d.name LIKE ?';
					$filterArr[] = $val.'%';
				} elseif ($name == 'docpath') {
					$filters .= ' AND df.docpath LIKE ?';
					$filterArr[] = $val.'%';
				} elseif ($name == 'docname') {
					$filters .= ' AND d.docname LIKE ?';
					$filterArr[] = $val.'%';
				} elseif ($rec->contains($name)) {
					$filters .= ' AND '.$name.' LIKE ?';
					$filterArr[] = $val.'%';
				}
			}
		}
		if ((int)$limit) {
			if (!is_null($orderBy)) {
				$doc = new DocLinkData();
				if (!isset($doc[$orderBy])) {
					$orderBy = 'd.name';
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
		$tmp = $this->conn->query('FROM DocLinkData l INNER JOIN l.Folder f INNER JOIN l.Doc d INNER JOIN d.Folder df WHERE'.$filters.$extra, $filterArr);
		if (!$tmp) {
			return array();
		}
		$data = array();
		foreach($tmp as $link) {
			$link = $link->toArray();
			$link['name'] = $link['Doc']['name'];
			$link['docpath'] = $link['Doc']['Folder']['docpath'];
			$link['docname'] = $link['Doc']['docname'];
			$data[] = $link;
		}
		return $data;
	}

}
?>