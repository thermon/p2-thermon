<?php
/*
    �t�@�C�����u���E�U�ŕҏW����
*/

require_once './conf/conf.inc.php';

$_login->authorize(); // ���[�U�F��

// �ϐ� ==================================
$filename   = isset($_REQUEST['file'])       ? $_REQUEST['file']       : null;
$modori_url = isset($_REQUEST['modori_url']) ? $_REQUEST['modori_url'] : null;
$encode     = isset($_REQUEST['encode'])     ? $_REQUEST['encode']     : null;

$rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : ($_conf['ktai'] ? 5 : 36);
$cols = isset($_REQUEST['cols']) ? intval($_REQUEST['cols']) : ($_conf['ktai'] ? 0 : 128);

$csrfid = P2Util::getCsrfId(__FILE__ . $filename);

//=========================================================
// �O����
//=========================================================

// �s���|�X�g�`�F�b�N
if (isset($_POST['filecont'])) {
    if (!isset($_POST['csrfid']) || $_POST['csrfid'] != $csrfid) {
        p2die('�s���ȃ|�X�g�ł�');
    } else {
        $filecont = $_POST['filecont'];
    }
}

// �������߂�t�@�C�������肷��
$writable_files = array(
    'p2_aborn_res.txt'  => '���ځ[�񃌃X',
);

if (!array_key_exists($filename, $writable_files)) {
    $files_st = implode(', ', array_keys($writable_files));
    p2die(basename($_SERVER['SCRIPT_NAME']) . " �搶�̏������߂�t�@�C���́A{$files_st}�����I");
}

$path = $_conf['pref_dir'] . DIRECTORY_SEPARATOR . $filename;

//=========================================================
// ���C��
//=========================================================
if (isset($filecont)) {
    if (setFile($path, $filecont, $encode)) {
        $_info_msg_ht .= "saved, OK.";
    }
}

editFile($path, $encode, $writable_files[$filename]);

exit;

//=========================================================
// �֐�
//=========================================================
// {{{ setFile()

/**
 * �t�@�C���ɓ��e���Z�b�g����֐�
 */
function setFile($path, $cont, $encode)
{
    if ($path == '') {
        p2die('path ���w�肳��Ă��܂���');
    }

    if ($encode == "EUC-JP") {
        $cont = mb_convert_encoding($cont, 'CP932', 'CP51932');
    }
    // ��������
    $fp = @fopen($path, 'wb') or p2die("cannot write. ({$path})");
    @flock($fp, LOCK_EX);
    fputs($fp, $cont);
    @flock($fp, LOCK_UN);
    fclose($fp);
    return true;
}

// }}}
// {{{ editFile()

/**
 * �t�@�C�����e��ǂݍ���ŕҏW����֐�
 */
function editFile($path, $encode, $title)
{
    global $_conf, $modori_url, $_info_msg_ht, $rows, $cols, $csrfid;

    if ($path == '') {
        p2die('path ���w�肳��Ă��܂���');
    }

    $filename = basename($path);
    $ptitle = 'Edit: ' . htmlspecialchars($title, ENT_QUOTES, 'Shift_JIS')
            . ' (' . $filename . ')';

    //�t�@�C�����e�ǂݍ���
    FileCtl::make_datafile($path) or p2die("cannot make file. ({$path})");
    $cont = file_get_contents($path);

    if ($encode == "EUC-JP") {
        $cont = mb_convert_encoding($cont, 'CP932', 'CP51932');
    }

    $cont_area = htmlspecialchars($cont, ENT_QUOTES);

    if ($modori_url) {
        $modori_url_ht = "<p><a href=\"{$modori_url}\">Back</a></p>\n";
    }

    $rows_at = ($rows > 0) ? sprintf(' rows="%d"', $rows) : '';
    $cols_at = ($cols > 0) ? sprintf(' cols="%d"', $cols) : '';

    // �v�����g
    echo $_conf['doctype'];
    echo <<<EOHEADER
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    {$_conf['extra_headers_ht']}
    <title>{$ptitle}</title>
</head>
<body onload="top.document.title=self.document.title;">
EOHEADER;

    echo $modori_url_ht;
    echo $ptitle;
    echo <<<EOFORM
<form action="{$_SERVER['SCRIPT_NAME']}" method="post" accept-charset="{$_conf['accept_charset']}">
    <input type="hidden" name="file" value="{$filename}">
    <input type="hidden" name="modori_url" value="{$modori_url}">
    <input type="hidden" name="encode" value="{$encode}">
    <input type="hidden" name="rows" value="{$rows}">
    <input type="hidden" name="cols" value="{$cols}">
    <input type="hidden" name="csrfid" value="{$csrfid}">
    <input type="submit" name="submit" value="Save">
    {$_info_msg_ht}<br>
    <textarea style="font-size:9pt;" id="filecont" name="filecont" wrap="off"{$rows_at}{$cols_at}>{$cont_area}</textarea>
    {$_conf['detect_hint_input_ht']}{$_conf['k_input_ht']}
</form>
EOFORM;

    echo '</body></html>';

    return true;
}

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
