<?php
/**
 * rep2expack - iPhone�p���[�U�����ݒ�
 */

// {{{ subject-i (�X���ꗗ)

// �����������C���W�P�[�^�[��\�� (����:1, ���Ȃ�:0)
$conf_user_def['iphone.subject.indicate-speed'] = 0; // (0)
$conf_user_rad['iphone.subject.indicate-speed'] = array('1' => '����', '0' => '���Ȃ�');

// �C���W�P�[�^�[�̕� (pixels)
$conf_user_def['iphone.subject.speed.width'] = 10; // (10)
$conf_user_rules['iphone.subject.speed.width'] = array('notIntExceptMinusToDef');

// �C���W�P�[�^�[�̐F (1���X/������)
$conf_user_def['iphone.subject.speed.0rpd'] = "#eeeeee"; // ("#eeeeee")
$conf_user_rules['iphone.subject.speed.0rpd'] = array('notCssColorToDef');

// �C���W�P�[�^�[�̐F (1���X/���ȏ�)
$conf_user_def['iphone.subject.speed.1rpd'] = "#ffcccc"; // ("#ffcccc")
$conf_user_rules['iphone.subject.speed.1rpd'] = array('notCssColorToDef');

// �C���W�P�[�^�[�̐F (10���X/���ȏ�)
$conf_user_def['iphone.subject.speed.10rpd'] = "#ff9999"; // ("#ff9999")
$conf_user_rules['iphone.subject.speed.10rpd'] = array('notCssColorToDef');

// �C���W�P�[�^�[�̐F (100���X/���ȏ�)
$conf_user_def['iphone.subject.speed.100rpd'] = "#ff6666"; // ("#ff6666")
$conf_user_rules['iphone.subject.speed.100rpd'] = array('notCssColorToDef');

// �C���W�P�[�^�[�̐F (1000���X/���ȏ�)
$conf_user_def['iphone.subject.speed.1000rpd'] = "#ff3333"; // ("#ff3333")
$conf_user_rules['iphone.subject.speed.1000rpd'] = array('notCssColorToDef');

// �C���W�P�[�^�[�̐F (10000���X/���ȏ�)
$conf_user_def['iphone.subject.speed.10000rpd'] = "#ff0000"; // ("#ff0000")
$conf_user_rules['iphone.subject.speed.10000rpd'] = array('notCssColorToDef');

// }}}
// {{{ read-i (�X�����e)


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
