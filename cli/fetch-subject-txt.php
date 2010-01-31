<?php
/**
 * rep2expack - �R�}���h���C����subject.txt�����_�E�����[�h
 */

// {{{ �����ݒ�

if (PHP_SAPI != 'cli') {
    die('CLI only!');
}

if (!extension_loaded('http')) {
    fwrite(STDERR, 'http extension is not loaded.' . PHP_EOL);
    exit(1);
}

define('P2_CLI_RUN', 1);
define('P2_FETCH_SUBJECT_TXT_DEBUG', 0);
define('P2_FETCH_SUBJECT_TXT_DEBUG_OUTPUT_FILE', '/tmp/p2_fetch_subject_txt.log');

require dirname(__FILE__) . '/../conf/conf.inc.php';
require_once 'Console/Getopt.php';

P2HttpExt::activate();

// }}}
// {{{ �R�}���h���C���������擾

$getopt = new Console_Getopt;
$args = $getopt->readPHPArgv();
if (PEAR::isError($args)) {
    fwrite(STDERR, $args->getMessage() . PHP_EOL);
    exit(1);
}
array_shift($args);

$short_options = 'm:s:';
$long_options = array('mode=', 'set=');
$options = $getopt->getopt2($args, $short_options, $long_options);
if (PEAR::isError($options)) {
    fwrite(STDERR, $options->getMessage() . PHP_EOL);
    exit(1);
}

$mode = null;
$set = null;

foreach ($options[0] as $option) {
    switch ($option[0]) {
    case 'm':
    case '--mode':
        $mode = p2_fst_checkopt_mode($option[1]);
        break;
    case 's':
    case '--set':
        $set = p2_fst_checkopt_set($option[1]);
        break;
    }
}

if ($mode === null) {
    fwrite(STDERR, 'Option `mode\' is required.' . PHP_EOL);
    exit(1);
} elseif (PEAR::isError($mode)) {
    fwrite(STDERR, sprintf('Invalid mode was given (%s).%s', $mode->getMessage(), PHP_EOL));
    exit(1);
}

if ($set === null) {
    $set = 0;
} elseif (PEAR::isError($set)) {
    fwrite(STDERR, sprintf('Invalid set was given (%s).%s', $set->getMessage(), PHP_EOL));
    exit(1);
}

// }}}
// {{{ �_�E�����[�h�Ώۂ�ݒ�

$pref_dir_s = $_conf['pref_dir'] . DIRECTORY_SEPARATOR;

switch ($mode) {
// ���C�ɃX��
case 'fav':
    if ($set == 0) {
        $source = $_conf['favlist_idx'];
    } else {
        // @see FavSetManager::switchFavSet()
        $source = $pref_dir_s . sprintf('p2_favlist%d.idx', $set);
    }
    break;

// �ŋߓǂ񂾃X��
case 'recent':
    $source = $_conf['recent_idx'];
    break;

// �������ݗ���
case 'res_hist':
    $source = $_conf['res_hist_idx'];
    break;

// ���C�ɔ��}�[�W
case 'merge_favita':
    if ($set == 0) {
        $source = $_conf['favita_brd'];
    } else {
        // @see FavSetManager::switchFavSet()
        $source = $pref_dir_s . sprintf('p2_favita%d.brd', $set);
    }
    break;

// �\�����Ȃ��G���[
default:
    fwrite(STDERR, sprintf('Invalid mode was given (%s).%s', $mode, PHP_EOL));
    exit(1);
}

// }}}
// {{{ �A�N�Z�X���`�F�b�N�E����_�E�����[�h���s

if (!is_file($source) || !is_readable($source)) {
    fwrite(STDERR, 'Permission denied: cannot read file ' . $source . PHP_EOL);
    exit(1);
}
if (!is_dir($_conf['dat_dir']) || !is_writable($_conf['dat_dir'])) {
    fwrite(STDERR, 'Permission denied: cannot write directory ' . $_conf['dat_dir'] . PHP_EOL);
    exit(1);
}

