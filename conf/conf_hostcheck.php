<?php
/**
 * rep2 - �z�X�g�P�ʂł̃A�N�Z�X����/���ۂ̐ݒ�t�@�C��
 *
 * ���̃t�@�C���̐ݒ�́A�K�v�ɉ����ĕύX���Ă�������
 */

$GLOBALS['_HOSTCHKCONF'] = array();

// �z�X�g���Ƃ̐ݒ� (0:����; 1:����;)
// $_conf['secure']['auth_host'] == 0 �̂Ƃ��A���R�Ȃ��疳���B
// $_conf['secure']['auth_host'] == 1 �̂Ƃ��A�l��1�i�^�j�̃z�X�g�̂݋��B
// $_conf['secure']['auth_host'] == 2 �̂Ƃ��A�l��0�i�U�j�̃z�X�g�̂݋��ہB
$GLOBALS['_HOSTCHKCONF']['host_type'] = array(
    // p2�����삵�Ă���}�V��
    'localhost' => 1,
    // �N���XA-C�̃v���C�x�[�g�A�h���X
    'private'   => 1,
    // NTT docomo i���[�h
    'docomo'    => 0,
    // au EZweb
    'au'        => 0,
    // SoftBank Mobile
    'softbank'  => 0,
    // WILLCOM AIR-EDGE
    'willcom'   => 0,
    // EMOBILE
    'emobile'   => 0,
    // iPhone 3G
    'iphone'    => 0,
    // jig�u���E�U�Ejig�u���E�UWEB�Ejig�A�v���EjigWEB
    'jig'       => 0,
    // ibisBrowserDX
    'ibis'      => 0,
    // ���[�U�[�ݒ�
    'custom'    => 0,
    // ���[�U�[�ݒ� (IPv6)
    'custom_v6' => 0,
);

// �e�g�уL�����A��IP�A�h���X�ш挟�؂Ɏ��s�����ہA
// ���K�\���Ń����[�g�z�X�g�̌��؂�����B
$GLOBALS['_HOSTCHKCONF']['mobile_use_regex'] = false;

// �A�N�Z�X��������IP�A�h���X�ш�
// �gIP�A�h���X => �}�X�N�h�`���̘A�z�z��
// $_conf['secure']['auth_host'] == 1 ����
// $GLOBALS['_HOSTCHKCONF']['host_type']['custom'] = 1 �̂Ƃ��g����
$GLOBALS['_HOSTCHKCONF']['custom_allowed_host'] = array(
    //'192.168.0.0' => 24,
    //'210.143.108.0' => 24, // jig
    //'117.55.0.0' => 17,   // emb? @link http://pc11.2ch.net/test/read.cgi/software/1216565984/531
    //'60.254.192.0' => 18, // ����
    //'119.72.0.0' => 16,   // ����
);
$GLOBALS['_HOSTCHKCONF']['custom_allowed_host_v6'] = null;

// �A�N�Z�X�������郊���[�g�z�X�g�̐��K�\��
// preg_match()�֐��̑������Ƃ��Đ�����������ł��邱��
// �g�p���Ȃ��ꍇ��null
// $_conf['secure']['auth_host'] == 1 ����
// $GLOBALS['_HOSTCHKCONF']['host_type']['custom'] = 1 �̂Ƃ��g����
$GLOBALS['_HOSTCHKCONF']['custom_allowed_host_regex'] = null;

// �A�N�Z�X�����ۂ���IP�A�h���X�ш�
// �gIP�A�h���X => �}�X�N�h�`���̘A�z�z��
// $_conf['secure']['auth_host'] == 2 ����
// $GLOBALS['_HOSTCHKCONF']['host_type']['custom'] = 0 �̂Ƃ��g����
$GLOBALS['_HOSTCHKCONF']['custom_denied_host'] = array(
    //'192.168.0.0' => 24,
);
$GLOBALS['_HOSTCHKCONF']['custom_denied_host_v6'] = null;

// �A�N�Z�X�����ۂ��郊���[�g�z�X�g�̐��K�\��
// preg_match()�֐��̑������Ƃ��Đ�����������ł��邱��
// �g�p���Ȃ��ꍇ��null
// $_conf['secure']['auth_host'] == 2 ����
// $GLOBALS['_HOSTCHKCONF']['host_type']['custom'] = 0 �̂Ƃ��g����
$GLOBALS['_HOSTCHKCONF']['custom_denied_host_regex'] = null;

// gethostbyaddr(), gethostbyname() �L���b�V���̗L������ (�b���Ŏw��A0�Ȃ疈��m�F)
$GLOBALS['_HOSTCHKCONF']['gethostby_lifetime'] = 3600;

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
