<?php
/**
 * rep2 - ���X��������
 */

require_once './conf/conf.inc.php';

$_login->authorize(); // ���[�U�F��

if (!empty($_conf['disable_res'])) {
    p2die('�������݋@�\�͖����ł��B');
}

// �����G���[
if (empty($_POST['host'])) {
    p2die('�����̎w�肪�ςł�');
}

$el = error_reporting(E_ALL & ~E_NOTICE);
$salt = 'post' . $_POST['host'] . $_POST['bbs'] . $_POST['key'];
error_reporting($el);

if (!isset($_POST['csrfid']) or $_POST['csrfid'] != P2Util::getCsrfId($salt)) {
    p2die('�s���ȃ|�X�g�ł�');
}

if ($_conf['expack.aas.enabled'] && !empty($_POST['PREVIEW_AAS'])) {
    include P2_BASE_DIR . '/aas.php';
    exit;
}

//================================================================
// �ϐ�
//================================================================
$newtime = date('gis');

$post_param_keys    = array('bbs', 'key', 'time', 'FROM', 'mail', 'MESSAGE', 'subject', 'submit');
$post_internal_keys = array('host', 'sub', 'popup', 'rescount', 'ttitle_en');
$post_optional_keys = array('newthread', 'beres', 'p2res', 'from_read_new', 'maru', 'csrfid');
$post_p2_flag_keys  = array('b', 'p2_post_confirm_cookie');

foreach ($post_param_keys as $pk) {
    ${$pk} = (isset($_POST[$pk])) ? $_POST[$pk] : '';
}
foreach ($post_internal_keys as $pk) {
    ${$pk} = (isset($_POST[$pk])) ? $_POST[$pk] : '';
}

if (!isset($ttitle)) {
    if ($ttitle_en) {
        $ttitle = UrlSafeBase64::decode($ttitle_en);
    } elseif ($subject) {
        $ttitle = $subject;
    } else {
        $ttitle = '';
    }
}

//$MESSAGE = rtrim($MESSAGE);

// {{{ �\�[�X�R�[�h�����ꂢ�ɍČ������悤�ɕϊ�

if (!empty($_POST['fix_source'])) {
    // �^�u���X�y�[�X��
    $MESSAGE = tab2space($MESSAGE);
    // ���ꕶ�������̎Q�Ƃ�
    $MESSAGE = htmlspecialchars($MESSAGE, ENT_QUOTES, 'Shift_JIS');
    // ����URL�����N���
    $MESSAGE = str_replace('tp://', 't&#112;://', $MESSAGE);
    // �s���̃X�y�[�X�����̎Q�Ƃ�
    $MESSAGE = preg_replace('/^ /m', '&nbsp;', $MESSAGE);
    // ������X�y�[�X�̈�ڂ����̎Q�Ƃ�
    $MESSAGE = preg_replace('/(?<!&nbsp;)  /', '&nbsp; ', $MESSAGE);
    // ���X�y�[�X������Ԃ��Ƃ��̎d�グ
    $MESSAGE = preg_replace('/(?<=&nbsp;)  /', ' &nbsp;', $MESSAGE);
}

// }}}

// ������΂�livedoor�ړ]�ɑΉ��Bpost���livedoor�Ƃ���B
$host = P2Util::adjustHostJbbs($host);

// machibbs�AJBBS@������� �Ȃ�
if (P2Util::isHostMachiBbs($host) or P2Util::isHostJbbsShitaraba($host)) {
    $bbs_cgi = '/bbs/write.cgi';

    // JBBS@������� �Ȃ�
    if (P2Util::isHostJbbsShitaraba($host)) {
        $bbs_cgi = '../../bbs/write.cgi';
        preg_match('/\\/(\\w+)$/', $host, $ar);
        $dir = $ar[1];
        $dir_k = 'DIR';
    }

    /* compact() �� array_combine() ��POST����l�̔z������̂ŁA
       $post_param_keys �� $post_send_keys �̒l�̏����͑�����I */
    //$post_param_keys  = array('bbs', 'key', 'time', 'FROM', 'mail', 'MESSAGE', 'subject', 'submit');
    $post_send_keys     = array('BBS', 'KEY', 'TIME', 'NAME', 'MAIL', 'MESSAGE', 'SUBJECT', 'submit');
    $key_k     = 'KEY';
    $subject_k = 'SUBJECT';

// 2ch
} else {
    if ($sub) {
        $bbs_cgi = "/test/{$sub}bbs.cgi";
    } else {
        $bbs_cgi = '/test/bbs.cgi';
    }
    $post_send_keys = $post_param_keys;
    $key_k     = 'key';
    $subject_k = 'subject';
}

