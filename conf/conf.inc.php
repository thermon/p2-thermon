<?php
/**
 * rep2 - ��{�ݒ�t�@�C��
 * ���̃t�@�C���́A���ɗ��R�̖�������ύX���Ȃ�����
 */

// �o�[�W�������
$_conf = array(
    'p2version' => '1.7.29+1.8.x',  // rep2�̃o�[�W����
    'p2expack'  => '100131.1645',   // �g���p�b�N�̃o�[�W����
    'p2name'    => 'expack',        // rep2�̖��O
);

$_conf['p2ua'] = "{$_conf['p2name']}/{$_conf['p2version']}+{$_conf['p2expack']}";

define('P2_VERSION_ID', sprintf('%u', crc32($_conf['p2ua'])));

/*
 * �ʏ�̓Z�b�V�����t�@�C���̃��b�N�҂����ɗ͒Z�����邽��
 * ���[�U�[�F�،シ���ɃZ�b�V�����ϐ��̕ύX���R�~�b�g����B
 * �F�،���Z�b�V�����ϐ���ύX����X�N���v�g�ł�
 * ���̃t�@�C����ǂݍ��ޑO��
 *  define('P2_SESSION_CLOSE_AFTER_AUTHENTICATION', 0);
 * �Ƃ���B
 */
if (!defined('P2_SESSION_CLOSE_AFTER_AUTHENTICATION')) {
    define('P2_SESSION_CLOSE_AFTER_AUTHENTICATION', 1);
}

// {{{ �O���[�o���ϐ���������

$_info_msg_ht = null; // ���[�U�ʒm�p ��񃁃b�Z�[�WHTML

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
p2_init();

// E_NOTICE ����шÖق̔z�񏉊�������
$_conf['filtering'] = false;
$hd = array('word' => null);
$htm = array();
$word = null;

// {{{ p2_init()

/**
 * �ꎞ�ϐ��ŃO���[�o���ϐ����������Ȃ��悤�ɐݒ菈�����֐���
 */
