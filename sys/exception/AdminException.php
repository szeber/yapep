<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	Exception
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Admin exception
 *
 * @package	YAPEP
 * @subpackage	Exception
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_exception_AdminException extends Exception {

	// ERROR CODES
	const ERR_CONTAINER_INVALID_CONTROL_NAME = 1;

	const ERR_CONTAINER_CONTROL_EXISTS = 2;

	const ERR_CONTAINER_INPUT_NAME_IN_USE = 3;

	const ERR_CONTAINER_CONTROL_DOES_NOT_EXIST = 4;

	const ERR_POSITIONABLE_INVALID_COORD = 5;

	const ERR_XML_NOT_WELL_FORMED = 6;

	const ERR_INVALID_XML_RECEIVED = 7;

	const ERR_ADMIN_MODULE_NOT_FOUND = 8;

	const ERR_ID_NOT_FOUND = 9;

	const ERR_INVALID_LOCALE = 10;

	const ERR_INVALID_ADMIN_MODULE_NAME = 11;

	const ERR_MODULE_MODE_NOT_SET = 12;

	const ERR_MODULE_DOES_NOT_SUPPORT_MODE = 13;

	const ERR_INVALID_EVENT_NAME = 14;

	const ERR_NOT_AUTHORIZED = 15;

	const ERR_NO_DATABASE_HANDLER_SET = 17;

	const ERR_FILEINPUT_INVALID_DIRECTORY = 18;

	const ERR_LISTENER_NAME_EMPTY = 19;

	const ERR_MODULE_MODE_NOT_VALID = 20;

	const ERR_INVALID_LIST_NAME = 21;

	const ERR_INVALID_LIST_MODULE = 22;

	const ERR_NO_SUBMODULE_SET = 23;

	const ERR_SUBMODULE_NOT_FOUND = 24;

	const ERR_SAVING_NOT_IMPLEMENTED = 25;

	const ERR_LOADING_NOT_IMPLEMENTED = 26;

	const ERR_BAD_CONTROL_TYPE = 27;

	const ERR_PERSISTANCE_MODULE_NOT_FOUND = 28;

	const ERR_PERSISTANCE_MODULE_NOT_LIST = 29;

	const ERR_ADMIN_LOCALE_IS_INVALID = 30;

	const ERR_REQUIRED_OPTIONS_NOT_SET_FOR_CONTROL = 31;

	const ERR_INSUFFICIENT_RIGHTS = 32;

	const ERR_LOGIN_FAILED = 33;

	const ERR_NOT_LOGGED_IN = 34;

	const ERR_SAVE_ERROR = 35;

	const ERR_DELETING_NOT_IMPLEMENTED = 36;
}
?>