// submit �͏������ނŌŒ肵�Ă��܂��iBe�ŏ������ނ̏ꍇ�����邽�߁j
$submit = '��������';

$post = array_combine($post_send_keys, compact($post_param_keys));
$post_cache = $post;
unset($post_cache['submit']);

if (!empty($_POST['newthread'])) {
    unset($post[$key_k]);
    $location_ht = "{$_conf['subject_php']}?host={$host}&amp;bbs={$bbs}{$_conf['k_at_a']}";
} else {
    unset($post[$subject_k]);
    $location_ht = "{$_conf['read_php']}?host={$host}&amp;bbs={$bbs}&amp;key={$key}&amp;ls={$rescount}-&amp;refresh=1&amp;nt={$newtime}{$_conf['k_at_a']}#r{$rescount}";
}

if (P2Util::isHostJbbsShitaraba($host)) {
    $post[$dir_k] = $dir;
}

// {{{ 2ch�Ł����O�C�����Ȃ�sid�ǉ�

if (!empty($_POST['maru']) and P2Util::isHost2chs($host) && file_exists($_conf['sid2ch_php'])) {

    // ���O�C����A24���Ԉȏ�o�߂��Ă����玩���ă��O�C��
    if (file_exists($_conf['idpw2ch_php']) && filemtime($_conf['sid2ch_php']) < time() - 60*60*24) {
        require_once P2_LIB_DIR . '/login2ch.inc.php';
        login2ch();
    }

    include $_conf['sid2ch_php'];
    $post['sid'] = $SID2ch;
}

// }}}

if (!empty($_POST['p2_post_confirm_cookie'])) {
    $post_ignore_keys = array_merge($post_param_keys, $post_internal_keys, $post_optional_keys, $post_p2_flag_keys);
    foreach ($_POST as $k => $v) {
        if (!array_key_exists($k, $post) && !in_array($k, $post_ignore_keys)) {
            $post[$k] = $v;
        }
    }
}

if (!empty($_POST['newthread'])) {
    $ptitle = 'rep2 - �V�K�X���b�h�쐬';
} else {
    $ptitle = 'rep2 - ���X��������';
}

$post_backup_key = PostDataStore::getKeyForBackup($host, $bbs, $key, !empty($_REQUEST['newthread']));
$post_config_key = PostDataStore::getKeyForConfig($host, $bbs);

// �ݒ��ۑ�
PostDataStore::set($post_config_key, array(
    'beres' => !empty($_REQUEST['beres']),
    'p2res' => !empty($_REQUEST['p2res']),
));

//================================================================
// �������ݏ���
//================================================================

// �������݂��ꎞ�I�ɕۑ�
PostDataStore::set($post_backup_key, $post_cache);

// �|�X�g���s
if (!empty($_POST['p2res']) && empty($_POST['newthread'])) {
    // ����p2�ŏ�������
    $posted = postIt2($host, $bbs, $key, $FROM, $mail, $MESSAGE);
} else {
    // cookie �ǂݍ���
    $cookie_key = $_login->user_u . '/' . P2Util::normalizeHostName($host);
    if ($p2cookies = CookieDataStore::get($cookie_key)) {
        if (is_array($p2cookies)) {
            if (array_key_exists('expires', $p2cookies)) {
                // �����؂�Ȃ�j��
                if (time() > strtotime($p2cookies['expires'])) {
                    CookieDataStore::delete($cookie_key);
                    $p2cookies = null;
                }
            }
        } else {
            CookieDataStore::delete($cookie_key);
            $p2cookies = null;
        }
    } else {
        $p2cookies = null;
    }

    // ���ڏ�������
    $posted = postIt($host, $bbs, $key, $post);

    // cookie �ۑ�
    if ($p2cookies) {
        CookieDataStore::set($cookie_key, $p2cookies);
    }
}

// ���e���s�L�^���폜
if ($posted) {
    PostDataStore::delete($post_backup_key);
}

//=============================================
// �X�����Đ����Ȃ�Asubject����key���擾
//=============================================
if (!empty($_POST['newthread']) && $posted) {
    sleep(1);
    $key = getKeyInSubject();
}

