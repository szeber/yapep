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
 * Template administration class
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_cms_Template extends sys_admin_AdminModule {

	/**
	 * @var module_db_interface_Template
	 */
	protected $templateHandler;

	protected function buildForm() {
        $this->requireSuperuser();

		$this->templateHandler = getPersistClass('Template');

		$this->setDbHandler($this->templateHandler);

		$this->setTitle(_('Template editor'));

		$control = new sys_admin_control_IdSelect();
		$control->addOptions($this->templateHandler->getTemplateList());
		$control->setValue($this->id, true);
		$this->addControl($control, 'idSelect');

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Template name'));
		$this->addControl($control, 'name');

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Template file'));
		$this->addControl($control, 'file');

		$langHandler = getPersistClass('LangLocale');
		$control = new sys_admin_control_SelectInput();
		$control->addOptions($langHandler->getLocaleList());
		$control->setNullValueLabel(_('All languages'));
		$control->setLabel(_('Locale'));
		$this->addControl($control, 'locale_id');

		$control = new sys_admin_control_CheckBox();
		$control->setLabel(_('Reread boxplaces'));
		$control->setDefaultValue(false);
		$this->addControl($control, 'rereadBoxplaces');
	}

	protected function sanitizeFileName($filename) {
		$filename = preg_replace('/\.{2,}/', '.', str_replace('/', '', $filename));
		return $filename;
	}

	/**
	 * @see sys_admin_AdminModule::processSaveData()
	 *
	 */
	protected function processSaveData() {
		$this->data['file'] = $this->sanitizeFileName($this->data['file']);
		if (!file_exists(PROJECT_PATH.'template/page/'.$this->data['file'])) {
			throw new sys_exception_AdminException(_('Template file not found'), sys_exception_AdminException::ERR_SAVE_ERROR);
		}
	}

	/**
	 * @see sys_admin_AdminModule::postSave()
	 *
	 */
	protected function postSave() {
		if ($this->mode == sys_admin_AdminModule::MODE_EDIT && !$this->data['rereadBoxplaces']) {
			return;
		}
		$template = file_get_contents(PROJECT_PATH.'template/page/'.$this->data['file']);
		preg_match_all('/\{\$boxplaces\.([^}]+)\}/', $template, $boxplaces);
		$boxplaces = $boxplaces[1];
		$headerIdx = array_search('header', $boxplaces);
		if (false !== $headerIdx) {
			unset($boxplaces[$headerIdx]);
		}
		array_unshift($boxplaces, 'header');
		if ($this->mode == sys_admin_AdminModule::MODE_EDIT) {
			$data = $this->templateHandler->getTemplateBoxplaces($this->id);
			foreach($data as $val) {
				if (in_array($val['boxplace'], $boxplaces)) {
					unset($boxplaces[array_search($val['boxplace'], $boxplaces)]);
				}
			}
		}
		$this->templateHandler->addTemplateBoxplaces($this->id, $boxplaces);
	}

}
?>