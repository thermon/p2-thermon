<?php
/**
 * rep2 - �f�U�C���p �ݒ�t�@�C��
 *
 * Mac OS X �̃��^��UI���X�L��
 *
 * �R�����g�`����() ���̓f�t�H���g�l
 * �ݒ�� style/*_css.inc �ƘA��
 */

$STYLE['a_underline_none'] = "2"; // ("2") �����N�ɉ������i����:0, ���Ȃ�:1, �X���^�C�g���ꗗ�������Ȃ�:2�j

// {{{ �t�H���g

if (strpos($_SERVER['HTTP_USER_AGENT'], 'Mac') !== false) {
    /* Mac�p�t�H���g�t�@�~���[*/
    if (P2Util::isBrowserSafariGroup()){ /* Safari�n�Ȃ� */
        $STYLE['fontfamily'] = array("Helvetica Neue", "Helvetica", "Hiragino Kaku Gothic Pro"); // ("Hiragino Kaku Gothic Pro") ��{�̃t�H���g for Safari
        $STYLE['fontfamily_bold'] = array("Arial Black", "Hiragino Kaku Gothic Std"); // ("") ��{�{�[���h�p�t�H���g for Safari�i���ʂ̑�����葾���������ꍇ��"Hiragino Kaku Gothic Std"�j
    } else {
        $STYLE['fontfamily'] = array("Helvetica Neue", "Helvetica", "�q���M�m�p�S Pro W3"); // ("�q���M�m�p�S Pro W3") ��{�̃t�H���g
        $STYLE['fontfamily_bold'] = array("Arial Black", "�q���M�m�p�S Std W8"); // ("�q���M�m�p�S Pro W6") ��{�{�[���h�p�t�H���g�i���ʂɑ����ɂ������ꍇ�͎w�肵�Ȃ�("")�j
    }
    /* Mac�p�t�H���g�T�C�Y */
    $STYLE['fontsize'] = "12px"; // ("12px") ��{�t�H���g�̑傫��
    $STYLE['menu_fontsize'] = "11px"; // ("11px") ���j���[�̃t�H���g�̑傫��
    $STYLE['sb_fontsize'] = "11px"; // ("11px") �X���ꗗ�̃t�H���g�̑傫��
    $STYLE['read_fontsize'] = "12px"; // ("12px") �X���b�h���e�\���̃t�H���g�̑傫��
    $STYLE['respop_fontsize'] = "11px"; // ("11px") ���p���X�|�b�v�A�b�v�\���̃t�H���g�̑傫��
    $STYLE['infowin_fontsize'] = "11px"; // ("11px") ���E�B���h�E�̃t�H���g�̑傫��
    $STYLE['form_fontsize'] = "11px"; // ("11px") input, option, select �̃t�H���g�̑傫���iCamino�������j
}else{
    /* Mac�ȊO�̃t�H���g�t�@�~���[*/
    $STYLE['fontfamily'] = "�l�r �o�S�V�b�N"; // ("�l�r �o�S�V�b�N") ��{�̃t�H���g
    /* Mac�ȊO�̃t�H���g�T�C�Y */
    $STYLE['fontsize'] = "12px"; // ("12px") ��{�t�H���g�̑傫��
    $STYLE['menu_fontsize'] = "12px"; // ("12px") ���j���[�̃t�H���g�̑傫��
    $STYLE['sb_fontsize'] = "12px"; // ("12px") �X���ꗗ�̃t�H���g�̑傫��
    $STYLE['read_fontsize'] = "13px"; // ("13px") �X���b�h���e�\���̃t�H���g�̑傫��
    $STYLE['respop_fontsize'] = "12px"; // ("12px") ���p���X�|�b�v�A�b�v�\���̃t�H���g�̑傫��
    $STYLE['infowin_fontsize'] = "12px"; // ("12px") ���E�B���h�E�̃t�H���g�̑傫��
    $STYLE['form_fontsize'] = "12px"; // ("12px") input, option, select �̃t�H���g�̑傫��
}

// }}}
/**
 * �F�ʂ̐ݒ�
 *
 * ���w��("")�̓u���E�U�̃f�t�H���g�F�A�܂��͊�{�w��ƂȂ�܂��B
 * �D��x�́A�ʃy�[�W�w�� �� ��{�w�� �� �g�p�u���E�U�̃f�t�H���g�w�� �ł��B
 */
// {{{ ��{(style)

$STYLE['bgcolor'] = "#e3e3e3"; // ("#ffffff") ��{ �w�i�F
$STYLE['background'] = "./skin/metal/hl10a.gif"; // ("") ��{ �w�i�摜
$STYLE['textcolor'] = "#000"; // ("#000") ��{ �e�L�X�g�F
$STYLE['acolor'] = ""; // ("") ��{ �����N�F
$STYLE['acolor_v'] = ""; // ("") ��{ �K��ς݃����N�F�B
$STYLE['acolor_h'] = "#09c"; // ("#09c") ��{ �}�E�X�I�[�o�[���̃����N�F