//=============================================
// key.idx �ۑ�
//=============================================
// <> ���O���B�B
$tag_rec['FROM'] = str_replace('<>', '', $FROM);
$tag_rec['mail'] = str_replace('<>', '', $mail);

// ���O�ƃ��[���A�󔒎��� P2NULL ���L�^
$tag_rec_n['FROM'] = ($tag_rec['FROM'] == '') ? 'P2NULL' : $tag_rec['FROM'];
$tag_rec_n['mail'] = ($tag_rec['mail'] == '') ? 'P2NULL' : $tag_rec['mail'];

if ($host && $bbs && $key) {
    $keyidx = P2Util::idxDirOfHostBbs($host, $bbs) . $key . '.idx';

    // �ǂݍ���
    if ($keylines = FileCtl::file_read_lines($keyidx, FILE_IGNORE_NEW_LINES)) {
        $akeyline = explode('<>', $keylines[0]);
    }
    $sar = array($akeyline[0], $akeyline[1], $akeyline[2], $akeyline[3], $akeyline[4],
                 $akeyline[5], $akeyline[6], $tag_rec_n['FROM'], $tag_rec_n['mail'], $akeyline[9],
                 $akeyline[10], $akeyline[11], $akeyline[12]);
    P2Util::recKeyIdx($keyidx, $sar); // key.idx�ɋL�^
}

//=============================================
// �������ݗ���
//=============================================
if (empty($posted)) {
    exit;
}

if ($host && $bbs && $key) {

    $lock = new P2Lock($_conf['res_hist_idx'], false);

    FileCtl::make_datafile($_conf['res_hist_idx'], $_conf['res_write_perm']); // �Ȃ���ΐ���

    $lines = FileCtl::file_read_lines($_conf['res_hist_idx'], FILE_IGNORE_NEW_LINES);

    $neolines = array();

    // {{{ �ŏ��ɏd���v�f���폜���Ă���

    if (is_array($lines)) {
        foreach ($lines as $line) {
            $lar = explode('<>', $line);
            // �d�����, key�̂Ȃ����͕̂s���f�[�^
            if (!$lar[1] || $lar[1] == $key) {
                continue;
            } 
            $neolines[] = $line;
        }
    }

    // }}}

    // �V�K�f�[�^�ǉ�
    $newdata = "{$ttitle}<>{$key}<><><><><><>{$tag_rec['FROM']}<>{$tag_rec['mail']}<><>{$host}<>{$bbs}";
    array_unshift($neolines, $newdata);
    while (sizeof($neolines) > $_conf['res_hist_rec_num']) {
        array_pop($neolines);
    }

    // {{{ ��������

    if ($neolines) {
        $cont = '';
        foreach ($neolines as $l) {
            $cont .= $l . "\n";
        }

        if (FileCtl::file_write_contents($_conf['res_hist_idx'], $cont) === false) {
            p2die('cannot write file.');
        }
    }

    // }}}

    $lock->free();
}

//=============================================
// �������݃��O�L�^
//=============================================
if ($_conf['res_write_rec']) {

    // �f�[�^PHP�`���ip2_res_hist.dat.php, �^�u��؂�j�̏������ݗ������Adat�`���ip2_res_hist.dat, <>��؂�j�ɕϊ�����
    P2Util::transResHistLogPhpToDat();

    $date_and_id = date('y/m/d H:i');
    $message = htmlspecialchars($MESSAGE, ENT_NOQUOTES);
    $message = preg_replace('/\\r\\n|\\r|\\n/', '<br>', $message);

    FileCtl::make_datafile($_conf['res_hist_dat'], $_conf['res_write_perm']); // �Ȃ���ΐ���

    $resnum = '';
    if (!empty($_POST['newthread'])) {
        $resnum = 1;
    } else {
        if ($rescount) {
            $resnum = $rescount + 1;
        }
    }

    // �V�K�f�[�^
    $newdata = "{$tag_rec['FROM']}<>{$tag_rec['mail']}<>{$date_and_id}<>{$message}<>{$ttitle}<>{$host}<>{$bbs}<>{$key}<>{$resnum}";

    // �܂��^�u��S�ĊO���āi2ch�̏������݂ł̓^�u�͍폜����� 2004/12/13�j
    $newdata = str_replace("\t", '', $newdata);
    // <>���^�u�ɕϊ�����
    //$newdata = str_replace('<>', "\t", $newdata);

    $cont = $newdata."\n";

    // �������ݏ���
    if (FileCtl::file_write_contents($_conf['res_hist_dat'], $cont, FILE_APPEND) === false) {
        trigger_error('rep2 error: �������݃��O�̕ۑ��Ɏ��s���܂���', E_USER_WARNING);
        // ����͎��ۂ͕\������Ȃ�����ǂ�
        //P2Util::pushInfoHtml('<p>rep2 error: �������݃��O�̕ۑ��Ɏ��s���܂���</p>');
    }
}

