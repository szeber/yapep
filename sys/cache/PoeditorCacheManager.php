<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	Cache
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 11068 $
 */

/**
 * We will load several PO files in memory, so raise the memory limit
 */
ini_set('memory_limit', '100M');

 /**
 * Poeditor cache manager
 *
 * @package	YAPEP
 * @subpackage	Cache
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 11068 $
 */
class sys_cache_PoeditorCacheManager implements sys_cache_CacheManager {

	/**
	 * @see sys_cache_CacheManager::cacheEnabled()
	 *
	 * @return boolean
	 */
	public function cacheEnabled() {
		return true;
	}

	/**
	 * @see sys_cache_CacheManager::clearCache()
	 *
	 */
	public function clearCache() {
		// Do nothing
	}

	/**
	 * @see sys_cache_CacheManager::recreateCache()
	 *
	 */
	public function recreateCache() {
		$config = sys_ApplicationConfiguration::getInstance();
		$poeditDb = getPersistClass('Poeditor');
		$localeDb = getPersistClass('LangLocale');
		$locales = $localeDb->getAdminLocales();
		foreach($locales as $locale) {
			if (!is_dir(CACHE_DIR . 'locale/'.$locale['locale_code'].'/LC_MESSAGES')) {
				mkdir(CACHE_DIR . 'locale/'.$locale['locale_code'].'/LC_MESSAGES', 0755, true);
			}
			$poFile = fopen(CACHE_DIR . 'locale/'.$locale['locale_code'].'/LC_MESSAGES/YapepAdmin.po', 'wb');
			$translations = $poeditDb->getTranslations(module_db_interface_Poeditor::TARGET_ADMIN, $locale['locale_code']);
			fwrite($poFile, "msgid \"\"\nmsgstr \"\"\n\"MIME-Version: 1.0\\n\"\n");
			fwrite($poFile, "\"Content-Type: text/plain; charset=UTF-8\\n\"\n\"Content-Transfer-Encoding: 8bit\\n\"\n\n");
			foreach($translations as $translation) {
				if ($translation['Text']['text'] == '') {
					continue;
				}
				if ($translation['fuzzy']) {
					fwrite($poFile, "#, fuzzy\n");
				}
				fwrite($poFile, 'msgid "'.addcslashes($translation['Text']['text'], '\\"')."\"\n");
				fwrite($poFile, 'msgstr "'.str_replace("\n", '\n', str_replace("\r", '\r', addcslashes($translation['translation'], '\\"')))."\"\n\n");
			}
			exec('msgfmt -o '
				. escapeshellarg(CACHE_DIR . 'locale/'.$locale['locale_code'].'/LC_MESSAGES/YapepAdmin.mo') . ' '
				. escapeshellarg(CACHE_DIR . 'locale/'.$locale['locale_code'].'/LC_MESSAGES/YapepAdmin.po'));
		}
		if (!$config->getOption('useGettext')) {
			return;
		}
		$domain = $config->getOption('siteGettextDomain');
		$locales = $localeDb->getLocales();
		foreach($locales as $locale) {
			if (!is_dir(CACHE_DIR . 'locale/'.$locale['locale_code'].'/LC_MESSAGES')) {
				mkdir(CACHE_DIR . 'locale/'.$locale['locale_code'].'/LC_MESSAGES', 0755, true);
			}
			$poFile = fopen(CACHE_DIR . 'locale/'.$locale['locale_code'].'/LC_MESSAGES/'.$domain.'.po', 'wb');
			$translations = $poeditDb->getTranslations(module_db_interface_Poeditor::TARGET_SITE, $locale['locale_code']);
			fwrite($poFile, "msgid \"\"\nmsgstr \"\"\n\"MIME-Version: 1.0\\n\"\n");
			fwrite($poFile, "\"Content-Type: text/plain; charset=UTF-8\\n\"\n\"Content-Transfer-Encoding: 8bit\\n\"\n\n");
			foreach($translations as $translation) {
				if ($translation['Text']['text'] == '') {
					continue;
				}
				if ($translation['fuzzy']) {
					fwrite($poFile, "#, fuzzy\n");
				}
				fwrite($poFile, 'msgid "'.addcslashes($translation['Text']['text'], '\\"')."\"\n");
				fwrite($poFile, 'msgstr "'.str_replace("\n", '\n', str_replace("\r", '\r',addcslashes($translation['translation'], '\\"')))."\"\n\n");
			}
			exec('msgfmt -o '
				. escapeshellarg(CACHE_DIR . 'locale/'.$locale['locale_code'].'/LC_MESSAGES/'.$domain.'.mo') . ' '
				. escapeshellarg(CACHE_DIR . 'locale/'.$locale['locale_code'].'/LC_MESSAGES/'.$domain.'.po'));
		}


	}

}
?>