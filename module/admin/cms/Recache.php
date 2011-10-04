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
 * Cache recreation module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_cms_Recache extends sys_admin_AdminModule
{

    protected function buildForm ()
    {

        $this->setTitle(_('Recache'));

        $this->setAddBtnDisabled();
        $this->setDeleteBtnDisabled();
        $this->setSaveBtnText(_('Recache'));

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Configuration data cache'));
        $control->setValue(true);
        $this->addControl($control, 'appConfig');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Database schema'));
        $control->setValue(true);
        $this->addControl($control, 'schema');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Module cache'));
        $control->setValue(true);
        $this->addControl($control, 'module');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Page cache'));
        $control->setValue(true);
        $this->addControl($control, 'page');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Poeditor cache'));
        $control->setValue(true);
        $this->addControl($control, 'poeditor');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Folder cache'));
        $control->setValue(true);
        $this->addControl($control, 'folder');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('System configuration cache'));
        $control->setValue(true);
        $this->addControl($control, 'sysconfig');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Database cache'));
        $this->addControl($control, 'db');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Smarty cache'));
        $this->addControl($control, 'smarty');

        $control = new sys_admin_control_CheckBox();
        $control->setLabel(_('Smarty compile cache'));
        $this->addControl($control, 'smartyCompile');

    }

    protected function doSave ()
    {
        $data = $this->panel->getInputValues();
        if ($data['schema']) {
            $cache = new sys_cache_DbSchema();
            $cache->recreateCache();
        }
        if ($data['appConfig']) {
            $this->config->recreateCache();
        }
        if ($data['module']) {
            $cache = new sys_cache_ModuleCacheManager();
            $cache->recreateCache();
        }
        if ($data['page']) {
            $cache = new sys_cache_PageCacheManager();
            $cache->recreateCache();
        }
        if ($data['poeditor']) {
            $cache = new sys_cache_PoeditorCacheManager();
            $cache->recreateCache();
        }
        if ($data['folder']) {
            $cache = new sys_cache_FolderCacheManager();
            $cache->recreateCache();
        }
        if ($data['sysconfig']) {
            $cache = new sys_cache_SysConfigCacheManager();
            $cache->recreateCache();
        }
        if ($data['db']) {
            $cache = new sys_cache_DbCacheManager();
            $cache->recreateCache();
        }
        if ($data['smarty']) {
            $cache = new sys_cache_SmartyCacheManager();
            $cache->recreateCache();
        }
        if ($data['smartyCompile']) {
            $cache = new sys_cache_SmartyCompileCacheManager();
            $cache->recreateCache();
        }
    }

    protected function doLoad ()
    {}
}
?>