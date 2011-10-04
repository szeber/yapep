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
 * Template Doctrine database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_db_Doctrine_Template extends module_db_DoctrineDbModule implements module_db_interface_Template, module_db_interface_Admin  {

	/**
	 * @see module_db_interface_Template::addTemplateBoxplaces()
	 *
	 * @param integer $templateId
	 * @param array $boxplaces
	 */
	public function addTemplateBoxplaces($templateId, $boxplaces) {
		$templateId = (int)$templateId;
		foreach($boxplaces as $boxplace) {
			$data = new CmsBoxplaceData();
			$data['template_id'] = $templateId;
			$data['boxplace'] = $boxplace;
			$data->save();
		}
	}

	/**
	 * @see module_db_interface_Template::getTemplateBoxplaces()
	 *
	 * @param integer $templateId
	 * @return array
	 */
	public function getTemplateBoxplaces($templateId) {
		return $this->conn->query('FROM CmsBoxplaceData WHERE template_id = ?', array((int)$templateId));
	}
	/**
	 * @see module_db_interface_Admin::deleteItem()
	 *
	 * @param integer $itemId
	 */
	public function deleteItem($itemId) {
		return $this->basicDelete('CmsTemplateData', $itemId);
	}

	/**
	 * @see module_db_interface_Admin::insertItem()
	 *
	 * @param array $itemData
	 * @return string
	 */
	public function insertItem($itemData) {
		return $this->basicInsert('CmsTemplateData', $itemData);
	}

	/**
	 * @see module_db_interface_Admin::loadItem()
	 *
	 * @param integer $itemId
	 * @return array
	 */
	public function loadItem($itemId) {
		return $this->conn->queryOne('FROM CmsTemplateData WHERE id = ?', array((int)$itemId));
	}

	/**
	 * @see module_db_interface_Admin::updateItem()
	 *
	 * @param integer $itemId
	 * @param array $itemData
	 * @return string
	 */
	public function updateItem($itemId, $itemData) {
		return $this->basicUpdate('CmsTemplateData', $itemId, $itemData);
	}

	/**
	 * Returns the list of themes (array with id=>name format)
	 *
	 * @return array
	 */
	public function getTemplateList() {
		return $this->getBasicIdSelectList('CmsTemplateData');
	}
}
?>