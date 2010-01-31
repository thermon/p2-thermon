<?php
/**
 * rep2expack - �������X�N���v�g
 * conf/conf.inc.php �� p2_init() ����ǂݍ��܂��B
 */

require_once 'Net/UserAgent/Mobile.php';

// {{{ ���[�U�[�ݒ� �Ǎ�

// ���[�U�[�ݒ�t�@�C��
$_conf['conf_user_file'] = $_conf['pref_dir'] . '/conf_user.srd.cgi';

// ���[�U�[�ݒ肪����Γǂݍ���
if (file_exists($_conf['conf_user_file'])) {
    if ($cont = file_get_contents($_conf['conf_user_file'])) {
        $conf_user = unserialize($cont);
    } else {
        $conf_user = null;
    }

    // ���炩�̗��R�Ń��[�U�[�ݒ�t�@�C�������Ă�����
    if (!is_array($conf_user)) {
        if (unlink($_conf['conf_user_file'])) {
            $_info_msg_ht .= '<p>���[�U�[�ݒ�t�@�C�������Ă����̂ō폜���܂����B</p>';
            $conf_user = array();
        } else {
            $dispname = '$_conf[\'pref_dir\']/' . basename($_conf['conf_user_file']);
            p2die("���Ă��郆�[�U�[�ݒ�t�@�C�� {$dispname} ���폜�ł��܂���ł����B");
        }
    }

    // ���[�U�[�ݒ�̃o�[�W�������`�F�b�N
    if (array_key_exists('.', $conf_user) &&
        preg_match('/^\\d{6}\\.\\d{4}$/', $conf_user['.']))
    {
        $config_version = $conf_user['.'];
    } else {
        $config_version = '000000.0000';
    }

    if ($config_version !== $_conf['p2expack']) {
        // �ݒ�̍X�V
        if ($migrators = p2_check_migration($config_version)) {
            $conf_user = p2_invoke_migrators($migrators, $conf_user);
        }

        // �f�t�H���g�ݒ��ǂݍ��݁A���[�U�[�ݒ�ƂƂ��Ƀ}�[�W
        include P2_CONF_DIR . '/conf_user_def.inc.php';
        $_conf = array_merge($_conf, $conf_user_def, $conf_user);
        $creae_config_cache = true;
    } else {
        // �L���b�V������Ă������[�U�[�ݒ���}�[�W
        $_conf = array_merge($_conf, $conf_user);
        $creae_config_cache = false;
    }
} else {
    // �f�t�H���g�ݒ��ǂݍ��݁A�}�[�W
    include P2_CONF_DIR . '/conf_user_def.inc.php';
    $_conf = array_merge($_conf, $conf_user_def);
    $creae_config_cache = true;
}

// �V�������[�U�[�ݒ���L���b�V��
if ($creae_config_cache) {
    $conf_user = array('.' => $_conf['p2expack']);
    foreach ($conf_user_def as $k => $v) {
        $conf_user[$k] = $_conf[$k];
    }
    $cont = serialize($conf_user);
    if (FileCtl::file_write_contents($_conf['conf_user_file'], $cont) === false) {
        $dispname = '$_conf[\'pref_dir\']/' . basename($_conf['conf_user_file']);
        p2die("���[�U�[�ݒ�t�@�C�� {$dispname} �ɏ������߂܂���ł����B");
    }
}

// }}}
// {{{ �z�X�g�`�F�b�N

if ($_conf['secure']['auth_host'] || $_conf['secure']['auth_bbq']) {
    if (($_conf['secure']['auth_host'] && HostCheck::getHostAuth() == false) ||
        ($_conf['secure']['auth_bbq'] && HostCheck::getHostBurned() == true)
    ) {
        HostCheck::forbidden();
    }
}

// }}}
// {{{ ���N�G�X�g�ϐ��̌��؂ƕ����R�[�h�ϊ�

