<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	UtilityModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

 /**
 * Document serving error handler class
 *
 * @package	YAPEP
 * @subpackage	UtilityModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_utility_ErrorDocHandler implements sys_ErrorHandlerInterface {

	/**
	 * @see sys_ErrorHandlerInterface::handleError()
	 *
	 * @param unknown_type $errorCode
	 */
	public function handleError($errorCode) {
		$config = sys_ApplicationConfiguration::getInstance();
		 new sys_cache_SysConfigCacheManager();
		$message = sys_ErrorHandler::getErrorMessageByCode($errorCode);
		header('HTTP/1.1 '.$errorCode.' '.$message);
        $lang = $config->getOption('defaultLanguage');
        $url = sys_PageManager::getUrl();
        $urlArr = explode('/', $url);
        $url = reset($urlArr);
        if (!$url) {
            array_shift($urlArr);
            $url = reset($urlArr);
        }
        try {
            $urlHandler = new sys_UrlHandler('/'.$url, $config);
            if ($urlHandler->language) {
                $lang = $urlHandler->language;
            } else {
                $lang = $urlHandler->locale;
            }
        } catch (sys_exception_SiteException $e) {
            // do nothing
        }
		$ph = new sys_PageManager('/'.$lang.'/'.ERROR_DOC_FOLDER.'/'.$errorCode.$config->getOption('docSuffix') );
		$ph->pageMode = sys_PageManager::ERROR;
		$ph->preparePage ();
		$ph->renderPage ();
		exit();
	}

}
?>