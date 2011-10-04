<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Attila Danch <da@scp.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Doctrine page database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Attila Danch <da@scp.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_Doctrine_Box extends module_db_DoctrineDbModule implements module_db_interface_Box  {

	/**
	 * @see module_db_interface_Admin::deleteItem()
	 *
	 * @param integer $itemId
	 */
	public function deleteItem($itemId) {
		return $this->basicDelete('CmsPageBoxData', $itemId);
	}

	/**
	 * @see module_db_interface_Admin::insertItem()
	 *
	 * @param array $itemData
	 * @return string
	 */
	public function insertItem($itemData) {
		$result = $this->basicInsert('CmsPageBoxData', $itemData);
		if (!is_numeric($result)) {
			return $result;
		}
		$derivedPages = $this->conn->query('FROM CmsPageData WHERE parent_id = ? AND page_type = '.sys_PageManager::TYPE_DERIVED_PAGE, array((int)$itemData['page_id']));
		if (!count($derivedPages)) {
			return $result;
		}
		foreach($derivedPages as $page) {
			$insertData = $itemData;
			$insertData['page_id'] = $page['id'];
			$insertData['parent_id'] = $result;
			$insertData['status'] = module_admin_page_Box::STATUS_ACTIVE_INHERITED | module_admin_page_Box::STATUS_ORDER_INHERITED;
			$this->insertItem($insertData);
		}
		return $result;
	}

	/**
	 * @see module_db_interface_Admin::loadItem()
	 *
	 * @param integer $itemId
	 * @return array
	 */
	public function loadItem($itemId) {
		return $this->conn->queryOne('FROM CmsPageBoxData d LEFT JOIN d.Params p LEFT JOIN d.ModuleParams mp WHERE d.id = ?', array($itemId));
	}

	/**
	 * @see module_db_interface_Admin::updateItem()
	 *
	 * @param integer $itemId
	 * @param array $itemData
	 * @return string
	 */
	public function updateItem($itemId, $itemData) {
		$result = $this->basicUpdate('CmsPageBoxData', $itemId, $itemData);
		$this->updateDerivedPages($itemId);
		return $result;
	}

	/**
	 * Recursively updates a pages derived pages information
	 *
	 * @param integer $itemId
	 */
	protected function updateDerivedPages($itemId) {
		$parent = $this->conn->queryOne('FROM CmsPageBoxData WHERE id=?', array((int)$itemId));
		$derived = $this->conn->query('FROM CmsPageBoxData WHERE parent_id=?', array($parent['id']));
		if (!count($derived)) {
			return;
		}
		foreach($derived as $page) {
			if ($page['status'] & module_admin_page_Box::STATUS_ACTIVE_INHERITED) {
				$page['active'] = $parent['active'];
			}
			if ($page['status'] & module_admin_page_Box::STATUS_ORDER_INHERITED) {
				$page['box_order'] = $parent['box_order'];
			}
		}
		$derived->save();
		foreach($derived as $page) {
			$this->updateDerivedPages($page['id']);
		}
	}

	/**
	 * Returns a box's data
	 *
	 * @param integer $boxId
	 * @return array
	 */
	public function getBox($boxId) {
		return $this->conn->queryOne('FROM CmsPageBoxData b LEFT JOIN b.Params p LEFT JOIN p.ModuleParam WHERE b.id = ?', array($boxId));
	}

	/**
	 * @see module_db_interface_Box::updateBoxParams()
	 *
	 * @param integer $boxId
	 * @param array $params
	 * @param boolean $onlyInherited
	 */
	public function updateBoxParams($boxId, $params, $onlyInherited = false) {
		$boxData = $this->conn->queryOne('FROM CmsPageBoxData b INNER JOIN b.Module m LEFT JOIN b.Params p LEFT JOIN b.ModuleParams mp LEFT JOIN p.ModuleParam mp2 WHERE b.id=?', array((int)$boxId));
		if (is_null($params)) {
			$params = array();
		}
		$moduleParams = array();
		if ($boxData['parent_id']) {
			$inherited = true;
		}
		foreach($boxData['ModuleParams'] as $param) {
			$moduleParams[$param['name']] = array('id'=>$param['id'],'is_var'=>$param['default_is_variable'], 'value'=>$param['default_value'], 'inherited'=>$inherited, 'allow_variable'=>$param['allow_variable']);
		}
		foreach($params as $name=>$param) {
			if (!isset($moduleParams[$name])) {
				continue;
			}
			$moduleParams[$name]['value'] = $param['value'];
			$moduleParams[$name]['is_var'] = $moduleParams[$name]['allow_variable'] && $param['isVariable'];
			$moduleParams[$name]['inherited'] = $inherited && $param['useInherited'];
		}
		$savedParams = array();
		foreach($boxData['Params'] as $param) {
			if (!isset($moduleParams[$param['ModuleParam']['name']])) {
				$param->delete();
				continue;
			}
			if ($onlyInherited && !$param['inherited']) {
				if (!$params['parent_id'] && $inherited) {
					$parentId = $this->conn->queryOne('SELECT id FROM CmsPageBoxParamData WHERE module_param_id=? AND page_box_id = ?', array($param['module_param_id'], $boxData['parent_id']));
					if (!$parentId || !count($parentId)) {
						$this->updateBoxParams($boxData['parent_id'], array(), true);
						$parentId = $this->conn->queryOne('SELECT id FROM CmsPageBoxParamData WHERE module_param_id=? AND page_box_id = ?', array($param['module_param_id'], $boxData['parent_id']));
					}
					$param['parent_id'] = $parentId['id'];
				}
				$savedParams[]=$param['ModuleParam']['name'];
				continue;
			}
			$currentParam = $moduleParams[$param['ModuleParam']['name']];
			$param['is_var'] = $currentParam['is_var'];
			$param['inherited'] = $currentParam['inherited'];
			if ($param['inherited']) {
				if (!$param['parent_id']) {
					$parentId = $this->conn->queryOne('SELECT id FROM CmsPageBoxParamData WHERE module_param_id=? AND page_box_id = ?', array($param['module_param_id'], $boxData['parent_id']));
					if (!$parentId || !count($parentId)) {
						$this->updateBoxParams($boxData['parent_id'], array(), true);
						$parentId = $this->conn->queryOne('SELECT id FROM CmsPageBoxParamData WHERE module_param_id=? AND page_box_id = ?', array($param['module_param_id'], $boxData['parent_id']));
					}
					$param['parent_id'] = $parentId['id'];
				}
				$parentValue = $this->conn->queryOne('SELECT value, is_var FROM CmsPageBoxParamData WHERE id=?', array($param['parent_id']));
				$currentParam['is_var'] = $parentValue['is_var'];
				$currentParam['value'] = $parentValue['value'];
			}
			$param['is_var'] = $currentParam['is_var'];
			$param['value'] = $currentParam['value'];
			$savedParams[]=$param['ModuleParam']['name'];
		}
		$boxData->save();
		if (count($savedParams) < count($moduleParams)) {
			foreach($moduleParams as $name=>$param) {
				if (in_array($name, $savedParams)) {
					continue;
				}
				$newParam = new CmsPageBoxParamData();
				$newParam['module_param_id'] = $param['id'];
				$newParam['page_box_id'] = (int)$boxId;
				$newParam['value'] = $param['value'];
				$newParam['is_var'] = $param['is_var'];
				$newParam['inherited'] = $param['inherited'];
				if ($param['inherited']) {
					$parentData = $this->conn->queryOne('FROM CmsPageBoxParamData WHERE page_box_id = ? AND module_param_id = ?', array($boxData['parent_id'], $param['id']));
					if (!$parentData || !count($parentData)) {
						$this->updateBoxParams($boxData['parent_id'], array(), true);
						$parentData = $this->conn->queryOne('FROM CmsPageBoxParamData WHERE page_box_id = ? AND module_param_id = ?', array($boxData['parent_id'], $param['id']));
					}
					$newParam['parent_id'] = $parentData['id'];
					$newParam['value'] = $parentData['value'];
				}
				$newParam->save();
			}
		}
		$childBoxes = $this->conn->query('SELECT id FROM CmsPageBoxData WHERE parent_id = ?', array((int)$boxId));
		foreach($childBoxes as $child) {
			$this->updateBoxParams($child['id'], array(), true);
		}
	}
}
?>