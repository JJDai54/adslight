<?php
/*
-------------------------------------------------------------------------
                     ADSLIGHT 2 : Module for Xoops

        Redesigned and ameliorate By Luc Bizet user at www.frxoops.org
        Started with the Classifieds module and made MANY changes
        Website : http://www.luc-bizet.fr
        Contact : adslight.translate@gmail.com
-------------------------------------------------------------------------
             Original credits below Version History
##########################################################################
#                    Classified Module for Xoops                         #
#  By John Mordo user jlm69 at www.xoops.org and www.jlmzone.com         #
#      Started with the MyAds module and made MANY changes               #
##########################################################################
 Original Author: Pascal Le Boustouller
 Author Website : pascal.e-xoops@perso-search.com
 Licence Type   : GPL
-------------------------------------------------------------------------
*/

use Xmf\Request;

require_once dirname(dirname(dirname(__DIR__))) . '/include/cp_header.php';
require_once __DIR__ . '/admin_header.php';
xoops_cp_header();

//if ( !is_readable(XOOPS_ROOT_PATH . "/Frameworks/art/functions.admin.php")) {
//    adslight_adminmenu(6, "");
//} else {
//    require_once XOOPS_ROOT_PATH.'/Frameworks/art/functions.admin.php';
//    loadModuleAdminMenu (6, "");
//}

$action = Request::getString('action', '', 'POST');
if (!empty($action)) {
    $file = Request::getString('file', '', 'POST');
}
/*
$action = '';
if (\Xmf\Request::hasVar('action', 'POST')) {
    $action = $_POST['action'];
    $file   = $_POST['file'];
}
*/
$sql = 'SELECT conf_id FROM ' . $xoopsDB->prefix('config') . ' WHERE conf_name = "theme_set"';
$res = $xoopsDB->query($sql);
list($conf_id) = $xoopsDB->fetchRow($res);
/** @var \XoopsModuleHandler $moduleHandler */
$moduleHandler = xoops_getHandler('module');
$module        = $moduleHandler->getByDirname('system');
/** @var \XoopsConfigHandler $configHandler */
$configHandler = xoops_getHandler('config');
$config_theme  = $configHandler->getConfig($conf_id, true);

switch ($action) {
    case 'new':
        copy(XOOPS_ROOT_PATH . "/modules/adslight/Root/{$file}", XOOPS_ROOT_PATH . "/{$file}");
        break;
    case 'remove':
        unlink(XOOPS_ROOT_PATH . "/{$file}");
        break;
    case 'copy':
        rename(XOOPS_ROOT_PATH . "/{$file}", XOOPS_ROOT_PATH . "/{$file}.svg");
        copy(XOOPS_ROOT_PATH . "/modules/adslight/Root/{$file}", XOOPS_ROOT_PATH . "/{$file}");
        break;
    case 'restore':
        unlink(XOOPS_ROOT_PATH . "/{$file}");
        rename(XOOPS_ROOT_PATH . "/{$file}.svg", XOOPS_ROOT_PATH . "/{$file}");
        break;
    case 'install_template':
        if (file_exists(XOOPS_ROOT_PATH . '/themes/' . $config_theme->getConfValueForOutput() . "/modules/{$file}")) {
            unlink(XOOPS_ROOT_PATH . '/themes/' . $config_theme->getConfValueForOutput() . "/modules/{$file}");
        }
        //        FS_Storage::dircopy(XOOPS_ROOT_PATH . '/modules/adslight/Root/themes/', XOOPS_ROOT_PATH . '/themes/' . $config_theme->getConfValueForOutput() . '/', $success, $error);
        XoopsModules\Adslight\Utility::rcopy(XOOPS_ROOT_PATH . '/modules/adslight/Root/themes/', XOOPS_ROOT_PATH . '/themes/' . $config_theme->getConfValueForOutput() . '/');
        require_once XOOPS_ROOT_PATH . '/class/template.php';
        $xoopsTpl = new \XoopsTpl();
        $GLOBALS['xoopsTpl']->clear_cache('db:system_block_user.tpl');
        $GLOBALS['xoopsTpl']->clear_cache('db:system_userinfo.tpl');
        $GLOBALS['xoopsTpl']->clear_cache('db:profile_userinfo.tpl');
        break;
    case 'remove_template':
        unlink(XOOPS_ROOT_PATH . '/themes/' . $config_theme->getConfValueForOutput() . "/modules/{$file}");
        break;
}

xoops_cp_footer();
