<?php
/*
    rep2expack - ���[�U�ݒ� �f�t�H���g

    ���̃t�@�C���̓f�t�H���g�l�̐ݒ�Ȃ̂ŁA���ɕύX����K�v�͂���܂���
*/

// {{{ �g�т̃J���[�����O�ݒ�

// �w�i
$conf_user_def['mobile.background_color'] = ""; // ("")
$conf_user_rules['mobile.background_color'] = array('notHtmlColorToDef');

// ��{�����F
$conf_user_def['mobile.text_color'] = ""; // ("")
$conf_user_rules['mobile.text_color'] = array('notHtmlColorToDef');

// �����N
$conf_user_def['mobile.link_color'] = ""; // ("")
$conf_user_rules['mobile.link_color'] = array('notHtmlColorToDef');

// �K��ς݃����N
$conf_user_def['mobile.vlink_color'] = ""; // ("")
$conf_user_rules['mobile.vlink_color'] = array('notHtmlColorToDef');

// �V���X���b�h�}�[�N
$conf_user_def['mobile.newthre_color'] = "#ff0000"; // ("#ff0000")
$conf_user_rules['mobile.newthre_color'] = array('notHtmlColorToDef');

// �X���b�h�^�C�g��
$conf_user_def['mobile.ttitle_color'] = "#1144aa"; // ("#1144aa")
$conf_user_rules['mobile.ttitle_color'] = array('notHtmlColorToDef');

// �V�����X�ԍ�
$conf_user_def['mobile.newres_color'] = "#ff6600"; // ("#ff6600")
$conf_user_rules['mobile.newres_color'] = array('notHtmlColorToDef');

// NG���[�h
$conf_user_def['mobile.ngword_color'] = "#bbbbbb"; // ("#bbbbbb")
$conf_user_rules['mobile.ngword_color'] = array('notHtmlColorToDef');

// �I���U�t���C���X�ԍ�
$conf_user_def['mobile.onthefly_color'] = "#00aa00"; // ("#00aa00")
$conf_user_rules['mobile.onthefly_color'] = array('notHtmlColorToDef');

// �t�B���^�����O�Ń}�b�`�����L�[���[�h
$conf_user_def['mobile.match_color'] = ""; // ("")
$conf_user_rules['mobile.match_color'] = array('notHtmlColorToDef');

// �A�N�Z�X�L�[�̔ԍ���\���i���Ȃ�:0, ����:1, �G����:2�j
$conf_user_def['mobile.display_accesskey'] = 1; // (1)
$conf_user_rad['mobile.display_accesskey'] = array('2' => '�G����', '1' => '�\��', '0' => '��\��');

// }}}
// {{{ tGrep

// �ꔭ�����ioff:0, on:1�j
$conf_user_def['expack.tgrep.quicksearch'] = 1; // (1)
$conf_user_rad['expack.tgrep.quicksearch'] = array('1' => '�\��', '0' => '��\��');

// �����������L�^���鐔�ioff:0�j
$conf_user_def['expack.tgrep.recent_num'] = 10; // (10)
$conf_user_rules['expack.tgrep.recent_num'] = array('notIntExceptMinusToDef');

// �T�[�`�{�b�N�X�Ɍ����������L�^���鐔�ASafari��p�ioff:0�j
$conf_user_def['expack.tgrep.recent2_num'] = 10; // (10)
$conf_user_rules['expack.tgrep.recent2_num'] = array('notIntExceptMinusToDef');

// }}}
// {{{ �X�}�[�g�|�b�v�A�b�v���j���[

// �����Ƀ��X�ioff:0, on:1�j
// conf_admin_ex.inc.php �� $_conf['disable_res'] �� 1 �ɂȂ��Ă���Ǝg���Ȃ�
$conf_user_def['expack.spm.kokores'] = 1; // (1)
$conf_user_rad['expack.spm.kokores'] = array('1' => '�\��', '0' => '��\��');

// �����Ƀ��X�ŊJ���t�H�[���Ɍ����X�̓��e��\������ioff:0, on:1�j
$conf_user_def['expack.spm.kokores_orig'] = 1; // (1)
$conf_user_rad['expack.spm.kokores_orig'] = array('1' => '����', '0' => '���Ȃ�');

// ���ځ[�񃏁[�h�ENG���[�h�o�^�ioff:0, on:1�j
$conf_user_def['expack.spm.ngaborn'] = 1; // (1)
$conf_user_rad['expack.spm.ngaborn'] = array('1' => '�\��', '0' => '��\��');

// ���ځ[�񃏁[�h�ENG���[�h�o�^���Ɋm�F����ioff:0, on:1�j
$conf_user_def['expack.spm.ngaborn_confirm'] = 1; // (1)
$conf_user_rad['expack.spm.ngaborn_confirm'] = array('1' => '����', '0' => '���Ȃ�');

