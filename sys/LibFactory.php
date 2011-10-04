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
 * Library factory
 *
 * @package	YAPEP
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_LibFactory {

	/**
	 * Array containing the database connection instances
	 *
	 * @var array
	 */
	private static $DATABASE_INSTANCES;

	/**
	 * Smarty object
	 *
	 * @var Smarty
	 */
	private static $SMARTY = null;

	/**
	 * Application configuration object
	 *
	 * @var sys_IApplicationConfiguration
	 */
	private static $CONFIG = null;

	/**
	 * PHPMailer object
	 *
	 * @var PHPMailer
	 */
	private static $MAILER = null;

	/**
	 * Returns the requested database connection
	 *
	 * @param string $connection
	 * @return sys_db_Database|Doctrine_Connection
	 */
	public static function getDbConnection($connection = 'site') {
		if (!is_array (self::$DATABASE_INSTANCES) || !isset (self::$DATABASE_INSTANCES [$connection])) {
			self::makeDbConnection ($connection);
		}
		return self::$DATABASE_INSTANCES [$connection];
	}

	/**
	 * Clears, restarts and returns a database connection
	 *
	 * @param string $connection
	 * @return mixed
	 */
	public static function restartDbConnection($connection = 'site') {
		if (isset (self::$DATABASE_INSTANCES [$connection])) {
			if (self::$DATABASE_INSTANCES [$connection] instanceof Doctrine_Connection) {
				self::$DATABASE_INSTANCES [$connection]->close ();
			}
			unset (self::$DATABASE_INSTANCES [$connection]);
		}
		return self::getDbConnection ($connection);
	}

	/**
	 * Makes a new database connection
	 *
	 * @param string $connection
	 */
	private static function makeDbConnection($connection) {
		if (is_null (self::$CONFIG)) {
			self::loadConfig ();
		}
		if (!is_array (self::$DATABASE_INSTANCES)) {
			self::$DATABASE_INSTANCES = array ();
		}
		$dbData = self::$CONFIG->getDatabase ($connection);
		switch ( $dbData ['type']) {
			case 'Doctrine' :
                if (!is_dir(CACHE_DIR.'dbSchema/Doctrine/')) {
                    $cache = new sys_cache_DbSchema();
                    $cache->recreateCache();
                }

				$manager = Doctrine_Manager::getInstance ();
				$conn = $manager->openConnection ($dbData ['dsn'], $connection);
				self::$DATABASE_INSTANCES [$connection] = $conn;
				$conn->setCharset ($dbData ['charset']);
				if (DEBUGGING) {
					$conn->addListener (new sys_db_DoctrineDebugListener (), $connection);
				}
				return;
				break;
			case 'Adodb' :
				$conn = new sys_db_AdodbDatabase (self::$CONFIG, $connection);
				if (DEBUGGING) {
					$conn->addListener (new sys_db_DatabaseDebugListener (), $connection);
				}
				break;
			default :
				$dbhandler = 'sys_db_' . ucfirst (strtolower ($dbData ['type'])) . 'Database';
				$conn = new $dbhandler (self::$CONFIG, $connection);
				if (DEBUGGING) {
					$conn->addListener (new sys_db_DatabaseDebugListener (), $connection);
				}
				break;
		}
		if (!($conn instanceof sys_db_Database) || !$conn->getConnected ()) {
			throw new sys_exception_DatabaseException ('Can\'t connect to the database');
		}
		self::$DATABASE_INSTANCES [$connection] = $conn;
	}

	/**
	 * Returns a PHPMailer object
	 *
	 * @return PHPMailer
	 */
	public static function getMailer() {
		if (is_null (self::$MAILER)) {
			self::makeMailer ();
		}
		return clone(self::$MAILER);
	}

	/**
	 * Makes a new PHPMailer object and configures it
	 *
	 */
	private static function makeMailer() {
		if (is_null (self::$CONFIG)) {
			self::loadConfig ();
		}
		if (defined (PHPMAILER_PATH)) {
			require_once (PHPMAILER_PATH . 'class.phpmailer.php');
			$phpmailerPath = PHPMAILER_PATH;
		} else {
			require_once (LIB_DIR . 'PHPMailer/class.phpmailer.php');
			$phpmailerPath = LIB_DIR . 'PHPMailer/';
		}
		self::$MAILER = new PHPMailer ();
		self::$MAILER->SetLanguage (self::$CONFIG->getOption ('mailerLanguage'), $phpmailerPath . 'language/');
		self::$MAILER->PluginDir = $phpmailerPath;
		$smtp = self::$CONFIG->getOption ('mailerSMTP');
		if ($smtp != '') {
			require_once ($phpmailerPath . 'class.smtp.php');
			self::$MAILER->IsSMTP ();
			self::$MAILER->Host = $smtp;
		}
		self::$MAILER->CharSet = self::$CONFIG->getOption ('mailerCharSet');
	}

	/**
	 * Returns a cloned Smarty object
	 *
	 * @return Smarty
	 */
	public static function getSmarty() {
		if (is_null (self::$SMARTY)) {
			self::makeSmarty ();
		}
		return clone (self::$SMARTY);
	}

	/**
	 * Returns the original Smarty object
	 *
	 * Any changes to this object will be present in all later Smarty objects!
	 *
	 * @return Smarty
	 */
	public static function getSmartyProto() {
		if (is_null (self::$SMARTY)) {
			self::makeSmarty ();
		}
		return self::$SMARTY;
	}

	/**
	 * Makes a new Smarty object and configures it
	 *
	 */
	private static function makeSmarty() {
		if (is_null (self::$CONFIG)) {
			self::loadConfig ();
		}
		if (defined (SMARTY_PATH)) {
			require_once (SMARTY_PATH . 'Smarty.class.php');
		} else {
			require_once (LIB_DIR . 'Smarty/Smarty.class.php');
		}
		self::$SMARTY = new Smarty ();
		if (!file_exists(self::$CONFIG->getPath ('smartyCompileDir')) || !is_dir(self::$CONFIG->getPath ('smartyCompileDir'))) {
			mkdir(self::$CONFIG->getPath ('smartyCompileDir'), 0777, true);
		}
		self::$SMARTY->template_dir = self::$CONFIG->getPath ('smartyTemplateDir');
		self::$SMARTY->compile_dir = self::$CONFIG->getPath ('smartyCompileDir');
		self::$SMARTY->config_dir = self::$CONFIG->getPath ('siteConfigs');
		if (CACHING && self::$CONFIG->getOption ('smartyCache')) {
			self::$SMARTY->caching = 2;
		} else {
			self::$SMARTY->caching = 0;
		}
		if (self::$CONFIG->getOption ('final')) {
			self::$SMARTY->compile_check = false;
		}
		if (!is_dir (self::$CONFIG->getPath ('smartyCacheDir'))) {
			mkdir (self::$CONFIG->getPath ('smartyCacheDir'), 0775);
		}
		self::$SMARTY->cache_dir = self::$CONFIG->getPath ('smartyCacheDir');
		self::$SMARTY->debugging = self::$CONFIG->getOption ('smartyDebug');
		self::$SMARTY->register_resource ('yapep', array ('yapepGetTemplate', 'yapepGetTimestamp', 'yapepGetSecure', 'yapepGetTrusted'));
	}

	/**
	 * Sets the configuration instance for the class
	 *
	 * @param sys_IApplicationConfiguration $config
	 */
	public static function setConfig(sys_IApplicationConfiguration $config = null) {
		self::$CONFIG = $config;
	}

	/**
	 * Loads the current configuration
	 *
	 */
	private static function loadConfig() {
		self::$CONFIG = sys_ApplicationConfiguration::getInstance ();
	}

    /**
     * Returns an object that can be used to resize images
     *
     * The returned object will be either sys_image_Imagick (php-imagick extension)
     * or sys_image_ImagickCli (imagemagick via the command line)
     * or sys_image_Gd (GD extension (not thread safe!)).
     * If neither of the requirements are found, it throws a sys_exception_SiteException
     *
     * @return sys_image_IImage
     * @throws sys_exception_SiteException if no image manipulation extension is found
     * @todo add command line imagick
     */
    public static function getImage() {
		if (is_null (self::$CONFIG)) {
			self::loadConfig ();
		}
        if (class_exists('Imagick')) {
            return new sys_image_Imagick();
        } else if (self::$CONFIG->getPath('imagickDir')) {
            return new sys_image_ImagickCli();
        } else if (function_exists('imagecreatetruecolor')) {
            require_once LIB_DIR.'WideImage/WideImage.inc.php';
            return new sys_image_Gd();
        } else {
            throw new sys_exception_SiteException('No image manipulation extension found', 500);
        }
    }
}
?>