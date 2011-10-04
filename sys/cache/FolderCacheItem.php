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
 * Folder cache item
 *
 * @package	YAPEP
 * @subpackage	Cache
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_cache_FolderCacheItem {

	/**
	 *
	 * @var integer
	 */
	public $id;

	/**
	 *
	 * @var integer
	 */
	public $parent_id;

	/**
	 *
	 * @var string
	 */
	public $locale_id;

	/**
	 *
	 * @var string
	 */
	public $docpath;

	/**
	 *
	 * @var string
	 */
	public $short;

	/**
	 * @var integer
	 */
	public $folder_order;

	/**
	 *
	 * @var string
	 */
	public $name;

	/**
	 *
	 * @var boolean
	 */
	public $virtual_subfolders;

	/**
	 *
	 * @var string
	 */
	public $virtual_handler='';

	/**
	 * Folder pages
	 *
	 * @var array
	 */
	private $folderPage=null;

	/**
	 * Document pages
	 *
	 * @var array
	 */
	private $docPage=null;

	/**
	 * Stores the subfolder objects
	 *
	 * @var sys_cache_FolderCacheItem[]
	 */
	private $subfolders=null;

	/**
	 * @var integer
	 */
	private $visible;

	/**
	 * @var integer
	 */
	private $sitemap;

	/**
	 * @var string
	 */
	private $sitemap_desc;

	/**
	 * @var integer
	 */
	private $sitemap_link;

	/**
	 * @var array
	 */
	private $Images=array();

	/**
	 *
	 * @param array $folderInfo
	 */
	public function __construct($folderInfo=null) {
		if (is_null($folderInfo)) {
			$this->docpath='/';
			$this->short='';
			$this->name=_('Main page');
			$this->virtual_subfolders=false;
		} else {
			$this->id=$folderInfo['id'];
			$this->parent_id=$folderInfo['parent_id'];
			$this->locale_id=$folderInfo['locale_id'];
			$this->docpath=$folderInfo['docpath'];
			$this->short=$folderInfo['short'];
			$this->name=$folderInfo['name'];
			$this->folder_order=$folderInfo['folder_order'];
			$this->virtual_subfolders=(bool)$folderInfo['virtual_subfolders'];
			$this->virtual_handler=$folderInfo['virtual_handler'];
			$this->visible=$folderInfo['visible'];
			$this->sitemap=$folderInfo['sitemap'];
			$this->sitemap_link=$folderInfo['sitemap_link'];
			$this->sitemap_desc=$folderInfo['sitemap_desc'];
			if (isset($folderInfo['Pages'])) {
				$pageData = $this->convertPages($folderInfo['Pages']);
				$this->folderPage=$pageData['folderPage'];
				$this->docPage=$pageData['docPage'];
			} else {
				$this->folderPage=$folderInfo['folderPage'];
				$this->docPage=$folderInfo['docPage'];
			}
			$this->Images=$this->convertImages($folderInfo['Images']);
		}
	}

	private function convertPages($pages) {
		$pageData=array('folderPage'=>array(), 'docPage'=>array());
		foreach($pages as $val) {
			switch($val['relation_type']) {
				case module_db_interface_Page::TYPE_FOLDER:
				case 'folder':
					$pageData['folderPage'][$val['theme_id']]=$val['page_id'];
					break;
				case module_db_interface_Page::TYPE_FOLDER_DOC:
				case 'folder_doc':
					$pageData['docPage'][$val['theme_id']]=$val['page_id'];
					break;
			}
		}
		return $pageData;
	}

	private function convertImages($images) {
		$imgArray=array();
		if (count($images)) {
			foreach($images as $image) {
				if (is_object($image)) {
					$image=$image->toArray();
				}
				$imgArray[$image['type']]=$image;
			}
		}
		return $imgArray;
	}

	/**
	 * Sets the subfolder array
	 *
	 * @param sys_cache_FolderCacheItem[] $subfolders
	 */
	private function setSubfolders($subfolders) {
		$this->subfolders=$subfolders;
	}

	/**
	 * Sets the pages for this folder
	 *
	 * @param array $folderPage
	 * @param array $docPage
	 */
	private function setPages($folderPage, $docPage) {
		$this->folderPage=$folderPage;
		$this->docPage=$docPage;
	}

	/**
	 * Returns all subfolders
	 *
	 * @return sys_cacheFolderCacheItem[]
	 */
	public function getSubfolders() {
		return $this->subfolders();
	}

	/**
	 * Loads all subfolders of the current folder
	 *
	 */
	public function loadFolderData() {
		$pageDb = getPersistClass('Page');
		if ('/' == $this->docpath) {
			$tmp = $pageDb->getMainPageIdsForLocale($this->locale_id);
			$this->folderPage=array();
			foreach($tmp as $val) {
				$this->folderPage[$val['theme_id']]=$val['page_id'];
			}
		}
		if (!$this->docPage) {
			$tmp = $pageDb->getDefaultDocPageIdsForLocale($this->locale_id);
			$this->docPage=array();
			foreach($tmp as $val) {
				$this->docPage[$val['theme_id']]=$val['page_id'];
			}
		}
		if (is_null($this->subfolders)) {
			$db = getPersistClass('Folder');
			if (is_null($this->id)) {
				$folders=$db->getFoldersByLocaleId($this->locale_id);
			} else {
				$folders=$db->getFoldersByParentFolderId($this->id);
			}
			$this->subfolders=array();
			foreach($folders as $val) {
			    if (is_null($val['locale_id'])) {
			        $val['locale_id'] = $this->locale_id;
			    }
				$tmp=new sys_cache_FolderCacheItem($val);
				$tmp->loadFolderData();
				$this->subfolders[$tmp->short]=$tmp;
			}
		}
	}

	/**
	 *
	 * @param array $state
	 * @return sys_cache_FolderCacheItem
	 */
	public static function __set_state($state) {
		$item=new self($state);
		$item->setSubfolders($state['subfolders']);
		$item->setPages($state['folderPage'], $state['docPage']);
		return $item;
	}

	/**
	 * Returns a folder by it's document path
	 *
	 * @param array $pathArr
	 * @return array
	 */
	public function getFolder(&$pathArr, $withSubfolders=sys_cache_FolderCacheManager::WITHOUT_SUBFOLDERS) {
		if (!count($pathArr)) {
			return $this->getFolderData($withSubfolders);
		}
		$nextDir=array_shift($pathArr);
		if ($this->virtual_subfolders) {
			$folderdata = $this->getFolderData($withSubfolders);
			$folderdata['virtual_path'] = $nextDir;
			if (count($pathArr)) {
			    $folderdata['virtual_path'] .= '/'.implode('/', $pathArr);
			}
			return $folderdata;
		}
		if (isset($this->subfolders[$nextDir])) {
			return $this->subfolders[$nextDir]->getFolder($pathArr, $withSubfolders);
		}
		if (!count($pathArr)) {
			$docid = sys_DocFactory::docExists($this->locale_id, $this->docpath, $nextDir);
			if ($docid) {
				array_unshift($pathArr, $nextDir);
				$folderdata = $this->getFolderData();
				$folderdata['doc_id'] = $docid;
				return $folderdata;
			}
		}
		throw new sys_exception_SiteException('404 Not found', 404);
	}

	/**
	 * Returns the folder information as an array
	 *
	 * @return array
	 */
	public function getFolderData($withSubfolders=sys_cache_FolderCacheManager::WITHOUT_SUBFOLDER) {
		$folderData=array(
			'id'=>$this->id,
			'parent_id'=>$this->parent_id,
			'locale_id'=>$this->locale_id,
			'docpath'=>$this->docpath,
			'short'=>$this->short,
			'folder_order'=>$this->folder_order,
			'name'=>$this->name,
			'virtual_subfolders'=>$this->virtual_subfolders,
			'virtual_handler'=>$this->virtual_handler,
			'folderPage' => $this->folderPage,
			'docPage' => $this->docPage,
			'visible' => $this->visible,
			'sitemap' => $this->sitemap,
			'sitemap_link' => $this->sitemap_link,
			'sitemap_desc' => $this->sitemap_desc,
			'Images' => $this->Images,
		);
		if ($withSubfolders != sys_cache_FolderCacheManager::WITHOUT_SUBFOLDER) {
			$subfolders=array();
			if (is_array($this->subfolders)) {
				if ($withSubfolders == sys_cache_FolderCacheManager::WITH_SUBFOLDER_RECURSIVE) {
					$recurse=sys_cache_FolderCacheManager::WITH_SUBFOLDER_RECURSIVE;
				} else {
					$recurse=sys_cache_FolderCacheManager::WITHOUT_SUBFOLDER;
				}
				foreach($this->subfolders as $key=>$val) {
					$subfolders[]=$val->getFolderData($recurse);
				}
			}
			$folderData['subfolders']=$subfolders;
		}
		return $folderData;
	}
}
?>