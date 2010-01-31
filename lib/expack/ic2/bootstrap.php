<?php
/**
 * rep2expack - ImageCache2 �������X�N���v�g
 */

require_once 'DB.php';
require_once 'DB/DataObject.php';

// {{{ GLOBALS

$GLOBALS['_P2_GETIMAGE_CACHE'] = array();

// }}}
// {{{ constants

define('P2_IMAGECACHE_OK',     0);
define('P2_IMAGECACHE_ABORN',  1);
define('P2_IMAGECACHE_BROKEN', 2);
define('P2_IMAGECACHE_LARGE',  3);
define('P2_IMAGECACHE_VIRUS',  4);

define('P2_IMAGECACHE_BLACKLIST_NOMORE', 0);
define('P2_IMAGECACHE_BLACKLIST_ABORN',  1);
define('P2_IMAGECACHE_BLACKLIST_VIRUS',  2);

// }}}
// {{{ ic2_loadconfig()

/**
 * ���[�U�ݒ�ǂݍ��݊֐�
 *
 * @param void
 * @return array
 */
function ic2_loadconfig()
{
    static $ini = null;

    if (is_null($ini)) {
        include P2_CONF_DIR . '/conf_ic2.inc.php';

        $ini = array();
        $_ic2conf = preg_grep('/^expack\\.ic2\\.\\w+\\.\\w+$/', array_keys($_conf));
        foreach ($_ic2conf as $key) {
            $p = explode('.', $key);
            $cat = ucfirst($p[2]);
            $name = $p[3];
            if (!isset($ini[$cat])) {
                $ini[$cat] = array();
            }
            $ini[$cat][$name] = $_conf[$key];
        }

        // DB_DataObject�̐ݒ�
        $_dao_options = &PEAR::getStaticProperty('DB_DataObject', 'options');
        if (!is_array($_dao_options)) {
            $_dao_options = array();
        }
        $_dao_options['database'] = $ini['General']['dsn'];
        $_dao_options['debug'] = false;
        $_dao_options['quote_identifiers'] = true;
        $_dao_options['db_driver'] = 'DB';
    }

    return $ini;
}

// }}}
// {{{ ic2_findexec()

/**
 * ���s�t�@�C�������֐�
 *
 * $search_path������s�t�@�C��$command����������
 * ������΃p�X���G�X�P�[�v���ĕԂ��i$escape���U�Ȃ炻�̂܂ܕԂ��j
 * ������Ȃ����false��Ԃ�
 *
 * @param string $command
 * @param string $search_path
 * @param bool $escape
 * @return string
 */
function ic2_findexec($command, $search_path = '', $escape = true)
{
    // Windows���A���̑���OS��
    if (P2_OS_WINDOWS) {
        if (strtolower(strrchr($command, '.')) != '.exe') {
            $command .= '.exe';
        }
        $check = function_exists('is_executable') ? 'is_executable' : 'file_exists';
    } else {
        $check = 'is_executable';
    }

    // $search_path����̂Ƃ��͊��ϐ�PATH���猟������
    if ($search_path == '') {
        $search_dirs = explode(PATH_SEPARATOR, getenv('PATH'));
    } else {
        $search_dirs = explode(PATH_SEPARATOR, $search_path);
    }

    // ����
    foreach ($search_dirs as $path) {
        $path = realpath($path);
        if ($path === false || !is_dir($path)) {
            continue;
        }
        if ($check($path . DIRECTORY_SEPARATOR . $command)) {
            return ($escape ? escapeshellarg($command) : $command);
        }
    }

    // ������Ȃ�����
    return false;
}

// }}}
// {{{ ic2_load_class()

/**
 * �N���X���[�_�[
 *
 * @string $name
 * @return void
 */
function ic2_load_class($name)
{
    if (strncmp($name, 'IC2_', 3) === 0) {
        include P2EX_LIB_DIR . '/ic2/' . str_replace('_', '/', substr($name, 3)) . '.php';
    } elseif (strncmp($name, 'Thumbnailer_', 12) === 0) {
        include P2_LIB_DIR . '/' . str_replace('_', '/', $name) . '.php';
    }
}

// }}}

spl_autoload_register('ic2_load_class');

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
