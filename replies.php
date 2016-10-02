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
//include XOOPS_ROOT_PATH . '/modules/adslight/class/utilities.php';

$myts         = MyTextSanitizer::getInstance(); // MyTextSanitizer object
$module_id    = $xoopsModule->getVar('mid');
$groups       = ($GLOBALS['xoopsUser'] instanceof XoopsUser) ? $GLOBALS['xoopsUser']->getGroups() : XOOPS_GROUP_ANONYMOUS;
/** @var XoopsGroupPermHandler $gpermHandler */
$gpermHandler = xoops_getHandler('groupperm');
$perm_itemid  = Request::getInt('item_id', 0, 'POST');

//If no access
if (!$gpermHandler->checkRight('adslight_premium', $perm_itemid, $groups, $module_id)) {
    redirect_header(XOOPS_URL . '/modules/adslight/index.php', 3, _NOPERM);
}
include_once XOOPS_ROOT_PATH . '/modules/adslight/class/classifiedstree.php';
$mytree = new ClassifiedsTree($xoopsDB->prefix('adslight_categories'), 'cid', 'pid');

$lid                                     = Request::getInt('lid', 0, 'GET');
$GLOBALS['xoopsOption']['template_main'] = 'adslight_replies.tpl';
include XOOPS_ROOT_PATH . '/header.php';

$xoopsTpl->assign('nav_main', "<a href=\"index.php\">" . _ADSLIGHT_MAIN . '</a>');
$show = 1;
$min  = Request::getInt('min', 0, 'GET');
if (!isset($max)) {
    $max = $min + $show;
}
$orderby = 'date Desc';

$xoopsTpl->assign('lid', $lid);
$countresult = $xoopsDB->query('SELECT COUNT(*) FROM ' . $xoopsDB->prefix('adslight_replies') . ' WHERE lid=' . $xoopsDB->escape($lid));
list($trow) = $xoopsDB->fetchRow($countresult);
$trows   = $trow;
$pagenav = '';

if ($trows < '1') {
    $xoopsTpl->assign('has_replies', false);
    $xoopsTpl->assign('no_more_replies', _ADSLIGHT_NO_REPLIES);
}

