<?php
/**
 * rep2 - ���j���[
 * �t���[��������ʁA�������� PC�p
 *
 * menu.php, menu_side.php ���ǂݍ��܂��
 */

$_login->authorize(); //���[�U�F��

//==============================================================
// �ϐ��ݒ�
//==============================================================
$me_url = P2Util::getMyUrl();
$me_dir_url = dirname($me_url);
// menu_side.php �� URL�B�i���[�J���p�X�w��͂ł��Ȃ��悤���j
$menu_side_url = $me_dir_url.'/menu_side.php';

$brd_menus = array();
$matome_i = 0;

if (isset($_GET['word'])) {
    $word = $_GET['word'];
} elseif (isset($_POST['word'])) {
    $word = $_POST['word'];
}
$hd = array('word' => '');
$GLOBALS['ita_mikke'] = array('num' => 0);

// ����
if (isset($word) && strlen($word) > 0) {
    if (substr_count($word, '.') == strlen($word)) {
        $word = null;
    } elseif (p2_set_filtering_word($word, 'and') !== null) {
        $hd['word'] = htmlspecialchars($word, ENT_QUOTES);
    } else {
        $word = null;
    }
}

//============================================================
// ����ȑO�u����
//============================================================
// ���C�ɔ̒ǉ��E�폜
if (isset($_GET['setfavita'])) {
    require_once P2_LIB_DIR . '/setfavita.inc.php';
    setFavIta();
}

//================================================================
// �����C��
//================================================================
$aShowBrdMenuPc = new ShowBrdMenuPc();

//============================================================
// ���w�b�_
//============================================================
$reloaded_time = date('n/j G:i:s'); // �X�V����
$ptitle = 'p2 - menu';

P2Util::header_nocache();
echo $_conf['doctype'];
echo <<<EOP
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    {$_conf['extra_headers_ht']}\n
EOP;

// �����X�V
if ($_conf['menu_refresh_time']) {
    $refresh_time_s = $_conf['menu_refresh_time'] * 60;
    echo <<<EOP
    <meta http-equiv="refresh" content="{$refresh_time_s};URL={$me_url}?new=1">\n
EOP;
}

echo <<<EOP
    <title>{$ptitle}</title>
    <base target="subject">
    <link rel="stylesheet" type="text/css" href="css.php?css=style&amp;skin={$skin_en}">
    <link rel="stylesheet" type="text/css" href="css.php?css=menu&amp;skin={$skin_en}">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
    <script type="text/javascript" src="js/basic.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/showhide.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/menu.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/tgrepctl.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript">
    //<![CDATA[
    function addSidebar(title, url) {
       if ((typeof window.sidebar == "object") && (typeof window.sidebar.addPanel == "function")) {
          window.sidebar.addPanel(title, url, '');
       } else {
          goNetscape();
       }
    }
    function goNetscape()
    {
    //  var rv = window.confirm ("This page is enhanced for use with Netscape 7.  " + "Would you like to upgrade now?");
       var rv = window.confirm ("���̃y�[�W�� Netscape 7 �p�Ɋg������Ă��܂�.  " + "�������A�b�v�f�[�g���܂���?");
       if (rv)
          document.location.href = "http://home.netscape.com/ja/download/download_n6.html";
    }

    function chUnColor(idnum){
        unid='un'+idnum;
        document.getElementById(unid).style.color="{$STYLE['menu_color']}";
    }

    function chMenuColor(idnum){
        newthreid='newthre'+idnum;
        if(document.getElementById(newthreid)){document.getElementById(newthreid).style.color="{$STYLE['menu_color']}";}
        unid='un'+idnum;
        document.getElementById(unid).style.color="{$STYLE['menu_color']}";
    }

    //]]>
    </script>\n
