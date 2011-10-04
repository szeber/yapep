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
 * Virtual folder handler interface
 *
 * @package	YAPEP
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface sys_VirtualHandler extends module_db_interface_AdminList
{

    /**
     * Returns the folder subtree that's managed by this handler
     *
     * @param integer $rootFolder
     * @param string $imageRoot
     * @return array
     */
    public function getAdminFolderTree ($rootFolder, $imageRoot);

    /**
     * Returns the virtual folder's information for the list
     *
     * @param integer $rootFolder
     * @param string $path
     * @return array
     */
    public function getAdminFolderInfo ($rootFolder, $path);

    /**
     * Retruns the object specified by $path
     *
     * Throws a sys_exception_SiteException with 404 error code if the specified object is not found
     *
     * @param integer $rootFolder
     * @param string $path
     * @return array
     * @throws sys_exception_SiteException
     */
    public function getObject ($rootFolder, $path);

    /**
     * Returns the pageId specified by $path
     *
     * Throws a sys_exception_SiteException with 404 error code if the specified object is not found
     *
     * @param string $rootFolder
     * @param string $path
     * @return integer
     * @throws sys_exception_SiteException
     */
    public function getPageIdForPath ($rootFolder, $path);
}
?>