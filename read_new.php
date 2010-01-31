<?php
/**
 * rep2 - �X���b�h�\���X�N���v�g - �V���܂Ƃߓǂ�
 * �t���[��������ʁA�E������
 */

require_once './conf/conf.inc.php';

$_login->authorize(); // ���[�U�F��

//==================================================================
// �ϐ�
//==================================================================
if (isset($_conf['rnum_all_range']) and $_conf['rnum_all_range'] > 0) {
    $GLOBALS['rnum_all_range'] = $_conf['rnum_all_range'];
}

$sb_view = 'shinchaku';
$newtime = date('gis');

$online_num = 0;
$newthre_num = 0;

//=================================================
// �̎w��
//=================================================
if (isset($_GET['host'])) { $host = $_GET['host']; }
if (isset($_POST['host'])) { $host = $_POST['host']; }
if (isset($_GET['bbs'])) { $bbs = $_GET['bbs']; }
if (isset($_POST['bbs'])) { $bbs = $_POST['bbs']; }
if (isset($_GET['spmode'])) { $spmode = $_GET['spmode']; }
if (isset($_POST['spmode'])) { $spmode = $_POST['spmode']; }

if ((!isset($host) || !isset($bbs)) && !isset($spmode)) {
    p2die('�K�v�Ȉ������w�肳��Ă��܂���');
}

//=================================================
// ���ځ[��&NG���[�h�ݒ�ǂݍ���
//=================================================
$GLOBALS['ngaborns'] = NgAbornCtl::loadNgAborns();

//====================================================================
// ���C��
//====================================================================

$aThreadList = new ThreadList();

// �ƃ��[�h�̃Z�b�g===================================
$ta_keys = array();
if ($spmode) {
    if ($spmode == 'taborn' or $spmode == 'soko') {
        $aThreadList->setIta($host, $bbs, P2Util::getItaName($host, $bbs));
    }
    $aThreadList->setSpMode($spmode);
} else {
    $aThreadList->setIta($host, $bbs, P2Util::getItaName($host, $bbs));

    // �X���b�h���ځ[�񃊃X�g�Ǎ�
    $taborn_file = $aThreadList->getIdxDir() . 'p2_threads_aborn.idx';
    if ($tabornlines = FileCtl::file_read_lines($taborn_file, FILE_IGNORE_NEW_LINES)) {
        $ta_num = sizeof($tabornlines);
        foreach ($tabornlines as $l) {
            $tarray = explode('<>', $l);
            $ta_keys[ $tarray[1] ] = true;
        }
    }
}

// �\�[�X���X�g�Ǎ�
if ($spmode == 'merge_favita') {
    if ($_conf['expack.misc.multi_favs'] && !empty($_conf['m_favita_set'])) {
        $merged_faivta_read_idx = $_conf['pref_dir'] . '/p2_favita' . $_conf['m_favita_set'] . '_read.idx';
    } else {
        $merged_faivta_read_idx = $_conf['pref_dir'] . '/p2_favita_read.idx';
    }
    $lines = FileCtl::file_read_lines($merged_faivta_read_idx);
    if (is_array($lines)) {
        $have_merged_faivta_read_idx = true;
    } else {
        $have_merged_faivta_read_idx = false;
        $lines = $aThreadList->readList();
    }
} else {
    $lines = $aThreadList->readList();
}

// �y�[�W�w�b�_�\�� ===================================
$ptitle_hd = htmlspecialchars($aThreadList->ptitle, ENT_QUOTES);
$ptitle_ht = "{$ptitle_hd} �� �V���܂Ƃߓǂ�";
$matomeCache = new MatomeCache($ptitle_hd, $_conf['matome_cache_max']);
ob_start();

if ($aThreadList->spmode) {
    $sb_ht = <<<EOP
        <a href="{$_conf['subject_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}&amp;spmode={$aThreadList->spmode}" target="subject">{$ptitle_hd}</a>
EOP;
} else {
    $sb_ht = <<<EOP
        <a href="{$_conf['subject_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}" target="subject">{$ptitle_hd}</a>
EOP;
}

