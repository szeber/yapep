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
 * Poeditor generic database access module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_generic_Poeditor extends module_db_DbModule implements module_db_interface_Poeditor
{

    /**
     * @see module_db_interface_Poeditor::scanDbTexts()
     *
     * @return array
     */
    public function scanDbTexts ()
    {
        $texts = array();
        $tmp = $this->conn->select(array('table' => 'object_type_data' ,
        'fields' => 'name'));
        foreach ($tmp as $text) {
            $texts[] = $text['name'];
        }
        $tmp = $this->conn->select(array('table' => 'admin_func_data' , 'fields' => 'name'));
        foreach ($tmp as $text) {
            $texts[] = $text['name'];
        }
        return $texts;
    }

    /**
     * @see module_db_interface_Poeditor::loadTexts()
     *
     * @return array
     */
    public function loadTexts ()
    {
        return $this->conn->select(
            array('table' => 'cms_poeditor_text_data' ,
            'orderBy' => 'text ASC'));
    }

    /**
     * @see module_db_interface_Poeditor::addText()
     *
     * @param string $text
     * @param integer $type
     * @return integer
     */
    public function addText ($text, $type)
    {
        $record = array();
        $record['text'] = $text;
        $record['type'] = (int) $type;
        $this->conn->insert('cms_poeditor_text_data', $record);
        return $record['id'];
    }

    /**
     * @see module_db_interface_Poeditor::deleteObsoleteTexts()
     *
     * @param array $obsoleteIds
     */
    public function deleteObsoleteTexts (array $obsoleteIds)
    {
        foreach ($obsoleteIds as $key => $id) {
            if ($id != (int) $id) {
                unset($obsoleteIds[$key]);
            }
        }
        if (! count($obsoleteIds)) {
            return;
        }
        $this->conn->delete('cms_poeditor_text_data','id IN ('.implode(', ', $obsoleteIds).')');
    }

    /**
     * @see module_db_interface_Poeditor::getListCount()
     *
     * @param integer $type
     * @param string $localeCode
     * @param array $filter
     * @return integer
     */
    public function getListCount ($type, $localeCode, $filter = null)
    {
        $query = array(
            'table'=>'cms_poeditor_text_data',
            'fields'=>$this->conn->getFunc('COUNT', array('id')).' AS text_count',
            'where'=>'type='.(int)$type
        );
        if (is_array($filter) && count($filter)) {
            $textSchema = $this->conn->getTableSchema('cms_poeditor_text_data');
            $transSchema = $this->conn->getTableSchema('cms_poeditor_translation_data');
            foreach ($filter as $name => $val) {
                if (isset($textSchema['columns'][$name])) {
                    $query['where'] .= ' AND ' .
                         'te.'.$name .
                         ' LIKE ' .
                         $this->conn->quote(
                            $val .
                             '%');
                } elseif (isset($transSchema['columns'][$name])) {
                    $query['where'] .= ' AND ' .
                         'tr.'.$name .
                         ' LIKE ' .
                         $this->conn->quote(
                            $val .
                             '%');
                }
            }
        }
        $count = $this->conn->selectFirst($query);
        return $count['text_count'];
    }

    /**
     * @see module_db_interface_Poeditor::loadList()
     *
     * @param integer $type
     * @param string $localeCode
     * @param integer $limit
     * @param integer $offset
     * @param array $filter
     * @param string $orderBy
     * @param string $orderDir
     * 	 * @return array
     */
    public function loadList ($type, $localeCode, $limit = null, $offset = null, $filter = null, $orderBy = null,
        $orderDir = null)
    {
        $query = array(
            'table'=>'cms_poeditor_text_data te LEFT JOIN cms_poeditor_translation_data tr ON (te.id = tr.text_id AND tr.locale_code='.$this->conn->quote($localeCode).')',
            'fields'=>'te.id, te.text, tr.translation AS translation',
            'where'=>'te.type='.(int)$type,
        );
        if (is_array($filter) && count($filter)) {
            $textSchema = $this->conn->getTableSchema('cms_poeditor_text_data');
            $transSchema = $this->conn->getTableSchema('cms_poeditor_translation_data');
            foreach ($filter as $name => $val) {
                if (isset($textSchema['columns'][$name])) {
                    $query['where'] .= ' AND ' .
                         'te.'.$name .
                         ' LIKE ' .
                         $this->conn->quote(
                            $val .
                             '%');
                } elseif (isset($transSchema['columns'][$name])) {
                    $query['where'] .= ' AND ' .
                         'tr.'.$name .
                         ' LIKE ' .
                         $this->conn->quote(
                            $val .
                             '%');
                }
            }
        }
        if (! is_null($orderBy)) {
            switch($orderBy) {
                case 'id':
                    $orderBy = 'te.id';
                    break;
                case 'translation':
                    $orderBy = 'tr.translation';
                    break;
                case 'text':
                default:
                    $orderBy = 'te.text';
                    break;
            }
            switch (strtolower($orderDir)) {
                case 'desc':
                case '-':
                    $query['orderBy'] .= $orderBy.' DESC';
                    break;
                default:
                    $query['orderBy'] .= $orderBy.' ASC';
                    break;
            }
        }
        if ((int) $limit) {
            $query['limit'] = (int)$limit;
            if ((int) $offset < 0) {
                $offset = 0;
            }
            $query['offset'] = (int)$offset;
        }
        return $this->conn->select($query);
    }

    /**
     * @see module_db_interface_Poeditor::deleteTranslation()
     *
     * @param integer $textId
     * @param string $localeCode
     */
    public function deleteTranslation ($textId, $localeCode)
    {
        $this->conn->delete('cms_poeditor_translation_data', 'text_id='.(int)$textId.' AND locale_code='.$this->conn->quote($localeCode));
    }

    /**
     * @see module_db_interface_Poeditor::loadTranslation()
     *
     * @param integer $textId
     * @param string $localeCode
     * @return array
     */
    public function loadTranslation ($textId, $localeCode)
    {
        $query = array(
            'table'=>'cms_poeditor_text_data te LEFT JOIN cms_poeditor_translation_data tr ON te.id=tr.text_id',
            'fields'=>'te.text, tr.translation, tr.fuzzy',
            'where'=>'(tr.locale_code IS NULL OR tr.locale_code='.$this->conn->quote($localeCode).') AND te.id='.(int)$textId
        );
        return $this->conn->selectFirst($query);
    }

    /**
     * @see module_db_interface_Poeditor::saveTranslation()
     *
     * @param integer $textId
     * @param string $localeCode
     * @param string $translation
     * @param boolean $fuzzy
     */
    public function saveTranslation ($textId, $localeCode, $translation, $fuzzy)
    {
        $record = $this->conn->selectFirst(array(
            'table'=>'cms_poeditor_translation_data',
            'where'=>'text_id='.(int)$textId.' AND locale_code='.$this->conn->quote($localeCode)
        ));
        if (! $record) {
            $record = array();
            $record['text_id'] = (int) $textId;
            $record['locale_code'] = $localeCode;
            $record['translation'] = $translation;
            $record['fuzzy'] = (bool) $fuzzy;
            $this->conn->insert('cms_poeditor_translation_data', $record);
        } else {
            $record['translation'] = $translation;
            $record['fuzzy'] = (bool) $fuzzy;
            $this->conn->update(array('table'=>'cms_poeditor_translation_data', 'where'=>'id='.$record['id']), $record);
        }
    }

    /**
     * @see module_db_interface_Poeditor::getTranslations()
     *
     * @param integer $target
     * @param string $localeCode
     * @return array
     */
    public function getTranslations ($target, $localeCode)
    {
        $fieldMap = $this->getMappableFields(array('tr'=>'cms_poeditor_translation_data', 'te'=>'cms_poeditor_text_data'));
        $query = array(
            'table'=>'cms_poeditor_translation_data tr JOIN cms_poeditor_text_data te ON te.id=tr.text_id',
            'fields'=>$this->getFieldListFromMap($fieldMap),
            'where'=>'te.type='.(int)$target.' AND tr.locale_code='.$this->conn->quote($localeCode),
        );
        $tmp = $this->getDataFromMappedFields($fieldMap, $this->conn->select($query));
        $data = array();
        foreach($tmp as $row) {
            $row['tr']['Text'] = $row['te'];
            $data[] = $row['tr'];
        }
        return $data;
    }

}
?>