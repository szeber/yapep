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
 * Text validator class
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
final class sys_admin_validator_Text {

	private function __construct() {}

	static function required($val, $params=array()) {
        if (is_array($val)) {
            return (count($val) > 0);
        }
		return ('' != $val);
	}

	static function notEmpty($val, $params=array()) {
		return ('' != trim($val));
	}

    static function email($val, $params=array()) {
        if (!strlen(trim($val))) {
            return true;
        }
        return checkEmailAddressValid($val);
    }

	static function regex($val, $params=array()) {
		if (!$params['pattern']) {
			return false;
		}
		$ize =  (bool)preg_match($params['pattern'], $val);
		return $ize;
	}

	static function isNumeric($val, $params=array()) {
		return is_numeric($val);
	}

	static function minLength($val, $params=array()) {
		if (!isset($params['length'])) {
			return false;
		}
		return (mb_strlen($val, 'UTF-8') >= $params['length']);
	}

	static function maxLength($val, $params=array()) {
		if (!isset($params['length'])) {
			return false;
		}
		return (mb_strlen($val, 'UTF-8') <= $params['length']);
	}
}
?>