// �t�B���^�����O�ioff:0, on:1�j
$conf_user_def['expack.spm.filter'] = 1; // (1)
$conf_user_rad['expack.spm.filter'] = array('1' => '�\��', '0' => '��\��');

// �t�B���^�����O���ʂ��J���t���[���܂��̓E�C���h�E
$conf_user_def['expack.spm.filter_target'] = "_popup"; // ("_popup")
$conf_user_sel['expack.spm.filter_target'] = array(
    '_popup'    => 'HTML�|�b�v�A�b�v',
    '_blank'    => '�V�K�E�C���h�E',
    '_self'     => '�����t���[��',
    //'_parent' => '�e�t���[��',
    //'_top'    => '�����E�C���h�E',
);

// }}}
// {{{ �A�N�e�B�u���i�[

// �t�H���g
$conf_user_def['expack.am.fontfamily'] = "Mona,���i�["; // ("Mona,���i�[")

// �����̑傫��
$conf_user_def['expack.am.fontsize'] = "16px"; // ("16px")

// �X�C�b�`��\������ʒu
$conf_user_def['expack.am.display'] = 0; // (0)
$conf_user_sel['expack.am.display'] = array('0' => 'ID�̉�', '1' => 'SPM', '2' => '����');

// �������� (PC)
$conf_user_def['expack.am.autodetect'] = 0; // (0)
$conf_user_rad['expack.am.autodetect'] = array('1' => '����', '0' => '���Ȃ�');

// �������� & NG ���[�h���AAAS ���L���Ȃ� AAS �̃����N���쐬 (�g��)
$conf_user_def['expack.am.autong_k'] = 0; // (0)
$conf_user_rad['expack.am.autong_k'] = array('1' => '����', '0' => '���Ȃ�', '2' => '���� (�A��NG�͂��Ȃ�)');

// �������肷��s���̉���
$conf_user_def['expack.am.lines_limit'] = 5; // (5)
$conf_user_rules['expack.am.lines_limit'] = array('notIntExceptMinusToDef');

// }}}
// {{{ ���͎x��

// ��^��
//$conf_user_def['expack.editor.constant'] = 0; // (0)
//$conf_user_rad['expack.editor.constant'] = array('1' => '�g��', '0' => '�g��Ȃ�');

// ���A���^�C���E�v���r���[
$conf_user_def['expack.editor.dpreview'] = 0; // (0)
$conf_user_sel['expack.editor.dpreview'] = array('1' => '���e�t�H�[���̏�ɕ\��', '2' => '���e�t�H�[���̉��ɕ\��', '0' => '��\��');

// ���A���^�C���E�v���r���[��AA�␳�p�̃`�F�b�N�{�b�N�X��\������
$conf_user_def['expack.editor.dpreview_chkaa'] = 0; // (0)
$conf_user_rad['expack.editor.dpreview_chkaa'] = array('1' => '����', '0' => '���Ȃ�');

// �{������łȂ����`�F�b�N
$conf_user_def['expack.editor.check_message'] = 0; // (0)
$conf_user_rad['expack.editor.check_message'] = array('1' => '����', '0' => '���Ȃ�');

// sage �`�F�b�N
$conf_user_def['expack.editor.check_sage'] = 0; // (0)
$conf_user_rad['expack.editor.check_sage'] = array('1' => '����', '0' => '���Ȃ�');

// }}}
// {{{ RSS���[�_

// RSS���X�V���ꂽ���ǂ����m�F����Ԋu�i���w��j
$conf_user_def['expack.rss.check_interval'] = 30; // (30)
$conf_user_rules['expack.rss.check_interval'] = array('notIntExceptMinusToDef');

// RSS�̊O�������N���J���t���[���܂��̓E�C���h�E
$conf_user_def['expack.rss.target_frame'] = "read"; // ("read")

// �T�v���J���t���[���܂��̓E�C���h�E
$conf_user_def['expack.rss.desc_target_frame'] = "read"; // ("read")

// }}}
// {{{ ImageCache2

// �摜�L���b�V���ꗗ�̃f�t�H���g�\�����[�h
$conf_user_def['expack.ic2.viewer_default_mode'] = 0; // (0)
$conf_user_sel['expack.ic2.viewer_default_mode'] = array('3' => '�T���l�C������', '0' => '�ꗗ', '1' => '�ꊇ�ύX', '2' => '�ʊǗ�');

