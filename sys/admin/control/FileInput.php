<?php

/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * File input control
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_admin_control_FileInput extends sys_admin_control_TextInput
{

    /**
     * Sets whether the stored value can be overwritten with an empty value (eg '' or null)
     *
     * @var boolean
     */
    protected $overWriteWithEmpty = false;

    /**
     * @see sys_admin_control_TextInput::setNormalValue()
     *
     * @param mixed $value
     * @param boolean $load
     * @return boolean
     */
    protected function setNormalValue ($value, $load = false)
    {
        if (! $load && ! $value && ! $this->overWriteWithEmpty) {
            return true;
        }
        return parent::setNormalValue($value, $load);
    }

    /**
     * Sets whether the $value passed to setValue() can be set to an empty value (eg '' or null)
     *
     * @param boolean $overWrite
     */
    public function setOverWriteWithEmpty ($overWrite = true)
    {
        $this->overWriteWithEmpty = (bool) $overWrite;
    }

    /**
     * Sets the name of the form to be used for the upload
     *
     * @param string $formName
     */
    public function setUploadForm ($formName)
    {
        $this->options['uploadForm'] = $formName;
    }

    /**
     * @see sys_admin_control_TextInput::setDefaults()
     *
     */
    protected function setDefaults ()
    {
        parent::setDefaults();
        $this->options['uploadForm'] = 'cms_FileUpload';
        $this->options['sizeLimit'] = $this->getMbSize(ini_get('upload_max_filesize'));
    }

    /**
     * Decodes the upload_max_filesize shorthands and returns the value in MB
     *
     * @param integer $size
     * @return integer
     */
    protected function getMbSize ($size)
    {
        if (is_numeric($size)) {
            return round($size / 1024 / 1024);
        }
        $regexp = '';
        if (! preg_match('/^\s*([0-9]+)\s*([kKmMgG])\s*$/', $size, $regexp)) {
            return round((float) $size / 1024 / 1024);
        }
        switch ($regexp[2]) {
            case 'k':
            case 'K':
                return round($regexp[1] / 1024);
            case 'm':
            case 'M':
                return round($regexp[1]);
            case 'g':
            case 'G':
                return $regexp[1] * 1024;
        }
    }

    /**
     * Sets the upload size limit for the input in megabytes.
     *
     * Defaults to the upload_max_filesize setting, and it should not be set to higher than that.
     *
     * @param integer $limit
     */
    public function setSizeLimit ($limit)
    {
        $this->options['sizeLimit'] = (int) $limit;
    }

    /**
     * Sets the allowed extensions for the file
     *
     * @param array $extensions
     */
    public function setAllowedExtensions (array $extensions)
    {
        $this->options['allowedExtensions'] = implode(',', $extensions);
    }

    /**
     * Sets the allowed MIME types for the file
     *
     * @param array $types
     */
    public function setAllowedMimeTypes (array $types)
    {
        $this->options['allowedMimeTypes'] = implode(',', $types);
    }

}
?>