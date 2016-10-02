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

$moduleDirName = basename(dirname(__DIR__));
$main_lang     = '_' . strtoupper($moduleDirName);

/**
 * Xoops header ...
 */
include dirname(dirname(__DIR__)) . '/mainfile.php';
$GLOBALS['xoopsOption']['template_main'] = 'adslight_index.tpl';
include_once XOOPS_ROOT_PATH . '/header.php';

/**
 * Modules class includes
 */
include_once __DIR__ . '/class/pictures.php';

/**
 * Factory of pictures created
 */
$album_factory = new JlmPicturesHandler($xoopsDB);

/**
 * Getting the title
 */
$title = Request::getString('caption', '', 'POST');
$lid =  Request::getInt('lid', 0, 'POST');
/**
 * Getting parameters defined in admin side
 */

$path_upload   = $GLOBALS['xoopsModuleConfig']['adslight_path_upload'];
$pictwidth     = $GLOBALS['xoopsModuleConfig']['adslight_resized_width'];
$pictheight    = $GLOBALS['xoopsModuleConfig']['adslight_resized_height'];
$thumbwidth    = $GLOBALS['xoopsModuleConfig']['adslight_thumb_width'];
$thumbheight   = $GLOBALS['xoopsModuleConfig']['adslight_thumb_height'];
$maxfilebytes  = $GLOBALS['xoopsModuleConfig']['adslight_maxfilesize'];
$maxfileheight = $GLOBALS['xoopsModuleConfig']['adslight_max_orig_height'];
$maxfilewidth  = $GLOBALS['xoopsModuleConfig']['adslight_max_orig_width'];

/**
 * If we are receiving a file
 */
if ('sel_photo' === Request::getArray('xoops_upload_file', '', 'POST')[0]) {

    /**
     * Check if using Xoops or XoopsCube (by jlm69)
     * Right now Xoops does not have a directory called preload, Xoops Cube does.
     * If this finds a diectory called preload in the Xoops Root folder $xCube=true.
     * This could change if Xoops adds a Directory called preload
     */

    $xCube = false;
    if (preg_match('/^XOOPS Cube/', XOOPS_VERSION)) { // XOOPS Cube 2.1x
        $xCube = true;
    }
    if ($xCube) {
        if (!$xoopsGTicket->check(true, 'token')) {
            redirect_header(XOOPS_URL . '/', 3, $xoopsGTicket->getErrors());
        }
    } else {
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header($_SERVER['HTTP_REFERER'], 3, constant('_ADSLIGHT_TOKENEXPIRED'));
        }
    }
    /**
     * Try to upload picture resize it insert in database and then redirect to index
     */
    if ($album_factory->receivePicture($title, $path_upload, $thumbwidth, $thumbheight, $pictwidth, $pictheight, $maxfilebytes, $maxfilewidth, $maxfileheight)) {
        header('Location: ' . XOOPS_URL . "/modules/adslight/view_photos.php?lid=$lid&uid=" . $GLOBALS['xoopsUser']->getVar('uid'));

        $xoopsDB->queryF('UPDATE ' . $xoopsDB->prefix('adslight_listing') . ' SET photo=photo+1 WHERE lid = ' . $xoopsDB->escape($lid));
    } else {
        redirect_header(XOOPS_URL . '/modules/adslight/view_photos.php?uid=' . $GLOBALS['xoopsUser']->getVar('uid'), 15, constant('_ADSLIGHT_NOCACHACA'));
    }
}

/**
 * Close page
 */
include_once XOOPS_ROOT_PATH . '/footer.php';