// require_once P2_LIB_DIR . '/read_header.inc.php';

echo $_conf['doctype'];
echo <<<EOHEADER
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    {$_conf['extra_headers_ht']}
    <title>{$ptitle_ht}</title>
    <link rel="stylesheet" type="text/css" href="css.php?css=style&amp;skin={$skin_en}">
    <link rel="stylesheet" type="text/css" href="css.php?css=read&amp;skin={$skin_en}">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
    <script type="text/javascript" src="js/basic.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/respopup.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/htmlpopup.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/motolspopup.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/ngabornctl.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/setfavjs.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/delelog.js?{$_conf['p2_version_id']}"></script>\n
EOHEADER;

if ($_conf['link_youtube'] == 2 || $_conf['link_niconico'] == 2) {
    echo <<<EOP
    <script type="text/javascript" src="js/preview_video.js?{$_conf['p2_version_id']}"></script>\n
EOP;
}
if ($_conf['expack.am.enabled']) {
    echo <<<EOP
    <script type="text/javascript" src="js/asciiart.js?{$_conf['p2_version_id']}"></script>\n
EOP;
}
/*if ($_conf['expack.misc.async_respop']) {
    echo <<<EOP
    <script type="text/javascript" src="js/async.js?{$_conf['p2_version_id']}"></script>\n
EOP;
}*/
if ($_conf['expack.spm.enabled']) {
    echo <<<EOP
    <script type="text/javascript" src="js/invite.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/smartpopup.js?{$_conf['p2_version_id']}"></script>\n
EOP;
}
if ($_conf['expack.ic2.enabled']) {
    echo <<<EOP
    <script type="text/javascript" src="js/json2.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/loadthumb.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/ic2_getinfo.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/ic2_popinfo.js?{$_conf['p2_version_id']}"></script>
    <link rel="stylesheet" type="text/css" href="css/ic2_popinfo.css?{$_conf['p2_version_id']}">\n
EOP;
}

// pageLoaded()������JavaScript�ł���`���ꂽ���[�h���̃C�x���g�n���h���Ƃ��Ԃ�Ȃ��悤�ɂ���B
// �Â��u���E�U��DOMContentLoaded�Ɠ����̃^�C�~���O�ɂ͂������Ȃ��B
// rep2�̓t���[���O��Ȃ̂�jQuery.bindReady()�̂悤�ȋZ�͎g���Ȃ��i�ۂ��j�B
echo <<<EOHEADER
    <script type="text/javascript">
    //<![CDATA[
    gIsPageLoaded = false;

    function pageLoaded()
    {
        gIsPageLoaded = true;
        setWinTitle();
    }

    (function(){
        if (typeof window.p2BindReady == 'undefined') {
            window.setTimeout(arguments.callee, 100);
        } else {
            window.p2BindReady(pageLoaded, 'js/defer/pageLoaded.js');
        }
    })();
    //]]>
    </script>\n
EOHEADER;

if (!empty($_SESSION['use_narrow_toolbars'])) {
    echo <<<EOP
    <link rel="stylesheet" type="text/css" href="css.php?css=narrow_toolbar&amp;skin={$skin_en}">\n
EOP;
}

echo <<<EOP
</head>
<body><div id="popUpContainer"></div>\n
EOP;

P2Util::printInfoHtml();

//echo $ptitle_ht."<br>";

//==============================================================
// ���ꂼ��̍s���
//==============================================================

$linesize = sizeof($lines);
$subject_txts = array();

