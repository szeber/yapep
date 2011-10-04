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
 * Article administration module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_Article extends sys_admin_AdminModule {
	protected function buildForm() {

		$handler = getPersistClass('GenericAdmin');
		$handler->setObjType('Article');
		$this->setDbHandler($handler);

		$control = new sys_admin_control_TextInput();
		$control->setRequired(true);
		$control->setLabel(_('Title'));
		$this->addControl($control, 'name');

		$control = new sys_admin_control_TextArea();
		$control->setRichEdit();
		$control->setLabel(_('Lead'));
		$this->addControl($control, 'lead');

		$control = new sys_admin_control_TextArea();
		$control->setRichEdit();
		$control->setLabel(_('Content'));
		$this->addControl($control, 'content');

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Author'));
		$this->addControl($control, 'author');

		$control = new sys_admin_control_TextInput();
		$control->setLabel(_('Author email'));
		$this->addControl($control, 'author_email');

		$control = new sys_admin_control_RelationList ();
		$control->setLabel (_ ('Related articles'));
		$control->addObjectType ('Doc/Article');
		$control->setDisplayTemplate ('{$name}');
		$control->setNameField ('id');
		$control->setDataField ('id');
		$control->setValueField('name');
		$this->addControl ($control, 'RelatedArticles');

		$control = new sys_admin_control_AssetUrlInput();
		$control->setLabel(_('Title picture 1'));
		$control->addObjectType('asset_Editor/2');
		$this->addControl($control, 'titlepic_1');

		$control = new sys_admin_control_AssetUrlInput();
		$control->setLabel(_('Title picture 2'));
		$control->addObjectType('asset_Editor/2');
		$this->addControl($control, 'titlepic_2');
	}

	/**
	 * @see sys_admin_AdminModule::postSave()
	 *
	 */
	protected function postSave() {
		if ($this->mode == sys_admin_AdminModule::MODE_EDIT) {
			$objectHandler = getPersistClass('Object');
			$objectHandler->replaceObjectRels($this->id, module_db_interface_Article::REL_ARTICLE, $this->data['RelatedArticles']);
		}
	}

	/**
	 * @see sys_admin_AdminModule::processLoadData()
	 *
	 */
	protected function processLoadData() {
		$objectHandler = getPersistClass('Object');
		$this->data['RelatedArticles'] = $objectHandler->getRelList($this->id, module_db_interface_Article::REL_ARTICLE);
	}
}
?>