$STYLE['fav_color'] = "#999"; // ("#999") ���C�Ƀ}�[�N�̐F

// }}}
// {{{ ���j���[(menu)

$STYLE['menu_bgcolor'] = "#e3e3e3"; //("#fff") ���j���[�̔w�i�F
$STYLE['menu_background'] = "./skin/metal/hl10a.gif"; //("") ���j���[�̔w�i�摜
$STYLE['menu_color'] = "#000"; //("#000") menu �e�L�X�g�F
$STYLE['menu_cate_color'] = "#333"; // ("#333") ���j���[�J�e�S���[�̐F

$STYLE['menu_acolor_h'] = "#09c"; // ("#09c") ���j���[ �}�E�X�I�[�o�[���̃����N�F

$STYLE['menu_ita_color'] = ""; // ("") ���j���[ �� �����N�F
$STYLE['menu_ita_color_v'] = ""; // ("") ���j���[ �� �K��ς݃����N�F
$STYLE['menu_ita_color_h'] = "#09c"; // ("#09c") ���j���[ �� �}�E�X�I�[�o�[���̃����N�F

$STYLE['menu_newthre_color'] = "hotpink";   // ("hotpink") menu �V�K�X���b�h���̐F
$STYLE['menu_newres_color'] = "#f30";   // ("#ff3300") menu �V�����X���̐F

// }}}
// {{{ �X���ꗗ(subject)

$STYLE['sb_bgcolor'] = "#e3e3e3"; // ("#fff") subject �w�i�F
$STYLE['sb_background'] = "./skin/metal/hl10b.gif"; // ("") subject �w�i�摜
$STYLE['sb_color'] = "#000";  // ("#000") subject �e�L�X�g�F

$STYLE['sb_acolor'] = "#000"; // ("#000") subject �����N�F
$STYLE['sb_acolor_v'] = "#000"; // ("#000") subject �K��ς݃����N�F
$STYLE['sb_acolor_h'] = "#09c"; // ("#09c") subject �}�E�X�I�[�o�[���̃����N�F

$STYLE['sb_th_bgcolor'] = "#bcbcbc"; // ("#d6e7ff") subject �e�[�u���w�b�_�w�i�F
$STYLE['sb_th_background'] = "./skin/metal/hl06b.gif"; // ("") subject �e�[�u���w�b�_�w�i�摜
$STYLE['sb_tbgcolor'] = "#e3e3e3"; // ("#fff") subject �e�[�u�����w�i�F0
$STYLE['sb_tbackground'] = "./skin/metal/hl10b.gif"; // ("") subject �e�[�u�����w�i0
$STYLE['sb_tbgcolor1'] = "#CFCFCF"; // ("#eef") subject �e�[�u�����w�i�F1
$STYLE['sb_tbackground1'] = "./skin/metal/hl08b.gif"; // ("") subject �e�[�u�����w�i1

$STYLE['sb_ttcolor'] = "#333"; // ("#333") subject �e�[�u���� �e�L�X�g�F
$STYLE['sb_tacolor'] = "#000"; // ("#000") subject �e�[�u���� �����N�F
$STYLE['sb_tacolor_h'] = "#09c"; // ("#09c")subject �e�[�u���� �}�E�X�I�[�o�[���̃����N�F

$STYLE['sb_order_color'] = "#111"; // ("#111") �X���ꗗ�̔ԍ� �����N�F

$STYLE['thre_title_color'] = "#000"; // ("#000") subject �X���^�C�g�� �����N�F
$STYLE['thre_title_color_v'] = "#999"; // ("#999") subject �X���^�C�g�� �K��ς݃����N�F
$STYLE['thre_title_color_h'] = "#09c"; // ("#09c") subject �X���^�C�g�� �}�E�X�I�[�o�[���̃����N�F

$STYLE['sb_tool_bgcolor'] = "#9F9F9F"; // ("#8cb5ff") subject �c�[���o�[�̔w�i�F
$STYLE['sb_tool_background'] = "./skin/metal/hl03b.gif"; // ("") subject �c�[���o�[�̔w�i�摜
$STYLE['sb_tool_border_color'] = "#808080"; // ("#6393ef") subject �c�[���o�[�̃{�[�_�[�F
$STYLE['sb_tool_color'] = "#d6e7ff"; // ("#d6e7ff") subject �c�[���o�[�� �����F
$STYLE['sb_tool_acolor'] = "#d6e7ff"; // ("#d6e7ff") subject �c�[���o�[�� �����N�F
$STYLE['sb_tool_acolor_v'] = "#d6e7ff"; // ("#d6e7ff") subject �c�[���o�[�� �K��ς݃����N�F
$STYLE['sb_tool_acolor_h'] = "#fff"; // ("#fff") subject �c�[���o�[�� �}�E�X�I�[�o�[���̃����N�F
$STYLE['sb_tool_sepa_color'] = "#000"; // ("#000") subject �c�[���o�[�� �Z�p���[�^�����F

