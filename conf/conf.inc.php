<?php
/**
 * rep2 - ��{�ݒ�t�@�C��
 * ���̃t�@�C���́A���ɗ��R�̖�������ύX���Ȃ�����
 */

// �o�[�W�������
$_conf = array(
    'p2version' => '1.7.29+1.8.x',  // rep2�̃o�[�W����
    'p2expack'  => '090912.1710',   // �g���p�b�N�̃o�[�W����
    'p2name'    => 'expack',        // rep2�̖��O
);

$_conf['p2ua'] = "{$_conf['p2name']}/{$_conf['p2version']}+{$_conf['p2expack']}";

define('P2_VERSION_ID', sprintf('%u', crc32($_conf['p2ua'])));

// {{{ �O���[�o���ϐ���������

$_info_msg_ht = ''; // ���[�U�ʒm�p ��񃁃b�Z�[�WHTML

$MYSTYLE    = array();
$STYLE      = array();
$debug      = false;
$skin       = null;
$skin_en    = null;
$skin_name  = null;
$skin_uniq  = null;
$_login     = null;
$_p2session = null;

$conf_user_def   = array();
$conf_user_rules = array();
$conf_user_rad   = array();
$conf_user_sel   = array();

// }}}

// ��{�ݒ菈�������s
p2configure();

// �N���[���A�b�v
if (basename($_SERVER['SCRIPT_NAME']) != 'edit_conf_user.php') {
    unset($conf_user_def, $conf_user_rules, $conf_user_rad, $conf_user_sel);
}

// E_NOTICE ����шÖق̔z�񏉊�������
$_conf['filtering'] = false;
$hd = array('word' => null);
$htm = array();
$word = null;

// {{{ p2configure()

/**
 * �ꎞ�ϐ��ŃO���[�o���ϐ����������Ȃ��悤�ɐݒ菈�����֐���
 */
function p2configure()
{
    global $MYSTYLE, $STYLE, $debug;
    global $skin, $skin_en, $skin_name, $skin_uniq;
    global $_conf, $_info_msg_ht, $_login, $_p2session;
    global $conf_user_def, $conf_user_rules, $conf_user_rad, $conf_user_sel;

// �G���[�o�͐ݒ�
if (defined('E_DEPRECATED')) {
    error_reporting(E_ALL & ~(E_NOTICE | E_STRICT | E_DEPRECATED));
} else {
    error_reporting(E_ALL & ~(E_NOTICE | E_STRICT));
}

// {{{ ��{�ϐ�

$_conf['p2web_url']             = 'http://akid.s17.xrea.com/';
$_conf['p2ime_url']             = 'http://akid.s17.xrea.com/p2ime.php';
$_conf['favrank_url']           = 'http://akid.s17.xrea.com/favrank/favrank.php';
$_conf['expack.web_url']        = 'http://page2.xrea.jp/expack/';
$_conf['expack.download_url']   = 'http://page2.xrea.jp/expack/index.php/download';
$_conf['expack.history_url']    = 'http://page2.xrea.jp/expack/index.php/history';
$_conf['expack.tgrep_url']      = 'http://page2.xrea.jp/tgrep/search';
$_conf['expack.ime_url']        = 'http://page2.xrea.jp/r.p';
$_conf['menu_php']              = 'menu.php';
$_conf['subject_php']           = 'subject.php';
$_conf['read_php']              = 'read.php';
$_conf['read_new_php']          = 'read_new.php';
$_conf['read_new_k_php']        = 'read_new_k.php';

// }}}
// {{{ ���ݒ�

// �f�o�b�O
//$debug = !empty($_GET['debug']);

putenv('LC_CTYPE=C');

// �^�C���]�[�����Z�b�g
date_default_timezone_set('Asia/Tokyo');

// �X�N���v�g���s�������� (�b)
if (!defined('P2_CLI_RUN')) {
    set_time_limit(60); // (60) 
}

// �����t���b�V�����I�t�ɂ���
ob_implicit_flush(0);

// �N���C�A���g����ڑ���؂��Ă������𑱍s����
// ignore_user_abort(1);

// file($filename, FILE_IGNORE_NEW_LINES) �� CR/LF/CR+LF �̂�������s���Ƃ��Ĉ���
ini_set('auto_detect_line_endings', 1);

// session.trans_sid�L���� �� output_add_rewrite_var(), http_build_query() ���Ő����E�ύX�����
// URL��GET�p�����[�^��؂蕶��(��)��"&amp;"�ɂ���B�i�f�t�H���g��"&"�j
ini_set('arg_separator.output', '&amp;');

// ���N�G�X�gID��ݒ� (�R�X�g���傫�����Ɏg���Ă��Ȃ��̂Ŕp�~)
//define('P2_REQUEST_ID', substr($_SERVER['REQUEST_METHOD'], 0, 1) . md5(serialize($_REQUEST)));

// �Z�b�V����ID�� �t���@�\���L���ƂȂ����ꍇ�ɁA�Z�b�V����ID���܂߂邽�߂ɏ����� ������HTML�^�O��img=src��ǉ�
$temp_rewriter_tags = explode(',', ini_get('url_rewriter.tags'));
if (is_array($temp_rewriter_tags)) {
    if (!array_search('img=src', $temp_rewriter_tags)) {
        $temp_rewriter_tags[] = 'img=src';
        ini_set('url_rewriter.tags', implode(',', $temp_rewriter_tags));
    }
}

// Windows �Ȃ�
if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
    // Windows
    defined('PATH_SEPARATOR') or define('PATH_SEPARATOR', ';');
    defined('DIRECTORY_SEPARATOR') or define('DIRECTORY_SEPARATOR', '\\');
    define('P2_OS_WINDOWS', 1);
} else {
    defined('PATH_SEPARATOR') or define('PATH_SEPARATOR', ':');
    defined('DIRECTORY_SEPARATOR') or define('DIRECTORY_SEPARATOR', '/');
    define('P2_OS_WINDOWS', 0);
}