if ($trows > '0') {
    $xoopsTpl->assign('has_replies', true);
    $xoopsTpl->assign('last_head', _ADSLIGHT_THE . ' ' . $GLOBALS['xoopsModuleConfig']['adslight_newcount'] . ' ' . _ADSLIGHT_LASTADD);
    $xoopsTpl->assign('last_head_title', _ADSLIGHT_TITLE);
    $xoopsTpl->assign('last_head_price', _ADSLIGHT_PRICE);
    $xoopsTpl->assign('last_head_date', _ADSLIGHT_DATE);
    $xoopsTpl->assign('last_head_local', _ADSLIGHT_LOCAL2);
    $xoopsTpl->assign('last_head_views', _ADSLIGHT_VIEW);
    $xoopsTpl->assign('last_head_photo', _ADSLIGHT_PHOTO);
    $xoopsTpl->assign('min', $min);

    $sql    = 'SELECT r_lid, lid, title, date, submitter, message, tele, email, r_usid FROM ' . $xoopsDB->prefix('adslight_replies') . ' WHERE lid=' . $xoopsDB->escape($lid) . " ORDER BY $orderby";
    $result = $xoopsDB->query($sql, $show, $min);

    if ($trows > '1') {
        $xoopsTpl->assign('has_replies', true);
        $xoopsTpl->assign('show_nav', true);
        $xoopsTpl->assign('lang_sortby', _ADSLIGHT_SORTBY);
        $xoopsTpl->assign('lang_title', _ADSLIGHT_TITLE);
        $xoopsTpl->assign('lang_titleatoz', _ADSLIGHT_TITLEATOZ);
        $xoopsTpl->assign('lang_titleztoa', _ADSLIGHT_TITLEZTOA);
        $xoopsTpl->assign('lang_date', _ADSLIGHT_DATE);
        $xoopsTpl->assign('lang_dateold', _ADSLIGHT_DATEOLD);
        $xoopsTpl->assign('lang_datenew', _ADSLIGHT_DATENEW);
        $xoopsTpl->assign('lang_popularity', _ADSLIGHT_POPULARITY);
        $xoopsTpl->assign('lang_popularityleast', _ADSLIGHT_POPULARITYLTOM);
        $xoopsTpl->assign('lang_popularitymost', _ADSLIGHT_POPULARITYMTOL);
        $xoopsTpl->assign('lang_cursortedby', _ADSLIGHT_CURSORTEDBY . '' . $orderby);
    }

    while (list($r_lid, $lid, $title, $date, $submitter, $message, $tele, $email, $r_usid) = $xoopsDB->fetchRow($result)) {
        $useroffset = '';
        if ($GLOBALS['xoopsUser']) {
            $timezone = $GLOBALS['xoopsUser']->timezone();
            if (isset($timezone)) {
                $useroffset = $GLOBALS['xoopsUser']->timezone();
            } else {
                $useroffset = $xoopsConfig['default_TZ'];
            }
        }
        $r_usid = $r_usid;
        $xoopsTpl->assign('submitter', " <a href='" . XOOPS_URL . "/userinfo.php?uid=$r_usid'>$submitter</a>");
        $date = ($useroffset * 3600) + $date;
        $date = formatTimestamp($date, 's');
        $xoopsTpl->assign('title', "<a href='viewads.php?lid=$lid'>$title</a>");
        $xoopsTpl->assign('title_head', _ADSLIGHT_REPLY_TITLE);
        $xoopsTpl->assign('date_head', _ADSLIGHT_REPLIED_ON);
        $xoopsTpl->assign('submitter_head', _ADSLIGHT_REPLIED_BY);
        $xoopsTpl->assign('message_head', _ADSLIGHT_REPLY_MESSAGE);
        $xoopsTpl->assign('email_head', _ADSLIGHT_EMAIL);
        $xoopsTpl->assign('tele_head', _ADSLIGHT_TEL);
        $xoopsTpl->assign('email', "<a href ='mailto:$email'>$email</a>");
        $xoopsTpl->assign('delete_reply', "<a href='modify.php?op=DelReply&amp;r_lid=$r_lid'>" . _ADSLIGHT_DELETE_REPLY . '</a>');
        $xoopsTpl->append('items', array(
            'id'      => $lid,
            'title'   => $myts->htmlSpecialChars($title),
            'date'    => $date,
            'message' => $myts->displayTarea($message),
            'tele'    => $myts->htmlSpecialChars($tele)
        ));
    }
    $lid = Request::getInt('lid', 0, 'GET');
    //Calculates how many pages exist.  Which page one should be on, etc...
    $linkpages = ceil($trows / $show);
    //Page Numbering
    if ($linkpages != 1 && $linkpages != 0) {
        $prev = $min - $show;
        if ($prev >= 0) {
            $pagenav .= "<a href='replies.php?lid=$lid&min=$prev&show=$show'><strong><u>&laquo;</u></strong></a> ";
        }
        $counter     = 1;
        $currentpage = ($max / $show);
        while ($counter <= $linkpages) {
            $mintemp = ($show * $counter) - $show;
            if ($counter == $currentpage) {
                $pagenav .= "<strong>($counter)</strong> ";
            } else {
                $pagenav .= "<a href='replies.php?lid=$lid&min=$mintemp&show=$show'>$counter</a> ";
            }
            ++$counter;
        }
        if ($trows > $max) {
            $pagenav .= "<a href='replies.php?lid=$lid&min=$max&show=$show'>";
            $pagenav .= '<strong><u>&raquo;</u></strong></a>';
        }
        $xoopsTpl->assign('nav_page', '<strong>' . _ADSLIGHT_REPLY . "</strong>&nbsp;&nbsp; $pagenav");
    }
}

include XOOPS_ROOT_PATH . '/footer.php';