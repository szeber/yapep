<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	Virtual
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

 /**
 * Object lister virtual handler
 *
 * @package	YAPEP
 * @subpackage	Virtual
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_virtual_ObjectLister implements sys_VirtualHandler {

	/**
	 * @var module_db_interface_ObjectType
	 */
	private $typeHandler;

	/**
	 * @var module_db_interface_Object
	 */
	private $objectHandler;

	public function __construct() {
		$this->makeHandlers();
	}

	private function makeHandlers() {
		if (is_null($this->typeHandler)) {
			$this->typeHandler = getPersistClass('ObjectType');
			$this->objectHandler = getPersistClass('Object');
		}
	}

	/**
	 * @see sys_VirtualHandler::getAdminFolderTree()
	 *
	 * @param integer $rootFolder
	 * @return array
	 */
	public function getAdminFolderTree($rootFolder, $imageRoot) {
		$ignoreTypes = array('folder', 'admin', 'adminuser', 'admin_group', 'document', 'asset', 'poeditor');
		$data = $this->typeHandler->getUsedObjectTypes($ignoreTypes, false);
		$types = array();
		foreach($data as $val) {
			$types[] = array(
				'name'=>_($val['name']),
				'link'=>'Folder/'.$rootFolder.'/'.$val['id'],
				'icon'=>$imageRoot.$val['icon'],
				'iconAct'=>$imageRoot.$val['icon_act'],
				'subTree'=>array()
			);
		}
		return $types;
	}

	/**
	 * @see sys_VirtualHandler::getAdminFolderInfo()
	 *
	 * @param integer $rootFolder
	 * @param string $path
	 * @return array
	 */
	public function getAdminFolderInfo($rootFolder, $path) {
		$data = $this->typeHandler->getObjectTypesByIds(array((int)$path));
		if (!count($data)) {
			return array();
		}
		if (is_object($data)) {
			$data = $data->toArray();
		}
		$data = reset($data);
		$columnData = $this->typeHandler->getListColumnsByObjectTypeId($data['id']);
		if (is_object($columnData)) {
			$columnData = $columnData->toArray();
		}
		$nameColumn = array(array(
			'name'=>'name',
			'title'=>_('Name'),
			'column_number'=>0,
			'in_list'=>1,
			'in_export'=>1
		));
		$data['Columns'] = array_merge($nameColumn,  $columnData);
		return array(
			'id'=>$rootFolder.'/'.$path,
			'name'=>$data['name'],
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
	 * Returns the object type id from the folder ID
	 *
	 * @param string $path
	 * @return integer
	 */
	private function getTypeIdFromPath($path) {
		if (!preg_match('/\/([0-9]+)$/', $path, $regexp)) {
			return 0;
		}
		return $regexp[1];
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
		$objTypeId = $this->getTypeIdFromPath($folder);
		if (!$objTypeId) {
			return 0;
		}
        $objTypeDb = getPersistClass('ObjectType');
        $objType = $objTypeDb->loadItem($objTypeId);
        if ($objType['id']) {
            if (interface_exists('module_db_interface_'.$objType['handler_class'])) {
                $handler = getPersistClass($objType['handler_class']);
                if ($handler instanceof module_db_interface_AdminList) {
                    return $handler->getListResultCount($localeId, null, $subFolders, $filter);
                }
            }
        }
		return $this->objectHandler->getListCountForObjectType($objTypeId, $filter);
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
		$objTypeId = $this->getTypeIdFromPath($folder);
		if (!$objTypeId) {
			return array();
		}
        $objTypeDb = getPersistClass('ObjectType');
        $objType = $objTypeDb->loadItem($objTypeId);
        if ($objType['id']) {
            if (interface_exists('module_db_interface_'.$objType['handler_class'])) {
                $handler = getPersistClass($objType['handler_class']);
                if ($handler instanceof module_db_interface_AdminList) {
                    $data = $handler->listItems($localeId, null, $limit, $offset, $subFolders, $filter, $orderBy, $orderDir);
                    if (is_object($data)) {
                        $data = $data->toArray();
                    }
                    return $data;
                }
            }
        }
		$data = $this->objectHandler->getListForObjectType($objTypeId, $limit, $offset, $filter, $orderBy, $orderDir);
		if (is_object($data)) {
			$data = $data->toArray();
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