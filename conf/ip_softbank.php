<?php

/**
 * �\�t�g�o���N�[���̃����[�g�z�X�g���K�\����IP�A�h���X�ш�
 * (Yahoo!�P�[�^�C�EPC�T�C�g�u���E�U�� 2008/02/29 ���_�AX�V���[�Y�� 2008/09/19 ���_)
 *
 * @link http://creation.mb.softbank.jp/web/web_ip.html
 * @link http://creation.mb.softbank.jp/xseries/xseries_ip.html
 */

$host = '/\\.(?:jp-[a-z]|[a-z]\\.vodafone|softbank|openmobile|pcsitebrowser)\\.ne\\.jp$/';

$band = array(
    // Yahoo!�P�[�^�C
    '123.108.236.0/24',
    '123.108.237.0/27',
    '202.179.204.0/24',
    '202.253.96.224/27',
    '210.146.7.192/26',
    '210.146.60.192/26',
    '210.151.9.128/26',
    '210.169.130.112/28',
    '210.175.1.128/25',
    '210.228.189.0/24',
    '211.8.159.128/25',
    // PC�T�C�g�u���E�U
    '123.108.237.240/28',
    '202.253.96.0/28',
    // X�V���[�Y (IE)
    '123.108.237.240/28',
    '202.253.96.0/28',
    // X�V���[�Y (���A�v��)
    '219.73.128.0/17',
    '117.46.128.0/17',
);

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
