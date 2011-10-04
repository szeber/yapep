<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	BoxModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 11068 $
 */

 /**
 * Redirect module
 *
 * @arg			text "url" "Target URL" 0 "" 0
 * @arg         check "noprefix" "Don't prefix with $LANG" 0 "0" 0
 * @arg         text "code" "HTTP status code" 0 "301" 0
 * @package	YAPEP
 * @subpackage	BoxModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 11068 $
 */
class module_box_HtmlTemplate extends sys_BoxModule {
	protected function main() {
        switch((int)$this->argArr['code']) {
            case 301:
                $message = 'Moved permanently';
                break;
            case 302:
                $message = 'Found';
                break;
            default:
                $message = 'Found';
        }
        header('HTTP/1.1 '.(int)$this->argArr['code'].' '.$message);
        if ($this->argArr['noprefix']) {
            header('Location: '.$this->argArr['url']);
        } else {
            $url = preg_replace('/^\//', '', $this->argArr['url']);
            header('Location: '.$this->getPath('rootUrl').$this->argArr['lang'].'/'.$url);
        }
        exit();
	}

}
?>