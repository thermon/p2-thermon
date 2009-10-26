<?php
/**
 * rep2 - �X���b�h���E�B���h�E
 */

require_once './conf/conf.inc.php';
require_once P2_LIB_DIR . '/Thread.php';
require_once P2_LIB_DIR . '/dele.inc.php';

$_login->authorize(); // ���[�U�F��

//================================================================
// �ϐ��ݒ�
//================================================================
$host = isset($_GET['host']) ? $_GET['host'] : null; // "pc.2ch.net"
$bbs = isset($_GET['bbs']) ? $_GET['bbs'] : null; // "php"
$key = isset($_GET['key']) ? $_GET['key'] : null; // "1022999539"
$ttitle_en = isset($_GET['ttitle_en']) ? $_GET['ttitle_en'] : null;

// popup 0(false), 1(true), 2(true, �N���[�Y�^�C�}�[�t)
if (!empty($_GET['popup'])) {
    $popup_q = '&amp;popup=1';
} else {
    $popup_q = '';
}

if ($_conf['iphone']) {
    $btn_class = ' class="button"';
} else {
    $btn_class = '';
}

// �ȉ��ǂꂩ����Ȃ��Ă��_���o��
if (empty($host) || empty($bbs) || empty($key)) {
    p2die('����������������܂���B');
}

//================================================================
// ����ȑO����
//================================================================
// {{{ �폜

if (!empty($_GET['dele']) && $key && $host && $bbs) {
    $r = deleteLogs($host, $bbs, array($key));
    if (empty($r)) {
        $title_msg = "�~ ���O�폜���s";
        $info_msg = "�~ ���O�폜���s";
    } elseif ($r == 1) {
        $title_msg = "�� ���O�폜����";
        $info_msg = "�� ���O�폜����";
    } elseif ($r == 2) {
        $title_msg = "- ���O�͂���܂���ł���";
        $info_msg = "- ���O�͂���܂���ł���";
    }
}

// }}}
// {{{ �����폜

if (!empty($_GET['offrec']) && $key && $host && $bbs) {
    $r1 = offRecent($host, $bbs, $key);
    $r2 = offResHist($host, $bbs, $key);
    if ((empty($r1)) or (empty($r2))) {
        $title_msg = "�~ �����������s";
        $info_msg = "�~ �����������s";
    } elseif ($r1 == 1 || $r2 == 1) {
        $title_msg = "�� ������������";
        $info_msg = "�� ������������";
    } elseif ($r1 == 2 && $r2 == 2) {
        $title_msg = "- �����ɂ͂���܂���ł���";
        $info_msg = "- �����ɂ͂���܂���ł���";
    }

// }}}
// {{{ ���C�ɓ���X���b�h

} elseif (isset($_GET['setfav']) && $key && $host && $bbs) {
    if (!function_exists('setFav')) {
        include P2_LIB_DIR . '/setfav.inc.php';
    }
    $ttitle = is_string($ttitle_en) ? base64_decode($ttitle_en) : null;
    if (isset($_GET['setnum'])) {
        setFav($host, $bbs, $key, $_GET['setfav'], $ttitle, $_GET['setnum']);
    } else {
        setFav($host, $bbs, $key, $_GET['setfav'], $ttitle);
    }
    if ($_conf['expack.misc.multi_favs']) {
        FavSetManager::loadAllFavSet(true);
    }

// }}}
// {{{ �a������

} elseif (isset($_GET['setpal']) && $key && $host && $bbs) {
    require_once P2_LIB_DIR . '/setpalace.inc.php';
    setPal($host, $bbs, $key, $_GET['setpal']);

// }}}
// {{{ �X���b�h���ځ[��

} elseif (isset($_GET['taborn']) && $key && $host && $bbs) {
    require_once P2_LIB_DIR . '/settaborn.inc.php';
    settaborn($host, $bbs, $key, $_GET['taborn']);
}

// }}}
//=================================================================
// ���C��
//=================================================================

$aThread = new Thread();

// host�𕪉�����idx�t�@�C���̃p�X�����߂�
$aThread->setThreadPathInfo($host, $bbs, $key);
$key_line = $aThread->getThreadInfoFromIdx();
$aThread->getDatBytesFromLocalDat(); // $aThread->length ��set

if (!$aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs)) {
    $aThread->itaj = $aThread->bbs;
}
$hc['itaj'] = $aThread->itaj;

