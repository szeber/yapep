<?php

/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Object move module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_object_Move extends sys_admin_AdminModule
{

    /**
     * @see sys_admin_AdminModule::parseXml()
     *
     * @param SimpleXMLElement $xml
     */
    public function parseXml ($xml)
    {
        $type = (string) $xml->adminData->type;
        $id = (int) $xml->adminData->id;
        $target = (int) $xml->adminData->target;
        if (! $xml->data) {
            $xml->addChild('data');
        }
        if (! count($xml->data->value)) {
            $val = $xml->data->addChild('value', 'dummy');
            $val->addAttribute('name', 'dummy');
        }
        $this->xml = $xml;
        $this->id = $id;
        $this->options['id'] = $id;
        if ($id == $target) {
            throw new sys_exception_AdminException(
                _('Can\'t move object to itself'),
                sys_exception_AdminException::ERR_SAVE_ERROR);
        }
        $this->mode = sys_admin_AdminModule::MODE_EDIT;
        switch ($type) {
            case 'folder':
                $folderHandler = getPersistClass(
                    'Folder');
                $folderData = $folderHandler->loadItem(
                    $id);
                if ($target > 0) {
                    $targetFolderData = $folderHandler->loadItem(
                        $target);
                }
                if (! $folderData) {
                    throw new sys_exception_AdminException(
                        _(
                            'Can\'t find folder'),
                        sys_exception_AdminException::ERR_ID_NOT_FOUND);
                }
                if (! $targetFolderData && $target > 0) {
                    throw new sys_exception_AdminException(
                        _(
                            'Can\'t find target folder'),
                        sys_exception_AdminException::ERR_ID_NOT_FOUND);
                }
                if ($target > 0 && strstr(
                    $targetFolderData['docpath'],
                    $folderData['docpath'])) {
                    throw new sys_exception_AdminException(
                        _(
                            'Can\'t move a folder into it\'s subfolder'),
                        sys_exception_AdminException::ERR_SAVE_ERROR);
                }
                if ($target == $folderData['parent_id']) {
                    return;
                }
                $folderHandler->updateItem($id,
                    array(
                    'parent_id' => $target));
                $cache = new sys_cache_FolderCacheManager();
                $cache->recreateCache();
                break;
            case 'folderContents':
                $folderHandler = getPersistClass(
                    'Folder');
                if ($target <= 0) {
                    throw new sys_exception_AdminException(
                        _(
                            'Can\'t move a folder\'s contents into the root folder'),
                        sys_exception_AdminException::ERR_SAVE_ERROR);
                }
                if ($id == $target) {
                    return;
                }
                $srcFolder = $folderHandler->loadItem(
                    $id);
                $targetFolder = $folderHandler->loadItem(
                    $target);
                if (! $srcFolder) {
                    throw new sys_exception_AdminException(
                        _(
                            'Can\'t find source folder'),
                        sys_exception_AdminException::ERR_ID_NOT_FOUND);
                }
                if (! $targetFolder) {
                    throw new sys_exception_AdminException(
                        _(
                            'Can\'t find target folder'),
                        sys_exception_AdminException::ERR_ID_NOT_FOUND);
                }
                if ($srcFolder['folder_type_id'] != $targetFolder['folder_type_id']) {
                    throw new sys_exception_AdminException(
                        _(
                            'The source and target folder types can\'t be different'),
                        sys_exception_AdminException::ERR_SAVE_ERROR);
                }
                $docHandler = getPersistClass('Doc');
                $docHandler->moveFolderDocs(
                    $this->localeId, $id,
                    $target);
                break;
            case 'docMove':
                if ($target <= 0) {
                    throw new sys_exception_AdminException(
                        _(
                            'Can\'t move a document into the root folder'),
                        sys_exception_AdminException::ERR_SAVE_ERROR);
                }
                $folderHandler = getPersistClass(
                    'Folder');
                $docHandler = getPersistClass('Doc');
                $folderData = $folderHandler->getFullFolderInfoById(
                    $target);
                $docData = $docHandler->loadItem($id);
                if ($folderData['id'] == $docData['folder_id']) {
                    return;
                }
                if (! $folderData) {
                    throw new sys_exception_AdminException(
                        _(
                            'Can\'t find target folder'),
                        sys_exception_AdminException::ERR_ID_NOT_FOUND);
                }
                if (! $docData) {
                    throw new sys_exception_AdminException(
                        _(
                            'Can\'t find document'),
                        sys_exception_AdminException::ERR_ID_NOT_FOUND);
                }
                $docValid = false;
                foreach ($folderData['FolderType']['ObjectTypes'] as $objectType) {
                    if ($objectType['id'] ==
                         $docData['ref_object_type_id']) {
                            $docValid = true;
                        break;
                    }
                }
                if (! $docValid || $folderData['FolderType']['no_new_doc']) {
                    throw new sys_exception_AdminException(
                        _(
                            'Can\'t move document to that folder, this document type is not allowed in this folder type'),
                        sys_exception_AdminException::ERR_SAVE_ERROR);
                }
                $localeHandler = getPersistClass(
                    'LangLocale');
                $locale = $localeHandler->getLocaleByCode(
                    $this->locale);
                $docExists = $docHandler->getDocByDocPath(
                    $locale['id'],
                    $folderData['docpath'],
                    $docData['docname'], true);
                if ($docExists) {
                    throw new sys_exception_AdminException(
                        _(
                            'Can\'t move the document to this folder because the document name is in use in the folder'),
                        sys_exception_AdminException::ERR_SAVE_ERROR);
                }
                $docHandler->updateItem($id,
                    array(
                    'docfolder_id' => $target));
                break;
            case 'docCopy':
                if ($target <= 0) {
                    throw new sys_exception_AdminException(
                        _(
                            'Can\'t move a document into the root folder'),
                        sys_exception_AdminException::ERR_SAVE_ERROR);
                }
                $folderHandler = getPersistClass(
                    'Folder');
                $docHandler = getPersistClass('Doc');
                $folderData = $folderHandler->getFullFolderInfoById(
                    $target);
                $docData = $docHandler->loadItem($id);
                if ($folderData['id'] == $docData['folder_id']) {
                    return;
                }
                if (! $folderData) {
                    throw new sys_exception_AdminException(
                        _(
                            'Can\'t find target folder'),
                        sys_exception_AdminException::ERR_ID_NOT_FOUND);
                }
                if (! $docData) {
                    throw new sys_exception_AdminException(
                        _(
                            'Can\'t find document'),
                        sys_exception_AdminException::ERR_ID_NOT_FOUND);
                }
                $docValid = false;
                foreach ($folderData['FolderType']['ObjectTypes'] as $objectType) {
                    if ($objectType['id'] ==
                         $docData['ref_object_type_id']) {
                            $docValid = true;
                        break;
                    }
                }
                if (! $docValid || $folderData['FolderType']['no_new_doc']) {
                    throw new sys_exception_AdminException(
                        _(
                            'Can\'t copy document to that folder, this document type is not allowed in this folder type'),
                        sys_exception_AdminException::ERR_SAVE_ERROR);
                }
                $localeHandler = getPersistClass(
                    'LangLocale');
                $locale = $localeHandler->getLocaleByCode(
                    $this->locale);
                $docExists = $docHandler->getDocByDocPath(
                    $locale['id'],
                    $folderData['docpath'],
                    $docData['docname'], true);
                if ($docExists) {
                    throw new sys_exception_AdminException(
                        _(
                            'Can\'t copy the document to this folder because the document name is in use in the folder'),
                        sys_exception_AdminException::ERR_SAVE_ERROR);
                }
                $newDoc = array(
                'docObjectTypeName' => 'docCopy' ,
                'doctitle' => $docData['doctitle'] ,
                'object_id' => $docData['object_id'] ,
                'ref_object_type_id' => $docData['ref_object_type_id'] ,
                'docname' => $docData['docname'] ,
                'docFolderId' => $target ,
                'start_date' => $docData['start_date'] ,
                'end_date' => $docData['end_date'] ,
                'status' => $docData['status'] ,
                'docLocale' => $locale['id']);
                $docHandler->insertItem($newDoc);
                break;
            case 'docLink':
                if ($target <= 0) {
                    throw new sys_exception_AdminException(
                        _(
                            'Can\'t link a document into the root folder'),
                        sys_exception_AdminException::ERR_SAVE_ERROR);
                }
                $folderHandler = getPersistClass(
                    'Folder');
                $docHandler = getPersistClass('Doc');
                $folderData = $folderHandler->getFullFolderInfoById(
                    $target);
                $docData = $docHandler->loadItem($id);
                if ($folderData['id'] == $docData['folder_id']) {
                    return;
                }
                if (! $folderData) {
                    throw new sys_exception_AdminException(
                        _(
                            'Can\'t find target folder'),
                        sys_exception_AdminException::ERR_ID_NOT_FOUND);
                }
                if (! $docData) {
                    throw new sys_exception_AdminException(
                        _(
                            'Can\'t find document'),
                        sys_exception_AdminException::ERR_ID_NOT_FOUND);
                }
                $docValid = false;
                foreach ($folderData['FolderType']['ObjectTypes'] as $objectType) {
                    if ($objectType['short_name'] == 'doclink') {
                            $docValid = true;
                        break;
                    }
                }
                if (! $docValid || $folderData['FolderType']['no_new_doc']) {
                    throw new sys_exception_AdminException(
                        _(
                            'Can\'t link document to that folder, the folder does not allow document links'),
                        sys_exception_AdminException::ERR_SAVE_ERROR);
                }
                $localeHandler = getPersistClass(
                    'LangLocale');
                $locale = $localeHandler->getLocaleByCode(
                    $this->locale);
                $docLinkHandler = getPersistClass('GenericAdmin');
                /* @var $docLinkHandler module_db_generic_GenericAdmin */
                $docLinkHandler->setObjType('doclink');
                $docLink = array('folder_id'=>$target, 'doc_id'=>$id);
                $docLinkHandler->insertItem($docLink);
                break;
            case 'assetMove':
                if ($target <= 0) {
                    throw new sys_exception_AdminException(
                        _(
                            'Can\'t move an asset into the root folder'),
                        sys_exception_AdminException::ERR_SAVE_ERROR);
                }
                $assetHandler = getPersistClass('Asset');
                $genericHandler = getPersistClass('GenericAdmin');
                $genericHandler->setObjType('asset');
                $folderData = $assetHandler->getFolderInfoById(
                    $target);
                $assetData = $genericHandler->loadItem($id);
                if ($folderData['id'] == $assetData['folder_id']) {
                    return;
                }
                if (! $folderData) {
                    throw new sys_exception_AdminException(
                        _(
                            'Can\'t find target folder'),
                        sys_exception_AdminException::ERR_ID_NOT_FOUND);
                }
                if (! $assetData) {
                    throw new sys_exception_AdminException(
                        _(
                            'Can\'t find asset'),
                        sys_exception_AdminException::ERR_ID_NOT_FOUND);
                }
                $docValid = false;
                $genericHandler->updateItem($id,
                    array(
                    'folder_id' => $target));
                break;
            default:
                throw new sys_exception_AdminException(
                    _(
                        'Invalid move type'),
                    sys_exception_AdminException::ERR_MODULE_DOES_NOT_SUPPORT_MODE);
        }
    }

    /**
     * @see sys_admin_AdminModule::buildForm()
     *
     */
    protected function buildForm ()
    {}

    /**
     * @see sys_admin_AdminModule::doLoad()
     *
     * @return array;
     */
    protected function doLoad ()
    {}

    /**
     * @see sys_admin_AdminModule::doSave()
     *
     * @return boolean
     */
    protected function doSave ()
    {
        return '';
    }

}
?>