for ($x = 0; $x < $linesize ; $x++) {

    if (isset($GLOBALS['rnum_all_range']) and $GLOBALS['rnum_all_range'] <= 0) {
        break;
    }

    $l = $lines[$x];
    $aThread = new ThreadRead();

    $aThread->torder = $x + 1;

    // �f�[�^�ǂݍ���
    // spmode�Ȃ�
    if ($aThreadList->spmode) {
        switch ($aThreadList->spmode) {
        case "recent": // ����
            $aThread->getThreadInfoFromExtIdxLine($l);
            break;
        case "res_hist": // �������ݗ���
            $aThread->getThreadInfoFromExtIdxLine($l);
            break;
        case "fav": // ���C��
            $aThread->getThreadInfoFromExtIdxLine($l);
            break;
        case "taborn": // �X���b�h���ځ[��
            $aThread->getThreadInfoFromExtIdxLine($l);
            $aThread->host = $aThreadList->host;
            $aThread->bbs = $aThreadList->bbs;
            break;
        case "palace": // �X���̓a��
            $aThread->getThreadInfoFromExtIdxLine($l);
            break;
        case "merge_favita": // ���C�ɔ��}�[�W
            if ($have_merged_faivta_read_idx) {
                $aThread->getThreadInfoFromExtIdxLine($l);
            } else {
                $aThread->key = $l['key'];
                $aThread->setTtitle($l['ttitle']);
                $aThread->rescount = $l['rescount'];
                $aThread->host = $l['host'];
                $aThread->bbs = $l['bbs'];
                $aThread->torder = $l['torder'];
            }
            break;
        }
    // subject (not spmode)�̏ꍇ
    } else {
        $aThread->getThreadInfoFromSubjectTxtLine($l);
        $aThread->host = $aThreadList->host;
        $aThread->bbs = $aThreadList->bbs;
    }

    // host��bbs���s���Ȃ�X�L�b�v
    if (!($aThread->host && $aThread->bbs)) {
        unset($aThread);
        continue;
    }

    $subject_id = $aThread->host . '/' . $aThread->bbs;

    $aThread->setThreadPathInfo($aThread->host, $aThread->bbs, $aThread->key);

    // �����X���b�h�f�[�^��idx����擾
    $aThread->getThreadInfoFromIdx();

    // �V���̂�(for subject) =========================================
    if (!$aThreadList->spmode && $sb_view == 'shinchaku' && empty($_GET['word'])) {
        if ($aThread->unum < 1) {
            unset($aThread);
            continue;
        }
    }

    // �X���b�h���ځ[��`�F�b�N =====================================
    if ($aThreadList->spmode != 'taborn' && !empty($ta_keys[$aThread->key])) {
            unset($ta_keys[$aThread->key]);
            continue; // ���ځ[��X���̓X�L�b�v
    }

    //  spmode(�a�����������)�Ȃ� ====================================
    if ($aThreadList->spmode && $sb_view != "edit") {

        // subject.txt ����DL�Ȃ痎�Ƃ��ăf�[�^��z��Ɋi�[
        if (empty($subject_txts[$subject_id])) {
            $aSubjectTxt = new SubjectTxt($aThread->host, $aThread->bbs);

            $subject_txts[$subject_id] = $aSubjectTxt->subject_lines;
        }

        // �X�����擾 =============================
        if (!empty($subject_txts[$subject_id])) {
            $thread_key = (string)$aThread->key;
            $thread_key_len = strlen($thread_key);
            foreach ($subject_txts[$subject_id] as $l) {
                if (strncmp($l, $thread_key, $thread_key_len) == 0) {
                    $aThread->getThreadInfoFromSubjectTxtLine($l); // subject.txt ����X�����擾
                    break;
                }
            }
        }

        // �V���̂�(for spmode) ===============================
        if ($sb_view == 'shinchaku' && empty($_GET['word'])) {
            if ($aThread->unum < 1) {
                unset($aThread);
                continue;
            }
        }
    }

    if ($aThread->isonline) { $online_num++; } // ������set

    P2Util::printInfoHtml();

    $matomeCache->concat(ob_get_flush());
    flush();
    ob_start();

    if (($aThread->readnum < 1) || $aThread->unum) {
        readNew($aThread);
        $matomeCache->addReadThread($aThread);
    } elseif ($aThread->diedat) {
        echo $aThread->getdat_error_msg_ht;
        echo "<hr>\n";
    }

    $matomeCache->concat(ob_get_flush());
    flush();
    ob_start();

    // ���X�g�ɒǉ� ========================================
    // $aThreadList->addThread($aThread);
    $aThreadList->num++;
    unset($aThread);
}

