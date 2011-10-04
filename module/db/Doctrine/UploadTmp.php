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
 * Uploaded temp file Doctrine database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_Doctrine_UploadTmp extends module_db_DoctrineDbModule implements module_db_interface_UploadTmp {

	/**
	 * @see module_db_interface_UploadTmp::addFile()
	 *
	 * @param string $filename
	 * @param string $origName
	 * @return integer
	 */
	public function addFile($filename, $origName) {
		$file = new UploadTmpData();
		$file['filename'] = $filename;
		$file['orig_name'] = $origName;
		$file['upload_time'] = date('Y-m-d H:i:s');
		$file->save();
		return $file['id'];
	}

	/**
	 * @see module_db_interface_UploadTmp::deleteFile()
	 *
	 * @param integer $id
	 */
	public function deleteFile($id) {
		$file = $this->conn->queryOne('FROM UploadTmpData WHERE id=?', array((int)$id));
		if ($file && count($file)) {
			$file->delete();
		}
	}

	/**
	 * @see module_db_interface_UploadTmp::getOldFiles()
	 *
	 * @return array
	 */
	public function getOldFiles() {
		return $this->normalizeResults($this->conn->query('FROM UploadTmpData WHERE upload_time < ?', array(date('Y-m-d H:i:s', (time()-3600)))));
	}

	/**
	 * @see module_db_interface_UploadTmp::getFile()
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getFile($id) {
		return $this->normalizeResults($this->conn->queryOne('FROM UploadTmpData WHERE id=?', array((int)$id)));
	}

}
?>