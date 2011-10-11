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
 * Theme manager
 *
 * @package	YAPEP
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_ThemeManager {

	/**
	 * Database access
	 *
	 * @var module_db_generic_Theme
	 */
	private static $db;

	/**
	 * Returns the currently selected theme's ID
	 *
	 * @param sys_IApplicationConfiguration $config
	 * @return integer
	 */
	public static function getTheme(sys_IApplicationConfiguration $config = null) {
		if (!self::$db) {
			self::getDb();
		}
		if (!empty($_SESSION['cms_theme'])) {
			return $_SESSION['cms_theme'];
		}
		if (is_null($config)) {
			$config = sys_ApplicationConfiguration::getInstance();
		}
		$theme = array('id' => $config->getOption('defaultTheme'));
		if (!$theme['id']) {
			$theme = self::$db->getDefaultTheme();
		}
		self::setTheme($theme);
		return $theme;
	}

	/**
	 * Changes the current theme
	 *
	 * @param integer $themeId
	 * @return boolean True on success, false on failure
	 */
	public static function changeTheme($themeId) {
		if (!self::$db) {
			self::getDb();
		}
		if (!self::$db->checkThemeExists($themeId)) {
			return false;
		}
		self::setTheme($themeId);
		return true;
	}

	/**
	 * Sets the current theme
	 *
	 * @param integer $themeId
	 */
	private static function setTheme($themeId) {
		$_SESSION['cms_theme']=$themeId;
	}

	/**
	 * Makes the database connection object
	 *
	 */
	private static function getDb() {
		self::$db=getPersistClass('Theme');
	}

}
?>