// mbstring.script_encoding = SJIS-win ���� "\0", "\x00" �ȍ~���J�b�g�����̂�
define('P2_NULLBYTE', chr(0));

// }}}
// {{{ P2Util::header_content_type() ��s�v�ɂ��邨�܂��Ȃ�

ini_set('default_mimetype', 'text/html');
ini_set('default_charset', 'Shift_JIS');

// }}}
// {{{ ���C�u�����ނ̃p�X�ݒ�

define('P2_CONF_DIR', dirname(__FILE__)); // __DIR__ @php-5.3

define('P2_BASE_DIR', dirname(P2_CONF_DIR));

// ��{�I�ȋ@�\��񋟂��邷�郉�C�u����
define('P2_LIB_DIR', P2_BASE_DIR . DIRECTORY_SEPARATOR . 'lib');

// ���܂��I�ȋ@�\��񋟂��邷�郉�C�u����
define('P2EX_LIB_DIR', P2_BASE_DIR . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'expack');

// �X�^�C���V�[�g
define('P2_STYLE_DIR', P2_BASE_DIR . DIRECTORY_SEPARATOR . 'style');

// �X�L��
define('P2_SKIN_DIR', P2_BASE_DIR . DIRECTORY_SEPARATOR . 'skin');

// PEAR�C���X�g�[���f�B���N�g���A�����p�X�ɒǉ������
define('P2_PEAR_DIR', P2_BASE_DIR . DIRECTORY_SEPARATOR . 'includes');

// PEAR���n�b�N�����t�@�C���p�f�B���N�g���A�ʏ��PEAR���D��I�Ɍ����p�X�ɒǉ������
// Cache/Container/db.php(PEAR::Cache)��MySQL���肾�����̂ŁA�ėp�I�ɂ������̂�u���Ă���
// include_path��ǉ�����̂̓p�t�H�[�}���X�ɉe�����y�ڂ����߁A�{���ɕK�v�ȏꍇ�̂ݒ�`
if (defined('P2_USE_PEAR_HACK')) {
    define('P2_PEAR_HACK_DIR', P2_BASE_DIR . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'pear_hack');
}

// �R�}���h���C���c�[��
define('P2_CLI_DIR', P2_BASE_DIR . DIRECTORY_SEPARATOR . 'cli');

// �����p�X���Z�b�g
$include_path = '';
if (defined('P2_PEAR_HACK_DIR')) {
    $include_path .= P2_PEAR_HACK_DIR . PATH_SEPARATOR;
}
if (is_dir(P2_PEAR_DIR)) {
    $include_path .= P2_PEAR_DIR . PATH_SEPARATOR;
} else {
    $paths = array();
    foreach (explode(PATH_SEPARATOR, get_include_path()) as $dir) {
        if (is_dir($dir)) {
            $dir = realpath($dir);
            if ($dir != P2_BASE_DIR) {
                $paths[] = $dir;
            }
        }
    }
    if (count($paths)) {
        $include_path .= implode(PATH_SEPARATOR, array_unique($paths)) . PATH_SEPARATOR;
    }
}
$include_path .= P2_BASE_DIR; // fallback
set_include_path($include_path);

