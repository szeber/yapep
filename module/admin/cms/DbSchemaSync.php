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
 * Database synchronization module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 7827 $
 */
class module_admin_cms_DbSchemaSync extends sys_admin_AdminModule
{

    protected $unknownWarned = false;

    protected function init() {
        parent::init();
        $cache = new sys_cache_DbSchema($this->config);
        $cache->recreateCache();
    }

    protected function buildForm () {

        if ($this->mode == self::MODE_FORM) {
            $this->addWarning(_('Always back up your database(s) before using this module! Data loss may occur.'));
        }

        $this->setAddBtnDisabled();
        $this->setDeleteBtnDisabled();

        $control = new sys_admin_control_IdSelect();
        $control->setRequired();
        $control->setValue($this->id, true);
        $control->addOptions($this->getConnections());
        $control->setLabel(_('Database connection'));
        $control->setNullValueLabel(_('--- Please select ---'));
        $this->addControl($control, 'connection');

        $control = new sys_admin_control_TextArea();
        $control->setLabel(_('Required changes'));
        $control->setDescription(_('These changes are required to make the database compatible with the models. Data loss is not likely to occur.'));
        $control->setReadOnly();
        $this->addControl($control, 'incompatible');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Apply the required changes'));
        $this->addControl($control, 'apply_incompatible');

        $control = new sys_admin_control_TextArea();
        $control->setLabel(_('Optional changes'));
        $control->setDescription(_('These changes are optional. Without these the database is not in perfect sync with the models, but it is capable of storing all the required data and maintaining the consistency. These changes may cause some data loss.'));
        $control->setReadOnly();
        $this->addControl($control, 'changed');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Apply the optional changes'));
        $this->addControl($control, 'apply_changed');

        $control = new sys_admin_control_TextArea();
        $control->setLabel(_('Missing tables'));
        $control->setDescription(_('These models don\'t have their respective tables in the database and should be created. You can edit this field and remove the tables you don\'t wish to create with this connection.'));
        $this->addControl($control, 'createModels');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Create the above tables'));
        $this->addControl($control, 'apply_createModels');

        $control = new sys_admin_control_TextArea();
        $control->setLabel(_('Droppable objects'));
        $control->setDescription(_('These changes remove database objects that don\'t exist in the models. Data loss is VERY LIKELY to occur.'));
        $control->setReadOnly();
        $this->addControl($control, 'drop');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Drop the droppable objects'));
        $this->addControl($control, 'apply_drop');
    }

    protected function getConnections() {
        $idx = 1;
        $connections = $this->config->getDatabaseNames();
        $data = array();
        foreach($connections as $val) {
            $data[$idx] = $val;
            $idx++;
        }
        return $data;
    }

    protected function doSave() {
        $connections = $this->getConnections();
        $connection = $connections[$this->id];
        if (!$connection) {
            $this->addWarning(_('Can\'t find the specified connection.'));
            return '';
        }
        $db = sys_LibFactory::getDbConnection($connection);
        
        $connectionInfo = $db->getDbConfig();
        switch($connectionInfo['type']) {
            case 'mysqli':
                $schemaHandler = new sys_db_MysqliSchema($db);
                break;
            default:
                return;
                break;
        }

        $db->begin();

        if ($this->data['apply_createModels']) {
            $models = preg_split('/( |,|\n|\r)/m', $this->data['createModels']);
            foreach ($models as $key => $val) {
                $models[$key] = trim($val);
                if (! $val) {
                    unset($models[$key]);
                }
            }
            $schemaHandler->exportTables($models);
        }

        if ($this->data['apply_incompatible']) {
            $queries = explode("\n", $this->data['incompatible']);
            foreach($queries as $query) {
                if (!$db->execute(trim($query))) {
                    $db->fail();
                    $db->complete();
                    return $db->getLastError();
                }
            }
        }

        if ($this->data['apply_changed']) {
            $queries = explode("\n", $this->data['changed']);
            foreach($queries as $query) {
                if (!$db->execute(trim($query))) {
                    $db->fail();
                    $db->complete();
                    return $db->getLastError();
                }
            }
        }

        if ($this->data['apply_drop']) {
            $queries = explode("\n", $this->data['drop']);
            foreach($queries as $query) {
                if (!$db->execute(trim($query))) {
                    $db->fail();
                    $db->complete();
                    return $db->getLastError();
                }
            }
        }
        
        $db->complete();
        return '';
    }

    protected function doLoad() {
        $data['incompatible'] = '';
        $data['changed'] = '';
        $data['drop'] = '';
        $data['createModels'] = '';
        $connections = $this->getConnections();
        $connection = $connections[$this->id];
        if (!$connection) {
            $this->addWarning(_('Can\'t find the specified connection.'));
            return $data;
        }
        $db = sys_LibFactory::getDbConnection($connection);
        $connectionInfo = $db->getDbConfig();
        switch($connectionInfo['type']) {
            case 'mysqli':
                $schemaHandler = new sys_db_MysqliSchema($db);
                break;
            default:
                $this->addWarning(_('Sorry, this database connection type is not supported by this module.'));
                return $data;
                break;
        }
        
        $schema = $schemaHandler->getDbSchema();
        $modelNames = sys_db_ModelHelper::getAllModelNames();
        sort($modelNames);

        $incompatible = $this->getEmptyChangeArr();
        $changed = $this->getEmptyChangeArr();
        $drop = $this->getEmptyChangeArr();
        $createModels = array();

        foreach($modelNames as $model) {
            $tableName = sys_cache_DbSchema::makeTableName($model);
            if (isset($schema[$tableName])) {
                $differences = $schemaHandler->compareTables($db->getTableSchema($tableName), $schema[$tableName], $tableName);
                foreach($differences as $difference) {
                    switch($difference['difference']) {
                        case sys_db_DatabaseSchema::DIFFERENCE_CHANGED:
                            $changed[$difference['type']][] = $difference['fix'];
                            break;
                        case sys_db_DatabaseSchema::DIFFERENCE_INCOMPATIBLE:
                            $incompatible[$difference['type']][] = $difference['fix'];
                            break;
                        case sys_db_DatabaseSchema::DIFFERENCE_DROP:
                            $drop[$difference['type']][] = $difference['fix'];
                            break;
                    }
                }
                unset($schema[$tableName]);
            } else {
                $createModels[] = $model;
            }
        }
        if (count($schema) && !$this->unknownWarned) {
            $this->addWarning(_('The following tables don\'t have models:')."\n\n".implode("\n", array_keys($schema)));
            $this->unknownWarned = true;
        }
        $data['incompatible'] = $this->getStringFromChangeArr($incompatible);
        $data['changed'] = $this->getStringFromChangeArr($changed);
        $data['drop'] = $this->getStringFromChangeArr($drop);
        $data['createModels'] = implode("\n", $createModels);;
        return $data;
    }

    protected function getStringFromChangeArr($types) {
        $tmp = array();
        foreach($types as $changes) {
            foreach($changes as $change) {
                $tmp[] = $change;
            }
        }
        return implode("\n", $tmp);

    }

    protected function getEmptyChangeArr() {
        return array(
            sys_db_DatabaseSchema::TYPE_COLUMN => array(),
            sys_db_DatabaseSchema::TYPE_PRIMARY_KEY => array(),
            sys_db_DatabaseSchema::TYPE_INDEX => array(),
            sys_db_DatabaseSchema::TYPE_RELATION => array(),
        );
    }


}