/**
 * ���{�����͂���\���̂���t�H�[���ɂ͉B���v�f��
 * �G���R�[�f�B���O����p�̕�������d����ł���
 *
 * $_COOKIE �� $_REQUEST �Ɋ܂߂Ȃ�
 */
if (!empty($_GET) || !empty($_POST)) {
    $hint = null;

    // NULL�o�C�g�A�^�b�N�ƃX�N���v�g�C���W�F�N�V�����̌��؁A
    // �G���R�[�f�B���O����p������̎擾
    if (!empty($_POST)) {
        // �V�K���O�C���ƃ����o�[���O�C���̓����w��͂��肦�Ȃ��̂ŁA�G���[���o��
        if (isset($_POST['submit_new']) && isset($_POST['submit_member'])) {
            p2die('�����ȃ��N�G�X�g�ł��B');
        }

        array_walk_recursive($_POST, 'p2_scan_nullbyte');
        p2_scan_script_injection($_POST);
        if (array_key_exists('_hint', $_POST)) {
            $hint = $_POST['_hint'];
        }
    }
    if (!empty($_GET)) {
        array_walk_recursive($_GET, 'p2_scan_nullbyte');
        p2_scan_script_injection($_GET);
        if (array_key_exists('_hint', $_GET)) {
            $hint = $_GET['_hint'];
        }
    }

    // �G���R�[�f�B���O����
    if ($hint) {
        $request_encoding = mb_detect_encoding($hint, 'ASCII,UTF-8,SJIS-win');
        if ($request_encoding == 'ASCII') {
            p2die('�s���ȃG���R�[�f�B���O����q���g�ł��B');
        }
    } else {
        $request_encoding = 'ASCII,UTF-8,SJIS-win';
    }

    // UTF-8�Ȃ�Shift_JIS�ɕϊ�
    if ($request_encoding == 'UTF-8') {
        mb_convert_variables('SJIS-win', 'UTF-8', $_GET, $_POST);
    }

    // $_REQUEST ���č\��
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $_REQUEST = array_merge($_GET, $_POST);
    } else {
        $_REQUEST = $_GET;
    }
} else {
    $_REQUEST = array();
}

// }}}
// {{{ �[������

$_conf['ktai'] = false;
$_conf['iphone'] = false;
$_conf['input_type_search'] = false;

$_conf['accesskey'] = 'accesskey';
$_conf['accept_charset'] = 'Shift_JIS';
$_conf['extra_headers_ht'] = '';

$_conf['use_cookies'] = true;

$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
$mobile = Net_UserAgent_Mobile::singleton($userAgent);

// iPhone, iPod Touch or Android
if (UA::isIPhoneGroup($userAgent)) {
    $_conf['ktai'] = true;
    $_conf['iphone'] = true;
    $_conf['input_type_search'] = true;
    $_conf['accept_charset'] = 'UTF-8';

// PC��
} elseif ($mobile->isNonMobile()) {
    // Safari
    if (UA::isSafariGroup($userAgent)) {
        $_conf['input_type_search'] = true;
        $_conf['accept_charset'] = 'UTF-8';

    // Windows Mobile
    } elseif (P2Util::isClientOSWindowsCE()) {
        $_conf['ktai'] = true;

    // �g�уQ�[���@
    } elseif (UA::isNintendoDS($userAgent) || UA::isPSP($userAgent)) {
        $_conf['ktai'] = true;
    }

// �g��
} else {
    $_conf['ktai'] = true;

    // NTT docomo i���[�h
    if ($mobile->isDoCoMo()) {
        // i���[�h�u���E�U2.0����Cookie�ɑΉ����Ă���
        $_conf['use_cookies'] = UA::isIModeBrowser2();

    // au EZweb
    //} elseif ($mobile->isEZweb()) {
    //    $_conf['use_cookies'] = true;

    // SoftBank Mobile
    } elseif ($mobile->isSoftBank()) {
        // 3GC�^�[����nonumber�������T�|�[�g���Ȃ��̂�accesskey���g��
        if (!$mobile->isType3GC()) {
            $_conf['accesskey'] = 'DIRECTKEY';
            // 3GC�^�[����W�^�[���ȊO��Cookie���T�|�[�g���Ȃ�
            if (!$mobile->isTypeW()) {
                $_conf['use_cookies'] = false;
            }
        }

    // WILLCOM AIR-EDGE
    //} elseif ($mobile->isWillcom()) {
    //    $_conf['use_cookies'] = true;

    // ���̑�
    //} else {
    //    $_conf['use_cookies'] = true;
    }
}

