<?php
/**
 * rep2 - ���ځ[�񃏁[�h�ҏW�C���^�t�F�[�X
 */

require_once './conf/conf.inc.php';

$_login->authorize(); // ���[�U�F��

$filename = isset($_REQUEST['file']) ? $_REQUEST['file'] : '';

$csrfid = P2Util::getCsrfId(__FILE__ . $filename);
if (!empty($_POST['submit_save']) || !empty($_POST['submit_default'])) {
    if (!isset($_POST['csrfid']) or $_POST['csrfid'] != $csrfid) {
        p2die('�s���ȃ|�X�g�ł�');
    }
}

$writable_files = array(
    'p2_aborn_thread.txt' => '���ځ[��X���b�h�^�C�g��',
    'p2_aborn_name.txt'   => '���ځ[��l�[��',
    'p2_aborn_mail.txt'   => '���ځ[�񃁁[��',
    'p2_aborn_msg.txt'    => '���ځ[�񃁃b�Z�[�W',
    'p2_aborn_id.txt'     => '���ځ[��ID',
    'p2_ng_name.txt'      => 'NG�l�[��',
    'p2_ng_mail.txt'      => 'NG���[��',
    'p2_ng_msg.txt'       => 'NG���b�Z�[�W',
    'p2_ng_id.txt'        => 'NGID',
);

if (!array_key_exists($filename, $writable_files)) {
    $files_st = implode(', ', array_keys($writable_files));
    p2die(basename($_SERVER['SCRIPT_NAME']) . " �搶�̏������߂�t�@�C���́A{$files_st} �����I");
}

$path = $_conf['pref_dir'] . DIRECTORY_SEPARATOR . $filename;

//=====================================================================
// �O����
//=====================================================================

// {{{ ���ۑ��{�^����������Ă�����A�ݒ��ۑ�

if (!empty($_POST['submit_save'])) {

    $newdata = '';
    foreach ($_POST['nga'] as $na_info) {
        $a_word = strtr(trim($na_info['word'], "\t\r\n"), "\t\r\n", "   ");
        $a_bbs = strtr(trim($na_info['bbs'], "\t\r\n"), "\t\r\n", "   ");
        $a_tt = strtr(trim($na_info['tt'], "\t\r\n"), "\t\r\n", "   ");
        $a_time = strtr(trim($na_info['ht']), "\t\r\n", "   ");
        if ($a_time === '') {
            $a_time = '--';
        }
        $a_hits = $na_info['hn'];
        if ($a_word === '') {
            continue;
        }
        if (!empty($na_info['re'])) {
            $a_mode = !empty($na_info['ic']) ? '<regex:i>' : '<regex>';
        } elseif (!empty($na_info['ic'])) {
            $a_mode = '<i>';
        } else {
            $a_mode = '';
        }
        if (strlen($a_bbs) > 0) {
            $a_mode .= '<bbs>' . $a_bbs . '</bbs>';
        }
        if (strlen($a_tt) > 0) {
            $a_mode .= '<title>' . $a_tt . '</title>';
        }
        $newdata .= $a_mode . $a_word . "\t" . $a_time . "\t" . $a_hits . "\n";
    }
    if (FileCtl::file_write_contents($path, $newdata) !== FALSE) {
        $_info_msg_ht .= "<p>���ݒ���X�V�ۑ����܂���</p>";
    } else {
        $_info_msg_ht .= "<p>�~�ݒ���X�V�ۑ��ł��܂���ł���</p>";
    }

// }}}
// {{{ ���f�t�H���g�ɖ߂��{�^����������Ă�����

} elseif (!empty($_POST['submit_default'])) {
    if (@unlink($path)) {
        $_info_msg_ht .= "<p>�����X�g����ɂ��܂���</p>";
    } else {
        $_info_msg_ht .= "<p>�~���X�g����ɂł��܂���ł���</p>";
    }
}

// }}}
// {{{ ���X�g�ǂݍ���

