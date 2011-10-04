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
 * Docfactory database interface
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface module_db_interface_DocFactory {

	/**
	 * Returns the document type data based on the document ID
	 *
	 * @param integer $docId
	 * @return array
	 */
	public function getDocTypeByDocId($docId);

	/**
	 * Returns the document type data based on the document name, path and language
	 *
	 * @param string $localeId
	 * @param string $docpath
	 * @param string $docname
	 * @return array
	 */
	public function getDocTypeByDocPathName($localeId, $docPath, $docName);

	/**
	 * Returns information for all document types
	 *
	 * @return array
	 */
	public function getDoctypeList();

	/**
	 * Returns the document ID based on the document's name, path and language
	 *
	 * @param string $localeId
	 * @param string $docPath
	 * @param string $docName
	 * @param boolean $inactive If true also returns the id for inactive documents
	 * @return integer	The ID or null if not found
	 */
	public function getDocIdByDocPath($localeId, $docPath, $docName, $inactive = false);

	/**
	 * Returns the object type ID by it's short name
	 *
	 * @param string $shortName
	 * @return integer
	 */
	public function getDocTypeIdByShortName($shortName);
}
?>