//===========================================================
// �֐�
//===========================================================
// {{{ postIt()

/**
 * ���X����������
 *
 * @return boolean �������ݐ����Ȃ� true�A���s�Ȃ� false
 */
function postIt($host, $bbs, $key, $post)
{
    global $_conf, $post_result, $post_error2ch, $p2cookies, $popup, $rescount, $ttitle_en;
    global $bbs_cgi;

    $method = 'POST';
    $bbs_cgi_url = 'http://' . $host . $bbs_cgi;

    $URL = parse_url($bbs_cgi_url); // URL����
    if (isset($URL['query'])) { // �N�G���[
        $URL['query'] = '?' . $URL['query'];
    } else {
        $URL['query'] = '';
    }

    // �v���L�V
    if ($_conf['proxy_use']) {
        $send_host = $_conf['proxy_host'];
        $send_port = $_conf['proxy_port'];
        $send_path = $bbs_cgi_url;
    } else {
        $send_host = $URL['host'];
        $send_port = isset($URL['port']) ? $URL['port'] : 80;
        $send_path = $URL['path'] . $URL['query'];
    }

    if (!$send_port) { $send_port = 80; }    // �f�t�H���g��80

    $request = "{$method} {$send_path} HTTP/1.0\r\n";
    $request .= "Host: {$URL['host']}\r\n";
    $request .= "User-Agent: Monazilla/1.00 ({$_conf['p2ua']})\r\n";
    $request .= "Referer: http://{$URL['host']}/\r\n";

    // �N�b�L�[
    $cookies_to_send = '';
    if ($p2cookies) {
        foreach ($p2cookies as $cname => $cvalue) {
            if ($cname != 'expires') {
                $cookies_to_send .= " {$cname}={$cvalue};";
            }
        }
    }

    // be.2ch.net �F�؃N�b�L�[
    if (P2Util::isHostBe2chNet($host) || !empty($_REQUEST['beres'])) {
        $cookies_to_send .= ' MDMD='.$_conf['be_2ch_code'].';';    // be.2ch.net�̔F�؃R�[�h(�p�X���[�h�ł͂Ȃ�)
        $cookies_to_send .= ' DMDM='.$_conf['be_2ch_mail'].';';    // be.2ch.net�̓o�^���[���A�h���X
    }

    if (!$cookies_to_send) { $cookies_to_send = ' ;'; }
    $request .= 'Cookie:'.$cookies_to_send."\r\n";
    //$request .= 'Cookie: PON='.$SPID.'; NAME='.$FROM.'; MAIL='.$mail."\r\n";

    $request .= "Connection: Close\r\n";

    // {{{ POST�̎��̓w�b�_��ǉ����Ė�����URL�G���R�[�h�����f�[�^��Y�t

    if (strcasecmp($method, 'POST') == 0) {
        $post_enc = array();
        while (list($name, $value) = each($post)) {

            // ������� or be.2ch.net�Ȃ�AEUC�ɕϊ�
            if (P2Util::isHostJbbsShitaraba($host) || P2Util::isHostBe2chNet($host)) {
                $value = mb_convert_encoding($value, 'CP51932', 'CP932');
            }

            $post_enc[] = $name . '=' . rawurlencode($value);
        }
        $postdata = implode("&", $post_enc);
        $request .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $request .= "Content-Length: ".strlen($postdata)."\r\n";
        $request .= "\r\n";
        $request .= $postdata;

    } else {
        $request .= "\r\n";
    }
    // }}}

    // WEB�T�[�o�֐ڑ�
    $fp = fsockopen($send_host, $send_port, $errno, $errstr, $_conf['http_conn_timeout']);
    if (!$fp) {
        $errstr = htmlspecialchars($errstr, ENT_QUOTES);
        showPostMsg(false, "�T�[�o�ڑ��G���[: $errstr ($errno)<br>p2 Error: �T�[�o�ւ̐ڑ��Ɏ��s���܂���", false);
        return false;
    }
    stream_set_timeout($fp, $_conf['http_read_timeout'], 0);

    //echo '<h4>$request</h4><p>' . $request . "</p>"; //for debug
    fputs($fp, $request);

    $start_here = false;
    $post_seikou = false;

    while (!p2_stream_eof($fp, $timed_out)) {

        if ($start_here) {
            $wr = '';
            while (!p2_stream_eof($fp, $timed_out)) {
                $wr .= fread($fp, 164000);
            }
            $response = $wr;
            break;

        } else {
            $l = fgets($fp, 164000);
            //echo $l .'<br>'; // for debug
            // �N�b�L�[�L�^
            if (preg_match('/Set-Cookie: (.+?)\\r\\n/', $l, $matches)) {
                //echo '<p>' . $matches[0] . '</p>'; //
                $cgroups = explode(';', $matches[1]);
                if ($cgroups) {
                    foreach ($cgroups as $v) {
                        if (preg_match('/(.+)=(.*)/', $v, $m)) {
                            $k = ltrim($m[1]);
                            if ($k != 'path') {
                                if (!$p2cookies) {
                                    $p2cookies = array();
                                }
                                $p2cookies[$k] = $m[2];
                            }
                        }
                    }
                }
                if ($p2cookies) {
                    $cookies_to_send = '';
                    foreach ($p2cookies as $cname => $cvalue) {
                        if ($cname != 'expires') {
                            $cookies_to_send .= " {$cname}={$cvalue};";
                        }
                    }
                    $newcookies = "Cookie:{$cookies_to_send}\r\n";

                    $request = preg_replace('/Cookie: .*?\\r\\n/', $newcookies, $request);
                }

            // �]���͏������ݐ����Ɣ��f
            } elseif (preg_match('/^Location: /', $l, $matches)) {
                $post_seikou = true;
            }
            if ($l == "\r\n") {
                $start_here = true;
            }
        }

    }
    fclose($fp);

    // be.2ch.net or JBBS������� �����R�[�h�ϊ� EUC��SJIS
    if (P2Util::isHostBe2chNet($host) || P2Util::isHostJbbsShitaraba($host)) {
        $response = mb_convert_encoding($response, 'CP932', 'CP51932');

        //<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
        $response = preg_replace(
            '{<head>(.*?)<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">(.*)</head>}is',
            '<head><meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">$1$2</head>',
            $response);
    }

    $kakikonda_match = '{<title>.*(?:�������݂܂���|�� �������݂܂��� ��|�������ݏI�� - SubAll BBS).*</title>}is';
    $cookie_kakunin_match = '{<!-- 2ch_X:cookie -->|<title>�� �������݊m�F ��</title>|>�������݊m�F�B<}';

    if (preg_match('/<.+>/s', $response, $matches)) {
        $response = $matches[0];
    }

    // �J�L�R�~����
    if ($post_seikou || preg_match($kakikonda_match, $response)) {
        $reload = empty($_POST['from_read_new']);
        showPostMsg(true, '�������݂��I���܂����B', $reload);

        return true;
        //$response_ht = htmlspecialchars($response, ENT_QUOTES);
        //echo "<pre>{$response_ht}</pre>";

    // cookie�m�F�ipost�ă`�������W�j
    } elseif (preg_match($cookie_kakunin_match, $response)) {
        showCookieConfirmation($host, $response);
        return false;

    // ���̑��̓��X�|���X�����̂܂ܕ\��
    } else {
        echo preg_replace('@������Ń����[�h���Ă��������B<a href="\\.\\./[a-z]+/index\\.html"> GO! </a><br>@', '', $response);
        return false;
    }
}

