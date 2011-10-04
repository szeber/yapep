<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	Database
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Multi language template class
 *
 * @package	YAPEP
 * @subpackage	Database
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_db_MultiLanguageTemplate extends Doctrine_Template {

	protected $_options = array ('field' => 'locale_id');

	public function __construct(array $options = array()) {
		$this->_options = Doctrine_Lib::arrayDeepMerge ($this->_options, $options);
	}

	public function setTableDefinition() {
		$this->hasColumn ($this->_options ['field'], 'integer');
	}

	public function setUp() {
		$this->hasOne ('LocaleData as Locale', array ('local' => $this->_options ['field'], 'foreign' => 'id'));
	}

}
?>