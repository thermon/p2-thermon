<?php
/**
 * ImageCache2 - �������猏�����擾����
 */

// {{{ p2��{�ݒ�ǂݍ���&�F��

require_once './conf/conf.inc.php';

$_login->authorize();

if (!$_conf['expack.ic2.enabled']) {
    p2die('ImageCache2�͖����ł��B', 'conf/conf_admin_ex.inc.php �̐ݒ��ς��Ă��������B');
}

// }}}
// {{{ HTTP�w�b�_

P2Util::header_nocache();
header('Content-Type: text/plain; charset=UTF-8');

// }}}
// {{{ ������

// �p�����[�^������
if (!isset($_GET['key'])) {
    echo 'null';
    exit;
}

// ���C�u�����ǂݍ���
require_once P2EX_LIB_DIR . '/ic2_getcount.inc.php';

// }}}
// {{{ execute

echo getIC2ImageCount((string)$_GET['key']);
exit;

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
