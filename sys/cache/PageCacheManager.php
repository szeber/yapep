<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	Cache
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

 /**
 * Page cache manager
 *
 * @package	YAPEP
 * @subpackage	Cache
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_cache_PageCacheManager extends sys_cache_BaseCacheManager {

	/**
	 * Cache data
	 *
	 * @var array
	 */
	private static $cacheData = null;

	/**
	 * Temporary storage used during cache creation
	 *
	 * @var array
	 */
	private $tmpData;

	/**
	 * Boxplaces
	 *
	 * @var array
	 */
	private $boxplaces;

	/**
	 * Database
	 *
	 * @var module_db_generic_Page
	 */
	protected $db;

	/**
	 * @see sys_cache_CacheManager::cacheEnabled()
	 *
	 * @return boolean
	 */
	public function cacheEnabled() {
		return true;
	}

	/**
	 * Converts query arrays to cleaned arrays for processing
	 *
	 * @param array $pageData
	 * @param array $result
	 */
	private function convertPageData($pageData, &$result) {
		foreach($pageData as $val) {
			$result[$val['id']]=array('id'=>$val['id'], 'parent_id'=>$val['parent_id'], 'template_id'=>$val['template_id'], 'theme_id'=>$val['theme_id'], 'locale_id'=>$val['locale_id'], 'page_type'=>$val['page_type'], 'template_file'=>$val['Template']['file'], 'processed'=>0, 'boxplaces'=>array());
		}
	}

	/**
	 * Processes page information, queries boxplaces, boxes, params
	 *
	 * @param array $pageData
	 */
	private function processPageData(&$pageData) {
		// Don't process if already processed
		if ($pageData['processed']) {
			return;
		}
		// If derived page, copy parent's information
		if ($pageData['page_type']==sys_PageManager::TYPE_DERIVED_PAGE) {
			if (!$this->tmpData[$pageData['parent_id']]['processed']) {
				$this->processPageData($this->tmpData[$pageData['parent_id']]);
			}
			$pageData['boxplaces']=$this->tmpData[$pageData['parent_id']]['boxplaces'];
		}

		// Get information for this page
		if (is_array(($this->boxplaces[$pageData['template_id']]))) {
			foreach ($this->boxplaces[$pageData['template_id']] as $boxplaceId=>$boxplace) {
				$boxes=$this->db->getBoxesByPageId($pageData['id'],$boxplaceId);
				// boxes
				foreach($boxes as $box) {
					$tmpBox=array('id'=>$box['id'], 'parent_id'=>$box['parent_id'], 'boxplace_id'=>$box['boxplace_id'], 'module_id'=>$box['module_id'], 'name'=>$box['name'], 'box_order'=>$box['box_order'], 'active'=>$box['active'], 'status'=>$box['status'], 'params'=>array());
					$params=$this->db->getBoxParamByBoxId($box['id']);
					// box params
					foreach($params as $param) {
						$tmpBox['params'][$param['ModuleParam']['name']]=array('id'=>$param['id'], 'module_param_id'=>$param['module_param_id'], 'value'=>$param['value'], 'is_var'=>$param['is_var'], 'inherited'=>$param['inherited']);
					}
					$pageData['boxplaces'][$boxplace][$tmpBox['id']]=$tmpBox;
					if ($tmpBox['parent_id']) {
						unset($pageData['boxplaces'][$boxplace][$tmpBox['parent_id']]);
					}
				}
			}
		}
		// Set page to processed
		$pageData['processed']=1;
	}

	/**
	 * Cleans up the temporary data, prepares it for saving
	 *
	 */
	private function cleanupPageData() {
		self::$cacheData=array();
		foreach($this->tmpData as $val) {
			unset($val['processed']);
			self::$cacheData[$val['id']]=$val;
		}
	}

	/**
	 * Queries the database for the boxplace information
	 *
	 */
	private function queryBoxplaces() {
		$boxplaces=$this->db->getBoxplaces();
		$this->boxplaces=array();
		foreach($boxplaces as $val) {
			$this->boxplaces[$val['template_id']][$val['id']]=$val['boxplace'];
		}
	}

	/**
	 * @see sys_cache_FileCacheManager::doRecreateCache()
	 *
	 */
	protected function doRecreateCache() {
		$this->queryBoxplaces();
		$pages=$this->db->getPages();
		$this->tmpData=array();
		$this->convertPageData($pages, $this->tmpData);
		foreach($this->tmpData as &$val) {
			$this->processPageData($val);
		}
		$this->cleanupPageData();
		unset($this->tmpData);
		unset($this->boxplaces);
		$this->backend->set($this->cacheKey, self::$cacheData);
	}

	/**
	 * @see sys_cache_FileCacheManager::loadCacheData()
	 *
	 */
	protected function loadCacheData() {
		if (is_null(self::$cacheData) && $this->cacheEnabled()) {
			self::$cacheData=$this->backend->get($this->cacheKey);
			if (false === self::$cacheData && $this->backend->isVolatile()) {
			    // the backend is volatile, we should try recreating the cache
			    $this->recreateCache();
			}
		}
	}

	/**
	 * Returns a database access object
	 *
	 * @return module_db_UserAuth
	 */
	protected function getDb() {
		$this->db = getPersistClass('Page');
	}

	/**
	 * @see sys_cache_FileCacheManager::setCacheFile()
	 *
	 */
	protected function setCacheKey() {
		$this->cacheKey = 'pageCache';
	}

	/**
	 * Returns the data for a page
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public function getPage($id) {
		return self::$cacheData[$id];
	}
}
?>