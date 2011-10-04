<?php

/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	Database
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Mysqli schema manager
 *
 * @package	YAPEP
 * @subpackage	Database
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_db_MysqliSchema extends sys_db_DatabaseSchema
{

    /**
     * Creates the specified tables in the database
     *
     * @param array $models
     */
    public function exportTables (array $models)
    {
        sort($models);
        $this->_db->execute("SET FOREIGN_KEY_CHECKS = 0;");
        sys_Debugger::debug($models);
        $constraints = array();
        foreach ($models as $model) {
            $tableName = sys_cache_DbSchema::makeTableName($model);
            $table = $this->_db->getTableSchema($tableName);
            $sql = 'CREATE TABLE `' . $table['tableName'] . "` (\n";
            foreach ($table['columns'] as $columnName => $field) {
                $sql .= '  `' . $columnName . '` ' . $this->getNativeType(
                    $field);
                if ($field['primary']) {
                    $field['notnull'] = true;
                }
                if ($field['notnull']) {
                    $sql .= ' NOT NULL';
                }
                if ($field['autoincrement']) {
                    $sql .= ' auto_increment';
                }
                if (isset($field['default']) && !is_null($field['default'])) {
                    $sql .= ' DEFAULT '.$this->_db->getQuotedVal($field['default'], $field['type']);
                }
                $sql .= ",\n";
            }
            $pk = array();
            foreach ($table['primaryKey'] as $field) {
                $pk[] = '`' . $field . '`';
            }
            $sql .= '  PRIMARY KEY (' . implode(', ', $pk) . ")";
            if (is_array($table['indexes'])) {
                foreach ($table['indexes'] as $name => $index) {
                    $sql .= ",\n  ";
                    $fields = array();
                    foreach ($index['fields'] as $field) {
                        $fields[] = '`' .
                             trim(
                                $field) .
                             '`';
                    }
                    if ('unique' == $index['type']) {
                        $sql .= 'UNIQUE ';
                    }
                    $sql .= 'KEY `' . $name .
                         '` (' .
                         implode(
                            ', ',
                            $fields) .
                         ")";
                }
            }
            $sql .= "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8;\n\n";
            $this->_db->execute($sql);
            if (is_array($table['relations'])) {
                foreach ($table['relations'] as $relation) {
                    if ('one' != $relation['type']) {
                        continue;
                    }
                    if (!$relation['table'] || !$relation['foreign'] || !$relation['local']) {
                        continue;
                    }
                    $rel = 'ALTER TABLE `' .
                         $table['tableName'] .
                         "`\n  ADD CONSTRAINT " .
                         'FOREIGN KEY (`' .
                         $relation['local'] .
                         '`) REFERENCES `' .
                         $relation['table'] .
                         '` (`' .
                         $relation['foreign'] .
                         "`)";
                     if (isset($relation['onDelete']) && $relation['onDelete']) {
                         $rel .= ' ON DELETE '.$relation['onDelete'];
                     }
                     if (isset($relation['onUPDATE']) && $relation['onUPDATE']) {
                         $rel .= ' ON UPDATE '.$relation['onUPDATE'];
                     }
                     $rel .=";\n\n";
                     $constraints[] = $rel;
                }
            }
        }
        foreach($constraints as $constraint) {
            $this->_db->execute($constraint);
        }
        $this->_db->execute("SET FOREIGN_KEY_CHECKS = 1;");
    }

    public function getNativeType (array $field)
    {
        switch ($field['type']) {
            case 'integer':
                if (! empty($field['length'])) {
                    $length = $field['length'];
                    if ($length <= 1) {
                        return 'TINYINT';
                    } elseif ($length ==
                         2) {
                            return 'SMALLINT';
                    } elseif ($length ==
                         3) {
                            return 'MEDIUMINT';
                    } elseif ($length ==
                         4) {
                            return 'INT';
                    } elseif ($length > 4) {
                        return 'BIGINT';
                    }
                }
                return 'INT';
            case 'boolean':
                return 'TINYINT';
            case 'float':
            case 'double':
                return 'DOUBLE';
            case 'gzip':
            case 'blob':
                if (! empty($field['length'])) {
                    $length = $field['length'];
                    if ($length <= 255) {
                        return 'TINYBLOB';
                    } elseif ($length <=
                         65532) {
                            return 'BLOB';
                    } elseif ($length <=
                         16777215) {
                            return 'MEDIUMBLOB';
                    }
                }
                return 'LONGBLOB';
            case 'clob':
                if (! empty($field['length'])) {
                    $length = $field['length'];
                    if ($length <= 255) {
                        return 'TINYTEXT';
                    } elseif ($length <=
                         65532) {
                            return 'TEXT';
                    } elseif ($length <=
                         16777215) {
                            return 'MEDIUMTEXT';
                    }
                }
                return 'LONGTEXT';
            case 'date':
                return 'DATE';
            case 'time':
                return 'TIME';
            case 'timestamp':
                return 'DATETIME';
            case 'array':
            case 'object':
            case 'string':
                if (! isset($field['length'])) {
                    if (array_key_exists('default', $field)) {
                        $field['length'] = $this->conn->varchar_max_length;
                    } else {
                        $field['length'] = false;
                    }
                }

                $length = ($field['length'] <= 255) ? $field['length'] : false;
                $fixed = (isset($field['fixed'])) ? $field['fixed'] : false;

                return $fixed ? ($length ? 'CHAR(' . $length .
                     ')' : 'CHAR(255)') : ($length ? 'VARCHAR(' .
                     $length . ')' : 'TEXT');
            default:
                throw new sys_exception_DatabaseException(
                    'Unknown type: ' . $field['type'],
                    sys_exception_DatabaseException::ERR_SCHEMA_ERROR);
        }
    }

    public function getDbSchema() {
        $config = $this->_db->getDbConfig();
        $dbName = $config['dbName'];
        $tables = $this->_db->execute('SHOW TABLES');
        $schema = array();
        foreach($tables as $val) {
            $tableName = $val['Tables_in_'.$dbName];
            $result = $this->_db->execute('SHOW CREATE TABLE '.$tableName);
            $create = reset($result);
            if (isset($create['View']) && $create['View'] == $tableName) {
            	continue;
            }
            $rows = explode("\n", trim(preg_replace('/^[^(]+\((.+)\)[^)]*$/is', '$1', $create['Create Table'])));
            $table = array(
                'tableName' => $tableName,
                'primaryKey' => array(),
            );
            foreach($rows as $row) {
                if (preg_match('/^\s*PRIMARY KEY\s*\((.+)\)/', $row, $matches)) {
                    // primary key
                    $this->processPrimaryKeyRow($table, $matches);
                } else if (preg_match('/^\s*UNIQUE KEY\s*`([^`]+)`\s*\((.+)\)/', $row, $matches)) {
                    // unique index
                    $this->processIndexRow($table, $matches, true);
                } else if (preg_match('/^\s*KEY\s*`([^`]+)`\s*\((.+)\)/', $row, $matches)) {
                    // index
                    $this->processIndexRow($table, $matches, false);
                } else if(preg_match('/^\s*CONSTRAINT\s*`([^`]+)`\s*FOREIGN KEY\s*\((.+)\)\s*REFERENCES\s*`([^`]+)`\s*\((.+)\)\s*(?:ON DELETE (CASCADE|NO ACTION|RESTRICT|SET NULL))?\s*(?:ON UPDATE (CASCADE|NO ACTION|RESTRICT|SET NULL))?/', $row, $matches)) {
                    // foreign key
                    $this->processForeignKeyRow($table, $matches);
                } else if (preg_match('/\s*`([^`]+)`\s+([a-zA-Z0-9\(\)]+)(?:\s+(.*))?,?/', $row, $matches)) {
                    // field
                    $this->processFieldRow($table, $matches);
                }
            }
            foreach($table['primaryKey'] as $field) {
                $table['columns'][$field]['primary'] = 1;
                unset($table['columns'][$field]['notnull']);
            }
            if (isset($table['relations']) && is_array($table['relations'])) {
                foreach($table['relations'] as $rel) {
                    if(isset($table['indexes'][$rel['local']]) && count($table['indexes'][$rel['local']]['fields']) == 1) {
                        unset($table['indexes'][$rel['local']]);
                    }
                }
            }
            $table['create'] = $create['Create Table'];
            $schema[$tableName] = $table;
        }
        return $schema;
    }

    protected function processIndexRow(&$table, $matches, $isUnique) {
        $fields = explode(',', trim(str_replace('`', '', $matches[2])));
        if (count($fields) < 1) {
            // ERROR
            continue;
        }
        foreach($fields as $key=>$field) {
            $fields[$key] = trim($field);
        }
        $table['indexes'][$matches[1]] = array(
            'fields' => $fields,
        );
        if($isUnique) {
            $table['indexes'][$matches[1]]['type'] = 'unique';
        }

    }

    protected function processFieldRow(&$table, $matches) {
        $name = $matches[1];
        $typeStr = $matches[2];
        $opts = preg_replace('/\s*,$/', '', trim($matches[3]));
        preg_match('/^([a-zA-Z0-9]+)(?:\((\d+)\))?$/', $typeStr, $typeMatches);
        $column = $this->getGenericType($typeMatches[1], $typeMatches[2]);
        if (strstr(strtolower($opts), 'auto_increment')) {
            $column['autoincrement'] = 1;
            $opts = str_ireplace('auto_increment', '', $opts);
        }
        if (strstr($opts, 'NOT NULL')) {
            $column['notnull'] = 1;
            $opts = str_replace('NOT NULL', '', $opts);
        }
        if (preg_match('/default\s+(.+)$/', trim($opts), $optMatches)) {
            $defVal = $optMatches[1];
            if ('NULL' == trim($defVal)) {
                $column['default'] = null;
            } else {
                $column['default'] = $defVal;
            }
        }
        $table['columns'][$name] = $column;
    }

    protected function processForeignKeyRow(&$table, $matches) {
        $local = explode(',', trim(str_replace('`', '', $matches[2])));
        if (count($local) < 1) {
            // ERROR
            continue;
        } else if (count($local) == 1) {
            $local = reset($local);
        }
        $foreign = explode(',', trim(str_replace('`', '', $matches[4])));
        if (count($foreign) < 1) {
            // ERROR
            continue;
        } else if (count($foreign) == 1) {
            $foreign = reset($foreign);
        }
        $modelName = sys_cache_DbSchema::makeModelName($matches[3]);
        $relName = $modelName;
        $counter = 1;
        while(isset($table['relations'][$relName])) {
            $counter++;
            $relName = $modelName.'_'.$counter;
        }
        $table['relations'][$relName] = array(
            'table' => $matches[3],
            'class' => sys_cache_DbSchema::makeModelName($matches[3]),
            'local' => $local,
            'foreign' => $foreign,
            'type' => 'one',
            'keyName' => $matches[1],
        );
        if ($matches[5]) {
            $table['relations'][$relName]['onDelete'] = $matches[5];
        }
        if ($matches[6]) {
            $table['relations'][$relName]['onUpdate'] = $matches[6];
        }
    }

    protected function processPrimaryKeyRow(&$table, $matches) {
        $fields = explode(',', trim(str_replace('`', '', $matches[1])));
        if (count($fields) < 1) {
            // ERROR
            continue;
        }
        foreach($fields as $key=>$field) {
            $fields[$key] = trim($field);
        }
        $table['primaryKey'] = $fields;
    }


    public function getGenericType($type, $length = null) {
        $col = array();
        switch($type) {
            case 'timestamp':
                // fall through
            case 'datetime':
                $col['type'] = 'timestamp';
                break;
            case 'date':
                $col['type'] = 'date';
                break;
            case 'time':
                $col['type'] = 'time';
            case 'varchar':
                $col['type'] = 'string';
                $col['length'] = $length;
                $col['fixed'] = false;
                break;
            case 'char':
                $col['type'] = 'string';
                $col['length'] = $length;
                $col['fixed'] = true;
                break;
            case 'text':
                $col['type'] = 'string';
                $col['length'] = null;
                $col['fixed'] = false;
                break;
            case 'tinytext':
                // same as longtext
            case 'mediumtext':
                // same as longtext
            case 'longtext':
                $col['type'] = 'clob';
                $col['length'] = $length;
                break;
            case 'double':
                $col['type'] = 'double';
                $col['length'] = null;
                break;
            case 'tinyint':
                $col['type'] = 'integer';
                $col['length'] = 1;
                break;
            case 'smallint':
                $col['type'] = 'integer';
                $col['length'] = 2;
                break;
            case 'mediumint':
                $col['type'] = 'integer';
                $col['length'] = 3;
                break;
            case 'int':
                $col['type'] = 'integer';
                $col['length'] = 4;
                break;
            case 'bigint':
                $col['type'] = 'integer';
                $col['length'] = null;
                break;
            case 'tinyblob':
                // same as longblob
            case 'blob':
                // same as longblob
            case 'mediumblob':
                // same as longblob
            case 'longblob':
                $col['type'] = 'blob';
                $col['length'] = $length;
                break;
            default:
                return array();
        }
        return $col;
    }

    protected function _getSimpleGenericType(array $type) {
        if ($type['primary'] && isset($type['notnull'])) {
            unset($type['notnull']);
        }
        switch($type['type']) {
            case 'timestamp':
                $type['length'] = null;
                break;
            case 'double':
            case 'float':
                $type['type'] = 'double';
                $type['length'] = null;
                break;
            case 'clob':
                if ($type['length'] > 255 && $type['length'] <= 65532) {
                    $type['type'] = 'string';
                    $type['length'] = null;
                }
                break;
           case 'gzip':
               $type['type'] = 'blob';
               $type['length'] = null;
               break;
           case 'array':
           case 'object':
               $type['type'] = 'string';
               $type['length'] = null;
               break;
           case 'integer':
               if ($type['length']>4) {
                   $type['length'] = null;
               }
               break;
            case 'string':
                if ($type['length'] > 255) {
                    $type['length'] = null;
                }
                break;
            case 'boolean':
                $type['type'] = 'integer';
                $type['length'] = 1;
                break;
        }
        return $type;
    }

    protected function _getAddColumnSql($model, $fieldName, $tableName, $afterColumn) {
        $sql = 'ALTER TABLE '.$this->_db->quoteField($tableName).' ADD '
                .$this->_db->quoteField($fieldName).' '
                .$this->getNativeType($model);
        if ($model['notnull'] || $model['primary']) {
            $sql .= ' NOT NULL';
        } else {
            $sql .= ' NULL';
        }
        if (isset($model['default']) && !is_null($model['default'])) {
            $sql .= ' DEFAULT '.$this->_db->getQuotedVal($model['default'], $model['type']);
        }
        if (is_null($afterColumn)) {
            $sql .= ' FIRST';
        } else {
            $sql .= ' AFTER '.$afterColumn;
        }
      return $sql.';';
    }

    protected function _getAlterColumnSql($model, $fieldName, $tableName) {
        $sql = 'ALTER TABLE '.$this->_db->quoteField($tableName).' CHANGE '
                .$this->_db->quoteField($fieldName).' '
                .$this->_db->quoteField($fieldName).' '
                .$this->getNativeType($model);
        if ($model['notnull'] || $model['primary']) {
            $sql .= ' NOT NULL';
        } else {
            $sql .= ' NULL';
        }
        if ($model['primary'] && $model['autoincrement']) {
            $sql .= ' auto_increment';
        }
        if (isset($model['default']) && !is_null($model['default'])) {
            $sql .= ' DEFAULT '.$this->_db->getQuotedVal($model['default'], $model['type']);
        }
      return $sql.';';
    }

    protected function _getDropColumnSql($fieldName, $tableName) {
        return 'ALTER TABLE '.$this->_db->quoteField($tableName).' DROP '.$this->_db->quoteField($fieldName).';';
    }

    protected function _getAddIndexSql($model, $indexName, $tableName) {
        $sql = 'ALTER TABLE '.$this->_db->quoteField($tableName);
        if ($model['type'] == 'unique') {
            $sql .= ' ADD UNIQUE';
        } else {
            $sql .= ' ADD INDEX';
        }
        $sql .= ' '.$this->_db->quoteField($indexName);
        foreach($model['fields'] as $key=>$field) {
            $model['fields'][$key] = $this->_db->quoteField(trim($field));
        }
        $sql .= ' ('.implode(', ', $model['fields']).')';
        return $sql.';';
    }

    protected function _getAlterIndexSql($model, $indexName, $tableName) {
        $sql = 'ALTER TABLE '.$this->_db->quoteField($tableName)
            .' DROP INDEX '.$this->_db->quoteField($indexName).',';

        if ($model['type'] == 'unique') {
            $sql .= ' ADD UNIQUE';
        } else {
            $sql .= ' ADD INDEX';
        }
        $sql .= ' '.$this->_db->quoteField($indexName);
        foreach($model['fields'] as $key=>$field) {
            $model['fields'][$key] = $this->_db->quoteField(trim($field));
        }
        $sql .= ' ('.implode(', ', $model['fields']).')';
        return $sql.';';
    }

    protected function _getDropIndexSql($indexName, $tableName) {
        return 'ALTER TABLE '.$this->_db->quoteField($tableName)
            .' DROP INDEX '.$this->_db->quoteField($indexName).';';
    }

    protected function _getAlterPrimaryKeySql($fields, $tableName, $hasCurrent) {
        $sql = 'ALTER TABLE '.$this->_db->quoteField($tableName);
        if ($hasCurrent) {
            $sql .= ' DROP PRIMARY KEY,';
        }
        $sql .= ' ADD PRIMARY KEY';
        foreach($fields as $key=>$field) {
            $model['fields'][$key] = $this->_db->quoteField(trim($field));
        }
        $sql .= ' ('.implode(', ', $fields).')';
        return $sql.';';
        
    }

    protected function _getDropPrimaryKeySql($tableName) {
        return 'ALTER TABLE '.$this->_db->quoteField($tableName).' DROP PRIMARY KEY;';
    }

    protected function _getAddRelationSql($model, $tableName) {
        $sql = 'ALTER TABLE '.$this->_db->quoteField($tableName)
            .' ADD FOREIGN KEY ('.$this->_db->quoteField($model['local'])
            .') REFERENCES '.$this->_db->quoteField($model['table'])
            .' ('.$this->_db->quoteField($model['foreign']).')';
        if($model['onDelete']) {
            $sql .= ' ON DELETE '.$model['onDelete'];
        }
        if ($model['onUpdate']) {
            $sql .= ' ON UPDATE '.$model['onUpdate'];
        }
        return $sql.';';
    }

    protected function _getDropRelationSql($relName, $tableName) {
        return 'ALTER TABLE '.$this->_db->quoteField($tableName)
            .' DROP FOREIGN KEY '.$this->_db->quoteField($relName).';';
    }
}
?>