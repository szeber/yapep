<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Generic error handler class
 *
 * @package	YAPEP
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_ErrorHandler implements sys_ErrorHandlerInterface {
 	public function handleError($errorCode) {
 		$message=self::getErrorMessageByCode($errorCode);
 		header('HTTP/1.1 '.$errorCode.' '.$message);
 		echo '<h1>'.$errorCode.' '.$message.'</h1>';
 		exit();
 	}

 	public static function getErrorMessageByCode($errorCode) {
 		switch ($errorCode) {
 			case '404':
 				return 'Not found';
 			case '403':
 				return 'Forbidden';
 			case '500':
 				return 'Internal server error';
 		}
 	}
}
?>