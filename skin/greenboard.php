<?php
/**
 * rep2 - �f�U�C���p �ݒ�t�@�C��
 *
 * �����X�L��
 *
 * �R�����g�`����() ���̓f�t�H���g�l
 * �ݒ�� style/*_css.inc �ƘA��
 */

$STYLE['a_underline_none'] = "2"; // ("2") �����N�ɉ������i����:0, ���Ȃ�:1, �X���^�C�g���ꗗ�������Ȃ�:2�j

// {{{ �t�H���g

if (strpos($_SERVER['HTTP_USER_AGENT'], 'Mac') !== false) {
    /* Mac�p�t�H���g�t�@�~���[*/
    if (P2Util::isBrowserSafariGroup()){ /* Safari�n�Ȃ� */
        $STYLE['fontfamily'] = array("Comic Sans MS", "Hiragino Maru Gothic Pro"); // ("Hiragino Kaku Gothic Pro") ��{�̃t�H���g for Safari
        $STYLE['fontfamily_bold'] = array("Arial Black", "Hiragino Kaku Gothic Std"); // ("") ��{�{�[���h�p�t�H���g for Safari�i���ʂ̑�����葾���������ꍇ��"Hiragino Kaku Gothic Std"�j
    } else {
        $STYLE['fontfamily'] = array("Comic Sans MS", "�q���M�m�ۃS Pro W4"); // ("�q���M�m�p�S Pro W3") ��{�̃t�H���g
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
    $STYLE['read_fontsize'] = "14px"; // ("13px") �X���b�h���e�\���̃t�H���g�̑傫��
    $STYLE['respop_fontsize'] = "14px"; // ("12px") ���p���X�|�b�v�A�b�v�\���̃t�H���g�̑傫��
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

$STYLE['bgcolor'] = "#1f3f2f"; // ("#ffffff") ��{ �w�i�F
$STYLE['background'] = ""; // ("") ��{ �w�i�摜
$STYLE['textcolor'] = "#d6e9ca"; // ("#000") ��{ �e�L�X�g�F
$STYLE['acolor'] = "#ffafaf"; // ("") ��{ �����N�F
$STYLE['acolor_v'] = "#afffaf"; // ("") ��{ �K��ς݃����N�F�B
$STYLE['acolor_h'] = "#ffffaf"; // ("#09c") ��{ �}�E�X�I�[�o�[���̃����N�F

$STYLE['fav_color'] = "#fff"; // ("#999") ���C�Ƀ}�[�N�̐F

// }}}
// {{{ ���j���[(menu)

$STYLE['menu_bgcolor'] = "#1f3f2f"; //("#fff") ���j���[�̔w�i�F
$STYLE['menu_background'] = ""; //("") ���j���[�̔w�i�摜
$STYLE['menu_color'] = "#d6e9ca"; //("#000") menu �e�L�X�g�F
$STYLE['menu_cate_color'] = "#d6e9ca"; // ("#333") ���j���[�J�e�S���[�̐F

$STYLE['menu_acolor_h'] = "#ffffaf"; // ("#09c") ���j���[ �}�E�X�I�[�o�[���̃����N�F

$STYLE['menu_ita_color'] = "#bae4a6"; // ("") ���j���[ �� �����N�F
$STYLE['menu_ita_color_v'] = "#fff"; // ("") ���j���[ �� �K��ς݃����N�F
$STYLE['menu_ita_color_h'] = "#fff"; // ("#09c") ���j���[ �� �}�E�X�I�[�o�[���̃����N�F

$STYLE['menu_newthre_color'] = "#ffffaf";   // ("hotpink") menu �V�K�X���b�h���̐F
$STYLE['menu_newres_color'] = "#ff0";   // ("#ff3300") menu �V�����X���̐F

// }}}
// {{{ �X���ꗗ(subject)

$STYLE['sb_bgcolor'] = "#1f3f2f"; // ("#fff") subject �w�i�F
$STYLE['sb_background'] = ""; // ("") subject �w�i�摜
$STYLE['sb_color'] = "#d6e9ca";  // ("#000") subject �e�L�X�g�F

$STYLE['sb_acolor'] = "#ffafaf"; // ("#000") subject �����N�F
$STYLE['sb_acolor_v'] = "#afffaf"; // ("#000") subject �K��ς݃����N�F
$STYLE['sb_acolor_h'] = "#ffffaf"; // ("#09c") subject �}�E�X�I�[�o�[���̃����N�F

$STYLE['sb_th_bgcolor'] = "#1f3f2f"; // ("#d6e7ff") subject �e�[�u���w�b�_�w�i�F
$STYLE['sb_th_background'] = ""; // ("") subject �e�[�u���w�b�_�w�i�摜
$STYLE['sb_tbgcolor'] = "#1f3f2f"; // ("#fff") subject �e�[�u�����w�i�F0
$STYLE['sb_tbgcolor1'] = "#1f3f2f"; // ("#eef") subject �e�[�u�����w�i�F1
$STYLE['sb_tbgcolor_nosubject'] = "#1f3f2f"; // ("#eef") subject �e�[�u�����w�i�F�idat�q�Ɂj
$STYLE['sb_tbackground'] = ""; // ("") subject �e�[�u�����w�i�摜0
$STYLE['sb_tbackground1'] = ""; // ("") subject �e�[�u�����w�i�摜1

$STYLE['sb_ttcolor'] = "#d6e9ca"; // ("#333") subject �e�[�u���� �e�L�X�g�F
$STYLE['sb_tacolor'] = "#ffafaf"; // ("#000") subject �e�[�u���� �����N�F
$STYLE['sb_tacolor_h'] = "#ffffaf"; // ("#09c")subject �e�[�u���� �}�E�X�I�[�o�[���̃����N�F

$STYLE['sb_order_color'] = "#fff"; // ("#111") �X���ꗗ�̔ԍ� �����N�F

$STYLE['thre_title_color'] = "#d6e9ca"; // ("#000") subject �X���^�C�g�� �����N�F
$STYLE['thre_title_color_v'] = "#46845b"; // ("#999") subject �X���^�C�g�� �K��ς݃����N�F
$STYLE['thre_title_color_h'] = "#fff"; // ("#09c") subject �X���^�C�g�� �}�E�X�I�[�o�[���̃����N�F

$STYLE['sb_tool_bgcolor'] = "#1f3f2f"; // ("#8cb5ff") subject �c�[���o�[�̔w�i�F
$STYLE['sb_tool_background'] = ""; // ("") subject �c�[���o�[�̔w�i�摜
$STYLE['sb_tool_border_color'] = "#fff"; // ("#6393ef") subject �c�[���o�[�̃{�[�_�[�F
$STYLE['sb_tool_color'] = "#46845b"; // ("#d6e7ff") subject �c�[���o�[�� �����F
$STYLE['sb_tool_acolor'] = "#afffaf"; // ("#d6e7ff") subject �c�[���o�[�� �����N�F
$STYLE['sb_tool_acolor_v'] = "#afffaf"; // ("#d6e7ff") subject �c�[���o�[�� �K��ς݃����N�F
$STYLE['sb_tool_acolor_h'] = "#ffffaf"; // ("#fff") subject �c�[���o�[�� �}�E�X�I�[�o�[���̃����N�F
$STYLE['sb_tool_sepa_color'] = "#fff"; // ("#000") subject �c�[���o�[�� �Z�p���[�^�����F

$STYLE['sb_now_sort_color'] = "#afffaf";   // ("#1144aa") subject ���݂̃\�[�g�F

$STYLE['sb_thre_title_new_color'] = "#ff0"; // ("red") subject �V�K�X���^�C�g���̐F

$STYLE['sb_tool_newres_color'] = "#afffaf"; // ("#ff3300") subject �c�[���o�[�� �V�K���X���̐F
$STYLE['sb_newres_color'] = "#afffaf"; // ("#ff3300") subject �V�����X���̐F

// }}}
// {{{ �X�����e(read)

$STYLE['read_bgcolor'] = "#1f3f2f"; // ("#efefef") �X���b�h�\���̔w�i�F
$STYLE['read_background'] = ""; // ("") �X���b�h�\���̔w�i�摜
$STYLE['read_color'] = "#bae4a6"; // ("#000") �X���b�h�\���̃e�L�X�g�F

$STYLE['read_acolor'] = "#ffafaf"; // ("") �X���b�h�\�� �����N�F
$STYLE['read_acolor_v'] = "#afffaf"; // ("") �X���b�h�\�� �K��ς݃����N�F
$STYLE['read_acolor_h'] = "#ffffaf"; // ("#09c") �X���b�h�\�� �}�E�X�I�[�o�[���̃����N�F

$STYLE['read_newres_color'] = "#ffffaf"; // ("#ff3300")  �V�����X�Ԃ̐F

$STYLE['read_thread_title_color'] = "#fff"; // ("#f40") �X���b�h�^�C�g���F
$STYLE['read_name_color'] = "#d6e9ca"; // ("#1144aa") ���e�҂̖��O�̐F
$STYLE['read_mail_color'] = "#d6e9ca"; // ("") ���e�҂�mail�̐F ex)"#a00000"
$STYLE['read_mail_sage_color'] = "#d6e9ca"; // ("") sage�̎��̓��e�҂�mail�̐F ex)"#00b000"
$STYLE['read_ngword'] = "#666"; // ("#bbbbbb") NG���[�h�̐F

// }}}
// {{{ �������[�h

$SYTLE['live_b_width'] = "2px"; // ("1px") �������[�h�A�{�[�_�[����
$SYTLE['live_b_color'] = "#fff"; // ("#888") �������[�h�A�{�[�_�[�F
$SYTLE['live_b_style'] = "dashed"; // ("solid") �������[�h�A�{�[�_�[�X�^�C��

// }}}
// {{{ ���X�������݃t�H�[��

$STYLE['post_pop_size'] = "610,350"; // ("610,350") ���X�������݃|�b�v�A�b�v�E�B���h�E�̑傫���i��,�c�j
$STYLE['post_msg_rows'] = 10; // (10) ���X�������݃t�H�[���A���b�Z�[�W�t�B�[���h�̍s��
$STYLE['post_msg_cols'] = 70; // (70) ���X�������݃t�H�[���A���b�Z�[�W�t�B�[���h�̌���

// }}}
// {{{ ���X�|�b�v�A�b�v

$STYLE['respop_color'] = "#d6e9ca"; // ("#000") ���X�|�b�v�A�b�v�̃e�L�X�g�F
$STYLE['respop_bgcolor'] = "#1f3f2f"; // ("#ffffcc") ���X�|�b�v�A�b�v�̔w�i�F
$STYLE['respop_background'] = ""; // ("") ���X�|�b�v�A�b�v�̔w�i�摜
$STYLE['respop_b_width'] = "3px"; // ("1px") ���X�|�b�v�A�b�v�̃{�[�_�[��
$STYLE['respop_b_color'] = "#fff"; // ("black") ���X�|�b�v�A�b�v�̃{�[�_�[�F
$STYLE['respop_b_style'] = "double"; // ("solid") ���X�|�b�v�A�b�v�̃{�[�_�[�`��

$STYLE['info_pop_size'] = "600,380"; // ("600,380") ���|�b�v�A�b�v�E�B���h�E�̑傫���i��,�c�j

// }}}
// {{{ style/*_css.inc �Œ�`����Ă��Ȃ��ݒ�

$MYSTYLE['subject']['sb_td']['border-top'] = "1px dashed #fff";
$MYSTYLE['subject']['sb_td1']['border-top'] = "1px dashed #fff";

$MYSTYLE['base!']['#filterstart, .filtering']['background-color'] = "transparent";
$MYSTYLE['base!']['#filterstart, .filtering']['border-bottom'] = "3px #fff double";

$MYSTYLE['read']['#iframespace']['border'] = "2px #fff inset";
$MYSTYLE['read']['#closebox']['border'] = "2px #fff outset";
$MYSTYLE['read']['#closebox']['color'] = "#fff";
$MYSTYLE['read']['#closebox']['background-color'] = "#808080";
$MYSTYLE['subject']['#iframespace'] = $MYSTYLE['read']['#iframespace'];
$MYSTYLE['subject']['#closebox'] = $MYSTYLE['read']['#closebox'];

$MYSTYLE['info']['td.tdleft']['color'] = "#90F0C0";
$MYSTYLE['kanban']['td.tdleft']['color'] = "#1f3f2f";

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
