<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	BoxModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

 /**
 * Static HTML from template module
 *
 * @arg			text "template" "Template neve" 0 "" 0
 * @package	YAPEP
 * @subpackage	BoxModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_box_HtmlTemplate extends sys_BoxModule {
	protected function main() {
		return $this->smartyFetch();
	}

}
?>