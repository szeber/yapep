<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 7827 $
 */

 /**
 * File upload module
 *
 * Receives an uploaded file, and returns the id to the uploaded file, so it can be saved later
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 7827 $
 */
class module_admin_cms_FileUpload extends sys_admin_AdminModule {

	protected $uploadId;

	protected function buildForm() {
		$control = new sys_admin_control_Label();
		$this->addControl($control, 'file_id');
	}

	/**
	 * @see sys_admin_AdminModule::doLoad()
	 *
	 * @return array;
	 */
	protected function doLoad() {
		if ($this->uploadId) {
			return array('file_id'=> $this->uploadId);
		}
		return array();
	}

	/**
	 * @see sys_admin_AdminModule::doSave()
	 *
	 * @return string
	 */
	protected function doSave() {
		$this->cleanUpOldFiles();
		if (!isset ($_FILES ['uploadedFile'] ['tmp_name'])) {
			throw new sys_exception_AdminException(_('No uploaded file'), sys_exception_AdminException::ERR_SAVE_ERROR);
		}
		if (!is_uploaded_file($_FILES['uploadedFile']['tmp_name'])) {
			throw new sys_exception_AdminException(_('Upload error'), sys_exception_AdminException::ERR_SAVE_ERROR);
		}
		$tmpDir = $this->config->getPath('uploadTempDir');
		$tmpName = $tmpDir.md5(rand(1000, 2000).time().microtime(true));
		move_uploaded_file($_FILES['uploadedFile']['tmp_name'], $tmpName);
		$fileHandler = getPersistClass('UploadTmp');
		$this->uploadId = $fileHandler->addFile($tmpName, $_FILES['uploadedFile']['name']);
	}

	/**
	 * Deletes files older than 1 hour from both the filesystem and the database
	 */
	protected function cleanUpOldFiles() {
		$fileHandler = getPersistClass('UploadTmp');
		$files = $fileHandler->getOldFiles();
		if (!$files) {
			return;
		}
		foreach($files as $file) {
			if (file_exists($file['filename'])) {
				unlink ($file['filename']);
			}
			$fileHandler->deleteFile($file['id']);
		}
	}

}
?>