<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	Database
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

 /**
 * Doctrine model helper class
 *
 * @package	YAPEP
 * @subpackage	Database
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_db_ModelHelper {

	public static function getAllModelNames() {
		$dirs = array ();
		$dir = opendir (PROJECT_PATH . 'models');
		$tmp = readdir ($dir);
		while ( false !== $tmp ) {
			if ('.' != $tmp && '..' != $tmp && '.' != substr ($tmp, 0, 1)) {
				$dirs [] = substr ($tmp, 0, strpos ($tmp, '.'));
			}
			$tmp = readdir ($dir);
		}
		closedir ($dir);
		$dir = opendir (SYS_PATH . 'models');
		$tmp = readdir ($dir);
		while ( false !== $tmp ) {
			if ('.' != $tmp && '..' != $tmp && '.' != substr ($tmp, 0, 1) && !in_array ($tmp, $dirs)) {
				$dirs [] = substr ($tmp, 0, strpos ($tmp, '.'));
			}
			$tmp = readdir ($dir);
		}
		return $dirs;
	}
}
?>