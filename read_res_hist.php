<?php
/**
 * rep2 - �������ݗ��� ���X���e�\��
 * �t���[��������ʁA�E������
 */

require_once './conf/conf.inc.php';
require_once P2_LIB_DIR . '/read_res_hist.inc.php';

$_login->authorize(); // ���[�U�F��

//======================================================================
// �ϐ�
//======================================================================
$newtime = date('gis');

$deletemsg_st = '�폜';
$ptitle = '�������񂾃��X�̋L�^';

//================================================================
// ����ȑO�u����
//================================================================
// �폜
if ($_POST['submit'] == $deletemsg_st or isset($_GET['checked_hists'])) {
    $checked_hists = array();
    if (isset($_POST['checked_hists'])) {
        $checked_hists = $_POST['checked_hists'];
    } elseif (isset($_GET['checked_hists'])) {
        $checked_hists = $_GET['checked_hists'];
    }
    $checked_hists and deleMsg($checked_hists);
}

// �f�[�^PHP�`���ip2_res_hist.dat.php, �^�u��؂�j�̏������ݗ������Adat�`���ip2_res_hist.dat, <>��؂�j�ɕϊ�����
P2Util::transResHistLogPhpToDat();

//======================================================================
// ���C��
//======================================================================

//==================================================================
// ����DAT�ǂ�
//==================================================================
// �ǂݍ����
if (!$datlines = FileCtl::file_read_lines($_conf['res_hist_dat'], FILE_IGNORE_NEW_LINES)) {
    echo '<html><head><title>', $ptitle, '</title></head>',
         '<body><p>�������ݗ�����e�͋���ۂ̂悤�ł�</p></body></html>';
    exit;
}

// �t�@�C���̉��ɋL�^����Ă�����̂��V����
$datlines = array_reverse($datlines);

$aResHist = new ResHist();

$aResHist->readLines($datlines);

// HTML�v�����g�p�ϐ�
$htm['checkall'] = '�S�Ẵ`�F�b�N�{�b�N�X��
<input type="button" onclick="hist_checkAll(true)" value="�I��">
<input type="button" onclick="hist_checkAll(false)" value="����">';

$htm['toolbar'] = <<<EOP
            �`�F�b�N�������ڂ�<input type="submit" name="submit" value="{$deletemsg_st}">
            �@{$htm['checkall']}
EOP;

//==================================================================
// �w�b�_ �\��
//==================================================================
//P2Util::header_nocache();
echo $_conf['doctype'];
echo <<<EOP
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    {$_conf['extra_headers_ht']}
    <title>{$ptitle}</title>
EOP;

// PC�p�\��
if (!$_conf['ktai']) {
    echo <<<EOP
    <link rel="stylesheet" type="text/css" href="css.php?css=style&amp;skin={$skin_en}">
    <link rel="stylesheet" type="text/css" href="css.php?css=read&amp;skin={$skin_en}">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
    <script type="text/javascript" src="js/basic.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/respopup.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript">
    //<![CDATA[
    function hist_checkAll(mode) {
        if (!document.getElementsByName) {
            return;
        }
        var checkboxes = document.getElementsByName('checked_hists[]');
        var cbnum = checkboxes.length;
        for (var i = 0; i < cbnum; i++) {
            checkboxes[i].checked = mode;
        }
    }
    //]]>
    </script>\n
EOP;
}

$body_at = ($_conf['ktai']) ? $_conf['k_colors'] : ' onload="gIsPageLoaded = true;"';
echo <<<EOP
</head>
<body{$body_at}>\n
EOP;

P2Util::printInfoHtml();

// �g�їp�\��
if ($_conf['ktai']) {
    echo "{$ptitle}<br>";
    echo '<div id="header" name="header">';
    $aResHist->showNaviK("header");
    echo " <a href=\"#footer\"{$_conf['k_accesskey_at']['bottom']}>{$_conf['k_accesskey_st']['bottom']}��</a><br>";
    echo "</div>";
    echo "<hr>";

// PC�p�\��
} else {
    echo <<<EOP
<form method="POST" action="./read_res_hist.php" target="_self" onSubmit="if(gIsPageLoaded){return true;}else{alert('�܂��y�[�W��ǂݍ��ݒ��Ȃ�ł��B����������Ƒ҂��āB');return false;}">
EOP;

    echo <<<EOP
<table id="header" width="100%" style="padding:0px 10px 0px 0px;">
    <tr>
        <td>
            <h3 class="thread_title">{$ptitle}</h3>
        </td>
        <td align="right">{$htm['toolbar']}</td>
        <td align="right" style="padding-left:12px;"><a href="#footer">��</a></td>
    </tr>
</table>\n
EOP;
}


//==================================================================
// ���X�L�� �\��
//==================================================================
if ($_conf['ktai']) {
    $aResHist->showArticlesK();
} else {
    $aResHist->showArticles();
}

//==================================================================
// �t�b�^ �\��
//==================================================================
// �g�їp�\��
if ($_conf['ktai']) {
    echo '<div id="footer" name="footer">';
    $aResHist->showNaviK("footer");
    echo " <a href=\"#header\"{$_conf['k_accesskey_at']['above']}>{$_conf['k_accesskey_st']['above']}��</a><br>";
    echo "</div>";
    echo "<hr><div class=\"center\">{$_conf['k_to_index_ht']}</div>";

// PC�p�\��
} else {
    echo "<hr>";
    echo <<<EOP
<table id="footer" width="100%" style="padding:0px 10px 0px 0px;">
    <tr>
        <td align="right">{$htm['toolbar']}</td>
        <td align="right" style="padding-left:12px;"><a href="#header">��</a></td>
    </tr>
</table>\n
EOP;
}

if (!$_conf['ktai']) {
    echo '</form>'."\n";
}

echo '</body></html>';

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