if ($mode == 'merge_favita') {
    $favitas = array();
    foreach (file($source, FILE_IGNORE_NEW_LINES) as $l) {
        if (preg_match('/^\\t?(.+?)\\t(.+?)\\t.+?$/', $l, $matches)) {
            $_host = $matches[1];
            $_bbs  = $matches[2];
            $_id   = $_host . '/' . $_bbs;
            $favitas[$_id] = array('host' => $_host, 'bbs' => $_bbs);
        }
    }
    P2HttpRequestPool::fetchSubjectTxt($favitas);
} else {
    P2HttpRequestPool::fetchSubjectTxt($source);
}

// }}}
// {{{ �㏈��

// �G���[���b�Z�[�W�̎擾
if (P2Util::hasInfoHtml()) {
    $errmsg = str_replace("\n", PHP_EOL, P2Util::getInfoHtml());
} else {
    $errmsg = null;
}

// �f�o�b�O�p���O�t�@�C���ɏ�������
if (P2_FETCH_SUBJECT_TXT_DEBUG) {
    $debug_output = '====================' . PHP_EOL;
    $debug_output .= __FILE__ . PHP_EOL;
    $debug_output .= 'date: ' . date('Y-m-d H:i:s') . PHP_EOL;

    if (extension_loaded('posix')) {
        $debug_output .= sprintf('pid: %d%s', posix_getpid(), PHP_EOL);
        $debug_output .= sprintf('uid: %d%s', posix_getuid(), PHP_EOL);
        $debug_output .= sprintf('gid: %d%s', posix_getgid(), PHP_EOL);
    } else {
        $pid = @getmypid();
        $debug_output .= sprintf('pid: %d%s', ($pid === false) ? -1 : $pid, PHP_EOL);
    }

    $debug_output .= 'mode: ' . $mode . PHP_EOL;
    if ($mode == 'merge_favita') {
        $debug_output .= print_r($favitas, true);
    }

    $debug_output .= 'error: ';
    if ($errmsg === null) {
        $debug_output .= '(none)';
    } else {
        $debug_output .= rtrim($errmsg);
    }
    $debug_output .= PHP_EOL . PHP_EOL;

    if (!P2_OS_WINDOWS) {
        $debug_output = mb_convert_encoding($debug_output, 'UTF-8', 'SJIS-win');
    }

    if (file_put_contents(P2_FETCH_SUBJECT_TXT_DEBUG_OUTPUT_FILE, $debug_output, LOCK_EX | FILE_APPEND) === false) {
        $errmsg .= sprintf("<p><b>cannot write to `%s'.</b></p>\n",
                           htmlspecialchars(P2_FETCH_SUBJECT_TXT_DEBUG_OUTPUT_FILE, ENT_QUOTES)
                           );
    }
}

// �G���[���b�Z�[�W����łȂ���΁A�G���[�R�[�h2 (�G���[���b�Z�[�W��HTML) �ŏI��
if ($errmsg !== null) {
    fwrite(STDERR, $errmsg);
    exit(2);
}

// ����I��
exit(0);

// }}}
// {{{ p2_fst_checkopt_mode()

/**
 * ���[�h������������΂��̂܂܁A�������Ȃ����PEAR_Error��Ԃ�
 *
 * @param string $mode
 * @return string|PEAR_Error
 */
function p2_fst_checkopt_mode($mode)
{
    switch ($mode) {
    case 'fav':
    case 'recent':
    case 'res_hist':
    case 'merge_favita':
        return $mode;
    }
    return PEAR::raiseError($mode);
}

// }}}
// {{{ p2_fst_checkopt_set()

/**
 * �Z�b�gID����������ΐ����Ƃ��āA�������Ȃ����PEAR_Error��Ԃ�
 *
 * @param string $set
 * @return int|PEAR_Error
 */
function p2_fst_checkopt_set($set)
{
    global $_conf;

    if (!is_numeric($set)) {
        return PEAR::raiseError($set);
    }

    $set = (int)$set;
    if ($set == 0) {
        return $set;
    }

    if (!$_conf['expack.misc.multi_favs']) {
        return PEAR::raiseError('Multi favorites is not enabled.');
    }

    if ($set > $_conf['expack.misc.favset_num']) {
        return PEAR::raiseError("{$set}: Out of range.");
    }

    return $set;
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