// }}}
// {{{ postIt2()

/**
 * ����p2�Ń��X����������
 *
 * @return boolean �������ݐ����Ȃ� true�A���s�Ȃ� false
 */
function postIt2($host, $bbs, $key, $FROM, $mail, $MESSAGE)
{
    if (P2Util::isHostBe2chNet($host) || !empty($_REQUEST['beres'])) {
        $beRes = true;
    } else {
        $beRes = false;
    }

    try {
        $posted = P2Util::getP2Client()->post($host, $bbs, $key,
                                              $FROM, $mail, $MESSAGE,
                                              $beRes, $response);
    } catch (P2Exception $e) {
        p2die('����p2�|�X�g���s', $e->getMessage());
    }

    if ($posted) {
        $reload = empty($_POST['from_read_new']);
        showPostMsg(true, '�������݂��I���܂����B', $reload);
    } else {
        $result_msg = '����p2�|�X�g���s</p>'
                    . '<pre>' . htmlspecialchars($response['body'], ENT_QUOTES, 'Shift_JIS') . '</pre>'
                    . '<p>-';
        showPostMsg(false, $result_msg, false);
    }

    return $posted;
}

// }}}
// {{{ showPostMsg()

/**
 * �������ݏ������ʕ\������
 *
 * @return void
 */