// �L���b�V���Ɏ��s�����Ƃ��̊m�F�p��ime�o�R�Ń\�[�X�ւ̃����N���쐬
$conf_user_def['expack.ic2.through_ime'] = 0; // (0)
$conf_user_rad['expack.ic2.through_ime'] = array('1' => '����', '0' => '���Ȃ�');

// �|�b�v�A�b�v�摜�̑傫�����E�C���h�E�̑傫���ɍ��킹��
$conf_user_def['expack.ic2.fitimage'] = 0; // (0)
$conf_user_sel['expack.ic2.fitimage'] = array('1' => '����', '0' => '���Ȃ�', '2' => '�����傫���Ƃ���������', '3' => '�������傫���Ƃ���������', '4' => '�蓮�ł���');

// �g�тŃC�����C���E�T���l�C�����L���̂Ƃ��̕\�����鐧�����i0�Ŗ������j
$conf_user_def['expack.ic2.pre_thumb_limit_k'] = 5; // (5)
$conf_user_rules['expack.ic2.pre_thumb_limit_k'] = array('notIntExceptMinusToDef');

// �V�����X�̉摜�� pre_thumb_limit �𖳎����đS�ĕ\������
$conf_user_def['expack.ic2.newres_ignore_limit'] = 0; // (0)
$conf_user_rad['expack.ic2.newres_ignore_limit'] = array('1' => '����', '0' => '���Ȃ�');

// �g�тŐV�����X�̉摜�� pre_thumb_limit_k �𖳎����đS�ĕ\������
$conf_user_def['expack.ic2.newres_ignore_limit_k'] = 0; // (0)
$conf_user_rad['expack.ic2.newres_ignore_limit_k'] = array('1' => '����', '0' => '���Ȃ�');

// }}}
// {{{ AAS

// �g�т� AA �Ǝ������肳�ꂽ�Ƃ��C�����C�� AAS �\������i0:���Ȃ�; 1:����;�j
$conf_user_def['expack.aas.inline_enabled'] = 0; // (0)
$conf_user_rad['expack.aas.inline_enabled'] = array('1' => '����', '0' => '���Ȃ�');

// PC�p�̉摜�`���i0:PNG; 1:JPEG; 2:GIF;�j
$conf_user_def['expack.aas.default.type'] = 0; // (0)
$conf_user_sel['expack.aas.default.type'] = array('0' => 'PNG', '1' => 'JPEG', '2' => 'GIF');

// JPEG�̕i���i0-100�j
$conf_user_def['expack.aas.default.quality'] = 80; // (80)
$conf_user_rules['expack.aas.default.quality'] = array('emptyToDef', 'notIntExceptMinusToDef');

// PC�p�̉摜�̉��� (�s�N�Z��)
$conf_user_def['expack.aas.default.width'] = 640; // (640)
$conf_user_rules['expack.aas.default.width'] = array('emptyToDef', 'notIntExceptMinusToDef');

// PC�p�̉摜�̍��� (�s�N�Z��)
$conf_user_def['expack.aas.default.height'] = 480; // (480)
$conf_user_rules['expack.aas.default.height'] = array('emptyToDef', 'notIntExceptMinusToDef');

// PC�p�̉摜�̃}�[�W�� (�s�N�Z��)
$conf_user_def['expack.aas.default.margin'] = 5; // (5)
$conf_user_rules['expack.aas.default.margin'] = array('notIntExceptMinusToDef');

// �����T�C�Y (�|�C���g)
$conf_user_def['expack.aas.default.fontsize'] = 16; // (16)
$conf_user_rules['expack.aas.default.fontsize'] = array('emptyToDef', 'notIntExceptMinusToDef');

// �������摜����͂ݏo��ꍇ�A���T�C�Y���Ĕ[�߂� (0:���T�C�Y; 1:��\��)
$conf_user_def['expack.aas.default.overflow'] = 0; // (0)
$conf_user_rad['expack.aas.default.overflow'] = array('1' => '��\��', '0' => '���T�C�Y');

// �����ɂ��� (0:���Ȃ�; 1:����)
$conf_user_def['expack.aas.default.bold'] = 0; // (0)
$conf_user_rad['expack.aas.default.bold'] = array('1' => '����', '0' => '���Ȃ�');

// �����F (6���܂���3����16�i��)
$conf_user_def['expack.aas.default.fgcolor'] = '000000'; // ('000000')

// �w�i�F (6���܂���3����16�i��)
$conf_user_def['expack.aas.default.bgcolor'] = 'ffffff'; // ('ffffff')

// �g�їp�̉摜�`���i0:PNG; 1:JPEG; 2:GIF;�j
$conf_user_def['expack.aas.mobile.type'] = 2; // (2)
$conf_user_sel['expack.aas.mobile.type'] = array('0' => 'PNG', '1' => 'JPEG', '2' => 'GIF');