EOP;
    if ($_conf['expack.ic2.enabled']) {
    echo <<<EOP
    <script type="text/javascript" src="js/ic2_switch.js?{$_conf['p2_version_id']}"></script>\n
EOP;
}
echo <<<EOP
</head>
<body>\n
EOP;

P2Util::printInfoHtml();

if (!empty($sidebar)) {
    echo <<<EOP
<p><a href="index.php?sidebar=true" target="_content">p2 - 2�y�C���\��</a></p>\n
EOP;
}

if ($_conf['enable_menu_new']) {
    echo <<<EOP
$reloaded_time [<a href="{$_SERVER['SCRIPT_NAME']}?new=1" target="_self">�X�V</a>]
EOP;
}

//==============================================================
// ���N�C�b�N����
//==============================================================

    echo <<<EOP
<div id="c_search">\n
EOP;

if ($_conf['input_type_search']) {
// {{{ <input type="search">���g��

    // ����
    echo <<<EOP
    <form method="GET" action="{$_SERVER['SCRIPT_NAME']}" accept-charset="{$_conf['accept_charset']}" target="_self" class="inline-form">
        <input type="search" name="word" value="{$hd['word']}" size="16" autosave="rep2.expack.search.menu" results="10" placeholder="����">
        {$_conf['detect_hint_input_ht']}{$_conf['k_input_ht']}
    </form><br />\n
EOP;
    // �X���^�C����
    echo <<<EOP
    <form method="GET" action="tgrepc.php" accept-charset="{$_conf['accept_charset']}" target="subject" class="inline-form">
        <input type="search" name="Q" value="" size="16" autosave="rep2.expack.search.thread" results="{$_conf['expack.tgrep.recent2_num']}" placeholder="�X���^�C����">
        {$_conf['detect_hint_input_ht']}{$_conf['k_input_ht']}
    </form><br>\n
EOP;

// }}}
} else {
// {{{ �ʏ�̌����t�H�[��

    // ����
    echo <<<EOP
    <form method="GET" action="{$_SERVER['SCRIPT_NAME']}" accept-charset="{$_conf['accept_charset']}" target="_self" class="inline-form" style="white-space:nowrap">
        <input type="text" name="word" value="{$hd['word']}" size="12"><input type="submit" name="submit" value="��">
        {$_conf['detect_hint_input_ht']}{$_conf['k_input_ht']}
    </form><br>\n
EOP;
    // �X���^�C����
    echo <<<EOP
    <form method="GET" action="tgrepc.php" accept-charset="{$_conf['accept_charset']}" target="subject" class="inline-form" style="white-space:nowrap">
        <input type="text" name="Q" value="" size="12"><input type="submit" value="��">
        {$_conf['detect_hint_input_ht']}{$_conf['k_input_ht']}
    </form><br>\n
EOP;

// }}}
}

echo <<<EOP
</div>\n
EOP;

//==============================================================
// �����C�ɔ��v�����g����
//==============================================================
$aShowBrdMenuPc->printFavIta();

flush();

//==============================================================
// ��tGrep�ꔭ�������v�����g����
//==============================================================
if ($_conf['expack.tgrep.quicksearch']) {
    require_once P2EX_LIB_DIR . '/tgrep/menu_quick.inc.php';
}

//==============================================================
// ��tGrep�����������v�����g����
//==============================================================
if ($_conf['expack.tgrep.recent_num'] > 0) {
    require_once P2EX_LIB_DIR . '/tgrep/menu_recent.inc.php';
}

//==============================================================
// ��RSS���v�����g����
//==============================================================
if ($_conf['expack.rss.enabled']) {
    require_once P2EX_LIB_DIR . '/rss/menu.inc.php';
}

flush();

//==============================================================
// ������
//==============================================================
$norefresh_q = '&amp;norefresh=true';

