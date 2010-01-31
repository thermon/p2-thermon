<?php
/**
 * rep2 - �T�u�W�F�N�g - �g�уw�b�_�\��
 * for subject.php
 */

//===============================================================
// HTML�\���p�ϐ�
//===============================================================
$newtime = date("gis");
$norefresh_q = "&amp;norefresh=1";

// {{{ �y�[�W�^�C�g������URL�ݒ�

$p2_subject_url = "{$_conf['subject_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}{$_conf['k_at_a']}";

// �ʏ� ��
if (!$aThreadList->spmode) {
    // �����ꂠ��
    if ((isset($GLOBALS['word']) && strlen($GLOBALS['word']) > 0) || !empty($GLOBALS['wakati_words'])) {
        $ptitle_url = $p2_subject_url;

    // 2ch�n (iPhone����)
    } elseif (!$_conf['iphone'] && P2Util::isHost2chs($aThreadList->host)) {
        if (P2Util::isHostBbsPink($aThreadList->host)) {
            //$ptitle_url = "http://{$aThreadList->host}/{$aThreadList->bbs}/i/";
            $ptitle_url = "http://speedo.ula.cc/test/p.so/{$aThreadList->host}/{$aThreadList->bbs}/";
        } else {
            $ptitle_url = "http://c.2ch.net/test/-/{$aThreadList->bbs}/i";
        }

    // ���̑�
    } else {
        $ptitle_url = "http://{$aThreadList->host}/{$aThreadList->bbs}/";
        // ���ʂȃp�^�[�� index2.html
        // match�o�^���head�Ȃ��ĕ������ق����悳���������A�������X�|���X������̂�����
        if (!strcasecmp($aThreadList->host, 'livesoccer.net')) {
            $ptitle_url .= 'index2.html';
        }
    }

// ���ځ[�� or �q��
} elseif ($aThreadList->spmode == 'taborn' || $aThreadList->spmode == 'soko') {
    $ptitle_url = $p2_subject_url;

// �������ݗ���
} elseif ($aThreadList->spmode == 'res_hist') {
    $ptitle_url = "./read_res_hist.php{$_conf['k_at_q']}#footer";
}

// }}}
// {{{ �y�[�W�^�C�g������HTML�ݒ�

if ($aThreadList->spmode == 'fav' && $_conf['expack.misc.multi_favs']) {
    $ptitle_hd = FavSetManager::getFavSetPageTitleHt('m_favlist_set', $aThreadList->ptitle);
} else {
    $ptitle_hd = htmlspecialchars($aThreadList->ptitle, ENT_QUOTES);
}

if ($aThreadList->spmode == 'taborn') {
    $ptitle_ht = <<<EOP
<a href="{$ptitle_url}"><b>{$aThreadList->itaj_hd}</b></a>�i���ݒ��j
EOP;
} elseif ($aThreadList->spmode == 'soko') {
    $ptitle_ht = <<<EOP
<a href="{$ptitle_url}"><b>{$aThreadList->itaj_hd}</b></a>�idat�q�Ɂj
EOP;
} elseif (!empty($ptitle_url)) {
    $ptitle_ht = <<<EOP
<a href="{$ptitle_url}" class="nobutton"><b>{$ptitle_hd}</b></a>
EOP;
} else {
    $ptitle_ht = <<<EOP
<b>{$ptitle_hd}</b>
EOP;
}

// }}}
// �t�H�[�� ==================================================
$sb_form_hidden_ht = <<<EOP
<input type="hidden" name="bbs" value="{$aThreadList->bbs}">
<input type="hidden" name="host" value="{$aThreadList->host}">
<input type="hidden" name="spmode" value="{$aThreadList->spmode}">
{$_conf['detect_hint_input_ht']}{$_conf['k_input_ht']}{$_conf['m_favita_set_input_ht']}
EOP;

// �t�B���^���� ==================================================

$hd['word'] = htmlspecialchars($word, ENT_QUOTES);

$filter_form_ht = '';
$hit_ht = '';

if ($_conf['iphone']) {
    $hd['input_nocorrect_at'] = ' autocorrect="off" autocapitalize="off"';
    $hd['input_numeric_at'] = ' autocorrect="off" autocapitalize="off" placeholder="#"';
} else {
    $hd['input_nocorrect_at'] = '';
    $hd['input_numeric_at'] = ' maxlength="4" istyle="4" format="4N" mode="numeric"';
}