// }}}
// {{{ �N�G���[�ɂ�鋭���r���[�w��

// b=pc �͂܂������N�悪���S�łȂ�?
// b=i ��CSS��WebKit�̓Ǝ��g��/��s�����v���p�e�B�𑽗p���Ă���

$_conf['b'] = $_conf['client_type'] = ($_conf['iphone'] ? 'i' : ($_conf['ktai'] ? 'k' : 'pc'));
$_conf['view_forced_by_query'] = false;
$_conf['k_at_a'] = '';
$_conf['k_at_q'] = '';
$_conf['k_input_ht'] = '';

if (isset($_REQUEST['b'])) {
    switch ($_REQUEST['b']) {

    // ����PC�r���[�w��
    case 'pc':
        if ($_conf['b'] != 'pc') {
            $_conf['b'] = 'pc';
            $_conf['ktai'] = false;
            $_conf['iphone'] = false;
        }
        break;

    // ����iPhone�r���[�w��
    case 'i':
        if ($_conf['b'] != 'i') {
            $_conf['b'] = 'i';
            $_conf['ktai'] = true;
            $_conf['iphone'] = true;
        }
        break;

    // �����g�уr���[�w��
    case 'k':
        if ($_conf['b'] != 'k') {
            $_conf['b'] = 'k';
            $_conf['ktai'] = true;
            $_conf['iphone'] = false;
        }
        break;

    } // endswitch

    // �����r���[�w�肳��Ă����Ȃ�
    if ($_conf['b'] != $_conf['client_type']) {
        $_conf['view_forced_by_query'] = true;
        $_conf['k_at_a'] = '&amp;b=' . $_conf['b'];
        $_conf['k_at_q'] = '?b=' . $_conf['b'];
        $_conf['k_input_ht'] = '<input type="hidden" name="b" value="' . $_conf['b'] . '">';
        //output_add_rewrite_var('b', $_conf['b']);
    }
}

// }}}
// {{{ ���[�U�[�ݒ�̒�������

$_conf['ext_win_target_at'] = ($_conf['ext_win_target']) ? " target=\"{$_conf['ext_win_target']}\"" : '';
$_conf['bbs_win_target_at'] = ($_conf['bbs_win_target']) ? " target=\"{$_conf['bbs_win_target']}\"" : '';

if ($_conf['get_new_res']) {
    if ($_conf['get_new_res'] == 'all') {
        $_conf['get_new_res_l'] = $_conf['get_new_res'];
    } else {
        $_conf['get_new_res_l'] = 'l' . $_conf['get_new_res'];
    }
} else {
    $_conf['get_new_res_l'] = 'l200';
}

if ($_conf['expack.user_agent']) {
    ini_set('user_agent', $_conf['expack.user_agent']);
}

// }}}
// {{{ �f�U�C���ݒ� �Ǎ�

