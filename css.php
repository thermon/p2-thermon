<?php
/**
 * rep2expack - �X�^�C���V�[�g���O���X�^�C���V�[�g�Ƃ��ďo�͂���
 */

// {{{ �����ݒ�ǂݍ��� & ���[�U�F��

require_once './conf/conf.inc.php';
$_login->authorize();
require_once P2_STYLE_DIR . '/mystyle_css.php';

// }}}
// {{{ �Ó��ȃt�@�C��������

if (isset($_GET['css']) && preg_match('/^\\w+$/', $_GET['css'])) {
    $cssName =  $_GET['css'];
    $cssFilePath = P2_STYLE_DIR . DIRECTORY_SEPARATOR . $cssName . '_css.inc';
    if (!file_exists($cssFilePath)) {
        exit;
    }
} else {
    exit;
}

// }}}
// {{{ �o��

// �N�G���Ƀ��j�[�N�L�[�𖄂ߍ���ł��邢��̂ŁA�L���b�V�������Ă悢
$now = time();
header('Expires: ' . http_date($now + 3600));
header('Last-Modified: ' . http_date($now));
header('Pragma: cache');
header('Content-Type: text/css; charset=Shift_JIS');
echo "@charset \"Shift_JIS\";\n\n";
ob_start();
include $cssFilePath;
disp_mystyle($cssName);
// ��X�^�C��������
echo preg_replace('/[a-z\\-]+[ \\t]*:[ \\t]*;/', '', ob_get_clean());

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
