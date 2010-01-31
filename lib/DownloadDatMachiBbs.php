<?php
/**
 * rep2expack - �܂�BBS�pdat�_�E�����[�h�N���X
 */

// {{{ DownloadDatMachiBbs

/**
 * �܂�BBS��offlaw.cgi���琶dat���擾����
 *
 * @link http://www.machi.to/offlaw.txt
 */
class DownloadDatMachiBbs implements DownloadDatInterface
{
    // {{{ invoke()

    /**
     * �X���b�h��dat���_�E�����[�h���A�ۑ�����
     *
     * @param ThreadRead $thread
     * @return bool
     */
    static public function invoke(ThreadRead $thread)
    {
        global $_conf;

        // {{{ ����dat�̎擾���X�����K�����ǂ�����O�̂��߃`�F�b�N

        if (file_exists($thread->keydat)) {
            $dls = FileCtl::file_read_lines($thread->keydat);
            if (!$dls || count($dls) != $thread->gotnum) {
                // echo 'bad size!<br>';
                unlink($thread->keydat);
                $thread->gotnum = 0;
            }
        } else {
            $thread->gotnum = 0;
        }

        // }}}
        // {{{ offlaw.cgi����dat���_�E�����[�h

        $host = $thread->host;
        $bbs = $thread->bbs;
        $key = $thread->key;

        if ($thread->gotnum == 0) {
            $option = '';
            $append = false;
        } else {
            $option = sprintf('%d-', $thread->gotnum + 1);
            $append = true;
        }

        // http://[SERVER]/bbs/offlaw.cgi/[BBS]/[KEY]/[OPTION];
        $url = "http://{$host}/bbs/offlaw.cgi/{$bbs}/{$key}/{$option}";

        $tempfile = $thread->keydat . '.tmp';
        FileCtl::mkdirFor($tempfile);
        if ($append) {
            touch($tempfile, filemtime($thread->keydat));
        } elseif (file_exists($tempfile)) {
            unlink($tempfile);
        }
        $response = P2Util::fileDownload($url, $tempfile);

        if ($response->isError()) {
            if (304 != $response->code) {
                $thread->diedat = true;
            }
            return false;
        }

        // }}}
        // {{{ �_�E�����[�h�����e�s���`�F�b�N�����[�J��dat�ɏ�������

        $lines = file($tempfile);
        unlink($tempfile);

        if ($append) {
            $fp = fopen($thread->keydat, 'ab');
        } else {
            $fp = fopen($thread->keydat, 'wb');
        }
        if (!$fp) {
            p2die("cannot write file. ({$thread->keydat})");
        }
        flock($fp, LOCK_EX);

        foreach ($lines as $i => $line) {
            // �擾�ς݃��X�����C���N�������g
            $thread->gotnum++;
            // �s�𕪉��A�v�f���`�F�b�N (�����ɂ� === 6)
            $lar = explode('<>', rtrim($line));
            if (count($lar) >= 5) {
                // ���X�ԍ��͕ۑ����Ȃ��̂Ŏ��o��
                $resnum = (int)array_shift($lar);
                // ���X�ԍ��Ǝ擾�ς݃��X�����قȂ��Ă����炠�ځ[�񈵂�
                while ($thread->gotnum < $resnum) {
                    $abn = "���ځ[��<>���ځ[��<>���ځ[��<>���ځ[��<>";
                    if ($thread->gotnum == 1) {
                        $abn .= $lar[4]; // �X���^�C�g��
                    }
                    $abn .= "\n";
                    fwrite($fp, $abn);
                    $thread->gotnum++;
                }
                // �s����������
                fwrite($fp, implode('<>', $lar) . "\n");
            } else {
                $thread->gotnum--;
                $lineno = $i + 1;
                P2Util::pushInfoHtml("<p>rep2 info: dat�����G���[: line {$lineno} of {$url}.</p>");
                break;
            }
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        // }}}

        $thread->isonline = true;

        return true;
    }

    // }}}
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