if (!$aThread->ttitle) {
    if (isset($ttitle_en)) {
        $aThread->setTtitle(base64_decode($ttitle_en));
    } else {
        $aThread->setTitleFromLocal();
    }
}
if (!$ttitle_en) {
    if ($aThread->ttitle) {
        $ttitle_en = base64_encode($aThread->ttitle);
        //$ttitle_urlen = rawurlencode($ttitle_en);
    }
}
if ($ttitle_en) {
    $ttitle_en_q = '&amp;ttitle_en=' . rawurlencode($ttitle_en);
} else {
    $ttitle_en_q = '';
}

if (!is_null($aThread->ttitle_hc)) {
    $hc['ttitle_name'] = $aThread->ttitle_hc;
} else {
    $hc['ttitle_name'] = "�X���b�h�^�C�g�����擾";
}

$common_q = "host={$aThread->host}&amp;bbs={$aThread->bbs}&amp;key={$aThread->key}";

// {{{ favlist �`�F�b�N

/*
// ���C�ɃX�����X�g �Ǎ�
if ($favlines = FileCtl::file_read_lines($_conf['favlist_idx'], FILE_IGNORE_NEW_LINES)) {
    foreach ($favlines as $l) {
        $favarray = explode('<>', $l);
        if ($aThread->key == $favarray[1] && $aThread->bbs == $favarray[11]) {
            $aThread->fav = "1";
            if ($favarray[0]) {
                $aThread->setTtitle($favarray[0]);
            }
            break;
        }
    }
}
*/

if ($_conf['expack.misc.multi_favs']) {
    $favlist_titles = FavSetManager::getFavSetTitles('m_favlist_set');
    $favdo = (!empty($aThread->favs[0])) ? 0 : 1;
    $favdo_q = '&amp;setfav=' . $favdo;
    $favmark = $favdo ? '+' : '��';
    $favtitle = ((!isset($favlist_titles[0]) || $favlist_titles[0] == '') ? '���C�ɃX��' : $favlist_titles[0]) . ($favdo ? '�ɒǉ�' : '����O��');
    $setnum_q = '&amp;setnum=0';
    if ($_conf['iphone']) {
        $fav_ht = '<br>';
        $fav_delim = ' ';
    } else {
        $fav_ht = '';
        $fav_delim = ' | ';
    }
    $fav_ht .= <<<EOP
<a href="info.php?{$common_q}{$ttitle_en_q}{$favdo_q}{$setnum_q}{$popup_q}{$_conf['k_at_a']}"{$btn_class}><span class="fav" title="{$favtitle}">{$favmark}</span></a>
EOP;
    for ($i = 1; $i <= $_conf['expack.misc.favset_num']; $i++) {
        $favdo = (!empty($aThread->favs[$i])) ? 0 : 1;
        $favdo_q = '&amp;setfav=' . $favdo;
        $favmark = $favdo ? $i : '��';
        $favtitle = ((!isset($favlist_titles[$i]) || $favlist_titles[$i] == '') ? '���C�ɃX��' . $i : $favlist_titles[$i]) . ($favdo ? '�ɒǉ�' : '����O��');
        $setnum_q = '&amp;setnum=' . $i;
        $fav_ht .= <<<EOP
{$fav_delim}<a href="info.php?{$common_q}{$ttitle_en_q}{$favdo_q}{$setnum_q}{$popup_q}{$_conf['k_at_a']}"{$btn_class}><span class="fav" title="{$favtitle}">{$favmark}</span></a>
EOP;
    }
} else {
    $favdo = (!empty($aThread->fav)) ? 0 : 1;
    $favdo_q = '&amp;setfav=' . $favdo;
    $favmark = $favdo ? '+' : '��';
    $favtitle = $favdo ? '���C�ɃX���ɒǉ�' : '���C�ɃX������O��';
    $fav_ht = <<<EOP
<a href="info.php?{$common_q}{$ttitle_en_q}{$favdo_q}{$popup_q}{$_conf['k_at_a']}"{$btn_class}><span class="fav" title="{$favtitle}">{$favmark}</span></a>
EOP;
}

// }}}
// {{{ palace �`�F�b�N

// �a������X�����X�g �Ǎ�
if ($pallines = FileCtl::file_read_lines($_conf['palace_idx'], FILE_IGNORE_NEW_LINES)) {
    foreach ($pallines as $l) {
        $palarray = explode('<>', $l);
        if ($aThread->key == $palarray[1]) {
            $isPalace = true;
            if ($palarray[0]) {
                $aThread->setTtitle($palarray[0]);
            }
            break;
        }
    }
}

$paldo = $isPalace ? 0 : 1;

