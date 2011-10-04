<?php

/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 11068 $
 */

/**
 * Button control to open a form in a popup window
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 11068 $
 */
class sys_admin_control_DownloadButton extends sys_admin_control_TextInput
{

    /**
     * Sets the displayed form's name
     *
     * @param string $formName
     */
    public function setTargetForm ($formName)
    {
        $this->options['targetForm'] = $formName;
    }

}
?>