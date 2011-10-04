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
 * Asset editor admin module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_asset_Editor extends sys_admin_AdminModule
{

    /**
     * @var string
     */
    protected $videoSrcName;

    /**
     * @var string
     */
    protected $videoDstName;

    protected function buildForm ()
    {

        $handler = getPersistClass('GenericAdmin');
        $handler->setObjType('Asset');
        $this->setDbHandler($handler);

        $control = new sys_admin_control_HiddenInput();
        $control->setReadonly();
        $this->addControl($control, 'folder_id');

        $control = new sys_admin_control_TextInput();
        $control->setRequired(true);
        $control->setLabel(_('Name'));
        $this->addControl($control, 'name');

        $control = new sys_admin_control_TextArea();
        $control->setLabel(_('Description'));
        $this->addControl($control, 'description');

        $control = new sys_admin_control_Label();
        $control->setLabel(_('Information'));
        $control->setReadOnly();
        $this->addControl($control, 'info');

        $control = new sys_admin_control_Label();
        $control->setLabel(_('Path'));
        $control->setReadOnly();
        $this->addControl($control, 'path1');

        if ($this->subModule[0] == module_db_interface_Asset::ASSET_TYPE_IMAGE) {
            $control = new sys_admin_control_SelectInput();
            $control->setLabel(_('Resize image'));
            $control->setNullValueLabel(_('No resizing'));
            $control->addOptions($this->getResizeOptions());
            $this->addControl($control, 'resizeImage');
        }

        $control = new sys_admin_control_ImageFileInput();
        switch ($this->subModule[0]) {
            case module_db_interface_Asset::ASSET_TYPE_IMAGE:
                $control->setLabel(_('Picture'));
                $control->setAllowedExtensions(
                    array('gif' , 'jpg' , 'jpeg' , 'png'));
                $control->setAllowedMimeTypes(
                    array('image/gif' , 'image/jpeg' , 'image/png'));
                break;
            case module_db_interface_Asset::ASSET_TYPE_VIDEO:
                $control->setAllowedExtensions(
                    array('avi' , 'wmv' , 'mpg' , 'mpeg' , 'flv'));
                $control->setAllowedMimeTypes(
                    array('video/msvideo' , 'video/x-msvideo' ,
                    'video/x-wmv' , 'video/x-ms-wmv' , 'video/mpeg' ,
                    'video/flv' , 'video/x-flv'));
                $control->setLabel(_('Video'));
                break;
            default:
                $control->setLabel(_('File'));
                break;
        }
        $this->addControl($control, 'imageFile');
        $control = new sys_admin_control_HiddenInput();
        $this->addControl($control, 'imageData');
    }

    /**
     * Returns the array with the options for the resizer select
     *
     * @return array
     */
    protected function getResizeOptions ()
    {
        $dbHandler = getPersistClass('Asset');
        $resizers = $dbHandler->getResizers();
        $results = array();
        foreach ($resizers as $resizer) {
            $results[$resizer['id']] = $resizer['name'] . ' (' . $resizer['width'] . 'x' . $resizer['height'] .
                 ')';
        }
        return $results;
    }

    /**
     * @see sys_admin_AdminModule::processLoadData()
     *
     */
    protected function processLoadData ()
    {
        if ($this->data['path1']) {
            $this->data['imageFile'] = $this->config->getPath('rootUrl') . $this->data['path1'];
        }
    }

    /**
     * @see sys_admin_AdminModule::processSaveData()
     *
     */
    protected function processSaveData ()
    {
        if ($this->mode == sys_admin_AdminModule::MODE_ADD) {
            $this->data['folder_id'] = $this->subModule[1];
            $this->data['asset_type_id'] = $this->subModule[0];
        }
        if (! isset($_FILES['uploadedFile']['tmp_name']) && ! $this->data['imageData'] && ! is_numeric(
            $this->data['imageFile'])) {
            return;
        }
        $newName = $_FILES['uploadedFile']['name'];
        $tmpFile = $_FILES['uploadedFile']['tmp_name'];
        $urlDir = $this->config->getPath('uploadDir') . 'asset/' . $this->data['folder_id'] . '/';
        if ($this->data['imageFile'] && is_numeric($this->data['imageFile'])) {
            $fileHandler = getPersistClass('UploadTmp');
            $fileData = $fileHandler->getFile($this->data['imageFile']);
            $tmpFile = $fileData['filename'];
            $newName = $fileData['orig_name'];
            $fileHandler->deleteFile($this->data['imageFile']);
        }
        $fileNameParts = array();
        switch ($this->subModule[0]) {
            case module_db_interface_Asset::ASSET_TYPE_IMAGE:
                if ($this->data['imageData']) {
                    $newName = $this->data['imageFile'];
                    $tmpFile = $this->config->getPath('uploadTempDir') .
                         md5(
                            time() . '.' .
                             microtime(
                                true));
                    file_put_contents($tmpFile,
                        base64_decode(
                            $this->data['imageData']));
                    unset($this->data['imageData']);
                    $this->getControl('imageData')->setValue('', true);
                }
                if ($this->data['resizeImage']) {
                    $newName = $this->resizeImage($tmpFile, $newName);
                }
                $imageData = getimagesize($tmpFile);
                $this->data['info'] = $imageData[0] . ' x ' . $imageData[1] . ' - ' . round(
                    filesize($tmpFile) / 1024) . ' KB';
                preg_match('/^(.+)\.([^.]+)$/', $newName, $fileNameParts);
                $fileName = $fileNameParts[1];
                $ext = $fileNameParts[2];
                if ($this->config->getOption('makeAssetThumbnails')) {
                    $this->makeThumbnail($tmpFile, $fileName, $urlDir);
                }
                $this->data['path1'] = $urlDir . $this->saveAssetFile($tmpFile,
                    $fileName, $ext, $urlDir);
                break;
            case module_db_interface_Asset::ASSET_TYPE_VIDEO:
                preg_match('/^(.+)\.([^.]+)$/', $newName, $fileNameParts);
                $fileName = $fileNameParts[1];
                $ext = $fileNameParts[2];
                if ('flv' == strtolower($ext)) {
                    $buffer = null;
                    $fileInfo = array();
                    exec(
                        $this->config->getPath(
                            'ffmpegBin') .
                             ' -i ' . escapeshellarg(
                                $tmpFile) .
                             ' 2>&1',
                            $buffer);
                    preg_match_all('/^\s*Duration:.*$/m',
                        implode("\n", $buffer),
                        $fileInfo);
                    $this->data['info'] = $fileInfo[0][0] . ', ' . round(
                        filesize($tmpFile) / 1024) . ' KB';
                    $this->data['path1'] = $urlDir . $this->saveAssetFile(
                        $tmpFile, $fileName, $ext,
                        $urlDir);
                } elseif ('wmv' == strtolower($ext) || 'avi' == strtolower($ext) || 'mpg' ==
                     strtolower($ext)) {
                        $queueDir = $this->config->getPath(
                            'videoQueueDir');
                    if (! file_exists($queueDir)) {
                        mkdir($queueDir);
                    }
                    $this->videoSrcName = $queueDir . md5(
                        time() . '.' . microtime(true));
                    copy($tmpFile, $this->videoSrcName);
                    unlink($tmpFile);
                    file_put_contents($tmpFile, '');
                    $this->videoDstName = $urlDir . $this->saveAssetFile(
                        $tmpFile, $fileName, 'flv',
                        $urlDir);
                    $this->data['info'] = '';
                    $this->data['path1'] = $this->videoDstName;
                } else {
                    throw new sys_exception_AdminException(
                        _(
                            'Invalid video file. File must be one of "avi", "flv", "mpg", "wmv"'),
                        sys_exception_AdminException::ERR_SAVE_ERROR);
                }
                break;
            case module_db_interface_Asset::ASSET_TYPE_FILE:
            default:
                $this->data['info'] = round(filesize($tmpFile) / 1024) . ' KB';
                preg_match('/^(.+)\.([^.]+)$/', $newName, $fileNameParts);
                $fileName = $fileNameParts[1];
                $ext = $fileNameParts[2];
                $db = getPersistClass('Asset');
                $subtype = $db->getAssetSubtypeByExt (module_db_interface_Asset::ASSET_TYPE_FILE, $ext);
                if ($subtype['id']) {
                    $this->data['asset_subtype_id'] = $subtype['id'];
                }
                $this->data['path1'] = $urlDir . $this->saveAssetFile($tmpFile,
                    $fileName, $ext, $urlDir);
                break;
        }
    }

    /**
     * Resizes an image and overwrites the original file
     *
     * @param string $file
     */
    protected function resizeImage ($file, $fileName)
    {
        $dbHandler = getPersistClass('Asset');
        $resizeInfo = $dbHandler->loadResizeItem($this->data['resizeImage']);
        if (! $resizeInfo || ! count($resizeInfo)) {
            return $fileName;
        }
        $fileNameParts = array();
        preg_match('/^(.+?)(?:\.([^.]+))?$/', $fileName, $fileNameParts);
        $baseName = $fileNameParts[1];
        $ext = strtolower($fileNameParts[2]);
        if (! $ext || ! in_array(strtolower($ext), array('jpg' , 'jpeg' , 'png' , 'gif'))) {
            $ext = 'jpg';
        }
        $pic = sys_LibFactory::getImage();
        $pic->readImage($file);
        $pic->setImageFormat($ext);
        $orgWidth = $pic->getImageWidth();
        $orgHeight = $pic->getImageHeight();
        if ($resizeInfo['force_exact']) {
            $pic->scaleImage($resizeInfo['width'], $resizeInfo['height']);
        } else if ($resizeInfo['width'] > $orgWidth && $resizeInfo['height'] > $orgHeight) {
            return $fileName;
        } else {
            if ($pic->getImageWidth() > $resizeInfo['width']) {
                $pic->scaleImage($resizeInfo['width'], 0);
            }
            if ($pic->getImageHeight() > $resizeInfo['height']) {
                $pic->scaleImage(0, $resizeInfo['height']);
            }
        }
        $pic->writeImage($file);
        $pic->close();
        return $baseName . '.' . $ext;
    }

    /**
     * @see sys_admin_AdminModule::postSave()
     *
     */
    protected function postSave ()
    {
        if ($this->videoDstName && $this->videoSrcName) {
            $assetHandler = getPersistClass('Asset');
            $assetHandler->saveVideoToQueue($this->id, $this->videoSrcName, $this->videoDstName);
        }
    }

    protected function saveAssetFile ($tmpFile, $fileName, $fileExt, $urlDir)
    {
        if (! $tmpFile || ! $fileName) {
            return '';
        }
        $fileName = convertStringToDocname($fileName);
        $baseName = $fileName;
        $dir = $this->config->getPath('wwwRoot') . $urlDir;
        if (! file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $counter = 0;
        while (file_exists($dir . $fileName . '.' . $fileExt)) {
            $counter ++;
            $fileName = $baseName . str_pad($counter, 3, '0', STR_PAD_LEFT);
        }
        copy($tmpFile, $dir . $fileName . '.' . $fileExt);
        unlink($tmpFile);
        return $fileName . '.' . $fileExt;
    }

    protected function makeThumbnail ($currFile, $origName, $urlDir)
    {
        $thumbWidth = (int)$this->config->getOption('assetThumbnailWidth');
        $thumbHeight = (int)$this->config->getOption('assetThumbnailHeight');
        if ($thumbHeight < 1 || $thumbWidth < 1) {
            return;
        }
        $tmpFile = $this->config->getPath('uploadTempDir') . md5(time() . '.' . microtime(true));
        $fileName = 'tn_' . $origName;
        $pic = sys_LibFactory::getImage();
        $pic->readImage($currFile);
        $pic->setImageFormat('jpg');
        if ($this->config->getOption('assetThumbnailCrop')) {
            $pic->cropThumbnailImage($thumbWidth, $thumbHeight);
        } else {
            if ($pic->getImageWidth() > $thumbWidth) {
                $pic->scaleImage($thumbWidth, 0);
            }
            if ($pic->getImageHeight() > $thumbHeight) {
                $pic->scaleImage(0, $thumbHeight);
            }
        }
        $pic->writeImage($tmpFile);
        $pic->close();
        $this->data['path2'] = $urlDir . $this->saveAssetFile($tmpFile, $fileName, 'jpg', $urlDir);
    }

}
?>