$P2_CONF_DIR_S = P2_CONF_DIR . DIRECTORY_SEPARATOR;
$P2_LIB_DIR_S = P2_LIB_DIR . DIRECTORY_SEPARATOR;

// }}}
// {{{ ���`�F�b�N�ƃf�o�b�O

// ���[�e�B���e�B��ǂݍ���
require_once $P2_LIB_DIR_S . 'DataPhp.php';
require_once $P2_LIB_DIR_S . 'FileCtl.php';
require_once $P2_LIB_DIR_S . 'P2Util.php';
require_once $P2_LIB_DIR_S . 'p2util.inc.php';
require_once $P2_LIB_DIR_S . 'wiki/p2utilwiki.class.php';

// ��������m�F (�v���𖞂����Ă���Ȃ�R�����g�A�E�g��)
// p2checkenv(__LINE__);

if ($debug) {
    if (!class_exists('Benchmark_Profiler', false)) {
        require 'Benchmark/Profiler.php';
    }
    $profiler = new Benchmark_Profiler(true);
    // print_memory_usage();
    register_shutdown_function('print_memory_usage');
}

// }}}
// {{{ �����R�[�h�̎w��

//mb_detect_order("CP932,CP51932,ASCII");
mb_internal_encoding('CP932');
mb_http_output('pass');
mb_substitute_character(63); // �����R�[�h�ϊ��Ɏ��s���������� "?" �ɂȂ�
//mb_substitute_character(0x3013); // ��
//ob_start('mb_output_handler');

if (function_exists('mb_ereg_replace')) {
    define('P2_MBREGEX_AVAILABLE', 1);
    mb_regex_encoding('CP932');
} else {
    define('P2_MBREGEX_AVAILABLE', 0);
}

// }}}
// {{{ �Ǘ��җp�ݒ�etc.

// �Ǘ��җp�ݒ��ǂݍ���
require_once $P2_CONF_DIR_S . 'conf_admin.inc.php';

// �f�B���N�g���̐�΃p�X��
$_conf['data_dir'] = p2_realpath($_conf['data_dir']);
$_conf['dat_dir']  = p2_realpath($_conf['dat_dir']);
$_conf['idx_dir']  = p2_realpath($_conf['idx_dir']);
$_conf['pref_dir'] = p2_realpath($_conf['pref_dir']);

// �Ǘ��p�ۑ��f�B���N�g��
$_conf['admin_dir'] = $_conf['data_dir'] . DIRECTORY_SEPARATOR . 'admin';

// cache �ۑ��f�B���N�g��
// 2005/06/29 $_conf['pref_dir'] . '/p2_cache' ���ύX
$_conf['cache_dir'] = $_conf['data_dir'] . DIRECTORY_SEPARATOR . 'cache';

// Cookie �ۑ��f�B���N�g��
// 2008/09/09 $_conf['pref_dir'] . '/p2_cookie' ���ύX
$_conf['cookie_dir'] = $_conf['data_dir'] . DIRECTORY_SEPARATOR . 'cookie';

// �R���p�C�����ꂽ�e���v���[�g�̕ۑ��f�B���N�g��
$_conf['compile_dir'] = $_conf['data_dir'] . DIRECTORY_SEPARATOR . 'compile';

// �Z�b�V�����f�[�^�ۑ��f�B���N�g��
$_conf['session_dir'] = $_conf['data_dir'] . DIRECTORY_SEPARATOR . 'session';

// �e���|�����f�B���N�g��
$_conf['tmp_dir'] = $_conf['data_dir'] . DIRECTORY_SEPARATOR . 'tmp';

// �o�[�W����ID���d���p����q�A�h�L�������g���ɖ��ߍ��ނ��߂̕ϐ�
$_conf['p2_version_id'] = P2_VERSION_ID;

// �����R�[�h��������p�̃q���g������
$_conf['detect_hint'] = '����';
$_conf['detect_hint_input_ht'] = '<input type="hidden" name="_hint" value="����">';
$_conf['detect_hint_input_xht'] = '<input type="hidden" name="_hint" value="����" />';
//$_conf['detect_hint_utf8'] = mb_convert_encoding('����', 'UTF-8', 'CP932');
$_conf['detect_hint_q'] = '_hint=%81%9D%81%9E'; // rawurlencode($_conf['detect_hint'])
$_conf['detect_hint_q_utf8'] = '_hint=%E2%97%8E%E2%97%87'; // rawurlencode($_conf['detect_hint_utf8'])

// }}}
// {{{ �ϐ��ݒ�

