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
 * Site locale administration module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_cms_Language extends sys_admin_AdminModule {

	/**
	 * Stores the language and locale db handler instance
	 *
	 * @var module_db_interface_LangLocale
	 */
	protected $langHandler;

	protected function buildForm() {

        $this->requireSuperuser();

		$this->langHandler = getPersistClass('LangLocale');

		$this->setTitle(_('Language editor'));

		$control = new sys_admin_control_IdSelect();
		$control->addOptions($this->langHandler->getLangList());
		$control->setValue($this->id, true);
		$this->addControl($control, 'idSelect');

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Language code'));
		$this->addControl($control, 'language_code');

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Locale name'));
		$this->addControl($control, 'name');

		$control = new sys_admin_control_SelectInput();
		$control->addOptions($this->langHandler->getLocaleList());
		$control->setLabel('Locale ID');
		$control->setRequired();
		$control->setNullValueLabel(_('-- Please select --'));
		$this->addControl($control, 'locale_id');
	}

	protected function doLoad() {
		return $this->langHandler->loadLangItem ($this->id);
	}

	protected function doSave() {
		if ($this->mode == sys_admin_AdminModule::MODE_ADD) {
			$result = $this->langHandler->insertLangItem ($this->panel->getInputValues ());
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
			$result = $this->langHandler->updateLangItem ($this->id, $this->panel->getInputValues ());
		}
		return $result;
	}

	protected function doDelete() {
		return $this->langHandler->deleteLangItem($this->id);
	}
}
?>