$skin_name = $default_skin_name = 'conf_user_style';
$skin = P2_CONF_DIR . '/conf_user_style.inc.php';
if (!$_conf['ktai'] && $_conf['expack.skin.enabled']) {
    // �ۑ�����Ă���X�L����
    $saved_skin_name = null;
    if (file_exists($_conf['expack.skin.setting_path'])) {
        $saved_skin_name = rtrim(file_get_contents($_conf['expack.skin.setting_path']));
        if (!preg_match('/^[0-9A-Za-z_\\-]+$/', $saved_skin_name)) {
            $saved_skin_name = null;
        }
    } else {
        FileCtl::make_datafile($_conf['expack.skin.setting_path'], $_conf['expack.skin.setting_perm']);
    }

    // ���N�G�X�g�Ŏw�肳�ꂽ�X�L����
    $new_skin_name = null;
    if (array_key_exists('skin', $_REQUEST) && is_string($_REQUEST['skin'])) {
        $new_skin_name = $_REQUEST['skin'];
        if (!preg_match('/^[0-9A-Za-z_\\-]+$/', $new_skin_name)) {
            $new_skin_name = null;
        } elseif ($new_skin_name != $saved_skin_name) {
            FileCtl::file_write_contents($_conf['expack.skin.setting_path'], $new_skin_name);
        }
    }

    // ���N�G�X�g�Ŏw�肳�ꂽ�ꎞ�X�L����
    $tmp_skin_name = null;
    if (array_key_exists('tmp_skin', $_REQUEST) && is_string($_REQUEST['tmp_skin'])) {
        $tmp_skin_name = $_REQUEST['tmp_skin'];
        if (!preg_match('/^[0-9A-Za-z_\\-]+$/', $tmp_skin_name)) {
            $tmp_skin_name = null;
        }
    }

    // �X�L������
    foreach (array($tmp_skin_name, $new_skin_name, $saved_skin_name, $default_skin_name) as $skin_name) {
        if ($skin_name !== null) {
            if ($skin_name == $default_skin_name) {
                break;
            }
            $user_skin_path = P2_USER_SKIN_DIR . '/' . $skin_name . '.php';
            if (file_exists($user_skin_path)) {
                $skin = $user_skin_path;
                break;
            }
            $bundled_skin_path = P2_SKIN_DIR . '/' . $skin_name . '.php';
            if (file_exists($bundled_skin_path)) {
                $skin = $bundled_skin_path;
                break;
            }
        }
    }
}

if (!file_exists($skin)) {
    $skin_name = 'conf_user_style';
    $skin = P2_CONF_DIR . '/conf_user_style.inc.php';
}
$skin_en = rawurlencode($skin_name) . '&amp;_=' . P2_VERSION_ID;
if ($_conf['view_forced_by_query']) {
    $skin_en .= $_conf['k_at_a'];
}

// �f�t�H���g�ݒ��ǂݍ����
include P2_CONF_DIR . '/conf_user_style.inc.php';
// �X�L���ŏ㏑��
if ($skin != P2_CONF_DIR . '/conf_user_style.inc.php') {
    include $skin;
}

// }}}
// {{{ �f�U�C���ݒ�̒�������

$skin_uniq = P2_VERSION_ID;

foreach ($STYLE as $K => $V) {
    if (empty($V)) {
        $STYLE[$K] = '';
    } elseif (strpos($K, 'fontfamily') !== false) {
        $STYLE[$K] = p2_correct_css_fontfamily($V);
    } elseif (strpos($K, 'color') !== false) {
        $STYLE[$K] = p2_correct_css_color($V);
    } elseif (strpos($K, 'background') !== false) {
        $STYLE[$K] = "url('" . p2_escape_css_url($V) . "')";
    }
}

if (!$_conf['ktai']) {
    if (!function_exists('p2_fontconfig_apply_custom')) {
        include P2_LIB_DIR . '/fontconfig.inc.php';
    }

    if ($_conf['expack.am.enabled']) {
        $_conf['expack.am.fontfamily'] = p2_correct_css_fontfamily($_conf['expack.am.fontfamily']);
        if ($STYLE['fontfamily']) {
            $_conf['expack.am.fontfamily'] .= '","' . $STYLE['fontfamily'];
        }
    }

    p2_fontconfig_apply_custom();
}

// }}}
// {{{ �g�сEiPhone�p�ϐ�

// iPhone�pHTML�w�b�_�v�f
if ($_conf['client_type'] == 'i') {
    switch ($_conf['b']) {

    // ����PC�r���[��
    case 'pc':
        $_conf['extra_headers_ht'] .= <<<EOS
<meta name="format-detection" content="telephone=no">
<link rel="apple-touch-icon" type="image/png" href="img/touch-icon/p2-serif.png">
<style type="text/css">body { -webkit-text-size-adjust: none; }</style>
EOS;
        break;

    // �����g�уr���[��
    case 'k':
        $_conf['extra_headers_ht'] .= <<<EOS
<meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=yes">
<meta name="format-detection" content="telephone=no">
<link rel="apple-touch-icon" type="image/png" href="img/touch-icon/p2-serif.png">
<style type="text/css">
body { word-break: normal; word-break: break-all; -webkit-text-size-adjust: none; }
* { font-family: sans-serif; font-size: medium; line-height: 150%; }
h1 { font-size: xx-large; }
h2 { font-size: x-large; }
h3 { font-size: large; }
</style>
EOS;
        break;

    // ����iPhone�r���[
    case 'i':
    default:
        $_conf['extra_headers_ht'] .= <<<EOS
<meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=yes">
<meta name="format-detection" content="telephone=no">
<link rel="apple-touch-icon" type="image/png" href="img/touch-icon/p2-serif.png">
<link rel="stylesheet" type="text/css" media="screen" href="css/iphone.css?{$_conf['p2_version_id']}">
<script type="text/javascript" src="js/iphone.js?{$_conf['p2_version_id']}"></script>
EOS;

    } // endswitch

// ����iPhone�r���[��
} elseif ($_conf['iphone']) {
    $_conf['extra_headers_ht'] .= <<<EOS
<link rel="stylesheet" type="text/css" media="screen" href="css/iphone.css?{$_conf['p2_version_id']}">
<script type="text/javascript" src="js/iphone.js?{$_conf['p2_version_id']}"></script>
EOS;
}

// iPhone�p�X�L��
if ($_conf['iphone'] && isset($_conf['expack.iphone.skin'])) {
    if (strpos($_conf['expack.iphone.skin'], DIRECTORY_SEPARATOR) === false) {
        $iskin = 'skin/iphone/' . $iskin . '.css';
        if (file_exists($iskin)) {
            $iskin_mtime = filemtime($iskin);
            $_conf['extra_headers_ht'] .= <<<EOS
<link rel="stylesheet" type="text/css" media="screen" href="{$iskin}?{$iskin_mtime}">
EOS;
        }
    }
}

