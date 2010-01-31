<?php
/**
 * rep2expack - �g���p�b�N�@�\�� On/Off �ƃ��[�U�ݒ�ҏW�y�[�W����ύX�����Ȃ��ݒ�
 *
 * ���̃t�@�C���̐ݒ�́A�K�v�ɉ����ĕύX���Ă�������
 */

// {{{ �S��

// ImageCache2 ���Ńt�@�C���������[�g����擾����ۂ� User-Agent
// ��̏ꍇ�̓u���E�U��User-Agent�𑗐M����
$_conf['expack.user_agent'] = ""; // ("")

// �R�}���h���C����PHP�̃p�X�B�ł��邾����΃p�X�Ŏw�肷�邱��
$_conf['expack.php_cli_path'] = "php"; // ("php")

// Zend Framework (Zend Gdata�ł���) ��library�f�B���N�g���ւ̃p�X
$_conf['expack.zf_path'] = ""; // ("")

// pecl_http �����p�ł���ꍇ�AHttpRequestPool �ɂ�����_�E�����[�h��L���ɂ���
// (off:0, on:1, �R�}���h���C���Ŏ��s:2)
$_conf['expack.use_pecl_http'] = 1; // (1)

// expack.use_pecl_http �� 2 ����CLI�pphp.ini��http�G�N�X�e���V������
// ���[�h����悤�ɂȂ��Ă��Ȃ��ꍇ�̂� 1 �ɂ���
$_conf['expack.dl_pecl_http'] = 0; // (0)

// }}}
// {{{ �X�L��

// �X�L���ioff:0, on:1�j
$_conf['expack.skin.enabled'] = 1; // (1)

// �ݒ�t�@�C���̃p�X
$_conf['expack.skin.setting_path'] = $_conf['pref_dir'] . '/p2_user_skin.txt';

// �ݒ�t�@�C���̃p�[�~�b�V����
$_conf['expack.skin.setting_perm'] = 0606; // (0606)

// �t�H���g�ݒ�t�@�C���̃p�X
$_conf['expack.skin.fontconfig_path'] = $_conf['pref_dir'] . '/p2_user_font.txt';

// �t�H���g�ݒ�t�@�C���̃p�[�~�b�V����
$_conf['expack.skin.fontconfig_perm'] = 0606; // (0606)

// }}}
// {{{ tGrep

// �ꔭ�������X�g�̃p�X
$_conf['expack.tgrep.quick_file'] = $_conf['pref_dir'] . '/p2_tgrep_quick.txt';

// �����������X�g�̃p�X
$_conf['expack.tgrep.recent_file'] = $_conf['pref_dir'] . '/p2_tgrep_recent.txt';

// �t�@�C���̃p�[�~�b�V����
$_conf['expack.tgrep.file_perm'] = 0606; // (0606)

// }}}
// {{{ �X�}�[�g�|�b�v�A�b�v���j���[

// SPM�ioff:0, on:1�j
$_conf['expack.spm.enabled'] = 1; // (1)

// }}}
// {{{ �A�N�e�B�u���i�[

// AA �␳�ioff:0, on:1�j
$_conf['expack.am.enabled'] = 1; // (0)

// }}}
// {{{ ���͎x��

// ActiveMona �ɂ�� AA �v���r���[�ioff:0, on:1�j
$_conf['expack.editor.with_activemona'] = 0; // (0)

// AAS �ɂ�� AA �v���r���[�ioff:0, on:1�j
$_conf['expack.editor.with_aas'] = 0; // (0)

// }}}
// {{{ RSS���[�_

// RSS���[�_�ioff:0, on:1�j
$_conf['expack.rss.enabled'] = 1; // (0)

// �ݒ�t�@�C���̃p�X
$_conf['expack.rss.setting_path'] = $_conf['pref_dir'] . '/p2_rss.txt';

// �ݒ�t�@�C���̃p�[�~�b�V����
$_conf['expack.rss.setting_perm'] = 0606; // (0606)

// ImageCache2���g���ă����N���ꂽ�摜���L���b�V������ioff:0, on:1�j
$_conf['expack.rss.with_imgcache'] = 1; // (0)

// }}}
// {{{ ImageCache2

/*
 * ���̋@�\���g���ɂ�PHP��GD�@�\�g���܂���ImageMagick��
 * SQLite, PostgreSQL, MySQL�̂����ꂩ���K�v�B
 * ���p�ɓ������Ă� doc/ImageCache2/README.txt �� doc/ImageCache2/INSTALL.txt ��
 * �悭�ǂ�ŁA����ɏ]���Ă��������B
 */

// ImageCache2�ioff:0, PC�̂�:1, �g�т̂�:2, ����:3�j
$_conf['expack.ic2.enabled'] = 3; // (0)

// �ꎞ�I��ON/OFF�̐ؑփt���O��ۑ�����t�@�C���̃p�X
$_conf['expack.ic2.switch_path'] = $_conf['pref_dir'] . '/ic2_switch.txt';

// }}}
// {{{ Google����

// Google�����ioff:0, on:1�j
$_conf['expack.google.enabled'] = 1; // (0)

// WSDL �̃p�X�i��F/path/to/googleapi/GoogleSearch.wsdl�j
$_conf['expack.google.wsdl'] = "./conf/GoogleSearch.wsdl"; // ("./conf/GoogleSearch.wsdl")

// }}}
// {{{ AAS

// AAS�ioff:0, on:1�j
$_conf['expack.aas.enabled'] = 1; // (0)
//TrueType�t�H���g�̃p�X
$_conf['expack.aas.font_path'] = "./ttf/mona.ttf"; // ("./ttf/mona.ttf")
//$_conf['expack.aas.font_path'] = "./ttf/ipagp-mona.ttf";

// ���l�Q�Ƃ̃f�R�[�h�Ɏ��s�����Ƃ��̑�֕���
$_conf['expack.aas.unknown_char'] = "?"; // ("?")

// �t�H���g�`�揈���̕����R�[�h
// "CP51932" �ł� configure �̃I�v�V������ --enable-gd-native-ttf ���w�肳��Ă��Ȃ��ƕ�����������
// ���̂Ƃ� Unicode �Ή��t�H���g���g���Ă���Ȃ� "UTF-8" �ɂ���Ɛ������\���ł���
$_conf['expack.aas.output_charset'] = "CP51932"; // ("CP51932")

// }}}
// {{{ ���̑�

// ���C�ɃZ�b�g�؂�ւ��ioff:0, on:1�j
$_conf['expack.misc.multi_favs'] = 0; // (0)

// ���p���邨�C�ɃZ�b�g���i���C�ɃX���E���C�ɔERSS�ŋ��ʁj
$_conf['expack.misc.favset_num'] = 5; // (5)

// ���C�ɃZ�b�g�������L�^����t�@�C���̃p�X
$_conf['expack.misc.favset_file'] = $_conf['pref_dir'] . '/p2_favset.txt';

// iPhone�Ŕ�/�X����BB2C�ŊJ���{�^����\�� (off:0, on:1)
$_conf['expack.misc.use_bb2c'] = 0; // (0)

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