$pref_dir_s = $_conf['pref_dir'] . DIRECTORY_SEPARATOR;

$_conf['favita_brd']        = $pref_dir_s . 'p2_favita.brd';        // ���C�ɔ� (brd)
$_conf['favlist_idx']       = $pref_dir_s . 'p2_favlist.idx';       // ���C�ɃX�� (idx)
$_conf['recent_idx']        = $pref_dir_s . 'p2_recent.idx';        // �ŋߓǂ񂾃X�� (idx)
$_conf['palace_idx']        = $pref_dir_s . 'p2_palace.idx';        // �X���̓a�� (idx)
$_conf['res_hist_idx']      = $pref_dir_s . 'p2_res_hist.idx';      // �������݃��O (idx)
$_conf['res_hist_dat']      = $pref_dir_s . 'p2_res_hist.dat';      // �������݃��O�t�@�C�� (dat)
$_conf['res_hist_dat_php']  = $pref_dir_s . 'p2_res_hist.dat.php';  // �������݃��O�t�@�C�� (�f�[�^PHP)
$_conf['idpw2ch_php']       = $pref_dir_s . 'p2_idpw2ch.php';       // 2ch ID�F�ؐݒ�t�@�C�� (�f�[�^PHP)
$_conf['sid2ch_php']        = $pref_dir_s . 'p2_sid2ch.php';        // 2ch ID�F�؃Z�b�V����ID�L�^�t�@�C�� (�f�[�^PHP)
$_conf['auth_user_file']    = $pref_dir_s . 'p2_auth_user.php';     // �F�؃��[�U�ݒ�t�@�C��(�f�[�^PHP)
$_conf['auth_imodeid_file'] = $pref_dir_s . 'p2_auth_imodeid.php';  // docomo i���[�hID�F�؃t�@�C�� (�f�[�^PHP)
$_conf['auth_docomo_file']  = $pref_dir_s . 'p2_auth_docomo.php';   // docomo �[�������ԍ��F�؃t�@�C�� (�f�[�^PHP)
$_conf['auth_ez_file']      = $pref_dir_s . 'p2_auth_ez.php';       // EZweb �T�u�X�N���C�oID�F�؃t�@�C�� (�f�[�^PHP)
$_conf['auth_jp_file']      = $pref_dir_s . 'p2_auth_jp.php';       // SoftBank �[���V���A���ԍ��F�؃t�@�C�� (�f�[�^PHP)
$_conf['login_log_file']    = $pref_dir_s . 'p2_login.log.php';     // ���O�C������ (�f�[�^PHP)
$_conf['login_failed_log_file'] = $pref_dir_s . 'p2_login_failed.dat.php';  // ���O�C�����s���� (�f�[�^PHP)

$_conf['matome_cache_path'] = $pref_dir_s . 'matome_cache';
$_conf['matome_cache_ext']  = '.htm';
$_conf['matome_cache_max']  = 3; // �\���L���b�V���̐�

$_conf['orig_favita_brd']   = $_conf['favita_brd'];
$_conf['orig_favlist_idx']  = $_conf['favlist_idx'];

$_conf['cookie_file_name']  = 'p2_cookie.txt';

// �␳
if ($_conf['expack.use_pecl_http'] && !extension_loaded('http')) {
    if (!($_conf['expack.use_pecl_http'] == 2 && $_conf['expack.dl_pecl_http'])) {
        $_conf['expack.use_pecl_http'] = 0;
    }
}

// �R�}���h���C�����[�h�ł͂����܂�
if (defined('P2_CLI_RUN')) {
    return;
}

// }}}
// {{{ �z�X�g�`�F�b�N

if ($_conf['secure']['auth_host'] || $_conf['secure']['auth_bbq']) {
    require_once $P2_LIB_DIR_S . 'HostCheck.php';
    if (($_conf['secure']['auth_host'] && HostCheck::getHostAuth() == false) ||
        ($_conf['secure']['auth_bbq'] && HostCheck::getHostBurned() == true)
    ) {
        HostCheck::forbidden();
    }
}

// }}}
// {{{ ���N�G�X�g�ϐ��̏���

// �V�K���O�C���ƃ����o�[���O�C���̓����w��͂��肦�Ȃ��̂ŁA�G���[���o��
if (isset($_POST['submit_new']) && isset($_POST['submit_member'])) {
    p2die('������URL�ł��B');
}