// JPEG�̕i���i0-100�j
$conf_user_def['expack.aas.mobile.quality'] = 80; // (80)
$conf_user_rules['expack.aas.mobile.quality'] = array('emptyToDef', 'notIntExceptMinusToDef');

// �g�їp�̉摜�̉��� (�s�N�Z��)
$conf_user_def['expack.aas.mobile.width'] = 230; // (230)
$conf_user_rules['expack.aas.mobile.width'] = array('emptyToDef', 'notIntExceptMinusToDef');

// �g�їp�̉摜�̍��� (�s�N�Z��)
$conf_user_def['expack.aas.mobile.height'] = 450; // (450)
$conf_user_rules['expack.aas.mobile.height'] = array('emptyToDef', 'notIntExceptMinusToDef');

// �g�їp�̉摜�̃}�[�W�� (�s�N�Z��)
$conf_user_def['expack.aas.mobile.margin'] = 2; // (2)
$conf_user_rules['expack.aas.mobile.margin'] = array('notIntExceptMinusToDef');

// �����T�C�Y (�|�C���g)
$conf_user_def['expack.aas.mobile.fontsize'] = 16; // (16)
$conf_user_rules['expack.aas.mobile.fontsize'] = array('emptyToDef', 'notIntExceptMinusToDef');

// �������摜����͂ݏo��ꍇ�A���T�C�Y���Ĕ[�߂� (0:���T�C�Y; 1:��\��)
$conf_user_def['expack.aas.mobile.overflow'] = 0; // (0)
$conf_user_rad['expack.aas.mobile.overflow'] = array('1' => '��\��', '0' => '���T�C�Y');

// �����ɂ��� (0:���Ȃ�; 1:����)
$conf_user_def['expack.aas.mobile.bold'] = 0; // (0)
$conf_user_rad['expack.aas.mobile.bold'] = array('1' => '����', '0' => '���Ȃ�');

// �����F (6���܂���3����16�i��)
$conf_user_def['expack.aas.mobile.fgcolor'] = '000000'; // ('000000')

// �w�i�F (6���܂���3����16�i��)
$conf_user_def['expack.aas.mobile.bgcolor'] = 'ffffff'; // ('ffffff')

// �C�����C���\���̉摜�`���i0:PNG; 1:JPEG; 2:GIF;�j
$conf_user_def['expack.aas.inline.type'] = 2; // (2)
$conf_user_sel['expack.aas.inline.type'] = array('0' => 'PNG', '1' => 'JPEG', '2' => 'GIF');

// JPEG�̕i���i0-100�j
$conf_user_def['expack.aas.inline.quality'] = 80; // (80)
$conf_user_rules['expack.aas.inline.quality'] = array('emptyToDef', 'notIntExceptMinusToDef');

// �C�����C���\���̉��� (�s�N�Z��)
$conf_user_def['expack.aas.inline.width'] = 64; // (64)
$conf_user_rules['expack.aas.inline.width'] = array('emptyToDef', 'notIntExceptMinusToDef');

// �C�����C���\���̍��� (�s�N�Z��)
$conf_user_def['expack.aas.inline.height'] = 64; // (64)
$conf_user_rules['expack.aas.inline.height'] = array('emptyToDef', 'notIntExceptMinusToDef');

// �C�����C���\���̃}�[�W�� (�s�N�Z��)
$conf_user_def['expack.aas.inline.margin'] = 0; // (0)
$conf_user_rules['expack.aas.inline.margin'] = array('notIntExceptMinusToDef');

// �����T�C�Y (�|�C���g)
$conf_user_def['expack.aas.inline.fontsize'] = 6; // (6)
$conf_user_rules['expack.aas.inline.fontsize'] = array('emptyToDef', 'notIntExceptMinusToDef');

// �������摜����͂ݏo��ꍇ�A���T�C�Y���Ĕ[�߂� (0:���T�C�Y; 1:��\��)
$conf_user_def['expack.aas.inline.overflow'] = 1; // (1)
$conf_user_rad['expack.aas.inline.overflow'] = array('1' => '��\��', '0' => '���T�C�Y');

// �����ɂ��� (0:���Ȃ�; 1:����)
$conf_user_def['expack.aas.inline.bold'] = 0; // (0)
$conf_user_rad['expack.aas.inline.bold'] = array('1' => '����', '0' => '���Ȃ�');

// �����F (6���܂���3����16�i��)
$conf_user_def['expack.aas.inline.fgcolor'] = '000000'; // ('000000')

// �w�i�F (6���܂���3����16�i��)
$conf_user_def['expack.aas.inline.bgcolor'] = 'ffffff'; // ('ffffff')

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
