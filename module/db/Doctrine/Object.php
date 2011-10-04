<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

 /**
 * Object Doctrine database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_Doctrine_Object extends module_db_DoctrineDbModule implements module_db_interface_Object {

	/**
	 * @see module_db_interface_Object::getListCountForObjectType()
	 *
	 * @param integer $typeId
	 * @param array $filter
	 * @return integer
	 */
	public function getListCountForObjectType($typeId, $filter = null) {
		$typeData = $this->conn->queryOne('FROM ObjectTypeData WHERE id = ?', array((int)$typeId));
		if (!$typeData['persist_class']) {
			return 0;
		}
		$tableName = $typeData['persist_class'];
		$filters = 'object_type_id = ?';
		$filterArr = array((int)$typeId);
		if (is_array($filter) && count($filter)) {
			$rec = new $tableName();
			foreach($filter as $name=>$val) {
				if ($rec->contains($name) && ('' !== $val)) {
					$filters .= ' AND '.$name.' LIKE ?';
					$filterArr[] = $val.'%';
				}
			}
		}
		$count = $this->conn->queryOne ( 'SELECT COUNT(id) as itemCount FROM ' . $tableName . ' WHERE ' . $filters, $filterArr);
		return $count ['itemCount'];
	}

	/**
	 * @see module_db_interface_Object::getListForObjectType()
	 *
	 * @param integer $typeId
	 * @param integer $limit
	 * @param integer $offset
	 * @param array $filter
	 * @param string $orderBy
	 * @param string $orderDir
	 * @return array
	 */
	public function getListForObjectType($typeId, $limit=null, $offset=null, $filter=null, $orderBy=null, $orderDir=null) {
		$typeData = $this->conn->queryOne('FROM ObjectTypeData WHERE id = ?', array((int)$typeId));
		if (!$typeData['persist_class']) {
			return array();
		}
		$tableName =  $typeData['persist_class'] ;
		$filters = 'object_type_id = ?';
		$filterArr = array((int)$typeId);
		$extra = '';
		if (is_array($filter) && count($filter)) {
			$rec = new $tableName();
			foreach($filter as $name=>$val) {
				if ($rec->contains($name) && ('' !== $val)) {
					$filters .= ' AND '.$name.' LIKE ?';
					$filterArr[] = $val.'%';
				}
			}
		}
		if (( int ) $limit) {
			if (!is_null($orderBy)) {
				$obj = new $tableName();
				if (!isset($obj[$orderBy])) {
					$orderBy = 'name';
				}
				$extra .= ' ORDER BY '.$orderBy;
				switch (strtolower($orderDir)) {
					case 'desc':
					case '-':
						$extra .= ' DESC';
						break;
					default:
						$extra .= ' ASC';
						break;
				}
			}
			$extra .= ' LIMIT ' . ( int ) $limit;
			if (( int ) $offset < 0) {
				$offset = 0;
			}
			$extra .= ' OFFSET ' . ( int ) $offset;
		}
		return $this->conn->query ( 'FROM ' . $tableName . ' WHERE ' . $filters . $extra, $filterArr );
	}

	/**
	 * @see module_db_interface_Object::getRelList()
	 *
	 * @param integer $objectId
	 * @param integer $relationType
	 * @return array
	 */
	public function getRelList($objectId, $relationType = null) {
		$extra = '';
		if (!is_null($relationType)) {
			$extra .= ' AND relation_type = '.(int)$relationType;
		}
		$parents = $this->conn->query('FROM ObjectObjectRel r INNER JOIN r.Parent WHERE child_id = ?'.$extra, array((int)$objectId));
		$children = $this->conn->query('FROM ObjectObjectRel r INNER JOIN r.Child WHERE parent_id = ?'.$extra, array((int)$objectId));
		$data = array();
		foreach($parents as $val) {
			$item = sys_db_DoctrineHelper::getFullObject($val['Parent']);
			if ($item instanceof DocData) {
				if ($item['status'] == module_db_interface_Doc::STATUS_INACTIVE || strtotime($item['start_date'])>time() || strtotime($item['end_date'])<time()) {
					continue;
				}
				$item->loadReference('Folder');
			}
			$data[] = $item->toArray();
		}
		foreach($children as $val) {
			$item = sys_db_DoctrineHelper::getFullObject($val['Child']);
			if ($item instanceof DocData) {
				if ($item['status'] == module_db_interface_Doc::STATUS_INACTIVE || strtotime($item['start_date'])>time() || strtotime($item['end_date'])<time()) {
					continue;
				}
				$item->loadReference('Folder');
			}
			$data[] = $item->toArray();
		}
		return $data;
	}

	/**
	 * @see module_db_interface_Object::replaceObjectRels()
	 *
	 * @param integer $objectId
	 * @param integer $relationType
	 * @param unknown_type $rels
	 * @return boolean
	 */
	public function replaceObjectRels($objectId, $relationType, $rels) {
		$parents = $this->conn->query('FROM ObjectObjectRel WHERE child_id = ? AND relation_type = ?', array((int)$objectId, (int)$relationType));
		$children = $this->conn->query('FROM ObjectObjectRel WHERE parent_id = ? AND relation_type = ?', array((int)$objectId, (int)$relationType));
		$currentRels = array();
		foreach($parents as $val) {
			$currentRels[$val['parent_id']] = $val['id'];
		}
		foreach($children as $val) {
			$currentRels[$val['child_id']] = $val['id'];
		}
		$savedRels = array();
		if (!is_array($rels)) {
			$rels = array();
		}
		foreach($rels as $key=>$val) {
			if(isset($currentRels[$key]) && !in_array($key, $savedRels)) {
				$savedRels[] = $key;
				unset($currentRels[$key]);
			} else {
				$rel = new ObjectObjectRel();
				$rel['parent_id'] = (int)$objectId;
				$rel['relation_type'] = (int)$relationType;
				$rel['child_id'] = (int)$key;
				$rel->save();
				$savedRels[] = $key;
			}
		}
		if (count($currentRels)) {
			$this->conn->query('DELETE FROM ObjectObjectRel WHERE id IN ('.implode(', ', $currentRels).')');
		}
	}

	/**
	 * @see module_db_interface_Object::getObjectById()
	 *
	 * @param integer $objectId
	 * @return array
	 */
	public function getObjectById($objectId) {
		return $this->normalizeResults($this->conn->queryOne('FROM ObjectData WHERE id=?', array((int)$objectId)));
	}

	/**
	 * @see module_db_interface_Object::getFullObjectById()
	 *
	 * @param integer $objectId
	 */
	public function getFullObjectById($objectId) {
		$obj = $this->conn->queryOne('FROM ObjectData WHERE id=?', array((int)$objectId));
		return $this->normalizeResults(sys_db_DoctrineHelper::getFullObject($obj));
	}

}
?>