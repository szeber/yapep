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
 * Document link generic Database Module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_generic_DocLink extends module_db_DbModule implements module_db_interface_DocLink,
    module_db_interface_AdminList
{

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
        $query = array(
        'table' => 'doc_link_data l JOIN folder_data f on f.id=l.folder_id JOIN doc_data d ON d.id=l.doc_id' .
             ' JOIN folder_data df ON df.id=d.folder_id' ,
            'fields' => $this->conn->getFunc('COUNT', array('l.id')) . ' AS itemCount');
        if ($subFolders) {
            $folderData = $this->conn->selectFirst(
                array('table' => 'folder_data' ,
                'where' => 'id=' . (int) $folder));
            $query['where'] = ' f.docpath LIKE ' . $this->conn->quote(
                $folderData['docpath'] . '%');
        } else {
            $query['where'] = ' l.folder_id = ' . (int) $folder;
        }
        if (is_array($filter) && count($filter)) {
            $schema = $this->conn->getTableSchema('doc_data');
            foreach ($filter as $name => $val) {
                $val = $this->conn->quote($val . '%');
                if ($name == 'name') {
                    $query['where'] .= ' AND d.name LIKE ' .
                         $val;
                } elseif ($name == 'docpath') {
                    $query['where'] .= ' AND df.docpath LIKE ' .
                         $val;
                } elseif ($name == 'docname') {
                    $query['where'] .= ' AND d.docname LIKE ' .
                         $val;
                } elseif (isset($schema['columns'][$name])) {
                    $query['where'] .= ' AND ' .
                         $name .
                         ' LIKE ' .
                         $val;
                }
            }
        }
        $count = $this->conn->selectFirst($query);
        return $count['itemCount'];
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
     * @param string $orderBy
     * @param string $orderDir
     * @return array
     */
    public function listItems ($localeId, $folder = null, $limit = null, $offset = null, $subFolders = false,
        $filter = null, $orderBy = null, $orderDir = null)
    {
        $fieldMap = $this->getMappableFields(array('l'=>'doc_link_data', 'f'=>'folder_data', 'd'=>'doc_data', 'df'=>'folder_data'));
        $query = array(
        'table' => 'doc_link_data l JOIN folder_data f on f.id=l.folder_id JOIN doc_data d ON d.id=l.doc_id' .
             ' JOIN folder_data df ON df.id=d.folder_id' ,
            'fields' => $this->getFieldListFromMap($fieldMap));
        if ($subFolders) {
            $folderData = $this->conn->selectFirst(
                array('table' => 'folder_data' ,
                'where' => 'id=' . (int) $folder));
            $query['where'] = ' f.docpath LIKE ' . $this->conn->quote(
                $folderData['docpath'] . '%');
        } else {
            $query['where'] = ' l.folder_id = ' . (int) $folder;
        }
        $schema = $this->conn->getTableSchema('doc_data');
        if (is_array($filter) && count($filter)) {
            foreach ($filter as $name => $val) {
                $val = $this->conn->quote($val . '%');
                if ($name == 'name') {
                    $query['where'] .= ' AND d.name LIKE ' .
                         $val;
                } elseif ($name == 'docpath') {
                    $query['where'] .= ' AND df.docpath LIKE ' .
                         $val;
                } elseif ($name == 'docname') {
                    $query['where'] .= ' AND d.docname LIKE ' .
                         $val;
                } elseif (isset($schema['columns'][$name])) {
                    $query['where'] .= ' AND ' .
                         $name .
                         ' LIKE ' .
                         $val;
                }
            }
        }
        if (!is_null($orderBy)) {
            if (!isset($schema['columns'][$orderBy])) {
                $orderBy = 'name';
            }
            switch(strtolower($orderDir)) {
                case' desc':
                case '-':
                    $query['orderBy'] = 'd.'.$orderBy.' DESC';
                    break;
                default:
                    $query['orderBy'] = 'd.'.$orderBy.' ASC';
                    break;
            }
        }
        if ((int)$limit) {
            $query['limit'] = (int)$limit;
            if ((int)$offset < 0) {
                $offset = 0;
            }
            $query['offset'] = (int)$offset;
        }
        $tmp = $this->getDataFromMappedFields($fieldMap, $this->conn->select($query));
        if (! $tmp) {
            return array();
        }
        $data = array();
        foreach ($tmp as $link) {
            $link['l']['name'] = $link['d']['name'];
            $link['l']['docpath'] = $link['df']['docpath'];
            $link['l']['docname'] = $link['d']['docname'];
            $data[] = $link['l'];
        }
        return $data;
    }

}
?>