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
 * Object database interface
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface module_db_interface_Object {

	/**
	 * Returns the count of objects for the given object type and filters
	 *
	 * @param integer $typeId
	 * @param array $filter
	 * @return integer
	 */
	public function getListCountForObjectType($typeId, $filter = null);

	/**
	 * Returns the object list for the given object type and filters
	 *
	 * @param integer $typeId
	 * @param integer $limit
	 * @param integer $offset
	 * @param array $filter
	 * @param string $orderBy
	 * @param string $orderDir
	 * @return array
	 */
	public function getListForObjectType($typeId, $limit=null, $offset=null, $filter=null, $orderBy=null, $orderDir=null);

	/**
	 * Returns the list of all related objects for the specified object
	 *
	 * @param integer $objectId
	 * @param integer $relationType Returns all relations if NULL, or the specified relation type otherwise
	 * @return array
	 */
	public function getRelList($objectId, $relationType = null);

	/**
	 * Replaces the list of an object's relations for a given relation type
	 *
	 * @param integer $objectId
	 * @param integer $relationType
	 * @param array$rels
	 * @return boolean
	 */
	public function replaceObjectRels($objectId, $relationType, $rels);

	/**
	 * Returns an object by its ID
	 *
	 * @param integer $objectId
	 * @return array
	 */
	public function getObjectById($objectId);

	/**
	 * Returns the full object by a given ID
	 *
	 * @param integer $objectId
	 */
	public function getFullObjectById($objectId);
}
?>