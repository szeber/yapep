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
 * Image handler interface
 *
 * @package	YAPEP
 * @subpackage  Image
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 11068 $
 */
interface sys_image_IImage {
    /**
     * Constructor
     */
    public function __construct(sys_IApplicationConfiguration $config = null);

    /**
     *
     * @param string $image
     * @return boolean
     */
    public function readImage($image);

    /**
     *
     * @param string $format
     */
    public function setImageFormat($format);

    /**
     * @return integer
     */
    public function getImageWidth();

    /**
     * @return integer
     */
    public function getImageHeight();

    /**
     *
     * @param integer $cols
     * @param integer $rows
     * @param boolean $fit
     */
    public function scaleImage($cols, $rows, $fit = false);

    /**
     *
     * @param string $image
     */
    public function writeImage($image = null);

    /**
     *
     * @param integer $width
     * @param integer $height
     */
    public function cropThumbnailImage($width, $height);

    /**
     * @return integer
     */
    public function getImageSize();

    /**
     * Close the image
     */
    public function close();
}
?>