// $aThread = new ThreadRead();

//======================================================================
//  �X���b�h�̐V��������ǂݍ���ŕ\������
//======================================================================
function readNew($aThread)
{
    global $_conf, $newthre_num, $STYLE;
    global $word;
    static $favlist_titles = null;

    if ($_conf['expack.misc.multi_favs'] && is_null($favlist_titles)) {
        $favlist_titles = FavSetManager::getFavSetTitles('m_favlist_set');
        if (empty($favlist_titles)) {
            $favlist_titles = array();
        }
        if (!isset($favlist_titles[0]) || $favlist_titles[0] == '') {
            $favlist_titles[0] = '���C�ɃX��';
        }
        for ($i = 1; $i <= $_conf['expack.misc.favset_num']; $i++) {
            if (!isset($favlist_titles[$i]) || $favlist_titles[$i] == '') {
                $favlist_titles[$i] = '���C�ɃX��' . $i;
            }
        }
    }

    $newthre_num++;

    //==========================================================
    //  idx�̓ǂݍ���
    //==========================================================

    // host�𕪉�����idx�t�@�C���̃p�X�����߂�
    $aThread->setThreadPathInfo($aThread->host, $aThread->bbs, $aThread->key);

    // FileCtl::mkdirFor($aThread->keyidx); // �f�B���N�g����������΍�� // ���̑���͂����炭�s�v

    $aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs);
    if (!$aThread->itaj) { $aThread->itaj = $aThread->bbs; }

    // idx�t�@�C��������Γǂݍ���
    if ($lines = FileCtl::file_read_lines($aThread->keyidx, FILE_IGNORE_NEW_LINES)) {
        $data = explode('<>', $lines[0]);
    } else {
        $data = array_fill(0, 12, '');
    }
    $aThread->getThreadInfoFromIdx();

    //==================================================================
    // DAT�̃_�E�����[�h
    //==================================================================
    if (!($word and file_exists($aThread->keydat))) {
        $aThread->downloadDat();
    }

    // DAT��ǂݍ���
    $aThread->readDat();
    $aThread->setTitleFromLocal(); // ���[�J������^�C�g�����擾���Đݒ�

    //===========================================================
    // �\�����X�Ԃ͈̔͂�ݒ�
    //===========================================================
    // �擾�ς݂Ȃ�
    if ($aThread->isKitoku()) {
        $from_num = $aThread->readnum +1 - $_conf['respointer'] - $_conf['before_respointer_new'];
        if ($from_num > $aThread->rescount) {
            $from_num = $aThread->rescount - $_conf['respointer'] - $_conf['before_respointer_new'];
        }
        if ($from_num < 1) {
            $from_num = 1;
        }

        //if (!$aThread->ls) {
            $aThread->ls = "$from_num-";
        //}
    }

    $aThread->lsToPoint();

    //==================================================================
    // �w�b�_ �\��
    //==================================================================
    $motothre_url = $aThread->getMotoThread(false, '');

    $ttitle_en = UrlSafeBase64::encode($aThread->ttitle);
    $ttitle_en_q = '&amp;ttitle_en=' . $ttitle_en;
    $bbs_q = '&amp;bbs=' . $aThread->bbs;
    $key_q = '&amp;key=' . $aThread->key;
    $host_bbs_key_q = 'host=' . $aThread->host . $bbs_q . $key_q;
    $popup_q = '&amp;popup=1';

    // require_once P2_LIB_DIR . '/read_header.inc.php';

    $prev_thre_num = $newthre_num - 1;
    $next_thre_num = $newthre_num + 1;
    if ($prev_thre_num != 0) {
        $prev_thre_ht = "<a href=\"#ntt{$prev_thre_num}\">��</a>";
    } else {
        $prev_thre_ht = '';
    }
    $next_thre_ht = "<a id=\"ntta{$next_thre_num}\" href=\"#ntt{$next_thre_num}\">��</a>";

    P2Util::printInfoHtml();

    // �w�b�_����HTML
    $read_header_ht = <<<EOP
