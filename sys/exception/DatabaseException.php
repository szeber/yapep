<?php
/**
 *
 * @package	YAPEP
 * @subpackage	Exception
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Database exception class
 *
 * @package	YAPEP
 * @subpackage	Exception
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_exception_DatabaseException extends Exception {
	const ERR_OBJECT_TYPE_NOT_SET = 1;
	const ERR_OBJECT_TYPE_NOT_FOUND = 2;
	const ERR_MISSING_FIELD = 3;
	const ERR_INVALID_SELECT_QUERY = 4;
	const ERR_INVALID_UPDATE_QUERY = 5;
	const ERR_INVALID_TABLE_NAME = 6;
	const ERR_SCHEMA_ERROR = 7;
	const ERR_FUNC_ERROR = 8;
	const ERR_INVALID_LISTENER = 9;
	const ERR_INVALID_PARAM_COUNT = 10;
	const ERR_CUSTOM_ERROR_1 = 101;
    const ERR_CUSTOM_ERROR_2 = 102;
    const ERR_CUSTOM_ERROR_3 = 103;
    const ERR_CUSTOM_ERROR_4 = 104;
    const ERR_CUSTOM_ERROR_5 = 105;
    const ERR_CUSTOM_ERROR_6 = 106;
    const ERR_CUSTOM_ERROR_7 = 107;
    const ERR_CUSTOM_ERROR_8 = 108;
    const ERR_CUSTOM_ERROR_9 = 109;
    const ERR_CUSTOM_ERROR_10 = 110;
}
?>