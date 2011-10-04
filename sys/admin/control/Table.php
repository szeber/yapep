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
 * Table control
 *
 * @package	YAPEP
 * @subpackage	Admin
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_admin_control_Table extends sys_admin_Control {

	/**
	 * Stores the data in the table
	 *
	 * @var array
	 */
	protected $tableData = array();

	/**
	 * Stores the headers for the table
	 *
	 * @var array
	 */
	protected $headers = array();

	/**
	 * @see sys_admin_interface_Control::getXml()
	 *
	 * @return string
	 */
	public function getXml() {
		$this->smarty->assign ('options', $this->options);
		$this->smarty->assign ('headers', $this->headers);
		$this->smarty->assign ('tableData', $this->tableData);
		return $this->fetchSmarty ();
	}

	/**
	 * Fetches the XML from Smarty
	 *
	 * @return string
	 */
	protected function fetchSmarty() {
		return $this->smarty->fetch ('yapep:admin/control/Table.tpl');
	}

	/**
	 * Sets the title for the table
	 *
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->options['title'] = $title;
	}

	/**
	 * Sets the headers for the list
	 *
	 * @param string $headers
	 */
	public function setHeaders($headers) {
		$this->headers = $headers;
	}

	/**
	 * Sets the table data
	 *
	 * @param array $data
	 */
	public function setTableData($data) {
		$this->tableData = $data;
	}

}
?>