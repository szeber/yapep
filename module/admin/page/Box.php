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
 * Box editor module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_page_Box extends sys_admin_AdminModule {

	const TYPE_DOC = 1;
	const TYPE_FOLDER = 2;

	const TYPE_LIST = 1;
	const TYPE_SINGLE = 2;

	const STATUS_ORDER_INHERITED = 1;
	const STATUS_ACTIVE_INHERITED = 2;


	/**
	 * Stores the parameter type information
	 *
	 * @var array
	 */
	private $typeData;

	/**
	 * Module db handler module
	 *
	 * @var module_db_interface_Module
	 */
	private $moduleHandler;

	/**
	 * Box db handler module
	 *
	 * @var module_db_interface_Box
	 */
	private $boxHandler;

	/**
	 * Page db handler module
	 *
	 * @var module_db_interface_Page
	 */
	private $pageHandler;

	/**
	 * @var array
	 */
	private $boxData;

	public function init() {
		$this->moduleHandler = getPersistClass('Module');
		$this->boxHandler = getPersistClass('Box');
		$this->pageHandler = getPersistClass('Page');
	}

	public function buildForm() {

		$this->setDbHandler($this->boxHandler);

		if (! $this->subModule[2]) {
//			$this->setReloadOnSave();
		} elseif (! $this->checkBoxValid()) {
			throw new sys_exception_AdminException(_('Box not found'), sys_exception_AdminException::ERR_SUBMODULE_NOT_FOUND);
		}

		$mainPanel = new sys_admin_control_Panel();
		$mainPanel->setDock(sys_admin_interface_Dockable::DOCK_CLIENT);
		$this->addControl($mainPanel, 'boxData');

		$control = new sys_admin_control_SelectInput();
		$control->addOptions($this->moduleHandler->getModuleList());
		$control->setDisplayOn(sys_admin_interface_Input::DISPLAY_ADD);
		$control->setLabel(_('Module type'));
		$mainPanel->addControl($control, 'moduleId');

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Module name'));
		$mainPanel->addControl($control, 'boxName');

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Module order'));
		$control->enableBoxMode();
		$mainPanel->addControl($control, 'boxOrder');

		$control = new sys_admin_control_CheckBox();
		$control->setLabel(_('Active?'));
		$control->enableBoxMode();
		$mainPanel->addControl($control, 'boxActive');

		$paramPanel = new sys_admin_control_Panel();
		$this->addControl($paramPanel, 'paramData');

		if ($this->boxData['Module']['Params']) {
			foreach ($this->boxData['Module']['Params'] as $param) {
				switch($param['param_type_id']) {
					case module_db_interface_ModuleParam::TYPE_CHECK:
						$control = new sys_admin_control_CheckBox();
						break;
					case module_db_interface_ModuleParam::TYPE_SELECT:
						$control = new sys_admin_control_SelectInput();
						$control->addOptions($this->getParamValues($param['id']));
						break;
					case module_db_interface_ModuleParam::TYPE_FOLDER_LIST:
						$control = $this->makeBrowserList(self::TYPE_FOLDER, self::TYPE_LIST);
						break;
					case module_db_interface_ModuleParam::TYPE_DOC_LIST:
						$control = $this->makeBrowserList(self::TYPE_DOC, self::TYPE_LIST);
						break;
					case module_db_interface_ModuleParam::TYPE_FOLDER:
						$control = $this->makeBrowserList(self::TYPE_FOLDER, self::TYPE_SINGLE);
						break;
					case module_db_interface_ModuleParam::TYPE_DOC:
						$control = $this->makeBrowserList(self::TYPE_DOC, self::TYPE_SINGLE);
						break;
					case module_db_interface_ModuleParam::TYPE_LONG_TEXT:
						$control = new sys_admin_control_TextArea();
						break;
					case module_db_interface_ModuleParam::TYPE_TEXT:
					default:
						$control = new sys_admin_control_TextInput();
						break;
				}
				$control->enableBoxMode();
				$control->setLabel($param['name']);
				$control->setDescription($param['description']);
				$control->setDefaultValue($param['default_value']);
				$paramPanel->addControl($control, $param['name']);
			}
		}
	}

	/**
	 * Makes a new browser list control
	 *
	 * @param integer $type
	 * @param boolean $single
	 * @return sys_admin_control_BrowserList
	 */
	private function makeBrowserList($type, $list) {
		$control = new sys_admin_control_BrowserList();
		if ($type == self::TYPE_FOLDER) {
			$control->setDataForm('folder_Prop');
			$control->setBrowserForm('folder_Browser');
			$control->setDisplayTemplate('{$docpath}');
			$control->setDataField('docpath');
		} else {
			$control->setDataForm('Doc/*');
			$control->setBrowserForm('object_Browser');
			$control->setDisplayTemplate('{$docPath}/{$docname}');
			$control->setDataField('id');
		}
		if ($list == self::TYPE_SINGLE) {
			$control->setSingleItem();
		}
		return $control;
	}

	public function checkBoxValid() {
		$data = $this->boxHandler->getBox((int)$this->subModule[2]);
		if (is_object($data)) {
			$data = $data->toArray();
		}
		if (!$data || !count($data)) {
			return false;
		}
		$tmp = $this->moduleHandler->getModule($data['module_id']);
		if (is_object($tmp)) {
			$tmp = $tmp->toArray();
		}
		$data['Module'] = $tmp;
		$this->boxData = $data;
		return true;
	}

	protected function getParamValues($paramId) {
		$data =  $this->moduleHandler->getModuleParamValuesForParam($paramId);
		$items = array();
		foreach($data as $item) {
			$items[$item['value']] = $item['description'];
		}
		return $items;
	}

	/**
	 * @see sys_admin_AdminModule::processLoadData()
	 *
	 */
	protected function processLoadData() {
		$inherited = false;
		if ($this->mode == sys_admin_AdminModule::MODE_EDIT) {
			if ($this->data['parent_id']) {
				$inherited = true;
			}
		}
		$newdata = array(
			'moduleId' => $this->data['module_id'],
			'boxName'=>$this->data['name'],
			'boxOrder'=>$this->data['box_order'],
			'boxActive'=>$this->data['active'],
		);
		$control = $this->panel->getInput('boxOrder');
		$control->setBoxValue(array('value'=>$this->data['box_order'],'isInherited'=>$inherited, 'useInherited'=>(bool)($this->data['status'] & self::STATUS_ORDER_INHERITED)), true);
		$control = $this->panel->getInput('boxActive');
		$control->setBoxValue(array('value'=>$this->data['active'],'isInherited'=>$inherited, 'useInherited'=>(bool)($this->data['status'] & self::STATUS_ACTIVE_INHERITED)), true);
		if ($this->data['ModuleParams']) {
			foreach($this->data['ModuleParams'] as $param) {
				$boxData[$param['name']] = array('value'=>$param['default_value'], 'isVariable'=>$param['default_is_variable'], 'allowVariable'=>$param['allow_variable']);
				$newdata[$param['name']] = $param['default_value'];
				$control = $this->panel->getInput($param['name']);
				if (is_object($control)) {
					$control->setBoxValue($boxData[$param['name']], true);
				}
			}
			if ($this->data['Params']) {
				foreach($this->data['Params'] as $param) {
					$newdata[$param['ModuleParam']['name']] = $param['value'];
					$boxData[$param['ModuleParam']['name']]['value'] = $param['value'];
					$boxData[$param['ModuleParam']['name']]['isInherited'] = (bool)$param['parent_id'];
					$boxData[$param['ModuleParam']['name']]['useInherited'] = $param['inherited'];
					$boxData[$param['ModuleParam']['name']]['isVariable'] = $param['is_var'];
					$control = $this->panel->getInput($param['ModuleParam']['name']);
					if (is_object($control)) {
						$control->setBoxValue($boxData[$param['ModuleParam']['name']], true);
					}
				}
			}
		}
		$this->data = $newdata;
	}

	protected function postSave() {
		$cache = new sys_cache_PageCacheManager();
		$cache->recreateCache();
	}

	protected function postDelete() {
		$this->postSave();
	}

	/**
	 * @see sys_admin_AdminModule::processSaveData()
	 *
	 */
	protected function processSaveData() {
		$data2 = array();
		$data2['name'] = $this->data['boxName'];
		$data2['box_order'] = $this->data['boxOrder'];
		$data2['active'] = $this->data['boxActive'];
		$data2['status'] = 0;
		if ($this->dataAttrs['boxOrder']['useInherited']) {
			$data2['status'] = $data2['status'] | self::STATUS_ORDER_INHERITED;
		}
		if ($this->dataAttrs['boxActive']['useInherited']) {
			$data2['status'] = $data2['status'] | self::STATUS_ACTIVE_INHERITED;
		}
		if ($this->mode == sys_admin_AdminModule::MODE_ADD) {
			$data2['module_id'] = $this->data['moduleId'];
			$data2['boxplace_id'] = (int)$this->subModule[0];
			$data2['page_id'] = (int)$this->subModule[1];
		}
		unset($this->data['moduleId'], $this->data['boxOrder'], $this->data['boxName'], $this->data['boxActive']);
		$params = $this->data;
		foreach($params as $key=>$param) {
			if (is_array($param)) {
				$param = implode(',',$param);
			}
			$params[$key] = $this->dataAttrs[$key];
			$params[$key]['value'] = $param;
		}
		$this->data = $data2;
		if (count($params) && $this->mode == sys_admin_AdminModule::MODE_EDIT) {
			$this->boxHandler->updateBoxParams($this->id, $params);
		}
	}
}
?>