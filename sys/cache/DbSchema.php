<?php

/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	Cache
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Database model cache manager
 *
 * @package	YAPEP
 * @subpackage	Cache
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class sys_cache_DbSchema extends sys_cache_DummyCacheManager
{
    protected $_yamlData;

    /**
     * @see sys_cache_CacheManager::cacheEnabled()
     *
     * @return boolean
     */
    public function cacheEnabled ()
    {
        // Schema cache is always enabled
        return true;
    }

    /**
     * @see sys_cache_DummyCacheManager::recreateCache()
     *
     */
    public function recreateCache ()
    {
        $this->clearCache();
        if (!file_exists($this->getCachePath())) {
            mkdir($this->getCachePath(),0775);
        }
        $cacheDir = $this->getCachePath().'YAPEP/';
        $doctrineCacheDir = $this->getCachePath().'Doctrine/';
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir,0775);
        }
        if (!file_exists($doctrineCacheDir)) {
            mkdir($doctrineCacheDir,0775);
        }
        $this->loadYamlSchema();
        $tables = array_keys($this->_yamlData);
        foreach($tables as $table) {
            $model = $this->processTable($table);
            file_put_contents($cacheDir.$model['tableName'].'.php', "<?php\n\$tableData = ".var_export($model, true).';');
        }
        sys_LibFactory::getDbConnection();
        Doctrine::generateModelsFromYaml(array(SYS_PATH.'models/', PROJECT_PATH.'models/'), $doctrineCacheDir, array('generateBaseClasses' => false));
        $dir = opendir($doctrineCacheDir);
        while (false !== ($file = readdir($dir))) {
            if (!is_file($doctrineCacheDir.$file)) {
                continue;
            }
            if (!preg_match('/^(.+)(\.php)$/', $file)) {
                continue;
            }
            // FIXME Remove hack
            if ('ObjectData.php' == $file) {
                $objectData = file_get_contents($doctrineCacheDir.$file);
                $objectData = preg_replace('/^.*?new sys_db_ObjectListener.*?$/m', '', $objectData);
                $objectData = preg_replace('/^}\s*$/m', '', $objectData);
                $objectData.= "\n\n  public function construct() {\n    \$this->addListener(new sys_db_ObjectListener());\n  }\n}";
                file_put_contents($doctrineCacheDir.$file, $objectData);
            }
            file_put_contents($doctrineCacheDir.$file, preg_replace('/^\s*parent::.*$/m', '', file_get_contents($doctrineCacheDir.$file)));
        }
        closedir($dir);
    }

    /**
     * @see sys_cache_DummyCacheManager::getCachePath()
     *
     * @return string
     */
    protected function getCachePath ()
    {
        return CACHE_DIR.'dbSchema/';
    }

    public static function makeTableName($tableName) {
        return strtolower(preg_replace_callback('/(?<!^)[A-Z]/', create_function('$matches', 'return "_".$matches[0];'), $tableName));
    }

    protected function processTable($tableName) {
        $typeParts = array();
        $table = $this->_yamlData[$tableName];
        if (!$table['tableName']) {
            $table['tableName'] = $this->makeTableName($tableName);
        }
        if (!is_array($table['columns'])) {
            $table['columns'] = array();
        }
        if (is_array($table['actAs'])) {
            if (isset($table['actAs']['Timestampable'])) {
                $table['columns']['created_at'] = array('type'=>'timestamp');
                $table['columns']['updated_at'] = array('type'=>'timestamp');
            }
        }
        $primaryKeys = array();
        foreach($table['columns'] as $columnName=>$column) {
            if (!is_array($column)) {
                $column = array('type'=>$column);
                $table['columns'][$columnName] = $column;
            }
            if (preg_match('/^([^ \)]+)\s*\(([ 0-9]+)\)/', $column['type'], $typeParts)) {
                $table['columns'][$columnName]['type'] = $typeParts[1];
                $table['columns'][$columnName]['length'] = $typeParts[2];
            } else if (preg_match('/^([^ \)]+)\s*\(notnull\)/', $column['type'], $typeParts)) {
                $table['columns'][$columnName]['type'] = $typeParts[1];
                $table['columns'][$columnName]['length'] = null;
                $table['columns'][$columnName]['notnull'] = true;
            }
            if (!isset($table['columns'][$columnName]['length'])) {
                $table['columns'][$columnName]['length'] = null;
            }
        }
        if (!is_array($table['listeners'])) {
            $table['listeners'] = array();
        }
        if (is_array($table['inheritance'])) {
            $parentTable = $this->processTable($table['inheritance']['extends']);
            $table['inheritance']['extendsTable'] = $this->makeTableName($table['inheritance']['extends']);
            $table['columns'] = array_merge($table['columns'], $parentTable['columns']);
            $table['listeners'] = array_merge($table['listeners'], $parentTable['listeners']);
        }
        if (is_array($table['relations'])) {
            foreach($table['relations'] as $relationName=>$relation) {
                $table['relations'][$relationName]['table'] = $this->makeTableName($relation['class']);
            }
        }
        foreach($table['columns'] as $columnName=>$column) {
            if (isset($column['primary']) && $column['primary']) {
                $primaryKeys[] = $columnName;
            }
        }
        if (!count($primaryKeys)) {
            $primaryKeys[] = 'id';
            if (!$table['columns']['id']) {
                $table['columns']['id'] = array('type'=>'integer', 'length'=>null, 'autoincrement'=>true);
            }
            $table['colunms']['id']['primary'] = true;
        }
        if (count($table['listeners'])) {
            foreach($table['listeners'] as $key=>$listener) {
                $table['listeners'][$key] = trim($listener);
            }
            $table['listeners'] = array_unique($table['listeners']);
        }
        $table['primaryKey'] = $primaryKeys;
        return $table;
    }

    protected function loadYamlSchema() {
        require_once LIB_DIR.'spyc/spyc.php';
        $yamlString = '';
        $dir = opendir(SYS_PATH.'models/');
        while (false !== ($file = readdir($dir))) {
            if (!is_file(SYS_PATH.'models/'.$file)) {
                continue;
            }
            if (!preg_match('/^(.+)(\.ya?ml)$/', $file)) {
                continue;
            }
            $yamlString .= file_get_contents(SYS_PATH.'models/'.$file)."\n";
        }
        closedir($dir);
        if (SYS_PATH != PROJECT_PATH) {
            $dir = opendir(PROJECT_PATH.'models/');
            while (false !== ($file = readdir($dir))) {
                if ('.' == $file || '..' == $file) {
                    continue;
                }
                if (!is_file(PROJECT_PATH.'models/'.$file)) {
                    continue;
                }
                if (!preg_match('/^(.+)(\.ya?ml)$/', $file)) {
                    continue;
                }
                $yamlString .= file_get_contents(PROJECT_PATH.'models/'.$file);
            }
            closedir($dir);
        }
        $this->_yamlData = Spyc::YAMLLoadString($yamlString);
    }

}
?>