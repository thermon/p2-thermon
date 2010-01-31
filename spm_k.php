<?php
/**
 * rep2 - ����@�\���s�X�N���v�g�i�g�сj
 */

require_once './conf/conf.inc.php';
require_once P2_LIB_DIR . '/spm_k.inc.php';

$_login->authorize(); // ���[�U�F��

//=================================================
// ���ꃊ�N�G�X�g�����s
//=================================================
if (isset($_GET['ktool_name']) && isset($_GET['ktool_value'])) {
    $ktv = (int)$_GET['ktool_value'];
    $base_dir_s = P2_BASE_DIR . DIRECTORY_SEPARATOR;
    switch ($_GET['ktool_name']) {
        case 'goto':
            $_REQUEST['ls'] = $_GET['ls'] = $ktv . '-' . ($ktv + $_conf['mobile.rnum_range']);
            include $base_dir_s . 'read.php';
            exit;
        case 'res':
        case 'res_quote':
            $_GET['resnum'] = $ktv;
            $_GET['inyou'] = ($_GET['ktool_name'] == 'res') ? -1 : 1;
            include $base_dir_s . 'post_form.php';
            exit;
        case 'copy_quote':
            $_GET['inyou'] = 1;
        case 'copy':
            $_GET['copy'] = $ktv;
            include $base_dir_s . 'read_copy_k.php';
            exit;
        case 'aas_rotate':
            $_GET['rotate'] = 1;
        case 'aas':
            $_GET['resnum'] = $ktv;
            include $base_dir_s . 'aas.php';
            exit;
        case 'aborn_res':
        case 'aborn_name':
        case 'aborn_mail':
        case 'aborn_id':
        case 'aborn_msg':
        case 'ng_name':
        case 'ng_mail':
        case 'ng_id':
        case 'ng_msg':
            $_GET['resnum'] = $ktv;
            $_GET['popup'] = 1;
            $_GET['mode'] = $_GET['ktool_name'];
            include $base_dir_s . 'info_sp.php';
            exit;
        default:
            p2die('�s���ȃR�}���h');
    }
}

//=================================================
// �X���̎w��
//=================================================
kspDetectThread(); // global $host, $bbs, $key, $ls
$aThread = new Thread();
// host�𕪉�����idx�t�@�C���̃p�X�����߂�
if (!isset($aThread->keyidx)) {
    $aThread->setThreadPathInfo($host, $bbs, $key);
}
$aThread->itaj = P2Util::getItaName($host, $bbs);
if (!$aThread->itaj) { $aThread->itaj = $aThread->bbs; }
// idx�t�@�C��������Γǂݍ���
if ($lines = FileCtl::file_read_lines($aThread->keyidx, FILE_IGNORE_NEW_LINES)) {
    $idx_data = explode('<>', $lines[0]);
} else {
    p2die('�w�肳�ꂽ�X���b�h��idx������܂���B');
}
$aThread->getThreadInfoFromIdx();

//=================================================
// �\���p�ϐ���ݒ�
//=================================================
$ptitle_ht = $aThread->ttitle_hd;
$thread_url = "{$_conf['read_php']}?host={$host}&amp;bbs={$bbs}&amp;key={$key}{$_conf['k_at_a']}";
$params = array();
if (!empty($_GET['from_read_new'])) {
    $params['from_read_new'] = '1';
}
$default = (!empty($_GET['spm_default'])) ? intval($_GET['spm_default']) : '';

//=================================================
// �\��
//=================================================
P2Util::header_nocache();
echo $_conf['doctype'];
echo <<<EOHEADER
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
{$_conf['extra_headers_ht']}
<title>{$ptitle_ht}</title>
</head>\n
EOHEADER;

echo "<body{$_conf['k_colors']}>";

echo $_info_msg_ht;
$_info_msg_ht = '';

echo "<p><a href=\"{$thread_url}\">{$ptitle_ht}</a></p>";
echo '<hr>';
echo kspform($aThread, $default, $params);
echo '<hr>';
echo '<p>';
if (!empty($_GET['from_read_new'])) {
    echo "<a href=\"{$_conf['read_new_k_php']}?cview=1{$_conf['k_at_a']}\">�܂Ƃߓǂ݂ɖ߂�</a><br>";
}
echo "<a href=\"{$thread_url}\">�ڂɖ߂�</a>";
echo '</p>';
echo '</body></html>';
exit;

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
