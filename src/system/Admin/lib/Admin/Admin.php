<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2002, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Admin
 */

class Admin_Admin extends Zikula_Controller
{
    /**
     * the main administration function
     * This function is the default function, and is called whenever the
     * module is initiated without defining arguments.  As such it can
     * be used for a number of things, but most commonly it either just
     * shows the module menu and returns or calls whatever the module
     * designer feels should be the default function (often this is the
     * view() function)
     * @author Mark West
     * @return string HTML string
     */
    public function main()
    {
        // Security check will be done in view()
        return $this->view();
    }

    /**
     * Add a new admin category
     * This is a standard function that is called whenever an administrator
     * wishes to create a new module item
     * @author Mark West
     * @return string HTML string
     */
    public function newcat()
    {
        if (!SecurityUtil::checkPermission('Admin::Item', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        $renderer = Renderer::getInstance('Admin', false);

        // Return the output that has been generated by this function
        return $renderer->fetch('admin_admin_newcat.htm');
    }

    /**
     * This is a standard function that is called with the results of the
     * form supplied by admin_admin_new() to create a new category
     * @author Mark West
     * @see Admin_admin_new()
     * @param string $args['catname'] the name of the category to be created
     * @param string $args['description'] the description of the category to be created
     * @return mixed category id if create successful, false otherwise
     */
    public function create($args)
    {
        $category = FormUtil::getPassedValue('category', isset($args['category']) ? $args['category'] : null, 'POST');

        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Admin', 'admin', 'view'));
        }

        $cid = ModUtil::apiFunc('Admin', 'admin', 'create',
                array('catname' => $category['catname'],
                'description' => $category['description']));

        if ($cid != false) {
            // Success
            LogUtil::registerStatus($this->__('Done! Created new category.'));
        }

        return System::redirect(ModUtil::url('Admin', 'admin', 'view'));
    }

    /**
     * Modify a category
     * This is a standard function that is called whenever an administrator
     * wishes to modify an admin category
     * @author Mark West
     * @param int $args['cid'] category id
     * @param int $args['objectid'] generic object id maps to cid if present
     * @return string HTML string
     */
    public function modify($args)
    {
        $cid = FormUtil::getPassedValue('cid', isset($args['cid']) ? $args['cid'] : null, 'GET');
        $objectid = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'GET');

        if (!empty($objectid)) {
            $cid = $objectid;
        }

        $renderer = Renderer::getInstance('Admin', false);

        $category = ModUtil::apiFunc('Admin', 'admin', 'get', array('cid' => $cid));

        if ($category == false) {
            return LogUtil::registerError($this->__('Error! No such category found.'), 404);
        }

        if (!SecurityUtil::checkPermission('Admin::Category', "$category[catname]::$cid", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $renderer->assign('category', $category);
        return $renderer->fetch('admin_admin_modify.htm');
    }

    /**
     * This is a standard function that is called with the results of the
     * form supplied by template_admin_modify() to update a current item
     * @author Mark West
     * @see Admin_admin_modify()
     * @param int $args['cid'] the id of the item to be updated
     * @param int $args['objectid'] generic object id maps to cid if present
     * @param string $args['catname'] the name of the category to be updated
     * @param string $args['description'] the description of the item to be updated
     * @return bool true if update successful, false otherwise
     */
    public function update($args)
    {
        $category = FormUtil::getPassedValue('category', isset($args['category']) ? $args['category'] : null, 'POST');
        if (!empty($category['objectid'])) {
            $category['cid'] = $category['objectid'];
        }

        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Admin', 'admin', 'view'));
        }

        if (ModUtil::apiFunc('Admin', 'admin', 'update',
        array('cid' => $category['cid'],
        'catname' => $category['catname'],
        'description' => $category['description']))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Saved category.'));
        }

        return System::redirect(ModUtil::url('Admin', 'admin', 'view'));
    }

    /**
     * delete item
     * This is a standard function that is called whenever an administrator
     * wishes to delete a current module item.  Note that this function is
     * the equivalent of both of the modify() and update() functions above as
     * it both creates a form and processes its output.  This is fine for
     * simpler functions, but for more complex operations such as creation and
     * modification it is generally easier to separate them into separate
     * functions.  There is no requirement in the Zikula MDG to do one or the
     * other, so either or both can be used as seen appropriate by the module
     * developer
     * @author Mark West
     * @param int $args['cid'] the id of the category to be deleted
     * @param int $args['objectid'] generic object id maps to cid if present
     * @param bool $args['confirmation'] confirmation that this item can be deleted
     * @return mixed HTML string if confirmation is null, true if delete successful, false otherwise
     */
    public function delete($args)
    {
        $cid = FormUtil::getPassedValue('cid', isset($args['cid']) ? $args['cid'] : null, 'REQUEST');
        $objectid = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'REQUEST');
        $confirmation = FormUtil::getPassedValue('confirmation', null, 'POST');
        if (!empty($objectid)) {
            $cid = $objectid;
        }

        $category = ModUtil::apiFunc('Admin', 'admin', 'get', array('cid' => $cid));

        if ($category == false) {
            return LogUtil::registerError($this->__('Error! No such category found.'), 404);
        }

        if (!SecurityUtil::checkPermission('Admin::Category', "$category[catname]::$cid", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        // Check for confirmation.
        if (empty($confirmation)) {
            // No confirmation yet - display a suitable form to obtain confirmation
            // of this action from the user
            $renderer = Renderer::getInstance('Admin', false);
            $renderer->assign('cid', $cid);
            return $renderer->fetch('admin_admin_delete.htm');
        }

        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Admin', 'admin', 'view'));
        }

        if (ModUtil::apiFunc('Admin', 'admin', 'delete', array('cid' => $cid))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Category deleted.'));
        }

        return System::redirect(ModUtil::url('Admin', 'admin', 'view'));
    }

    /**
     * View all admin categories
     * @author Mark West
     * @param int $startnum the starting id to view from - optional
     * @return string HTML string
     */
    public function view($args = array())
    {
        if (!SecurityUtil::checkPermission('Admin::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $startnum = FormUtil::getPassedValue('startnum', isset($args['startnum']) ? $args['startnum'] : null, 'GET');

        $renderer = Renderer::getInstance('Admin', false);

        $categoryArray = ModUtil::apiFunc('Admin', 'admin', 'getall',
                array('startnum' => $startnum,
                'numitems' => $this->getVar('itemsperpage')));

        $categories = array();
        foreach ($categoryArray as $category)
        {
            if (SecurityUtil::checkPermission('Admin::', "$category[catname]::$category[cid]", ACCESS_READ)) {
                // Options for the item.
                $options = array();

                if (SecurityUtil::checkPermission('Admin::', "$category[catname]::$category[cid]", ACCESS_EDIT)) {
                    $options[] = array('url' => ModUtil::url('Admin', 'admin', 'modify', array('cid' => $category['cid'])),
                            'image' => 'xedit.gif',
                            'title' => $this->__('Edit'));

                    if (SecurityUtil::checkPermission('Admin::', "$category[catname]::$category[cid]", ACCESS_DELETE)) {
                        $options[] = array('url' => ModUtil::url('Admin', 'admin', 'delete', array('cid' => $category['cid'])),
                                'image' => '14_layer_deletelayer.gif',
                                'title' => $this->__('Delete'));
                    }
                }
                $category['options'] = $options;
                $categories[] = $category;
            }
        }
        $renderer->assign('categories', $categories);

        $renderer->assign('pager', array('numitems' => ModUtil::apiFunc('Admin', 'admin', 'countitems'),
                'itemsperpage' => $this->getVar('itemsperpage')));

        // Return the output that has been generated by this function
        return $renderer->fetch('admin_admin_view.htm');
    }

    /**
     * Display main admin panel for a category
     * @author Mark West
     * @param int $args['acid'] the id of the category to be displayed
     * @return string HTML string
     */
    public function adminpanel($args)
    {
        if (!SecurityUtil::checkPermission('::', '::', ACCESS_EDIT)) {
            // suppress admin display - return to index.
            return System::redirect(System::getHomepageUrl());
        }

        // Create output object
        $renderer = Renderer::getInstance('Admin', false);

        if (!$this->getVar('ignoreinstallercheck') && System::getVar('development') == 0) {
            // check if the Zikula Recovery Console exists
            $zrcexists = file_exists('zrc.php');
            // check if upgrade scripts exist
            if ($zrcexists == true) {
                $renderer->assign('zrcexists', $zrcexists);
                $renderer->assign('adminpanellink', ModUtil::url('Admin','admin', 'adminpanel'));
                return $renderer->fetch('admin_admin_warning.htm');
            }
        }

        // Now prepare the display of the admin panel by getting the relevant info.

        // Get parameters from whatever input we need.
        $acid = FormUtil::getPassedValue('acid', (isset($args['acid']) ? $args['acid'] : null), 'GET');

        // cid isn't set, so we check the last session var lastcid to see where the admin has been before.
        if (empty($acid)) {
            $acid = SessionUtil::getVar('lastacid');
            if (empty($acid)) {
                // cid is still not set, go to the default category
                $acid = $this->getVar('startcategory');
            }
        }

        // now we know where we are or where the admin wants us to go to, lets store it in a
        // session var for later use
        SessionUtil::setVar('lastacid', $acid);

        // Add category menu to output
        $renderer->assign('menu', $this->categorymenu(array('acid' => $acid)));

        // Admin_admin_categorymenu may have changed the acid. In this case it has been
        // stored to lastacid so we need to read it again now
        $acid = SessionUtil::getVar('lastacid');

        // Handle the case where the current/default category does not contain any accessible items
        // (the current user may just have admin access to a single module)
        if (empty($acid)) {
            $acid = $this->getVar('startcategory');
        }

        // Check to see if we have access to the requested category.
        if (!SecurityUtil::checkPermission("Admin::", "::$acid", ACCESS_ADMIN)) {
            $acid = -1;
        }

        // Get Details on the selected category
        if ($acid > 0) {
            $category = ModUtil::apiFunc('Admin', 'admin', 'get', array('cid' => $acid));
        } else {
            $category = null;
        }
        if (!$category) {
            // get the default category
            $acid = $this->getVar('startcategory');

            // Check to see if we have access to the requested category.
            if (!SecurityUtil::checkPermission("Admin::", "::$acid", ACCESS_ADMIN)) {
                return LogUtil::registerPermissionError(System::getHomepageUrl());
            }

            $category = ModUtil::apiFunc('Admin', 'admin', 'get', array('cid' => $acid));
        }

        // assign the category
        $renderer->assign('category', $category);

        // assign all module vars
        $renderer->assign('modvars', ModUtil::getVar('Admin'));

        $displayNameType = $this->getVar('displaynametype', 1);

        // get admin capable modules
        $adminmodules = ModUtil::getAdminMods();
        $adminlinks = array();
        foreach ($adminmodules as $adminmodule) {
            if (SecurityUtil::checkPermission("{$adminmodule['name']}::", 'ANY', ACCESS_EDIT)) {
                $catid = ModUtil::apiFunc('Admin', 'admin', 'getmodcategory',
                        array('mid' => ModUtil::getIdFromName($adminmodule['name'])));

                if (($catid == $acid) || (($catid == false) && ($acid == $this->getVar('defaultcategory')))) {
                    $modinfo = ModUtil::getInfo(ModUtil::getIdFromName($adminmodule['name']));
                    $menutexturl = ModUtil::url($modinfo['name'], 'admin');
                    $modpath = ($modinfo['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';

                    if ($displayNameType == 1) {
                        $menutext = $modinfo['displayname'];
                    } elseif ($displayNameType == 2) {
                        $menutext = $modinfo['name'];
                    } elseif ($displayNameType == 3) {
                        $menutext = $modinfo['displayname'] . ' (' . $modinfo['name'] . ')';
                    }
                    $menutexttitle = $modinfo['description'];

                    $osmoddir = DataUtil::formatForOS($modinfo['directory']);
                    $adminicons = array($modpath . '/' . $osmoddir . '/images/admin.png',
                            $modpath . '/' . $osmoddir . '/images/admin.jpg',
                            $modpath . '/' . $osmoddir . '/images/admin.gif',
                            $modpath . '/' . $osmoddir . '/pnimages/admin.gif',
                            $modpath . '/' . $osmoddir . '/pnimages/admin.jpg',
                            $modpath . '/' . $osmoddir . '/pnimages/admin.jpeg',
                            $modpath . '/' . $osmoddir . '/pnimages/admin.png',
                            'system/Admin/images/default.gif');

                    foreach ($adminicons as $adminicon) {
                        if (is_readable($adminicon)) {
                            break;
                        }
                    }

                    $adminlinks[] = array('menutexturl' => $menutexturl,
                            'menutext' => $menutext,
                            'menutexttitle' => $menutexttitle,
                            'modname' => $modinfo['name'],
                            'adminicon' => $adminicon,
                            'id' => $modinfo['id']);
                }
            }
        }
        $renderer->assign('adminlinks', $adminlinks);

        // work out what stylesheet is being used to render to the admin panel
        $css = $this->getVar('modulestylesheet');
        $cssfile = explode('.', $css);

        // Return the output that has been generated by this function
        if ($renderer->template_exists('admin_admin_adminpanel_'.$cssfile[0].'.htm')) {
            return $renderer->fetch('admin_admin_adminpanel_'.$cssfile[0].'.htm');
        } else {
            return $renderer->fetch('admin_admin_adminpanel.htm');
        }
    }

    /**
     * This is a standard function to modify the configuration parameters of the
     * module
     * @author Mark West
     * @return string HTML string
     */
    public function modifyconfig()
    {
        if (!SecurityUtil::checkPermission('Admin::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Create output object
        $renderer = Renderer::getInstance('Admin', false);

        // get admin capable mods
        $adminmodules = ModUtil::getAdminMods();

        // Get all categories
        $categories = ModUtil::apiFunc('Admin', 'admin', 'getall');
        $renderer->assign('categories', $categories);

        // assign all the module vars
        $renderer->assign('modvars', ModUtil::getVar('Admin'));

        $modulecategories = array();
        foreach ($adminmodules as $adminmodule)
        {
            // Get the category assigned to this module
            $category = ModUtil::apiFunc('Admin', 'admin', 'getmodcategory',
                    array('mid' => ModUtil::getIdFromName($adminmodule['name'])));

            if ($category === false) {
                // it's not set, so we use the default category
                $category = $this->getVar('defaultcategory');
            }
            // output module category selection
            $modulecategories[] = array('displayname' => $adminmodule['displayname'],
                    'name' => $adminmodule['name'],
                    'category' => $category);
        }
        $renderer->assign('modulecategories', $modulecategories);

        // Return the output that has been generated by this function
        return $renderer->fetch('admin_admin_modifyconfig.htm');
    }

    /**
     * This is a standard function to update the configuration parameters of the
     * module given the information passed back by the modification form
     * @author Mark West
     * @see Admin_admin_modifyconfig()
     * @param int $modulesperrow the number of modules to display per row in the admin panel
     * @param int $admingraphic switch for display of admin icons
     * @param int $modulename,... the id of the category to set for each module
     * @return string HTML string
     */
    public function updateconfig()
    {
        if (!SecurityUtil::checkPermission('Admin::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Admin', 'admin', 'view'));
        }

        // get module vars
        $modvars = FormUtil::getPassedValue('modvars', null, 'POST');

        // check module vars
        $modvars['modulesperrow'] = isset($modvars['modulesperrow']) ? $modvars['modulesperrow'] : 5;
        if (!is_numeric($modvars['modulesperrow'])) {
            unset($modvars['modulesperrow']);
            LogUtil::registerError($this->__("Error! You must enter a number for the 'Modules per row' setting."));
        }
        $modvars['ignoreinstallercheck'] = isset($modvars['ignoreinstallercheck']) ? $modvars['ignoreinstallercheck'] : false;
        $modvars['itemsperpage'] = isset($modvars['itemsperpage']) ? $modvars['itemsperpage'] : 5;
        if (!is_numeric($modvars['itemsperpage'])) {
            unset($modvars['itemsperpage']);
            LogUtil::registerError($this->__("Error! You must enter a number for the 'Modules per page' setting."));
        }
        $modvars['modulestylesheet'] = isset($modvars['modulestylesheet']) ? $modvars['modulestylesheet'] : 'navtabs.css';
        $modvars['admingraphic'] = isset($modvars['admingraphic']) ? $modvars['admingraphic'] : 0;
        $modvars['moduledescription'] = isset($modvars['moduledescription']) ? $modvars['moduledescription'] : 0;
        $modvars['displaynametype'] = isset($modvars['displaynametype']) ? $modvars['displaynametype'] : 1;
        $modvars['startcategory'] = isset($modvars['startcategory']) ? $modvars['startcategory'] : 1;
        $modvars['defaultcategory'] = isset($modvars['defaultcategory']) ? $modvars['defaultcategory'] : 1;
        $modvars['admintheme'] = isset($modvars['admintheme']) ? $modvars['admintheme'] : null;

        // save module vars
        ModUtil::setVars('Admin', $modvars);

        // get admin modules
        $adminmodules = ModUtil::getAdminMods();
        $adminmods = FormUtil::getPassedValue('adminmods', null, 'POST');

        foreach ($adminmodules as $adminmodule) {
            $category = $adminmods[$adminmodule['name']];

            if ($category) {
                // Add the module to the category
                $result = ModUtil::apiFunc('Admin', 'admin', 'addmodtocategory',
                        array('module' => $adminmodule['name'],
                        'category' => $category));
                if ($result == false) {
                    LogUtil::registerError($this->__('Error! Could not add module to module category.'));
                    return System::redirect(ModUtil::url('Admin', 'admin', 'view'));
                }
            }
        }

        // Let any other modules know that the modules configuration has been updated
        $this->callHooks('module','updateconfig','Admin', array('module' => 'Admin'));

        // the module configuration has been updated successfuly
        LogUtil::registerStatus($this->__('Done! Saved module configuration.'));

        // This function generated no output, and so now it is complete we redirect
        // the user to an appropriate page for them to carry on their work
        return System::redirect(ModUtil::url('Admin', 'admin', 'main'));
    }

    /**
     * Main category menu
     * @author Mark West
     * @return string HTML string
     */
    public function categorymenu($args)
    {
        // get the current category
        $acid = FormUtil::getPassedValue('acid', isset($args['acid']) ? $args['acid'] : SessionUtil::getVar('lastacid'), 'GET');
        if (empty($acid)) {
            // cid is still not set, go to the default category
            $acid = $this->getVar('startcategory');
        }

        // Get all categories
        $categories = ModUtil::apiFunc('Admin', 'admin', 'getall');

        // get admin capable modules
        $adminmodules = ModUtil::getAdminMods();
        $adminlinks = array();

        foreach ($adminmodules as $adminmodule) {
            if (SecurityUtil::checkPermission("$adminmodule[name]::", '::', ACCESS_EDIT)) {
                $catid = ModUtil::apiFunc('Admin', 'admin', 'getmodcategory', array('mid' => $adminmodule['id']));
                $menutexturl = ModUtil::url($adminmodule['name'], 'admin');
                $menutext = $adminmodule['displayname'];
                $menutexttitle = $adminmodule['description'];
                $adminlinks[$catid][] = array('menutexturl' => $menutexturl,
                        'menutext' => $menutext,
                        'menutexttitle' => $menutexttitle,
                        'modname' => $adminmodule['name']);
            }
        }

        $menuoptions = array();
        $possible_cids = array();
        $permission = false;

        if (isset($categories) && is_array($categories)) {
            foreach($categories as $category) {
                // only categories containing modules where the current user has permissions will
                // be shown, all others will be hidden
                // admin will see all categories
                if ( (isset($adminlinks[$category['cid']]) && count($adminlinks[$category['cid']]) )
                        || SecurityUtil::checkPermission('.*', '.*', ACCESS_ADMIN) ) {
                    $menuoption = array('url'         => ModUtil::url('Admin','admin','adminpanel', array('acid' => $category['cid'])),
                            'title'       => $category['catname'],
                            'description' => $category['description'],
                            'cid'         => $category['cid']);
                    if (isset($adminlinks[$category['cid']])) {
                        $menuoption['items'] = $adminlinks[$category['cid']];
                    } else {
                        $menuoption['items'] = array();
                    }
                    $menuoptions[] = $menuoption;
                    $possible_cids[] = $category['cid'];
                    if ($acid == $category['cid']) {
                        $permission =true;
                    }
                }
            }
        }

        // if permission is false we are not allowed to see this category because its
        // empty and we are not admin
        if ($permission==false) {
            // show the first category
            $acid = !empty($possible_cids) ? (int)$possible_cids[0] : null;
        }

        // store it
        SessionUtil::setVar('lastcid', $acid);

        $renderer = Renderer::getInstance('Admin', false);

        $renderer->assign('currentcat', $acid);
        $renderer->assign('menuoptions', $menuoptions);

        // security analyzer and update checker warnings
        $notices = array();
        $notices['security'] = $this->_securityanalyzer();
        $notices['update'] = $this->_updatecheck();
        $notices['developer'] = $this->_developernotices();
        $renderer->assign('notices', $notices);

        // work out what stylesheet is being used to render to the admin panel
        $css = $this->getVar('modulestylesheet');
        $cssfile = explode('.', $css);

        // Return the output that has been generated by this function
        if ($renderer->template_exists('admin_admin_categorymenu_'.$cssfile[0].'.htm')) {
            return $renderer->fetch('admin_admin_categorymenu_'.$cssfile[0].'.htm');
        } else {
            return $renderer->fetch('admin_admin_categorymenu.htm');
        }
    }

    /**
     * display the module help page
     *
     */
    public function help()
    {
        if (!SecurityUtil::checkPermission('Admin::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        $renderer = Renderer::getInstance('Admin', false);
        return $renderer->fetch('admin_admin_help.htm');
    }

    /**
     * Get security analyzer data
     * @author Mark West
     * @return array data
     */
    private function _securityanalyzer()
    {
        $data = array();

        // check for magic_quotes
        $data['magic_quotes_gpc'] = DataUtil::getBooleanIniValue('magic_quotes_gpc');

        // check for register_globals
        $data['register_globals'] = DataUtil::getBooleanIniValue('register_globals');

        // check for config.php beeing writable
        // cannot rely on is_writable() because it falsely reports a number of cases - drak
        $config_php = @fopen('config/config.php', 'a');
        if ($config_php === true) {
            fclose($config_php);
        }
        $data['config_php'] = (bool)$config_php;

        // check for .htaccess in temp directory
        $temp_htaccess = false;
        $tempDir = $GLOBALS['ZConfig']['System']['temp'];
        if ($tempDir) {
            // check if we have an absolute path which is possibly not within the document root
            $docRoot = System::serverGetVar('DOCUMENT_ROOT');
            if (StringUtil::left($tempDir, 1) == '/' && (strpos($tempDir, $docRoot) === false)) {
                // temp dir is outside the webroot, no .htaccess file needed
                $temp_htaccess = true;
            } else {
                if (strpos($tempDir, $docRoot) === false) {
                    $ldir = dirname(__FILE__);
                    $p = strpos($ldir, DIRECTORY_SEPARATOR.'system'); // we are in system/Admin
                    $b = substr($ldir,0,$p);
                    $filePath = $b.'/'.$tempDir.'/.htaccess';
                } else {
                    $filePath = $tempDir.'/.htaccess';
                }
                $temp_htaccess = (bool) file_exists($filePath);
            }
        } else {
            // already customized, admin should know about what he's doing...
            $temp_htaccess = true;
        }
        $data['temp_htaccess'] = $temp_htaccess;

        $data['scactive']  = (bool)ModUtil::available('SecurityCenter');

        // check for outputfilter
        $data['useids'] = (bool)(ModUtil::available('SecurityCenter') && System::getVar('useids') == 1);
        $data['idssoftblock'] = System::getVar('idssoftblock');

        return $data;
    }


    /**
     * Check for updates
     *
     * @author Drak
     * @return data or false
     */
    private function _updatecheck($force=false)
    {
        if (!System::getVar('updatecheck')) {
            return array('update_show' => false);
        }

        $now = time();
        $lastChecked = (int)System::getVar('updatelastchecked');
        $checkInterval = (int)System::getVar('updatefrequency') * 86400;
        $updateversion = System::getVar('updateversion');

        if ($force == false && (($now - $lastChecked) < $checkInterval)) {
            // dont get an update because TTL not expired yet
            $onlineVersion = $updateversion;
        } else {
            $s = (extension_loaded('openssl') ? 's' : '');
            $onlineVersion = trim($this->_zcurl("http$s://update.zikula.org/cgi-bin/engine/checkcoreversion.cgi"));
            if ($onlineVersion === false) {
                return array('update_show' => false);
            }
            System::setVar('updateversion', $onlineVersion);
            System::setVar('updatelastchecked', (int)time());
        }

        // if 1 then there is a later version available
        if (version_compare($onlineVersion, System::VERSION_NUM) == 1) {
            return array('update_show' => true,
                    'update_version' => $onlineVersion);
        } else {
            return array('update_show' => false);
        }
    }


    /**
     * Developer notices
     *
     * @author Carsten Volmer
     * @return data or false
     */
    private function _developernotices()
    {
        global $ZConfig;

        $modvars = ModUtil::getVar('Theme');

        $data = array();
        $data['devmode']                     = (bool) $ZConfig['System']['development'];

        if ($data['devmode'] == true) {
            $data['cssjscombine']                = $modvars['cssjscombine'];

            if ($modvars['render_compile_check']) {
                $data['render']['compile_check'] = array('state' => $modvars['render_compile_check'],
                        'title' => $this->__('Compile check'));
            }
            if ($modvars['render_force_compile']) {
                $data['render']['force_compile'] = array('state' => $modvars['render_force_compile'],
                        'title' => $this->__('Force compile'));
            }
            if ($modvars['render_cache']) {
                $data['render']['cache']         = array('state' => $modvars['render_cache'],
                        'title' => $this->__('Caching'));
            }
            if ($modvars['compile_check']) {
                $data['theme']['compile_check']  = array('state' => $modvars['compile_check'],
                        'title' => $this->__('Compile check'));
            }
            if ($modvars['force_compile']) {
                $data['theme']['force_compile']  = array('state' => $modvars['force_compile'],
                        'title' => $this->__('Force compile'));
            }
            if ($modvars['enablecache']) {
                $data['theme']['cache']          = array('state' => $modvars['enablecache'],
                        'title' => $this->__('Caching'));
            }
        }

        return $data;

    }

    /**
     * Zikula curl
     *
     * This function is internal for the time being and may be extended to be a proper library
     * or find an alternative solution later.
     *
     * @author Drak
     *
     * @todo relocate this somewhere sensible after feature has been correctly implemented - drak
     *
     * @param string $url
     * @param ing $timeout default=5
     * @return mixed, false or string
     */
    private function _zcurl($url, $timeout=5)
    {
        $urlArray = parse_url($url);
        $data = '';
        $userAgent = 'Zikula/' . System::VERSION_NUM;
        $ref = System::getBaseUrl();
        $port = (($urlArray['scheme'] == 'https') ? 443 : 80);
        if (ini_get('allow_url_fopen')) {
            // handle SSL connections
            $path_query = (isset($urlArray['query']) ? $urlArray['path'] . $urlArray['query'] : $urlArray['path']);
            $host = ($port==443 ? "ssl://$urlArray[host]" : $urlArray['host']);
            $fp = fsockopen($host, $port, $errno, $errstr, $timeout);
            if (!$fp) {
                return false;
            } else {
                $out = "GET $path_query? HTTP/1.1\r\n";
                $out .= "User-Agent: $userAgent\r\n";
                $out .= "Referer: $ref\r\n";
                $out .= "Host: $urlArray[host]\r\n";
                $out .= "Connection: Close\r\n\r\n";
                fwrite($fp, $out);
                while (!feof($fp)) {
                    $data .= fgets($fp, 1024);
                }
                fclose($fp);
                $dataArray = explode("\r\n\r\n", $data);

                return $dataArray[1];
            }
        } elseif (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_URL, "$url?");
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
            curl_setopt($ch, CURLOPT_REFERER, $ref);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            $data = curl_exec($ch);
            if (!$data && $port=443) {
                // retry non ssl
                $url = str_replace('https://', 'http://', $url);
                curl_setopt($ch, CURLOPT_URL, "$url?");
                $data = curl_exec($ch);
            }
            //$headers = curl_getinfo($ch);
            curl_close($ch);
            return $data;
        } else {
            return false;
        }
    }
}
