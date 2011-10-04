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
 * Folder generic database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_generic_FolderImage extends module_db_DbModule implements module_db_interface_FolderImage {

    public function getImagesForFolder($folderId) {
        return $this->conn->select(
            array(
                'table'=>'folder_image_data',
                'where'=>'folder_id='.(int)$folderId,
                'orderBy'=>'type',
            )
        );
    }

    public function getImageForFolderByType($folderId, $type) {
        return $this->conn->selectFirst(
            array(
                'table'=>'folder_image_data',
                'where'=>'folder_id='.(int)$folderId.' AND type='.(int)$type,
            )
        );
    }

    public function replaceFolderImages($folderId, array $images) {
        $this->conn->delete('folder_image_data', 'folder_id='.(int)$folderId);
        foreach($images as $image) {
            $record = $this->conn->getDefaultRecord('folder_image_data');
            $this->modifyData($record, $image);
            $this->conn->insert('folder_image_data', $record);
        }
    }

}
?>