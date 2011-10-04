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
 * GD image handler class
 *
 * This class uses the WideImage library
 *
 * @package	YAPEP
 * @subpackage  Image
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 11068 $
 */
class sys_image_Gd implements sys_image_IImage {

    /**
     *
     * @var wiImage
     */
    protected $pic;

    /**
     *
     * @var string
     */
    protected $format = null;

    /**
     * @var sys_IApplicationConfiguration
     */
    protected $config;

    public function __construct(sys_IApplicationConfiguration $config = null) {
        if (is_null($config)) {
            $config = sys_ApplicationConfiguration::getInstance();
        }
        $this->config = $config;
    }

    public function __destruct() {
        $this->close();
    }

    public function readImage($image) {
        $tmp = getimagesize($image);
        if (!is_array($tmp)) {
            return;
        }
        switch($tmp['2']) {
            case IMAGETYPE_GIF:
                $format = 'GIF';
                break;
            case IMAGETYPE_JPEG:
                $format = 'JPEG';
                break;
            case IMAGETYPE_PNG:
                $format = 'PNG';
                break;
            default:
                return;
        }
        $this->setImageFormat($format);
        $this->pic = wiImage::load($image, $format);
        return (bool)$this->pic;
    }

    public function setImageFormat($format) {
        $this->format = $format;
    }

    public function getImageWidth() {
        return $this->pic->getWidth();
    }

    public function getImageHeight() {
        return $this->pic->getHeight();
    }

    public function scaleImage($cols, $rows, $fit = false) {
        if ($fit) {
            $fill = 'fill';
        } else {
            $fill = 'inside';
        }
        $this->doScaleImage($cols, $rows, $fill);
    }
    protected function doScaleImage($cols, $rows, $fill) {
        if ($cols == 0 && $rows != 0) {
            $width = $this->getImageWidth();
            $height = $this->getImageHeight();
            $ar = $rows/$height;
            $cols = $ar*$width;
        } else if ($rows == 0 && $cols != 0) {
            $width = $this->getImageWidth();
            $height = $this->getImageHeight();
            $ar = $cols/$width;
            $rows = $ar*$height;
        }
        $this->pic = $this->pic->resize($cols, $rows, $fill);
    }

    public function writeImage($image = null) {
        $format = $this->format;
        if (!$format) {
            $format = 'JPEG';
        }
        if (is_null($image)) {
            echo $this->pic->asString($format);
        } else {
            $this->pic->saveToFile($image, $format);
        }
    }

    public function cropThumbnailImage($width, $height) {
        $this->doScaleImage($width, $heigth, 'outside');
        $top = 0;
        $left = 0;
        if ($this->getImageHeight() > $height) {
            $top = floor(($this->getImageHeight() - $height)/2);
        } else if ($this->getImageWidth() > $width) {
            $left = floor(($this->getImageWidth() - $width)/2);
        }
        $this->pic = $this->pic->crop($left, $top, $width, $height);
    }

    public function getImageSize() {
        $format = $this->format;
        if (!$format) {
            $format = 'JPEG';
        }
        $tmpFile = $this->config->getPath('uploadTmpDir').md5('gdTmpImage'.microtime(true));
        $this->pic->saveToFile($tmpFile, $format);
        $size = null;
        if (file_exists($tmpFile)) {
            $size = filesize($tmpFile);
            unlink($tmpFile);
        }
        return $size;
    }

    public function close() {
        if (is_object($this->pic)) {
            $this->pic->destroy();
        }
    }
}
?>
