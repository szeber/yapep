<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Document module base
 *
 * @package	YAPEP
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
abstract class sys_DocModule {

	/**
	 * If ture, loads a document even if it's inactive
	 *
	 * @var boolean
	 */
	public $loadInactive = false;

	/**
	 * Persistance layer
	 *
	 * @var module_db_interface_DocObject
	 */
	protected $db;

	/**
	 * Document data
	 *
	 * @var array
	 */
	protected $docData = array ();

	/**
	 * Document type
	 *
	 * @var string
	 */
	protected $doctype;

    /**
     * @var module_db_interface_Object
     */
    protected $objectDb;

    /**
	 * Constructor
	 *
	 * @param mmodule_db_generic_Doc $db
	 * @param string $docType
	 * @param module_db_interface_Object $objectDb
	 */
	public function __construct(module_db_interface_DocObject $db, $docType = null, module_db_interface_Object $objectDb = null) {
		$this->db = $db;
		$this->doctype = $docType;
		if (is_null($objectDb)) {
		    $objectDb = getPersistClass('Object');
		}
		$this->objectDb = $objectDb;
	}

	/**
	 * Loads a document from the database
	 *
	 * @param integer $docId
	 */
	public function loadDoc($docId) {
		$this->setDoc ($this->db->getObjectByDocId ($docId, $this->loadInactive));
	}

	/**
	 * Finds and returns the documents containing $text
	 *
	 * @param string $lang
	 * @param string $startPath
	 * @param string $text
	 * @param integer $limit
	 * @param integer $offset
	 * @param array $includeFolders
	 * @param array $excludeFolders
	 * @param boolean $inactive
	 * @return sys_DocModule[]
	 */
	public function findDoc($localeId, $startPath, $text, $limit = -1, $offset = 0, $includeFolders = array(), $excludeFolders = array(), $inactive = false) {
		$docDataList = $this->db->findDoc ($localeId, $startPath, $text, $limit, $offset, $includeFolders, $excludeFolders, $inactive);
		$docObjectList = array ();
		$className = get_class ($this);
		foreach ( $docDataList as $val ) {
			$tmp = new $className ($this->db);
			$tmp->setDoc ($val);
			$docObjectList [] = $tmp;
		}
		return $docObjectList;
	}

	/**
	 * Finds and returns the documents containing $text
	 *
	 * @param string $lang
	 * @param string $startPath
	 * @param string $text
	 * @param integer $limit
	 * @param integer $offset
	 * @param array $includeFolders
	 * @param array $excludeFolders
	 * @param boolean $inactive
	 * @return sys_DocModule[]
	 */
	public function getFindDocCount($localeId, $startPath, $text, $includeFolders = array(), $excludeFolders = array(), $inactive = false) {
		return $this->db->getFindDocCount ($localeId, $startPath, $text, $includeFolders, $excludeFolders, $inactive);
	}

	/**
	 * Returns the document data
	 *
	 * @return array
	 */
	public function getDocData() {
		return $this->docData;
	}

	/**
	 * Returns the document's template file
	 *
	 * @return string
	 */
	public function getDocTemplate() {
		return $this->docData ['template_file'];
	}

	/**
	 * Sets the document data
	 *
	 * @param array $docData
	 */
	protected function setDoc($docData) {
		if (is_object ($docData)) {
			$docData = $docData->toArray ();
		}
		$this->docData = $docData;
	}
}
?>