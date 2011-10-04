<?php

/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 7827 $
 */

/**
 * Table creation module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 7827 $
 */
class module_admin_cms_CreateTable extends sys_admin_AdminModule
{

    protected function buildForm ()
    {

        $this->requireSuperuser();

        $this->setTitle(_('Create tables'));

        $this->setAddBtnDisabled();
        $this->setDeleteBtnDisabled();
        $this->setSaveBtnText(_('Recache'));

        $control = new sys_admin_control_TextInput();
        $control->setLabel(_('Connection identifier'));
        $control->setDescription(
            _(
                'The name of the connection to use. It must be a MySQLi connection!'));
        $control->setDefaultValue('site');
        $control->setRequired();
        $this->addControl($control, 'connection');

        $control = new sys_admin_control_TextArea();
        $control->setLabel(_('Model names'));
        $control->setRequired();
        $this->addControl($control, 'model');

    }

    protected function doSave ()
    {
        $cache = new sys_cache_DbSchema($this->config);
        $cache->recreateCache();
        $models = preg_split('/( |,|\n|\r)/m', $this->data['model']);
        foreach ($models as $key => $val) {
            $models[$key] = trim($val);
            if (! $val) {
                unset($models[$key]);
            }
        }
        if (! count($models)) {
            return;
        }
        $conn = sys_LibFactory::getDbConnection($this->data['connection']);
        if ($conn instanceof sys_db_MysqliDatabase) {
            $schemaHandler = new sys_db_MysqliSchema($conn);
            $schemaHandler->exportTables($models);
        } else {
            throw new sys_exception_AdminException(
                _(
                    'Provided connection is not a MySQLi connection.'),
                sys_exception_AdminException::ERR_SAVE_ERROR);
        }

    }

    protected function doLoad ()
    {}
}
?>