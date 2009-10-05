<?php
/**
 * ImageCache2 - �T���l�C���̍č\�z
 */

// {{{ p2��{�ݒ�ǂݍ���&�F��

require_once './conf/conf.inc.php';

$_login->authorize();
ini_set('display_errors', true);
if (!$_conf['expack.ic2.enabled']) {
    p2die('ImageCache2�͖����ł��B', 'conf/conf_admin_ex.inc.php �̐ݒ��ς��Ă��������B');
}

// }}}

/*if ($GLOBALS['debug']) {
    require_once 'Var_Dump.php';
    Var_Dump::display($_GET);
    exit;
}*/

require_once 'PEAR.php';
require_once 'DB/DataObject.php';
require_once P2EX_LIB_DIR . '/ic2/loadconfig.inc.php';
require_once P2EX_LIB_DIR . '/ic2/DataObject/Common.php';
require_once P2EX_LIB_DIR . '/ic2/Thumbnailer.php';

// {{{ ���N�G�X�g�p�����[�^�̏���

$uri    = $_GET['u'];
$type   = $_GET['v'];
$thumb  = isset($_GET['t']) ? intval($_GET['t']) : 0;
$options = array();
$options['quality'] = isset($_GET['q']) ? intval($_GET['q']) : null;
$options['rotate']  = isset($_GET['r']) ? intval($_GET['r']) : 0;
$options['trim']    = !empty($_GET['w']);
if (isset($_GET['x']) && $_GET['x'] >= 1 && isset($_GET['y']) && $_GET['y'] >= 1) {
    $options['width']   = intval($_GET['x']);
    $options['height']  = intval($_GET['y']);
}
$preset = isset($_GET['p']) ? $_GET['p'] : '';
if (!empty($preset)) {
    $ini = ic2_loadconfig();
    $preset = $_GET['p'];
    if (isset($ini['Dynamic']['presets'][$preset])) {
        $options['width']   = $ini['Dynamic']['presets'][$preset][0];
        $options['height']  = $ini['Dynamic']['presets'][$preset][1];
        if (isset($ini['Dynamic']['presets'][$preset][2])) {
            $options['quality'] = $ini['Dynamic']['presets'][$preset][2];
        }
    }
}
$attachment = !empty($_GET['z']);

// }}}
// {{{ �摜�������E�T���l�C�����쐬

$search = new IC2_DataObject_Images;

switch ($type) {
    case 'id':
        $search->whereAddQuoted('id', '=', $uri);
        break;
    case 'file':
        preg_match('/^([1-9][0-9]*)_([0-9a-f]{32})(?:\.(jpg|png|gif))?$/', $uri, $fdata);
        $search->whereAddQuoted('size', '=', $fdata[0]);
        $search->whereAddQuoted('md5', '=', $fdata[1]);
        break;
    default:
        $search->whereAddQuoted('uri', '=', $uri);
}

if ($search->find(true)) {
    if (!empty($_GET['o'])) {
        $thumb = new IC2_Thumbnailer(IC2_Thumbnailer::SIZE_DEFAULT);
        $src = $thumb->srcPath($search->size, $search->md5, $search->mime);
        if (!file_exists($src)) {
            ic2_mkthumb_error("&quot;{$uri}&quot;�̃��[�J���L���b�V��������܂���B");
        } else {
            ic2_mkthumb_success(basename($src), $search->mime, $src, true, $attachment);
        }
    } else {
        $thumb = new IC2_Thumbnailer($thumb, $options);
        $result = $thumb->convert($search->size, $search->md5, $search->mime, $search->width, $search->height);
        if (PEAR::isError($result)) {
            ic2_mkthumb_error($result->getMessage());
        } else {
            $mime = ($thumb->type == '.png') ? 'image/png' : 'image/jpeg';
            ic2_mkthumb_success(basename($result), $mime, $thumb->buf, false, $attachment);
        }
    }
} else {
    ic2_mkthumb_error("&quot;{$uri}&quot;�̓L���b�V������Ă��܂���B");
}

// }}}
// {{{ ic2_mkthumb_success()

/**
 * �T���l�C���̍쐬�ɐ��������ꍇ
 */
function ic2_mkthumb_success($name, $mime, $data, $is_file, $attachment)
{
    while (ob_get_level()) {
        ob_end_clean();
    }
    header(sprintf('Content-Type: %s; filename="%s"', $mime, $name));
    if ($attachment) {
        header(sprintf('Content-Disposition: attachment; filename="%s"', $name));
    } else {
        header(sprintf('Content-Disposition: inline; filename="%s"', $name));
    }
    if ($is_file) {
        header(sprintf('Content-Length: %d', filesize($data)));
        readfile($data);
    } else {
        header(sprintf('Content-Length: %d', strlen($data)));
        echo $data;
    }
}

// }}}
// {{{ ic2_mkthumb_error()

/**
 * �T���l�C���̍쐬�Ɏ��s�����ꍇ
 */
function ic2_mkthumb_error($msg)
{
    echo <<<EOF
<html>
<head><title>ImageCache::Error</title></head>
<body>
<p>{$msg}</p>
</body>
</html>
EOF;
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