// �g�їp�u�g�b�v�ɖ߂�v�����N��accesskey
if ($_conf['ktai']) {
    // iPhone
    if ($_conf['iphone']) {
        $_conf['k_accesskey_at'] = array_fill(0, 10, '');
        $_conf['k_accesskey_at']['*'] = '';
        $_conf['k_accesskey_at']['#'] = '';
        foreach ($_conf['k_accesskey'] as $name => $key) {
            $_conf['k_accesskey_at'][$name] = '';
        }

        $_conf['k_accesskey_st'] = $_conf['k_accesskey_at'];

        $_conf['k_to_index_ht'] = <<<EOP
<a href="index.php{$_conf['k_at_q']}" class="button">TOP</a>
EOP;

    // ���̑�
    } else {
        // SoftBank Mobile
        if ($_conf['accesskey'] == 'DIRECTKEY') {
            $_conf['k_accesskey_at'] = array(
                '0' => ' directkey="0" nonumber',
                '1' => ' directkey="1" nonumber',
                '2' => ' directkey="2" nonumber',
                '3' => ' directkey="3" nonumber',
                '4' => ' directkey="4" nonumber',
                '5' => ' directkey="5" nonumber',
                '6' => ' directkey="6" nonumber',
                '7' => ' directkey="7" nonumber',
                '8' => ' directkey="8" nonumber',
                '9' => ' directkey="9" nonumber',
                '*' => ' directkey="*" nonumber',
                '#' => ' directkey="#" nonumber',
            );

        // ���̑�
        } else {
            $_conf['k_accesskey_at'] = array(
                '0' => ' accesskey="0"',
                '1' => ' accesskey="1"',
                '2' => ' accesskey="2"',
                '3' => ' accesskey="3"',
                '4' => ' accesskey="4"',
                '5' => ' accesskey="5"',
                '6' => ' accesskey="6"',
                '7' => ' accesskey="7"',
                '8' => ' accesskey="8"',
                '9' => ' accesskey="9"',
                '*' => ' accesskey="*"',
                '#' => ' accesskey="#"',
            );
        }

        switch ($_conf['mobile.display_accesskey']) {
        case 2:
            if (!function_exists('p2_get_emoji')) {
                include P2_LIB_DIR . '/emoji.inc.php';
            }
            $emoji = p2_get_emoji($mobile);
            //$emoji = p2_get_emoji(Net_UserAgent_Mobile::factory('KDDI-SA31 UP.Browser/6.2.0.7.3.129 (GUI) MMP/2.0'));
            $_conf['k_accesskey_st'] = array(
                '0' => $emoji[0],
                '1' => $emoji[1],
                '2' => $emoji[2],
                '3' => $emoji[3],
                '4' => $emoji[4],
                '5' => $emoji[5],
                '6' => $emoji[6],
                '7' => $emoji[7],
                '8' => $emoji[8],
                '9' => $emoji[9],
                '*' => $emoji['*'],
                '#' => $emoji['#'],
            );
            break;
        case 0:
            $_conf['k_accesskey_st'] = array_fill(0, 10, '');
            $_conf['k_accesskey_st']['*'] = '';
            $_conf['k_accesskey_st']['#'] = '';
            break;
        case 1:
        default:
            $_conf['k_accesskey_st'] = array(
                0 => '0.', 1 => '1.', 2 => '2.', 3 => '3.', 4 => '4.',
                5 => '5.', 6 => '6.', 7 => '7.', 8 => '8.', 9 => '9.',
                '*' => '*.', '#' => '#.'
            );
        }

        foreach ($_conf['k_accesskey'] as $name => $key) {
            $_conf['k_accesskey_at'][$name] = $_conf['k_accesskey_at'][$key];
            $_conf['k_accesskey_st'][$name] = $_conf['k_accesskey_st'][$key];
        }

        $_conf['k_to_index_ht'] = <<<EOP
<a href="index.php{$_conf['k_at_q']}"{$_conf['k_accesskey_at'][0]}>{$_conf['k_accesskey_st'][0]}TOP</a>
EOP;
    }
}

// }}}
// {{{ �g�їp�J���[�����O�̒�������

$_conf['k_colors'] = '';

if ($_conf['ktai']) {
    // ��{�F
    if (!$_conf['iphone']) {
        if ($_conf['mobile.background_color']) {
            $_conf['k_colors'] .= ' bgcolor="' . htmlspecialchars($_conf['mobile.background_color']) . '"';
        }
        if ($_conf['mobile.text_color']) {
            $_conf['k_colors'] .= ' text="' . htmlspecialchars($_conf['mobile.text_color']) . '"';
        }
        if ($_conf['mobile.link_color']) {
            $_conf['k_colors'] .= ' link="' . htmlspecialchars($_conf['mobile.link_color']) . '"';
        }
        if ($_conf['mobile.vlink_color']) {
            $_conf['k_colors'] .= ' vlink="' . htmlspecialchars($_conf['mobile.vlink_color']) . '"';
        }
    }

    // �����F
    if ($_conf['mobile.newthre_color']) {
        $STYLE['mobile_subject_newthre_color'] = htmlspecialchars($_conf['mobile.newthre_color']);
    }
    if ($_conf['mobile.newres_color']) {
        $STYLE['mobile_read_newres_color']    = htmlspecialchars($_conf['mobile.newres_color']);
        $STYLE['mobile_subject_newres_color'] = htmlspecialchars($_conf['mobile.newres_color']);
    }
    if ($_conf['mobile.ttitle_color']) {
        $STYLE['mobile_read_ttitle_color'] = htmlspecialchars($_conf['mobile.ttitle_color']);
    }
    if ($_conf['mobile.ngword_color']) {
        $STYLE['mobile_read_ngword_color'] = htmlspecialchars($_conf['mobile.ngword_color']);
    }
    if ($_conf['mobile.onthefly_color']) {
        $STYLE['mobile_read_onthefly_color'] = htmlspecialchars($_conf['mobile.onthefly_color']);
    }

    // �}�[�J�[
    if ($_conf['mobile.match_color']) {
        if ($_conf['iphone']) {
            $_conf['extra_headers_ht'] .= sprintf('<style type="text/css">b.filtering, span.matched { color: %s; }</style>',
                                                  htmlspecialchars($_conf['mobile.match_color']));
            $_conf['k_filter_marker'] = '<span class="matched">\\1</span>';
        } else {
            $_conf['k_filter_marker'] = '<font color="' . htmlspecialchars($_conf['mobile.match_color']) . '">\\1</font>';
        }
    } else {
        $_conf['k_filter_marker'] = false;
    }
}