function p2_init()
{
    global $MYSTYLE, $STYLE, $debug;
    global $skin, $skin_en, $skin_name, $skin_uniq;
    global $_conf, $_login, $_p2session;

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
    $_conf['expack.web_url']        = 'http://page2.skr.jp/rep2/';
    $_conf['expack.download_url']   = 'http://page2.skr.jp/rep2/downloads.html';
    $_conf['expack.history_url']    = 'http://page2.skr.jp/rep2/history.html';
    $_conf['expack.tgrep_url']      = 'http://page2.xrea.jp/tgrep/search';
    $_conf['expack.gate_php']       = 'http://page2.skr.jp/gate.php';
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

    // file($filename, FILE_IGNORE_NEW_LINES) �� CR/LF/CR+LF �̂�������s���Ƃ��Ĉ���
    ini_set('auto_detect_line_endings', 1);

    // session.trans_sid�L���� �� output_add_rewrite_var(),
    // http_build_query() ���Ő����E�ύX�����
    // URL��GET�p�����[�^��؂蕶��(��)��"&amp;"�ɂ���B�i�f�t�H���g��"&"�j
    ini_set('arg_separator.output', '&amp;');

    // Windows �Ȃ�
    if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
        // Windows
        defined('PATH_SEPARATOR') or define('PATH_SEPARATOR', ';');
        defined('DIRECTORY_SEPARATOR') or define('DIRECTORY_SEPARATOR', '\\');
        define('P2_OS_WINDOWS', 1);
    } else {
        defined('PATH_SEPARATOR') or define('PATH_SEPARATOR', ':');
        defined('DIRECTORY_SEPARATOR') or define('DIRECTORY_SEPARATOR', '/');
        define('P2_OS_WINDOWS', 0);
    }

    // HTTPS�ڑ��Ȃ�
    if (array_key_exists('HTTPS', $_SERVER) && strcasecmp($_SERVER['HTTPS'], 'on') === 0) {
        define('P2_HTTPS_CONNECTION', 1);
    } else {
        define('P2_HTTPS_CONNECTION', 0);
    }

    // �k���o�C�g�萔
    // mbstring.script_encoding = SJIS-win ����
    // "\0", "\x00" �ȍ~���J�b�g�����̂ŁAchr()�֐����g��
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
    define('P2_LIB_DIR', P2_BASE_DIR . '/lib');

    // ���܂��I�ȋ@�\��񋟂��邷�郉�C�u����
    define('P2EX_LIB_DIR', P2_BASE_DIR . '/lib/expack');

    // �X�^�C���V�[�g
    define('P2_STYLE_DIR', P2_BASE_DIR . '/style');

    // �X�L��
    define('P2_SKIN_DIR', P2_BASE_DIR . '/skin');
    define('P2_USER_SKIN_DIR', P2_BASE_DIR . '/user_skin');

    // PEAR�C���X�g�[���f�B���N�g���A�����p�X�ɒǉ������
    define('P2_PEAR_DIR', P2_BASE_DIR . '/includes');

    // �R�}���h���C���c�[��
    define('P2_CLI_DIR', P2_BASE_DIR . '/cli');

    // �����p�X���Z�b�g
    if (is_dir(P2_PEAR_DIR)) {
        set_include_path(P2_PEAR_DIR . PATH_SEPARATOR . get_include_path());
    }

    // }}}
    // {{{ ���`�F�b�N�ƃf�o�b�O

    // ���[�e�B���e�B��ǂݍ���
    include P2_LIB_DIR . '/global.funcs.php';
    include P2_LIB_DIR . '/startup.funcs.php';
    spl_autoload_register('p2_load_class');

    // ��������m�F (�v���𖞂����Ă���Ȃ�R�����g�A�E�g��)
    p2_check_environment(__LINE__);

    if ($debug) {
        if (!class_exists('Benchmark_Profiler', false)) {
            require 'Benchmark/Profiler.php';
        }
        $profiler = new Benchmark_Profiler(true);
        // p2_print_memory_usage();
        register_shutdown_function('p2_print_memory_usage');
    }

    // }}}
    // {{{ �����R�[�h�̎w��

    mb_internal_encoding('SJIS-win');
    mb_http_output('pass');
    mb_substitute_character(63); // �����R�[�h�ϊ��Ɏ��s���������� "?" �ɂȂ�
    //mb_substitute_character(0x3013); // ��
    //ob_start('mb_output_handler');

    if (function_exists('mb_ereg_replace')) {
        define('P2_MBREGEX_AVAILABLE', 1);
        mb_regex_encoding('SJIS-win');
    } else {
        define('P2_MBREGEX_AVAILABLE', 0);
    }

    // }}}
    // {{{ �Ǘ��җp�ݒ�etc.

    // �Ǘ��җp�ݒ��ǂݍ���
    include P2_CONF_DIR . '/conf_admin.inc.php';

    // �f�B���N�g���̐�΃p�X��
    $_conf['data_dir'] = p2_realpath($_conf['data_dir']);
    $_conf['dat_dir']  = p2_realpath($_conf['dat_dir']);
    $_conf['idx_dir']  = p2_realpath($_conf['idx_dir']);
    $_conf['pref_dir'] = p2_realpath($_conf['pref_dir']);
    $_conf['db_dir']   = p2_realpath($_conf['db_dir']);

    // �Ǘ��p�ۑ��f�B���N�g��
    $_conf['admin_dir'] = $_conf['data_dir'] . '/admin';

    // cache �ۑ��f�B���N�g��
    // 2005/06/29 $_conf['pref_dir'] . '/p2_cache' ���ύX
    $_conf['cache_dir'] = $_conf['data_dir'] . '/cache';

    // Cookie �ۑ��f�B���N�g��
    // 2008/09/09 $_conf['pref_dir'] . '/p2_cookie' ���ύX
    $_conf['cookie_dir'] = $_conf['data_dir'] . '/cookie';

    // �R���p�C�����ꂽ�e���v���[�g�̕ۑ��f�B���N�g��
    $_conf['compile_dir'] = $_conf['data_dir'] . '/compile';

    // �Z�b�V�����f�[�^�ۑ��f�B���N�g��
    $_conf['session_dir'] = $_conf['data_dir'] . '/session';

    // �e���|�����f�B���N�g��
    $_conf['tmp_dir'] = $_conf['data_dir'] . '/tmp';

    // �o�[�W����ID���d���p����q�A�h�L�������g���ɖ��ߍ��ނ��߂̕ϐ�
    $_conf['p2_version_id'] = P2_VERSION_ID;

    // �����R�[�h��������p�̃q���g������
    $_conf['detect_hint'] = '����';
    $_conf['detect_hint_input_ht'] = '<input type="hidden" name="_hint" value="����">';
    $_conf['detect_hint_input_xht'] = '<input type="hidden" name="_hint" value="����" />';
    //$_conf['detect_hint_utf8'] = mb_convert_encoding('����', 'UTF-8', 'SJIS-win');
    $_conf['detect_hint_q'] = '_hint=%81%9D%81%9E'; // rawurlencode($_conf['detect_hint'])
    $_conf['detect_hint_q_utf8'] = '_hint=%E2%97%8E%E2%97%87'; // rawurlencode($_conf['detect_hint_utf8'])

    // }}}
    // {{{ �ϐ��ݒ�

    $preferences = array(
        'conf_user_file'    => 'conf_user.srd.cgi',     // ���[�U�[�ݒ�t�@�C�� (�V���A���C�Y�h�f�[�^)
        'favita_brd'        => 'p2_favita.brd',         // ���C�ɔ� (brd)
        'favlist_idx'       => 'p2_favlist.idx',        // ���C�ɃX�� (idx)
        'recent_idx'        => 'p2_recent.idx',         // �ŋߓǂ񂾃X�� (idx)
        'palace_idx'        => 'p2_palace.idx',         // �X���̓a�� (idx)
        'res_hist_idx'      => 'p2_res_hist.idx',       // �������݃��O (idx)
        'res_hist_dat'      => 'p2_res_hist.dat',       // �������݃��O�t�@�C�� (dat)
        'res_hist_dat_php'  => 'p2_res_hist.dat.php',   // �������݃��O�t�@�C�� (�f�[�^PHP)
        'idpw2ch_php'       => 'p2_idpw2ch.php',        // 2ch ID�F�ؐݒ�t�@�C�� (�f�[�^PHP)
        'sid2ch_php'        => 'p2_sid2ch.php',         // 2ch ID�F�؃Z�b�V����ID�L�^�t�@�C�� (�f�[�^PHP)
        'auth_user_file'    => 'p2_auth_user.php',      // �F�؃��[�U�ݒ�t�@�C��(�f�[�^PHP)
        'auth_imodeid_file' => 'p2_auth_imodeid.php',   // docomo i���[�hID�F�؃t�@�C�� (�f�[�^PHP)
        'auth_docomo_file'  => 'p2_auth_docomo.php',    // docomo �[�������ԍ��F�؃t�@�C�� (�f�[�^PHP)
        'auth_ez_file'      => 'p2_auth_ez.php',        // EZweb �T�u�X�N���C�oID�F�؃t�@�C�� (�f�[�^PHP)
        'auth_jp_file'      => 'p2_auth_jp.php',        // SoftBank �[���V���A���ԍ��F�؃t�@�C�� (�f�[�^PHP)
        'login_log_file'    => 'p2_login.log.php',      // ���O�C������ (�f�[�^PHP)
        'login_failed_log_file' => 'p2_login_failed.dat.php',   // ���O�C�����s���� (�f�[�^PHP)
    );
    foreach ($preferences as $k => $v) {
        $_conf[$k] = $_conf['pref_dir'] . '/' . $v;
    }

    $_conf['orig_favita_brd']   = $_conf['favita_brd'];
    $_conf['orig_favlist_idx']  = $_conf['favlist_idx'];

    $_conf['cookie_db_path']    = $_conf['db_dir'] . '/p2_cookies.sqlite3';
    $_conf['post_db_path']      = $_conf['db_dir'] . '/p2_post_data.sqlite3';
    $_conf['hostcheck_db_path'] = $_conf['db_dir'] . '/p2_hostcheck_cache.sqlite3';
    $_conf['matome_db_path']    = $_conf['db_dir'] . '/p2_matome_cache.sqlite3';
    $_conf['iv2_cache_db_path'] = $_conf['db_dir'] . '/iv2_cache.sqlite3';

    // �␳
    if ($_conf['expack.use_pecl_http'] && !extension_loaded('http')) {
        if (!($_conf['expack.use_pecl_http'] == 2 && $_conf['expack.dl_pecl_http'])) {
            $_conf['expack.use_pecl_http'] = 0;
        }
    }

    // }}}

    include P2_CONF_DIR . '/empty_style.php';
    include P2_LIB_DIR . '/bootstrap.php';
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
