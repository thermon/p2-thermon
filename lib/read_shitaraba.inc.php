<?php
/**
 * rep2 - �������JBBS jbbs.livedoor.jp �p�̊֐�
 *
 * �e��BBS�ɑΉ��ł���v���t�@�C���N���X�݂����Ȃ̂���肽�����̂��B�B aki
 */

// {{{ shitarabaDownload()

/**
 * �������JBBS�� rawmode.cgi ��ǂ�ŁAdat�ɕۑ�����i2ch���ɐ��`�j
 */
function shitarabaDownload()
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

    // JBBS@�������
    if (P2Util::isHostJbbsShitaraba($aThread->host)) {
        // ������΂�livedoor�ړ]�ɑΉ��B�Ǎ����livedoor�Ƃ���B
        $host = P2Util::adjustHostJbbs($aThread->host);
        list($host, $category, ) = explode('/', $host);
        $machiurl = "http://{$host}/bbs/rawmode.cgi/{$category}/{$aThread->bbs}/{$aThread->key}/{$START}-";
    }

    $tempfile = $aThread->keydat.'.dat.temp';

    FileCtl::mkdir_for($tempfile);
    $machiurl_res = P2Util::fileDownload($machiurl, $tempfile);

    if ($machiurl_res->isError()) {
        $aThread->diedat = true;
        return false;
    }

    // {{{ ������΂Ȃ�EUC��SJIS�ɕϊ�
    if (P2Util::isHostJbbsShitaraba($aThread->host)) {
        $temp_data = FileCtl::file_read_contents($tempfile);
        $temp_data = mb_convert_encoding($temp_data, 'CP932', 'CP51932');
        if (FileCtl::file_write_contents($tempfile, $temp_data) === false) {
            p2die('cannot write file.');
        }
    }
    // }}}

    $mlines = FileCtl::file_read_lines($tempfile);

    // �ꎞ�t�@�C�����폜����
    unlink($tempfile);

    // ��rawmode.cgi�ł͂���͏o�Ȃ����낤
    /*
    // �iJBBS�jERROR!: �X���b�h������܂���B�ߋ����O�q�ɂɂ�����܂���B
    if (stripos($mlines[0], 'ERROR') === 0) {
        $aThread->getdat_error_msg_ht .= $mlines[0];
        $aThread->diedat = true;
        return false;
    }
    */

    // {{{ DAT����������
    if ($mdatlines = shitarabaDatTo2chDatLines($mlines)) {

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
// {{{ shitarabaDatTo2chDatLines()

/**
 * �������BBS�� rawmode.cgi �œǂݍ���DAT��2ch��dat�ɕϊ�����
 *
 * @see shitarabaDownload()
 */
function shitarabaDatTo2chDatLines($mlines)
{
    if (!$mlines) {
        $retval = false;
        return $retval;
    }
    $mdatlines = "";

    foreach ($mlines as $ml) {
        $ml = rtrim($ml);

        // 1<><font color=#FF0000>�Ǘ��l</font><>sage<>2005/04/06(��) 21:44:54<>Pandemonium�����X���b�h�ł��B���X���́@<a href="/bbs/read.cgi/game/10109/1112791494/950" target="_blank">&gt;&gt;950</a> �����ӂ������Đ\�����鎖�B<br><br>��5W1H�̖@���𖳎��������̂͑S�ĕ��u�ł��肢���܂��B<br>���S���E���R�S���N���E����E�x��E�`�`�͕��u�ŁB�����ł��Ȃ��l�͓��ނƂ݂Ȃ���܂��B<br>���E�l�ɑ΂���S���s�ׁE�e�W���u�̒@���Ȃǐ��̃X���ł��肢���܂��B<br>�������s�ׂ̕��R�e�͊��S���u�ŁB���X�Ƃ����a��^���Ȃ��悤�ɂ��܂��傤�B<br>���ȏ�𓥂܂��Ĉ��Y�̓x���߂���ꍇ�͍폜�˗��X���ɂ��肢���܂��B<br><br>[�O�X��]�y�t�[���|�b�v�zPandemonium(20)Part.41�y���ꂷ�珬���z<br>http://jbbs.livedoor.jp/bbs/read.cgi/game/10109/1109905935/<>�y�����́zPandemonium(20)Part.42�y�ʂ̔���|�z<>EM04DJXI

        $data = explode('<>', $ml);

        $order = $data[0];
        $name = $data[1];
        $mail = $data[2];
        $date = $data[3];
        $body = $data[4];
        if ($order == 1) {
            $mtitle = $data[5];
        }
        if ($data[6]) {
            $date .= " ID:".$data[6];
        }

        /* rawmode.cgi �ł͂���͂Ȃ�
        // �������JBBS jbbs.livedoor.com ��link.cgi������
        // <a href="http://jbbs.livedoor.jp/bbs/link.cgi?url=http://dempa.2ch.net/gazo/free/img-box/img20030424164949.gif" target="_blank">http://dempa.2ch.net/gazo/free/img-box/img20030424164949.gif</a>
        $body = preg_replace('{<a href="(?:http://jbbs\.(?:shitaraba\.com|livedoor\.(?:com|jp)))?/bbs/link\.cgi\?url=([^"]+)" target="_blank">([^><]+)</a>}i', '$1', $body);
        */

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
