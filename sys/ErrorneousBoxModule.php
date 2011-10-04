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
 * Missing box module
 *
 * This module gets called if a referenced module is missing or can't be loaded.
 * If debugging is enabled, it prints out the box for the module and the moudle's information.
 * With debugging disabled it doesn't do anything.
 *
 * @package	YAPEP
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_ErrorneousBoxModule extends sys_MissingBoxModule  {

	/**
	 * @see sys_BoxModule::main()
	 *
	 */
	protected function main() {
		if ( DEBUGGING) {
			return '<div style="border: 1px solid #f00; height: 85px; text-align: center; background-color: #fff; overflow: auto;"><p style="color: #f00; font-size: 13px; font-weight: bold;">ERROR IN MODULE</p><p style="font-size: 13px; font-weight: bold;">' . $this->moduleInfo ['name'] . '</p><p style="font-size: 12px; font-weight: normal;">' . $this->moduleInfo ['description'] . '</p><p style="color: #f00; font-size: 12px; font-weight: normal;"><strong>Error message: </strong>'.$this->argArr['_errorMessage'].'</strong></p><p style="color: #f00; font-size: 12px; font-weight: normal;"><strong>Thrown at: </strong>'.$this->argArr['_throwFile'].' <strong>line</strong> '.$this->argArr['_throwLine'].'</p></div>';
		}
		return '';
	}
}
?>