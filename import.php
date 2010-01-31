<?php
/**
 * rep2 - dat���C���|�[�g����
 */

require_once './conf/conf.inc.php';

$_login->authorize(); // ���[�U�F��

// �ϐ� =============
$link_ht = '';
$max_size = 1000000;

$default_host = !empty($_REQUEST['host']) ? htmlspecialchars($_REQUEST['host'], ENT_QUOTES) : '_.2ch.net';
$default_bbs = !empty($_REQUEST['bbs']) ? htmlspecialchars($_REQUEST['bbs'], ENT_QUOTES) : '';
$default_key = !empty($_REQUEST['key']) ? htmlspecialchars($_REQUEST['key'], ENT_QUOTES) : 'auto';

//================================================================
// �A�b�v���[�h���ꂽ�t�@�C���̏���
//================================================================
if (!empty($_POST['host']) && !empty($_POST['bbs']) && !empty($_POST['key']) && isset($_FILES['dat_file'])) {
    $is_error = FALSE;

    // �A�b�v���[�h�����̂Ƃ�
    if ($_FILES['dat_file']['error'] == UPLOAD_ERR_OK) {
        // �l�̌���
        if ($_POST['MAX_FILE_SIZE'] != $max_size) {
            $is_error = TRUE;
            $_info_msg_ht .= '<p>Warning: �t�H�[���� MAX_FILE_SIZE �̒l�������񂳂�Ă��܂��B</p>';
        }
        if (!preg_match('/^[1-9][0-9]+\.dat$/', $_FILES['dat_file']['name'])) {
            $is_error = TRUE;
            $_info_msg_ht .= '<p>Error: �A�b�v���[�h���ꂽdat�̃t�@�C�������ςł��B</p>';
        }
        $host = $_POST['host'];
        $bbs  = $_POST['bbs'];
        //if ($_POST['key'] == 'auto') {
            $key = preg_replace('/\.(dat|html?)$/', '', $_FILES['dat_file']['name']);
        /*} elseif (preg_match('/^[1-9][0-9]+$/', $_POST['key'])) {
            $key = $_POST['key'];
            if ($key != preg_replace('/\.(dat|html?)$/', '', $_FILES['dat_file']['name'])) {
                $is_error = TRUE;
                $_info_msg_ht .= '<p>Error: �A�b�v���[�h���ꂽdat�̃t�@�C�����ƃX���b�h�L�[���}�b�`���܂���B</p>';
            }
        } else {
            $is_error = TRUE;
            $_info_msg_ht .= '<p>Error: �X���b�h�L�[�̎w�肪�ςł��B</p>';
        }*/
        $dat_path = P2Util::datDirOfHostBbs($host, $bbs) . $key . '.dat';

    // �A�b�v���[�h���s�̂Ƃ�
    } else {
        $is_error = TRUE;
        // �G���[���b�Z�[�W�� http://jp.php.net/manual/ja/features.file-upload.errors.php ����R�s�y
        switch ($_FILES['dat_file']['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $_info_msg_ht .= '<p>Error: �A�b�v���[�h���ꂽ�t�@�C���́Aphp.ini �� upload_max_filesize �f�B���N�e�B�u�̒l�𒴂��Ă��܂��B</p>';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $_info_msg_ht .= '<p>Error: �A�b�v���[�h���ꂽ�t�@�C���́AHTML�t�H�[���Ŏw�肳�ꂽ MAX_FILE_SIZE �𒴂��Ă��܂��B</p>';
                break;
            case UPLOAD_ERR_PARTIAL:
                $_info_msg_ht .= '<p>Error: �A�b�v���[�h���ꂽ�t�@�C���͈ꕔ�݂̂����A�b�v���[�h����Ă��܂���B</p>';
                break;
            case UPLOAD_ERR_NO_FILE:
                $_info_msg_ht .= '<p>Error: �t�@�C���̓A�b�v���[�h����܂���ł����B</p>';
                break;
            default:
                $_info_msg_ht .= '<p>Error: �����s���̃G���[�B</p>';
                break;
        }
    }

    // �t�@�C����ۑ����A�����N���쐬
    if (!$is_error) {
        move_uploaded_file($_FILES['dat_file']['tmp_name'], $dat_path);
        $ttitle = '???';
        if ($datlines = FileCtl::file_read_lines($dat_path, FILE_IGNORE_NEW_LINES)) {
            if (strpos($datlines[0], '<>') !== false) {
                $one = explode('<>', $datlines[0]);
            } else {
                $one = explode(',', $datlines[0]);
            }
            $ttitle = array_pop($one);
            unset($datlines, $one);
        }
        $read_url = sprintf('%s?host=%s&bbs=%s&key=%d&offline=true', $_conf['read_php'], rawurlencode($host), rawurlencode($bbs), $key);
        $link_ht = sprintf('<p><a href="%s" target="read"><b>%s</b> ���������ǂށB</a></p>', $read_url, $ttitle);
    }

} elseif (!empty($_POST['host']) || !empty($_POST['bbs']) || !empty($_POST['key']) || isset($_FILES['dat_file'])) {
    $_info_msg_ht .= '<p>Error: ��URL���w�肳��Ă��Ȃ����Adat���I������Ă��܂���B</p>';
}

//================================================================
// �w�b�_
//================================================================
P2Util::header_nocache();
echo $_conf['doctype'];
echo <<<EOP
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    {$_conf['extra_headers_ht']}
    <title>p2 - dat�̃C���|�[�g</title>
    <link rel="stylesheet" type="text/css" href="css.php?css=style&amp;skin={$skin_en}">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
</head>
<body>\n
EOP;

echo $_info_msg_ht;
$_info_msg_ht = '';

//================================================================
// ���C������HTML�\��
//================================================================
echo <<<EOP
<p>dat�̃C���|�[�g</p>
<form method="post" enctype="multipart/form-data" action="{$_SERVER['SCRIPT_NAME']}">
    <input type="hidden" name="MAX_FILE_SIZE" value="{$max_size}">
    ��URL: http://<input type="text" size="20" value="{$default_host}" name="host">/<input type="text" size="10"  value="{$default_bbs}" name="bbs">/
    <input type="hidden" value="{$default_key}" name="key">(�X���b�h�L�[�̓t�@�C�������玩������)<br>
    dat��I��: <input type="file" size="50" name="dat_file"><br>
    <input type="submit" value="���M">
</form>
EOP;
if ($link_ht) {
    echo '<hr><p>�A�b�v���[�h�����I</p>';
    echo $link_ht;
} else {
    echo <<<EOP
<hr>
<div>
�g����
<ul>
    <li>
        ��URL����2�Ԗڂ̍��ڂɔ��i��:software�j����͂��Adat��I��ł���<br>
        ���M�{�^���������ƁAdat���A�b�v���[�h����p2�œǂނ��Ƃ��ł��܂��B
    </li>
    <li>
        2�����˂��dat���C���|�[�g����Ƃ��A1�Ԗڂ̍��ځi�z�X�g���j�� _.2ch.net �̂܂܂�OK�ł��B<br>
        ���̌f���ł͐������z�X�g������͂��Ă��������B<br>
        ������΂̔ł̓z�X�g���ɑ����Ĕ��p�X���b�V���ƃJ�e�S�������K�v�ł��B�i��:jbbs.livedoor.jp/computer�j
    </li>
</ul>
</div>
EOP;
}

//================================================================
// �t�b�^HTML�\��
//================================================================
echo '</body></html>';

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
