<?php
/**
 * rep2 - �������ʂ̃y�[�W�J�ڗp�ɕϐ���ݒ肷��
 */

// �����N�G��
$_conf['filter_q'] = '?host=' . $aThread->host . $bbs_q . $key_q . $offline_q;
$_conf['filter_q'] .= '&amp;word=' . rawurlencode($word);
foreach ($res_filter as $_key => $_value) {
    $_conf['filter_q'] .= "&amp;{$_key}=" . rawurldecode($_value);
}
$_conf['filter_q'] .= '&amp;ls=all&amp;page=';

$prev_st = '�O*';
$next_st = '��*';

if ($filter_range['page'] > 1) {
    $read_navi_previous_url = $_conf['read_php'] . $_conf['filter_q'] . ($filter_range['page'] - 1) . $_conf['k_at_a'];
    $read_navi_previous = "<a href=\"{$read_navi_previous_url}\">{$prev_st}</a>";
    $read_navi_previous_btm = "<a href=\"{$read_navi_previous_url}\"{$_conf['k_accesskey_at']['prev']}>{$_conf['k_accesskey_st']['prev']}{$prev_st}</a>";
} else {
    $read_navi_previous = '';
    $read_navi_previous_btm = '';
}

if ($filter_range['to'] < $filter_hits) {
    $read_navi_next_url = $_conf['read_php'] . $_conf['filter_q'] . ($filter_range['page'] + 1) . $_conf['k_at_a'];
    $read_navi_next = "<a href=\"{$read_navi_next_url}\"{$_conf['k_accesskey_at']['next']}>{$_conf['k_accesskey_st']['next']}{$next_st}</a>";
    $read_navi_next_btm = "<a href=\"{$read_navi_next_url}\"{$_conf['k_accesskey_at']['next']}>{$_conf['k_accesskey_st']['next']}{$next_st}</a>";
} else {
    $read_navi_next = '';
    $read_navi_next_btm = '';
}

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
