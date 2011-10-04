<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	Virtual
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 11068 $
 */

 /**
 * Object lister virtual handler
 *
 * @package	YAPEP
 * @subpackage	Virtual
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 11068 $
 */
class module_virtual_Poeditor implements sys_VirtualHandler {

	/**
	 * @var module_db_interface_LangLocale
	 */
	private $localeHandler;

	/**
	 * @var module_db_interface_Poeditor
	 */
	private $poeditorHandler;

	/**
	 * @var module_db_interface_ObjectType
	 */
	private $typeHandler;

	public function __construct() {
		$this->makeHandlers();
	}

	private function makeHandlers() {
		if (is_null($this->typeHandler)) {
			$this->localeHandler = getPersistClass('LangLocale');
			$this->poeditorHandler = getPersistClass('Poeditor');
			$this->typeHandler = getPersistClass('ObjectType');
		}
	}

	/**
	 * @see sys_VirtualHandler::getAdminFolderTree()
	 *
	 * @param integer $rootFolder
	 * @return array
	 */
	public function getAdminFolderTree($rootFolder, $imageRoot) {
		$locales = $this->localeHandler->getAdminLocales();
		$adminTree = array(
			'name' => _('Admin'),
			'link' => 'Folder/'.$rootFolder.'/admin',
			'subTree' => array()
		);
		foreach($locales as $locale) {
			$adminTree['subTree'][] = array(
				'name' => _($locale['name']),
				'link' => 'Folder/'.$rootFolder.'/admin/'.$locale['locale_code'],
				'subTree' => array()
			);
		}
		$locales = $this->localeHandler->getLocales();
		$siteTree = array(
			'name' => _('Site'),
			'link' => 'Folder/'.$rootFolder.'/site',
			'subTree' => array()
		);
		foreach($locales as $locale) {
			$siteTree['subTree'][] = array(
				'name' => _($locale['name']),
				'link' => 'Folder/'.$rootFolder.'/site/'.$locale['locale_code'],
				'subTree' => array()
			);
		}
		return array($adminTree, $siteTree);
	}

	/**
	 * @see sys_VirtualHandler::getAdminFolderInfo()
	 *
	 * @param integer $rootFolder
	 * @param string $path
	 * @return array
	 */
	public function getAdminFolderInfo($rootFolder, $path) {
		$pathParts = explode('/', $path);
		if (count($pathParts) != 2) {
			return array();
		}
		switch($path[0]) {
			case 'admin':
				$locale = $this->localeHandler->getAdminLocaleById($pathParts[1]);
				break;
			case 'site':
				$locale = $this->localeHandler->getLocaleByCode($pathParts[1]);
				break;
			default:
				break;
		}
		$data = $this->typeHandler->getObjectTypeByShortName('poeditor');
		$data['admin_class'].= '/'.$pathParts[1];
		$data['Columns'] = array(
			array(
				'name'=>'text',
				'title'=>_('TextID'),
				'column_number'=>0,
				'in_list'=>1,
				'in_export'=>1
			),
			array(
				'name'=>'translation',
				'title'=>_('Translation'),
				'column_number'=>1,
				'in_list'=>1,
				'in_export'=>1
			),
		);
		return array(
			'id'=>$rootFolder.'/'.$path,
			'name'=>$locale['name'],
			'FolderType' => array(
				'no_new_doc'=>0,
				'non_doc'=>1,
				'ObjectTypes' => array($data)
			),
		);
	}

	/**
	 * @see sys_VirtualHandler::getObject()
	 *
	 * @param integer $rootFolder
	 * @param string $path
	 * @return array
	 */
	public function getObject($rootFolder, $path) {
		throw new sys_exception_SiteException('404 Not found', 404);
	}

	/**
	 * Parses a path, and returns the identifiers for the parts
	 *
	 * @param string $path
	 */
	protected function parsePath($path) {
		$pathParts = array();
		preg_match('/(admin|site)\/([^\/]+)\/?$/',$path, $pathParts);
		if (count($pathParts) != 3) {
			return array();
		}
		return array($pathParts[1], $pathParts[2]);
	}

	/**
	 * @see module_db_interface_AdminList::getListResultCount()
	 *
	 * @param integer $folder
	 * @param boolean $subFolders
	 * @param array $filter
	 * @return array
	 */
	public function getListResultCount($localeId, $folder=null, $subFolders=false, $filter=null) {
		$pathParts = $this->parsePath($folder);
		if (count($pathParts) != 2) {
			return 0;
		}
		$target = -1;
		switch($pathParts[0]) {
			case 'admin':
				$target = module_db_interface_Poeditor::TARGET_ADMIN;
				break;
			case 'site':
				$target = module_db_interface_Poeditor::TARGET_SITE;
				break;
			default:
				break;
		}
		$count = $this->poeditorHandler->getListCount($target, $pathParts[1], $filter);
		return $count;
	}

	/**
	 * @see module_db_interface_AdminList::listItems()
	 *
	 * @param integer $folder
	 * @param integer $limit
	 * @param integer $offset
	 * @param boolean $subFolders
	 * @param array $filter
	 * @param string $orderBy
	 * @param string $orderDir
	 * @return array
	 */
	public function listItems($localeId, $folder=null, $limit=null, $offset=null, $subFolders=false, $filter=null, $orderBy=null, $orderDir=null) {
		$pathParts = $this->parsePath($folder);
		if (count($pathParts) != 2) {
			return array();
		}
		switch($pathParts[0]) {
			case 'admin':
				$target = module_db_interface_Poeditor::TARGET_ADMIN;
				break;
			case 'site':
				$target = module_db_interface_Poeditor::TARGET_SITE;
				break;
			default:
				break;
		}
		$data = $this->poeditorHandler->loadList($target, $pathParts[1], $limit, $offset, $filter, $orderBy, $orderDir);
		foreach($data as &$text) {
			$text['id'] = $text['id'].'/'.$pathParts[1];
		}
		return $data;
	}

    /**
     * @see sys_VirtualHandler::getPageIdForPath()
     *
     * @param string $rootFolder
     * @param string $path
     * @return integer
     */
    public function getPageIdForPath ($rootFolder, $path)
    {}

}
?>