$formdata = array();
if ($lines = FileCtl::file_read_lines($path, FILE_IGNORE_NEW_LINES)) {
    $i = 0;
    foreach ($lines as $l) {
        $lar = explode("\t", $l);
        if (strlen($lar[0]) == 0) {
            continue;
        }
        $ar = array(
            'cond' => $lar[0], // ��������
            'word' => $lar[0], // �Ώە�����
            'ht' => isset($lar[1]) ? $lar[1] : '--',    // �Ō��HIT��������
            'hn' => isset($lar[2]) ? (int)$lar[2] : 0,  // HIT��
            're' => '', // ���K�\��
            'ic' => '', // �啶���������𖳎�
            'bbs' => '', // ��
            'tt' => '', // �^�C�g��
        );
        // ����
        if (preg_match('!<bbs>(.+?)</bbs>!', $ar['word'], $matches)) {
            $ar['bbs'] = $matches[1];
        }
        $ar['word'] = preg_replace('!<bbs>(.*)</bbs>!', '', $ar['word']);
        // �^�C�g������
        if (preg_match('!<title>(.+?)</title>!', $ar['word'], $matches)) {
            $ar['tt'] = $matches[1];
        }
        $ar['word'] = preg_replace('!<title>(.*)</title>!', '', $ar['word']);
        // ���K�\��
        if (preg_match('/^<(mb_ereg|preg_match|regex)(:[imsxeADSUXu]+)?>(.*)$/', $ar['word'], $m)) {
            $ar['word'] = $m[3];
            $ar['re'] = ' checked';
            // �啶���������𖳎�
            if ($m[2] && strpos($m[2], 'i') !== false) {
                $ar['ic'] = ' checked';
            }
        // �啶���������𖳎�
        } elseif (preg_match('/^<i>(.*)$/', $ar['word'], $m)) {
            $ar['word'] = $m[1];
            $ar['ic'] = ' checked';
        }
        if (strlen($ar['word']) == 0) {
            continue;
        }
        $formdata[$i++] = $ar;
    }
}

// }}}

//=====================================================================
// �v�����g�ݒ�
//=====================================================================
$ptitle_top = sprintf('���ځ[��/NG���[�h�ҏW &gt; <a href="%s?file=%s">%s</a>',
    $_SERVER['SCRIPT_NAME'], rawurlencode($filename), $writable_files[$filename]);
$ptitle = strip_tags($ptitle_top);

//=====================================================================
// �v�����g
//=====================================================================
// �w�b�_HTML���v�����g
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
    <title>{$ptitle}</title>\n
EOP;

if (!$_conf['ktai']) {
    echo <<<EOP
    <link rel="stylesheet" type="text/css" href="css.php?css=style&amp;skin={$skin_en}">
    <link rel="stylesheet" type="text/css" href="css.php?css=edit_conf_user&amp;skin={$skin_en}">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
    <script type="text/javascript" src="js/basic.js?{$_conf['p2_version_id']}"></script>\n
EOP;
}

$body_at = ($_conf['ktai']) ? $_conf['k_colors'] : ' onload="top.document.title=self.document.title;"';
echo <<<EOP
</head>
<body{$body_at}>\n
EOP;

// PC�p�\��
if (!$_conf['ktai']) {
    echo <<<EOP
<p id="pan_menu"><a href="editpref.php">�ݒ�Ǘ�</a> &gt; {$ptitle_top}</p>\n
EOP;
} else {
    echo $filename . "<br>";
}

// PC�p�\��
if (!$_conf['ktai']) {
    $htm['form_submit'] = <<<EOP
        <tr class="group">
            <td colspan="6" align="center">
                <input type="submit" name="submit_save" value="�ύX��ۑ�����">
                <input type="submit" name="submit_default" value="���X�g����ɂ���" onclick="if (!window.confirm('���X�g����ɂ��Ă���낵���ł����H�i��蒼���͂ł��܂���j')) {return false;}"><br>
            </td>
        </tr>\n
EOP;
// �g�їp�\��
} else {
    $htm['form_submit'] = <<<EOP
<input type="submit" name="submit_save" value="�ύX��ۑ�����"><br>\n
EOP;
}

// ��񃁃b�Z�[�W�\��
if (!empty($_info_msg_ht)) {
    echo $_info_msg_ht;
    $_info_msg_ht = "";
}

if ($filename == 'p2_aborn_thread.txt') {
    $usage_ttitle = '<li>�X���^�C: �u���ځ[��X���b�h�^�C�g���v�ł͕s�g�p</li>';
} else {
    $usage_ttitle = '<li>�X���^�C: �X���b�h�^�C�g�� (������v, ��ɑ啶���������𖳎�)</li>';
}

$usage = <<<EOP
<ul>
<li>���[�h: NG/���ځ[�񃏁[�h (��ɂ���Ɠo�^����)</li>
<li>i: �啶���������𖳎�</li>
<li>re: ���K�\��</li>
<li>��: newsplus,software �� (���S��v, �J���}��؂�)</li>
{$usage_ttitle}
</ul>
EOP;
if ($_conf['ktai']) {
    $usage = mb_convert_kana($usage, 'k');
}
echo <<<EOP
{$usage}
<form method="POST" action="{$_SERVER['SCRIPT_NAME']}" target="_self" accept-charset="{$_conf['accept_charset']}">
    <input type="hidden" name="file" value="{$filename}">
    <input type="hidden" name="csrfid" value="{$csrfid}">\n
EOP;

// PC�p�\���itable�j
if (!$_conf['ktai']) {
    echo <<<EOP
    <table class="edit_conf_user" cellspacing="0">
        <tr>
            <td align="center">���[�h</td>
            <td align="center">i</td>
            <td align="center">re</td>
            <td align="center">��</td>
            <td align="center">�X���^�C</td>
            <td align="center">�ŏI�q�b�g�����Ɖ�</td>
        </tr>
        <tr class="group">
            <td colspan="6">�V�K�o�^</td>
        </tr>\n
EOP;
    $row_format = <<<EOP
        <tr>
            <td><input type="text" size="35" name="nga[%1\$d][word]" value="%2\$s"></td>
            <td><input type="checkbox" name="nga[%1\$d][ic]" value="1"%3\$s></td>
            <td><input type="checkbox" name="nga[%1\$d][re]" value="1"%4\$s></td>
            <td><input type="text" size="10" name="nga[%1\$d][bbs]" value="%7\$s"></td>
            <td><input type="text" size="15" name="nga[%1\$d][tt]" value="%8\$s"></td>
            <td align="right">
                <input type="hidden" name="nga[%1\$d][ht]" value="%5\$s">%5\$s
                <input type="hidden" name="nga[%1\$d][hn]" value="%6\$d">(%6\$d)
            </td>
        </tr>\n
EOP;
// �g�їp�\��
} else {
    echo "�V�K�o�^<br>\n";
    if ($_conf['iphone']) {
        $row_format = <<<EOP
<fieldset>
<input type="text" name="nga[%1\$d][word]" value="%2\$s"><br>
��:<input type="text" name="nga[%1\$d][bbs]" value="%7\$s"><br>
��:<input type="text" name="nga[%1\$d][tt]" value="%8\$s"><br>
<input type="checkbox" name="nga[%1\$d][ic]" value="1"%3\$s>i
<input type="checkbox" name="nga[%1\$d][re]" value="1"%4\$s>re
<input type="hidden" name="nga[%1\$d][ht]" value="%5\$s">
<input type="hidden" name="nga[%1\$d][hn]" value="%6\$d">(%6\$d)
</fieldset>\n
EOP;
    } else {
        $row_format = <<<EOP
<input type="text" name="nga[%1\$d][word]" value="%2\$s"><br>
��:<input type="text" size="5" name="nga[%1\$d][bbs]" value="%7\$s">
����:<input type="text" size="5" name="nga[%1\$d][tt]" value="%8\$s"><br>
<input type="checkbox" name="nga[%1\$d][ic]" value="1"%3\$s>i
<input type="checkbox" name="nga[%1\$d][re]" value="1"%4\$s>re
<input type="hidden" name="nga[%1\$d][ht]" value="%5\$s">
<input type="hidden" name="nga[%1\$d][hn]" value="%6\$d">(%6\$d)<hr>\n
EOP;
    }
}

printf($row_format, -1, '', '', '', '--', 0, '', '');

echo $htm['form_submit'];

if (!empty($formdata)) {
    if ($_conf['ktai'] && !$_conf['iphone']) {
        echo "<hr>\n";
    }
    foreach ($formdata as $k => $v) {
        printf($row_format,
            $k,
            htmlspecialchars($v['word'], ENT_QUOTES),
            $v['ic'],
            $v['re'],
            htmlspecialchars($v['ht'], ENT_QUOTES),
            $v['hn'],
            htmlspecialchars($v['bbs'], ENT_QUOTES),
            htmlspecialchars($v['tt'], ENT_QUOTES)
        );
    }
    echo $htm['form_submit'];
}

// PC�Ȃ�
if (!$_conf['ktai']) {
    echo '</table>'."\n";
}

echo <<<EOP
{$_conf['detect_hint_input_ht']}{$_conf['k_input_ht']}
</form>\n
EOP;


// �g�тȂ�
if ($_conf['ktai']) {
    echo <<<EOP
<hr>
<div class="center">
<a href="editpref.php{$_conf['k_at_q']}"{$_conf['k_accesskey_at']['up']}>{$_conf['k_accesskey_st']['up']}�ݒ�ҏW</a>
{$_conf['k_to_index_ht']}
</div>
EOP;
}

echo '</body></html>';

// �����܂�
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
