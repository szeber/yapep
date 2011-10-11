<?php

/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	Database
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 17282 $
 */

/**
 * Abstract class for database schemas
 *
 * @package	YAPEP
 * @subpackage	Database
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev: 17282 $
 */

abstract class sys_db_DatabaseSchema {

    const DIFFERENCE_CHANGED = 1;
    const DIFFERENCE_INCOMPATIBLE = 2;
    const DIFFERENCE_DROP = 3;
    const TYPE_COLUMN = 1;
    const TYPE_PRIMARY_KEY = 2;
    const TYPE_INDEX = 3;
    const TYPE_RELATION = 4;

    /**
     * @var sys_db_Database
     */
    protected $_db;

    /**
     *
     * @param sys_db_Database $db
     */
    public function __construct (sys_db_Database $db)
    {
        $this->_db = $db;
    }

    /**
     * Creates the specified tables in the database
     *
     * @param array $models
     */
    abstract public function exportTables (array $models);

    /**
     * Returns the database engine equivalent of the field specified in the model
     *
     * @param array $field
     * @return string
     */
    abstract public function getNativeType (array $field);

    /**
     * Returns the generic type from the native type
     *
     * @param string $type
     * @param integer $charLength
     * @param integer $octLength
     * @return array
     */
    abstract public function getGenericType($type, $length = null);

    /**
     * Returns the generic type that would be returned by the getNativeType function if converted to a native type first
     *
     * @param array $type
     * @return array
     */
    abstract protected function _getSimpleGenericType(array $type);

    abstract protected function _getAddColumnSql($model, $fieldName, $tableName, $afterColumn);

    abstract protected function _getAlterColumnSql($model, $fieldName, $tableName);

    abstract protected function _getDropColumnSql($fieldName, $tableName);

    abstract protected function _getAddIndexSql($model, $indexName, $tableName);

    abstract protected function _getAlterIndexSql($model, $indexName, $tableName);

    abstract protected function _getDropIndexSql($indexName, $tableName);

    abstract protected function _getAlterPrimaryKeySql($fields, $tableName, $hasCurrent);

    abstract protected function _getDropPrimaryKeySql($tableName);

    abstract protected function _getAddRelationSql($model, $tableName);

    abstract protected function _getDropRelationSql($relName, $tableName);

    /**
     * Checks if the field type in the database is capable of storing the data specified in the model
     *
     * @param array $modelType
     * @param array $dbType
     * @return boolean
     */
    public function checkGenericTypesAreCompatible(array $modelType, array $dbType) {
        $modelType = $this->_getSimpleGenericType($modelType);
        $ok = true;
        if (
            empty($modelType['notnull'])
            && empty($modelType['primary'])
            && (!empty($dbType['notnull']) || !empty($dbType['primary']))
        ) {
            return false;
        }
        if ((isset($modelType['autoincrement']) || isset($dbType['autoincrement'])) && $modelType['autoincrement'] != $dbType['autoincrement']) {
            return false;
        }
        if (empty($modelType['fixed']) != empty($dbType['fixed'])) {
            return false;
        }
        if (
            ($modelType['type'] == $dbType['type'])
            && (empty($dbType['length']) || (isset($modelType['length']) && $modelType['length'] <= $dbType['length']))
        ) {
            return true;
        }
        switch($modelType['type']) {
            case 'string':
                if (is_null($modelType['length'])) {
                    if ($dbType['type'] == 'clob' && (is_null($dbType['length']) || $dbType['length'] > 255)) {
                        return true;
                    }
                    if (is_null($dbType['length'])) {
                        return true;
                    }
                }
                break;
            case 'clob':
                if ($dbType['type'] == 'string' && ((is_null($dbType['length']) && $modelType['length'] < 65532) || $dbType['length'] > $modelType['length'])) {
                    return true;
                }
                break;
        }
        return false;
    }

