<?php
/**
 * rep2 - 2ch���O�C��
 */

require_once './conf/conf.inc.php';
require_once P2_LIB_DIR . '/FileCtl.php';
require_once P2_LIB_DIR . '/Wap.php';

// {{{ login2ch()

/**
 * 2ch ID�Ƀ��O�C������
 *
 * @return  string|false  ����������2ch SID��Ԃ�
 */
function login2ch()
{
    global $_conf;

    // 2ch��ID, PW�ݒ��ǂݍ���
    if ($array = P2Util::readIdPw2ch()) {
        list($login2chID, $login2chPW, $autoLogin2ch) = $array;

    } else {
        P2Util::pushInfoHtml("<p>p2 error: 2ch���O�C���̂��߂�ID�ƃp�X���[�h��o�^���ĉ������B[<a href=\"login2ch.php\" target=\"subject\">2ch���O�C���Ǘ�</a>]</p>");
        return false;
    }

    $auth2ch_url    = 'https://2chv.tora3.net/futen.cgi';
    $postf          = 'ID=' . $login2chID . '&PW=' . $login2chPW;
    $x_2ch_ua       = 'X-2ch-UA: ' . $_conf['p2ua'];
    $dolib2ch       = 'DOLIB/1.00';
    $tempfile       = $_conf['tmp_dir'] . '/p2temp.php';

    // �O�̂��߂��炩����temp�t�@�C�����������Ă���
    if (file_exists($tempfile)) {
        unlink($tempfile);
    }

    $curl_msg = '';

    // �܂���fsockopen��SSL�ڑ�����
    // ������PHP�R���p�C������OpenSSL�T�|�[�g���L���ɂȂ��Ă��Ȃ��Ɨ��p�ł����A
    // DSO�Łiopenssl.{so,dll}���j�ł̓G���[���o��B
    // @see http://jp.php.net/manual/ja/function.fsockopen.php
    if ($_conf['precede_openssl']) {
        if (!extension_loaded('openssl')) {
            $curl_msg .= "�uPHP��openssl�v�͎g���Ȃ��悤�ł�";
        } elseif (!$r = getAuth2chWithOpenSSL($login2chID, $login2chPW, $auth2ch_url, $x_2ch_ua, $dolib2ch)) {
            $curl_msg .= "�uPHP��openssl�v�Ŏ��s���s�B";
        }
    }

    if (empty($r)) {

        // �R�}���hCURL�D��
        if (empty($_conf['precede_phpcurl'])) {
            if (!$r = getAuth2chWithCommandCurl($login2chID, $login2chPW, $tempfile, $auth2ch_url, $x_2ch_ua, $dolib2ch)) {
                $curl_msg .= "�usystem��curl�R�}���h�v�Ŏ��s���s�B";
                if (!extension_loaded('curl')) {
                    $curl_msg .= "�uPHP��curl�v�͎g���Ȃ��悤�ł�";
                } elseif (!$r = getAuth2chWithPhpCurl($tempfile, $auth2ch_url, $x_2ch_ua, $dolib2ch, $postf)) {
                    $curl_msg .= "�uPHP��curl�v�Ŏ��s���s�B";
                }
            }

        // PHP CURL�D��
        } else {
            if (!extension_loaded('curl')) {
                $curl_msg .= "�uPHP��curl�v�͎g���Ȃ��悤�ł�";
            } elseif (!$r = getAuth2chWithPhpCurl($tempfile, $auth2ch_url, $x_2ch_ua, $dolib2ch, $postf)) {
                $curl_msg .= "�uPHP��curl�v�Ŏ��s���s�B";
            }

            if (empty($r)) {
                if (!$r = getAuth2chWithCommandCurl($login2chID, $login2chPW, $tempfile, $auth2ch_url, $x_2ch_ua, $dolib2ch)) {
                    $curl_msg .= "�usystem��curl�R�}���h�v�Ŏ��s���s�B";
                }
            }
        }

    }

    // �ڑ����s�Ȃ��
    if (empty($r)) {
        if (file_exists($_conf['idpw2ch_php'])) { unlink($_conf['idpw2ch_php']); }
        if (file_exists($_conf['sid2ch_php']))  { unlink($_conf['sid2ch_php']); }

        P2Util::pushInfoHtml("<p>p2 info: 2�����˂�ւ́�ID���O�C�����s���ɂ́Asystem��curl�R�}���h���g�p�\�ł��邩�APHP��<a href=\"http://www.php.net/manual/ja/ref.curl.php\">CURL�֐�</a>���L���ł���K�v������܂��B</p>");

        P2Util::pushInfoHtml("<p>p2 error: 2ch���O�C�������Ɏ��s���܂����B{$curl_msg}</p>");
        return false;
    }

    // temp�t�@�C���͂����Ɏ̂Ă�
    if (file_exists($tempfile)) { unlink($tempfile); }

    $r = rtrim($r);

    // ����
    if (preg_match('/SESSION-ID=(.+?):(.+)/', $r, $matches)) {
        $uaMona = $matches[1];
        $SID2ch = $matches[1] . ':' . $matches[2];
    } else {
        if (file_exists($_conf['sid2ch_php'])) { unlink($_conf['sid2ch_php']); }
        P2Util::pushInfoHtml("<p>p2 error: 2ch�����O�C���ڑ��Ɏ��s���܂����B</p>");
        return false;
    }

    // �F�؏ƍ����s�Ȃ�
    if ($uaMona == 'ERROR') {
        file_exists($_conf['idpw2ch_php']) and unlink($_conf['idpw2ch_php']);
        file_exists($_conf['sid2ch_php']) and unlink($_conf['sid2ch_php']);
        P2Util::pushInfoHtml("<p>p2 error: 2ch�����O�C����SESSION-ID�̎擾�Ɏ��s���܂����BID�ƃp�X���[�h���m�F�̏�A���O�C���������ĉ������B</p>");
        return false;
    }

    //echo $r;//

    // SID�̋L�^�ێ�
    $cont = sprintf('<?php $uaMona = %s; $SID2ch = %s;', var_export($uaMona, true), var_export($SID2ch, true));
    FileCtl::make_datafile($_conf['sid2ch_php'], $_conf['pass_perm']);
    if (false === file_put_contents($_conf['sid2ch_php'], $cont, LOCK_EX)) {
        P2Util::pushInfoHtml("<p>p2 Error: {$_conf['sid2ch_php']} ��ۑ��ł��܂���ł����B���O�C���o�^���s�B</p>");
        return false;
    }

    return $SID2ch;
}

