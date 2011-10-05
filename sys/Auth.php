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
 * Include PasswordHash library
 */
if (defined('PHPPASS_PATH')) {
	require_once (PHPPASS_PATH.'PasswordHash.php');
} else {
	require_once (LIB_DIR . 'PHPPass/PasswordHash.php');
}

/**
 * Authentication class
 *
 * @package	YAPEP
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_Auth {

	/**
	 * The name of the key in the session to store the authentication info
	 *
	 * @var string
	 */
	protected $sessionArray='LoggedInAdminData';

	/**
	 * Password hash object
	 *
	 * @var PasswordHash
	 */
	static protected $passwordHash;

	/**
	 * Database access
	 *
	 * @var module_db_interface_UserAuth
	 */
	protected $db;

	/**
	 * Database access class name
	 *
	 * @var string
	 */
	protected $dbClass;

	protected $errors = array();

	/**
	 * Constructor
	 *
	 * @param string $sessionarray The name of the session key to use to store the authentication info
	 * @param string $dbClass The name of the database class to be used
	 */
	public function __construct($sessionArray, $dbClass) {
		self::makePasswordHashInstance();
		$this->sessionArray=$sessionArray;
		$this->dbClass=$dbClass;
	}

	protected static function makePasswordHashInstance() {
		if (!is_object(self::$passwordHash)) {
			self::$passwordHash = new PasswordHash(5, FALSE);
		}
	}

	/**
	 * Checks if a user is logged in
	 *
	 * @return boolean True on a logged in user, false otherwise
	 */
	public function checkLoggedIn($onlySession = false) {
		if($this->checkSessionLogin()) {
			return true;
		}
		if ($onlySession) {
			return false;
		}
		if($this->checkPostLogin()) {
			return true;
		}
		return false;
	}

	/**
	 * Logs out a user
	 *
	 * @return boolean True on success
	 */
	public function logout() {
		if(isset($_SESSION[$this->sessionArray])) {
			unset($_SESSION[$this->sessionArray]);
		}
		return true;
	}

	/**
	 * Checks for a session login (user already logged in)
	 *
	 * @return boolean True on a logged in user
	 */
	protected function checkSessionLogin() {
		if(isset($_SESSION[$this->sessionArray]['UserId']) && $_SESSION[$this->sessionArray]['UserId'] > 0) {
			$_SESSION[$this->sessionArray]['LastActivityTime'] = time();
			return true;
		}
		return false;
	}

	/**
	 * Checks for a POST login (user logs in through the login form)
	 *
	 * @return boolean True on successful login
	 */
	protected function checkPostLogin() {
		if(!isset($_POST['login']['username']) || !isset($_POST['login']['password'])) {
			if (!isset($_POST['login']) && isset($_POST['username']) && isset($_POST['password'])) {
				$_POST['login'] = array('username'=>$_POST['username'], 'password'=>$_POST['password']);
			} else {
				return false;
			}
		}
		if($_POST['login']['username'] == '' || $_POST['login']['password'] == '') {
			return false;
		}
		$this->db = getPersistClass($this->dbClass);
		$userdata = $this->db->getUserByUserName($_POST['login']['username']);
		if(!$userdata || !count($userdata)) {
			$this->errors[] = _('Bad username or password');
			return false;
		}
		$password = $_POST['login']['password'];
		if(!self::$passwordHash->CheckPassword($password, $userdata['password'])) {
			$this->errors[] = _('Bad username or password');
			return false;
		}
		self::cleanupUserdata($userdata);
		$_SESSION[$this->sessionArray] = $userdata;
		return true;
	}

	/**
	 * Checks if a user is already locked out
	 *
	 */
	protected function checkLockOut() {
		// FIXME implement lockout
	}

	/**
	 * Returns a hashed version of the provided password
	 *
	 * @param string $password
	 * @return string
	 */
	public static function hashPassword($password) {
		self::makePasswordHashInstance();
		return self::$passwordHash->HashPassword($password);

	}

	/**
	 * Returns all authentication errors as an array
	 *
	 * @return array
	 */
	public function getErrors() {
		return $this->errors;
	}

	/**
	 * Prepares an array of user information to be used for authentication
	 *
	 * @param array $userdata
	 */
	public static function cleanupUserdata(&$userdata) {
		unset($userdata['password']);
		$userdata['UserId'] = $userdata['id'];
		$time = time();
		$userdata['LoginTime'] = $time;
		$userdata['LastActivityTime'] = $time;
	}
}
?>