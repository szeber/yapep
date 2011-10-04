<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

 /**
 * List table control
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_admin_control_ListTable extends sys_admin_control_Table {

	protected $filters = array();

	protected $allowedTypes = array();

	/**
	 * Sets the name of the column that the data is ordered by
	 *
	 * @param string $column
	 */
	public function setOrderBy($column) {
		$this->options['orderBy']=$column;
	}

	/**
	 * Sets the ordering direction to descending if $direction = '-' or 'desc' or 'DESC', to ascending otherwise
	 *
	 * @param string $direction
	 */
	public function setOrderDirection($direction) {
		switch ($direction) {
			case '-':
			case 'desc':
			case 'DESC':
				$this->options['orderDirection'] = '-';
				break;
			default:
				$this->options['orderDirection'] = '+';
				break;
		}
	}

	/**
	 * Sets the current page number (indexed from 0!)
	 *
	 * @param integer $number
	 */
	public function setPageNumber($number) {
		$this->options['pageNumber']=$number;
	}

	/**
	 * Sets the number of hits/page
	 *
	 * @param integer $limit
	 */
	public function setPageLimit($limit) {
		$this->options['pageLimit'] = $limit;
	}

	/**
	 * Sets the filters used for the query
	 *
	 * @param array $filters
	 */
	public function setFilters($filters) {
		$this->filters = $filters;
	}

	/**
	 * Sets the number of hits returned
	 *
	 * @param integer $count
	 */
	public function setResultCount($count) {
		$this->options['resultCount'] = $count;
	}

	/**
	 * Sets if searching in subfolders is allowed
	 *
	 * @param boolean $subfolderSearch
	 */
	public function disableSubfolderSearch($disable = true) {
		if ($disable) {
			$this->options['disableSubfolderSearch'] = 1;
			return;
		}
		unset($this->options['disableSubfolderSearch']);
	}

	/**
	 * Sets if the results returned contain a subfolder search results too
	 *
	 * @param boolean $subfolderSearch
	 */
	public function setSubfolderSearchEnabled($subfolderSearch = true) {
		if ($subfolderSearch) {
			$this->options['subfolderSearchEnabled'] = 1;
			return;
		}
		unset($this->options['subfolderSearchEnabled']);
	}

	/**
	 * Sets the allowed types list for the list
	 *
	 * @param array $allowedTypes
	 */
	public function setAllowedTypes($allowedTypes) {
		$this->allowedTypes = $allowedTypes;
	}

	/**
	 * Enables or disables adding new items
	 *
	 * @param boolean $disable
	 */
	public function disableAddItems($disable = true) {
		if ($disable) {
			$this->options['disableAddItems'] = 1;
			return;
		}
		unset($this->options['disableAddItems']);
	}

	/**
	 * @see sys_admin_Control::setDefaults()
	 *
	 */
	protected function setDefaults() {
		$this->options['orderDirection'] = '+';
		$this->options['pageNumber'] = 0;
		$this->options['pageLimit'] = 20;
		$this->options['filterLabel'] = _('Filters');
		$this->options['resultCount'] = 0;
	}

	/**
	 * @see sys_admin_control_Table::fetchSmarty()
	 *
	 * @return string
	 */
	protected function fetchSmarty() {
		return $this->smarty->fetch ('yapep:admin/control/ListTable.tpl');
	}

	/**
	 * @see sys_admin_control_Table::getXml()
	 *
	 * @return string
	 */
	public function getXml() {
		$this->smarty->assign('filters', $this->filters);
		$this->smarty->assign('allowedTypes', $this->allowedTypes);
		return parent::getXml();
	}


}
?>