$STYLE['sb_now_sort_color'] = "#14a";   // ("red") subject ���݂̃\�[�g�F

$STYLE['sb_thre_title_new_color'] = "Red";  // ("red") subject �V�K�X���^�C�g���̐F

$STYLE['sb_tool_newres_color'] = "#f30"; // ("#ff3300") subject �c�[���o�[�� �V�K���X���̐F
$STYLE['sb_newres_color'] = "#f30"; // ("#ff3300") subject �V�����X���̐F

// }}}
// {{{ �X�����e(read)

$STYLE['read_bgcolor'] = "#e3e3e3"; // ("#efefef") �X���b�h�\���̔w�i�F
$STYLE['read_background'] = "./skin/metal/hl10a.gif"; // ("") �X���b�h�\���̔w�i�摜
$STYLE['read_color'] = "#000"; // ("#000") �X���b�h�\���̃e�L�X�g�F

$STYLE['read_acolor'] = ""; // ("") �X���b�h�\�� �����N�F
$STYLE['read_acolor_v'] = ""; // ("") �X���b�h�\�� �K��ς݃����N�F
$STYLE['read_acolor_h'] = "#09c"; // ("#09c") �X���b�h�\�� �}�E�X�I�[�o�[���̃����N�F

$STYLE['read_newres_color'] = "#f30"; // ("#ff3300")  �V�����X�Ԃ̐F

$STYLE['read_thread_title_color'] = "#f40"; // ("#f40") �X���b�h�^�C�g���F
$STYLE['read_name_color'] = "#14a"; // ("#1144aa") ���e�҂̖��O�̐F
$STYLE['read_mail_color'] = ""; // ("") ���e�҂�mail�̐F ex)"#a00000"
$STYLE['read_mail_sage_color'] = ""; // ("") sage�̎��̓��e�҂�mail�̐F ex)"#00b000"
$STYLE['read_ngword'] = "#999"; // ("#bbbbbb") NG���[�h�̐F

// }}}
// {{{ �������[�h

$SYTLE['live_b_width'] = "1px"; // ("1px") �������[�h�A�{�[�_�[��
$SYTLE['live_b_color'] = "#808080"; // ("#888") �������[�h�A�{�[�_�[�F
$SYTLE['live_b_style'] = "solid"; // ("solid") �������[�h�A�{�[�_�[�`��

// }}}
// {{{ ���X�������݃t�H�[��

$STYLE['post_pop_size'] = "610,350"; // ("610,350") ���X�������݃|�b�v�A�b�v�E�B���h�E�̑傫���i��,�c�j
$STYLE['post_msg_rows'] = 10; // (10) ���X�������݃t�H�[���A���b�Z�[�W�t�B�[���h�̍s��
$STYLE['post_msg_cols'] = 70; // (70) ���X�������݃t�H�[���A���b�Z�[�W�t�B�[���h�̌���

// }}}
// {{{ ���X�|�b�v�A�b�v

$STYLE['respop_color'] = "#000"; // ("#000") ���X�|�b�v�A�b�v�̃e�L�X�g�F
$STYLE['respop_bgcolor'] = "#d9d9d9"; // ("#ffffcc") ���X�|�b�v�A�b�v�̔w�i�F
$STYLE['respop_background'] = "./skin/metal/hl09a.gif"; // ("") ���X�|�b�v�A�b�v�̔w�i�摜
$STYLE['respop_b_width'] = "2px"; // ("1px") ���X�|�b�v�A�b�v�̃{�[�_�[��
$STYLE['respop_b_color'] = "#2f4f4f"; // ("black") ���X�|�b�v�A�b�v�̃{�[�_�[�F
$STYLE['respop_b_style'] = "inset"; // ("solid") ���X�|�b�v�A�b�v�̃{�[�_�[�`��

$STYLE['info_pop_size'] = "600,380"; // ("600,380") ���|�b�v�A�b�v�E�B���h�E�̑傫���i��,�c�j

// }}}
// {{{ style/*_css.inc �Œ�`����Ă��Ȃ��ݒ�

$MYSTYLE['read']['.thread_title']['text-shadow'] = "3px 3px 2px #000";

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
