<?php
/**
 *  rep2 - ���[�U�ݒ�ҏWUI
 */

require_once './conf/conf.inc.php';
require_once P2_CONF_DIR . '/conf_user_def.inc.php';

$_login->authorize(); // ���[�U�F��

$csrfid = P2Util::getCsrfId(__FILE__);

if (!empty($_POST['submit_save']) || !empty($_POST['submit_default'])) {
    if (!isset($_POST['csrfid']) or $_POST['csrfid'] != $csrfid) {
        p2die('�s���ȃ|�X�g�ł�');
    }
}

define('P2_EDIT_CONF_USER_DEFAULT',     0);
define('P2_EDIT_CONF_USER_LONGTEXT',    1);
define('P2_EDIT_CONF_USER_HIDDEN',      2);
define('P2_EDIT_CONF_USER_DISABLED',    4);
define('P2_EDIT_CONF_USER_SKIPPED',     8);
define('P2_EDIT_CONF_USER_PASSWORD',   16);
define('P2_EDIT_CONF_FILE_ADMIN',    1024);
define('P2_EDIT_CONF_FILE_ADMIN_EX', 2048);

//=====================================================================
// �O����
//=====================================================================

// {{{ �ۑ��{�^����������Ă�����A�ݒ��ۑ�

if (!empty($_POST['submit_save'])) {

    // �l�̓K���`�F�b�N�A����

    // �g����
    $_POST['conf_edit'] = array_map('trim', $_POST['conf_edit']);

    // �I�����ɂȂ����� �� �f�t�H���g����
    notSelToDef();

    // ���[����K�p����
    applyRules();

    // �|�X�g���ꂽ�l > ���݂̒l > �f�t�H���g�l �̏��ŐV�����ݒ���쐬����
    $conf_save = array('.' => P2_VERSION_ID);
    foreach ($conf_user_def as $k => $v) {
        if (array_key_exists($k, $_POST['conf_edit'])) {
            $conf_save[$k] = $_POST['conf_edit'][$k];
        } elseif (array_key_exists($k, $_conf)) {
            $conf_save[$k] = $_conf[$k];
        } else {
            $conf_save[$k] = $v;
        }
    }

    // �V���A���C�Y���ĕۑ�
    FileCtl::make_datafile($_conf['conf_user_file'], $_conf['conf_user_perm']);
    if (FileCtl::file_write_contents($_conf['conf_user_file'], serialize($conf_save)) === false) {
        $_info_msg_ht .= "<p>�~�ݒ���X�V�ۑ��ł��܂���ł���</p>";
    } else {
        $_info_msg_ht .= "<p>���ݒ���X�V�ۑ����܂���</p>";
        // �ύX������΁A�����f�[�^���X�V���Ă���
        $_conf = array_merge($_conf, $conf_user_def, $conf_save);
    }

    unset($conf_save);

// }}}
// {{{ �f�t�H���g�ɖ߂��{�^����������Ă�����

} elseif (!empty($_POST['submit_default'])) {
    if (file_exists($_conf['conf_user_file']) and unlink($_conf['conf_user_file'])) {
        $_info_msg_ht .= "<p>���ݒ���f�t�H���g�ɖ߂��܂���</p>";
        // �ύX������΁A�����f�[�^���X�V���Ă���
        $_conf = array_merge($_conf, $conf_user_def);
        if (is_array($conf_save)) {
            $_conf = array_merge($_conf, $conf_save);
        }
    }
}

// }}}
// {{{ �g�тŕ\������O���[�v

if ($_conf['ktai']) {
    if (isset($_REQUEST['edit_conf_user_group_en'])) {
        $selected_group = UrlSafeBase64::decode($_REQUEST['edit_conf_user_group_en']);
    } elseif (isset($_REQUEST['edit_conf_user_group'])) {
        $selected_group = $_REQUEST['edit_conf_user_group'];
    } else {
        $selected_group = null;
    }
} else {
    $selected_group = 'all';
    if (isset($_REQUEST['active_tab1'])) {
        $active_tab1 = $_REQUEST['active_tab1'];
        $active_tab1_ht = htmlspecialchars($active_tab1, ENT_QUOTES);
        $active_tab1_js = "'" . StrCtl::toJavaScript($active_tab1) . "'";
    } else {
        $active_tab1 = null;
        $active_tab1_ht = '';
        $active_tab1_js = 'null';
    }
    if (isset($_REQUEST['active_tab2'])) {
        $active_tab2 = $_REQUEST['active_tab2'];
        $active_tab2_ht = htmlspecialchars($active_tab2, ENT_QUOTES);
        $active_tab2_js = "'" . StrCtl::toJavaScript($active_tab2) . "'";
    } else {
        $active_tab2 = null;
        $active_tab2_ht = '';
        $active_tab2_js = 'null';
    }
    $parent_tabs_js = "['" . implode("','", array(
        StrCtl::toJavaScript('rep2��{�ݒ�'),
        StrCtl::toJavaScript('�g�ђ[���ݒ�'),
        StrCtl::toJavaScript('�g���p�b�N�ݒ�'),
    )) . "']";
    $active_tab_hidden_ht = <<<EOP
<input type="hidden" id="active_tab1" name="active_tab1" value="{$active_tab1_ht}">
<input type="hidden" id="active_tab2" name="active_tab2" value="{$active_tab2_ht}">
<script type="text/javascript">
// <![CDATA[
_EDIT_CONF_USER_JS_PARENT_TABS = $parent_tabs_js;
_EDIT_CONF_USER_JS_ACTIVE_TAB1 = $active_tab1_js;
_EDIT_CONF_USER_JS_ACTIVE_TAB2 = $active_tab2_js;
// ]]>
</script>
EOP;
}

$groups = array();
$keep_old = false;

// }}}

//=====================================================================
// �v�����g�ݒ�
//=====================================================================
$ptitle = '���[�U�ݒ�ҏW';

$me = P2Util::getMyUrl();

//=====================================================================
// �v�����g
//=====================================================================
// �w�b�_HTML���v�����g
P2Util::header_nocache();
echo $_conf['doctype'];
echo <<<EOP
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    {$_conf['extra_headers_ht']}
    <title>{$ptitle}</title>\n
EOP;

if (!$_conf['ktai']) {
    echo <<<EOP
    <script type="text/javascript" src="js/basic.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/tabber/tabber.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/edit_conf_user.js?{$_conf['p2_version_id']}"></script>
    <link rel="stylesheet" type="text/css" href="css.php?css=style&amp;skin={$skin_en}">
    <link rel="stylesheet" type="text/css" href="css.php?css=edit_conf_user&amp;skin={$skin_en}">
    <link rel="stylesheet" type="text/css" href="css/tabber/tabber.css?{$_conf['p2_version_id']}">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">\n
EOP;
}

$body_at = ($_conf['ktai']) ? $_conf['k_colors'] : '';
echo <<<EOP
</head>
<body{$body_at}>\n
EOP;

