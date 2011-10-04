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

        $control = new sys_admin_control_CheckBox();
		$control->setLabel(_('Delete unused boxplaces'));
        $control->setDescription(_('Only works if "Reread boxplaces" is also enabled. WARNING! Deleting a boxplace also deletes all boxes in this boxplace on all the pages using this template!'));
		$control->setDefaultValue(false);
		$this->addControl($control, 'deleteUnusedBoxplaces');
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
		preg_match_all('/\{\$boxplaces\.([^}]+)\}/', $template, $tmp);
		$tmp = $tmp[1];
		$headerIdx = array_search('header', $tmp);
		if (false !== $headerIdx) {
            $this->addWarning(_('The header boxplace is a reserved name. Please do not use it in your template.'));
			unset($tmp[$headerIdx]);
		}
        $boxplaces = array('header');
        foreach($tmp as $boxplace) {
            $boxplaces[] = $boxplace;
        }
		$this->templateHandler->addTemplateBoxplaces($this->id, $boxplaces);
        $data = $this->templateHandler->getTemplateBoxplaces($this->id);
        $unusedBoxplaces = array();
        foreach($data as $key=>$val) {
            $idx = array_search($val['boxplace'], $boxplaces);
            if (false === $idx) {
                $unusedBoxplaces[] = $val['boxplace'];
            }
        }
        if (count($unusedBoxplaces) > 0) {
            if ($this->data['deleteUnusedBoxplaces']) {
                $this->templateHandler->deleteTemplateBoxplaces($this->id, $unusedBoxplaces);
                $this->addWarning("Removed the following boxplaces:\n\n".implode("\n", $unusedBoxplaces));
            } else {
                $this->addWarning("The following boxplaces are not used in the template:\n\n".implode("\n", $unusedBoxplaces));
            }
        }
	}

}
?>