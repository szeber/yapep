<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

 /**
 * Admin locale administration module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_cms_AdminLocale extends sys_admin_AdminModule {

	/**
	 * Stores the language and locale db handler instance
	 *
	 * @var module_db_interface_LangLocale
	 */
	protected $langHandler;

	protected function buildForm() {

        $this->requireSuperuser();

		$this->langHandler = getPersistClass('LangLocale');

		$this->setTitle(_('Admin locale editor'));

		$control = new sys_admin_control_IdSelect();
		$control->addOptions($this->langHandler->getAdminLocaleList());
		$control->setValue($this->id, true);
		$this->addControl($control, 'idSelect');

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Locale code'));
		$this->addControl($control, 'locale_code');

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Locale name'));
		$this->addControl($control, 'name');
	}

	protected function doLoad() {
		return $this->langHandler->loadAdminItem ($this->id);
	}

	protected function doSave() {
		if ($this->mode == sys_admin_AdminModule::MODE_ADD) {
			$result = $this->langHandler->insertAdminItem ($this->panel->getInputValues ());
			if ($result && is_numeric ($result)) {
				$this->options ['newId'] = $result;
				$this->id = $result;
				if ($this->newForm) {
					$this->options ['newForm'] = $this->newForm;
				}
				$result = '';
			} elseif (!$result) {
				$result = 'Insert error';
			}
		} else {
			$result = $this->langHandler->updateAdminItem ($this->id, $this->panel->getInputValues ());
		}
		return $result;
	}

	protected function doDelete() {
		return $this->langHandler->deleteAdminItem($this->id);
	}
}
?>