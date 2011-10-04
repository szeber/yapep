<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 16203 $
 */

/**
 * Dummy debugger class
 *
 * Overrides all public methods of the Debugger class to disable debugging
 *
 * @package YAPEP
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.

 * @version	$Rev: 16203 $
 */
class sys_DummyDebugger extends sys_Debugger {

	public static function addModuleDebugInfo($moduleInfo,$args,$smartyVars,$cached) {}

	public function getDebugInfo() {}

    public function  getAdminDebugInfo($module, $receivedXml, $sentXml) {}

	public function setError($message) {}

	public function addSystemQueries() {}

    public function addLog() {}

    public function getFirePhp() {
        return new sys_DummyFirePhp();
    }
}