    /**
     * Checks if the field type in the database is the same as the one specified in the model
     *
     * @param array $modelType
     * @param array $dbType
     * @return boolean
     */
    public function checkGenericTypesAreSame(array $modelType, array $dbType) {
        $modelType = $this->_getSimpleGenericType($modelType);
        if (
            (!empty($modelType['primary']) || !empty($modelType['notnull']))
            != (!emptY($dbType['notnull']) || !empty($dbType['primary']))
        ) {
            return false;
        }
        if ((isset($modelType['autoincrement']) || isset($dbType['autoincrement'])) && $modelType['autoincrement'] != $dbType['autoincrement']) {
            return false;
        }
        if (empty($modelType['fixed']) != empty($dbType['fixed'])) {
            return false;
        }
        if (
            ($modelType['type'] == $dbType['type'])
            && isset($dbType['length']) == isset($modelType['length'])
            && (!isset($dbType['length']) ||$modelType['length'] == $dbType['length'])
        ) {
            return true;
        }
        return false;
    }

    public function compareTables(array $modelTable, array $dbTable, $tableName) {
        $differences = array(
        );
        $orgTable = $dbTable;
        // columns
        $prevColumn = null;
        foreach($modelTable['columns'] as $columnName=>$column) {
            if (!isset($dbTable['columns'][$columnName])) {
                $differences[] = array(
                    'name'=> $columnName,
                    'difference'=> self::DIFFERENCE_INCOMPATIBLE,
                    'type'=>self::TYPE_COLUMN,
                    'fix' => $this->_getAddColumnSql($column, $columnName, $tableName, $prevColumn),
                );
                $prevColumn = $columnName;
                continue;
            } else if (!$this->checkGenericTypesAreCompatible($column,  $dbTable['columns'][$columnName])) {
                $differences[] = array(
                    'name' => $columnName,
                    'difference' => self::DIFFERENCE_INCOMPATIBLE,
                    'type'=>self::TYPE_COLUMN,
                    'fix' => $this->_getAlterColumnSql($column, $columnName, $tableName),
                );
            } else if (!$this->checkGenericTypesAreSame($column, $dbTable['columns'][$columnName])) {
                $differences[] = array(
                    'name' => $columnName,
                    'difference' => self::DIFFERENCE_CHANGED,
                    'type'=>self::TYPE_COLUMN,
                    'fix' => $this->_getAlterColumnSql($column, $columnName, $tableName),
                );
            }
            $prevColumn = $columnName;
            unset($dbTable['columns'][$columnName]);
        }
        if (count($dbTable['columns'])) {
            foreach($dbTable['columns'] as $columnName=>$column) {
                $differences[] = array(
                    'name' => $columnName,
                    'difference' => self::DIFFERENCE_DROP,
                    'type' => self::TYPE_COLUMN,
                    'fix' => $this->_getDropColumnSql($columnName, $tableName),
                );
            }
        }

        // primary key
        $dbTable = $orgTable;
        if (is_array($modelTable['primaryKey']) && count($modelTable['primaryKey'])) {
            $keysDifferent = false;
            if (!is_array($dbTable['primaryKey'])) {
                $keysDifferent = true;
            } else {
                foreach($modelTable['primaryKey'] as $key) {
                    if (!in_array(trim($key), $dbTable['primaryKey'])) {
                        $keysDifferent = true;
                        break;
                    }
                }
            }
            if ($keysDifferent || count($modelTable['primaryKey']) != count($dbTable['primaryKey'])) {
                $differences[] = array(
                    'name' => 'PRIMARY KEY',
                    'difference' => self::DIFFERENCE_INCOMPATIBLE,
                    'type' => self::TYPE_PRIMARY_KEY,
                    'fix' => $this->_getAlterPrimaryKeySql($modelTable['primaryKey'], $tableName, (is_array($dbTable['primaryKey']) && count($dbTable['primaryKey'])>0)),
                );
            }
        } else if(is_array($dbTable['primaryKey']) && count($modelTable['primaryKey'])) {
            $differences[] = array(
                'name' => 'PRIMARY KEY',
                'difference' => self::DIFFERENCE_DROP,
                'type' => self::TYPE_PRIMARY_KEY,
                'fix' => $this->_getDropPrimaryKeySql($tableName),
            );
        }

        // indexes
        $dbTable = $orgTable;
        if (isset($modelTable['indexes']) && is_array($modelTable['indexes'])) {
            foreach($modelTable['indexes'] as $indexName=>$index) {
                if (!isset($dbTable['indexes'][$indexName ])) {
                    $differences[] = array(
                        'name'=> $indexName,
                        'difference'=> self::DIFFERENCE_INCOMPATIBLE,
                        'type'=>self::TYPE_INDEX,
                        'fix' => $this->_getAddIndexSql($index, $indexName, $tableName),
                    );
                    continue;
                } else {
                    $fieldsDifferent = false;
                    if (count($index['fields']) != count($dbTable['indexes'][$indexName]['fields'])) {
                        $fieldsDifferent = true;
                    } else {
                        foreach($index['fields'] as $field) {
                            if (!in_array(trim($field), $dbTable['indexes'][$indexName]['fields'])) {
                                $fieldsDifferent = true;
                                break;
                            }
                        }
                    }
                    if ($fieldsDifferent) {
                        $differences[] = array(
                            'name'=> $indexName,
                            'difference'=> self::DIFFERENCE_INCOMPATIBLE,
                            'type'=>self::TYPE_INDEX,
                            'fix' => $this->_getAlterIndexSql($index, $indexName, $tableName),
                        );
                    } else if (isset($index['type']) && 'unique' == $index['type']) {
                        if (!isset($dbTable['indexes'][$indexName]['type']) || 'unique' != $dbTable['indexes'][$indexName]['type']) {
                            $differences[] = array(
                                'name'=> $indexName,
                                'difference'=> self::DIFFERENCE_INCOMPATIBLE,
                                'type'=>self::TYPE_INDEX,
                                'fix' => $this->_getAlterIndexSql($index, $indexName, $tableName),
                            );
                        }
                    } else {
                        if (isset($dbTable['indexes'][$indexName]['type']) && 'unique' != $dbTable['indexes'][$indexName]['type']) {
                            $differences[] = array(
                                'name'=> $indexName,
                                'difference'=> self::DIFFERENCE_INCOMPATIBLE,
                                'type'=>self::TYPE_INDEX,
                                'fix' => $this->_getAlterIndexSql($index, $indexName, $tableName),
                            );
                        }
                    }
                    unset($dbTable['indexes'][$indexName]);
                }
            }
        }
        if (!empty($dbTable['indexes'])) {
            foreach($dbTable['indexes'] as $indexName=>$index) {
                if ($index['type'] == 'unique') {
                    $differences[] = array(
                        'name' => $indexName,
                        'difference' => self::DIFFERENCE_INCOMPATIBLE,
                        'type' => self::TYPE_INDEX,
                        'fix' => $this->_getDropIndexSql($indexName, $tableName),
                    );
                } else {
                    $differences[] = array(
                        'name' => $indexName,
                        'difference' => self::DIFFERENCE_DROP,
                        'type' => self::TYPE_INDEX,
                        'fix' => $this->_getDropIndexSql($indexName, $tableName),
                    );
                }
            }
        }

        // relations
        $dbTable = $orgTable;
        if(isset($modelTable['relations']) && is_array($modelTable['relations'])) {
            foreach($modelTable['relations'] as $relName=>$relation) {
                if($relation['type'] != 'one') {
                    continue;
                }
                if(is_array($dbTable['relations'])) {
                    foreach($dbTable['relations'] as $tableKey=>$tableRel) {
                        if ($tableRel['table'] == $relation['table'] && $tableRel['local'] == $relation['local'] && $tableRel['foreign'] == $relation['foreign']) {
                            if (isset($relation['onDelete'])) {
                                if ($relation['onDelete'] != $tableRel['onDelete']) {
                                    continue;
                                }
                            } else if (isset($tableRel['onDelete'])) {
                                continue;
                            }
                            if (isset($relation['onUpdate'])) {
                                if ($relation['onUpdate'] != $tableRel['onUpdate']) {
                                    continue;
                                }
                            } else if (isset($tableRel['onUpdate'])) {
                                continue;
                            }
                            unset($dbTable['relations'][$tableKey]);
                            continue 2;
                        }
                    }
                }
                $differences[] = array(
                    'name' => $relName,
                    'difference' => self::DIFFERENCE_INCOMPATIBLE,
                    'type' => self::TYPE_RELATION,
                    'fix' => $this->_getAddRelationSql($relation, $tableName),
                );
            }
        }
        if(!empty($dbTable['relations']) && is_array($dbTable['relations'])) {
            foreach($dbTable['relations'] as $relation) {
                $differences[] = array(
                    'name' => $relName,
                    'difference' => self::DIFFERENCE_INCOMPATIBLE,
                    'type' => self::TYPE_RELATION,
                    'fix' => $this->_getDropRelationSql($relation['keyName'], $tableName),
                );
            }
        }
        return $differences;
    }
}