$pal_a_ht = "info.php?{$common_q}&amp;setpal={$paldo}{$popup_q}{$ttitle_en_q}{$_conf['k_at_a']}";

if ($isPalace) {
    $pal_ht = "<a href=\"{$pal_a_ht}\"{$btn_class}>��</a>";
} else {
    $pal_ht = "<a href=\"{$pal_a_ht}\"{$btn_class}>+</a>";
}

// }}}
// {{{ �X���b�h���ځ[��`�F�b�N

// �X���b�h���ځ[�񃊃X�g�Ǎ�
$taborn_file = P2Util::idxDirOfHostBbs($host, $bbs) . 'p2_threads_aborn.idx';
if ($tabornlist = FileCtl::file_read_lines($taborn_file, FILE_IGNORE_NEW_LINES)) {
    foreach ($tabornlist as $l) {
        $tarray = explode('<>', $l);
        if ($aThread->key == $tarray[1]) {
            $isTaborn = true;
            break;
        }
    }
}

$taborndo_title_at = '';
if (!empty($isTaborn)) {
    $tastr1 = "���ځ[��";
    $tastr2 = "���ځ[���������";
    $taborndo = 0;
} else {
    $tastr1 = "�ʏ�";
    $tastr2 = "���ځ[�񂷂�";
    $taborndo = 1;
    if (!$_conf['ktai']) {
        $taborndo_title_at = ' title="�X���b�h�ꗗ�Ŕ�\���ɂ��܂�"';
    }
}

$taborn_ht = <<<EOP
{$tastr1} [<a href="info.php?{$common_q}&amp;taborn={$taborndo}{$popup_q}{$ttitle_en_q}{$_conf['k_at_a']}"{$taborndo_title_at}>{$tastr2}</a>]
EOP;

// }}}

// ���O����Ȃ��t���O�Z�b�g
if (file_exists($aThread->keydat) or file_exists($aThread->keyidx)) {
    $existLog = true;
}

//=================================================================
// HTML�v�����g
//=================================================================
if ($_conf['ktai']) {
    $target_read_at = '';
    $target_sb_at = '';
} else {
    $target_read_at = ' target="read"';
    $target_sb_at = ' target="sbject"';
}

$motothre_url = $aThread->getMotoThread();
if (P2Util::isHost2chs($aThread->host)) {
    $motothre_org_url = $aThread->getMotoThread(true);
} else {
    $motothre_org_url = $motothre_url;
}


if (!is_null($title_msg)) {
    $hc['title'] = $title_msg;
} else {
    $hc['title'] = "info - {$hc['ttitle_name']}";
}

$hd = array_map('htmlspecialchars', $hc);


P2Util::header_nocache();
echo $_conf['doctype'];
echo <<<EOHEADER
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    {$_conf['extra_headers_ht']}
    <title>{$hd['title']}</title>\n
EOHEADER;

$body_onload = '';
if (!$_conf['ktai']) {
    echo <<<EOP
    <link rel="stylesheet" type="text/css" href="css.php?css=style&amp;skin={$skin_en}">
    <link rel="stylesheet" type="text/css" href="css.php?css=info&amp;skin={$skin_en}">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">\n
EOP;
    if ($_GET['popup'] == 2) {
        echo <<<EOSCRIPT
    <script type="text/javascript" src="js/closetimer.js?{$_conf['p2_version_id']}"></script>\n
EOSCRIPT;
        $body_onload = ' onload="startTimer(document.getElementById(\'timerbutton\'))"';
    }
}

$body_at = ($_conf['ktai']) ? $_conf['k_colors'] : $body_onload;
echo <<<EOP
</head>
<body{$body_at}>
EOP;

echo $_info_msg_ht;
$_info_msg_ht = "";

echo "<p>\n";
echo "<b><a class=\"thre_title\" href=\"{$_conf['read_php']}?{$common_q}{$_conf['k_at_a']}\"{$target_read_at}>{$hd['ttitle_name']}</a></b>\n";
echo "</p>\n";

// �g�тȂ�`���ŕ\��
if ($_conf['ktai']) {
    if (!empty($info_msg)) {
        echo "<p>" . $info_msg . "</p>\n";
    }
}

if (checkRecent($aThread->host, $aThread->bbs, $aThread->key) or checkResHist($aThread->host, $aThread->bbs, $aThread->key)) {
    $offrec_ht = " / [<a href=\"info.php?{$common_q}&amp;offrec=true{$popup_q}{$ttitle_en_q}{$_conf['k_at_a']}\" title=\"���̃X�����u�ŋߓǂ񂾃X���v�Ɓu�������ݗ����v����O���܂�\">��������O��</a>]";
}