function showPostMsg($isDone, $result_msg, $reload)
{
    global $_conf, $location_ht, $popup, $ttitle, $ptitle;
    global $STYLE, $skin_en;

    // �v�����g�p�ϐ� ===============
    if (!$_conf['ktai']) {
        $class_ttitle = ' class="thre_title"';
    } else {
        $class_ttitle = '';
    }
    $ttitle_ht = "<b{$class_ttitle}>{$ttitle}</b>";
    // 2005/03/01 aki: jig�u���E�U�ɑΉ����邽�߁A&amp; �ł͂Ȃ� & ��
    // 2005/04/25 rsk: <script>�^�O����CDATA�Ƃ��Ĉ����邽�߁A&amp;�ɂ��Ă͂����Ȃ�
    $location_noenc = str_replace('&amp;', '&', $location_ht);
    if ($popup) {
        $popup_ht = <<<EOJS
<script type="text/javascript">
//<![CDATA[
    opener.location.href="{$location_noenc}";
    var delay= 3*1000;
    setTimeout("window.close()", delay);
//]]>
</script>
EOJS;

    } else {
        $popup_ht = '';
        $_conf['extra_headers_ht'] .= <<<EOP
<meta http-equiv="refresh" content="1;URL={$location_noenc}">
EOP;
    }

    // �v�����g ==============
    echo $_conf['doctype'];
    echo <<<EOHEADER
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    {$_conf['extra_headers_ht']}
EOHEADER;

    if ($isDone) {
        echo "    <title>rep2 - �������݂܂����B</title>";
    } else {
        echo "    <title>{$ptitle}</title>";
    }

    if (!$_conf['ktai']) {
        echo <<<EOP
    <link rel="stylesheet" type="text/css" href="css.php?css=style&amp;skin={$skin_en}">
    <link rel="stylesheet" type="text/css" href="css.php?css=post&amp;skin={$skin_en}">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">\n
EOP;
        if ($popup) {
            echo <<<EOSCRIPT
            <script type="text/javascript">
            //<![CDATA[
                resizeTo({$STYLE['post_pop_size']});
            //]]>
            </script>
EOSCRIPT;
        }
        if ($reload) {
            echo $popup_ht;
        }
        $kakunin_ht = '';
    } else {
        $kakunin_ht = <<<EOP
<p><a href="{$location_ht}">�m�F</a></p>
EOP;
    }

    echo "</head>\n";
    echo "<body{$_conf['k_colors']}>\n";

    P2Util::printInfoHtml();

    echo <<<EOP
<p>{$ttitle_ht}</p>
<p>{$result_msg}</p>
{$kakunin_ht}
</body>
</html>
EOP;
}

// }}}
// {{{ showCookieConfirmation()

/**
 * Cookie�m�FHTML��\������
 *
 * @param   string $host        �z�X�g��
 * @param   string $response    ���X�|���X�{�f�B
 * @return  void
 */
