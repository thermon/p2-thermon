<?php
/**
 * rep2expack - �Ȉ�RSS���[�_�i<description>�܂���<content:encoded>�̓��e��\���j
 */

// {{{ p2��{�ݒ�ǂݍ���&�F��

require_once './conf/conf.inc.php';
require_once P2EX_LIB_DIR . '/rss/parser.inc.php';

$_login->authorize();

// }}}

if ($_conf['view_forced_by_query']) {
    output_add_rewrite_var('b', $_conf['b']);
}

//============================================================
// �ϐ��̏�����
//============================================================

$channel = array();
$items = array();

$num = trim($_REQUEST['num']);
$xml = trim($_REQUEST['xml']);
$atom = empty($_REQUEST['atom']) ? 0 : 1;
$site_en = trim($_REQUEST['site_en']);

if (is_numeric($num)) {
    $num = (int)$num;
}
$xml_en = rawurlencode($xml);
$xml_ht = htmlspecialchars($xml, ENT_QUOTES, 'Shift_JIS', false);


//============================================================
// RSS�ǂݍ���
//============================================================

if ($xml) {
    $rss = p2GetRSS($xml, $atom);
    if ($rss instanceof XML_RSS) {
        clearstatcache();
        $rss_parse_success = true;
        $xml_path = rss_get_save_path($xml);
        $mtime    = filemtime($xml_path);
        $channel  = $rss->getChannelInfo();
        $items    = $rss->getItems();

        $fp = fopen($xml_path, 'rb');
        $xmldec = fgets($fp, 1024);
        fclose($fp);
        if (preg_match('/^<\\?xml version="1.0" encoding="((?i:iso)-8859-(?:[1-9]|1[0-5]))" ?\\?>/', $xmldec, $matches)) {
            $encoding = $matches[1];
        } else {
            $encoding = 'UTF-8,CP51932,CP932,JIS';
        }
        mb_convert_variables('CP932', $encoding, $channel, $items);
    } else {
        $rss_parse_success = false;
    }
} else {
    $rss_parse_success = false;
}


//===================================================================
// HTML�\���p�ϐ��̐ݒ�
//===================================================================

//�^�C�g��
if (isset($num)) {
    $title = htmlspecialchars($items[$num]['title'], ENT_QUOTES, 'Shift_JIS', false);
} else {
    $title = htmlspecialchars($channel['title'], ENT_QUOTES, 'Shift_JIS', false);
}


//============================================================
// HTML�v�����g
//============================================================

if ($_conf['ktai']) {
    if (!$_conf['expack.rss.check_interval']) {
        // �L���b�V�������Ȃ�
        P2Util::header_nocache();
    } else {
        // �X�V�`�F�b�N�Ԋu��1/3�����L���b�V��������i�[��or�Q�[�g�E�F�C�̎����ˑ��j
        header(sprintf('Cache-Control: max-age=%d', $_conf['expack.rss.check_interval'] * 60 / 3));
    }
}
echo $_conf['doctype'];
include P2EX_LIB_DIR . '/rss/' . ($_conf['ktai'] ? 'read_k' : 'read') . '.inc.php';

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
