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
class sys_image_Imagick implements sys_image_IImage {

    /**
     *
     * @var Imagick
     */
    protected $pic;

    public function __construct(sys_IApplicationConfiguration $config = null) {
        $this->pic = new Imagick();
    }

    public function __destruct() {
        $this->close();
    }

    public function readImage($image) {
        return $this->pic->readImage($image);
    }

    public function setImageFormat($format) {
        $this->pic->setImageFormat($format);
    }

    public function getImageWidth() {
        return $this->pic->getImageWidth();
    }

    public function getImageHeight() {
        return $this->pic->getImageHeight();
    }

    public function scaleImage($cols, $rows, $fit = false) {
        $this->pic->scaleImage($cols, $rows, $fit);
    }

    public function writeImage($image = null) {
        $this->pic->writeImage($image);
    }

    public function cropThumbnailImage($width, $height) {
        $this->pic->cropThumbnailImage($width, $height);
    }

    public function getImageSize() {
        return $this->pic->getImageSize();
    }

    public function close() {
        $this->pic->clear();
        $this->pic->destroy();
    }
}
?>
