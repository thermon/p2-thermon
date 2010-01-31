<?php
/**
 * rep2 - ���O�C��
 */

require_once './conf/conf.inc.php';

$_login->authorize(); // ���[�U�F��

$csrfid = P2Util::getCsrfId(__FILE__);

//=========================================================
// �����o���p�ϐ�
//=========================================================
$p_htm = array();

// �\������
$p_str = array(
    'ptitle'        => 'rep2�F�؃��[�U�Ǘ�',
    'autho_user'    => '�F�؃��[�U',
    'logout'        => '���O�A�E�g',
    'password'      => '�p�X���[�h',
    'login'         => '���O�C��',
    'user'          => '���[�U'
);

// �g�їp�\��������ϊ�
if ($_conf['ktai'] && function_exists('mb_convert_kana')) {
    foreach ($p_str as $k => $v) {
        $p_str[$k] = mb_convert_kana($v, 'rnsk');
    }
}

// �i�g�сj���O�C���pURL
//$user_u_q = $_conf['ktai'] ? "?user={$_login->user_u}" : '';
//$url = rtrim(dirname(P2Util::getMyUrl()), '/') . '/' . $user_u_q . '&amp;b=k';
$url = rtrim(dirname(P2Util::getMyUrl()), '/') . '/?b=k';

$p_htm['ktai_url'] = '�g��'.$p_str['login'].'�pURL <a href="'.$url.'" target="_blank">'.$url.'</a><br>';

//====================================================
// ���[�U�o�^����
//====================================================
if (isset($_POST['form_new_login_pass'])) {
    if (!isset($_POST['csrfid']) or $_POST['csrfid'] != $csrfid) {
        p2die('�s���ȃ|�X�g�ł�');
    }

    $new_login_pass = $_POST['form_new_login_pass'];

    // ���̓`�F�b�N
    if (!preg_match('/^[0-9A-Za-z_]+$/', $new_login_pass)) {
        P2Util::pushInfoHtml("<p>rep2 error: {$p_str['password']}�𔼊p�p�����œ��͂��ĉ������B</p>");
    } elseif ($new_login_pass != $_POST['form_new_login_pass2']) {
        P2Util::pushInfoHtml("<p>rep2 error: {$p_str['password']} �� {$p_str['password']} (�m�F) ����v���܂���ł����B</p>");

    // �p�X���[�h�ύX�o�^�������s��
    } else {
        $crypted_login_pass = sha1($new_login_pass);
        $auth_user_cont = <<<EOP
<?php
\$rec_login_user_u = '{$_login->user_u}';
\$rec_login_pass_x = '{$crypted_login_pass}';
?>
EOP;
        FileCtl::make_datafile($_conf['auth_user_file'], $_conf['pass_perm']); // �t�@�C�����Ȃ���ΐ���
        $fp = @fopen($_conf['auth_user_file'], 'wb');
        if (!$fp) {
            p2die("{$_conf['auth_user_file']} ��ۑ��ł��܂���ł����B�F�؃��[�U�o�^���s�B");
        }
        flock($fp, LOCK_EX);
        fputs($fp, $auth_user_cont);
        flock($fp, LOCK_UN);
        fclose($fp);

        P2Util::pushInfoHtml('<p>���F�؃p�X���[�h��ύX�o�^���܂���</p>');
    }

}

//====================================================
// �⏕�F��
//====================================================
$mobile = Net_UserAgent_Mobile::singleton();

$p_htm['auth_ctl'] = '';

