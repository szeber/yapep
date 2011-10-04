<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	Database
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

 /**
 * Query listener interface
 *
 * @package	YAPEP
 * @subpackage	Database
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface sys_db_ModifyListener {

    /**
     * Runs on insert
     *
     * @param sys_db_Database $db
     * @param string $table
     * @param array $values
     */
	public function onInsert(sys_db_Database $db, $table, array &$values);

	/**
	 * Runs on insert
	 *
	 * @param sys_db_Database $db
	 * @param array $query
	 * @param array $values
	 */
	public function onUpdate(sys_db_Database $db,array &$query, array &$values);

	/**
	 * Runs on delete
	 *
	 * @param sys_db_Database $db
	 * @param string $table
	 * @param string $where
	 * @param integer $limit
	 */
	public function onDelete(sys_db_Database $db,$table, &$where, $limit);
}
?>