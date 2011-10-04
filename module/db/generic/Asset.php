<?php

/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 12533 $
 */

/**
 * generic asset database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 12533 $
 */
class module_db_generic_Asset extends module_db_DbModule implements module_db_interface_Asset,
    module_db_interface_AdminList
{

    /**
     * @see module_db_interface_Asset::getFolderInfoById()
     *
     * @param integer $folderId
     */
    public function getFolderInfoById ($folderId)
    {
        return $this->conn->selectFirst(
            array('table' => 'asset_folder_data' ,
                'where' => 'id=' . (int) $folderId));
    }

    /**
     * @see module_db_interface_Asset::getAssetFolders()
     *
     * @param integer $typeId
     * @return array
     */
    public function getAllFoldersByTypeId ($typeId)
    {
        $query = array(
            'table' => 'asset_folder_data f INNER JOIN asset_data a ON a.folder_id=f.id' ,
            'fields' => 'DISTINCT f.*');
        $query['where'] = 'a.asset_type_id=' . (int) $typeId;
        return $this->conn->select($query);
    }

    /**
     * @see module_db_interface_Asset::getAssetTypes()
     *
     * @return array
     */
    public function getTypes ()
    {
        return $this->conn->select(array('table' => 'asset_type_data'));
    }

    /**
     * @see module_db_interface_Asset::getAllFolders()
     *
     * @return array
     */
    public function getAllFolders ()
    {
        return $this->conn->select(array('table' => 'asset_folder_data', 'orderBy'=>'name ASC'));
    }

    /**
     * @see module_db_interface_AdminList::getListResultCount()
     *
     * @param integer $localeId
     * @param integer $folder
     * @param boolean $subFolders
     * @param array $filter
     * @return array
     */
    public function getListResultCount ($localeId, $folder = null, $subFolders = false, $filter = null)
    {
        $query = array('table' => 'asset_data' ,
            'fields' => $this->conn->getFunc('COUNT', array('id')) . ' AS item_count');
        $query['where'] = ' folder_id = ' . (int) $folder;
        if (is_array($filter) && count($filter)) {
            $schema = $this->conn->getTableSchema('asset_data');
            foreach ($filter as $name => $val) {
                if (isset($schema['columns'][$name])) {
                    if ($name == 'asset_type_id') {
                        $query['where'] .= ' AND ' .
                             $name .
                             ' = ' .
                             $this->conn->quote(
                                $val);
                    } else {
                        $query['where'] .= ' AND ' .
                             $name .
                             ' LIKE ' .
                             $this->conn->quote(
                                $val .
                                 '%');
                    }
                }
            }
        }
        $count = $this->conn->selectFirst($query);
        return $count['item_count'];

    }

    /**
     * @see module_db_interface_AdminList::listItems()
     *
     * @param integer $localeId
     * @param integer $folder
     * @param integer $limit
     * @param integer $offset
     * @param boolean $subFolders
     * @param array $filter
     * @return array
     */
    public function listItems ($localeId, $folder = null, $limit = null, $offset = null, $subFolders = false,
        $filter = null, $orderBy = null, $orderDir = null)
    {
        $query = array('table' => 'asset_data' );

        $query['where'] = ' folder_id = ' . (int) $folder;
        $schema = $this->conn->getTableSchema('asset_data');
        if (is_array($filter) && count($filter)) {
            foreach ($filter as $name => $val) {
                if (isset($schema['columns'][$name])) {
                    if ($name == 'asset_type_id') {
                        $query['where'] .= ' AND ' .
                             $name .
                             ' = ' .
                             $this->conn->quote(
                                $val);
                    } else {
                        $query['where'] .= ' AND ' .
                             $name .
                             ' LIKE ' .
                             $this->conn->quote(
                                $val .
                                 '%');
                    }
                }
            }
        }
        $extra = '';
        if ((int) $limit) {
            $query['limit'] = (int) $limit;
            if ((int) $offset < 0) {
                $offset = 0;
            }
            $query['offset'] = (int) $offset;
        }
        if ($orderBy) {
            if (! isset($schema['columns'][$orderBy])) {
                $orderBy = 'name';
            }
            switch (strtolower($orderDir)) {
                case 'desc':
                case '-':
                    $query['orderBy'] .= $orderBy .
                         ' DESC';
                    break;
                default:
                    $query['orderBy'] .= $orderBy .
                         ' ASC';
                    break;
            }
        }
        return $this->conn->select($query);
    }

    /**
     * @see module_db_interface_Asset::saveVideoToQueue()
     *
     * @param integer $assetId
     * @param string $srcFile
     * @param string $dstFile
     */
    public function saveVideoToQueue ($assetId, $srcFile, $dstFile)
    {
        $video = $this->conn->getDefaultRecord('video_queue_data');
        $video['asset_id'] = (int) $assetId;
        $video['source_file'] = $srcFile;
        $video['destination_file'] = $dstFile;
        $this->conn->insert('video_queue_data', $video);
    }

    /**
     * @see module_db_interface_Asset::getQueuedVideos()
     *
     * @return array
     */
    public function getQueuedVideos ()
    {
        return $this->conn->select(
            array('table' => 'video_queue_data' , 'oderBy' => 'id'));
    }

    /**
     * @see module_db_interface_Asset::removeVideoFromQueue()
     *
     * @param integer $queueId
     */
    public function removeVideoFromQueue ($queueId)
    {
        $this->conn->delete('video_queue_data', 'id=' . (int) $queueId, 1);
    }

    /**
     * @see module_db_interface_Asset::getFolderByDocpath()
     *
     * @param string $docpath
     * @return array
     */
    public function getFolderByDocpath ($docpath)
    {
        $this->conn->selectFirst(
            array('table' => 'asset_folder_data' ,
                'where' => 'docpath=' . $this->conn->quote(
                    $docpath)));
    }

    /**
     * @see module_db_interface_Asset::addAssetTypeData()
     *
     * @param array $assets
     */
    public function addAssetTypeData ($assets)
    {
        if (! is_array($assets)) {
            return $assets;
        }
        $types = array();
        $subtypes = array();
        foreach ($assets as &$asset) {
            if ($asset['asset_type_id']) {
                if (! is_array(
                    $types[$asset['asset_type_id']])) {
                    $types[$asset['asset_type_id']] = $this->conn->selectFirst(
                        array(
                            'table' => 'asset_type_data' ,
                            'where' => 'id=' .
                                 (int) $asset['asset_type_id']));
                }
                $asset['Type'] = $types[$asset['asset_type_id']];
            }
            if ($asset['asset_subtype_id']) {
                if (! is_array(
                    $subtypes[$asset['asset_subtype_id']])) {
                    $subtypes[$asset['asset_subtype_id']] = $this->conn->selectFirst(
                        array(
                            'table' => 'asset_subtype_data' ,
                            'where' => 'id=' .
                                 (int) $asset['asset_subtype_id']));
                }
                $asset['Subtype'] = $subtypes[$asset['asset_subtype_id']];
            }
        }
        return $assets;
    }

    /**
     * @see module_db_interface_Asset::getAssetSubtypeByExt()
     *
     * @param integer $assetType
     * @param string $extension
     * @return array
     */
    public function getAssetSubtypeByExt ($assetType, $extension)
    {
        $data = $this->conn->selectFirst(
            array('table' => 'asset_subtype_data' ,
                'where' => 'asset_type_id=' . (int) $assetType .
                     ' AND extension=' . $this->conn->quote(
                        $extension)));
        if (! count($data)) {
            return $this->conn->selectFirst(
                array('table' => 'asset_subtype_data' ,
                    'where' => 'asset_type_id=' .
                         (int) $assetType .
                         ' AND is_default=' .
                         $this->conn->getBool(
                            true)));
        }
        return $data;
    }

    /**
     * @see module_db_interface_Asset::createFolder()
     *
     * @param integer $parentId
     * @param string $name
     * @param string $short
     * @return integer
     */
    public function createFolder ($parentId, $name, $short)
    {
        $parent = $this->conn->selectFirst(array('table'=>'asset_folder_data', 'where'=>'id='.(int)$parentId));
        if (! $parent || ! count($parent)) {
            return null;
        }
        $folder = $this->conn->getDefaultRecord('asset_folder_data');
        $folder['parent_id'] = $parentId;
        $folder['name'] = $name;
        $folder['short'] = $short;
        $folder['docpath'] = $parent['docpath'] . '/' . $short;
        $this->conn->insert('asset_folder_data', $folder);
        return $folder['id'];
    }

    /**
     * @see module_db_interface_Asset::deleteResizeItem()
     *
     * @param integer $itemId
     */
    public function deleteResizeItem ($itemId)
    {
        return $this->basicDelete('asset_resizer_data', $itemId);
    }

    /**
     * @see module_db_interface_Asset::insertResizeItem()
     *
     * @param array $itemData
     * @return string
     */
    public function insertResizeItem ($itemData)
    {
        return $this->basicInsert('asset_resizer_data', $itemData);
    }

    /**
     * @see module_db_interface_Asset::loadResizeItem()
     *
     * @param integer $itemId
     * @return array
     */
    public function loadResizeItem ($itemId)
    {
        return $this->basicLoad('asset_resizer_data', $itemId);
    }

    /**
     * @see module_db_interface_Asset::updateResizeItem()
     *
     * @param integer $itemId
     * @param array $itemData
     * @return string
     */
    public function updateResizeItem ($itemId, $itemData)
    {
        return $this->basicUpdate('asset_resizer_data', $itemId, $itemData);
    }

    /**
     * @see module_db_interface_Asset::getResizeList()
     *
     * @return array
     */
    public function getResizeList ()
    {
        return $this->getBasicIdSelectList('asset_resizer_data');
    }

    /**
     * @see module_db_interface_Asset::getResizers()
     *
     */
    public function getResizers ()
    {
        return $this->conn->select(array('table'=>'asset_resizer_data', 'orderBy'=>'name ASC'));
    }

}
?>