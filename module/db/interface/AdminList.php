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
 * Admin DB module interface
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface module_db_interface_AdminList {

	/**
	 * Lists items in a folder
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
	public function listItems($localeId, $folder = null, $limit = null, $offset = null, $subFolders = false, $filter = null, $orderBy = null, $orderDir = null);

	/**
	 * Returns the count of items that match the filters in a folder
	 *
	 * @param integer $localeId
	 * @param integer $folder
	 * @param boolean $subFolders
	 * @param array $filter
	 * @return array
	 */
	public function getListResultCount($localeId, $folder = null, $subFolders = false, $filter = null);
}
?>