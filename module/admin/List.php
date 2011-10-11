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
 * List admin module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_List extends sys_admin_AdminModule {

	protected $listModule;

	protected $listId;

	protected $virtualId;

	protected $listSuffix;

	protected $listLimit = 20;

	protected $listPage = 0;

	protected $orderDirection = '+';

	protected $subfolders = false;

	protected $filters = array();

	protected $orderBy;

	protected $isExport = false;

	protected $heads = array();

	protected $table = array();

	protected $folder;

    /**
     * @see sys_admin_AdminModule::init()
     *
     */
    protected function init ()
    {
        parent::init();
        if ($this->config->getOption('defaultListLimit')) {
            $this->listLimit = $this->config->getOption('defaultListLimit');
        }
    }


	/**
	 * @see sys_admin_AdminModule::buildForm()
	 *
	 */
	protected function buildForm() {
		$this->disableFormTag();

		$list = new sys_admin_control_ListTable();

		$this->processList($list);

		$list->setFilters($this->filters);

		$list->setPageLimit($this->listLimit);
		$list->setPageNumber($this->listPage);

		$list->setSubfolderSearchEnabled($this->subfolders);

		$list->setOrderDirection($this->orderDirection);
		$list->setOrderBy($this->orderBy);

		$this->addControl($list, 'listTable');
	}

	protected function processList(sys_admin_control_ListTable $list) {
		switch($this->listModule) {
			case 'Folder':
				$this->processFolderList($list);
				break;
			case 'Asset':
				$this->processAsset($list);
				break;
			default:
				throw new sys_exception_AdminException (_('Invalid list module'), sys_exception_AdminException::ERR_INVALID_LIST_MODULE);
				break;
		}
	}

	protected function processAsset(sys_admin_control_ListTable $list) {
		$assetHandler = getPersistClass('Asset');
		$this->folder = $assetHandler->getFolderInfoById($this->virtualId);
		$list->setTitle($this->folder['name']);
		$this->heads = array('name'=>_('Name'), 'description'=>_('Description'),'info'=>_('Information'), 'path1'=>_('File path'));
		$this->filters['asset_type_id'] = $this->listId;
		$list->setResultCount($assetHandler->getListResultCount($this->localeId, $this->folder['id'], $this->subfolders, $this->filters));

		$assets = $assetHandler->listItems($this->localeId, $this->folder['id'], $this->listLimit, ($this->listPage*$this->listLimit), $this->subfolders, $this->filters, $this->orderBy, $this->orderDirection);
		$this->table = array();
		foreach($assets as $asset) {
			if (is_object($asset)) {
				$asset = $asset->toArray();
			}
			$row=array('attrs'=>array('id'=>$asset['id'], 'adminForm'=>'asset_Editor/'.$this->listId), 'data'=>array());
			foreach($this->heads as $col=>$title) {
				$row['data'][$col] = $asset[$col];
			}
			$this->table[]=$row;
		}
		$list->setAllowedTypes(array('asset_Editor/'.$this->listId.'/'.$this->folder['id']=>'Asset'));
		$list->setHeaders($this->heads);
		$list->setTableData($this->table);

	}

	protected function getEmptyFolderInfoArray($id) {
		return array(
			'id' => $id,
			'name' => '',
			'FolderType' => array(
				'no_new_doc' => 1,
				'non_doc'=>1,
				'ObjectTypes' => array()
			),
		);
	}

	protected function getVirtualFolderInfo($folderInfo) {
		$className = 'module_virtual_'.$folderInfo['virtual_handler'];
		if (!class_exists($className)) {
			return $this->getEmptyFolderInfoArray($this->listId);
		}
		$handler = new $className();
		if (!$handler instanceof sys_VirtualHandler) {
			return $this->getEmptyFolderInfoArray($this->listId);
		}
		$data = $handler->getAdminFolderInfo($this->listId, $this->virtualId);
		if (!count($data)) {
			return $this->getEmptyFolderInfoArray($this->listId);
		}
		$data['virtual_subfolders'] = 1;
		$data['virtual_handler'] = $folderInfo['virtual_handler'];
		$data['virtual_handler_object'] = $handler;
		return $data;
	}

	protected function processFolderList(sys_admin_control_ListTable $list) {
		$folderHandler = getPersistClass('Folder');
		$this->folder = $folderHandler->getFullFolderInfoById($this->listId);
		if ($this->folder['virtual_subfolders']) {
			$this->folder = $this->getVirtualFolderInfo($this->folder);
		}
		$list->setTitle($this->folder['name']);
		$objectHandler = getPersistClass('ObjectType');
		$this->heads = array();
		$this->table = array();
		if (!$this->folder['FolderType']['no_new_doc']) {
			if ($this->folder['FolderType']['non_doc']) {
				$prefix = '';
			} else {
				$prefix = 'Doc/';
			}
			$postfix ='/'.$this->folder['id'];
			$types = array();
			if ($this->folder['FolderType']['ObjectTypes']) {
				foreach($this->folder['FolderType']['ObjectTypes'] as $type) {
					if ($type['handler_class']) {
						$handler = getPersistClass($type['handler_class']);
					}
					if(is_object ($handler) && !$prefix && ($handler instanceof module_db_interface_AdminListCustom)) {
						$types[$handler->getCustomAdmin()] = $type['name'];
					} else {
						$types[$prefix.$type['admin_class'].$postfix] = $type['name'];
					}
				}
			}
			$list->setAllowedTypes($types);
		}
		if ($this->folder['FolderType']['non_doc']) {
			if (!count($this->folder['FolderType']['ObjectTypes'])) {
				$list->setHeaders($this->heads);
				$list->setTableData($this->table);
				return;
			}
			$typeData = $this->folder['FolderType']['ObjectTypes'][0];
			$headField = 'in_list';
			if ($this->isExport) {
				$headField = 'in_export';
			}
			foreach($typeData['Columns'] as $column) {
				if (!$column[$headField]) {
					continue;
				}
				$this->heads[$column['name']] = $column['title'];
			}
			// FIXME HACK
			if ($this->folder['docpath'] != 'admin') {
				$this->heads['id']=_('ID');
			}
			if (!isset($this->heads[$this->orderBy])) {
				$this->orderBy = null;
				$this->orderDirection = null;
			}
			if (is_null($this->orderBy)) {
				$this->orderBy = $typeData['Columns'][0]['name'];
			}
			if ($this->folder['virtual_subfolders']) {
				$handler = $this->folder['virtual_handler_object'];
			} else {
				try {
					$handler = getPersistClass($typeData['handler_class']);
				} catch (sys_exception_ModuleException $e) {
					throw new sys_exception_AdminException('Persistance module not found: '.$typeData['handler_class'], sys_exception_AdminException::ERR_PERSISTANCE_MODULE_NOT_FOUND);
				}
			}
			if (!($handler instanceof module_db_interface_AdminList)) {
				throw new sys_exception_AdminException('Persistance module doesn\'t implement listing: '.$typeData['handler_class'], sys_exception_AdminException::ERR_PERSISTANCE_MODULE_NOT_LIST);
			}
			$list->setResultCount($handler->getListResultCount($this->localeId, $this->folder['id'], $this->subfolders, $this->filters));
			$items = $handler->listItems($this->localeId, $this->folder['id'], $this->listLimit, ($this->listPage*$this->listLimit), $this->subfolders, $this->filters, $this->orderBy, $this->orderDirection);
			// FIXME HACK NELKUL
			if ($this->folder['docpath'] == 'admin') {
				foreach($items as $key=>$item) {
					$items[$key]['id'] = 0;
				}
			}
			foreach($items as $item) {
				if (is_object($item)) {
					$item = $item->toArray();
				}
				$row=array('attrs'=>array('id'=>$item['id']), 'data'=>array(), 'docpath'=>$this->folder['docpath']);
				if (isset($item['class'])) {
					$row['attrs']['adminForm'] = $item['class'];
				} else {
					if ($handler instanceof module_db_interface_AdminListCustom ) {
						$row['attrs']['adminForm'] = $handler->getCustomAdmin();
					} else {
						$row['attrs']['adminForm'] = $this->folder['FolderType']['ObjectTypes'][0]['admin_class'];
					}
				}
				foreach($this->heads as $col=>$title) {
					if (is_bool($item[$col])) {
						$item[$col] = (int)$item[$col];
					}
					$row['data'][$col] = $item[$col];
				}
				$this->table[]=$row;
			}
		} else {
			$tmp = $objectHandler->getObjectTypeAdmins();
			$objectTypeAdminMap = array();
			foreach($tmp as $admin) {
				$objectTypeAdminMap[$admin['id']] = $admin['admin_class'];
			}
			$objectTypeData = $objectHandler->getObjectTypeByShortName('document');
			$columns = $objectHandler->getListColumnsByObjectTypeId($objectTypeData['id']);
			$headField = 'in_list';
			if ($this->isExport) {
				$headField = 'in_export';
			}
			foreach($columns as $column) {
				if (!$column[$headField]) {
					continue;
				}
				$this->heads[$column['name']] = $column['title'];
			}
			if (!isset($this->heads[$this->orderBy])) {
				$this->orderBy = null;
				$this->orderDirection = null;
			}
			if (is_null($this->orderBy)) {
				$this->orderBy = $columns[0]['name'];
			}
			$docHandler = getPersistClass('Doc');
			$list->setResultCount($docHandler->getListResultCount($this->localeId, $this->folder['id'], $this->subfolders, $this->filters));
			$docs = $docHandler->listItems($this->localeId, $this->folder['id'], $this->listLimit, ($this->listPage*$this->listLimit), $this->subfolders, $this->filters, $this->orderBy, $this->orderDirection);
			foreach($docs as $doc) {
				if (is_object($doc)) {
					$doc = $doc->toArray();
				}
				$row=array('attrs'=>array('id'=>$doc['id'], 'docpath'=>$this->folder['docpath'], 'adminForm'=>'Doc/'.$objectTypeAdminMap[$doc['ref_object_type_id']]), 'data'=>array());
				foreach($this->heads as $col=>$title) {
					$row['data'][$col] = $doc[$col];
				}
				$this->table[]=$row;
			}
		}
		$list->setHeaders($this->heads);
		$list->setTableData($this->table);
	}

	/**
	 * @see sys_admin_AdminModule::parseXml()
	 *
	 * @param SimpleXMLElement $xml
	 */
	public function parseXml(SimpleXMLElement $xml) {
		$this->manager->runEvent ('preParse');
		if ('list' != (string)$xml->adminData->id) {
			throw new sys_exception_AdminException (_('Invalid mode for this module'), sys_exception_AdminException::ERR_MODULE_MODE_NOT_VALID);
		}
		$listData = (string)$xml->adminData->name;
		if (!preg_match('@^([a-zA-Z0-9]+)/([0-9]+)(/(.*))?$@', (string)$xml->adminData->name, $listData)) {
			throw new sys_exception_AdminException (_('Invalid list name'.(string)$xml->adminData->name), sys_exception_AdminException::ERR_INVALID_LIST_NAME);
		}
		$this->listModule = $listData[1];
		$this->listId = $listData[2];
        $this->subModule = array($this->listModule, $this->listId);
		if (count($listData) > 4) {
			$this->virtualId = $listData[4];
		}
		if (!empty($listData[4])) {
			$this->listSuffix = $listData[4];
		}
		if (isset($xml->options)) {
			$this->parseOptions($xml->options);
		}
		$this->options['mode']='List';
		$this->mode = sys_admin_AdminModule::MODE_FORM;
		$this->options['listName'] = (string)$xml->adminData->name;
		$this->xml = $xml;
		$this->manager->runEvent ('postParse');
	}

	protected function parseOptions($xml) {
		foreach ($xml->option as $option) {
			switch($option['name']) {
				case 'filters':
					$this->filters = array();
					foreach($option as $filter) {
						$this->filters[(string)$filter['name']] = (string)$filter;
					}
					break;
				case 'orderBy':
					$this->orderBy = (string)$option;
					break;
				case 'orderDirection':
					$this->orderDirection = (string)$option;
					break;
				case 'subfolderSearchEnabled':
					if ('1' == $option) {
						$this->subfolders = true;
					} else {
						$this->subfolders = false;
					}
					break;
				case 'pageLimit':
					if (!$this->isExport) {
						$this->listLimit = (int)$option;
					}
					break;
				case 'pageNumber':
					if (!$this->isExport) {
						$this->listPage = (int)$option;
					}
					break;
				case 'isExport':
					if (!(int)$option) {
						break;
					}
					$this->listLimit = null;
					$this->listPage = 0;
					$this->isExport = true;
					break;
			}
		}
	}

	/**
	 * @see sys_admin_AdminModule::getXml()
	 *
	 * @return string
	 */
	protected function getXml() {
		if ($this->isExport) {
			$this->doExport();
			exit();
		}
		return parent::getXml();
	}

	protected function doExport() {
		header('Content-type: application/vnd.ms-excell');
		header('Content-Disposition: attachment; filename="'.urlencode(convertStringToDocname($this->folder['name']).'_export_'.date('Y_m_d').'.xls').'"');
		$smarty = sys_LibFactory::getSmarty();
		$smarty->assign('folder', $this->folder);
		$smarty->assign('heads', $this->heads);
		$smarty->assign('table', $this->table);
		$smarty->display('yapep:admin/module/List/export.tpl');
	}
}
?>