<?php

/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Page manager
 *
 * @package	YAPEP
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_PageManager
{

    const TYPE_FOLDER = 1;

    const TYPE_PAGE = 2;

    const TYPE_DERIVED_PAGE = 3;

    const NORMAL = 1;

    const ERROR = 0;

    /**
     * Operating mode
     *
     * If set to sys_PageHandler::ERROR and preparePage() catches an error, enters emergency mode,
     * and prints out a generic 500 Internal server error page to stop from
     * entering an infinite loop and posibly DOSing the server.
     *
     * @var integer
     */
    public $pageMode = self::NORMAL;

    /**
     * @var sys_Debugger
     */
    private $debugger;

    /**
     * The requested URL
     *
     * @var string
     */
    private $url;

    /**
     * The URL handler for the requested URL
     *
     * @var sys_UrlHandler
     */
    private $urlHandler;

    /**
     * Config data
     *
     * @var sys_IApplicationConfiguration
     */
    private $config;

    /**
     * Database access
     *
     * @var sys_db_Database
     */
    private $db;

    /**
     * Folder information
     *
     * @var array
     */
    private $folderInfo;

    /**
     * Theme ID
     *
     * @var integer
     * @todo implement theming
     */
    private $themeId;

    /**
     * Page data from cache
     *
     * @var array
     */
    private $pageData;

    /**
     * Stores whether caching is enabled or disabled
     *
     * @var boolean
     */
    private $caching = false;

    /**
     * Extra title string passed to the template
     *
     * @var string
     */
    private $pageTitle = '';

    /**
     * Extra tags passed to the template
     *
     * Meta tags, etc.
     *
     * @var array
     */
    private $pageHeadTags = array();

    /**
     * Extra meta tags passed to the template
     *
     * @var array
     */
    private $pageMetas = array();

    /**
     * Extra smarty variables assigned to the template
     *
     * @var array
     */
    private $pageVars = array();

    /**
     * Returns the requested URL
     *
     * @return string
     */
    static public function getUrl() {
        $result = '';
        if ($_SERVER['SCRIPT_URL'] && $_SERVER['REDIRECT_URL']) {
            $result = $_SERVER['SCRIPT_URL'];
        } elseif ($_SERVER['REDIRECT_URL']) {
            $result = $_SERVER['REDIRECT_URL'];
        } else {
            $url = array();
            preg_match('/^[^?]+/',
                $_SERVER['REQUEST_URI'],
                $url);
            $result = $url[0];
        }
        return $result;
    }

    /**
     * Constructor
     *
     * @param string $url Optional URL for the page
     */
    public function __construct ($url = false, $config = null)
    {
        if ($url) {
            $this->url = $url;
        } else {
            $this->url = self::getUrl();
        }
        if (is_null($config)) {
            $this->config = sys_ApplicationConfiguration::getInstance();
        } else {
            $this->config = $config;
        }
        $this->db = sys_LibFactory::getDbConnection('site');
        $this->themeId = sys_ThemeManager::getTheme();
        if ($this->config->getOption('pageCache') && CACHING) {
            $this->caching = true;
        }
        if (isset($_SESSION['LoggedInAdminData']) && $_SESSION['LoggedInAdminData']['UserId'] >
             0) {
                define('ADMIN_PREVIEW', true);
        }
        new sys_cache_SysConfigCacheManager();
        $this->debugger = sys_Debugger::getInstance();
    }

    /**
     * Disables caching for this page
     *
     */
    public function disableCache ()
    {
        $this->caching = false;
    }

    /**
     * Displays the page
     *
     * Calls the preaparePage() and renderPage() methods
     *
     */
    public function displayPage ()
    {
        $this->preparePage();
        $this->renderPage(true);
    }

    /**
     * Prepares the page for rendering
     *
     */
    public function preparePage ()
    {
        try {
            $this->urlHandler = new sys_UrlHandler($this->url);
            $this->folderInfo = $this->urlHandler->getFolderInfo();
            if ($this->urlHandler->isVirtual) {
                $this->prepareVirtualPage();
            } else
                if ($this->urlHandler->isDir) {
                    $this->prepareFolderPage();
                } else {
                    $this->prepareDocPage();
                }
            if (! is_array($this->pageData)) {
                throw new sys_exception_SiteException(
                    'Page information not loaded!',
                    500);
            }
            $this->initSmarty();
        } catch (sys_exception_SiteException $e) {
            $this->processSiteException($e);
        }
    }

    private function processSiteException(sys_exception_SiteException $e)
    {
        if (sys_exception_SiteException::ERR_HANDLED == $e->getCode()) {
            throw $e;
        }
        // don't log all 404 errors
        if (404 != $e->getCode()) {
            // DEBUG level, because modules can change the page generation flow with exceptions
            sys_Log::log(sys_Log::LEVEL_DEBUG, 'Page', __METHOD__, 'site exception caught. URL' . $this->url, $e);
        }
        $this->debugger->setError(
            'Error occured during page preparation. Error code: ' .
                 $e->getCode());
        $this->debugger->getDebugInfo();
        if ($this->pageMode == self::ERROR) {
            $this->emergencyMode();
            return;
        }
        switch ($e->getCode()) {
            case 500:
                $this->emergencyMode();
                break;
            case 403:
                $this->handleForbidden();
                break;
            case 404:
            default:
                $this->handleNotFound($e->getCode());
                break;
        }
    }

    /**
     * Initializes Smarty
     *
     */
    private function initSmarty ()
    {
        $smarty = sys_LibFactory::getSmartyProto();
        $smarty->assign('LANG', $this->urlHandler->language);
        $smarty->assign('LOCALE', $this->urlHandler->locale);
        $smarty->assign('MAIN_URL', $this->urlHandler->rootUrl);
        $smarty->assign('SYS_PATH', SYS_PATH);
        $smarty->assign('PROJECT_PATH', PROJECT_PATH);
        $smarty->assign('MAIN_URL_LANG',
            $this->urlHandler->rootUrl . $this->urlHandler->language . '/');
    }

    /**
     * Prepares a virtual handler managed page
     *
     */
    private function prepareVirtualPage ()
    {
        $handlerName = 'module_virtual_' . $this->folderInfo['virtual_handler'];
        if (! class_exists($handlerName)) {
            throw new sys_exception_SiteException(
                "Virtual handler '$handlerName' not found",
                500);
        }
        $handler = new $handlerName();
        /* @var $handler sys_VirtualHandler */
        if (isset($this->folderInfo['virtual_path'])) {
            $this->folderInfo['requestPath'] = $this->folderInfo['docpath'] .
                 '/' . $this->folderInfo['virtual_path'];
        }
        $pageId = $handler->getPageIdForPath($this->folderInfo['docpath'],
            $this->folderInfo['virtual_path']);
        if (! $pageId) {
            throw new sys_exception_SiteException(
                'No document page set for folder!', 500);
        }
        $pageCache = new sys_cache_PageCacheManager();
        $this->pageData = $pageCache->getPage($pageId);
    }

    /**
     * Prepares a document page
     *
     */
    private function prepareDocPage ()
    {
        $pageCache = new sys_cache_PageCacheManager();
        if ($this->folderInfo['doc_id']) {
            $pageDb = getPersistClass('Page');
            $pageId = $pageDb->getPageIdForObject(
                $this->folderInfo['doc_id'],
                $this->themeId,
                module_db_interface_Page::TYPE_DOC);
            if ($pageId) {
                $this->pageData = $pageCache->getPage(
                    $pageId);
                return;
            }
        }
        if (! $this->folderInfo['docPage']) {
            throw new sys_exception_SiteException(
                'No document page set for folder!', 500);
        }
        $this->pageData = $pageCache->getPage(
            $this->folderInfo['docPage'][$this->themeId['id']]);
    }

    /**
     * Prepares a folder index page
     *
     */
    private function prepareFolderPage ()
    {
        if (! $this->folderInfo['folderPage'][$this->themeId['id']]) {
            $docId = sys_DocFactory::docExists(
                $this->urlHandler->locale_id,
                $this->urlHandler->docPath, 'index');
            if (! $docId) {
                throw new sys_exception_SiteException(
                    '404 Not found', 404);
            }
            $this->folderInfo['doc_id'] = $docId;
            $this->urlHandler->isDir = false;
            $this->prepareDocPage();
            return;
        }
        $pageCache = new sys_cache_PageCacheManager();
        $this->pageData = $pageCache->getPage(
            $this->folderInfo['folderPage'][$this->themeId['id']]);
    }

    /**
     * Renders and returns or displays the page
     *
     * @param boolean $display
     */
    public function renderPage ($display = true, $smarty = null)
    {
        if (is_null($smarty)) {
            $smarty = sys_LibFactory::getSmarty();
        }
        if ($this->caching) {
            $smarty->cache_lifetime = $this->config->getOption(
                'pageCacheTime');
            if ($smarty->is_cached(
                'page/' . $this->pageData['template_file'],
                $this->makeCacheId())) {
                return $smarty->fetch(
                    'page/' . $this->pageData['template_file'],
                    $this->makeCacheId(),
                    null, $display);
            }
        }
        if (! isset($this->folderInfo['requestPath'])) {
            $this->folderInfo['requestPath'] = $this->folderInfo['docpath'];
        }
        $argArr = array('lang' => $this->urlHandler->language ,
        'locale' => $this->urlHandler->locale ,
        'locale_id' => $this->urlHandler->locale_id ,
        'request_path' => $this->urlHandler->docPath ,
        'doc_id' => $this->folderInfo['doc_id'] ,
        'real_request_path' => $this->folderInfo['requestPath']);
        if (isset($this->folderInfo['virtual_path'])) {
            $argArr['virtual_path'] = $this->folderInfo['virtual_path'];
        } else {
            $argArr['virtual_path'] = '';
        }
        if ($this->urlHandler->docName) {
            $argArr['request_path'] .= '/' . $this->urlHandler->docName;
        }
        $this->debugger->addSystemQueries();
        $boxplaces = array();
        $moduleCache = new sys_cache_ModuleCacheManager();
        foreach ($this->pageData['boxplaces'] as $boxplace => $modules) {
            $boxplaces[$boxplace] = '';
            foreach ($modules as $moduleData) {
                if (! $moduleData['active']) {
                    continue;
                }
                $moduleInfo = $moduleCache->getModule(
                    $moduleData['module_id']);
                if ($moduleInfo['cache_type'] == sys_BoxModule::CACHE_VETO_PAGE) {
                    $this->caching = false;
                }
                foreach ($moduleInfo['params'] as $paramName => $param) {
                    if (! isset(
                        $moduleData['params'][$paramName])) {
                        $moduleData['params'][$paramName] = array(
                        'is_var' => $param['default_is_variable'] ,
                        'param' => $param['default_value']);
                    }
                }
                unset($moduleInfo['params']);
                $moduleInfo['description'] = $moduleData['name'];
                $args = $this->convertParams(
                    $moduleData['params'],
                    $argArr);
                $moduleClass = 'module_box_' . $moduleInfo['name'];
                try {
                    if (! class_exists(
                        $moduleClass)) {
                        throw new sys_exception_SiteException(
                            'Missing module: ' .
                                 $moduleClass,
                                500);
                    }
                    $module = new $moduleClass(
                        $this->config,
                        $args,
                        $moduleInfo,
                        $this);
                    if (! ($module instanceof sys_BoxModule)) {
                        throw new sys_exception_SiteException(
                            'Missing module: ' .
                                 $moduleClass,
                                500);
                    }
                    $data = $module->execute();
                    $boxplaces[$boxplace] .= $data;
                } catch (sys_exception_ModuleException $e) {
                    $this->debugger->setError(
                        'Module error: ' .
                             $e->getMessage());
                    $args['_errorMessage'] = $e->getMessage();
                    $trace = $e->getTrace();
                    $args['_throwFile'] = $trace[0]['file'];
                    $args['_throwLine'] = $trace[0]['line'];
                    $module = new sys_ErrorneousBoxModule(
                        $this->config,
                        $args,
                        $moduleInfo,
                        $this);
                    $boxplaces[$boxplace] .= $module->execute();
                } catch (sys_exception_SiteException $e) {
                    $this->processSiteException($e);
                }
            }
        }
        $smarty->caching = $this->caching;
        foreach ($this->pageVars as $name => $content) {
            $smarty->assign($name, $content);
        }
        $smarty->assign('pageTitle', $this->pageTitle);
        foreach ($this->pageMetas as $name => $content) {
            $this->pageHeadTags[] = '<meta name="' . htmlspecialchars(
                $name) . '" content="' . htmlspecialchars(
                $content) . '" />';
        }
        $smarty->assign('pageMetas', $this->pageMetas);
        $smarty->assign('pageTags', $this->pageHeadTags);
        $smarty->assign('boxplaces', $boxplaces);
        $this->debugger->getDebugInfo();
        return $smarty->fetch('page/' . $this->pageData['template_file'],
            $this->makeCacheId(), null, $display);
    }

    /**
     * Returns a cache id generated from the url and request params
     *
     * @return unknown
     */
    protected function makeCacheId ()
    {
        $string = $this->url . '?';
        foreach (array_merge($_GET, $_POST) as $key => $val) {
            if (is_array($val)) {
                continue;
            }
            $string .= $key . '=' . substr($val, 0, 200) . '&';
        }
        return md5($string);
    }

    /**
     * Converts the parameter array, and substitutes the variables with their values
     *
     * @param array $params
     * @param array $argArr
     * @return array
     */
    private function convertParams ($params, $argArr)
    {
        $converted = array();
        foreach ($params as $parName => $param) {
            if ($param['is_var']) {
                switch ($param['value']) {
                    case 'full_docpath':
                    case 'docpath':
                        $converted[$parName] = $argArr['request_path'];
                        break;
                    default:
                        $converted[$parName] = $param['value'];
                        break;
                }
            } else {
                $converted[$parName] = $param['value'];
            }
        }
        $converted += $argArr;
        return $converted;
    }

    /**
     * Finds the appropriate 404 not found error handler and executes it
     *
     */
    private function handleNotFound ($errorCode = 404)
    {
        $handler = $this->config->getOption('notFoundHandler');
        if (! $handler) {
            $handler = $this->config->getOption('errorHandler');
            if (! $handler) {
                $handler = 'sys_ErrorHandler';
            }
        }
        $handler = new $handler();
        $handler->handleError($errorCode);
    }

    /**
     * Finds the appropriate 400 Forbidden error handler and executes it
     *
     */
    private function handleForbidden ()
    {
        $handler = $this->config->getOption('forbiddenHandler');
        if (! $handler) {
            $handler = $this->config->getOption('errorHandler');
            if (! $handler) {
                $handler = 'sys_ErrorHandler';
            }
        }
        $handler = new $handler();
        $handler->handleError(403);
    }

    /**
     * Sends a 500 Internal server error header and error message, and stops program execution
     *
     */
    private function emergencyMode ()
    {
        header('HTTP/1.1 500 Internal server error');
        $data = array(
            'trace' => debug_backtrace(false),
        );
        sys_Log::log(sys_Log::LEVEL_ERROR, 'Page', __METHOD__, 'Entered emergency mode during page generation', $data);
        $this->debugger->getDebugInfo();
        echo '<h1>500 Internal server error</h1>';
        throw new sys_exception_SiteException('Emergency mode entered', sys_exception_SiteException::ERR_HANDLED);
    }

    /**
     * Sets the title tag's content. If it's already set only overwrites it if $overwrite is true
     *
     * For this to work the page template must contain the $pageTitle tag.
     * Returns true, if the tag has been set, false otherwise
     * (already set, and $overwrite is false or not specified)
     *
     * @param string $title
     * @param boolean $overwrite
     * @return boolean
     */
    public function setTitle ($title, $overwrite = false)
    {
        if ($this->pageTitle && ! $overwrite) {
            return false;
        }
        $this->pageTitle = $title;
        return true;
    }

    /**
     * Adds a tag to the page head.
     *
     * For this to work the page template must contain the $pageTags tag.
     *
     * @param string $tag
     */
    public function addHeadTag ($tag)
    {
        $this->pageHeadTags[] = $tag;
    }

    /**
     * Sets the meta tag specified by $name to $content. If it's already set only overwrites it if $overwrite is true
     *
     * For this to work the page template must contain the $pageTags tag.
     * Returns true, if the tag has been set, false otherwise
     * (already set, and $overwrite is false or not specified)
     *
     * @param string $name
     * @param string $content
     * @param boolean $overwrite
     * @return boolean
     */
    public function setMeta ($name, $content, $overwrite = false)
    {
        if (isset($this->pageMetas[$name]) && ! $overwrite) {
            return false;
        }
        $this->pageMetas[$name] = $content;
        return true;
    }

    public function setPageVar ($name, $value)
    {
        $this->pageVars[$name] = $value;
    }
}
?>