/**
 * ���N�G�X�g�ϐ����ꊇ�ŃN�H�[�g�����������R�[�h�ϊ�
 *
 * ���{�����͂���\���̂���t�H�[���ɂ͉B���v�f��
 * �G���R�[�f�B���O����p�̕�������d����ł���
 *
 * $_COOKIE �� $_REQUEST �Ɋ܂߂Ȃ�
 */
if (!empty($_GET) || !empty($_POST)) {
    if (isset($_REQUEST['_hint'])) {
        // "CP932" �� "SJIS-win" �̃G�C���A�X�ŁA"SJIS-win" �� "SJIS" �͕ʕ�
        // "CP51932", "eucJP-win", "EUC-JP" �͂��ꂼ��ʕ� (libmbfl�I�ȈӖ���)
        $request_encoding = mb_detect_encoding($_REQUEST['_hint'], 'UTF-8,CP51932,CP932');
        if ($request_encoding == 'SJIS-win') {
            $request_encoding = false;
        }
    } else {
        $request_encoding = 'UTF-8,CP51932,CP932';
    }

    if (get_magic_quotes_gpc()) {
        $_GET = array_map('stripslashes_r', $_GET);
        $_POST = array_map('stripslashes_r', $_POST);
    }

    if ($request_encoding) {
        mb_convert_variables('CP932', $request_encoding, $_GET, $_POST);
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $_POST = array_map('nullfilter_r', $_POST);
        if (count($_GET)) {
            $_GET = array_map('nullfilter_r', $_GET);
            $_REQUEST = array_merge($_GET, $_POST);
        } else {
            $_REQUEST = $_POST;
        }
    } else {
        $_GET = array_map('nullfilter_r', $_GET);
        $_REQUEST = $_GET;
    }
} else {
    $_REQUEST = array();
}

// }}}
// {{{ �[������

require_once 'Net/UserAgent/Mobile.php';

$_conf['ktai'] = false;
$_conf['iphone'] = false;
$_conf['input_type_search'] = false;

$_conf['accesskey'] = 'accesskey';
$_conf['accept_charset'] = 'Shift_JIS';
$_conf['extra_headers_ht'] = '';

$support_cookies = true;

$mobile = Net_UserAgent_Mobile::singleton();

