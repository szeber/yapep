<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 11068 $
 */

/**
 * Asset resizer mode editor admin module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 11068 $
 */
class module_admin_asset_Resizer extends sys_admin_AdminModule {

	/**
	 * @var module_db_interface_Asset
	 */
	protected $dbHandler;

	protected function buildForm() {

		$this->dbHandler = getPersistClass('Asset');

		$control = new sys_admin_control_IdSelect ();
		$control->addOptions ($this->dbHandler->getResizeList ());
		$this->addControl ($control, 'idSelect');

		$control = new sys_admin_control_TextInput ();
		$control->setRequired (true);
		$control->setLabel (_ ('Name'));
		$this->addControl ($control, 'name');

		$control = new sys_admin_control_TextInput ();
		$control->setLabel (_ ('Width'));
		$control->setRequired();
		$control->setValidateNumeric();
		$this->addControl ($control, 'width');

		$control = new sys_admin_control_TextInput ();
		$control->setLabel (_ ('Height'));
		$control->setRequired();
		$control->setValidateNumeric();
		$this->addControl ($control, 'height');

		$control = new sys_admin_control_CheckBox();
		$control->setLabel (_ ('Force exact size'));
		$control->setDescription(_('This option sets the image size to exactly fit the specified values, regardless of aspect ratio. It also allows upscaling of the image.'));
		$control->setDefaultValue(0);
		$this->addControl($control, 'force_exact');

        $control = new sys_admin_control_TextInput ();
		$control->setLabel (_ ('Thumbnail Width'));
		$control->setRequired();
		$control->setValidateNumeric();
		$this->addControl ($control, 'thumb_width');

		$control = new sys_admin_control_TextInput ();
		$control->setLabel (_ ('Thumbnail Height'));
		$control->setRequired();
		$control->setValidateNumeric();
		$this->addControl ($control, 'thumb_height');

		$control = new sys_admin_control_CheckBox();
		$control->setLabel (_ ('Crop thumbnail'));
		$control->setDescription(_('This option sets the thumbnail size to exactly fit the specified size, by croping it and also allows upscaling'));
		$control->setDefaultValue(0);
		$this->addControl($control, 'thumb_crop');

    }

	/**
	 * @see sys_admin_AdminModule::doDelete()
	 *
	 */
	protected function doDelete() {
		return $this->dbHandler->deleteResizeItem($this->id);
	}

	/**
	 * @see sys_admin_AdminModule::doLoad()
	 *
	 * @return array;
	 */
	protected function doLoad() {
		return $this->dbHandler->loadResizeItem($this->id);
	}

	/**
	 * @see sys_admin_AdminModule::doSave()
	 *
	 * @return string
	 */
	protected function doSave() {
		if ($this->mode == sys_admin_AdminModule::MODE_ADD) {
			$result = $this->dbHandler->insertResizeItem ($this->panel->getInputValues ());
			if ($result && is_numeric ($result)) {
				$this->options ['newId'] = $result;
				$this->id = $result;
				if ($this->newForm) {
					$this->options ['newForm'] = $this->newForm;
				}
				$result = '';
			} elseif (!$result) {
				$result = 'Insert error';
			}
		} else {
			$result = $this->dbHandler->updateResizeItem($this->id, $this->panel->getInputValues ());
		}
		return $result;
	}

}
?>