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

include_once __DIR__ . '/header.php';
//include_once XOOPS_ROOT_PATH . '/class/module.errorhandler.php';
$myts = MyTextSanitizer::getInstance(); // MyTextSanitizer object
//include_once XOOPS_ROOT_PATH . '/modules/adslight/class/utilities.php';
if (!empty($HTTP_POST_VARS['submit'])) {
    //    $erh         = new ErrorHandler; //ErrorHandler object
    $ratinguser = ($GLOBALS['xoopsUser'] instanceof XoopsUser) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;

    $anonwaitdays = 1; // Make sure only 1 anonymous rating from an IP in a single day.
    $ip           = getenv('REMOTE_ADDR');
    $lid          = Request::getInt('lid', 0, 'POST');
    $rating       = Request::getInt('rating', 0, 'POST');

    // Check if Rating is Null
    if ($rating == '--') {
        redirect_header('rate-item.php?lid=' . $lid . '', 4, constant('_ADSLIGHT_NORATING'));
    }

    // Check if Link POSTER is voting (UNLESS Anonymous users allowed to post)
    if ($ratinguser != 0) {
        $result = $xoopsDB->query('SELECT submitter FROM ' . $xoopsDB->prefix('adslight_listing') . ' WHERE lid=' . $xoopsDB->escape($lid));
        while (list($ratinguserDB) = $xoopsDB->fetchRow($result)) {
            if ($ratinguserDB == $ratinguser) {
                redirect_header('viewads.php?lid=' . $lid . '', 4, constant('_ADSLIGHT_CANTVOTEOWN'));
            }
        }

        // Check if REG user is trying to vote twice.
        $result = $xoopsDB->query('SELECT ratinguser FROM ' . $xoopsDB->prefix('adslight_item_votedata') . ' WHERE lid=' . $xoopsDB->escape($lid));
        while (list($ratinguserDB) = $xoopsDB->fetchRow($result)) {
            if ($ratinguserDB == $ratinguser) {
                redirect_header('viewads.php?lid=' . $lid . '', 4, constant('_ADSLIGHT_VOTEONCE2'));
            }
        }
    } else {

        // Check if ANONYMOUS user is trying to vote more than once per day.
        $yesterday = (time() - (86400 * $anonwaitdays));
        $result    = $xoopsDB->query('SELECT count(*) FROM '
                                     . $xoopsDB->prefix('adslight_item_votedata')
                                     . ' WHERE lid='
                                     . $xoopsDB->escape($lid)
                                     . " AND ratinguser=0 AND ratinghostname = '$ip' AND ratingtimestamp > $yesterday");
        list($anonvotecount) = $xoopsDB->fetchRow($result);
        if ($anonvotecount > 0) {
            redirect_header('viewads.php?lid=' . $lid . '', 4, constant('_ADSLIGHT_VOTEONCE2'));
        }
    }
    $rating = ($rating > 10) ? 10 : (int)$rating;

    //All is well.  Add to Line Item Rate to DB.
    $newid    = $xoopsDB->genId($xoopsDB->prefix('adslight_item_votedata') . '_ratingid_seq');
    $datetime = time();
    $sql      = sprintf("INSERT INTO %s (ratingid, lid, ratinguser, rating, ratinghostname, ratingtimestamp) VALUES (%u, %u, %u, %u, '%s', %u)", $xoopsDB->prefix('adslight_item_votedata'), $newid,
                        $lid, $ratinguser, $rating, $ip, $datetime);
    // $xoopsDB->query($sql) || $erh->show('0013'); //            '0013' => 'Could not query the database.', // <br>Error: ' . mysql_error() . '',
    $success = $xoopsDB->query($sql);
    if (!$success) {
        $modHandler = xoops_getModuleHandler('module');
        $myModule   = $modHandler->getByDirname('adslight');
        $myModule->setErrors('Could not query the database.');
    }

    //All is well.  Calculate Score & Add to Summary (for quick retrieval & sorting) to DB.
    //    updateIrating($lid);
    AdslightUtilities::updateItemRating($lid);
    $ratemessage = constant('_ADSLIGHT_VOTEAPPRE') . '<br>' . sprintf(constant('_ADSLIGHT_THANKURATEITEM'), $xoopsConfig['sitename']);
    redirect_header('viewads.php?lid=' . $lid . '', 3, $ratemessage);
} else {
    $GLOBALS['xoopsOption']['template_main'] = 'adslight_rate_item.tpl';
    include XOOPS_ROOT_PATH . '/header.php';
    $lid    = Request::getInt('lid', 0, 'GET');
    $result = $xoopsDB->query('SELECT lid, title FROM ' . $xoopsDB->prefix('adslight_listing') . ' WHERE lid=' . $xoopsDB->escape($lid));
    list($lid, $title) = $xoopsDB->fetchRow($result);
    $xoopsTpl->assign('link', array('lid' => $lid, 'title' => $myts->htmlSpecialChars($title)));
    $xoopsTpl->assign('lang_voteonce', constant('_ADSLIGHT_VOTEONCE'));
    $xoopsTpl->assign('lang_ratingscale', constant('_ADSLIGHT_RATINGSCALE'));
    $xoopsTpl->assign('lang_beobjective', constant('_ADSLIGHT_BEOBJECTIVE'));
    $xoopsTpl->assign('lang_donotvote', constant('_ADSLIGHT_DONOTVOTE'));
    $xoopsTpl->assign('lang_rateit', constant('_ADSLIGHT_RATEIT'));
    $xoopsTpl->assign('lang_cancel', _CANCEL);
    $xoopsTpl->assign('mydirname', $moduleDirName);
    include XOOPS_ROOT_PATH . '/footer.php';
}