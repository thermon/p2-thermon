<?php
/**
 * rep2expack - ���`�F�b�N�Ŏg�p����ϐ�
 */

// �K�{�o�[�W����
$p2_required_version_5_2 = '5.2.8';
$p2_required_version_5_3 = '5.3.0';

// �����o�[�W����
$p2_recommended_version_5_2 = '5.2.12';
$p2_recommended_version_5_3 = '5.3.1';

// �K�{�g�����W���[��
$p2_required_extensions = array(
    'dom',
    'json',
    'libxml',
    'mbstring',
    'pcre',
    'pdo',
    'pdo_sqlite',
    'session',
    'spl',
    //'xsl',
    'zlib',
);

// �L�����Ɠ��삵�Ȃ�php.ini�f�B���N�e�B�u
$p2_incompatible_ini_directives = array(
    'safe_mode',
    'register_globals',
    'magic_quotes_gpc',
    'mbstring.encoding_translation',
    'session.auto_start',
);

// �ڍs�X�N���v�g�̎��s���K�v�ȕύX�̂������o�[�W�����ԍ��̔z��
// �l�� "yymmdd.hhmm" �`���Ń��j�[�N�������Ƀ\�[�g����Ă��Ȃ���΂Ȃ�Ȃ�
$p2_changed_versions = array(
    '100113.1300',
    '100120.0700',
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
