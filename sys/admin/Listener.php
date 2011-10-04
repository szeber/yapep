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
 * Admin listener base class
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
abstract class sys_admin_Listener {

	/**
	 * Fires before any admin processing takes place
	 *
	 * @param array $event
	 */
	public function preProcess(array $event, sys_admin_AdminManager $manager) {}

	/**
	 * Fires after any admin processing takes place
	 *
	 * @param array $event
	 */
	public function postProcess(array $event, sys_admin_AdminManager $manager) {}

	/**
	 * Fires before admin module execution
	 *
	 * @param array $event
	 */
	public function preExecute(array $event, sys_admin_AdminManager $manager) {}

	/**
	 * Fires after admin module execution
	 *
	 * @param array $event
	 */
	public function postExecute(array $event, sys_admin_AdminManager $manager) {}

	/**
	 * Fires before the form or list building
	 *
	 * @param array $event
	 */
	public function preBuild(array $event, sys_admin_AdminManager $manager) {}

	/**
	 * Fires after the form or list building
	 *
	 * @param array $event
	 */
	public function postBuild(array $event, sys_admin_AdminManager $manager) {}

	/**
	 * Fires before XML parsing by the module
	 *
	 * @param array $event
	 */
	public function preParse(array $event, sys_admin_AdminManager $manager) {}

	/**
	 * Fires after XML parsing by the module
	 *
	 * @param array $event
	 */
	public function postParse(array $event, sys_admin_AdminManager $manager) {}

	/**
	 * Fires before data loading from the database
	 *
	 * @param array $event
	 */
	public function preLoad(array $event, sys_admin_AdminManager $manager) {}

	/**
	 * Fires after data loading from the database
	 *
	 * @param array $event
	 */
	public function postLoad(array $event, sys_admin_AdminManager $manager) {}

	/**
	 * Fires before data deletion
	 *
	 * @param array $event
	 */
	public function preDelete(array $event, sys_admin_AdminManager $manager) {}

	/**
	 * Fires after data deletion
	 *
	 * @param array $event
	 */
	public function postDelete(array $event, sys_admin_AdminManager $manager) {}
	/**
	 * Fires before saving of posted data to the database
	 *
	 * @param array $event
	 */
	public function preSave(array $event, sys_admin_AdminManager $manager) {}

	/**
	 * Fires after saving of posted data to the database
	 *
	 * @param array $event
	 */
	public function postSave(array $event, sys_admin_AdminManager $manager) {}

	/**
	 * Fires before XML generation
	 *
	 * @param array $event
	 */
	public function preXml(array $event, sys_admin_AdminManager $manager) {}

	/**
	 * Fires after XML generation
	 *
	 * @param array $event
	 */
	public function postXml(array $event, sys_admin_AdminManager $manager) {}
}
?>