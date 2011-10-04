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
 * Poeditor Doctrine database access module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_Doctrine_Poeditor extends module_db_DoctrineDbModule implements module_db_interface_Poeditor
{

    /**
     * @see module_db_interface_Poeditor::scanDbTexts()
     *
     * @return array
     */
    public function scanDbTexts ()
    {
        $texts = array();
        $tmp = $this->conn->query('SELECT name FROM ObjectTypeData');
        foreach ($tmp as $text) {
            $texts[] = $text['name'];
        }
        $tmp = $this->conn->query('SELECT name FROM AdminFuncData');
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
        return $this->normalizeResults(
            $this->conn->query(
                'FROM CmsPoeditorTextData ORDER BY text ASC'));
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
        $record = new CmsPoeditorTextData();
        $record['text'] = $text;
        $record['type'] = (int) $type;
        $record->save();
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
        $data = $this->conn->query(
            'FROM CmsPoeditorTextData WHERE id IN (' . implode(', ',
                $obsoleteIds) . ')');
        $data->delete();
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
        $filters = ' WHERE te.type = ? AND (tr.locale_code IS NULL OR tr.locale_code = ?)';
        $filterArr = array((int) $type, $localeCode);
        if (is_array($filter) && count($filter)) {
            $textRec = new CmsPoeditorTextData();
            $transRec = new CmsPoeditorTranslationData();
            foreach ($filter as $name => $val) {
                if ($textRec->contains($name)) {
                    $filters .= ' AND te.' .
                         $name .
                         ' LIKE ?';
                    $filterArr[] = $val .
                         '%';
                } elseif ($transRec->contains($name)) {
                    $filters .= ' AND tr.' .
                         $name .
                         ' LIKE ?';
                    $filterArr[] = $val .
                         '%';
                }
            }
        }
        $count = $this->conn->queryOne(
            'SELECT COUNT(te.id) as text_count FROM CmsPoeditorTextData te LEFT JOIN te.Translations tr' .
                 $filters, $filterArr);
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
        $extra = '';
        $filters = ' WHERE te.type = ? AND (tr.locale_code IS NULL OR tr.locale_code=?)';
        $filterArr = array((int) $type, $localeCode);
        if (is_array($filter) && count($filter)) {
            $textRec = new CmsPoeditorTextData();
            $transRec = new CmsPoeditorTranslationData();
            foreach ($filter as $name => $val) {
                if ($textRec->contains($name)) {
                    $filters .= ' AND te.' .
                         $name .
                         ' LIKE ?';
                    $filterArr[] = $val .
                         '%';
                } elseif ($transRec->contains($name)) {
                    $filters .= ' AND tr.' .
                         $name .
                         ' LIKE ?';
                    $filterArr[] = $val .
                         '%';
                }
            }
        }
        if ((int) $limit) {
            if (! is_null($orderBy)) {
                if ('id' != $orderBy && 'text' != $orderBy &&
                     'tr.translation' != $orderBy) {
                        $orderBy = 'text';
                }
                $extra .= ' ORDER BY ' . $orderBy;
                switch (strtolower($orderDir)) {
                    case 'desc':
                    case '-':
                        $extra .= ' DESC';
                        break;
                    default:
                        $extra .= ' ASC';
                        break;
                }
            }
            $extra .= ' LIMIT ' . (int) $limit;
            if ((int) $offset < 0) {
                $offset = 0;
            }
            $extra .= ' OFFSET ' . (int) $offset;
        }
        $tmp = $this->normalizeResults(
            $this->conn->query(
                'FROM CmsPoeditorTextData te LEFT JOIN te.Translations tr' . $filters . $extra,
                    $filterArr));
        $data = array();
        foreach($tmp as $row) {
            $data[] = array('id'=>$row['id'],'text'=>$row['text'],'translation'=>$row['Translations'][0]['translation']);
        }
        return $data;
    }

    /**
     * @see module_db_interface_Poeditor::deleteTranslation()
     *
     * @param integer $textId
     * @param string $localeCode
     */
    public function deleteTranslation ($textId, $localeCode)
    {
        $this->conn->query(
            'FROM CmsPoeditorTranslationData WHERE text_id=? AND locale_code=?',
            array((int) $textId , $localeCode))->delete();
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
        $text = $this->conn->queryOne('FROM CmsPoeditorTextData WHERE id=?',
            array((int) $textId));
        $translation = $this->conn->queryOne(
            'FROM CmsPoeditorTranslationData WHERE text_id=? AND locale_code=?',
            array((int) $textId , $localeCode));
        $data = array('text' => $text['text'] , 'translation' => null , 'fuzzy' => false);
        if (count($translation)) {
            $data['translation'] = $translation['translation'];
            $data['fuzzy'] = $translation['fuzzy'];
        }
        return $data;
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
        $record = $this->conn->queryOne(
            'FROM CmsPoeditorTranslationData WHERE text_id=? AND locale_code=?',
            array((int) $textId , $localeCode));
        if (! $record) {
            $record = new CmsPoeditorTranslationData();
            $record['text_id'] = (int) $textId;
            $record['locale_code'] = $localeCode;
        }
        $record['translation'] = $translation;
        $record['fuzzy'] = (bool) $fuzzy;
        $record->save();
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
        return $this->normalizeResults(
            $this->conn->query(
                'FROM CmsPoeditorTranslationData tr INNER JOIN tr.Text te WHERE te.type=? AND tr.locale_code=?',
                array((int) $target , $localeCode)));
    }

}
?>