<table id="ntt{$newthre_num}" class="toolbar">
    <tr>
        <td class="lblock"><h3 class="thread_title">{$aThread->ttitle_hd}</h3></td>
        <td class="rblock">{$prev_thre_ht} {$next_thre_ht}</td>
    </tr>
</table>\n
EOP;

    //==================================================================
    // ���[�J��Dat��ǂݍ����HTML�\��
    //==================================================================
    $aThread->resrange['nofirst'] = true;
    $GLOBALS['newres_to_show_flag'] = false;
    if ($aThread->rescount) {
        $aShowThread = new ShowThreadPc($aThread, true);

        if ($_conf['expack.spm.enabled']) {
            $read_header_ht .= $aShowThread->getSpmObjJs();
        }

        $res1 = $aShowThread->quoteOne();
        $read_cont_ht = $res1['q'];
        $read_cont_ht .= $aShowThread->getDatToHtml();

        unset($aShowThread);
    }

    //==================================================================
    // �t�b�^ �\��
    //==================================================================
    // $read_footer_navi_new  ������ǂ� �V�����X�̕\��
    $newtime = date("gis");  // �����N���N���b�N���Ă��ēǍ����Ȃ��d�l�ɑ΍R����_�~�[�N�G���[

    $info_st = '���';
    $delete_st = '�폜';
    $prev_st = '�O';
    $next_st = '��';
    $dores_st = '����';

    $read_footer_navi_new = "<a href=\"{$_conf['read_php']}?{$host_bbs_key_q}&amp;ls={$aThread->rescount}-&amp;nt=$newtime#r{$aThread->rescount}\">�V�����X�̕\��</a>";

    if (!empty($_conf['disable_res'])) {
        $dores_ht = <<<EOP
          <a href="{$motothre_url}" target="_blank">{$dores_st}</a>
EOP;
    } else {
        $dores_ht = <<<EOP
        <a href="post_form.php?{$host_bbs_key_q}&amp;rescount={$aThread->rescount}{$ttitle_en_q}" target='_self' onclick="return OpenSubWin('post_form.php?{$host_bbs_key_q}&amp;rescount={$aThread->rescount}{$ttitle_en_q}{$popup_q}&amp;from_read_new=1',{$STYLE['post_pop_size']},1,0)">{$dores_st}</a>
EOP;
    }

    // �c�[���o�[����HTML =======

    // ���C�Ƀ}�[�N�ݒ�
    $itaj_hd = htmlspecialchars($aThread->itaj, ENT_QUOTES);
    $similar_q = '&amp;itaj_en=' . UrlSafeBase64::encode($aThread->itaj)
               . '&amp;method=similar&amp;word=' . rawurlencode($aThread->ttitle_hc);

    if ($_conf['expack.misc.multi_favs']) {
        $toolbar_setfav_ht = '���C��[';
        $favdo = (!empty($aThread->favs[0])) ? 0 : 1;
        $favdo_q = '&amp;setfav=' . $favdo;
        $favmark = $favdo ? '+' : '��';
        $favtitle = $favlist_titles[0] . ($favdo ? '�ɒǉ�' : '����O��');
        $setnum_q = '&amp;setnum=0';
        $toolbar_setfav_ht .= <<<EOP
<span class="favdo set0"><a href="info.php?{$host_bbs_key_q}{$ttitle_en_q}{$favdo_q}{$setnum_q}" target="info" onclick="return setFavJs('{$host_bbs_key_q}{$ttitle_en_q}', '{$favdo}', {$STYLE['info_pop_size']}, 'read_new', this, '0');" title="{$favtitle}">{$favmark}</a></span>
EOP;
        for ($i = 1; $i <= $_conf['expack.misc.favset_num']; $i++) {
            $favdo = (!empty($aThread->favs[$i])) ? 0 : 1;
            $favdo_q = '&amp;setfav=' . $favdo;
            $favmark = $favdo ? $i : '��';
            $favtitle = $favlist_titles[$i] . ($favdo ? '�ɒǉ�' : '����O��');
            $setnum_q = '&amp;setnum=' . $i;
            $toolbar_setfav_ht .= <<<EOP
|<span class="favdo set{$i}"><a href="info.php?{$host_bbs_key_q}{$ttitle_en_q}{$favdo_q}{$setnum_q}" target="info" onclick="return setFavJs('{$host_bbs_key_q}{$ttitle_en_q}', '{$favdo}', {$STYLE['info_pop_size']}, 'read_new', this, '{$i}');" title="{$favtitle}">{$favmark}</a></span>
EOP;
        }
        $toolbar_setfav_ht .= ']';
    } else {
        $favdo = (!empty($aThread->fav)) ? 0 : 1;
        $favdo_q = '&amp;setfav=' . $favdo;
        $favmark = $favdo ? '+' : '��';
        $favtitle = $favdo ? '���C�ɃX���ɒǉ�' : '���C�ɃX������O��';
        $toolbar_setfav_ht = <<<EOP
<span class="favdo"><a href="info.php?{$host_bbs_key_q}{$ttitle_en_q}{$favdo_q}" target="info" onclick="return setFavJs('{$host_bbs_key_q}{$ttitle_en_q}', '{$favdo}', {$STYLE['info_pop_size']}, 'read_new', this, '0');" title="{$favtitle}">���C��{$favmark}</a></span>
EOP;
    }

    $toolbar_right_ht = <<<EOTOOLBAR
            <a href="{$_conf['subject_php']}?{$host_bbs_key_q}" target="subject" title="���J��">{$itaj_hd}</a>
            <a href="info.php?{$host_bbs_key_q}{$ttitle_en_q}" target="info" onclick="return OpenSubWin('info.php?{$host_bbs_key_q}{$ttitle_en_q}{$popup_q}',{$STYLE['info_pop_size']},1,0)" title="�X���b�h����\��">{$info_st}</a>
            {$toolbar_setfav_ht}
            <span><a href="info.php?{$host_bbs_key_q}{$ttitle_en_q}&amp;dele=true" target="info" onclick="return deleLog('{$host_bbs_key_q}{$ttitle_en_q}', {$STYLE['info_pop_size']}, 'read_new', this);" title="���O���폜����">{$delete_st}</a></span>