echo <<<EOP
<div class="menu_cate"><b><a class="menu_cate" href="javascript:void(0);" onclick="showHide('c_spacial');" target="_self">����</a></b>
EOP;
if ($_conf['expack.misc.multi_favs']) {
    $favlist_onchange = "openFavList('{$_conf['subject_php']}', this.options[this.selectedIndex].value, window.top.subject);";
    echo "<br>\n";
    echo FavSetManager::makeFavSetSwitchElem('m_favlist_set', '���C�ɃX��', FALSE, $favlist_onchange);
}
echo <<<EOP
    <div class="itas" id="c_spacial">
EOP;

// ���V������\������ꍇ
if ($_conf['enable_menu_new'] == 1 && $_GET['new']) {
    // ����_�E�����[�h�̐ݒ�
    if ($_conf['expack.use_pecl_http'] == 1) {
        P2HttpExt::activate();
        $GLOBALS['expack.subject.multi-threaded-download.done'] = true;
    } elseif ($_conf['expack.use_pecl_http'] == 2) {
        $GLOBALS['expack.subject.multi-threaded-download.done'] = true;
    }

    // {{{ ���C�ɃX��

    // �_�E�����[�h
    if ($_conf['expack.use_pecl_http'] == 1) {
        P2HttpRequestPool::fetchSubjectTxt($_conf['favlist_idx']);
    } elseif ($_conf['expack.use_pecl_http'] == 2) {
        P2CommandRunner::fetchSubjectTxt('fav', $_conf);
    }

    // �V������������
    initMenuNewSp('fav');
    echo <<<EOP
    �@<a href="{$_conf['subject_php']}?spmode=fav{$norefresh_q}" onclick="chMenuColor({$matome_i});" accesskey="f">���C�ɃX��</a> (<a href="{$_conf['read_new_php']}?spmode=fav" target="read" id="un{$matome_i}" onclick="chUnColor({$matome_i});"{$class_newres_num}>{$shinchaku_num}</a>)<br>
EOP;
    flush();

    // }}}
    // {{{ �ŋߓǂ񂾃X��

    // �_�E�����[�h
    if ($_conf['expack.use_pecl_http'] == 1) {
        P2HttpRequestPool::fetchSubjectTxt($_conf['recent_idx']);
    } elseif ($_conf['expack.use_pecl_http'] == 2) {
        P2CommandRunner::fetchSubjectTxt('recent', $_conf);
    }

    // �V������������
    initMenuNewSp('recent');
    echo <<<EOP
    �@<a href="{$_conf['subject_php']}?spmode=recent{$norefresh_q}" onclick="chMenuColor({$matome_i});" accesskey="h">�ŋߓǂ񂾃X��</a> (<a href="{$_conf['read_new_php']}?spmode=recent" target="read" id="un{$matome_i}" onclick="chUnColor({$matome_i});"{$class_newres_num}>{$shinchaku_num}</a>)<br>
EOP;
    flush();

    // }}}
    // {{{ �������ݗ���

    // �_�E�����[�h
    if ($_conf['expack.use_pecl_http'] == 1) {
        P2HttpRequestPool::fetchSubjectTxt($_conf['res_hist_idx']);
    } elseif ($_conf['expack.use_pecl_http'] == 2) {
        P2CommandRunner::fetchSubjectTxt('res_hist', $_conf);
    }

    // �V������������
    initMenuNewSp('res_hist');
    echo <<<EOP
    �@<a href="{$_conf['subject_php']}?spmode=res_hist{$norefresh_q}" onclick="chMenuColor({$matome_i});">��������</a> <a href="read_res_hist.php" target="read">���O</a> (<a href="{$_conf['read_new_php']}?spmode=res_hist" target="read" id="un{$matome_i}" onclick="chUnColor({$matome_i});"{$class_newres_num}>{$shinchaku_num}</a>)<br>
EOP;
    flush();

    // }}}
// �V������\�����Ȃ��ꍇ
} else {
    echo <<<EOP
    �@<a href="{$_conf['subject_php']}?spmode=fav{$norefresh_q}" accesskey="f">���C�ɃX��</a><br>
    �@<a href="{$_conf['subject_php']}?spmode=recent{$norefresh_q}" accesskey="h">�ŋߓǂ񂾃X��</a><br>
    �@<a href="{$_conf['subject_php']}?spmode=res_hist{$norefresh_q}">��������</a> (<a href="./read_res_hist.php" target="read">���O</a>)<br>
EOP;
}

