<?php

/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 11068 $
 */

/**
 * Template generic database module
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 11068 $
 */
class module_db_generic_Template extends module_db_DbModule implements module_db_interface_Template,
    module_db_interface_Admin
{

    /**
     * @see module_db_interface_Template::addTemplateBoxplaces()
     *
     * @param integer $templateId
     * @param array $boxplaces
     */
    public function addTemplateBoxplaces ($templateId, $boxplaces)
    {
        $templateId = (int) $templateId;
        foreach ($boxplaces as $idx=>$boxplace) {
            $currBoxplace = $this->conn->selectFirst(
                array(
                    'table'=>'cms_boxplace_data',
                    'where'=>'template_id='.$templateId.' AND boxplace='.$this->conn->quote($boxplace)
                )
            );
            if ($currBoxplace['id']) {
                $data = array(
                    'boxplace_order' => $idx,
                );
                $this->conn->update(
                    array(
                        'table'=>'cms_boxplace_data' ,
                        'where'=>'id='.(int)$currBoxplace['id']
                        ), 
                        $data
                    );

            } else {
                $data = array();
                $data['template_id'] = $templateId;
                $data['boxplace'] = $boxplace;
                $data['boxplace_order'] = $idx;
                $this->conn->insert('cms_boxplace_data', $data);
            }
        }
    }

    /**
     * @see module_db_interface_Template::getTemplateBoxplaces()
     *
     * @param integer $templateId
     * @return array
     */
    public function getTemplateBoxplaces ($templateId)
    {
        return $this->conn->select(array('table'=>'cms_boxplace_data', 'where'=>'template_id='.(int)$templateId, 'orderBy'=>'boxplace_order'));
    }

    /**
     * @see module_db_interface_Template::deleteTemplateBoxplaces()
     *
     * @param integer $templateId
     * @param array $boxplaces
     */
    public function deleteTemplateBoxplaces($templateId, array $boxplaces) {
        foreach($boxplaces as $key=>$boxplace) {
            $boxplaces[$key] = $this->conn->quote($boxplace);
        }
        $this->conn->delete('cms_boxplace_data', 'template_id='.(int)$templateId.' AND boxplace IN ('.implode(', ', $boxplaces).')');
    }

    /**
     * @see module_db_interface_Admin::deleteItem()
     *
     * @param integer $itemId
     */
    public function deleteItem ($itemId)
    {
        return $this->basicDelete('cms_template_data', $itemId);
    }

    /**
     * @see module_db_interface_Admin::insertItem()
     *
     * @param array $itemData
     * @return string
     */
    public function insertItem ($itemData)
    {
        return $this->basicInsert('cms_template_data', $itemData);
    }

    /**
     * @see module_db_interface_Admin::loadItem()
     *
     * @param integer $itemId
     * @return array
     */
    public function loadItem ($itemId)
    {
        return $this->basicLoad('cms_template_data', $itemId);
    }

    /**
     * @see module_db_interface_Admin::updateItem()
     *
     * @param integer $itemId
     * @param array $itemData
     * @return string
     */
    public function updateItem ($itemId, $itemData)
    {
        return $this->basicUpdate('cms_template_data', $itemId, $itemData);
    }

    /**
     * Returns the list of themes (array with id=>name format)
     *
     * @return array
     */
    public function getTemplateList ()
    {
        return $this->getBasicIdSelectList('cms_template_data');
    }
}
?>