// }}}
// {{{ getAuth2chWithCommandCurl()

/**
 * system�R�}���h��curl�����s���āA2ch���O�C����SID�𓾂�
 *
 * @return  string|false
 */
function getAuth2chWithCommandCurl($login2chID, $login2chPW, $tempfile, $auth2ch_url, $x_2ch_ua, $dolib2ch)
{
    global $_conf;

    $curlrtn = 1;

    // proxy�̐ݒ�
    if ($_conf['proxy_use']) {
        $with_proxy = " -x ".$_conf['proxy_host'].":".$_conf['proxy_port'];
    } else {
        $with_proxy = '';
    }

    // �usystem�R�}���h��curl�v�i�ؖ������؂���j�����s
    $curlcmd = "curl -H \"{$x_2ch_ua}\" -A {$dolib2ch} -d ID={$login2chID} -d PW={$login2chPW} -o {$tempfile}{$with_proxy} {$auth2ch_url}";
    system($curlcmd, $curlrtn);

    // �usystem�R�}���h��curl�v�i�ؖ������؂���j�Ŗ����������Ȃ�A�i�ؖ������؂Ȃ��j�ōă`�������W
    if ($curlrtn != 0) {
        $curlcmd = "curl -H \"{$x_2ch_ua}\" -A {$dolib2ch} -d ID={$login2chID} -d PW={$login2chPW} -o {$tempfile}{$with_proxy} -k {$auth2ch_url}";
        system($curlcmd, $curlrtn);
    }

    if ($curlrtn == 0) {
        if ($r = file_get_contents($tempfile)) {
            return $r;
        }
    }

    return false;
}