if (!$_conf['ktai']) {
    echo "<table cellspacing=\"0\">\n";
}
print_info_line("���X��", "<a href=\"{$motothre_url}\" target=\"_blank\">{$motothre_url}</a>");
if (!$_conf['ktai']) {
    print_info_line("�z�X�g", $aThread->host);
}
print_info_line("��", "<a href=\"{$_conf['subject_php']}?host={$aThread->host}&amp;bbs={$aThread->bbs}{$_conf['k_at_a']}\"{$target_sb_at}>{$hd['itaj']}</a>");
if (!$_conf['ktai']) {
    print_info_line("key", $aThread->key);
}
if ($existLog) {
    print_info_line("���O", "���� [<a href=\"info.php?{$common_q}&amp;dele=true{$popup_q}{$ttitle_en_q}{$_conf['k_at_a']}\">�폜����</a>]{$offrec_ht}");
} else {
    print_info_line("���O", "���擾{$offrec_ht}");
}
if ($aThread->gotnum) {
    print_info_line("�������X��", $aThread->gotnum);
} elseif (!$aThread->gotnum and $existLog) {
    print_info_line("�������X��", "0");
} else {
    print_info_line("�������X��", "-");
}

// PC�p�\��
if (!$_conf['ktai']) {
    if (file_exists($aThread->keydat)) {
        if ($aThread->length) {
            print_info_line("dat�T�C�Y", $aThread->length.' �o�C�g');
        }
        print_info_line("dat", $aThread->keydat);
    } else {
        print_info_line("dat", "-");
    }
    if (file_exists($aThread->keyidx)) {
        print_info_line("idx", $aThread->keyidx);
    } else {
        print_info_line("idx", "-");
    }
}

print_info_line("���C�ɃX��", $fav_ht);
print_info_line("�a������", $pal_ht);
print_info_line("�\��", $taborn_ht);

// PC
if (!$_conf['ktai']) {
    echo "</table>\n";
}

if (!$_conf['ktai']) {
    if (!empty($info_msg)) {
        echo "<span class=\"infomsg\">".$info_msg."</span>\n";
    } else {
        echo "�@\n";
    }
}

// �g�уR�s�y�p�t�H�[��
if ($_conf['ktai']) {
    echo getCopypaFormHtml($motothre_org_url, $hd['ttitle_name']);
}

// {{{ ����{�^��

if (!empty($_GET['popup'])) {
    echo '<div align="center">';
    if ($_GET['popup'] == 1) {
        echo '<form action=""><input type="button" value="�E�B���h�E�����" onclick="window.close();"></form>';
    } elseif ($_GET['popup'] == 2) {
        echo <<<EOP
    <form action=""><input id="timerbutton" type="button" value="Close Timer" onclick="stopTimer(document.getElementById('timerbutton'))"></form>
EOP;
    }
    echo '</div>' . "\n";
}

// }}}

if ($_conf['ktai']) {
    echo "<hr><div class=\"center\">{$_conf['k_to_index_ht']}</div>";
}

echo '</body></html>';

// �I��
exit;

//=======================================================
// �֐�
//=======================================================
// {{{ print_info_line()

/**
 * �X�����HTML��\������
 */
function print_info_line($s, $c_ht)
{
    global $_conf;

    // �g��
    if ($_conf['ktai']) {
        echo "{$s}: {$c_ht}<br>";
    // PC
    } else {
        echo "<tr><td class=\"tdleft\" nowrap><b>{$s}</b>&nbsp;</td><td class=\"tdcont\">{$c_ht}</td></tr>\n";
    }
}

// }}}
// {{{ getCopypaFormHtml()

/**
 * �X���^�C��URL�̃R�s�y�p�̃t�H�[�����擾����
 */
function getCopypaFormHtml($url, $ttitle_name_hd)
{
    $url_hd = htmlspecialchars($url, ENT_QUOTES);

    $me_url = $me_url = P2Util::getMyUrl();
    // $_SERVER['REQUEST_URI']

    $htm = <<<EOP
<form action="{$me_url}">
 <textarea name="copy">{$ttitle_name_hd}&#10;{$url_hd}</textarea>
</form>
EOP;
// <input type="text" name="url" value="{$url_hd}">
// <textarea name="msg_txt">{$msg_txt}</textarea><br>

    return $htm;
}

// }}}

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

