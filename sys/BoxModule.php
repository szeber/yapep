<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.

 * @version	$Rev$
 */

/**
 * Box module base
 *
 * @package	YAPEP
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
abstract class sys_BoxModule {
 	const CACHE_ENABLED = 1;
 	const CACHE_VETO_PAGE = 2;
 	const CACHE_DISABLED = 3;

 	/**
 	 * Module information
 	 *
 	 * @var array
 	 */
 	protected $moduleInfo;

 	/**
 	 * Module arguments
 	 *
 	 * @var array
 	 */
 	protected $argArr;

 	/**
 	 * Smarty
 	 *
 	 * @var Smarty
 	 */
 	protected $smarty;

 	/**
 	 * Cache ID
 	 *
 	 * @var string
 	 */
 	protected $cacheId;

 	/**
 	 * Configuration data
 	 *
 	 * @var sys_IApplicationConfiguration
 	 */
 	protected $config;

 	/**
 	 * Additional variables required for generation of the cache ID
 	 *
 	 * @var array
 	 */
 	private $extraCacheVars = array();

 	/**
 	 * The page manager instance running the module
 	 *
 	 * @var sys_PageManager
 	 */
 	protected $pageManager;

 	/**
 	 * Constructor
 	 *
 	 * @param sys_IApplicationConfiguration $config
 	 * @param array $argArr
 	 * @param array $moduleInfo
 	 * @param sys_PageManager $manager
 	 */
 	final public function __construct(sys_IApplicationConfiguration $config, $argArr, $moduleInfo, sys_PageManager $manager) {
 		$this->argArr=$argArr;
 		$this->moduleInfo=$moduleInfo;
 		$this->moduleInfo['default_template']=$this->moduleInfo['name'].'.tpl';
 		$this->config = $config;
 		$this->init();
 		$this->prepareSmarty();
 		$this->pageManager = $manager;
 	}

 	/**
 	 * Prepares Smarty
 	 *
 	 */
 	protected function prepareSmarty() {
 		$this->smarty = sys_LibFactory::getSmarty();
 		if ($this->moduleInfo['cache_type']!=self::CACHE_ENABLED) {
 			$this->smarty->caching=0;
 		} else {
 			$this->smarty->cache_lifetime=$this->moduleInfo['cache_expire'];
 		}
 		$this->smarty->assign('docSuffix', $this->config->getOption('docSuffix'));
 		$this->smarty->assign('argArr', $this->argArr);
 		$this->smarty->assign('moduleInfo', $this->moduleInfo);
 		$this->smarty->assign_by_ref('MODULE', $this);
 	}

 	/**
 	 * Returns the directory for the specified template
 	 *
 	 * @param string $template
 	 * @return string
 	 */
 	protected function getSmartyBase($template) {
 		return 'box/'.$template;
 	}

 	/**
 	 * Returns the rendered output from Smarty
 	 *
 	 * @param string $template
 	 * @return string
 	 */
 	protected function smartyFetch($template=null) {
 		if (is_null($template)) {
 			$template=$this->moduleInfo['default_template'];
 		}
 		return $this->smarty->fetch($this->getSmartyBase($template), $this->generateCacheId());
 	}

 	/**
 	 * Generates the cache identified
 	 *
 	 * @return string
 	 */
 	protected function generateCacheId() {
 		if (!is_null($this->cacheId)) {
 			return $this->cacheId;
 		}
 		$args=$this->argArr;
 		unset($args['request_path']);
 		unset($args['doc_id']);
 		$args = array_merge($args, $this->extraCacheVars);
 		$this->cacheId = md5(implode(',',$args));
 		return $this->cacheId;
 	}

 	/**
 	 * Executes the module
 	 *
 	 * @return string
 	 */
 	public function execute() {
 		if ($this->smarty->is_cached($this->getSmartyBase($this->moduleInfo['default_template']), $this->generateCacheId())) {
 			sys_Debugger::addModuleDebugInfo($this->moduleInfo, $this->argArr, array(), true);
 			return $this->smartyFetch();
 		}
 		$result=$this->main();
 		sys_Debugger::addModuleDebugInfo($this->moduleInfo, $this->argArr, $this->smarty->_tpl_vars, false);
 		return $result;
 	}

 	/**
 	 * Adds an additional string to the cache ID
 	 *
 	 * @param string $value
 	 */
 	protected function addExtraCacheVar($value) {
		$this->extraCacheVars[] = $value;
 	}

 	/**
 	 * Hook for initialization. Called by the constructor.
 	 *
 	 */
 	protected function init() {}

 	/**
 	 * Executes the module
 	 *
 	 * @return string
 	 */
 	abstract protected function main();
}
?>