// }}}
// {{{ getAuth2chWithPhpCurl()

/**
 * PHP��curl��2ch���O�C����SID�𓾂�
 *
 * @return  string|false
 */
function getAuth2chWithPhpCurl($tempfile, $auth2ch_url, $x_2ch_ua, $dolib2ch, $postf)
{
    global $_conf;

    // PHP��CURL���g����Ȃ�A����Ń`�������W
    if (extension_loaded('curl')) {
        // �uPHP��curl�v�i�ؖ������؂���j�Ŏ��s
        execAuth2chWithPhpCurl($tempfile, $auth2ch_url, $x_2ch_ua, $dolib2ch, $postf, true);
        // �uPHP��curl�v�i�ؖ������؂���j�Ŗ����Ȃ�A�uPHP��curl�v�i�ؖ������؂Ȃ��j�ōă`�������W
        clearstatcache();
        if (!file_exists($tempfile) || !filesize($tempfile)) {
            execAuth2chWithPhpCurl($tempfile, $auth2ch_url, $x_2ch_ua, $dolib2ch, $postf, false);
        }
        if ($r = file_get_contents($tempfile)) {
            return $r;
        }

    }

    return false;
}

// }}}
// {{{ execAuth2chWithPhpCurl()

/**
 * PHP��curl�����s���āA�t�@�C���Ƀf�[�^��ۑ�����
 *
 * @return  boolean
 */
function execAuth2chWithPhpCurl($tempfile, $auth2ch_url, $x_2ch_ua, $dolib2ch, $postf, $withk = false)
{
    global $_conf;

    if (!$ch = curl_init()) {
        return false;
    }
    if (!$fp = fopen($tempfile, 'wb')) {
        return false;
    }
    @flock($fp, LOCK_EX);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_URL, $auth2ch_url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array($x_2ch_ua));
    curl_setopt($ch, CURLOPT_USERAGENT, $dolib2ch);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postf);
    // �ؖ����̌��؂����Ȃ��Ȃ�
    if ($withk) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    }
    // �v���L�V�̐ݒ�
    if ($_conf['proxy_use']) {
        curl_setopt($ch, CURLOPT_PROXY, $_conf['proxy_host'] . ':' . $_conf['proxy_port']);
    }
    curl_exec($ch);
    curl_close($ch);
    @flock($fp, LOCK_UN);
    fclose($fp);

    return true;
}

// }}}
// {{{ getAuth2chWithOpenSSL()

/**
 * fsockopen��SSL�ڑ�����2ch���O�C����SID�𓾂�i�ؖ������؂Ȃ��j
 *
 * @return  string|false
 */
function getAuth2chWithOpenSSL($login2chID, $login2chPW, $auth2ch_url, $x_2ch_ua, $dolib2ch)
{
    global $_conf;

    $wap_ua = new WapUserAgent;
    $wap_ua->setAgent($dolib2ch);
    $wap_ua->setTimeout($_conf['fsockopen_time_limit']);

    $wap_req = new WapRequest;
    $wap_req->setMethod('POST');
    $wap_req->post['ID'] = $login2chID;
    $wap_req->post['PW'] = $login2chPW;
    $wap_req->setHeaders($x_2ch_ua . "\r\n");
    $wap_req->setUrl($auth2ch_url);
    if ($_conf['proxy_use']) {
        $wap_req->setProxy($_conf['proxy_host'], $_conf['proxy_port']);
    }

    // futen.cgi�̎d�l���A����Ƃ��e�X�g����PHP�����������̂��A
    // �Ƃɂ��������O�C���ł�POST���镶�����URL�G���R�[�h���Ă���Ǝ��s����
    $wap_req->setNoUrlencodePost(true);

    $wap_res = $wap_ua->request($wap_req);

    //P2Util::pushInfoHtml(Var_Dump::display(array($wap_ua, $wap_req, $wap_res), TRUE));

    if (!$wap_res || $wap_res->isError()) {
        return false;
    }

    return $wap_res->content;
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
