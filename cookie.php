<?php
/**
 * rep2 - �N�b�L�[�F�؏���
 *
 * ���������G���R�[�f�B���O: Shift_JIS
 */

require_once './conf/conf.inc.php';

$_login->authorize(); // ���[�U�F��


// �����o���p�ϐ�

$return_path = 'login.php';

$next_url = <<<EOP
{$return_path}?check_regist_cookie=1&amp;regist_cookie={$_REQUEST['regist_cookie']}{$_conf['k_at_a']}
EOP;

$next_url = str_replace('&amp;', '&', $next_url);

header('Location: '.$next_url);
exit;

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