function showCookieConfirmation($host, $response)
{
    global $_conf, $post_param_keys, $post_send_keys, $post_optional_keys;
    global $popup, $rescount, $ttitle_en;
    global $STYLE, $skin_en;

    // HTML��DOM�ŉ��
    $doc = P2Util::getHtmlDom($response, 'Shift_JIS', false);
    if (!$doc) {
        showUnexpectedResponse($response, __LINE__);
        return;
    }

    $xpath = new DOMXPath($doc);
    $heads = $doc->getElementsByTagName('head');
    $bodies = $doc->getElementsByTagName('body');
    if ($heads->length != 1 || $bodies->length != 1) {
        showUnexpectedResponse($response, __LINE__);
        return;
    }

    $head = $heads->item(0);
    $body = $bodies->item(0);
    $xpath = new DOMXPath($doc);

    // �t�H�[����T��
    $forms = $xpath->query(".//form[(@method = 'POST' or @method = 'post')
            and (starts-with(@action, '../test/bbs.cgi') or starts-with(@action, '../test/subbbs.cgi'))]", $body);
    if ($forms->length != 1) {
        showUnexpectedResponse($response, __LINE__);
        return;
    }
    $form = $forms->item(0);

    if (!preg_match('{^\\.\\./test/(sub)?bbs\\.cgi(?:\\?guid=ON)?$}', $form->getAttribute('action'), $matches)) {
        showUnexpectedResponse($response, __LINE__);
        return;
    }

    if (array_key_exists(1, $matches) && strlen($matches[1])) {
        $subbbs = $matches[1];
    } else {
        $subbbs = false;
    }

    // form�v�f�̑����l������������
    // method������action�����ȊO�̑����͍폜���Aaccept-charset������ǉ�����
    // DOMNamedNodeMap�̃C�e���[�V�����ƁA����Ɋ܂܂��m�[�h�̍폜�͕ʂɍs��
    $rmattrs = array();
    foreach ($form->attributes as $name => $node) {
        switch ($name) {
            case 'method':
                //$node->value = 'POST';
                break;
            case 'action':
                $node->value = './post.php';
                break;
            default:
                $rmattrs[] = $name;
        }
    }
    foreach ($rmattrs as $name) {
        $form->removeAttribute($name);
    }
    $form->setAttribute('accept-charset', $_conf['accept_charset']);

    // POST����l���Đݒ�
    foreach (array_combine($post_send_keys, $post_param_keys) as $key => $name) {
        if (array_key_exists($name, $_POST)) {
            $nodes = $xpath->query("./input[@type = 'hidden' and @name = '{$key}']");
            if ($nodes->length) {
                $elem = $nodes->item(0);
                if ($key != $name) {
                    $elem->setAttribute('name', $name);
                }
                $elem->setAttribute('value', mb_convert_encoding($_POST[$name], 'UTF-8', 'CP932'));
            }
        }
    }

    // �e��B���p�����[�^��ǉ�
    $hidden = $doc->createElement('input');
    $hidden->setAttribute('type', 'hidden');

    // rep2���g�p����ϐ�����1
    foreach (array('host', 'popup', 'rescount', 'ttitle_en') as $name) {
        $elem = $hidden->cloneNode();
        $elem->setAttribute('name', $name);
        $elem->setAttribute('value', $$name);
        $form->appendChild($elem);
    }

    // rep2���g�p����ϐ�����2
    foreach ($post_optional_keys as $name) {
        if (array_key_exists($name, $_POST)) {
            $elem = $hidden->cloneNode();
            $elem->setAttribute('name', $name);
            $elem->setAttribute('value', mb_convert_encoding($_POST[$name], 'UTF-8', 'CP932'));
            $form->appendChild($elem);
        }
    }

    // POST�悪subbbs.cgi
    if ($subbbs !== false) {
        $elem = $hidden->cloneNode();
        $elem->setAttribute('name', 'sub');
        $elem->setAttribute('value', $subbbs);
        $form->appendChild($elem);
    }

    // �\�[�X�R�[�h�␳
    if (!empty($_POST['fix_source'])) {
        $elem = $hidden->cloneNode();
        $elem->setAttribute('name', 'fix_source');
        $elem->setAttribute('value', '1');
        $form->appendChild($elem);
    }

    // �����r���[�w��
    if ($_conf['b'] != $_conf['client_type']) {
        $elem = $hidden->cloneNode();
        $elem->setAttribute('name', 'b');
        $elem->setAttribute('value', $_conf['b']);
        $form->appendChild($elem);
    }

    // Cookie�m�F�t���O
    $elem = $hidden->cloneNode();
    $elem->setAttribute('name', 'p2_post_confirm_cookie');
    $elem->setAttribute('value', '1');
    $form->appendChild($elem);

    // �G���R�[�f�B���O����̃q���g
    $hidden->setAttribute('name', '_hint');
    $hidden->setAttribute('value', mb_convert_encoding($_conf['detect_hint'], 'UTF-8', 'CP932'));
    $form->insertBefore($hidden, $form->firstChild);

    // �w�b�_�ɗv�f��ǉ�
    if (!$_conf['ktai']) {
        $skin_q = str_replace('&amp;', '&', $skin_en);
        $link = $doc->createElement('link');
        $link->setAttribute('rel', 'stylesheet');
        $link->setAttribute('type', 'text/css');
        $link->setAttribute('href', "css.php?css=style&skin={$skin_q}");
        $link = $head->appendChild($link)->cloneNode();
        $link->setAttribute('href', "css.php?css=post&skin={$skin_q}");
        $head->appendChild($link);

        if ($popup) {
            $mado_okisa = explode(',', $STYLE['post_pop_size']);
            $script = $doc->createElement('script');
            $script->setAttribute('type', 'text/javascript');
            $head->appendChild($script)->appendChild($doc->createCDATASection(
                sprintf('resizeTo(%d,%d);', $mado_okisa[0], $mado_okisa[1] + 200)
            ));
        }
    }

    // �\���C��
    // li�v�f�𒼐ڂ̎q�v�f�Ƃ��Ċ܂܂Ȃ�ul�v�f��blockquote�v�f�Œu��
    // DOMNodeList�̃C�e���[�V�����ƁA����Ɋ܂܂��m�[�h�̍폜�͕ʂɍs��
    $nodes = array();
    foreach ($xpath->query('.//ul[count(./li)=0]', $body) as $node) {
        $nodes[] = $node;
    }
    foreach ($nodes as $node) {
        $children = array();
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }
        $elem = $doc->createElement('blockquote');
        foreach ($children as $child) {
            $elem->appendChild($node->removeChild($child));
        }
        $node->parentNode->replaceChild($elem, $node);
    }

    // libxml2�����̕�����G���R�[�f�B���O��UTF-8�ł��邪�AsaveHTML()����
    // ���\�b�h�ł͓ǂݍ��񂾕����̃G���R�[�f�B���O�ɍĕϊ����ďo�͂����
    // (DOMDocument��encoding�v���p�e�B��ύX���邱�Ƃŕς���)
    echo $doc->saveHTML();
}

