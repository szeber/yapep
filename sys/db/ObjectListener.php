<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	Database
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Object listener
 *
 * @package	YAPEP
 * @subpackage	Database
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_db_ObjectListener implements sys_db_ModifyListener {


	/**
	 * getTimestamp
	 *
	 * Gets the timestamp in the correct format
	 *
	 * @return string
	 */
	protected function getTimestamp() {
		return date ('Y-m-d H:i:s', time ());
	}

	/**
	 * getCreator
	 *
	 * Returns the currently logged in user's ID
	 *
	 * @return integer
	 */
	protected function getCurrentUser() {
		// FIXME Implement retrieving of current user
		if (defined ('CURRENT_USER_ID')) {
			return CURRENT_USER_ID;
		}
		return null;
	}

    /**
     * @see sys_db_ModifyListener::onDelete()
     *
     * @param sys_db_Database $db
     * @param string $table
     * @param string $where
     * @param integer $limit
     */
    public function onDelete (sys_db_Database $db, $table, &$where, $limit)
    {}

    /**
     * @see sys_db_ModifyListener::onInsert()
     *
     * @param sys_db_Database $db
     * @param string $table
     * @param array $values
     */
    public function onInsert (sys_db_Database $db, $table, array &$values)
    {
        $user = $this->getCurrentUser();
        if (is_null($user)) {
            $user = 'NULL';
        }
        if (!isset($values['creator']) || is_null($values['creator'])) {
            $values['creator'] = $user;
        } else if (0 === $values['creator']) {
            $values['creator'] = 'NULL';
        }
        if (!isset($values['updater']) || is_null($values['updater'])) {
            $values['updater'] = $values['creator'];
        } else if (0 === $values['updater']) {
            $values['updater'] = 'NULL';
        }
        $values['created_at'] = $db->getFunc('NOW');
        $values['updated_at'] = $db->getFunc('NOW');
    }

    /**
     * @see sys_db_ModifyListener::onUpdate()
     *
     * @param sys_db_Database $db
     * @param array $query
     * @param array $values
     */
    public function onUpdate (sys_db_Database $db, array &$query, array &$values)
    {
        $user = $this->getCurrentUser();
        if (is_null($user)) {
            $user = 'NULL';
        }
        if (!isset($values['updater']) || is_null($values['updater'])) {
            $values['updater'] = $user;
        } else if (0 === $values['updater']) {
            $values['updater'] = 'NULL';
        }
        $values['updated_at'] = $db->getFunc('NOW');
    }

}
?>