// PC�p�\��
if (!$_conf['ktai']) {
    echo <<<EOP
<p id="pan_menu"><a href="editpref.php">�ݒ�Ǘ�</a> &gt; {$ptitle} �i<a href="{$me}">�����[�h</a>�j</p>\n
EOP;
}

// �g�їp�\��
if ($_conf['ktai']) {
    $htm['form_submit'] = <<<EOP
<input type="submit" name="submit_save" value="�ύX��ۑ�����">\n
EOP;
}

// ��񃁃b�Z�[�W�\��
echo $_info_msg_ht;
$_info_msg_ht = "";

echo <<<EOP
<form id="edit_conf_user_form" method="POST" action="{$_SERVER['SCRIPT_NAME']}" target="_self" accept-charset="{$_conf['accept_charset']}">
    <input type="hidden" name="csrfid" value="{$csrfid}">\n
EOP;

// PC�p�\��
if (!$_conf['ktai']) {
    echo $active_tab_hidden_ht;
    echo <<<EOP
<div class="tabber">
<div class="tabbertab" title="rep2��{�ݒ�">
<h3>rep2��{�ݒ�</h3>
<div class="tabber">\n
EOP;
// �g�їp�\��
} else {
    if (!empty($selected_group)) {
        echo $htm['form_submit'];
    }
}

// {{{ rep2��{�ݒ�
// {{{ 'be/p2'

$groupname = 'be/p2';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname);
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('be_2ch_code', '<a href="http://be.2ch.net/" target="_blank">be.2ch.net</a>�̔F�؃R�[�h(�p�X���[�h�ł͂Ȃ�)', P2_EDIT_CONF_USER_LONGTEXT),
        array('be_2ch_mail', 'be.2ch.net�̓o�^���[���A�h���X', P2_EDIT_CONF_USER_LONGTEXT),
        array('p2_2ch_mail', '<a href="http://p2.2ch.net/" target="_blank">p2.2ch.net</a>�̓o�^���[���A�h���X', P2_EDIT_CONF_USER_LONGTEXT),
        array('p2_2ch_pass', 'p2.2ch.net�̃��O�C���p�X���[�h', P2_EDIT_CONF_USER_LONGTEXT | P2_EDIT_CONF_USER_PASSWORD),
        array('p2_2ch_ignore_cip', ' p2.2ch.net Cookie�F�؎���IP�A�h���X�̓��ꐫ���`�F�b�N'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ PATH

$groupname = 'PATH';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname);
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
//        array('first_page', '�E�������ɍŏ��ɕ\�������y�[�W�B�I�����C��URL���B'),
        array('brdfile_online',
'���X�g�̎w��i�I�����C��URL�j<br>
���X�g���I�����C��URL���玩���œǂݍ��ށB
�w���� menu.html �`���A2channel.brd �`���̂ǂ���ł��悢�B
<!-- �K�v�Ȃ���΁A�󔒂ɁB --><br>
2ch��{ <a href="http://menu.2ch.net/bbsmenu.html" target="_blank">http://menu.2ch.net/bbsmenu.html</a><br>
2ch + �O��BBS <a href="http://azlucky.s25.xrea.com/2chboard/bbsmenu.html" target="_blank">http://azlucky.s25.xrea.com/2chboard/bbsmenu.html</a>',
            P2_EDIT_CONF_USER_LONGTEXT),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ subject

$groupname = 'subject';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname);
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('refresh_time', '�X���b�h�ꗗ�̎����X�V�Ԋu (���w��B0�Ȃ玩���X�V���Ȃ�)'),

        array('sb_show_motothre', '�X���b�h�ꗗ�Ŗ��擾�X���ɑ΂��Č��X���ւ̃����N�i�E�j��\��'),
        array('sb_show_one', '�X���b�h�ꗗ�i�\���j��&gt;&gt;1��\��'),
        array('sb_show_spd', '�X���b�h�ꗗ�ł��΂₳�i���X�Ԋu�j��\��'),
        array('sb_show_ikioi', '�X���b�h�ꗗ�Ő����i1��������̃��X���j��\��'),
        array('sb_show_fav', '�X���b�h�ꗗ�ł��C�ɃX���}�[�N����\��'),
        array('sb_sort_ita', '�\���̃X���b�h�ꗗ�ł̃f�t�H���g�̃\�[�g�w��'),
        array('sort_zero_adjust', '�V���\�[�g�ł́u�����Ȃ��v�́u�V�����[���v�ɑ΂���\�[�g�D�揇��'),
        array('cmp_dayres_midoku', '�����\�[�g���ɐV�����X�̂���X����D��'),
        array('cmp_title_norm', '�^�C�g���\�[�g���ɑS�p���p�E�啶���������𖳎�'),
        array('viewall_kitoku', '�����X���͕\�������Ɋւ�炸�\��'),

        array('sb_ttitle_max_len', '�X���b�h�ꗗ�ŕ\������^�C�g���̒����̏�� (0�Ŗ�����)'),
        array('sb_ttitle_trim_len', '�X���b�h�^�C�g���������̏�����z�����Ƃ��A���̒����܂Ő؂�l�߂�'),
        array('sb_ttitle_trim_pos', '�X���b�h�^�C�g����؂�l�߂�ʒu'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ read

$groupname = 'read';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname);
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('respointer', '�X�����e�\�����A���ǂ̉��R�O�̃��X�Ƀ|�C���^�����킹�邩'),
        array('before_respointer', '�|�C���^�̉��R�O�̃��X����\�����邩'),
        array('before_respointer_new', '�V���܂Ƃߓǂ݂̎��A�|�C���^�̉��R�O�̃��X����\�����邩'),
        array('rnum_all_range', '�V���܂Ƃߓǂ݂ň�x�ɕ\�����郌�X��'),
        array('preview_thumbnail', '�摜URL�̐�ǂ݃T���l�C����\��'),
        array('pre_thumb_limit', '�摜URL�̐�ǂ݃T���l�C������x�ɕ\�����鐧���� (0�Ŗ�����)'),
//        array('pre_thumb_height', '�摜�T���l�C���̏c�̑傫�����w�� (�s�N�Z��)'),
//        array('pre_thumb_width', '�摜�T���l�C���̉��̑傫�����w�� (�s�N�Z��)'),
        array('link_youtube', 'YouTube�̃����N���v���r���[�\��<br>(�蓮�̏ꍇ��URL�̉���<img src="img/show.png" width="30" height="12" alt="show">���N���b�N���ĕ\��)'),
        array('link_niconico', '�j�R�j�R����̃����N���v���r���[�\��<br>(�蓮�̏ꍇ��URL�̉���<img src="img/show.png" width="30" height="12" alt="show">���N���b�N���ĕ\��)'),
        array('iframe_popup', 'HTML�|�b�v�A�b�v'),
        array('iframe_popup_event', 'HTML�|�b�v�A�b�v������ꍇ�̃C�x���g'),
        array('iframe_popup_type', 'HTML�|�b�v�A�b�v�̎��'),
//        array('iframe_popup_delay', 'HTML�|�b�v�A�b�v�̕\���x������ (�b)'),
        array('flex_idpopup', 'ID:xxxxxxxx��ID�t�B���^�����O�̃����N�ɕϊ�'),
        array('ext_win_target', '�O���T�C�g���փW�����v���鎞�ɊJ���E�B���h�E�̃^�[�Q�b�g��<br>(��Ȃ瓯���E�C���h�E�A_blank �ŐV�����E�C���h�E)'),
        array('bbs_win_target', 'rep2�Ή�BBS�T�C�g���ŃW�����v���鎞�ɊJ���E�B���h�E�̃^�[�Q�b�g��<br>(��Ȃ瓯���E�C���h�E�A_blank �ŐV�����E�C���h�E)'),
        array('bottom_res_form', '�X���b�h�����ɏ������݃t�H�[����\��'),
        array('quote_res_view', '���p���X��\��'),
        array('quote_res_view_ng', 'NG���X�����p���X�\�����邩'),
        array('quote_res_view_aborn', '���ځ[�񃌃X�����p���X�\�����邩'),
        array('strip_linebreaks', '�����̉��s�ƘA��������s������'),
        array('link_wikipedia', '[[�P��]]��Wikipedia�ւ̃����N�ɂ���'),
        array('backlink_list', '�t�Q�ƃ|�b�v�A�b�v���X�g�̕\��'),
        array('backlink_list_future_anchor', '�t�Q�ƃ��X�g�Ŗ����A���J�[��L���ɂ��邩'),
        array('backlink_list_range_anchor_limit', '�t�Q�ƃ��X�g�ł��̒l���L���͈̓��X��ΏۊO�ɂ���(0�Ő����Ȃ�)'),
        array('backlink_block', '�t�Q�ƃu���b�N��W�J�ł���悤�ɂ��邩'),
        array('backlink_block_readmark', '�t�Q�ƃu���b�N�œW�J����Ă��郌�X�̖{�̂ɑ������邩'),
        array('backlink_coloring_track', '�{�����_�u���N���b�N����ƒ��F���ă��X�ǐ�'),
        array('backlink_coloring_track_colors', '�{�����_�u���N���b�N�ă��X�ǐՎ��̐F���X�g(�J���}��؂�)'),
        array('coloredid.enable', 'ID�ɐF��t����'),
        array('coloredid.rate.type', '��ʕ\������ID�ɒ��F���Ă�������'),
        array('coloredid.rate.times', '�������o�����̏ꍇ�̐�(n�ȏ�)'),
        array('coloredid.rate.hissi.times', '�K������(ID�u�����N)�̏o����(0�Ŗ����BIE/Safari��blink��Ή�)'),
        array('coloredid.click', 'ID�o�������N���b�N����ƒ��F���g�O��(�u���Ȃ��v�ɂ����Javascript�ł͂Ȃ�PHP�Œ��F)'),
        array('coloredid.marking.colors', 'ID�o�������_�u���N���b�N���ă}�[�L���O�̐F���X�g(�J���}��؂�)'),
        array('coloredid.coloring.type', '�J���[�����O�̃^�C�v�ithermon�ł�PHP�Œ��F(coloredid.click=���Ȃ�)�̏ꍇ�̂ݗL���j'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ NG/���ځ[��

$groupname = 'NG/���ځ[��';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname);
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('ngaborn_frequent', '&gt;&gt;1 �ȊO�̕p�oID�����ځ[�񂷂�'),
        array('ngaborn_frequent_one', '&gt;&gt;1 ���p�oID���ځ[��̑ΏۊO�ɂ���'),
        array('ngaborn_frequent_num', '�p�oID���ځ[��̂������l (�o���񐔂�����ȏ��ID�����ځ[��)'),
        array('ngaborn_frequent_dayres', '�����̑����X���ł͕p�oID���ځ[�񂵂Ȃ�<br>(�����X��/�X�����Ă���̓����A0�Ȃ疳��)'),
        array('ngaborn_chain', '�A��NG���ځ[��<br>�u����v�Ȃ炠�ځ[�񃌃X�ւ̃��X�͂��ځ[��ANG���X�ւ̃��X��NG�B<br>�u���ׂ�NG�ɂ���v�̏ꍇ�A���ځ[�񃌃X�ւ̃��X��NG�ɂ���B'),
        array('ngaborn_chain_all', '�\���͈͊O�̃��X���A��NG���ځ[��̑Ώۂɂ���<br>(�������y�����邽�߁A�f�t�H���g�ł͂��Ȃ�)'),
        array('ngaborn_daylimit', '���̊��ԁANG���ځ[���HIT���Ȃ���΁A�o�^���[�h�������I�ɊO�� (����)'),
        array('ngaborn_purge_aborn', '���ځ[�񃌃X�͕s��div�u���b�N���`�悵�Ȃ�'),
		array('live.highlight_chain', '�A���n�C���C�g (�A���͈͂� ngaborn_chain_all �ɂĐݒ�)'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ ETC

$groupname = 'ETC';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname);
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('frame_menu_width', '�t���[���� ���j���[ �̕\����'),
        array('frame_subject_width', '�t���[���E�� �X���ꗗ �̕\����'),
        array('frame_read_width', '�t���[���E�� �X���{�� �̕\����'),

        array('my_FROM', '���X�������ݎ��̃f�t�H���g�̖��O'),
        array('my_mail', '���X�������ݎ��̃f�t�H���g��mail'),

        array('editor_srcfix', 'PC�{�����A�\�[�X�R�[�h�̃R�s�y�ɓK�����␳������`�F�b�N�{�b�N�X��\��'),

        array('get_new_res', '�V�����X���b�h���擾�������ɕ\�����郌�X��(�S�ĕ\������ꍇ:&quot;all&quot;)'),
        array('rct_rec_num', '�ŋߓǂ񂾃X���̋L�^��'),
        array('res_hist_rec_num', '�������ݗ����̋L�^��'),
        array('res_write_rec', '�������ݓ��e���O���L�^'),
        array('through_ime', '�O��URL�W�����v����ۂɒʂ��Q�[�g<br>�u���ځv�ł�Cookie���g���Ȃ��[���ł� gate.php ��ʂ�'),
        array('through_ime_http_only', ' HTTPS�ŃA�N�Z�X���Ă���Ƃ��͊O��URL�Q�[�g��ʂ��Ȃ�<br>(�ŋ߂�Web�u���E�U�̑����� https �� http �̑J�ڂ�Referer�𑗏o���܂��񂪁A<br>�uHTTPS�ł͒��v�ɂ���ꍇ�́A���g���̃u���E�U�̎d�l���m�F���Ă�������)'),
        array('ime_manual_ext', '�Q�[�g�Ŏ����]�����Ȃ��g���q�i�J���}��؂�ŁA�g���q�̑O�̃s���I�h�͕s�v�j'),
        array('join_favrank', '<a href="http://akid.s17.xrea.com/favrank/favrank.html" target="_blank">���C�ɃX�����L</a>�ɎQ��'),
        array('merge_favita', '���C�ɔ̃X���ꗗ���܂Ƃ߂ĕ\�� (���C�ɔ̐��ɂ���Ă͏����Ɏ��Ԃ�������)'),
        array('favita_order_dnd', '�h���b�O���h���b�v�ł��C�ɔ���בւ���'),
        array('enable_menu_new', '���j���[�ɐV������\��'),
        array('menu_refresh_time', '���j���[�����̎����X�V�Ԋu (���w��B0�Ȃ玩���X�V���Ȃ�)'),
        array('menu_hide_brds', '�J�e�S���ꗗ�������Ԃɂ���'),
        array('brocra_checker_use', '�u���N���`�F�b�J (����, ���Ȃ�)'),
        array('brocra_checker_url', '�u���N���`�F�b�JURL'),
        array('brocra_checker_query', '�u���N���`�F�b�J�̃N�G���[ (��̏ꍇ�APATH_INFO��URL��n��)'),
        array('enable_exfilter', '�t�B���^�����O��AND/OR�������\�ɂ���'),
        array('proxy_use', '�v���L�V�𗘗p'), 
        array('proxy_host', '�v���L�V�z�X�g ex)&quot;127.0.0.1&quot;, &quot;p2proxy.example&quot;'), 
        array('proxy_port', '�v���L�V�|�[�g ex)&quot;8080&quot;'), 
        array('precede_openssl', '�����O�C�����A�܂���openssl�Ŏ��݂�<br>(OpenSSL���ÓI�Ƀ����N����Ă���K�v������)'),
        array('precede_phpcurl', 'curl���g�����A�R�}���h���C���ł�PHP�֐��łǂ����D�悷�邩'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// }}}

// PC�p�\��
if (!$_conf['ktai']) {
    echo <<<EOP
</div><!-- end of tab -->
</div><!-- end of child tabset "rep2��{�ݒ�" -->

<div class="tabbertab" title="�g�ђ[���ݒ�">
<h3>�g�ђ[���ݒ�</h3>
<div class="tabber">\n
EOP;
}

// {{{ �g�ђ[���ݒ�
// {{{ Mobile

$groupname = 'mobile';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname);
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('mobile.background_color', '�w�i�F'),
        array('mobile.text_color', '��{�����F'),
        array('mobile.link_color', '�����N�F'),
        array('mobile.vlink_color', '�K��ς݃����N�F'),
        array('mobile.newthre_color', '�V���X���b�h�}�[�N�̐F'),
        array('mobile.ttitle_color', '�X���b�h�^�C�g���̐F'),
        array('mobile.newres_color', '�V�����X�ԍ��̐F'),
        array('mobile.ngword_color', 'NG���[�h�̐F'),
        array('mobile.onthefly_color', '�I���U�t���C���X�ԍ��̐F'),
        array('mobile.match_color', '�t�B���^�����O�Ń}�b�`�����L�[���[�h�̐F'),
        array('mobile.display_accesskey', '�A�N�Z�X�L�[�̔ԍ���\��'),
        array('mobile.save_packet', '�p�P�b�g�ʂ����炷���߁A�S�p�p���E�J�i�E�X�y�[�X�𔼊p�ɕϊ�'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ Mobile - subject

$groupname = 'subject (mobile)';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname);
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('mobile.sb_show_first', '�X���b�h�ꗗ�i�\���j���珉�߂ẴX�����J�����̕\�����@'),
        array('mobile.sb_disp_range', '��x�ɕ\������X���̐�'),
        array('mobile.sb_ttitle_max_len', '�X���b�h�ꗗ�ŕ\������^�C�g���̒����̏�� (0�Ŗ�����)'),
        array('mobile.sb_ttitle_trim_len', '�X���b�h�^�C�g���������̏�����z�����Ƃ��A���̒����܂Ő؂�l�߂�'),
        array('mobile.sb_ttitle_trim_pos', '�X���b�h�^�C�g����؂�l�߂�ʒu'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ Mobile - read

$groupname = 'read (mobile)';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname);
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('mobile.rnum_range', '��x�ɕ\�����郌�X�̐�'),
        array('mobile.res_size', '��̃��X�̍ő�\���T�C�Y'),
        array('mobile.ryaku_size', '���X���ȗ������Ƃ��̕\���T�C�Y'),
        array('mobile.aa_ryaku_size', 'AA�炵�����X���ȗ�����T�C�Y (0�Ȃ疳��)'),
        array('mobile.before_respointer', '�|�C���^�̉��R�O�̃��X����\�����邩'),
        array('mobile.anchor_link_page', '�A���J�[����y�[�W�P�ʂŕ\�����邩'),
        array('mobile.use_tsukin', '�O�������N�ɒʋ΃u���E�U(��)�𗘗p'),
        array('mobile.use_picto', '�摜�����N��pic.to(��)�𗘗p'),
        array('mobile.link_youtube', 'YouTube�̃����N���T���l�C���\��'),

        array('mobile.bbs_noname_name', '�f�t�H���g�̖���������\��'),
        array('mobile.date_zerosuppress', '���t��0���ȗ��\��'),
        array('mobile.clip_time_sec', '�����̕b���ȗ��\��'),
        array('mobile.clip_unique_id', '�d�����Ȃ�ID�͖����݂̂̏ȗ��\��'),
        array('mobile.underline_id', 'ID������&quot;O&quot;�ɉ���������'),
        array('mobile.strip_linebreaks', '�����̉��s�ƘA��������s������'),

        array('mobile.copy_divide_len', '�u�ʁv�̃R�s�[�p�e�L�X�g�{�b�N�X�𕪊����镶����'),
        array('mobile.link_wikipedia', '[[�P��]]��Wikipedia�ւ̃����N�ɂ���'),
        array('mobile.backlink_list', '�t�Q�ƃ��X�g�̕\��'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ iPhone - subject

$groupname = 'subject (iPhone)';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname);
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('iphone.subject.indicate-speed', '�����������C���W�P�[�^�[��\��'),
        array('iphone.subject.speed.width', '�C���W�P�[�^�[�̕� (pixels)'),
        array('iphone.subject.speed.0rpd', '�C���W�P�[�^�[�̐F (1���X/������)'),
        array('iphone.subject.speed.1rpd', '�C���W�P�[�^�[�̐F (1���X/���ȏ�)'),
        array('iphone.subject.speed.10rpd', '�C���W�P�[�^�[�̐F (10���X/���ȏ�)'),
        array('iphone.subject.speed.100rpd', '�C���W�P�[�^�[�̐F (100���X/���ȏ�)'),
        array('iphone.subject.speed.1000rpd', '�C���W�P�[�^�[�̐F (1000���X/���ȏ�)'),
        array('iphone.subject.speed.10000rpd', '�C���W�P�[�^�[�̐F (10000���X/���ȏ�)'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ iPhone - read
/*
$groupname = 'read (iPhone)';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname);
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}
*/
// }}}
// }}}

// PC�p�\��
if (!$_conf['ktai']) {
    echo <<<EOP
</div><!-- end of tab -->
</div><!-- end of child tabset "�g�ђ[���ݒ�" -->

<div class="tabbertab" title="�g���p�b�N�ݒ�">
<h3>�g���p�b�N�ݒ�</h3>
<div class="tabber">\n
EOP;
}

// {{{ �g���p�b�N�ݒ�
// {{{ expack - tGrep

$groupname = 'tGrep';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname);
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('expack.tgrep.quicksearch', '�ꔭ����'),
        array('expack.tgrep.recent_num', '�����������L�^���鐔�i�L�^���Ȃ�:0�j'),
        array('expack.tgrep.recent2_num', '�T�[�`�{�b�N�X�Ɍ����������L�^���鐔�ASafari��p�i�L�^���Ȃ�:0�j'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ expack - �X�}�[�g�|�b�v�A�b�v���j���[

$groupname = 'SPM';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname, 'expack.spm.enabled');
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('expack.spm.kokores', '�����Ƀ��X'),
        array('expack.spm.kokores_orig', '�����Ƀ��X�ŊJ���t�H�[���Ɍ����X�̓��e��\������'),
        array('expack.spm.ngaborn', '���ځ[�񃏁[�h�ENG���[�h�o�^'),
        array('expack.spm.ngaborn_confirm', '���ځ[�񃏁[�h�ENG���[�h�o�^���Ɋm�F����'),
        array('expack.spm.filter', '�t�B���^�����O'),
        array('expack.spm.filter_target', '�t�B���^�����O���ʂ��J���t���[���܂��̓E�C���h�E'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ expack - �A�N�e�B�u���i�[

$groupname = 'ActiveMona';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname, 'expack.am.enabled');
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    if (isset($_conf['expack.am.fontfamily.orig'])) {
        $_current_am_fontfamily = $_conf['expack.am.fontfamily'];
        $_conf['expack.am.fontfamily'] = $_conf['expack.am.fontfamily.orig'];
    }
    $conflist = array(
        array('expack.am.fontfamily', 'AA�p�̃t�H���g'),
        array('expack.am.fontsize', 'AA�p�̕����̑傫��'),
        array('expack.am.display', '�X�C�b�`��\������ʒu'),
        array('expack.am.autodetect', '�����Ŕ��肵�AAA�p�\��������iPC�j'),
        array('expack.am.autong_k', '�����Ŕ��肵�ANG���[�h�ɂ���BAAS ���L���Ȃ� AAS �̃����N���쐬�i�g�сj'),
        array('expack.am.lines_limit', '�������肷��s���̉���'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
    if (isset($_conf['expack.am.fontfamily.orig'])) {
        $_conf['expack.am.fontfamily'] = $_current_am_fontfamily;
    }
}

// }}}
// {{{ expack - ���͎x��

$groupname = '���͎x��';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname);
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        //array('expack.editor.constant', '��^�� (�g��, �g��Ȃ�)'),
        array('expack.editor.dpreview', '���A���^�C���E�v���r���['),
        array('expack.editor.dpreview_chkaa', '���A���^�C���E�v���r���[��AA�␳�p�̃`�F�b�N�{�b�N�X��\������'),
        array('expack.editor.check_message', '�{������łȂ����`�F�b�N'),
        array('expack.editor.check_sage', 'sage�`�F�b�N'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ expack - RSS���[�_

$groupname = 'RSS';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname, 'expack.rss.enabled');
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('expack.rss.check_interval', 'RSS���X�V���ꂽ���ǂ����m�F����Ԋu (���w��)'),
        array('expack.rss.target_frame', 'RSS�̊O�������N���J���t���[���܂��̓E�C���h�E'),
        array('expack.rss.desc_target_frame', '�T�v���J���t���[���܂��̓E�C���h�E'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ expack - ImageCache2

$groupname = 'ImageCache2';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname, 'expack.ic2.enabled');
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('expack.ic2.viewer_default_mode', '�摜�L���b�V���ꗗ�̃f�t�H���g�\�����[�h'),
        array('expack.ic2.through_ime', '�L���b�V���Ɏ��s�����Ƃ��̊m�F�p��ime�o�R�Ń\�[�X�ւ̃����N���쐬'),
        array('expack.ic2.fitimage', '�|�b�v�A�b�v�摜�̑傫�����E�C���h�E�̑傫���ɍ��킹��'),
        array('expack.ic2.pre_thumb_limit_k', '�g�тŃC�����C���E�T���l�C�����L���̂Ƃ��̕\�����鐧���� (0�Ŗ�����)'),
        array('expack.ic2.newres_ignore_limit', '�V�����X�̉摜�� pre_thumb_limit �𖳎����đS�ĕ\��'),
        array('expack.ic2.newres_ignore_limit_k', '�g�тŐV�����X�̉摜�� pre_thumb_limit_k �𖳎����đS�ĕ\��'),
        array('expack.ic2.thread_imagelink', '�X���\�����ɉ摜�L���b�V���ꗗ�ւ̃X���^�C���������N��\������'),
        array('expack.ic2.thread_imagecount', '�X���\�����ɃX���^�C�Ō����������̉摜����\������'),
        array('expack.ic2.fav_auto_rank', '���C�ɃX���ɓo�^����Ă���X���̉摜�Ɏ��������N��ݒ肷��'),
        array('expack.ic2.fav_auto_rank_setting', '���C�ɃX���̉摜�����������N�ݒ肷��ꍇ�̐ݒ�l(�J���}��؂�)[���C��0�̃����N�l,���C��1�̃����N�l, , ,]'),
        array('expack.ic2.fav_auto_rank_override', '���C�ɃX���̉摜�����������N�ݒ肷��ꍇ�ɁA�L���b�V���ς݉摜�Ɏ��������N���㏑�����邩'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ expack - AAS

$groupname = 'AAS';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname, 'expack.aas.enabled');
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('expack.aas.inline_enabled', '�g�тŎ��� AA ����ƘA�����A�C�����C���\������'),
        'PC�p',
        array('expack.aas.default.type', '�摜�`�� (PNG, JPEG, GIF)'),
        array('expack.aas.default.quality', 'JPEG�̕i�� (0-100)'),
        array('expack.aas.default.width', '�摜�̉��� (�s�N�Z��)'),
        array('expack.aas.default.height', '�摜�̍��� (�s�N�Z��)'),
        array('expack.aas.default.margin', '�摜�̃}�[�W�� (�s�N�Z��)'),
        array('expack.aas.default.fontsize', '�����T�C�Y (�|�C���g)'),
        array('expack.aas.default.overflow', '�������摜����͂ݏo��ꍇ�A���T�C�Y���Ĕ[�߂� (��\��, ���T�C�Y)'),
        array('expack.aas.default.bold', '�����ɂ���'),
        array('expack.aas.default.fgcolor', '�����F (6���܂���3����16�i��)'),
        array('expack.aas.default.bgcolor', '�w�i�F (6���܂���3����16�i��)'),
        '�g�їp',
        array('expack.aas.mobile.type', '�摜�`�� (PNG, JPEG, GIF)'),
        array('expack.aas.mobile.quality', 'JPEG�̕i�� (0-100)'),
        array('expack.aas.mobile.width', '�摜�̉��� (�s�N�Z��)'),
        array('expack.aas.mobile.height', '�摜�̍��� (�s�N�Z��)'),
        array('expack.aas.mobile.margin', '�摜�̃}�[�W�� (�s�N�Z��)'),
        array('expack.aas.mobile.fontsize', '�����T�C�Y (�|�C���g)'),
        array('expack.aas.mobile.overflow', '�������摜����͂ݏo��ꍇ�A���T�C�Y���Ĕ[�߂� (��\��, ���T�C�Y)'),
        array('expack.aas.mobile.bold', '�����ɂ���'),
        array('expack.aas.mobile.fgcolor', '�����F (6���܂���3����16�i��)'),
        array('expack.aas.mobile.bgcolor', '�w�i�F (6���܂���3����16�i��)'),
        '�C�����C���\��',
        array('expack.aas.inline.type', '�摜�`�� (PNG, JPEG, GIF)'),
        array('expack.aas.inline.quality', 'JPEG�̕i�� (0-100)'),
        array('expack.aas.inline.width', '�摜�̉��� (�s�N�Z��)'),
        array('expack.aas.inline.height', '�摜�̍��� (�s�N�Z��)'),
        array('expack.aas.inline.margin', '�}�[�W�� (�s�N�Z��)'),
        array('expack.aas.inline.fontsize', '�����T�C�Y (�|�C���g)'),
        array('expack.aas.inline.overflow', '�������摜����͂ݏo��ꍇ�A���T�C�Y���Ĕ[�߂� (��\��, ���T�C�Y)'),
        array('expack.aas.inline.bold', '�����ɂ���'),
        array('expack.aas.inline.fgcolor', '�����F (6���܂���3����16�i��)'),
        array('expack.aas.inline.bgcolor', '�w�i�F (6���܂���3����16�i��)'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// }}}

// PC�p�\��
if (!$_conf['ktai']) {
    echo <<<EOP
</div><!-- end of tab -->
</div><!-- end of child tabset "�g���p�b�N�ݒ�" -->
</div><!-- end of parent tabset -->\n
EOP;
// �g�їp�\��
} else {
    if (!empty($selected_group)) {
        $group_en = UrlSafeBase64::encode($selected_group);
        echo "<input type=\"hidden\" name=\"edit_conf_user_group_en\" value=\"{$group_en}\">";
        echo $htm['form_submit'];
    }
}

echo <<<EOP
{$_conf['detect_hint_input_ht']}{$_conf['k_input_ht']}
</form>\n
EOP;


// �g�тȂ�
if ($_conf['ktai']) {
    echo <<<EOP
<hr>
<form method="GET" action="{$_SERVER['SCRIPT_NAME']}">
<select name="edit_conf_user_group_en">
EOP;
    if ($_conf['iphone']) {
        echo '<optgroup label="rep2��{�ݒ�">';
    }
    foreach ($groups as $groupname) {
        if ($_conf['iphone']) {
            if ($groupname == 'tGrep') {
                echo '</optgroup><optgroup label="�g���p�b�N�ݒ�">';
            } elseif ($groupname == 'subject-i') {
                echo '</optgroup><optgroup label="iPhone�ݒ�">';
            }
        }
        $group_ht = htmlspecialchars($groupname, ENT_QUOTES);
        $group_en = UrlSafeBase64::encode($groupname);
        $selected = ($selected_group == $groupname) ? ' selected' : '';
        echo "<option value=\"{$group_en}\"{$selected}>{$group_ht}</option>";
    }
    if ($_conf['iphone']) {
        echo '</optgroup>';
    }
    echo <<<EOP
</select>
<input type="submit" value="�̐ݒ��ҏW">
{$_conf['detect_hint_input_ht']}{$_conf['k_input_ht']}
</form>
<hr>
<div class="center">
<a href="editpref.php{$_conf['k_at_q']}"{$_conf['k_accesskey_at']['up']}>{$_conf['k_accesskey_st']['up']}�ݒ�ҏW</a>
{$_conf['k_to_index_ht']}
</div>
EOP;
}

echo '</body></html>';

exit;

//=====================================================================
// �֐��i���̃t�@�C�����݂̗̂��p�j
//=====================================================================

// {{{ applyRules()

/**
 * ���[���ݒ�i$conf_user_rules�j�Ɋ�Â��āA�t�B���^�����i�f�t�H���g�Z�b�g�j���s��
 *
 * @return  void
 */
function applyRules()
{
    global $conf_user_rules, $conf_user_def;

    if (is_array($conf_user_rules)) {
        foreach ($conf_user_rules as $k => $v) {
            if (isset($_POST['conf_edit'][$k])) {
                $def = isset($conf_user_def[$k]) ? $conf_user_def[$k] : null;
                foreach ($v as $func) {
                    $_POST['conf_edit'][$k] = call_user_func($func, $_POST['conf_edit'][$k], $def);
                }
            }
        }
    }
}

// }}} 
// {{{ �t�B���^�֐�
// emptyToDef() �Ȃǂ̃t�B���^��EditConfFiter�N���X�Ȃǂɂ܂Ƃ߂�\��
// {{{ emptyToDef()

/**
 * empty�̎��́A�f�t�H���g�Z�b�g����
 *
 * @param   string  $val    ���͂��ꂽ�l
 * @param   mixed   $def    �f�t�H���g�̒l
 * @return  mixed
 */
function emptyToDef($val, $def)
{
    if (empty($val)) {
        $val = $def;
    }
    return $val;
}

// }}}
// {{{ notIntExceptMinusToDef()

/**
 * ���̐������ł��鎞�͐��̐������i0���܂ށj���A
 * �ł��Ȃ����́A�f�t�H���g�Z�b�g����
 *
 * @param   string  $str    ���͂��ꂽ�l
 * @param   int     $def    �f�t�H���g�̒l
 * @return  int
 */
function notIntExceptMinusToDef($val, $def)
{
    // �S�p�����p ����
    $val = mb_convert_kana($val, 'a');
    // �������ł���Ȃ�
    if (is_numeric($val)) {
        // ����������
        $val = intval($val);
        // ���̐��̓f�t�H���g��
        if ($val < 0) {
            $val = intval($def);
        }
    // �������ł��Ȃ����̂́A�f�t�H���g��
    } else {
        $val = intval($def);
    }
    return $val;
}

// }}}
// {{{ notFloatExceptMinusToDef()

/**
 * ���̎������ł��鎞�͐��̎������i0���܂ށj���A
 * �ł��Ȃ����́A�f�t�H���g�Z�b�g����
 *
 * @param   string  $str    ���͂��ꂽ�l
 * @param   float   $def    �f�t�H���g�̒l
 * @return  float
 */
function notFloatExceptMinusToDef($val, $def)
{
    // �S�p�����p ����
    $val = mb_convert_kana($val, 'a');
    // �������ł���Ȃ�
    if (is_numeric($val)) {
        // ����������
        $val = floatval($val);
        // ���̐��̓f�t�H���g��
        if ($val < 0.0) {
            $val = floatval($def);
        }
    // �������ł��Ȃ����̂́A�f�t�H���g��
    } else {
        $val = floatval($def);
    }
    return $val;
}

// }}}
// {{{ notSelToDef()

/**
 * �I�����ɂȂ��l�̓f�t�H���g�Z�b�g����
 */
function notSelToDef()
{
    global $conf_user_def, $conf_user_sel, $conf_user_rad;

    $conf_user_list = array_merge($conf_user_sel, $conf_user_rad);
    $names = array_keys($conf_user_list);

    if (is_array($names)) {
        foreach ($names as $n) {
            if (isset($_POST['conf_edit'][$n])) {
                if (!array_key_exists($_POST['conf_edit'][$n], $conf_user_list[$n])) {
                    $_POST['conf_edit'][$n] = $conf_user_def[$n];
                }
            }
        }
    }
    return true;
}

// }}}
// {{{ invalidUrlToDef()

/**
 * HTTP�܂���HTTPS��URL�łȂ��ꍇ�̓f�t�H���g�Z�b�g����
 *
 * @param   string  $str    ���͂��ꂽ�l
 * @param   string  $def    �f�t�H���g�̒l
 * @return  string
 */
function invalidUrlToDef($val, $def)
{
    $purl = @parse_url($val);
    if (is_array($purl) && array_key_exists('scheme', $purl) &&
        ($purl['scheme'] == 'http' || $purl['scheme'] == 'https'))
    {
        return $val;
    }
    return $def;
}

// }}}
// {{{ escapeHtmlExceptEntity()

/**
 * �����̃G���e�B�e�B�������ē��ꕶ����HTML�G���e�B�e�B������
 *
 * htmlspecialchars() �̑�l���� $double_encode �� PHP 5.2.3 �Œǉ����ꂽ
 *
 * @param   string  $str    ���͂��ꂽ�l
 * @param   string  $def    �f�t�H���g�̒l
 * @return  string
 */
function escapeHtmlExceptEntity($val, $def)
{
    return htmlspecialchars($val, ENT_QUOTES, 'Shift_JIS', false);
}

// }}}
// {{{ notHtmlColorToDef()

/**
 * ��̏ꍇ��HTML�̐F�Ƃ��Đ������Ȃ��ꍇ�́A�f�t�H���g�Z�b�g����
 * W3C�̎d�l�Œ�`����Ă��Ȃ����A�u���E�U�͔F�����閼�O�͋����Ȃ�
 * orange��CSS2.1�̐F�����ǁA��O�I�ɋ���
 *
 * @param   string  $str    ���͂��ꂽ�l
 * @param   string  $def    �f�t�H���g�̒l
 * @return  string
 */
function notHtmlColorToDef($val, $def)
{
    if (strlen($val) == 0) {
        return $def;
    }

    $val = strtolower($val);

    // �F����16�i��
    if (in_array($val, array('black',   // #000000
                             'silver',  // #c0c0c0
                             'gray',    // #808080
                             'white',   // #ffffff
                             'maroon',  // #800000
                             'red',     // #ff0000
                             'purple',  // #800080
                             'fuchsia', // #ff00ff
                             'green',   // #008000
                             'lime',    // #00ff00
                             'olive',   // #808000
                             'yellow',  // #ffff00
                             'navy',    // #000080
                             'blue',    // #0000ff
                             'teal',    // #008080
                             'aqua',    // #00ffff
                             'orange',  // #ffa500
                             )) ||
        preg_match('/^#[0-9a-f]{6}$/', $val))
    {
        return $val;
    }

    return $def;
}

// }}}
// {{{ notCssColorToDef()

/**
 * ��̏ꍇ��CSS�̐F�Ƃ��Đ������Ȃ��ꍇ�́A�f�t�H���g�Z�b�g����
 * W3C�̎d�l�Œ�`����Ă��Ȃ����A�u���E�U�͔F�����閼�O�͋����Ȃ�
 * transparent,inherit,none�͋���
 *
 * @param   string  $str    ���͂��ꂽ�l
 * @param   string  $def    �f�t�H���g�̒l
 * @return  string
 */
function notCssColorToDef($val, $def)
{
//	print_backtrace(debug_backtrace());
    if (strlen($val) == 0) {
        return $def;
    }

    $val = strtolower($val);

    // �F����16�i��
    if (in_array($val, array('black',   // #000000
                             'silver',  // #c0c0c0
                             'gray',    // #808080
                             'white',   // #ffffff
                             'maroon',  // #800000
                             'red',     // #ff0000
                             'purple',  // #800080
                             'fuchsia', // #ff00ff
                             'green',   // #008000
                             'lime',    // #00ff00
                             'olive',   // #808000
                             'yellow',  // #ffff00
                             'navy',    // #000080
                             'blue',    // #0000ff
                             'teal',    // #008080
                             'aqua',    // #00ffff
                             'orange',  // #ffa500
                             'transparent',
                             'inherit',
                             'none')) ||
        preg_match('/^#(?:[0-9a-f]{3}|[0-9a-f]{6})$/', $val))
    {
        return $val;
    }

    // rgb(d,d,d)
    if (preg_match('/rgb\\(
                    [ ]*(0|[1-9][0-9]*)[ ]*,
                    [ ]*(0|[1-9][0-9]*)[ ]*,
                    [ ]*(0|[1-9][0-9]*)[ ]*
                    \\)/x', $val, $m))
    {
        return sprintf('rgb(%d, %d, %d)',
                       min(255, (int)$m[1]),
                       min(255, (int)$m[2]),
                       min(255, (int)$m[3])
                       );
    }

    // rgba(%,%,%)
    if (preg_match('/rgb\\(
                    [ ]*(0|[1-9][0-9]*)%[ ]*,
                    [ ]*(0|[1-9][0-9]*)%[ ]*,
                    [ ]*(0|[1-9][0-9]*)%[ ]*
                    \\)/x', $val, $m))
    {
        return sprintf('rgb(%d%%, %d%%, %d%%)',
                       min(100, (int)$m[1]),
                       min(100, (int)$m[2]),
                       min(100, (int)$m[3])
                       );
    }

    // rgba(d,d,d,f)
    if (preg_match('/rgba\\(
                    [ ]*(0|[1-9][0-9]*)[ ]*,
                    [ ]*(0|[1-9][0-9]*)[ ]*,
                    [ ]*(0|[1-9][0-9]*)[ ]*,
                    [ ]*([01](?:\\.[0-9]+)?)[ ]*
                    \\)/x', $val, $m))
    {
        return sprintf('rgba(%d, %d, %d, %0.2f)',
                       min(255, (int)$m[1]),
                       min(255, (int)$m[2]),
                       min(255, (int)$m[3]),
                       min(1.0, (float)$m[4])
                       );
    }

    // rgba(%,%,%,f)
    if (preg_match('/rgba\\(
                    [ ]*(0|[1-9][0-9]*)%[ ]*,
                    [ ]*(0|[1-9][0-9]*)%[ ]*,
                    [ ]*(0|[1-9][0-9]*)%[ ]*,
                    [ ]*([01](?:\\.[0-9]+)?)[ ]*
                    \\)/x', $val, $m))
    {
        return sprintf('rgba(%d%%, %d%%, %d%%, %0.2f)',
                       min(100, (int)$m[1]),
                       min(100, (int)$m[2]),
                       min(100, (int)$m[3]),
                       min(1.0, (float)$m[4])
                       );
    }

    return $def;
}

// }}}
// {{{ notCssFontSizeToDef()

/**
 * CSS�̃t�H���g�̑傫���Ƃ��Đ������Ȃ��ꍇ�́A�f�t�H���g�Z�b�g����
 * media="screen" ��O��ɁAin,cm,mm,pt,pc���̐�ΓI�ȒP�ʂ̓T�|�[�g���Ȃ�
 *
 * @param   string  $str    ���͂��ꂽ�l
 * @param   string  $def    �f�t�H���g�̒l
 * @return  string
 */
function notCssFontSizeToDef($val, $def)
{
    if (strlen($val) == 0) {
        return $def;
    }

    $val = strtolower($val);

    // �L�[���[�h
    if (in_array($val, array('xx-large', 'x-large', 'large',
                             'larger', 'medium', 'smaller',
                             'small', 'x-small', 'xx-small')))
    {
        return $val;
    }

    // ����
    if (preg_match('/^[1-9][0-9]*(?:em|ex|px|%)$/', $val)) {
        return $val;
    }

    // ���� (�����_��3�ʂŎl�̌ܓ��A�]����0��؂�̂�)
    if (preg_match('/^((?:0|[1-9][0-9]*)\\.[0-9]+)(em|ex|px|%)$/', $val, $m)) {
        $val = rtrim(sprintf('%0.2f', (float)$m[1]), '.0');
        if ($val !== '0') {
            return $val . $m[2];
        }
    }

    return $def;
}

// }}}
// {{{ notCssSizeToDef()

/**
 * CSS�̑傫���Ƃ��Đ������Ȃ��ꍇ�́A�f�t�H���g�Z�b�g����
 * media="screen" ��O��ɁAin,cm,mm,pt,pc���̐�ΓI�ȒP�ʂ̓T�|�[�g���Ȃ�
 *
 * @param   string  $str    ���͂��ꂽ�l
 * @param   string  $def    �f�t�H���g�̒l
 * @param   boolean $allow_zero
 * @param   boolean $allow_negative
 * @return  string
 */
function notCssSizeToDef($val, $def, $allow_zero = true, $allow_negative = true)
{
    if (strlen($val) == 0) {
        return $def;
    }

    $val = strtolower($val);

    // 0
    if ($allow_zero && $val === '0') {
        return '0';
    }

    // ���� (0�͒P�ʂȂ���)
    if (preg_match('/^(-?(?:0|[1-9][0-9]*))(?:em|ex|px|%)$/', $val, $m)) {
        $i = (int)$m[1];
        if ($i > 0 || ($i < 0 && $allow_negative) || $allow_zero) {
            if ($i === 0) {
                return '0';
            } else {
                return $val;
            }
        }
    }

    // ���� (�����_��3�ʂŎl�̌ܓ��A�]����0��؂�̂�)
    if (preg_match('/^(-?(?:0|[1-9][0-9]*)\\.[0-9]+)(em|ex|px|%)$/', $val, $m)) {
        $f = (float)$m[1];
        if ($f > 0.0 || ($f < 0.0 && $allow_negative) || $allow_zero) {
            $val = rtrim(sprintf('%0.2f', $f), '.0');
            if ($val === '0') {
                if ($allow_zero) {
                    return '0';
                }
            } else {
                return $val . $m[2];
            }
        }
    }

    return $def;
}

// }}}
// {{{ notCssPositiveSizeToDef()

/**
 * CSS�̑傫���Ƃ��Đ������Ȃ��ꍇ���A���̒l�łȂ��Ƃ��́A�f�t�H���g�Z�b�g����
 *
 * @param   string  $str    ���͂��ꂽ�l
 * @param   string  $def    �f�t�H���g�̒l
 * @return  string
 */
function notCssPositiveSizeToDef($val, $def)
{
    return notCssSizeToDef($val, $def, false, false);
}

// }}}
// {{{ notCssSizeExceptMinusToDef()

/**
 * CSS�̑傫���Ƃ��Đ������Ȃ��ꍇ���A���̒l�̂Ƃ��́A�f�t�H���g�Z�b�g����
 *
 * @param   string  $str    ���͂��ꂽ�l
 * @param   string  $def    �f�t�H���g�̒l
 * @return  string
 */
function notCssSizeExceptMinusToDef($val, $def)
{
    return notCssSizeToDef($val, $def, true, false);
}

// }}}
// }}}
// {{{ �\���p�֐�
// {{{ getGroupShowFlags()

/**
 * �O���[�v�̕\�����[�h�𓾂�
 *
 * @param   stirng  $group_key  �O���[�v��
 * @param   string  $conf_key   �ݒ荀�ږ�
 * @return  int
 */
function getGroupShowFlags($group_key, $conf_key = null)
{
    global $_conf, $selected_group;

    $flags = P2_EDIT_CONF_USER_DEFAULT;

    if (empty($selected_group) || ($selected_group != 'all' && $selected_group != $group_key)) {
        $flags |= P2_EDIT_CONF_USER_HIDDEN;
        if ($_conf['ktai']) {
            $flags |= P2_EDIT_CONF_USER_SKIPPED;
        }
    }
    if (!empty($conf_key)) {
        if (empty($_conf[$conf_key])) {
            $flags |= P2_EDIT_CONF_USER_DISABLED;
        }
        if (preg_match('/^expack\\./', $conf_key)) {
            $flags |= P2_EDIT_CONF_FILE_ADMIN_EX;
        } else {
            $flags |= P2_EDIT_CONF_FILE_ADMIN;
        }
    }
    return $flags;
}

// }}}
// {{{ getGroupSepaHtml()

/**
 * �O���[�v�����p��HTML�𓾂�i�֐�����PC�A�g�їp�\����U�蕪���j
 *
 * @param   stirng  $title  �O���[�v��
 * @param   int     $flags  �\�����[�h
 * @return  string
 */
function getGroupSepaHtml($title, $flags)
{
    global $_conf;

    $admin_php = ($flags & P2_EDIT_CONF_FILE_ADMIN_EX) ? 'conf_admin_ex' : 'conf_admin';

    // PC�p
    if (!$_conf['ktai']) {
        $ht = <<<EOP
<div class="tabbertab" title="{$title}">
<h4>{$title}</h4>\n
EOP;
        if ($flags & P2_EDIT_CONF_USER_DISABLED) {
            $ht .= <<<EOP
<p><i>���݁A���̋@�\�͖����ɂȂ��Ă��܂��B<br>
�L���ɂ���ɂ� conf/{$admin_php}.inc.php �� {$title} �� on �ɂ��Ă��������B</i></p>\n
EOP;
        }
        $ht .= <<<EOP
<table class="edit_conf_user" cellspacing="0">
    <tr>
        <th>�ϐ���</th>
        <th>�l</th>
        <th>����</th>
    </tr>\n
EOP;
    // �g�їp
    } else {
        if ($flags & P2_EDIT_CONF_USER_HIDDEN) {
            $ht = '';
        } else {
            $ht = "<hr><h4>{$title}</h4>" . "\n";
            if ($flags & P2_EDIT_CONF_USER_DISABLED) {
            $ht .= <<<EOP
<p>���݁A���̋@�\�͖����ɂȂ��Ă��܂��B<br>
�L���ɂ���ɂ� conf/{$admin_php}.inc.php �� {$title} �� on �ɂ��Ă��������B</p>\n
EOP;
            }
        }
    }
    return $ht;
}

// }}}
// {{{ getConfBorderHtml()

/**
 * �O���[�v�I�[��HTML�𓾂�i�g�тł͋�j
 *
 * @param   string  $label  ���x��
 * @return  string
 */
function getConfBorderHtml($label)
{
    global $_conf;

    if ($_conf['ktai']) {
        $format = '<p>[%s]</p>';
    } else {
        $format = '<tr class="group"><td colspan="3" align="center">%s</td></tr>';
    }

    return sprintf($format, htmlspecialchars($label, ENT_QUOTES, 'Shift_JIS'));
}

// }}}
// {{{ getGroupEndHtml()

/**
 * �O���[�v�I�[��HTML�𓾂�i�g�тł͋�j
 *
 * @param   int     $flags  �\�����[�h
 * @return  string
 */
function getGroupEndHtml($flags)
{
    global $_conf;

    // PC�p
    if (!$_conf['ktai']) {
        $ht = '';
        if (!($flags & P2_EDIT_CONF_USER_HIDDEN)) {
            $ht .= <<<EOP
    <tr class="group">
        <td colspan="3" align="center">
            <input type="submit" name="submit_save" value="�ύX��ۑ�����">
            <input type="reset"  name="reset_change" value="�ύX��������" onclick="return window.confirm('�ύX���������Ă���낵���ł����H\\n�i�S�Ẵ^�u�̕ύX�����Z�b�g����܂��j');">
            <input type="submit" name="submit_default" value="�f�t�H���g�ɖ߂�" onclick="return window.confirm('���[�U�ݒ���f�t�H���g�ɖ߂��Ă���낵���ł����H\\n�i��蒼���͂ł��܂���j');">
        </td>
    </tr>\n
EOP;
        }
        $ht .= <<<EOP
</table>
</div><!-- end of tab -->\n
EOP;
    // �g�їp
    } else {
        $ht = '';
    }
    return $ht;
}

// }}}
// {{{ getEditConfHtml()

/**
 * �ҏW�t�H�[��input�pHTML�𓾂�i�֐�����PC�A�g�їp�\����U�蕪���j
 *
 * @param   stirng  $name   �ݒ荀�ږ�
 * @param   string  $description_ht HTML�`���̐���
 * @param   int     $flags  �\�����[�h
 * @return  string
 */
function getEditConfHtml($name, $description_ht, $flags)
{
    global $_conf, $conf_user_def, $conf_user_sel, $conf_user_rad;

    // �f�t�H���g�l�̋K�肪�Ȃ���΁A�󔒂�Ԃ�
    if (!isset($conf_user_def[$name])) {
        return '';
    }

    $name_view = htmlspecialchars($_conf[$name], ENT_QUOTES);

    // ����or��\���Ȃ�
    if ($flags & (P2_EDIT_CONF_USER_HIDDEN | P2_EDIT_CONF_USER_DISABLED)) {
        $form_ht = getEditConfHidHtml($name);
        // �g�тȂ炻�̂܂ܕԂ�
        if ($_conf['ktai']) {
            return $form_ht;
        }
        if ($name_view === '') {
            $form_ht .= '<i>(empty)</i>';
        } else {
            $form_ht .= $name_view;
        }
        if (is_string($conf_user_def[$name])) {
            $def_views[$name] = htmlspecialchars($conf_user_def[$name], ENT_QUOTES);
        } else {
            $def_views[$name] = strval($conf_user_def[$name]);
        }
    // select �I���`���Ȃ�
    } elseif (isset($conf_user_sel[$name])) {
        $form_ht = getEditConfSelHtml($name);
        $key = $conf_user_def[$name];
        $def_views[$name] = htmlspecialchars($conf_user_sel[$name][$key], ENT_QUOTES);
    // radio �I���`���Ȃ�
    } elseif (isset($conf_user_rad[$name])) {
        $form_ht = getEditConfRadHtml($name);
        $key = $conf_user_def[$name];
        $def_views[$name] = htmlspecialchars($conf_user_rad[$name][$key], ENT_QUOTES);
    // input ���͎��Ȃ�
    } else {
        if (!$_conf['ktai']) {
            $input_size_at = sprintf(' size="%d"', ($flags & P2_EDIT_CONF_USER_LONGTEXT) ? 40 : 20);
        } else {
            $input_size_at = '';
        }
        $input_type = ($flags & P2_EDIT_CONF_USER_PASSWORD) ? 'password' : 'text';
        $form_ht = <<<EOP
<input type="{$input_type}" name="conf_edit[{$name}]" value="{$name_view}"{$input_size_at}>
EOP;
        if (is_string($conf_user_def[$name])) {
            $def_views[$name] = htmlspecialchars($conf_user_def[$name], ENT_QUOTES);
        } else {
            $def_views[$name] = strval($conf_user_def[$name]);
        }
    }

    // iPhone�p
    if ($_conf['iphone']) {
        return "<fieldset><legend>{$name}</legend>{$description_ht}<br>{$form_ht}</fieldset>\n";

    // �g�їp
    } elseif ($_conf['ktai']) {
        return "[{$name}]<br>{$description_ht}<br>{$form_ht}<br><br>\n";

    // PC�p
    } else {
        return <<<EOP
    <tr title="�f�t�H���g�l: {$def_views[$name]}">
        <td>{$name}</td>
        <td>{$form_ht}</td>
        <td>{$description_ht}</td>
    </tr>\n
EOP;
    }
}

// }}}
// {{{ getEditConfHidHtml()

/**
 * �ҏW�t�H�[��hidden�pHTML�𓾂�
 *
 * @param   stirng  $name   �ݒ荀�ږ�
 * @return  string
 */
function getEditConfHidHtml($name)
{
    global $_conf, $conf_user_def;

    if (isset($_conf[$name]) && $_conf[$name] != $conf_user_def[$name]) {
        $value_ht = htmlspecialchars($_conf[$name], ENT_QUOTES);
    } else {
        $value_ht = htmlspecialchars($conf_user_def[$name], ENT_QUOTES);
    }

    $form_ht = "<input type=\"hidden\" name=\"conf_edit[{$name}]\" value=\"{$value_ht}\">";

    return $form_ht;
}

// }}}
// {{{ getEditConfSelHtml()

/**
 * �ҏW�t�H�[��select�pHTML�𓾂�
 *
 * @param   stirng  $name   �ݒ荀�ږ�
 * @return  string
 */
function getEditConfSelHtml($name)
{
    global $_conf, $conf_user_def, $conf_user_sel;

    $form_ht = "<select name=\"conf_edit[{$name}]\">\n";

    foreach ($conf_user_sel[$name] as $key => $value) {
        /*
        if ($value == "") {
            continue;
        }
        */
        $selected = "";
        if ($_conf[$name] == $key) {
            $selected = " selected";
        }
        $key_ht = htmlspecialchars($key, ENT_QUOTES);
        $value_ht = htmlspecialchars($value, ENT_QUOTES);
        $form_ht .= "\t<option value=\"{$key_ht}\"{$selected}>{$value_ht}</option>\n";
    } // foreach

    $form_ht .= "</select>\n";

    return $form_ht;
}

// }}}
// {{{ getEditConfRadHtml()

/**
 * �ҏW�t�H�[��radio�pHTML�𓾂�
 *
 * @param   stirng  $name   �ݒ荀�ږ�
 * @return  string
 */
function getEditConfRadHtml($name)
{
    global $_conf, $conf_user_def, $conf_user_rad;

    $form_ht = '';

    foreach ($conf_user_rad[$name] as $key => $value) {
        /*
        if ($value == "") {
            continue;
        }
        */
        $checked = "";
        if ($_conf[$name] == $key) {
            $checked = " checked";
        }
        $key_ht = htmlspecialchars($key, ENT_QUOTES);
        $value_ht = htmlspecialchars($value, ENT_QUOTES);
        if ($_conf['iphone']) {
            $form_ht .= "<input type=\"radio\" name=\"conf_edit[{$name}]\" value=\"{$key_ht}\"{$checked}><span onclick=\"if(!this.previousSibling.checked)this.previousSibling.checked=true;\">{$value_ht}</span>\n";
        } else {
            $form_ht .= "<label><input type=\"radio\" name=\"conf_edit[{$name}]\" value=\"{$key_ht}\"{$checked}>{$value_ht}</label>\n";
        }
    } // foreach

    return $form_ht;
}

// }}}
// {{{ printEditConfGroupHtml()

/**
 * �ҏW�t�H�[����\������
 *
 * @param   stirng  $groupname  �O���[�v��
 * @param   array   $conflist   �ݒ荀�ږ��Ɛ����̔z��
 * @param   int     $flags      �\�����[�h
 * @return  void
 */
function printEditConfGroupHtml($groupname, $conflist, $flags)
{
    echo getGroupSepaHtml($groupname, $flags);
    foreach ($conflist as $c) {
        if (!is_array($c)) {
            echo getConfBorderHtml($c);
        } elseif (isset($c[2]) && is_integer($c[2]) && $c[2] > 0) {
            echo getEditConfHtml($c[0], $c[1], $c[2] | $flags);
        } else {
            echo getEditConfHtml($c[0], $c[1], $flags);
        }
    }
    echo getGroupEndHtml($flags);
}

// }}}
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