echo <<<EOP
    �@<a href="{$_conf['subject_php']}?spmode=palace{$norefresh_q}" title="DAT�����X���p�̂��C�ɓ���">�X���̓a��</a><br>
    �@<a href="setting.php">���O�C���Ǘ�</a><br>
    �@<a href="editpref.php">�ݒ�Ǘ�</a><br>
    �@<a href="import.php" onclick="return OpenSubWin('import.php', 600, 380, 0, 0);">dat�̃C���|�[�g</a><br>
    �@<a href="http://find.2ch.net/" target="_blank" title="2ch��������">find.2ch.net</a>
    </div>
</div>\n
EOP;

//==============================================================
// ��ImageCache2
//==============================================================
if ($_conf['expack.ic2.enabled']) {
    if (!class_exists('IC2_Switch', false)) {
        include P2EX_LIB_DIR . '/ic2/Switch.php';
    }
    if (IC2_Switch::get()) {
        $ic2sw = array('inline', 'none');
    } else {
        $ic2sw = array('none', 'inline');
    }

    echo <<<EOP
<div class="menu_cate"><b class="menu_cate" onclick="showHide('c_ic2');">ImageCache2</b>
    (<a href="#" id="ic2_switch_on" onclick="return ic2_menu_switch(0);" style="display:{$ic2sw[0]};font-weight:bold;">ON</a><a href="#" id="ic2_switch_off" onclick="return ic2_menu_switch(1);" style="display:{$ic2sw[1]};font-weight:bold;">OFF</a>)<br>
    <div class="itas" id="c_ic2">
    �@<a href="iv2.php" target="_blank">�摜�L���b�V���ꗗ</a><br>
    �@<a href="ic2_setter.php">�A�b�v���[�_</a>
        (<a href="#" onclick="return OpenSubWin('ic2_setter.php?popup=1', 480, 320, 1, 1);">p</a>)<br>
    �@<a href="ic2_getter.php">�_�E�����[�_</a>
        (<a href="#" onclick="return OpenSubWin('ic2_getter.php?popup=1', 480, 320, 1, 1);">p</a>)<br>
    �@<a href="ic2_manager.php">�f�[�^�x�[�X�Ǘ�</a>
    </div>
</div>
EOP;
}

//==============================================================
// ���J�e�S���Ɣ�\��
//==============================================================
// brd�ǂݍ���
$brd_menus_dir = BrdCtl::read_brd_dir();
$brd_menus_online = BrdCtl::read_brd_online();
$brd_menus = array_merge($brd_menus_dir, $brd_menus_online);

//===========================================================
// ���v�����g
//===========================================================

// {{{ �������[�h�������

if (isset($word) && strlen($word) > 0) {

    $msg_ht .= '<p>';
    if (empty($GLOBALS['ita_mikke']['num'])) {
        if (empty($GLOBALS['threti_match_ita_num'])) {
            $msg_ht .=  "\"{$hd['word']}\"���܂ޔ͌�����܂���ł����B\n";
        }
    } else {
        $match_cates = array();
        $match_cates[0] = new BrdMenuCate("&quot;{$hd['word']}&quot;���܂ޔ� {$GLOBALS['ita_mikke']['num']}hit!\n");
        $match_cates[0]->is_open = true;
        foreach ($brd_menus as $a_brd_menu) {
            if (!empty($a_brd_menu->matches)) {
                foreach ($a_brd_menu->matches as $match_ita) {
                    $match_cates[0]->addBrdMenuIta(clone $match_ita);
                }
            }
        }
        ob_start();
        $aShowBrdMenuPc->printBrdMenu($match_cates);
        $msg_ht .= ob_get_clean();

        // �������ʂ���Ȃ�A�����Ŕꗗ���J��
        if ($GLOBALS['ita_mikke']['num'] == 1) {
        $msg_ht .= '�i�����I�[�v�������j';
            echo <<<EOP
<script type="text/javascript">
//<![CDATA[
    parent.subject.location.href="{$_conf['subject_php']}?host={$GLOBALS['ita_mikke']['host']}&bbs={$GLOBALS['ita_mikke']['bbs']}&itaj_en={$GLOBALS['ita_mikke']['itaj_en']}";
//]]>
</script>
EOP;
        }
    }
    $msg_ht .= '</p>';

    P2Util::pushInfoHtml($msg_ht);
} else {
    $match_cates = null;
}

// }}}

P2Util::printInfoHtml();

if ($_conf['menu_hide_brds'] && !$GLOBALS['ita_mikke']['num']) {
    $brd_menus_style = ' style="display:none"';
} else {
    $brd_menus_style = '';
}
// board�f�B���N�g������ǂݍ��񂾃��[�U��`�J�e�S�����j���[��\��
if ($brd_menus_dir) {
    $brd_menus_title = ($brd_menus_online) ? '�ꗗ (private)' : '�ꗗ';
    echo <<<EOP
<hr>
<div class="menu_cate"><b class="menu_cate" onclick="showHide('c_private_boards');">�y{$brd_menus_title}�z</b><br>
    <div id="c_private_boards"{$brd_menus_style}>\n
EOP;
    foreach ($brd_menus_dir as $a_brd_menu) {
        $aShowBrdMenuPc->printBrdMenu($a_brd_menu->categories);
    }
    echo <<<EOP
    </div>
</div>\n
EOP;
}
// �I�����C���J�e�S�����j���[��\��
if ($brd_menus_online) {
    $brd_menus_title = ($brd_menus_dir) ? '�ꗗ (online)' : '�ꗗ';
    echo <<<EOP
<hr>
<div class="menu_cate"><b class="menu_cate" onclick="showHide('c_online_boards');">�y{$brd_menus_title}�z</b><br>
    <div id="c_online_boards"{$brd_menus_style}>\n
EOP;
    foreach ($brd_menus_online as $a_brd_menu) {
        $aShowBrdMenuPc->printBrdMenu($a_brd_menu->categories);
    }
    echo <<<EOP
    </div>
</div>\n
EOP;
}

//==============================================================
// �t�b�^��\��
//==============================================================

// ��for Mozilla Sidebar
if (empty($sidebar)) {
    echo <<<EOP
<script type="text/javascript">
//<![CDATA[
if ((typeof window.sidebar == "object") && (typeof window.sidebar.addPanel == "function")) {
    document.writeln("<p><a href=\"javascript:void(0);\" onclick=\"addSidebar('p2 Menu', '{$menu_side_url}');\">p2 Menu�� Sidebar �ɒǉ�<" + "/a><" + "/p>");
}
//]]>
</script>\n
EOP;
}

echo '</body></html>';


//==============================================================
// �֐�
//==============================================================
/**
 * spmode�p��menu�̐V����������������
 */
function initMenuNewSp($spmode_in)
{
    global $shinchaku_num, $matome_i, $host, $bbs, $spmode, $STYLE, $class_newres_num;
    $matome_i++;
    $host = "";
    $bbs = "";
    $spmode = $spmode_in;
    include P2_LIB_DIR . '/subject_new.inc.php'; // $shinchaku_num, $_newthre_num ���Z�b�g
    if ($shinchaku_num > 0) {
        $class_newres_num = ' class="newres_num"';
    } else {
        $class_newres_num = ' class="newres_num_zero"';
    }
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
