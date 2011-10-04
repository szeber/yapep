<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage  Image
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 11068 $
 */

/**
 * Imagemagick image handler class
 *
 * @package	YAPEP
 * @subpackage  Image
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 11068 $
 */
class sys_image_ImagickCli implements sys_image_IImage {

    /**
     *
     * @var string
     */
    protected $pic;

    /**
     *
     * @var string
     */
    protected $picEsc;

    /**
     *
     * @var string
     */
    protected $format;

    /**
     *
     * @var sys_IApplicationConfiguration
     */
    protected $config;

    /**
     *
     * @var string
     */
    protected $imagickDir;

    /**
     *
     * @var array
     */
    protected $imageInfo = array();

    /**
     *
     * @var string
     */
    protected $execCmd;


    public function __construct(sys_IApplicationConfiguration $config = null) {
        if (!is_null($config)) {
            $this->config = $config;
        } else {
            $this->config = sys_ApplicationConfiguration::getInstance();
        }
        $this->imagickDir = $this->config->getPath('imagickDir');
    }

    public function __destruct() {
        $this->close();
    }

    public function readImage($image) {
        $this->execCmd = '';
        $this->imageInfo = array();
        $this->pic = trim($image);
        $this->picEsc = escapeshellarg($this->pic);
        $cmd = $this->imagickDir.'identify -verbose '.$this->picEsc;
        exec($cmd, $returnArr, $returnVal);
        if ($returnVal) {
            return false;
        }
        $matches=array();
        foreach($returnArr as $line) {
            if (preg_match('/^\s*Format: ([^\s]+)/i', $line, $matches)) {
                switch(strtolower($matches[1])) {
                    case 'jpeg':
                    case 'gif':
                    case 'png':
                        $this->format = strtoupper($matches[1]);
                        $this->imageInfo['format'] = strtolower($matches[1]);
                        break;
                    default:
                        return false;
                }
            } else if (preg_match('/^\s*Geometry: (\d+)x(\d+)/i', $line, $matches)) {
                $this->imageInfo['width'] = (int)$matches[1];
                $this->imageInfo['height'] = (int)$matches[2];
            }
        }
        return true;
    }

    public function setImageFormat($format) {
        $this->format = strtoupper($format);
    }

    public function getImageWidth() {
        $cmd = $this->getReadCmd().$this->execCmd.$this->getConvertCmd().' | identify -format %w -';
        return exec($cmd);
    }

    public function getImageHeight() {
        $cmd = $this->getReadCmd().$this->execCmd.$this->getConvertCmd().' | identify -format %h -';
        return exec($cmd);
    }

    public function scaleImage($cols, $rows, $fit = false) {
        $cols = (int)$cols;
        $rows = (int)$rows;
        if (!$cols && !$rows) {
            return;
        }
        if (!$cols) {
            $cols = '';
        }
        if (!$rows) {
            $rows = '';
        }
        $this->execCmd .= ' | '.$this->imagickDir.'convert - -resize '.$cols.'x'.$rows;
        if ($cols && $rows && $fit) {
            $this->execCmd .='\\!';
        }
        $this->execCmd .= ' -';
    }

    public function writeImage($image = null) {
        if (is_null($image)) {
            $image = $this->pic;
        }
        $cmd = $this->getReadCmd().$this->execCmd.' | '.$this->imagickDir.'convert - '.$this->format.':'.escapeshellarg($image);
        exec($cmd);
        $this->readImage($image);
    }

    public function cropThumbnailImage($width, $height) {
        $this->execCmd .= ' | '.$this->imagickDir.'convert - -thumbnail '.$width.'x'.$height.'^ -gravity center -extent '.$width.'x'.$height.' -';
    }

    public function getImageSize() {
        $cmd = $this->getReadCmd().$this->execCmd.$this->getConvertCmd().' | identify -format %b -';
        return exec($cmd);
    }

    public function close() {
        $this->pic = null;
        $this->picEsc = null;
        $this->execCmd = '';
        $this->imageInfo = array();
    }

    protected function getReadCmd() {
        return $this->imagickDir.'convert '.$this->picEsc.' - ';
    }

    protected function getConvertCmd() {
        return ' | convert - '.$this->format.':-';
    }

}
?>