// docomo�F��
if ($mobile->isDoCoMo()) {
    if (file_exists($_conf['auth_imodeid_file'])) {
        $p_htm['auth_ctl'] .= <<<EOP
i���[�hID�F�ؓo�^��[<a href="{$_SERVER['SCRIPT_NAME']}?ctl_regist_imodeid=1{$_conf['k_at_a']}">����</a>]<br>
EOP;
    }
    if (file_exists($_conf['auth_docomo_file'])) {
        $p_htm['auth_ctl'] .= <<<EOP
�[��ID�F�ؓo�^��[<a href="{$_SERVER['SCRIPT_NAME']}?ctl_regist_docomo=1{$_conf['k_at_a']}">����</a>]<br>
EOP;
    }

    if ($p_htm['auth_ctl'] == '' && $_login->pass_x) {
        if (empty($_SERVER['HTTPS'])) {
            $p_htm['auth_ctl'] = <<<EOP
[<a href="{$_SERVER['SCRIPT_NAME']}?ctl_regist_imodeid=1&amp;regist_imodeid=1&amp;guid=ON{$_conf['k_at_a']}">i���[�hID�ŔF�؂�o�^</a>]<br>
EOP;
        } else {
            $p_htm['auth_ctl'] = <<<EOP
[<a href="{$_SERVER['SCRIPT_NAME']}?ctl_regist_docomo=1&amp;regist_docomo=1{$_conf['k_at_a']}" utn>�[��ID�ŔF�؂�o�^</a>]<br>
EOP;
        }
    }

// EZ�F��
} elseif ($mobile->isEZweb()) {
    if (file_exists($_conf['auth_ez_file'])) {
        $p_htm['auth_ctl'] = <<<EOP
�[��ID�F�ؓo�^��[<a href="{$_SERVER['SCRIPT_NAME']}?ctl_regist_ez=1{$_conf['k_at_a']}">����</a>]<br>
EOP;
    } elseif ($mobile->getUID() !== null) {
        if ($_login->pass_x) {
            $p_htm['auth_ctl'] = <<<EOP
[<a href="{$_SERVER['SCRIPT_NAME']}?ctl_regist_ez=1&amp;regist_ez=1{$_conf['k_at_a']}">�[��ID�ŔF�؂�o�^</a>]<br>
EOP;
        }
    }

// Y!�F��
} elseif ($mobile->isSoftBank()) {
    if (file_exists($_conf['auth_jp_file'])) {
        $p_htm['auth_ctl'] = <<<EOP
�[��ID�F�ؓo�^��[<a href="{$_SERVER['SCRIPT_NAME']}?ctl_regist_jp=1{$_conf['k_at_a']}">����</a>]<br>
EOP;
    } elseif ($mobile->getSerialNumber() !== null) {
        if ($_login->pass_x) {
            $p_htm['auth_ctl'] = <<<EOP
[<a href="{$_SERVER['SCRIPT_NAME']}?ctl_regist_jp=1&amp;regist_jp=1{$_conf['k_at_a']}">�[��ID�ŔF�؂�o�^</a>]<br>
EOP;
        }
    }

// Cookie�F��
} else {
    if ($_login->checkUserPwWithCid($_COOKIE['cid'])) {
            $p_htm['auth_cookie'] = <<<EOP
cookie�F�ؓo�^��[<a href="cookie.php?ctl_regist_cookie=1{$_conf['k_at_a']}">����</a>]<br>
EOP;
    } else {
        if ($_login->pass_x) {
            $p_htm['auth_cookie'] = <<<EOP
[<a href="cookie.php?ctl_regist_cookie=1&amp;regist_cookie=1{$_conf['k_at_a']}">cookie�ŔF�؂�o�^</a>]<br>
EOP;
        }
    }
}

//====================================================
// Cookie�F�؃`�F�b�N
//====================================================
if (!empty($_REQUEST['check_regist_cookie'])) {

    if ($_login->checkUserPwWithCid($_COOKIE['cid'])) {
        if ($_REQUEST['regist_cookie'] == '1') {
            $info_msg_ht = '<p>��cookie�F�ؓo�^����</p>';
        } else {
            $info_msg_ht = '<p>�~cookie�F�؉������s</p>';
        }

    } else {
        if ($_REQUEST['regist_cookie'] == '1') {
            $info_msg_ht = '<p>�~cookie�F�ؓo�^���s</p>';
        } else  {
            $info_msg_ht = '<p>��cookie�F�؉�������</p>';
        }
    }

    P2Util::pushInfoHtml($info_msg_ht);
}

//====================================================
// �F�؃��[�U�o�^�t�H�[��
//====================================================
if ($_conf['ktai']) {
    $login_form_ht = <<<EOP
<hr>
<form id="login_change" method="POST" action="{$_SERVER['SCRIPT_NAME']}" target="_self">
    {$p_str['password']}�̕ύX<br>
    {$_conf['k_input_ht']}
    <input type="hidden" name="csrfid" value="{$csrfid}">
    �V����{$p_str['password']}:<br>
    <input type="password" name="form_new_login_pass"><br>
    �V����{$p_str['password']} (�m�F):<br>
    <input type="password" name="form_new_login_pass2"><br>
    <input type="submit" name="submit" value="�ύX�o�^">
</form>
<hr>
<div class="center">{$_conf['k_to_index_ht']}</div>
EOP;
} else {
    $login_form_ht = <<<EOP
<form id="login_change" method="POST" action="{$_SERVER['SCRIPT_NAME']}" target="_self">
    {$p_str['password']}�̕ύX<br>
    {$_conf['k_input_ht']}
    <input type="hidden" name="csrfid" value="{$csrfid}">
    <table border="0">
        <tr>
            <td>�V����{$p_str['password']}</td>
            <td><input type="password" name="form_new_login_pass"></td>
        </tr>
        <tr>
            <td>�V����{$p_str['password']} (�m�F)</td>
            <td><input type="password" name="form_new_login_pass2"></td>
        </tr>
    </table>
    <input type="submit" name="submit" value="�ύX�o�^">
</form>
EOP;
}

//=========================================================
// HTML�v�����g
//=========================================================
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
    <title>{$p_str['ptitle']}</title>\n
EOP;

if (!$_conf['ktai']) {
    echo <<<EOP
    <link rel="stylesheet" type="text/css" href="css.php?css=style&amp;skin={$skin_en}">
    <link rel="stylesheet" type="text/css" href="css.php?css=login&amp;skin={$skin_en}">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
    <script type="text/javascript" src="js/basic.js?{$_conf['p2_version_id']}"></script>\n
EOP;
}

$body_at = ($_conf['ktai']) ? $_conf['k_colors'] : ' onload="setWinTitle();"';
echo <<<EOP
</head>
<body{$body_at}>
EOP;

if (!$_conf['ktai']) {
    echo <<<EOP
<p id="pan_menu"><a href="setting.php">���O�C���Ǘ�</a> &gt; {$p_str['ptitle']}</p>
EOP;
}

// ���\��
P2Util::printInfoHtml();

echo '<p id="login_status">';
echo <<<EOP
{$p_str['autho_user']}: {$_login->user_u}<br>
{$p_htm['auth_ctl']}
{$p_htm['auth_cookie']}
<br>
[<a href="./index.php?logout=1" target="_parent">{$p_str['logout']}����</a>]
EOP;
echo '</p>';

echo $login_form_ht;

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
