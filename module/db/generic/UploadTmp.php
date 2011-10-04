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
 * Uploaded temp file generic database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_generic_UploadTmp extends module_db_DbModule implements module_db_interface_UploadTmp
{

    /**
     * @see module_db_interface_UploadTmp::addFile()
     *
     * @param string $filename
     * @param string $origName
     * @return integer
     */
    public function addFile ($filename, $origName)
    {
        $file = array();
        $file['filename'] = $filename;
        $file['orig_name'] = $origName;
        $file['upload_time'] = date('Y-m-d H:i:s');
        $this->conn->insert('upload_tmp_data', $file);
        return $file['id'];
    }

    /**
     * @see module_db_interface_UploadTmp::deleteFile()
     *
     * @param integer $id
     */
    public function deleteFile ($id)
    {
        $this->conn->delete('upload_tmp_data', 'id=' . (int) $id);
    }

    /**
     * @see module_db_interface_UploadTmp::getOldFiles()
     *
     * @return array
     */
    public function getOldFiles ()
    {
        return $this->conn->select(
            array('table' => 'upload_tmp_data' ,
            'where' => 'uoload time < ' . $this->conn->quote(
                date('Y-m-d H:i:s', (time() - 3600)))));
    }

    /**
     * @see module_db_interface_UploadTmp::getFile()
     *
     * @param integer $id
     * @return array
     */
    public function getFile ($id)
    {
        return $this->basicLoad('upload_tmp_data', $id);
    }

}
?>