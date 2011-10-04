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
 * Document object interface
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface module_db_interface_DocObject {

	/**
	 * Returns the full object by the document's ID
	 *
	 * @param integer $docId
	 * @param boolean $inactive
	 */
	public function getObjectByDocId($docId, $inactive = false);

	/**
	 * Finds doc objects of the given object type
	 *
	 * @see sys_DocFactory::findDoc()
	 * @param integer $localeId
	 * @param string $startPath
	 * @param string $text
	 * @param integer $limit
	 * @param integer $offset
	 * @param array $includeFolders
	 * @param array $excludeFolders
	 * @param boolean $inactive
	 * @return array
	 */
	public function findDoc($localeId, $startPath, $text, $limit = -1, $offset = 0, $includeFolders = array(), $excludeFolders = array(), $inactive = false);

	/**
	 * Returns the number of doc objects of the given type
	 *
	 * @param integer $localeId
	 * @param string $startPath
	 * @param string $text
	 * @param array $includeFolders
	 * @param array $excludeFolders
	 * @param boolean $inactive
	 * @return integer
	 */
	public function getFindDocCount($localeId, $startPath, $text, $includeFolders = array(), $excludeFolders = array(), $inactive = false);
}
?>