<!--            <a href="info.php?{$host_bbs_key_q}{$ttitle_en_q}&amp;taborn=2" target="info" onclick="return OpenSubWin('info.php?{$host_bbs_key_q}{$ttitle_en_q}&amp;popup=2&amp;taborn=2',{$STYLE['info_pop_size']},0,0)" title="�X���b�h�̂��ځ[���Ԃ��g�O������">���ڂ�</a> -->
            <a href="{$motothre_url}" title="�T�[�o��̃I���W�i���X����\��" onmouseover="showMotoLsPopUp(event, this)" onmouseout="hideMotoLsPopUp()">���X��</a>
            <a href="{$_conf['subject_php']}?{$host_bbs_key_q}{$similar_q}" target="subject" title="�^�C�g�������Ă���X���b�h������">���X��</a>
EOTOOLBAR;

    // ���X�̂��΂₳
    $spd_ht = "";
    if ($spd_st = $aThread->getTimePerRes() and $spd_st != "-") {
        $spd_ht = '<span class="spd" title="���΂₳������/���X">'."" . $spd_st."".'</span>';
    }

    // dat�T�C�Y
    if (file_exists($aThread->keydat) && $dsize_ht = filesize($aThread->keydat)) {
        $dsize_ht = sprintf('<span class="spd" title="%s">%01.1fKB</span> |', 'dat�T�C�Y', $dsize_ht / 1024);
    } else {
        $dsize_ht = '';
    }

    // �t�b�^����HTML
    $read_footer_ht = <<<EOP
