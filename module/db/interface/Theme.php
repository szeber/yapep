<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Theme generic database interface
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface module_db_interface_Theme {

	/**
	 * Returns the default theme ID
	 *
	 * @return integer
	 */
	public function getDefaultTheme();

	/**
	 * Returns true if the provided theme id exists
	 *
	 * @param integer $themeId
	 * @return boolean
	 */
	public function checkThemeExists($themeId);

	/**
	 * Returns the list of themes (array with id=>name format)
	 *
	 * @return array
	 */
	public function getThemeList();

}
?>