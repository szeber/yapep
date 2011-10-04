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
 * Uploaded temp file database interface
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface module_db_interface_UploadTmp {

	/**
	 * Adds a new file to the table and returns it's ID
	 *
	 * @param string $filename
	 * @param string $origName
	 * @return integer
	 */
	public function addFile($filename, $origName);

	/**
	 * Returns a file's info by it's ID
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getFile($id);

	/**
	 * Deletes a file from the table
	 *
	 * @param integer $id
	 */
	public function deleteFile($id);

	/**
	 * Returns the list of old files
	 *
	 * @return array
	 */
	public function getOldFiles();

}
?>