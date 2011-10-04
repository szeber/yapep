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
 * We will load several PO files in memory, so raise the memory limit
 */
ini_set('memory_limit', '100M');

 /**
 * POEditor scanner module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_poeditor_Scan extends sys_admin_AdminModule {

	/**
	 * @var array
	 */
	protected $texts;

	/**
	 * @var array
	 */
	protected $foundTexts;

	/**
	 * @var array
	 */
	protected $newTexts;

	/**
	 * @var array
	 */
	protected $translations;

	/**
	 * @var module_db_interface_Poeditor
	 */
	protected $poeditDb;

	/**
	 * @see sys_admin_AdminModule::init()
	 *
	 */
	protected function init() {
		$this->poeditDb = getPersistClass('Poeditor');
		$this->texts = array('admin'=>array(), 'site'=>array());
		$this->newTexts = $this->texts;
		$this->foundTexts = $this->texts;
	}

	protected function buildForm() {
		$this->setDeleteBtnDisabled(true);
		$this->setAddBtnDisabled(true);

		$control = new sys_admin_control_CheckBox();
		$control->setLabel(_('Delete obsolete texts'));
		$this->addControl($control, 'delete_obsolete');

		$control = new sys_admin_control_CheckBox();
		$control->setLabel(_('Import translations'));
		$this->addControl($control, 'import_translations');

		$control = new sys_admin_control_CheckBox();
		$control->setLabel(_('Imports overwrite'));
		$control->setDescription(_('When importing the translations in the PO files overwrite the ones in the database'));
		$this->addControl($control, 'imports_overwrite');
	}

	protected function scanFiles() {
		$this->processDir(PROJECT_PATH . 'module/admin/', module_db_interface_Poeditor::TARGET_ADMIN);
		$this->processDir(PROJECT_PATH . 'module/box/', module_db_interface_Poeditor::TARGET_SITE);
		$this->processDir(PROJECT_PATH . 'module/doc/', module_db_interface_Poeditor::TARGET_SITE);
		$this->processDir(PROJECT_PATH . 'module/utility/', module_db_interface_Poeditor::TARGET_SITE);
		if (file_exists(PROJECT_PATH . 'sys/admin/')) {
			$this->processDir(PROJECT_PATH . 'sys/admin/', module_db_interface_Poeditor::TARGET_ADMIN);
		}
		if (file_exists(PROJECT_PATH . 'template/admin/')) {
			$this->processDir(PROJECT_PATH . 'template/admin/', module_db_interface_Poeditor::TARGET_ADMIN);
		}
		$this->processDir(PROJECT_PATH . 'template/box/', module_db_interface_Poeditor::TARGET_SITE);
		$this->processDir(PROJECT_PATH . 'template/doc/', module_db_interface_Poeditor::TARGET_SITE);
		$this->processDir(PROJECT_PATH . 'template/misc/', module_db_interface_Poeditor::TARGET_SITE);
		$this->processDir(PROJECT_PATH . 'template/page/', module_db_interface_Poeditor::TARGET_SITE);
		if (file_exists(PROJECT_PATH . 'sys/Auth.php')) {
			$this->processFile(PROJECT_PATH . 'sys/Auth.php', module_db_interface_Poeditor::TARGET_BOTH);
		}
		if (PROJECT_PATH == SYS_PATH) {
			return;
		}
		$this->processDir(SYS_PATH . 'module/admin/', module_db_interface_Poeditor::TARGET_ADMIN);
		$this->processDir(SYS_PATH . 'module/box/', module_db_interface_Poeditor::TARGET_SITE);
		$this->processDir(SYS_PATH . 'module/doc/', module_db_interface_Poeditor::TARGET_SITE);
		$this->processDir(SYS_PATH . 'module/utility/', module_db_interface_Poeditor::TARGET_SITE);
		$this->processDir(SYS_PATH . 'sys/admin/', module_db_interface_Poeditor::TARGET_ADMIN);
		$this->processDir(SYS_PATH . 'template/admin/', module_db_interface_Poeditor::TARGET_ADMIN);
		$this->processDir(SYS_PATH . 'template/box/', module_db_interface_Poeditor::TARGET_SITE);
		$this->processDir(SYS_PATH . 'template/doc/', module_db_interface_Poeditor::TARGET_SITE);
		$this->processDir(SYS_PATH . 'template/misc/', module_db_interface_Poeditor::TARGET_SITE);
		$this->processDir(SYS_PATH . 'template/page/', module_db_interface_Poeditor::TARGET_SITE);
		$this->processFile(SYS_PATH . 'sys/Auth.php', module_db_interface_Poeditor::TARGET_BOTH);
	}

	protected function processDir($dirName, $target) {
		$dir = opendir($dirName);
		$file = true;
		while(false !== $file) {
			$file = readdir($dir);
			if (substr($file, 0, 1) == '.' || false === $file) {
				continue;
			}
			if (is_dir($dirName.$file)) {
				$this->processDir($dirName.$file.'/', $target);
				continue;
			}
			if (!preg_match('/\.(tpl|php)$/', $file)) {
				continue;
			}
			$this->processFile($dirName.$file, $target);
		}
	}

	protected function processFile($fileName, $target) {
		$extension = array();
		preg_match('/\.(tpl|php)$/', $fileName, $extension);
		$texts = array();
		$fileContents = file_get_contents($fileName);
		switch($extension[1]) {
			case 'tpl':
				preg_match_all('/\{t\}(.+?)\{\/t\}/', $fileContents, $texts);
				break;
			case 'php':
				preg_match_all('/\b(?:gettext|_)\s*\(\s*[\'"](.+?)[\'"]\s*\)/', $fileContents, $texts);
				break;
			default:
				break;
		}
		if (!count($texts[1])) {
			return;
		}
		switch ($target) {
			case module_db_interface_Poeditor::TARGET_ADMIN:
				$this->processTexts($texts[1], 'admin');
				break;
			case module_db_interface_Poeditor::TARGET_SITE:
				$this->processTexts($texts[1], 'site');
				break;
			case module_db_interface_Poeditor::TARGET_BOTH:
				$this->processTexts($texts[1], 'admin');
				$this->processTexts($texts[1], 'site');
				break;
			default:
				break;
		}
	}

	protected function processTexts($texts, $targetName) {
		foreach ($texts as $text) {
			if (preg_match('/\{\s*\$/', $text)) {
				continue;
			}
			if (in_array($text, $this->texts[$targetName])) {
				if (!in_array($text, $this->foundTexts[$targetName])) {
					$this->foundTexts[$targetName][] = $text;
				}
			} elseif (!in_array($text, $this->foundTexts[$targetName])) {
				$this->foundTexts[$targetName][] = $text;
				$this->newTexts[$targetName][] = $text;
			}
		}
	}

	protected function scanDb() {
		$texts = $this->poeditDb->scanDbTexts();
		$this->processTexts($texts, 'admin');
	}

	protected function loadTexts() {
		$texts = $this->poeditDb->loadTexts();
		foreach($texts as $text) {
			switch($text['type']) {
				case module_db_interface_Poeditor::TARGET_ADMIN:
					$this->texts['admin'][$text['id']] = $text['text'];
					break;
				case module_db_interface_Poeditor::TARGET_SITE:
					$this->texts['site'][$text['id']] = $text['text'];
					break;
				default:
					break;
			}
		}
	}

	protected function importPoTranslations($overwrite = false) {
		$this->loadTexts();
		$localeHandler = getPersistClass('LangLocale');
		$locales = $localeHandler->getAdminLocales();
		foreach($locales as $locale) {
			$this->translations = array();
			if (file_exists(SYS_PATH.'locale/'.$locale['locale_code'].'/LC_MESSAGES/YapepAdmin.po')) {
				$lines = file(SYS_PATH.'locale/'.$locale['locale_code'].'/LC_MESSAGES/YapepAdmin.po');
				$this->processPoLines($lines);
			}
			if (file_exists(PROJECT_PATH.'locale/'.$locale['locale_code'].'/LC_MESSAGES/YapepAdmin.po')) {
				$lines = file(PROJECT_PATH.'locale/'.$locale['locale_code'].'/LC_MESSAGES/YapepAdmin.po');
				$this->processPoLines($lines);
			}
			foreach($this->texts['admin'] as $textId=>$text) {
				if (isset($this->translations[$text])) {
					if (!$overwrite) {
						$translation = $this->poeditDb->loadTranslation($textId, $locale['locale_code']);
						if ($translation['translation']) {
							continue;
						}
					}
					$this->poeditDb->saveTranslation($textId, $locale['locale_code'], $this->translations[$text]['translation'], $this->translations[$text]['fuzzy']);
				}
			}
		}
		if (!$this->config->getOption('useGettext')) {
			return;
		}
		$domain = $this->config->getOption('siteGettextDomain');
		$locales = $localeHandler->getLocales();
		foreach($locales as $locale) {
			$this->translations = array();
			if (file_exists(PROJECT_PATH.'locale/'.$locale['locale_code'].'/LC_MESSAGES/'.$domain.'.po')) {
				$lines = file(PROJECT_PATH.'locale/'.$locale['locale_code'].'/LC_MESSAGES/'.$domain.'.po');
				$this->processPoLines($lines);
			}
			foreach($this->texts['site'] as $textId=>$text) {
				if (isset($this->translations[$text])) {
					if (!$overwrite) {
						$translation = $this->poeditDb->loadTranslation($textId, $locale['locale_code']);
						if ($translation['translation']) {
							continue;
						}
					}
					$this->poeditDb->saveTranslation($textId, $locale['locale_code'], $this->translations[$text]['translation'], $this->translations[$text]['fuzzy']);
				}
			}
		}
	}

	protected function processPoLines($lines) {
		$text = '';
		$fuzzy = false;
		$matches = array();
		foreach($lines as $line) {
			if (preg_match('/^\s*msgstr\s*"(.+)"\s*$/i', $line, $matches)) {
				if ($text) {
					$this->translations[$text] = array('translation'=>$matches[1], 'fuzzy'=>$fuzzy);
				}
				$text = '';
				$fuzzy = false;
			} else if(preg_match('/^\s*msgid\s*"(.+)"\s*$/i', $line, $matches)) {
				$text = $matches[1];
			} else if(preg_match('/^\s*#,\s*fuzzy/i', $line)) {
				$fuzzy = true;
			}
		}
	}

	/**
	 * @see sys_admin_AdminModule::doLoad()
	 *
	 * @return array;
	 */
	protected function doLoad() {}

	/**
	 * @see sys_admin_AdminModule::doSave()
	 *
	 * @return string
	 */
	protected function doSave() {
		$this->loadTexts();
		$this->scanDb();
		$this->scanFiles();
		if (count($this->newTexts['admin'])) {
			foreach($this->newTexts['admin'] as $text) {
				$this->poeditDb->addText($text, module_db_interface_Poeditor::TARGET_ADMIN);
			}
		}
		if (count($this->newTexts['site'])) {
			foreach($this->newTexts['site'] as $text) {
				$this->poeditDb->addText($text, module_db_interface_Poeditor::TARGET_SITE);
			}
		}
		if ($this->data['delete_obsolete']) {
			$obsoleteIds = array();
			foreach($this->texts['admin'] as $textId=>$text) {
				if (!in_array($text, $this->foundTexts['admin'])) {
					$obsoleteIds[] = $textId;
				}
			}
			foreach($this->texts['site'] as $textId=>$text) {
				if (!in_array($text, $this->foundTexts['site'])) {
					$obsoleteIds[] = $textId;
				}
			}
			$this->poeditDb->deleteObsoleteTexts($obsoleteIds);
		}
		if ($this->data['import_translations']) {
			$this->importPoTranslations($this->data['imports_overwrite']);
		}
	}

}
?>