<?php
/**
 * rep2 - �܂�BBS�p�̊֐�
 */

require_once P2_LIB_DIR . '/FileCtl.php';

// {{{ machiDownload()

/**
 * �܂�BBS�� read.cgi ��ǂ�� dat�ɕۑ�����
 */
function machiDownload()
{
    global $aThread;

    $GLOBALS['machi_latest_num'] = '';

    // {{{ ����dat�̎擾���X�����K�����ǂ�����O�̂��߃`�F�b�N
    if (file_exists($aThread->keydat)) {
        $dls = FileCtl::file_read_lines($aThread->keydat);
        if (!$dls || sizeof($dls) != $aThread->gotnum) {
            // echo 'bad size!<br>';
            unlink($aThread->keydat);
            $aThread->gotnum = 0;
        }
    } else {
        $aThread->gotnum = 0;
    }
    // }}}

    if ($aThread->gotnum == 0) {
        $file_append = false;
        $START = 1;
    } else {
        $file_append = true;
        $START = $aThread->gotnum + 1;
    }

    // �܂�BBS
    $machiurl = "http://{$aThread->host}/bbs/read.cgi?BBS={$aThread->bbs}&KEY={$aThread->key}&START={$START}";

    $tempfile = $aThread->keydat.'.html.temp';

    FileCtl::mkdir_for($tempfile);
    $machiurl_res = P2Util::fileDownload($machiurl, $tempfile);

    if ($machiurl_res->isError()) {
        $aThread->diedat = true;
        return false;
    }

    $mlines = FileCtl::file_read_lines($tempfile);

    // �ꎞ�t�@�C�����폜����
    unlink($tempfile);

    // �i�܂�BBS�j<html>error</html>
    if (trim($mlines[0]) == '<html>error</html>') {
        $aThread->getdat_error_msg_ht .= 'error';
        $aThread->diedat = true;
        return false;
    }

    // {{{ DAT����������
    if ($mdatlines = machiHtmltoDatLines($mlines)) {

        $file_append = ($file_append) ? FILE_APPEND : 0;

        $cont = '';
        for ($i = $START; $i <= $GLOBALS['machi_latest_num']; $i++) {
            if ($mdatlines[$i]) {
                $cont .= $mdatlines[$i];
            } else {
                $cont .= "���ځ[��<>���ځ[��<>���ځ[��<>���ځ[��<>\n";
            }
        }
        if (FileCtl::file_write_contents($aThread->keydat, $cont, $file_append) === false) {
            p2die('cannot write file.');
        }
    }
    // }}}

    $aThread->isonline = true;

    return true;
}

// }}}
// {{{ machiHtmltoDatLines()

/**
 * �܂�BBS��read.cgi�œǂݍ���HTML��dat�ɕϊ�����
 *
 * @see machiDownload()
 */
function machiHtmltoDatLines($mlines)
{
    if (!$mlines) {
        $retval = false;
        return $retval;
    }
    $mdatlines = "";

    foreach ($mlines as $ml) {
        $ml = rtrim($ml);
        if (!$tuduku) {
            unset($order, $mail, $name, $date, $ip, $body);
        }

        if ($tuduku) {
            if (preg_match('{^ \\]</font><br><dd>(.*) <br><br>$}i', $ml, $matches)) {
                $body = $matches[1];
            } else {
                unset($tuduku);
                continue;
            }
        } elseif (preg_match('{^<dt>(?:<a[^>]+?>)?(\\d+)(?:</a>)? ���O�F(<font color="#.+?">|<a href="mailto:(.*)">)<b> (.+) </b>(</font>|</a>) ���e���F (.+)<br><dd>(.*) <br><br>$}i', $ml, $matches)) {
            $order = $matches[1];
            $mail = $matches[3];
            $name = preg_replace('{<font color="?#.+?"?>(.+)</font>}i', '\\1', $matches[4]);
            $date = $matches[6];
            $body = $matches[7];
        } elseif (preg_match('{<title>(.*)</title>}i', $ml, $matches)) {
            $mtitle = $matches[1];
            continue;
        } elseif (preg_match('{^<dt>(?:<a[^>]+?>)?(\\d+)(?:</a>)? ���O�F(<font color="#.+?">|<a href="mailto:(.*)">)<b> (.+) </b>(</font>|</a>) ���e���F (.+) <font size=1>\\[ ?(.*)$}i', $ml, $matches)) {
            $order = $matches[1];
            $mail = $matches[3];
            $name = preg_replace('{<font color="?#.+?"?>(.+)</font>}i', '\\1', $matches[4]);
            $date = $matches[6];
            $ip = $matches[7];
            $tuduku = true;
            continue;
        }

        if ($ip) {
            $date = "$date [$ip]";
        }

        // �����N�O��
        $body = preg_replace('{<a href="(https?://[-_.!~*\'()0-9A-Za-z;/?:@&=+\$,%#]+)" target="_blank">(https?://[-_.!~*\'()0-9A-Za-z;/?:@&=+\$,%#]+)</a>}i', '$1', $body);

        if ($order == 1) {
            $datline = $name.'<>'.$mail.'<>'.$date.'<>'.$body.'<>'.$mtitle."\n";
        } else {
            $datline = $name.'<>'.$mail.'<>'.$date.'<>'.$body.'<>'."\n";
        }
        $mdatlines[$order] = $datline;
        if ($order > $GLOBALS['machi_latest_num']) {
            $GLOBALS['machi_latest_num'] = $order;
        }
        unset($tuduku);
    }

    return $mdatlines;
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