<table class="toolbar">
    <tr>
        <td class="lblock">{$res1['body']} | <a href="{$_conf['read_php']}?{$host_bbs_key_q}&amp;offline=1&amp;rescount={$aThread->rescount}#r{$aThread->rescount}">{$aThread->ttitle_hd}</a> | {$dores_ht} {$dsize_ht} {$spd_ht}</td>
        <td class="rblock">{$toolbar_right_ht}</td>
        <td class="rblock"><a href="#ntt{$newthre_num}">��</a></td>
    </tr>
</table>\n
EOP;

    // �������ځ[��ŕ\�����Ȃ��ꍇ�̓X�L�b�v
    if ($GLOBALS['newres_to_show_flag']) {
        echo '<div style="width:100%;">'."\n"; // �ق�IE ActiveX��Gray()�̂��߂����Ɉ͂��Ă���
        echo $read_header_ht;
        echo $read_cont_ht;
        echo $read_footer_ht;
        echo '</div>'."\n\n";
        echo '<hr>'."\n\n";
    }

    //==================================================================
    // key.idx �̒l�ݒ�
    //==================================================================
    if ($aThread->rescount) {

        $aThread->readnum = min($aThread->rescount, max(0, $data[5], $aThread->resrange['to']));

        $newline = $aThread->readnum + 1; // $newline�͔p�~�\�肾���A���݊��p�ɔO�̂���

        $sar = array($aThread->ttitle, $aThread->key, $data[2], $aThread->rescount, $aThread->modified,
                    $aThread->readnum, $data[6], $data[7], $data[8], $newline,
                    $data[10], $data[11], $aThread->datochiok);
        P2Util::recKeyIdx($aThread->keyidx, $sar); // key.idx�ɋL�^
    }
}

//==================================================================
// �y�[�W�t�b�^�\��
//==================================================================
$newthre_num++;

if (!$aThreadList->num) {
    $GLOBALS['matome_naipo'] = TRUE;
    echo "�V�����X�͂Ȃ���";
    echo "<hr>";
}

$shinchaku_matome_url = "{$_conf['read_new_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}&amp;spmode={$aThreadList->spmode}&amp;nt={$newtime}";

if ($aThreadList->spmode == 'merge_favita') {
    $shinchaku_matome_url .= $_conf['m_favita_set_at_a'];
}

if (!isset($GLOBALS['rnum_all_range']) or $GLOBALS['rnum_all_range'] > 0 or !empty($GLOBALS['limit_to_eq_to'])) {
    if (!empty($GLOBALS['limit_to_eq_to'])) {
        $str = '�V���܂Ƃߓǂ݂̍X�V/����';
    } else {
        $str = '�V���܂Ƃߓǂ݂��X�V';
    }
} else {
    $str = '�V���܂Ƃߓǂ݂̑���';
    $shinchaku_matome_url .= '&amp;norefresh=1';
}

echo <<<EOP
<div id="ntt{$newthre_num}" align="center">{$sb_ht} �� <a href="{$shinchaku_matome_url}">{$str}</a></div>\n
EOP;

if ($_conf['expack.ic2.enabled']) {
    echo "<script type=\"text/javascript\" src=\"js/ic2_popinfo.js\"></script>";
    include P2EX_LIB_DIR . '/ic2/templates/info.tpl.html';
}

echo '</body></html>';

$matomeCache->concat(ob_get_flush());

// NG���ځ[����L�^
NgAbornCtl::saveNgAborns();

/*
 * Local Variables:
 * mode: php
 * coding: cp932
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */
// vim: set syn=php fenc=cp932 ai et ts=4 sw=4 sts=4 fdm=marker:
