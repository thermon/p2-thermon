<?php
/**
 * rep2 - SPM���ځ[��
 */

require_once './conf/conf.inc.php';

$_login->authorize(); // ���[�U�F��

//=====================================================
// �ϐ��̐ݒ�
//=====================================================
$host = isset($_GET['host']) ? $_GET['host'] : null;
$bbs  = isset($_GET['bbs']) ? $_GET['bbs'] : null;
$key  = isset($_GET['key']) ? $_GET['key'] : null;
$rc   = isset($_GET['rescount']) ? $_GET['rescount'] : null;
$ttitle_en = isset($_GET['ttitle_en']) ? $_GET['ttitle_en'] : null;
$resnum = isset($_GET['resnum']) ? $_GET['resnum'] : null;
$popup  = isset($_GET['popup']) ? $_GET['popup'] : null;
$mode   = isset($_GET['mode']) ? $_GET['mode'] : null;

if (isset($_GET['aborn_str_en'])) {
    $aborn_str_en = $_GET['aborn_str_en'];
    $aborn_str = UrlSafeBase64::decode($aborn_str_en);
} elseif (isset($_GET['aborn_str'])) {
    $aborn_str = $_GET['aborn_str'];
}
if (isset($_GET['aborn_id'])) {
    $aborn_id = $_GET['aborn_id'];
}

$itaj = P2Util::getItaName($host, $bbs);
if (!$itaj) {
    $itaj = $bbs;
}

$ttitle_name = is_string($ttitle_en) ? UrlSafeBase64::decode($ttitle_en) : '';

$thread_url = "{$_conf['read_php']}?host={$host}&amp;bbs={$bbs}&amp;key={$key}{$_conf['k_at_a']}";

if (!$_conf['ktai']) {
    $target_edit_at = ' target="editfile"';
    $target_read_at = ' target="read"';
    $target_sb_at = ' target="sbject"';
} else {
    $target_edit_at = '';
    $target_read_at = '';
    $target_sb_at = '';
}


//=====================================================
// �f�[�^�t�@�C���̓ǂݏ���
//=====================================================
if (preg_match('/^(aborn|ng)_/', $mode)) {
    $path = $_conf['pref_dir'] . '/p2_' . $mode . '.txt';
}

if ($popup == 1 || $_conf['expack.spm.ngaborn_confirm'] == 0) {
    $_GET['popup'] = 2;
    $aThread = new ThreadRead;
    $aThread->setThreadPathInfo($host, $bbs, $key);
    $aThread->readDat($aThread->keydat);
    $resar = $aThread->explodeDatLine($aThread->datlines[$resnum-1]);
    $resar = array_map('trim', $resar);
    $resar = array_map('strip_tags', $resar);
    if (preg_match('/ID: ?([^ ]+?)(?= |$)/', $resar[2], $idar)) {
        $aborn_id = $idar[1];
    } else {
        $aborn_id = '';
    }
    if ($_conf['expack.spm.ngaborn_confirm'] == 0 && !isset($aborn_str)) {
        if ($mode == 'aborn_res') {
            $aborn_str = $host . '/' . $bbs . '/' . $key . '/' . $resnum;
        } elseif (strpos($mode, '_name') !== false) {
            $aborn_str = $resar[0];
        } elseif (strpos($mode, '_mail') !== false) {
            $aborn_str = $resar[1];
        } elseif (strpos($mode, '_id') !== false) {
            $aborn_str = $aborn_id;
        } elseif (strpos($mode, '_msg') !== false) {
            $popup = 1;
        }
    }
    if (!is_string($ttitle_en)) {
        $onear = $aThread->explodeDatLine($aThread->datlines[0]);
        $_GET['ttitle_en'] = $ttitle_en = UrlSafeBase64::encode($ttitle_name = $onear[4]);
    }
}

if ($popup == 2) {
    // ���ځ[��ENG���[�h�o�^
    if (preg_match('/^(aborn|ng)_/', $mode) && ($aborn_str = trim($aborn_str)) !== '') {
        if (file_exists($path) && ($data = FileCtl::file_read_lines($path))) {
            $data = array_map('trim', $data);
            $data = array_filter($data, create_function('$v', 'return ($v !== "");'));
            array_unshift($data, $aborn_str);
            $data = array_unique($data);
        } else {
            $data = array($aborn_str);
        }
        $fp = fopen($path, 'wb');
        flock($fp, LOCK_EX);
        fputs($fp, implode("\n", $data));
        fputs($fp, "\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}

if (strpos($mode, '_msg') !== false) {
    if (isset($_GET['selected_string'])) {
        $aborn_str = trim($_GET['selected_string']);
        $aborn_str = preg_replace('/\r\n|\r|\n/u', ' <br> ', $aborn_str);
        // $selected_string��JavaScript��encodeURIComponent()�֐���URL�G���R�[�h����Ă���A
        // encodeURIComponent()��ECMA-262 3rd Edition�̎d�l�ɂ�蕶�����UTF-8�ň������߁B
        $aborn_str = mb_convert_encoding($aborn_str, 'CP932', 'UTF-8');
        $aborn_str = htmlspecialchars($aborn_str, ENT_QUOTES);
        $input_size_at = ($_conf['ktai']) ? '' : ' size="50"';
    } elseif (!isset($aborn_str)) {
        $aborn_str = '';
    }
}

//=====================================================
// ���b�Z�[�W�ݒ�
//=====================================================
switch ($mode) {
    case 'aborn_res':
        $title_st = 'p2 - ���̃��X�����ځ[��';
        if ($popup == 2) {
            $msg = '<b>' . $aborn_str . '</b> �����ځ[�񂵂܂����B';
        } else {
            $aborn_str = $host . '/' . $bbs . '/' . $key . '/' . $resnum;
            $msg = '<b>' . $aborn_str . '</b> �����ځ[�񂵂Ă�낵���ł����H';
            $aborn_str_en = UrlSafeBase64::encode($aborn_str);
        }
        $edit_value = '���ځ[�񃌃X�ҏW';
        break;
    case 'aborn_name':
        $title_st = 'p2 - ���ځ[�񃏁[�h�o�^�F���O';
        if ($popup == 2) {
            $msg = '���ځ[�񃏁[�h�i���O�j�� <b>' . $aborn_str . '</b> ��o�^���܂����B';
        } elseif ($resar[0] != "") {
            $msg = '���ځ[�񃏁[�h�i���O�j�� <b>' . $resar[0] . '</b> ��o�^���Ă�낵���ł����H';
            $aborn_str_en = UrlSafeBase64::encode($resar[0]);
        }
        $edit_value = '���ځ[�񃏁[�h�ҏW�F���O';
        break;
    case 'aborn_mail':
        $title_st = 'p2 - ���ځ[�񃏁[�h�o�^�F���[��';
        if ($popup == 2) {
            $msg = '���ځ[�񃏁[�h�i���[���j�� <b>' . $aborn_str . '</b> ��o�^���܂����B';
        } elseif ($resar[1] != "") {
            $msg = '���ځ[�񃏁[�h�i���[���j�� <b>' . $resar[1] . '</b> ��o�^���Ă�낵���ł����H';
            $aborn_str_en = UrlSafeBase64::encode($resar[1]);
        }
        $edit_value = '���ځ[�񃏁[�h�ҏW�F���[��';
        break;
    case 'aborn_msg':
        $title_st = 'p2 - ���ځ[�񃏁[�h�o�^�F���b�Z�[�W';
        if ($popup == 2) {
            $msg = '���ځ[�񃏁[�h�i���b�Z�[�W�j�� <b>' . $aborn_str . '</b> ��o�^���܂����B';
        } else {
            $msg = '���ځ[�񃏁[�h�i���b�Z�[�W�j<br><input type="text" name="aborn_str"' . $input_size_at . ' value="' . $aborn_str . '">';
        }
        $edit_value = '���ځ[�񃏁[�h�ҏW�F���b�Z�[�W';
        break;
    case 'aborn_id':
        $title_st = 'p2 - ���ځ[�񃏁[�h�o�^�FID';
        if ($popup == 2) {
            $msg = '���ځ[�񃏁[�h�iID�j�� <b>' . $aborn_str . '</b> ��o�^���܂����B';
        } elseif ($aborn_id != "") {
            $msg = '���ځ[�񃏁[�h�iID�j�� <b>' . $aborn_id . '</b> ��o�^���Ă�낵���ł����H';
            $aborn_str_en = UrlSafeBase64::encode($aborn_id);
        }
        $edit_value = '���ځ[�񃏁[�h�ҏW�FID';
        break;
    case 'ng_name':
        $title_st = 'p2 - NG���[�h�o�^�F���O';
        if ($popup == 2) {
            $msg = 'NG���[�h�i���O�j�� <b>' . $aborn_str . '</b> ��o�^���܂����B';
        } elseif ($resar[0] != "") {
            $msg = 'NG���[�h�i���O�j�� <b>' . $resar[0] . '</b> ��o�^���Ă�낵���ł����H';
            $aborn_str_en = UrlSafeBase64::encode($resar[0]);
        }
        $edit_value = 'NG���[�h�ҏW�F���O';
        break;
    case 'ng_mail':
        $title_st = 'p2 - NG���[�h�o�^�F���[��';
        if ($popup == 2) {
            $msg = 'NG���[�h�i���[���j�� <b>' . $aborn_str . '</b> ��o�^���܂����B';
        } elseif ($resar[1] != "") {
            $msg = 'NG���[�h�i���[���j�� <b>' . $resar[1] . '</b> ��o�^���Ă�낵���ł����H';
            $aborn_str_en = UrlSafeBase64::encode($resar[1]);
        }
        $edit_value = 'NG���[�h�ҏW�F���[��';
        break;
    case 'ng_msg':
        $title_st = 'p2 - NG���[�h�o�^�F���b�Z�[�W';
        if ($popup == 2) {
            $msg = 'NG���[�h�i���b�Z�[�W�j�� <b>' . $aborn_str . '</b> ��o�^���܂����B';
        } else {
            $msg = 'NG���[�h�i���b�Z�[�W�j<br><input type="text" name="aborn_str"' . $input_size_at . ' value="' . $aborn_str . '">';
        }
        $edit_value = 'NG���[�h�ҏW�F���b�Z�[�W';
        break;
    case 'ng_id':
        $title_st = 'p2 - NG���[�h�o�^�FID';
        if ($popup == 2) {
            $msg = 'NG���[�h�iID�j�� <b>' . $aborn_str . '</b> ��o�^���܂����B';
        } elseif ($aborn_id != "") {
            $msg = 'NG���[�h�iID�j�� <b>' . $aborn_id . '</b> ��o�^���Ă�낵���ł����H';
            $aborn_str_en = UrlSafeBase64::encode($aborn_id);
        }
        $edit_value = 'NG���[�h�ҏW�FID';
        break;
    default:
        /*���u*/
}


//=====================================================
// HTML�v�����g
//=====================================================
P2Util::header_nocache();
echo $_conf['doctype'];
echo <<<EOHEADER
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    {$_conf['extra_headers_ht']}
    <title>{$title_st}</title>\n
EOHEADER;

$body_onload = '';

if (!$_conf['ktai']) {
    echo <<<EOSTYLE
    <link rel="stylesheet" type="text/css" href="css.php?css=style&amp;skin={$skin_en}">
    <link rel="stylesheet" type="text/css" href="css.php?css=info&amp;skin={$skin_en}">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">\n
EOSTYLE;
    if ($popup == 2) {
        echo "\t<script type=\"text/javascript\" src=\"js/closetimer.js?{$_conf['p2_version_id']}\"></script>\n";
        if (preg_match('/^aborn_/', $mode)) {
            if ($mode != 'aborn_res' && isset($aborn_id) && strlen($aborn_id) >= 8) {
                $aborn_target = 'ID:' . addslashes($aborn_id);
                $aborn_once = 'false';
            } elseif (isset($resnum)) {
                $aborn_target = '>' . intval($resnum) . '</';
                $aborn_once = 'true';
            }
            echo <<<EOJS
    <script type="text/javascript">
    //<![CDATA[
    function infoSpLiveAborn()
    {
        var tgt = "{$aborn_target}";
        var once = {$aborn_once};
        /*try {*/
            var heads = opener.document.getElementsByTagName('div');
            for (var i = heads.length - 1; i >= 0 ; i--) {
                if (heads[i].className.indexOf('res-header') != -1 &&
                    heads[i].innerHTML.indexOf(tgt) != -1)
                {
                    heads[i].parentNode.parentNode.removeChild(heads[i].parentNode);
                    if (once) break;
                }
            }
        /*} catch (e) {
            window.alert(e.toString());
            return false;
        }*/
        return true;
    }
    //]]>
    </script>\n
EOJS;
            $body_onload = " onload=\"infoSpLiveAborn();startTimer(document.getElementById('timerbutton'));\"";
        } else {
            $body_onload = " onload=\"startTimer(document.getElementById('timerbutton'));\"";
        }
    }
}

echo <<<EOP
</head>
<body{$body_onload}>
<p><b><a class="thre_title" href="{$thread_url}"{$target_read_at}>{$ttitle_name}</a></b></p>
<hr>
<div align="center">
EOP;

echo "<form action=\"info_sp.php\" method=\"get\" accept-charset=\"{$_conf['accept_charset']}\">\n";
echo "<p>{$msg}</p>\n";
if ($popup == 1 && $msg != "") {
    foreach ($_GET as $idx => $value) {
        if ($idx == 'selected_string') {
            continue;
        }
        $value_ht = htmlspecialchars($value, ENT_QUOTES);
        echo "\t<input type=\"hidden\" name=\"{$idx}\" value=\"{$value_ht}\">\n";
    }
    if (isset($aborn_str_en)) {
        echo "\t<input type=\"hidden\" name=\"aborn_str_en\" value=\"{$aborn_str_en}\">\n";
    }
    if (isset($aborn_id)) {
        $aborn_id_ht = htmlspecialchars($aborn_id, ENT_QUOTES);
        echo "\t<input type=\"hidden\" name=\"aborn_id\" value=\"{$aborn_id_ht}\">\n";
    }
    echo "\t<input type=\"submit\" value=\"�@�n�@�j�@\">\n";
    if (!$_conf['ktai']) {
        echo "\t<input type=\"button\" value=\"�L�����Z��\" onclick=\"window.close();\">\n";
    }
} elseif (!$_conf['ktai'] && $popup == 2) {
    echo <<<EOB
    <input id="timerbutton" type="button" value="Close Timer" onclick="stopTimer(document.getElementById('timerbutton'))">\n
EOB;
}
echo "\t{$_conf['detect_hint_input_ht']}{$_conf['k_input_ht']}\n";
echo "</form>\n";

//�f�[�^�t�@�C���̕ҏW�{�^��
/*if ($mode == 'readhere') {
    $_GET['mode'] = 'resethere';
    echo "<form action=\"info_sp.php\" method=\"get\">\n";
    foreach ($_GET as $idx => $value) {
        echo "\t<input type=\"hidden\" name=\"{$idx}\" value=\"{$value}\">\n";
    }
    echo "\t<input type=\"submit\" value=\"���̔̂���������Z�b�g\">\n";
    echo "</form>\n";
} else*/
if (isset($edit_value)) {
    $rows = $_conf['ktai'] ? 5 : 36;
    $cols = $_conf['ktai'] ? 0 : 128;
    $edit_php = ($mode == 'aborn_res') ? 'editfile.php' : 'edit_aborn_word.php';
    echo <<<EOFORM
<form action="{$edit_php}" method="get"{$target_edit_at}>
    {$_conf['k_input_ht']}
    <input type="hidden" name="path" value="{$path}">
    <input type="hidden" name="encode" value="Shift_JIS">
    <input type="hidden" name="rows" value="{$rows}">
    <input type="hidden" name="cols" value="{$cols}">
    <input type="submit" value="{$edit_value}">\n
EOFORM;
    if (!$_conf['ktai'] && $popup == 1 && $msg == "") {
        echo "\t<input type=\"button\" value=\"�L�����Z��\" onclick=\"window.close();\">\n";
    }
    echo "</form>\n";
}

echo "</div>\n";

echo "<hr>\n";

if ($_conf['ktai']) {
    echo '<p>';
    if (!empty($_GET['from_read_new'])) {
        echo "<a href=\"{$_conf['read_new_k_php']}?cview=1\">�܂Ƃߓǂ݂ɖ߂�</a><br>";
    }
    echo "<a href=\"{$thread_url}\">�ڂɖ߂�</a>";
    echo '</p>';
}

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