// }}}
// {{{ �Z�b�V����

// ���O�́A�Z�b�V�����N�b�L�[��j������Ƃ��̂��߂ɁA�Z�b�V�������p�̗L���Ɋւ�炸�ݒ肷��
session_name('PS');

// {{{ �Z�b�V�����f�[�^�ۑ��f�B���N�g�����`�F�b�N

if ($_conf['session_save'] == 'p2' and session_module_name() == 'files') {
    if (!is_dir($_conf['session_dir'])) {
        FileCtl::mkdir_r($_conf['session_dir']);
    } elseif (!is_writable($_conf['session_dir'])) {
        p2die("�Z�b�V�����f�[�^�ۑ��f�B���N�g�� ({$_conf['session_dir']}) �ɏ������݌���������܂���B");
    }

    session_save_path($_conf['session_dir']);
}

// }}}

$_p2session = new Session(null, null, $_conf['use_cookies']);

// }}}
// {{{ ���C�ɃZ�b�g

// �����̂��C�ɃZ�b�g���g���Ƃ�
if ($_conf['expack.misc.multi_favs']) {
    // �؂�ւ��\���p�ɑS�Ă̂��C�ɃX���E���C�ɔ�ǂݍ���ł���
    FavSetManager::loadAllFavSet();
    // ���C�ɃZ�b�g��؂�ւ���
    FavSetManager::switchFavSet();
} else {
    $_conf['m_favlist_set'] = '';
    $_conf['m_favlist_set_at_a'] = '';
    $_conf['m_favlist_set_input_ht'] = '';
    $_conf['m_favita_set'] = '';
    $_conf['m_favita_set_at_a'] = '';
    $_conf['m_favita_set_input_ht'] = '';
    $_conf['m_rss_set'] = '';
    $_conf['m_rss_set_at_a'] = '';
    $_conf['m_rss_set_input_ht'] = '';
}

// }}}
// {{{ misc.

// DOCTYPE HTML �錾
$_conf['doctype'] = '';
$ie_strict = false;
if (!$_conf['ktai'] || $_conf['client_type'] != 'k') {
    if ($ie_strict || strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') === false) {
        $_conf['doctype'] = <<<EODOC
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">\n
EODOC;
    } else {
        $_conf['doctype'] = <<<EODOC
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">\n
EODOC;
    }
}

// XHTML�w�b�_�v�f
if (defined('P2_OUTPUT_XHTML')) {
    $_conf['extra_headers_xht'] = preg_replace('/<((?:link|meta) .+?)>/', '<\\1 />', $_conf['extra_headers_ht']);
}

// ���O�C���N���X�̃C���X�^���X�����i���O�C�����[�U���w�肳��Ă��Ȃ���΁A���̎��_�Ń��O�C���t�H�[���\���Ɂj
$_login = new Login();

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
