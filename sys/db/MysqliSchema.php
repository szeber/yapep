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
class sys_db_MysqliSchema
{

    /**
     * @var sys_db_Database
     */
    protected $_db;

    public function __construct (sys_db_Database $db)
    {
        $this->_db = $db;
    }

    public function exportTables (array $models)
    {
        sort($models);
        $this->_db->execute("SET FOREIGN_KEY_CHECKS = 0;");
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
                    if (array_key_exists(
                        'default',
                        $field)) {
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
}
?>