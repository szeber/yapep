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
 * Main administration module
 *
 * @package	YAPEP
 * @subpackage	AdminModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_admin_Main extends sys_admin_AdminModule
{

    /**
     * @see sys_admin_AdminModule::buildForm()
     *
     */
    protected function buildForm ()
    {

        $this->setName('');
        $this->setRootForm();
        $this->disableFormTag();
        $this->options['PHPSESSID'] = session_id();

        $control = new sys_admin_control_BrowserForm();
        $control->setTitle(_('Content manager'));
        $control->setType('ContentManager');
        $control->setForms(array('tree' => 'folder_Tree' , 'treeProp' => 'folder_Prop'));
        $this->addControl($control, 'browser');

        $control = new sys_admin_control_BrowserForm();
        $control->setTitle(_('Asset manager'));
        $control->setType('AssetManager');
        $control->setForms(array('tree' => 'asset_Tree' , 'treeProp' => 'asset_Prop'));
        $this->addControl($control, 'assetbrowser');

        $control = new sys_admin_control_BrowserForm();
        $control->setTitle(_('Page manager'));
        $control->setType('ObjectBrowser');
        $control->setForms(
            array('tree' => 'page_Tree' , 'treeProp' => 'page_Prop' ,
            'editor' => 'page_Editor'));
        $this->addControl($control, 'template');

        $control = new sys_admin_control_PopupFormButton();
        $control->setTargetForm('cms_Recache');
        $control->setLabel(_('Recache'));
        $this->addControl($control, 'recache');

        $control = new sys_admin_control_LocaleSelect();
        $control->setType('site');
        $control->setLabel(_('Site locale'));
        $control->addOptions($this->getLocaleList(false));
        $control->setDefaultValue($this->locale);
        $this->addControl($control, 'siteLocale');

        $control = new sys_admin_control_LocaleSelect();
        $control->setType('admin');
        $control->setLabel(_('Admin locale'));
        $control->setDefaultValue($this->manager->getAdminLocale());
        $control->addOptions($this->getLocaleList(true));
        $this->addControl($control, 'adminLocale');

        $control = new sys_admin_control_Label();
        $control->setLabel(_('Logged in as'));
        $control->setDefaultValue($_SESSION['LoggedInAdminData']['name']);
        $this->addControl($control, 'loggedInLabel');

        $control = new sys_admin_control_PopupFormButton();
        $control->setLabel(_('Logout'));
        $control->setTargetForm('Logout');
        $this->addControl($control, 'logoutButton');

    }

    /**
     * Returns the list of locales or admin locales
     *
     * @param boolean $admin
     */
    protected function getLocaleList ($admin)
    {
        $handler = getPersistClass('LangLocale');
        if ($admin) {
            $data = $handler->getAdminLocales();
        } else {
            $data = $handler->getLocales();
        }
        $locales = array();
        foreach ($data as $val) {
            $locales[$val['locale_code']] = $val['name'];
        }
        return $locales;
    }

    protected function doSave ()
    {
        return '';
    }
}
?>