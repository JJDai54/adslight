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
 * Xoops Header
 */
include dirname(dirname(__DIR__)) . '/mainfile.php';
include_once XOOPS_ROOT_PATH . '/header.php';
include_once XOOPS_ROOT_PATH . '/class/criteria.php';

/**
 * Include modules classes
 */
include_once __DIR__ . '/class/pictures.php';

/**
 * Check if using XoopsCube (by jlm69)
 * Needed because of a difference in the way Xoops and XoopsCube handle tokens
 */

$xCube = false;
if (preg_match('/^XOOPS Cube/', XOOPS_VERSION)) { // XOOPS Cube 2.1x
    $xCube = true;
}

/**
 * Verify Ticket for Xoops Cube (by jlm69)
 * If your site is XoopsCube it uses $xoopsGTicket for the token.
 */

if ($xCube) {
    if (!$xoopsGTicket->check(true, 'token')) {
        redirect_header($_SERVER['HTTP_REFERER'], 3, $xoopsGTicket->getErrors());
    }
} else {
    /**
     * Verify TOKEN for Xoops
     * If your site is Xoops it uses xoopsSecurity for the token.
     */
    if (!$GLOBALS['xoopsSecurity']->check()) {
        redirect_header($_SERVER['HTTP_REFERER'], 3, constant('_ADSLIGHT_TOKENEXPIRED'));
    }
}

/**
 * Receiving info from get parameters
 */
$cod_img = Request::getString('cod_img', '', 'POST');
//$lid = (int)$_POST['lid'];
//$marker = $_POST['marker'];
$marker = Request::getInt('marker', '', 'POST');

if ($marker == 1) {
    /**
     * Creating the factory  loading the picture changing its caption
     */
    $picture_factory = new JlmPicturesHandler($xoopsDB);
    $picture         = $picture_factory->create(false);
    $picture->load(Request::getString('cod_img', '', 'POST'));
    $picture->setVar('title', Request::getString('caption', '', 'POST'));

    /**
     * Verifying who's the owner to allow changes
     */
    $uid = $GLOBALS['xoopsUser']->getVar('uid');
    $lid = $picture->getVar('lid');
    if ($uid == $picture->getVar('uid_owner')) {
        if ($picture_factory->insert($picture)) {
            redirect_header('view_photos.php?lid=' . $lid . '&uid=' . $uid . '', 2, constant('_ADSLIGHT_DESC_EDITED'));
        } else {
            redirect_header('view_photos.php?lid=' . $lid . '&uid=' . $uid . '', 2, constant('_ADSLIGHT_NOCACHACA'));
        }
    }
}

/**
 * Creating the factory  and the criteria to edit the desc of the picture
 * The user must be the owner
 */
$album_factory = new JlmPicturesHandler($xoopsDB);
$criteria_img  = new Criteria('cod_img', $cod_img);
$uid           = $GLOBALS['xoopsUser']->getVar('uid');
$criteria_uid  = new Criteria('uid_owner', $uid);
$criteria      = new CriteriaCompo($criteria_img);
$criteria->add($criteria_uid);

/**
 * Lets fetch the info of the pictures to be able to render the form
 * The user must be the owner
 */
if ($array_pict =& $album_factory->getObjects($criteria)) {
    $caption = $array_pict[0]->getVar('title');
    $url     = $array_pict[0]->getVar('url');
}
$url = $GLOBALS['xoopsModuleConfig']['adslight_link_upload'] . '/thumbs/thumb_' . $url;
$album_factory->renderFormEdit($caption, $cod_img, $url);

/**
 * Close page
 */
include XOOPS_ROOT_PATH . '/footer.php';