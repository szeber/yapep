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
 * Math validator class
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
final class sys_admin_validator_Math {

	private function __construct() {}

	static function isInteger($val, $params=array()) {
		return ((int)$val == $val);
	}

	static function greaterThan($val, $params=array()) {
		if (!isset($params['value'])) {
			return false;
		}
		return ($val > $params['value']);
	}

	static function greaterEquals($val, $params=array()) {
		if (!isset($params['value'])) {
			return false;
		}
		return ($val >= $params['value']);
	}

	static function lowerThan($val, $params=array()) {
		if (!isset($params['value'])) {
			return false;
		}
		return ($val < $params['value']);
	}

	static function loverEquals($val, $params=array()) {
		if (!isset($params['value'])) {
			return false;
		}
		return ($val <= $params['value']);
	}

	static function notZero($val, $params=array()) {
		return (0 != (int)$val);
	}

	static function isEqual($val, $params=array()) {
		if (!isset($params['value'])) {
			return false;
		}
		return ($val == $params['value']);
	}
}
?>