<?php
/**
 * This file is part of YAPEP.
 *
 * @package YAPEP
 * @subpackage  Admin
 * @author      Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright   2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version $Rev: 11068 $
 */

 /**
 * BrowserForm admin control
 *
 * @package YAPEP
 * @subpackage  Admin
 * @author      Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright   2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version $Rev: 11068 $
 */
class sys_admin_control_BrowserForm extends sys_admin_control_Panel {
    public function setType($type) {
        $this->options['formType']=$type;
    }

    public function setForms($forms) {
        foreach($forms as $formName=>$form) {
            $control = new sys_admin_control_Form();
            $control->setName($form);
            $this->addControl($control, $formName);
        }
    }
}
?>