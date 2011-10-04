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
 * Doctrine article object class
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_Doctrine_Article extends module_db_DoctrineDbModule implements module_db_interface_Article, module_db_interface_DocObject {

	/**
	 * @see module_db_interface_DocObject::getObjectByDocId()
	 *
	 * @param integer $docId
	 * @param boolean $inactive
	 */
	public function getObjectByDocId($docId, $inactive = true) {
		$extra = '';
		if (!$inactive) {
			$extra .= " AND status = ".module_db_interface_Doc::STATUS_ACTIVE." AND start_date <= NOW() AND end_date >= NOW()";
		}
		$docData = $this->conn->queryOne ('FROM DocData d INNER JOIN d.Folder WHERE id = ?' . $extra, array ((int) $docId))->toArray ();
		if (!count ($docData)) {
			return array ();
		}
		$docData ['FullObject'] = $this->conn->queryOne ('FROM ArticleData WHERE id = ?', array ($docData ['object_id']))->toArray ();
		return $docData;
	}

	/**
	 * @see module_db_interface_DocObject::findDoc()
	 *
	 * @param integer $localeId
	 * @param string $startPath
	 * @param string $text
	 * @param array $includeFolders
	 * @param array $excludeFolders
	 * @param boolean $inactive
	 * @return array
	 */
	public function findDoc($localeId, $startPath, $text, $limit = -1, $offset = 0, $includeFolders = array(), $excludeFolders = array(), $inactive = false) {
		if ($limit >= 0) {
			$limit = ' LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
		} else {
			$limit = '';
		}
		$query = $this->getFindQuery ($text, $includeFolders, $excludeFolders, $inactive) . ' ORDER BY name ASC' . $limit;
		$data = $this->conn->query ($query, array ($startPath . '%', (int) $localeId, $text, $text, $text, $text));
		return $this->normalizeResults ($data);
	}

	/**
	 * @see module_db_interface_DocObject::getFindDocCount()
	 *
	 * @param integer $localeId
	 * @param string $startPath
	 * @param string $text
	 * @param array $includeFolders
	 * @param array $excludeFolders
	 * @param unknown_type $inactive
	 * @return integer
	 */
	public function getFindDocCount($localeId, $startPath, $text, $includeFolders = array(), $excludeFolders = array(), $inactive = false) {
		$query = 'SELECT COUNT(id) AS doc_count ' . $this->getFindQuery ($text, $includeFolders, $excludeFolders, $inactive);
		$data = $this->conn->queryOne ($query, array ($startPath . '%', (int) $localeId, $text, $text, $text, $text));
		if (!$data) {
			return 0;
		}
		return $data ['doc_count'];
	}

	/**
	 * Returns a DQL query string for searching atricles
	 *
	 * @param array $includeFolders
	 * @param array $excludeFolders
	 * @param boolean $inactive
	 * @return string
	 */
	protected function getFindQuery(&$text, $includeFolders, $excludeFolders, $inactive) {
		$extra = $this->makeDocInactiveExtra ($inactive);
		$folderExtra = $this->makeDocIncludeExtra ($includeFolders);
		$extra .= $this->makeDocExcludeExtra ($excludeFolders);
		$text = '%' . $text . '%';
		$query = 'FROM ArticleData a INNER JOIN a.Docs d INNER JOIN d.Folder f WHERE (f.docpath LIKE ?';
		$query .= $folderExtra . ') AND (f.locale_id = ? OR f.locale_ID IS NULL) AND (d.name LIKE ? OR a.name LIKE ? OR a.lead LIKE ? OR a.content LIKE ?)';
		$query .= $extra;
		return $query;
	}

	/**
	 * @see module_db_interface_Article::getArticlesByIds()
	 *
	 * @param array $idList
	 * @return array
	 */
	public function getArticlesByIds(array $idList) {
		foreach($idList as $key=>$id) {
			if ((int)$id) {
				$idList[$key] = (int)$id;
			} else {
				unset($idList[$key]);
			}
		}
		if (!count($idList)) {
			return array();
		}
		return $this->normalizeResults($this->conn->query('FROM ArticleData a LEFT JOIN a.Docs d LEFT JOIN d.Folder WHERE id IN ('.implode(', ', $idList).') ORDER BY name ASC'));
	}

}
?>