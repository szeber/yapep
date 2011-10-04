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
 * Template generic database interface
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface module_db_interface_Template {

	/**
	 * Returns the list of templates (array with id=>name format)
	 *
	 * @return array
	 */
	public function getTemplateList();

	/**
	 * Returns the boxplaces for a given template
	 *
	 * @param integer $templateId
	 * @return array
	 */
	public function getTemplateBoxplaces($templateId);

	/**
	 * Adds boxplaces to the specified template
	 *
	 * @param integer $templateId
	 * @param array $boxplaces
	 */
	public function addTemplateBoxplaces($templateId, $boxplaces);

}
?>