if (!$spmode_without_palace_or_favita) {
    if (array_key_exists('method', $sb_filter) && $sb_filter['method'] == 'or') {
        $hd['method_checked_at'] = ' checked';
    } else {
        $hd['method_checked_at'] = '';
    }

    $filter_form_ht = <<<EOP
<form method="GET" action="{$_conf['subject_php']}" accept-charset="{$_conf['accept_charset']}">
{$sb_form_hidden_ht}<input type="text" id="sb_filter_word" name="word" value="{$hd['word']}" size="15"{$hd['input_nocorrect_at']}>
<input type="checkbox" id="sb_filter_method" name="method" value="or"{$hd['method_checked_at']}><label for="sb_filter_method">OR</label>
<input type="submit" name="submit_kensaku" value="����">
</form>\n
EOP;
}

// ��������
if ($GLOBALS['sb_mikke_num']) {
    $hit_ht = "<div>&quot;{$word}&quot; {$GLOBALS['sb_mikke_num']}hit!</div>";
}

// iPhone�p�w�b�_�v�f
if ($_conf['iphone']) {
    $_conf['extra_headers_ht'] .= <<<EOS
<link rel="stylesheet" type="text/css" href="iui/toggle-only.css?{$_conf['p2_version_id']}">
<script type="text/javascript" src="js/json2.js?{$_conf['p2_version_id']}"></script>
<script type="text/javascript" src="js/sb_iphone.js?{$_conf['p2_version_id']}"></script>
EOS;
    // �X���̐������������߂̃X�^�C���V�[�g
    if ($_conf['iphone.subject.indicate-speed']) {
        $_conf['extra_headers_ht'] .= <<<EOS
<style type="text/css">
/* <![CDATA[ */
ul.subject > li > a { border-left: transparent solid {$_conf['iphone.subject.speed.width']}px; }
ul.subject > li > a.dayres-0 { border-left-color: {$_conf['iphone.subject.speed.0rpd']}; }
ul.subject > li > a.dayres-1 { border-left-color: {$_conf['iphone.subject.speed.1rpd']}; }
ul.subject > li > a.dayres-10 { border-left-color: {$_conf['iphone.subject.speed.10rpd']}; }
ul.subject > li > a.dayres-100 { border-left-color: {$_conf['iphone.subject.speed.100rpd']}; }
ul.subject > li > a.dayres-1000 { border-left-color: {$_conf['iphone.subject.speed.1000rpd']}; }
ul.subject > li > a.dayres-10000 { border-left-color: {$_conf['iphone.subject.speed.10000rpd']}; }
/* ]]> */
</style>
EOS;
    }
}

/*
if ($_conf['iphone'] && ($_conf['expack.ic2.enabled'] == 2 || $_conf['expack.ic2.enabled'] == 3)) {
    $_conf['extra_headers_ht'] .= <<<EOS
<script type="text/javascript" src="js/basic.js?{$_conf['p2_version_id']}"></script>
<script type="text/javascript" src="js/ic2_switch.js?{$_conf['p2_version_id']}"></script>
EOS;
}
*/

//=================================================
//�w�b�_�v�����g
//=================================================
P2Util::header_nocache();
echo $_conf['doctype'];
echo <<<EOP
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
{$_conf['extra_headers_ht']}
<title>{$ptitle_hd}</title>
</head>
<body{$_conf['k_colors']}>
EOP;

echo $_info_msg_ht;
$_info_msg_ht = "";

include P2_LIB_DIR . '/sb_toolbar_k.inc.php';

echo <<<EOP
<form method="get" action="{$_conf['read_new_k_php']}">
{$sb_form_hidden_ht}<input type="hidden" name="nt" value="1">{$shinchaku_norefresh_ht}
���ǐ���<input type="text" name="unum_limit" value="100" size="4"{$hd['input_numeric_at']}>������
<input type="submit" value="�V�܂Ƃ�">
</form>\n
EOP;

echo $filter_form_ht;
echo $hit_ht;

if (!$_conf['iphone']) {
    echo '<hr>';
}

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
