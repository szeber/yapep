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
 * Doctrine docfactory database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_Doctrine_DocFactory extends module_db_DoctrineDbModule implements module_db_interface_DocFactory {

	/**
	 * Returns the document type data
	 *
	 * @param string $where SQL WHERE
	 * @param array $params Bound parameters
	 * @return array
	 */
	protected function getDocType($where, $params) {
		$data = $this->conn->queryOne ('SELECT d.id, ot.* FROM DocData d INNER JOIN d.Folder INNER JOIN d.RefObjectType ot INNER JOIN d.Folder f WHERE ' . $where, $params);
		return $data ['RefObjectType'];
	}

	/**
	 * Returns the document type data based on the document ID
	 *
	 * @param integer $docId
	 * @return array
	 */
	public function getDocTypeByDocId($docId) {
		return $this->getDocType ('d.id = ?', array ($docId));
	}

	/**
	 * Returns the document type data based on the document name, path and language
	 *
	 * @param integer $localeId
	 * @param string $docPath
	 * @param string $docName
	 * @return array
	 */
	public function getDocTypeByDocPathName($localeId, $docPath, $docName) {
		return $this->getDocType ('(f.locale_id IS NULL OR f.locale_id = ?) AND f.docpath = ? AND d.docname = ?', array ($localeId, $docPath, $docName));
	}

	/**
	 * Returns information for all document types
	 *
	 * @return array
	 */
	public function getDoctypeList() {
		$tmp = $this->conn->query ('FROM ObjectTypeData');
		$doctypes = array ();
		foreach ( $tmp as $val ) {
			$doctypes [$val ['id']] = $val->toArray ();
		}
		return $doctypes;
	}

	/**
	 * Returns the document ID based on the document's name, path and language
	 *
	 * @param integer $localeId
	 * @param string $docPath
	 * @param string $docName
	 * @param boolean $inactive If true also returns the id for inactive documents
	 * @return integer	The ID or null if not found
	 */
	public function getDocIdByDocPath($localeId, $docPath, $docName, $inactive = false) {
		$extra = '';
		if (!$inactive) {
			$extra .= ' AND start_date <= NOW() AND end_date >= NOW() AND status = '.module_db_interface_Doc::STATUS_ACTIVE;
		}
		$docData = $this->conn->queryOne ('SELECT d.id FROM DocData d INNER JOIN d.Folder f WHERE (f.locale_id IS NULL OR f.locale_id = ?) AND f.docpath = ? AND d.docname = ?' . $extra, array ($localeId, $docPath, $docName));
		if (!$docData ['id']) {
			return null;
		}
		return $docData ['id'];
	}

	/**
	 * @see module_db_interface_DocFactory::getDocTypeIdByShortName()
	 *
	 * @param string $shortName
	 * @return integer
	 */
	public function getDocTypeIdByShortName($shortName) {
		return $this->getObjectTypeIdByShortName($shortName);
	}

}
?>