<?php
/**
 * rep2 - �C���f�b�N�X�y�[�W
 */

require_once './conf/conf.inc.php';

$_login->authorize(); //���[�U�F��

//=============================================================
// �O����
//=============================================================
// �A�N�Z�X���ۗp��.htaccess���f�[�^�f�B���N�g���ɍ쐬����
makeDenyHtaccess($_conf['pref_dir']);
makeDenyHtaccess($_conf['dat_dir']);
makeDenyHtaccess($_conf['idx_dir']);

//=============================================================

$me_url = P2Util::getMyUrl();
$me_dir_url = dirname($me_url);
$me_url_b = htmlspecialchars(rtrim($me_dir_url, '/') . '/?b=', ENT_QUOTES);

if ($_conf['ktai']) {

    //=========================================================
    // �g�їp �C���f�b�N�X
    //=========================================================
    // url�w�肪����΁A���̂܂܃X���b�h�ǂ݂֔�΂�
    if (!empty($_GET['url']) || !empty($_GET['nama_url'])) {
        header('Location: '.$me_dir_url.'/read.php?'.$_SERVER['QUERY_STRING']);
        exit;
    }
    if ($_conf['iphone']) {
        include P2_BASE_DIR . '/menu_i.php';
        exit;
    }
    require_once P2_LIB_DIR . '/index_print_k.inc.php';
    index_print_k();

} else {
    //=========================================
    // PC�p �ϐ�
    //=========================================
    $title_page = "title.php";

    if (!empty($_GET['url']) || !empty($_GET['nama_url'])) {
        $htm['read_page'] = "read.php?".$_SERVER['QUERY_STRING'];
    } else {
        if (!empty($_conf['first_page'])) {
            $htm['read_page'] = $_conf['first_page'];
        } else {
            $htm['read_page'] = 'first_cont.php';
        }
    }

    $sidebar = !empty($_GET['sidebar']);

    $ptitle = "rep2";
    //======================================================
    // PC�p HTML�v�����g
    //======================================================
    //P2Util::header_nocache();
    if ($_conf['doctype']) { 
        echo str_replace(
            array('Transitional', 'loose.dtd'),
            array('Frameset', 'frameset.dtd'),
            $_conf['doctype']);
    }
    echo <<<EOHEADER
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    {$_conf['extra_headers_ht']}
    <title>{$ptitle}</title>
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
</head>\n
EOHEADER;

    if (!$sidebar) {
        echo <<<EOMENUFRAME
<frameset id="menuframe" cols="{$_conf['frame_menu_width']},*" border="1">
    <frame src="menu.php" id="menu" name="menu" scrolling="auto" frameborder="1">\n
EOMENUFRAME;
    }

    echo <<<EOMAINFRAME
    <frameset id="mainframe" rows="{$_conf['frame_subject_width']},{$_conf['frame_read_width']}" border="2">
        <frame src="{$title_page}" id="subject" name="subject" scrolling="auto" frameborder="1">
        <frame src="{$htm['read_page']}" id="read" name="read" scrolling="auto" frameborder="1">
    </frameset>\n
EOMAINFRAME;

    if (!$sidebar) {
        echo <<<EONOFRAMES
</frameset>
<noframes>
    <body>
        <h1>{$ptitle}</h1>
        <ul>
            <li>�@�g�їpURL: <a href="{$me_url_b}k">{$me_url_b}k</a></li>
            <li>iPhone�pURL: <a href="{$me_url_b}i">{$me_url_b}i</a></li>
        </ul>
    </body>
</noframes>\n
EONOFRAMES;
    }

    echo '</html>';
}

// {{{ makeDenyHtaccess()

/**
 * �f�B���N�g���Ɂi�A�N�Z�X���ۂ̂��߂́j .htaccess ���Ȃ���΁A�����Ő�������
 */
function makeDenyHtaccess($dir)
{
    $hta = $dir . '/.htaccess';
    if (!file_exists($hta)) {
        $data = 'Order allow,deny'."\n".'Deny from all'."\n";
        FileCtl::file_write_contents($hta, $data);
    }
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
