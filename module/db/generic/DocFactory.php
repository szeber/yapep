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
 * generic docfactory database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_generic_DocFactory extends module_db_DbModule implements module_db_interface_DocFactory
{

    /**
     * Returns the document type data based on the document ID
     *
     * @param integer $docId
     * @return array
     */
    public function getDocTypeByDocId ($docId)
    {
        return $this->conn->selectFirst(
            array(
            'table' => 'doc_data d JOIN object_type_data ot ON d.ref_object_type_id=ot.id' ,
            'fields' => 'ot.*' , 'where' => 'd.id=' . (int) $docId));
    }

    /**
     * Returns the document type data based on the document name, path and language
     *
     * @param integer $localeId
     * @param string $docPath
     * @param string $docName
     * @return array
     */
    public function getDocTypeByDocPathName ($localeId, $docPath, $docName)
    {
        return $this->conn->selectFirst(
            array(
            'table' => 'doc_data d JOIN object_type_data ot ON d.ref_object_type_id=ot.id' .
                 ' JOIN folder_data f ON f.id=d.folder_id' ,
                'fields' => 'ot.*' ,
                'where' => '(f.locale_id IS NULL OR f.locale_id = ' .
                 $this->conn->quote($localeId) . ') AND f.docpath = ' .
                 $this->conn->quote($docPath) . ' AND d.docname = ' .
                 $this->conn->quote($docName) . ' AND (d.locale_id IS NULL OR d.locale_id = '.(int)$localeId.')'));
    }

    /**
     * Returns information for all document types
     *
     * @return array
     */
    public function getDoctypeList ()
    {
        $tmp = $this->conn->select(array('table' => 'object_type_data'));
        $doctypes = array();
        foreach ($tmp as $val) {
            $doctypes[$val['id']] = $val;
        }
        return $doctypes;
    }

    /**
     * Returns the document ID based on the document's name, path and language
     *
     * @param integer $localeId
     * @param string $docPath
     * @param string $docName
     * @param boolean $inactive If true also returns the id for inactive documents
     * @return integer	The ID or null if not found
     */
    public function getDocIdByDocPath ($localeId, $docPath, $docName, $inactive = false)
    {
        $query = array('table' => 'doc_data d JOIN folder_data f ON f.id=d.folder_id' ,
        'fields' => 'd.id' ,
        'where' => '(f.locale_id IS NULL OR f.locale_id=' . (int) $localeId . ') AND (d.locale_id IS NULL OR d.locale_id=' . (int) $localeId . ') AND f.docpath=' .
             $this->conn->quote($docPath) . ' AND d.docname=' . $this->conn->quote(
                $docName));
        if (! $inactive) {
            $query['where'] .= ' AND start_date <= NOW() AND end_date >= NOW() AND status = ' .
                 module_db_interface_Doc::STATUS_ACTIVE;
        }
        $docData = $this->conn->selectFirst($query);
        if (! $docData['id']) {
            return null;
        }
        return $docData['id'];
    }

    /**
     * @see module_db_interface_DocFactory::getDocTypeIdByShortName()
     *
     * @param string $shortName
     * @return integer
     */
    public function getDocTypeIdByShortName ($shortName)
    {
        return $this->getObjectTypeIdByShortName($shortName);
    }

}
?>