// iPhone, iPod Touch
if (P2Util::isBrowserIphone()) {
    $_conf['ktai'] = true;
    $_conf['iphone'] = true;
    $_conf['input_type_search'] = true;
    $_conf['accept_charset'] = 'UTF-8';

// PC��
} elseif ($mobile->isNonMobile()) {
    // Safari
    if (P2Util::isBrowserSafariGroup()) {
        $_conf['input_type_search'] = true;
        $_conf['accept_charset'] = 'UTF-8';

    // Windows Mobile, �g�уQ�[���@
    } elseif (P2Util::isClientOSWindowsCE() || P2Util::isBrowserNintendoDS() || P2Util::isBrowserPSP()) {
        $_conf['ktai'] = true;
    }

// �g��
} else {
    $_conf['ktai'] = true;

    // NTT docomo i���[�h
    if ($mobile->isDoCoMo()) {
        $support_cookies = false;

    // au EZweb
    //} elseif ($mobile->isEZweb()) {
    //    $support_cookies = true;

    // SoftBank Mobile
    } elseif ($mobile->isSoftBank()) {
        // 3GC�^�[����nonumber�������T�|�[�g���Ȃ��̂�accesskey���g��
        if (!$mobile->isType3GC()) {
            $_conf['accesskey'] = 'DIRECTKEY';
            // 3GC�^�[����W�^�[���ȊO��Cookie���T�|�[�g���Ȃ�
            if (!$mobile->isTypeW()) {
                $support_cookies = false;
            }
        }

    // WILLCOM AIR-EDGE
    //} elseif ($mobile->isWillcom()) {
    //    $support_cookies = true;

    // ���̑�
    //} else {
    //    $support_cookies = true;
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
// {{{ ���[�U�ݒ� �Ǎ�

// ���[�U�ݒ�t�@�C��
$_conf['conf_user_file'] = $_conf['pref_dir'] . '/conf_user.srd.cgi';

// ���`���t�@�C�����R�s�[
$conf_user_file_old = $_conf['pref_dir'] . '/conf_user.inc.php';
if (!file_exists($_conf['conf_user_file']) && file_exists($conf_user_file_old)) {
    $old_cont = DataPhp::getDataPhpCont($conf_user_file_old);
    FileCtl::make_datafile($_conf['conf_user_file'], $_conf['conf_user_perm']);
    if (FileCtl::file_write_contents($_conf['conf_user_file'], $old_cont) === false) {
        $_info_msg_ht .= '<p>���`�����[�U�ݒ�̃R�s�[�Ɏ��s���܂����B</p>';
    }
}

// ���[�U�ݒ肪����Γǂݍ���
if (file_exists($_conf['conf_user_file'])) {
    if ($cont = file_get_contents($_conf['conf_user_file'])) {
        $conf_user = unserialize($cont);
    } else {
        $conf_user = null;
    }

    // ���炩�̗��R�Ń��[�U�ݒ�t�@�C�������Ă�����
    if (!is_array($conf_user)) {
        if (unlink($_conf['conf_user_file'])) {
            $_info_msg_ht .= '<p>���[�U�ݒ�t�@�C�������Ă����̂Ŕj�����܂����B</p>';
        } else {
            $_info_msg_ht .= '<p>���[�U�ݒ�t�@�C�������Ă��܂����A�j���ł��܂���ł����B<br>&quot;';
            $_info_msg_ht .= htmlspecialchars($_conf['conf_user_file'], ENT_QUOTES);
            $_info_msg_ht .= '&quot; ���蓮�ō폜���Ă��������B</p>';
        }
        $conf_user = array();
        $conf_user_mtime = 0;
    } else {
        $conf_user_mtime = filemtime($_conf['conf_user_file']);
    }

    // ���[�U�ݒ�t�@�C���ƃf�t�H���g�ݒ�t�@�C���̍X�V�������`�F�b�N
    if (!isset($conf_user['.']) ||
        $conf_user['.'] != P2_VERSION_ID ||
        filemtime(__FILE__) > $conf_user_mtime ||
        filemtime($P2_CONF_DIR_S . 'conf_user_def.inc.php')    > $conf_user_mtime ||
        filemtime($P2_CONF_DIR_S . 'conf_user_def_ex.inc.php') > $conf_user_mtime ||
        filemtime($P2_CONF_DIR_S . 'conf_user_def_i.inc.php')  > $conf_user_mtime)
    {
        // �f�t�H���g�ݒ��ǂݍ���
        require_once $P2_CONF_DIR_S . 'conf_user_def.inc.php';

        // �ݒ�̍X�V
        if (!array_key_exists('mobile.link_youtube', $conf_user)) {
            require_once $P2_LIB_DIR_S . 'conf_user_updater.inc.php';
            $conf_user = conf_user_update_080908($conf_user);
        }

        $_conf = array_merge($_conf, $conf_user_def, $conf_user);

        // �V�������[�U�ݒ���L���b�V��
        $conf_user = array('.' => P2_VERSION_ID);
        foreach ($conf_user_def as $k => $v) {
            $conf_user[$k] = $_conf[$k];
        }
        if (FileCtl::file_write_contents($_conf['conf_user_file'], serialize($conf_user)) === false) {
            $_info_msg_ht .= '<p>���[�U�ݒ�̃L���b�V���Ɏ��s���܂���</p>';
        }

    // ���[�U�ݒ�t�@�C���̍X�V�����̕����V�����ꍇ�́A�f�t�H���g�ݒ�𖳎�
    } else {
        $_conf = array_merge($_conf, $conf_user);
    }

    unset($cont, $conf_user);
} else {
    // �f�t�H���g�ݒ��ǂݍ���
    require_once $P2_CONF_DIR_S . 'conf_user_def.inc.php';
    $_conf = array_merge($_conf, $conf_user_def);
}

// }}}
// {{{ ���[�U�ݒ�̒�������

$_conf['ext_win_target_at'] = ($_conf['ext_win_target']) ? " target=\"{$_conf['ext_win_target']}\"" : '';
$_conf['bbs_win_target_at'] = ($_conf['bbs_win_target']) ? " target=\"{$_conf['bbs_win_target']}\"" : '';

if ($_conf['get_new_res']) {
    if ($_conf['get_new_res'] == 'all') {
        $_conf['get_new_res_l'] = $_conf['get_new_res'];
    } else {
        $_conf['get_new_res_l'] = 'l'.$_conf['get_new_res'];
    }
} else {
    $_conf['get_new_res_l'] = 'l200';
}

if ($_conf['expack.user_agent']) {
    ini_set('user_agent', $_conf['expack.user_agent']);
}

// }}}
// {{{ �f�U�C���ݒ� �Ǎ�

$skin_name = 'conf_user_style';
$skin = $P2_CONF_DIR_S . 'conf_user_style.inc.php';
if (!$_conf['ktai'] && $_conf['expack.skin.enabled']) {
    if (file_exists($_conf['expack.skin.setting_path'])) {
        $skin_name = rtrim(file_get_contents($_conf['expack.skin.setting_path']));
        $skin = P2_SKIN_DIR . DIRECTORY_SEPARATOR . $skin_name . '.php';
    } else {
        FileCtl::make_datafile($_conf['expack.skin.setting_path'], $_conf['expack.skin.setting_perm']);
    }
    if (isset($_REQUEST['skin']) && preg_match('/^\\w+$/', $_REQUEST['skin']) && $skin_name != $_REQUEST['skin']) {
        $skin_name = $_REQUEST['skin'];
        $skin = P2_SKIN_DIR . DIRECTORY_SEPARATOR . $skin_name . '.php';
        FileCtl::file_write_contents($_conf['expack.skin.setting_path'], $skin_name);
    }
}
if (!file_exists($skin)) {
    $skin_name = 'conf_user_style';
    $skin = $P2_CONF_DIR_S . 'conf_user_style.inc.php';
}
$skin_en = rawurlencode($skin_name) . '&amp;_=' . P2_VERSION_ID;
if ($_conf['view_forced_by_query']) {
    $skin_en .= $_conf['k_at_a'];
}

// �f�t�H���g�ݒ��ǂݍ����
include $P2_CONF_DIR_S . 'conf_user_style.inc.php';
// �X�L���ŏ㏑��
if ($skin != $P2_CONF_DIR_S . 'conf_user_style.inc.php') {
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
    require_once $P2_LIB_DIR_S . 'fontconfig.inc.php';

    if ($_conf['expack.am.enabled']) {
        $_conf['expack.am.fontfamily'] = p2_correct_css_fontfamily($_conf['expack.am.fontfamily']);
        if ($STYLE['fontfamily']) {
            $_conf['expack.am.fontfamily'] .= '","' . $STYLE['fontfamily'];
        }
    }

    fontconfig_apply_custom();
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
            require_once $P2_LIB_DIR_S . 'emoji.inc.php';
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

// �N�b�L�[���g���Ȃ��ꍇ��session.use_only_cookies��1���ƃZ�b�V������
// �p���ł��Ȃ��̂Łi�Z�L�����e�B���X�N�����܂邪�N�b�L�[���Ȃ��ꍇ��
// �������邵���Ȃ��j
if ($_conf['disable_cookie'] && ini_get('session.use_only_cookies')) {
    ini_set('session.use_only_cookies', 0);
}

// ���O�́A�Z�b�V�����N�b�L�[��j������Ƃ��̂��߂ɁA�Z�b�V�������p�̗L���Ɋւ�炸�ݒ肷��
session_name('PS');

if (defined('P2_FORCE_USE_SESSION') || $_conf['expack.misc.multi_favs']) {
    $_conf['use_session'] = 1;
}

$_conf['sid_at_a'] = '';

if ($_conf['use_session'] == 1 or ($_conf['use_session'] == 2 && !$_COOKIE['cid'])) {
    require_once $P2_LIB_DIR_S . 'Session.php';

    // {{{ �Z�b�V�����f�[�^�ۑ��f�B���N�g�����`�F�b�N

    if ($_conf['session_save'] == 'p2' and session_module_name() == 'files') {
        if (!is_dir($_conf['session_dir'])) {
            FileCtl::mkdir_for($_conf['session_dir'] . '/dummy_filename');
        } elseif (!is_writable($_conf['session_dir'])) {
            p2die("�Z�b�V�����f�[�^�ۑ��f�B���N�g�� ({$_conf['session_dir']}) �ɏ������݌���������܂���B");
        }

        session_save_path($_conf['session_dir']);

        // session.save_path �̃p�X�̐[����2���傫���ƃK�[�x�b�W�R���N�V�������s���Ȃ��̂�
        // ���O�ŃK�[�x�b�W�R���N�V��������
        P2Util::session_gc();
    }

    // }}}

    $_p2session = new Session();

    if (!$support_cookies) {
        if (ini_get('session.use_only_cookies')) {
            p2die('Session unavailable', 'php.ini �� session.use_only_cookies �� On �ɂȂ��Ă��܂��B');
        }
        if (!ini_get('session.use_trans_sid')) {
            output_add_rewrite_var(session_name(), session_id());
            $_conf['sid_at_a'] = '&amp;' . rawurldecode(session_name()) . '=' . rawurldecode(session_id());
        }
    }
}

// }}}
// {{{ ���C�ɃZ�b�g

// �����̂��C�ɃZ�b�g���g���Ƃ�
if ($_conf['expack.misc.multi_favs']) {
    if (!class_exists('FavSetManager', false)) {
        include $P2_LIB_DIR_S . 'FavSetManager.php';
    }
    // �؂�ւ��\���p�ɑS�Ă̂��C�ɃX���E���C�ɔ�ǂݍ���ł���
    FavSetManager::loadAllFavSet();
    // ���C�ɃZ�b�g��؂�ւ���
    FavSetManager::switchFavSet();
} else {
    $_conf['m_favlist_set'] = $_conf['m_favlist_set_at_a'] = $_conf['m_favlist_set_input_ht'] = '';
    $_conf['m_favita_set']  = $_conf['m_favita_set_at_a']  = $_conf['m_favita_set_input_ht']  = '';
    $_conf['m_rss_set']     = $_conf['m_rss_set_at_a']     = $_conf['m_rss_set_input_ht']     = '';
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
require_once $P2_LIB_DIR_S . 'Login.php';
$_login = new Login();

// ���܂��Ȃ�
//$a = ceil(1/2);
//$b = floor(1/3);
//$c = round(1/4, 1);

// }}}
}

// }}}
// {{{ p2checkenv()

/**
 * ��������m�F����
 *
 * @return bool
 */
function p2checkenv($check_recommended)
{
    global $_info_msg_ht;

    $php_version = phpversion();
    $required_version = '5.2.8';
    $recommended_version = '5.2.10';

    // PHP�̃o�[�W����
    if (version_compare($php_version, $required_version, '<')) {
        p2die("PHP {$required_version} �����ł͎g���܂���B");
    }

    // �K�{�g�����W���[��
    foreach (array('json', 'mbstring', 'pcre', 'pdo', 'pdo_sqlite', 'session', 'zlib') as $ext) {
        if (!extension_loaded($ext)) {
            p2die("{$ext} �g�����W���[�������[�h����Ă��܂���B");
        }
    }

    // �Z�[�t���[�h
    if (ini_get('safe_mode')) {
        p2die('�Z�[�t���[�h�œ��삷��PHP�ł͎g���܂���B');
    }

    // register_globals
    if (ini_get('register_globals')) {
        $msg = <<<EOP
�\�����Ȃ����������邽�߂� php.ini �� register_globals �� Off �ɂ��Ă��������B
magic_quotes_gpc �� mbstring.encoding_translation �� Off �ɂ���邱�Ƃ��������߂��܂��B
EOP;
        p2die('register_globals �� On �ł��B', $msg);
    }

    // eAccelerator
    if (extension_loaded('eaccelerator') && version_compare(EACCELERATOR_VERSION, '0.9.5.2', '<')) {
        $err = 'eAccelerator���X�V���Ă��������B';
        $ev = EACCELERATOR_VERSION;
        $msg = <<<EOP
<p>PHP 5.2�ŗ�O��ߑ��ł��Ȃ����̂���eAccelerator ({$ev})���C���X�g�[������Ă��܂��B<br>
eAccelerator�𖳌��ɂ��邩�A���̖�肪�C�����ꂽeAccelerator 0.9.5.2�ȍ~���g�p���Ă��������B<br>
<a href="http://eaccelerator.net/">http://eaccelerator.net/</a></p>
EOP;
        p2die($err, $msg, true);
    }

    // �����o�[�W����
    if ($check_recommended && version_compare($php_version, $recommended_version, '<')) {
        $conf_php = htmlspecialchars(__FILE__, ENT_QUOTES);
        $_info_msg_ht .= <<<EOP
<p><strong>�����o�[�W�������Â�PHP�œ��삵�Ă��܂��B</strong><em>(PHP {$php_version})</em><br>
PHP {$recommended_version} �ȍ~�ɃA�b�v�f�[�g���邱�Ƃ��������߂��܂��B<br>
<small>�i���̃��b�Z�[�W��\�����Ȃ��悤�ɂ���ɂ� {$conf_php} �� {$check_recommended} �s�ڂ�
&quot;p2checkenv(__LINE__);&quot; �� quot;p2checkenv(false);&quot; �ɏ��������Ă��������j</small></p>
EOP;
        return false;
    }

    return true;
}

// }}}
// {{{ __autoload()

/**
 * PEAR�ő�2������false�ɂ�����class_exists()��ǂ�ł���\��������̂�
 * __autoload()���g���͕̂|��
 */
/*function __autoload($name)
{
    if (preg_match('/^[A-Za-z_][0-9A-Za-z_]*$/', $name)) {
        require_once str_replace('_', DIRECTORY_SEPARATOR, $name) . '.php';
    }
}*/

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