// }}}
// {{{ showUnexpectedResponse()

/**
 * �T�[�o����\�����Ȃ����X�|���X���Ԃ��Ă����|��\������
 *
 * @param   string $response    ���X�|���X�{�f�B
 * @param   int $line   �s�ԍ�
 * @return  void
 */
function showUnexpectedResponse($response, $line = null)
{
    echo '<html><head><title>p2 ERROR</title></head><body>';
    echo '<h1>p2 ERROR</h1><p>�T�[�o����̃��X�|���X���ςł��B';
    if (is_numeric($line)) {
        echo "({$line})";
    }
    echo '</p><pre>';
    echo htmlspecialchars($response, ENT_QUOTES);
    echo '</pre></body></html>';
}

// }}}
// {{{ getKeyInSubject()

/**
 *  subject����key���擾����
 *
 * @return string|false
 */
function getKeyInSubject()
{
    global $host, $bbs, $ttitle;

    $aSubjectTxt = new SubjectTxt($host, $bbs);

    foreach ($aSubjectTxt->subject_lines as $l) {
        if (strpos($l, $ttitle) !== false) {
            if (preg_match("/^([0-9]+)\.(dat|cgi)(,|<>)(.+) ?(\(|�i)([0-9]+)(\)|�j)/", $l, $matches)) {
                return $key = $matches[1];
            }
        }
    }

    return false;
}

// }}}
// {{{ tab2space()

/**
 * ���`���ێ����Ȃ���A�^�u���X�y�[�X�ɒu��������
 *
 * @param   string $in_str      �Ώە�����
 * @param   int $tabwidth       �^�u��
 * @param   string $linebreak   ���s����(��)
 * @return  string
 */
function tab2space($in_str, $tabwidth = 4, $linebreak = "\n")
{
    $out_str = '';
    $lines = preg_split('/\\r\\n|\\r|\\n/', $in_str);
    $ln = count($lines);
    $i = 0;

    while ($i < $ln) {
        $parts = explode("\t", rtrim($lines[$i]));
        $pn = count($parts);

        for ($j = 0; $j < $pn; $j++) {
            if ($j == 0) {
                $l = $parts[$j];
            } else {
                //$t = $tabwidth - (strlen($l) % $tabwidth);
                $sn = $tabwidth - (mb_strwidth($l) % $tabwidth); // UTF-8�ł��S�p��������2�ƃJ�E���g����
                for ($k = 0; $k < $sn; $k++) {
                    $l .= ' ';
                }
                $l .= $parts[$j];
            }
        }

        $out_str .= $l;
        if (++$i < $ln) {
            $out_str .= $linebreak;
        }
    }

    return $out_str;
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
