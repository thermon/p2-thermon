<?php
/**
 * rep2 - �X���b�h ���[�h �N���X
 */

// {{{ ThreadRead

/**
 * �X���b�h���[�h�N���X
 */
class ThreadRead extends Thread
{
    // {{{ properties

    public $datlines; // dat����ǂݍ��񂾃��C�����i�[����z��

    public $resrange; // array('start' => i, 'to' => i, 'nofirst' => bool)

    public $onbytes; // �T�[�o����擾����dat�T�C�Y
    public $diedat; // �T�[�o����dat�擾���悤�Ƃ��Ăł��Ȃ���������true���Z�b�g�����
    public $onthefly; // ���[�J����dat�ۑ����Ȃ��I���U�t���C�ǂݍ��݂Ȃ�true

    public $idp;     // ���X�ԍ����L�[�AID�̑O�̕����� ("ID:", " " ��) ��l�Ƃ���A�z�z��
    public $ids;     // ���X�ԍ����L�[�AID��l�Ƃ���A�z�z��
    public $idcount; // ID���L�[�A�o���񐔂�l�Ƃ���A�z�z��

    public $getdat_error_msg_ht; // dat�擾�Ɏ��s�������ɕ\������郁�b�Z�[�W�iHTML�j

    public $old_host;  // �z�X�g�ړ]���o���A�ړ]�O�̃z�X�g��ێ�����

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     */
    public function __construct()
    {
        parent::__construct();
        $this->getdat_error_msg_ht = "";
    }

    // }}}
    // {{{ downloadDat()

    /**
     * DAT���_�E�����[�h����
     */
    public function downloadDat()
    {
        global $_conf;

        // �܂�BBS
        if (P2Util::isHostMachiBbs($this->host)) {
            require_once P2_LIB_DIR . '/read_machibbs.inc.php';
            machiDownload();
        // JBBS@�������
        } elseif (P2Util::isHostJbbsShitaraba($this->host)) {
            require_once P2_LIB_DIR . '/read_shitaraba.inc.php';
            shitarabaDownload();

        // 2ch�n
        } else {
            $this->getDatBytesFromLocalDat(); // $aThread->length ��set

            // 2ch bbspink���ǂ�
            if (P2Util::isHost2chs($this->host) && !empty($_GET['maru'])) {
                // ���O�C�����ĂȂ���� or ���O�C����A24���Ԉȏ�o�߂��Ă����玩���ă��O�C��
                if (!file_exists($_conf['sid2ch_php']) ||
                    !empty($_REQUEST['relogin2ch']) ||
                    (filemtime($_conf['sid2ch_php']) < time() - 60*60*24))
                {
                    require_once P2_LIB_DIR . '/login2ch.inc.php';
                    if (!login2ch()) {
                        $this->getdat_error_msg_ht .= $this->get2chDatError();
                        $this->diedat = true;
                        return false;
                    }
                }

                include $_conf['sid2ch_php'];
                $this->_downloadDat2chMaru($uaMona, $SID2ch);

            // 2ch bbspink �����^�|�ǂ�
            } elseif (P2Util::isHost2chs($this->host) && !empty($_GET['moritapodat']) &&
                      $_conf['p2_2ch_mail'] && $_conf['p2_2ch_pass'])
            {
                if (!array_key_exists('csrfid', $_GET) ||
                    $this->_getCsrfIdForMoritapoDat() != $_GET['csrfid'])
                {
                    p2die('�s���ȃ��N�G�X�g�ł�');
                }
                $this->_downloadDat2chMoritapo();

            // 2ch�̉ߋ����O�q�ɓǂ�
            } elseif (!empty($_GET['kakolog']) && !empty($_GET['kakoget'])) {
                if ($_GET['kakoget'] == 1) {
                    $ext = '.dat.gz';
                } elseif ($_GET['kakoget'] == 2) {
                    $ext = '.dat';
                }
                $this->_downloadDat2chKako(urldecode($_GET['kakolog']), $ext);

            // 2ch or 2ch�݊�
            } else {
                // DAT������DL����
                $this->_downloadDat2ch($this->length);
            }

        }
    }

    // }}}
    // {{{ _downloadDat2ch()

    /**
     * �W�����@�� 2ch�݊� DAT �������_�E�����[�h����
     *
     * @return mix �擾�ł������A�X�V���Ȃ������ꍇ��true��Ԃ�
     */
    protected function _downloadDat2ch($from_bytes)
    {
        global $_conf, $_info_msg_ht;
        global $debug;

        if (!($this->host && $this->bbs && $this->key)) {
            return false;
        }

        $from_bytes = intval($from_bytes);

        if ($from_bytes == 0) {
            $zero_read = true;
        } else {
            $zero_read = false;
            $from_bytes = $from_bytes - 1;
        }

        $method = 'GET';

        $url = "http://{$this->host}/{$this->bbs}/dat/{$this->key}.dat";
        //$url="http://news2.2ch.net/test/read.cgi?bbs=newsplus&key=1038486598";

        $purl = parse_url($url); // URL����
        if (isset($purl['query'])) { // �N�G���[
            $purl['query'] = '?' . $purl['query'];
        } else {
            $purl['query'] = '';
        }

        // �v���L�V
        if ($_conf['proxy_use']) {
            $send_host = $_conf['proxy_host'];
            $send_port = $_conf['proxy_port'];
            $send_path = $url;
        } else {
            $send_host = $purl['host'];
            $send_port = isset($purl['port']) ? $purl['port'] : 80;
            $send_path = $purl['path'] . $purl['query'];
        }

        if (!$send_port) {
            $send_port = 80; // �f�t�H���g��80
        }

        $request = "{$method} {$send_path} HTTP/1.0\r\n";
        $request .= "Host: {$purl['host']}\r\n";
        $request .= "Accept: */*\r\n";
        //$request .= "Accept-Charset: Shift_JIS\r\n";
        //$request .= "Accept-Encoding: gzip, deflate\r\n";
        $request .= "Accept-Language: ja, en\r\n";
        $request .= "User-Agent: Monazilla/1.00 ({$_conf['p2ua']})\r\n";
        if (!$zero_read) {
            $request .= "Range: bytes={$from_bytes}-\r\n";
        }
        $request .= "Referer: http://{$purl['host']}/{$this->bbs}/\r\n";

        if ($this->modified) {
            $request .= "If-Modified-Since: ".$this->modified."\r\n";
        }

        // Basic�F�ؗp�̃w�b�_
        if (isset($purl['user']) && isset($purl['pass'])) {
            $request .= "Authorization: Basic ".base64_encode($purl['user'].":".$purl['pass'])."\r\n";
        }

        $request .= "Connection: Close\r\n";

        $request .= "\r\n";

        // WEB�T�[�o�֐ڑ�
        $fp = fsockopen($send_host, $send_port, $errno, $errstr, $_conf['fsockopen_time_limit']);
        if (!$fp) {
            $url_t = P2Util::throughIme($url);
            $_info_msg_ht .= "<p>�T�[�o�ڑ��G���[: {$errstr} ({$errno})<br>p2 info: <a href=\"{$url_t}\"{$_conf['ext_win_target_at']}>{$url}</a> �ɐڑ��ł��܂���ł����B</p>";
            $this->diedat = true;
            return false;
        }
        $wr = "";
        fputs($fp, $request);
        $start_here = false;
        while (!feof($fp)) {

            if ($start_here) {

                if ($code == "200" || $code == "206") {

                    while (!feof($fp)) {
                        $wr .= fread($fp, 4096);
                    }

                    // �����̉��s�ł��ځ[��`�F�b�N
                    if (!$zero_read) {
                        if (substr($wr, 0, 1) != "\n") {
                            //echo "���ځ[�񌟏o";
                            fclose($fp);
                            unset($this->onbytes);
                            unset($this->modified);
                            return $this->_downloadDat2ch(0); // ���ځ[�񌟏o�B�S����蒼���B
                        }
                        $wr = substr($wr, 1);
                    }
                    FileCtl::make_datafile($this->keydat, $_conf['dat_perm']);

                    $file_append = ($zero_read) ? 0 : FILE_APPEND;

                    if (FileCtl::file_write_contents($this->keydat, $wr, $file_append) === false) {
                        p2die('cannot write file.');
                    }

                    //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection("dat_size_check");
                    // �擾��T�C�Y�`�F�b�N
                    if ($zero_read == false && $this->onbytes) {
                        $this->getDatBytesFromLocalDat(); // $aThread->length ��set
                        if ($this->onbytes != $this->length) {
                            fclose($fp);
                            unset($this->onbytes);
                            unset($this->modified);
                            $_info_msg_ht .= "p2 info: $this->onbytes/$this->length �t�@�C���T�C�Y���ςȂ̂ŁAdat���Ď擾<br>";
                            //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection("dat_size_check");
                            return $this->_downloadDat2ch(0); //dat�T�C�Y�͕s���B�S����蒼���B

                        // �T�C�Y�������Ȃ炻�̂܂�
                        } elseif ($this->onbytes == $this->length) {
                            fclose($fp);
                            $this->isonline = true;
                            //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('dat_size_check');
                            return true;
                        }
                    }
                    //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('dat_size_check');

                // �X���b�h���Ȃ��Ɣ��f
                } else {
                    fclose($fp);
                    return $this->_downloadDat2chNotFound();
                }

            } else {
                $l = fgets($fp, 32800);
                // ex) HTTP/1.1 304 Not Modified
                if (preg_match("/^HTTP\/1\.\d (\d+) (.+)\r\n/", $l, $matches)) {
                    $code = $matches[1];

                    if ($code == "200" || $code == "206") { // Partial Content
                        ;

                    } elseif ($code == "302") { // Found

                        // �z�X�g�̈ړ]��ǐ�
                        $new_host = BbsMap::getCurrentHost($this->host, $this->bbs);
                        if ($new_host != $this->host) {
                            fclose($fp);
                            $this->old_host = $this->host;
                            $this->host = $new_host;
                            return $this->_downloadDat2ch($from_bytes);
                        } else {
                            fclose($fp);
                            return $this->_downloadDat2chNotFound();
                        }

                    } elseif ($code == "304") { // Not Modified
                        fclose($fp);
                        $this->isonline = true;
                        return "304 Not Modified";

                    } elseif ($code == "416") { // Requested Range Not Satisfiable
                        //echo "���ځ[�񌟏o";
                        fclose($fp);
                        unset($this->onbytes);
                        unset($this->modified);
                        return $this->_downloadDat2ch(0); // ���ځ[�񌟏o�B�S����蒼���B

                    } else {
                        fclose($fp);
                        return $this->_downloadDat2chNotFound();
                    }
                }

                if ($zero_read) {
                    if (preg_match("/^Content-Length: ([0-9]+)/", $l, $matches)) {
                        $this->onbytes = $matches[1];
                    }
                } else {

                    if (preg_match("/^Content-Range: bytes ([^\/]+)\/([0-9]+)/", $l, $matches)) {
                        $this->onbytes = $matches[2];
                    }

                }

                if (preg_match("/^Last-Modified: (.+)\r\n/", $l, $matches)) {
                    //echo $matches[1]."<br>"; //debug
                    $this->modified = $matches[1];

                } elseif ($l == "\r\n") {
                    $start_here = true;
                }
            }
        }
        fclose($fp);
        $this->isonline = true;
        return true;
    }

    // }}}
    // {{{ _downloadDat2chNotFound()

    /**
     * 2ch DAT���_�E�����[�h�ł��Ȃ������Ƃ��ɌĂяo�����
     */
    protected function _downloadDat2chNotFound()
    {
        // 2ch, bbspink �Ȃ�read.cgi�Ŋm�F
        if (P2Util::isHost2chs($this->host)) {
            $this->getdat_error_msg_ht .= $this->get2chDatError();
        }
        $this->diedat = true;
        return false;
    }

    // }}}
    // {{{ _downloadDat2chMaru()

    /**
     * 2ch���p DAT���_�E�����[�h����
     *
     * @param string $uaMona
     * @param string $SID2ch
     * @return bool
     * @see lib/login2ch.inc.php
     */
    protected function _downloadDat2chMaru($uaMona, $SID2ch)
    {
        global $_conf;

        if (!($this->host && $this->bbs && $this->key && $this->keydat)) {
            return false;
        }

        //unset($datgz_attayo, $start_here, $isGzip, $done_gunzip, $marudatlines, $code);

        $method = 'GET';

        //  GET /test/offlaw.cgi?bbs=��&key=�X���b�h�ԍ�&sid=�Z�b�V����ID HTTP/1.1
        $url = "http://{$this->host}/test/offlaw.cgi/{$this->bbs}/{$this->key}/?raw=0.0&sid=";
        $url .= rawurlencode($SID2ch);

        $purl = parse_url($url); // URL����
        if (isset($purl['query'])) { // �N�G���[
            $purl['query'] = '?'.$purl['query'];
        } else {
            $purl['query'] = '';
        }

        // �v���L�V
        if ($_conf['proxy_use']) {
            $send_host = $_conf['proxy_host'];
            $send_port = $_conf['proxy_port'];
            $send_path = $url;
        } else {
            $send_host = $purl['host'];
            $send_port = $purl['port'];
            $send_path = $purl['path'].$purl['query'];
        }

        if (!$send_port) {
            $send_port = 80; // �f�t�H���g��80
        }

        $request = $method." ".$send_path." HTTP/1.0\r\n";
        $request .= "Host: ".$purl['host']."\r\n";
        $request .= "Accept-Encoding: gzip, deflate\r\n";
        //$request .= "Accept-Language: ja, en\r\n";
        $request .= "User-Agent: {$uaMona} ({$_conf['p2ua']})\r\n";
        //$request .= "X-2ch-UA: {$_conf['p2ua']}\r\n";
        //$request .= "Range: bytes={$from_bytes}-\r\n";
        $request .= "Connection: Close\r\n";
        /*
        if ($modified) {
            $request .= "If-Modified-Since: $modified\r\n";
        }
        */
        $request .= "\r\n";

        // WEB�T�[�o�֐ڑ�
        $fp = fsockopen($send_host, $send_port, $errno, $errstr, $_conf['fsockopen_time_limit']);
        if (!$fp) {
            $url_t = P2Util::throughIme($url);
            P2Util::pushInfoHtml("<p>�T�[�o�ڑ��G���[: {$errstr} ({$errno})<br>p2 info - <a href=\"{$url_t}\"{$_conf['ext_win_target_at']}>{$url}</a> �ɐڑ��ł��܂���ł����B</p>");
            $this->diedat = true;
            return false;
        }

        fputs($fp, $request);
        $body = '';
        $start_here = false;
        while (!feof($fp)) {

            if ($start_here) {

                if ($code == "200") {

                    while (!feof($fp)) {
                        $body .= fread($fp, 4096);
                    }

                    // gzip���k�Ȃ�
                    if ($isGzip) {
                        // gzip temp�t�@�C���ɕۑ�
                        $gztempfile = $this->keydat.".gz";
                        FileCtl::mkdir_for($gztempfile);
                        if (FileCtl::file_write_contents($gztempfile, $body) === false) {
                            p2die('cannot write file. downloadDat2chMaru()');
                        }

                        // PHP�ŉ𓀓ǂݍ���
                        if (extension_loaded('zlib')) {
                            $body = FileCtl::get_gzfile_contents($gztempfile);
                        // �R�}���h���C���ŉ�
                        } else {
                            // ���ɑ��݂���Ȃ�ꎞ�o�b�N�A�b�v�ޔ�
                            if (file_exists($this->keydat)) {
                                if (file_exists($this->keydat . ".bak")) {
                                    unlink($this->keydat . ".bak");
                                }
                                rename($this->keydat, $this->keydat . ".bak");
                            }
                            $rcode = 1;
                            // �𓀂���
                            system("gzip -d $gztempfile", $rcode);
                            // �𓀎��s�Ȃ�o�b�N�A�b�v��߂�
                            if ($rcode != 0) {
                                if (file_exists($this->keydat.".bak")) {
                                    if (file_exists($this->keydat)) {
                                        unlink($this->keydat);
                                    }
                                    rename($this->keydat.".bak", $this->keydat);
                                }
                                $this->getdat_error_msg_ht .= "<p>p2 info - 2�����˂�ߋ����O�q�ɂ���̃X���b�h��荞�݂́APHP��<a href=\"http://www.php.net/manual/ja/ref.zlib.php\">zlib�g�����W���[��</a>���Ȃ����Asystem��gzip�R�}���h���g�p�\�łȂ���΂ł��܂���B</p>";
                                // gztemp�t�@�C�����̂Ă�
                                if (file_exists($gztempfile)) {
                                    unlink($gztempfile);
                                }
                                $this->diedat = true;
                                return false;
                            // �𓀐����Ȃ�
                            } else {
                                if (file_exists($this->keydat.".bak")) {
                                    unlink($this->keydat.".bak");
                                }
                                $done_gunzip = true;
                            }

                        }
                        // gzip temp�t�@�C�����̂Ă�
                        if (file_exists($gztempfile)) {
                            unlink($gztempfile);
                        }
                    }

                    if (!$done_gunzip) {
                        FileCtl::make_datafile($this->keydat, $_conf['dat_perm']);
                        if (FileCtl::file_write_contents($this->keydat, $body) === false) {
                            p2die('cannot write file. downloadDat2chMaru()');
                        }
                    }

                    // �N���[�j���O =====
                    if ($marudatlines = FileCtl::file_read_lines($this->keydat)) {
                        $firstline = array_shift($marudatlines);
                        // �`�����N�Ƃ�
                        if (strpos($firstline, '+OK') === false) {
                            $secondline = array_shift($marudatlines);
                        }
                        $cont = '';
                        foreach ($marudatlines as $aline) {
                            // �`�����N�G���R�[�f�B���O���~�����Ƃ���(HTTP 1.0�ł��̂�)
                            if ($chunked) {
                                $cont .= $aline;
                            } else {
                                $cont .= $aline;
                            }
                        }
                        FileCtl::make_datafile($this->keydat, $_conf['dat_perm']);
                        if (FileCtl::file_write_contents($this->keydat, $cont) === false) {
                            p2die('cannot write file. downloadDat2chMaru()');
                        }
                    }

                // dat.gz�͂Ȃ������Ɣ��f
                } else {
                    fclose($fp);
                    return $this->_downloadDat2chMaruNotFound();
                }

            // �w�b�_�̏���
            } else {
                $l = fgets($fp,128000);
                //echo $l."<br>";// for debug
                // ex) HTTP/1.1 304 Not Modified
                if (preg_match("/^HTTP\/1\.\d (\d+) (.+)\r\n/", $l, $matches)) {
                    $code = $matches[1];

                    if ($code == "200") {
                        ;
                    } elseif ($code == "304") {
                        fclose($fp);
                        //$this->isonline = true;
                        return "304 Not Modified";
                    } else {
                        fclose($fp);
                        return $this->_downloadDat2chMaruNotFound();
                    }

                } elseif (preg_match("/^Content-Encoding: (x-)?gzip/", $l, $matches)) {
                    $isGzip = true;
                } elseif (preg_match("/^Last-Modified: (.+)\r\n/", $l, $matches)) {
                    $lastmodified = $matches[1];
                } elseif (preg_match("/^Content-Length: ([0-9]+)/", $l, $matches)) {
                    $onbytes = $matches[1];
                } elseif (preg_match("/^Transfer-Encoding: (.+)\r\n/", $l, $matches)) { // Transfer-Encoding: chunked
                    $t_enco = $matches[1];
                    if ($t_enco == "chunked") {
                        $chunked = true;
                    }
                } elseif ($l == "\r\n") {
                    $start_here = true;
                }
            }

        }
        fclose($fp);
        //$this->isonline = true;
        //$this->datochiok = 1;
        return true;
    }

    // }}}
    // {{{ _downloadDat2chMaruNotFound()

    /**
     * ��ID�ł̎擾���ł��Ȃ������Ƃ��ɌĂяo�����
     */
    protected function _downloadDat2chMaruNotFound()
    {
        global $_conf;

        // �ă`�������W���܂��Ȃ�A�ă`�������W����BSID���ύX����Ă��܂��Ă���ꍇ�����鎞�̂��߂̎����`�������W�B
        if (empty($_REQUEST['relogin2ch'])) {
            $_REQUEST['relogin2ch'] = true;
            return $this->downloadDat();
        } else {
            $remarutori_ht = " [<a href=\"{$_conf['read_php']}?host={$this->host}&amp;bbs={$this->bbs}&amp;key={$this->key}&amp;ls={$this->ls}&amp;maru=true&amp;relogin2ch=true\">�Ď擾�����݂�</a>]";
            $moritori_ht = $this->_generateMoritapoDatLink();
            $this->getdat_error_msg_ht .= "<p>p2 info - ��ID�ł̃X���b�h�擾�Ɏ��s���܂����B{$remarutori_ht}{$moritori_ht}</p>";
            $this->diedat = true;
            return false;
        }
    }

    // }}}
    // {{{ _downloadDat2chKako()

    /**
     * 2ch�̉ߋ����O�q�ɂ���dat.gz���_�E�����[�h���𓀂���
     */
    protected function _downloadDat2chKako($uri, $ext)
    {
        global $_conf, $_info_msg_ht;

        $url = $uri.$ext;

        $method = 'GET';

        $purl = parse_url($url); // URL����
        // �N�G���[
        if (isset($purl['query'])) {
            $purl['query'] = "?".$purl['query'];
        } else {
            $purl['query'] = "";
        }

        // �v���L�V
        if ($_conf['proxy_use']) {
            $send_host = $_conf['proxy_host'];
            $send_port = $_conf['proxy_port'];
            $send_path = $url;
        } else {
            $send_host = $purl['host'];
            $send_port = $purl['port'];
            $send_path = $purl['path'].$purl['query'];
        }
        // �f�t�H���g��80
        if (!$send_port) {
            $send_port = 80;
        }

        $request = "{$method} {$send_path} HTTP/1.0\r\n";
        $request .= "Host: {$purl['host']}\r\n";
        $request .= "User-Agent: Monazilla/1.00 ({$_conf['p2ua']})\r\n";
        $request .= "Connection: Close\r\n";
        //$request .= "Accept-Encoding: gzip\r\n";
        /*
        if ($modified) {
            $request .= "If-Modified-Since: $modified\r\n";
        }
        */
        $request .= "\r\n";

        // WEB�T�[�o�֐ڑ�
        $fp = fsockopen($send_host, $send_port, $errno, $errstr, $_conf['fsockopen_time_limit']);
        if (!$fp) {
            $url_t = P2Util::throughIme($url);
            echo "<p>�T�[�o�ڑ��G���[: $errstr ($errno)<br>p2 info - <a href=\"{$url_t}\"{$_conf['ext_win_target_at']}>$url</a> �ɐڑ��ł��܂���ł����B</p>";
            $this->diedat = true;
            return false;
        }

        fputs($fp, $request);
        $body = "";
        $start_here = false;
        while (!feof($fp)) {

            if ($start_here) {

                if ($code == "200") {

                    while (!feof($fp)) {
                        $body .= fread($fp, 4096);
                    }

                    if ($isGzip) {
                        $gztempfile = $this->keydat.".gz";
                        FileCtl::mkdir_for($gztempfile);
                        if (FileCtl::file_write_contents($gztempfile, $body) === false) {
                            p2die('cannot write file. downloadDat2chKako()');
                        }
                        if (extension_loaded('zlib')) {
                            $body = FileCtl::get_gzfile_contents($gztempfile);
                        } else {
                            // ���ɑ��݂���Ȃ�ꎞ�o�b�N�A�b�v�ޔ�
                            if (file_exists($this->keydat)) {
                                if (file_exists($this->keydat.".bak")) { unlink($this->keydat.".bak"); }
                                rename($this->keydat, $this->keydat.".bak");
                            }
                            $rcode = 1;
                            system("gzip -d $gztempfile", $rcode); // ��
                            if ($rcode != 0) {
                                if (file_exists($this->keydat.".bak")) {
                                    if (file_exists($this->keydat)) {
                                        unlink($this->keydat);
                                    }
                                    // ���s�Ȃ�o�b�N�A�b�v�߂�
                                    rename($this->keydat.".bak", $this->keydat);
                                }
                                $this->getdat_error_msg_ht = "<p>p2 info - 2�����˂�ߋ����O�q�ɂ���̃X���b�h��荞�݂́APHP��<a href=\"http://www.php.net/manual/ja/ref.zlib.php\">zlib�g�����W���[��</a>���Ȃ����Asystem��gzip�R�}���h���g�p�\�łȂ���΂ł��܂���B</p>";
                                // gztemp�t�@�C�����̂Ă�
                                if (file_exists($gztempfile)) { unlink($gztempfile); }
                                $this->diedat = true;
                                return false;
                            } else {
                                if (file_exists($this->keydat.".bak")) { unlink($this->keydat.".bak"); }
                                $done_gunzip = true;
                            }

                        }
                        if (file_exists($gztempfile)) { unlink($gztempfile); } // temp�t�@�C�����̂Ă�
                    }

                    if (!$done_gunzip) {
                        FileCtl::make_datafile($this->keydat, $_conf['dat_perm']);
                        if (FileCtl::file_write_contents($this->keydat, $body) === false) {
                            p2die('cannot write file. downloadDat2chKako()');
                        }
                    }

                // �Ȃ������Ɣ��f
                } else {
                    fclose($fp);
                    return $this->_downloadDat2chKakoNotFound($uri, $ext);

                }

            } else {
                $l = fgets($fp,128000);
                if (preg_match("/^HTTP\/1\.\d (\d+) (.+)\r\n/", $l, $matches)) { // ex) HTTP/1.1 304 Not Modified
                    $code = $matches[1];

                    if ($code == "200") {
                        ;
                    } elseif ($code == "304") {
                        fclose($fp);
                        //$this->isonline = true;
                        return "304 Not Modified";
                    } else {
                        fclose($fp);
                        return $this->_downloadDat2chKakoNotFound($uri, $ext);
                    }

                } elseif (preg_match("/^Content-Encoding: (x-)?gzip/", $l, $matches)) {
                    $isGzip = true;
                } elseif (preg_match("/^Last-Modified: (.+)\r\n/", $l, $matches)) {
                    $lastmodified = $matches[1];
                } elseif (preg_match("/^Content-Length: ([0-9]+)/", $l, $matches)) {
                    $onbytes = $matches[1];
                } elseif ($l == "\r\n") {
                    $start_here = true;
                }
            }

        }
        fclose($fp);
        //$this->isonline = true;
        return true;
    }

    // }}}
    // {{{ _downloadDat2chKakoNotFound()

    /**
     * �ߋ����O���擾�ł��Ȃ������Ƃ��ɌĂяo�����
     */
    protected function _downloadDat2chKakoNotFound($uri, $ext)
    {
        global $_conf;

        if ($ext == ".dat.gz") {
            //.dat.gz���Ȃ�������.dat�ł�����x
            return $this->_downloadDat2chKako($uri, ".dat");
        }
        if ($_GET['kakolog']) {
            $kakolog_ht = "<p><a href=\"{$_GET['kakolog']}.html\"{$_conf['bbs_win_target_at']}>{$_GET['kakolog']}.html</a></p>";
        }
        $this->getdat_error_msg_ht = "<p>p2 info - 2�����˂�ߋ����O�q�ɂ���̃X���b�h��荞�݂Ɏ��s���܂����B</p>";
        $this->getdat_error_msg_ht .= $kakolog_ht;
        $this->diedat = true;
        return false;
    }

    // }}}
    // {{{ get2chDatError()

    /**
     * 2ch��dat���擾�ł��Ȃ�����������Ԃ�
     *
     * @return  string �G���[���b�Z�[�W�i�������킩��Ȃ��ꍇ�͋�ŕԂ��j
     */
    public function get2chDatError()
    {
        global $_conf, $_info_msg_ht;

        // �z�X�g�ړ]���o�ŕύX�����z�X�g�����ɖ߂�
        if (!empty($this->old_host)) {
            $this->host = $this->old_host;
            $this->old_host = null;
        }

        $read_url = "http://{$this->host}/test/read.cgi/{$this->bbs}/{$this->key}/";

        // {{{ read.cgi ����HTML���擾

        $read_response_html = '';
        $wap_ua = new WapUserAgent();
        $wap_ua->setAgent($_conf['p2ua']); // �����́A"Monazilla/" �������NG
        $wap_ua->setTimeout($_conf['fsockopen_time_limit']);
        $wap_req = new WapRequest();
        $wap_req->setUrl($read_url);
        if ($_conf['proxy_use']) {
            $wap_req->setProxy($_conf['proxy_host'], $_conf['proxy_port']);
        }
        $wap_res = $wap_ua->request($wap_req);

        if ($wap_res->isError()) {
            $url_t = P2Util::throughIme($wap_req->url);
            $_info_msg_ht .= "<div>Error: {$wap_res->code} {$wap_res->message}<br>";
            $_info_msg_ht .= "p2 info: <a href=\"{$url_t}\"{$_conf['ext_win_target_at']}>{$wap_req->url}</a> �ɐڑ��ł��܂���ł����B</div>";
        } else {
            $read_response_html = $wap_res->content;
        }
        unset($wap_ua, $wap_req, $wap_res);

        // }}}
        // {{{ �擾����HTML�i$read_response_html�j����͂��āA������������

        $dat_response_status = "";
        $dat_response_msg = "";

        $kakosoko_match = "/���̃X���b�h�͉ߋ����O�q�ɂɊi.{1,2}����Ă��܂�/";

        $naidesu_match = "/<title>����Ȕ�or�X���b�h�Ȃ��ł��B<\/title>/";
        $error3939_match = "{<title>�Q�����˂� error 3939</title>}";    // �ߋ����O�q�ɂ�html���̎��i���ɂ����邩���A�悭�m��Ȃ��j

        //<a href="http://qb5.2ch.net/sec2chd/kako/1091/10916/1091634596.html">
        //<a href="../../../../mac/kako/1004/10046/1004680972.html">
        //$kakohtml_match = "{<a href=\"\.\./\.\./\.\./\.\./([^/]+/kako/\d+(/\d+)?/(\d+)).html\">}";
        $kakohtml_match = "{/([^/]+/kako/\d+(/\d+)?/(\d+)).html\">}";
        $waithtml_match = "/html�������̂�҂��Ă���悤�ł��B/";

        //
        // <title>�����̃X���b�h�͉ߋ����O�q�ɂ�
        //
        if (preg_match($kakosoko_match, $read_response_html, $matches)) {
            $dat_response_status = "���̃X���b�h�͉ߋ����O�q�ɂɊi�[����Ă��܂��B";
            //if (file_exists($_conf['idpw2ch_php']) || file_exists($_conf['sid2ch_php'])) {
                $marutori_ht = " [<a href=\"{$_conf['read_php']}?host={$this->host}&amp;bbs={$this->bbs}&amp;key={$this->key}&amp;ls={$this->ls}&amp;maru=true\">��ID��p2�Ɏ�荞��</a>]";
            //} else {
            //    $marutori_ht = " [<a href=\"login2ch.php\" target=\"subject\">��ID���O�C��</a>]";
            //}
            $moritori_ht = $this->_generateMoritapoDatLink();
            $dat_response_msg = "<p>2ch info - ���̃X���b�h�͉ߋ����O�q�ɂɊi�[����Ă��܂��B{$marutori_ht}{$moritori_ht}</p>";

        //
        // <title>������Ȕ�or�X���b�h�Ȃ��ł��Bor error 3939
        //
        } elseif (preg_match($naidesu_match, $read_response_html, $matches) || preg_match($error3939_match, $read_response_html, $matches)) {

            if (preg_match($kakohtml_match, $read_response_html, $matches)) {
                $dat_response_status = "����! �ߋ����O�q�ɂŁAhtml�����ꂽ�X���b�h�𔭌����܂����B";
                $kakolog_uri = "http://{$this->host}/{$matches[1]}";
                $kakolog_url_en = rawurlencode($kakolog_uri);
                $read_kako_url = "{$_conf['read_php']}?host={$this->host}&amp;bbs={$this->bbs}&amp;key={$this->key}&amp;ls={$this->ls}&amp;kakolog={$kakolog_url_en}&amp;kakoget=1";
                $dat_response_msg = "<p>2ch info - ����! �ߋ����O�q�ɂŁA<a href=\"{$kakolog_uri}.html\"{$_conf['bbs_win_target_at']}>�X���b�h {$matches[3]}.html</a> �𔭌����܂����B [<a href=\"{$read_kako_url}\">p2�Ɏ�荞��œǂ�</a>]</p>";

            } elseif (preg_match($waithtml_match, $read_response_html, $matches)) {
                $dat_response_status = "����! �X���b�h��html�������̂�҂��Ă���悤�ł��B";
                $marutori_ht = " [<a href=\"{$_conf['read_php']}?host={$this->host}&amp;bbs={$this->bbs}&amp;key={$this->key}&amp;ls={$this->ls}&amp;maru=true\">��ID��p2�Ɏ�荞��</a>]";
                $moritori_ht = $this->_generateMoritapoDatLink();
                $dat_response_msg = "<p>2ch info - ����! �X���b�h��html�������̂�҂��Ă���悤�ł��B{$marutori_ht}{$moritori_ht}</p>";

            } else {
                if ($_GET['kakolog']) {
                    $dat_response_status = "����Ȕ�or�X���b�h�Ȃ��ł��B";
                    $kako_html_url = urldecode($_GET['kakolog']) . ".html";
                    $read_kako_url = "{$_conf['read_php']}?host={$this->host}&amp;bbs={$this->bbs}&amp;key={$this->key}&amp;ls={$this->ls}&amp;kakolog={$_GET['kakolog']}&amp;kakoget=1";
                    $dat_response_msg = "<p>2ch info - ����Ȕ�or�X���b�h�Ȃ��ł��B</p>";
                    $dat_response_msg .= "<p><a href=\"{$kako_html_url}\"{$_conf['bbs_win_target_at']}>{$kako_html_url}</a> [<a href=\"{$read_kako_url}\">p2�Ƀ��O����荞��œǂ�</a>]</p>";
                } else {
                    $dat_response_status = "����Ȕ�or�X���b�h�Ȃ��ł��B";
                    $dat_response_msg = "<p>2ch info - ����Ȕ�or�X���b�h�Ȃ��ł��B</p>";
                }
            }

        // ������������Ȃ��ꍇ�ł��A�Ƃ肠�����ߋ����O��荞�݂̃����N���ێ����Ă���B�Ǝv���B���܂�o���Ă��Ȃ� 2005/2/27 aki
        } elseif ($_GET['kakolog']) {
            $dat_response_status = "";
            $kako_html_url = urldecode($_GET['kakolog']).".html";
            $read_kako_url = "{$_conf['read_php']}?host={$this->host}&amp;bbs={$this->bbs}&amp;key={$this->key}&amp;ls={$this->ls}&amp;kakolog={$_GET['kakolog']}&amp;kakoget=1";
            $dat_response_msg = "<p><a href=\"{$kako_html_url}\"{$_conf['bbs_win_target_at']}>{$kako_html_url}</a> [<a href=\"{$read_kako_url}\">p2�Ƀ��O����荞��œǂ�</a>]</p>";

        }

        // }}}

        return $dat_response_msg;
    }

    // }}}
    // {{{ previewOne()

    /**
     * >>1�݂̂��v���r���[����
     */
    public function previewOne()
    {
        global $_conf, $_info_msg_ht;

        if (!($this->host && $this->bbs && $this->key)) { return false; }

        // ���[�J��dat����擾
        if (is_readable($this->keydat)) {
            $fd = fopen($this->keydat, "rb");
            $first_line = fgets($fd, 32800);
            fclose ($fd);

            // be.2ch.net �Ȃ�EUC��SJIS�ϊ�
            if (P2Util::isHostBe2chNet($this->host)) {
                $first_line = mb_convert_encoding($first_line, 'CP932', 'CP51932');
            }

            $first_datline = rtrim($first_line);
            if (strpos($first_datline, '<>') !== false) {
                $datline_sepa = "<>";
            } else {
                $datline_sepa = ",";
                $this->dat_type = "2ch_old";
            }
            $d = explode($datline_sepa, $first_datline);
            $this->setTtitle($d[4]);
        }

        // ���[�J��dat�Ȃ���΃I�����C������
        if (!$first_line) {

            $method = 'GET';
            $url = "http://{$this->host}/{$this->bbs}/dat/{$this->key}.dat";

            $purl = parse_url($url); // URL����
            if (isset($purl['query'])) { // �N�G���[
                $purl['query'] = '?' . $purl['query'];
            } else {
                $purl['query'] = '';
            }

            // �v���L�V
            if ($_conf['proxy_use']) {
                $send_host = $_conf['proxy_host'];
                $send_port = $_conf['proxy_port'];
                $send_path = $url;
            } else {
                $send_host = $purl['host'];
                $send_port = $purl['port'];
                $send_path = $purl['path'] . $purl['query'];
            }

            if (!$send_port) {$send_port = 80;} // �f�t�H���g��80

            $request = "{$method} {$send_path} HTTP/1.0\r\n";
            $request .= "Host: {$purl['host']}\r\n";
            $request .= "User-Agent: Monazilla/1.00 ({$_conf['p2ua']})\r\n";
            // $request .= "Range: bytes={$from_bytes}-\r\n";

            // Basic�F�ؗp�̃w�b�_
            if (isset($purl['user']) && isset($purl['pass'])) {
                $request .= "Authorization: Basic ".base64_encode($purl['user'].":".$purl['pass'])."\r\n";
            }

            $request .= "Connection: Close\r\n";
            $request .= "\r\n";

            // WEB�T�[�o�֐ڑ�
            $fp = fsockopen($send_host, $send_port, $errno, $errstr, $_conf['fsockopen_time_limit']);
            if (!$fp) {
                $url_t = P2Util::throughIme($url);
                $_info_msg_ht .= "<p>�T�[�o�ڑ��G���[: $errstr ($errno)<br>p2 info - <a href=\"{$url_t}\"{$_conf['ext_win_target_at']}>{$url}</a> �ɐڑ��ł��܂���ł����B</p>";
                $this->diedat = true;
                return false;
            }

            fputs($fp, $request);
            $start_here = false;
            while (!feof($fp)) {

                if ($start_here) {

                    if ($code == "200") {
                        $first_line = fgets($fp, 32800);
                        break;
                    } else {
                        fclose($fp);
                        return $this->previewOneNotFound();
                    }
                } else {
                    $l = fgets($fp,32800);
                    //echo $l."<br>";// for debug
                    if (preg_match("/^HTTP\/1\.\d (\d+) (.+)\r\n/", $l, $matches)) { // ex) HTTP/1.1 304 Not Modified
                        $code = $matches[1];

                        if ($code == "200") {
                            ;
                        } else {
                            fclose($fp);
                            return $this->previewOneNotFound();
                        }

                    } elseif (preg_match("/^Content-Length: ([0-9]+)/", $l, $matches)) {
                        $onbytes = $matches[1];
                    } elseif ($l == "\r\n") {
                        $start_here = true;
                    }
                }

            }
            fclose($fp);

            // be.2ch.net �Ȃ�EUC��SJIS�ϊ�
            if (P2Util::isHostBe2chNet($this->host)) {
                $first_line = mb_convert_encoding($first_line, 'CP932', 'CP51932');
            }

            $first_datline = rtrim($first_line);

            if (strpos($first_datline, '<>') !== false) {
                $datline_sepa = "<>";
            } else {
                $datline_sepa = ",";
                $this->dat_type = "2ch_old";
            }
            $d = explode($datline_sepa, $first_datline);
            $this->setTtitle($d[4]);

            $this->onthefly = true;

        } else {
            // �֋X��
            if (!$this->readnum) {
                $this->readnum = 1;
            }
        }

        if ($_conf['ktai']) {
            $aShowThread = new ShowThreadK($this);
            $aShowThread->am_autong = false;
        } else {
            $aShowThread = new ShowThreadPc($this);
        }

        $body = '';
        if ($this->onthefly) {
            $body .= "<div><span class=\"onthefly\">on the fly</span></div>\n";
        }
        $body .= "<div class=\"thread\">\n";
        $body .= $aShowThread->transRes($first_line, 1); // 1��\��
        $body .= "</div>\n";

        return $body;
    }

    // }}}
    // {{{ previewOneNotFound()

    /**
     * >>1���v���r���[�ŃX���b�h�f�[�^��������Ȃ������Ƃ��ɌĂяo�����
     */
    public function previewOneNotFound()
    {
        // 2ch, bbspink �Ȃ�read.cgi�Ŋm�F
        if (P2Util::isHost2chs($this->host)) {
            $this->getdat_error_msg_ht = $this->get2chDatError();
        }
        $this->diedat = true;
        return false;
    }

    // }}}
    // {{{ lsToPoint()

    /**
     * $ls�𕪉�����start��to��nofirst�����߂�
     */
    public function lsToPoint()
    {
        global $_conf;

        $start = 1;
        $to = false;
        $nofirst = false;

        // n���܂�ł���ꍇ�́A>>1��\�����Ȃ��i$nofirst�j
        if (strpos($this->ls, 'n') !== false) {
            $nofirst = true;
            $this->ls = str_replace('n', '', $this->ls);
        }

        // �͈͎w��ŕ���
        $n = explode('-', $this->ls);
        // �͈͎w�肪�Ȃ����
        if (sizeof($n) == 1) {
            // l�w�肪�����
            if (substr($n[0], 0, 1) == "l") {
                $ln = intval(substr($n[0], 1));
                if ($_conf['ktai']) {
                    if ($ln > $_conf['mobile.rnum_range']) {
                        $ln = $_conf['mobile.rnum_range'];
                    }
                }
                $start = $this->rescount - $ln + 1;
                if ($start < 1) {
                    $start = 1;
                }
                $to = $this->rescount;
            // all�w��Ȃ�
            } elseif ($this->ls == "all") {
                $start = 1;
                $to = $this->rescount;

            } else {
                // ���X�Ԏw��
                if (intval($this->ls) > 0) {
                    $this->ls = intval($this->ls);
                    $start = $this->ls;
                    $to = $this->ls;
                    $nofirst = true;
                // �w�肪�Ȃ� or �s���ȏꍇ�́Aall�Ɠ����\���ɂ���
                } else {
                    $start = 1;
                    $to = $this->rescount;
                }
            }
        // �͈͎w�肪�����
        } else {
            if (!$start = intval($n[0])) {
                $start = 1;
            }
            if (!$to = intval($n[1])) {
                $to = $this->rescount;
            }
        }

        // �V���܂Ƃߓǂ݂̕\��������
        if (isset($GLOBALS['rnum_all_range']) and $GLOBALS['rnum_all_range'] > 0) {

            /*
            ���g�т̐V���܂Ƃߓǂ݂��A����������ŏI��������ɁA�́u����or�X�V�v������

            ���~�b�g < �X���̕\���͈�
            �����~�b�g�́@0
            �X���̕\���͈͂��I����O�Ƀ��~�b�g������
            ������

            ���~�b�g > �X���̕\���͈�
            �����~�b�g�� +
            ���~�b�g�����c���Ă���ԂɁA�X���̕\���͈͂��I����
            ���X�V

            ���~�b�g = �X���̕\���͈�
            �����~�b�g�� 0
            �X���̕\���͈͒��x�Ń��~�b�g����������
            ������? �X�V?
            �����̏ꍇ���X�V�̏ꍇ������B���������̂��߁A
            ���̃X���̎c��V���������邩�ǂ������s���Ŕ���ł��Ȃ��B
            */

            // ���~�b�g���X���̕\���͈͂�菬�����ꍇ�́A�X���̕\���͈͂����~�b�g�ɍ��킹��
            $limit_to = $start + $GLOBALS['rnum_all_range'] -1;

            if ($limit_to < $to) {
                $to = $limit_to;

            // �X���̕\���͈͒��x�Ń��~�b�g�����������ꍇ
            } elseif ($limit_to == $to) {
                $GLOBALS['limit_to_eq_to'] = TRUE;
            }

            // ���̃��~�b�g�́A����̃X���̕\���͈͕������炵����
            $GLOBALS['rnum_all_range'] = $GLOBALS['rnum_all_range'] - ($to - $start) -1;

            //print_r("$start, $to, {$GLOBALS['rnum_all_range']}");

        } else {
            // �g�їp
            if ($_conf['ktai']) {
                // �\��������
                /*
                if ($start + $_conf['mobile.rnum_range'] -1 <= $to) {
                    $to = $start + $_conf['mobile.rnum_range'] -1;
                }
                */
                // ��X���ł́A�O����܂݁A����+1�ƂȂ�̂ŁA1���܂�����
                if ($start + $_conf['mobile.rnum_range'] <= $to) {
                    $to = $start + $_conf['mobile.rnum_range'];
                }
                if ($_conf['filtering']) {
                    $start = 1;
                    $to = $this->rescount;
                    $nofirst = false;
                }
            }
        }

        $this->resrange = compact('start', 'to', 'nofirst');
        return $this->resrange;
    }

    // }}}
    // {{{ readDat()

    /**
     * Dat��ǂݍ���
     * $this->datlines �� set ����
     */
    public function readDat()
    {
        global $_conf;

        if (file_exists($this->keydat)) {
            if ($this->datlines = FileCtl::file_read_lines($this->keydat)) {

                // be.2ch.net �Ȃ�EUC��SJIS�ϊ�
                // �O�̂���SJIS��UTF-8�������R�[�h����̌��ɓ���Ă���
                // �E�E�E���A�������������^�C�g���̃X���b�h�Ō딻�肪�������̂ŁA�w�肵�Ă���
                if (P2Util::isHostBe2chNet($this->host)) {
                    //mb_convert_variables('CP932', 'CP51932,CP932,UTF-8', $this->datlines);
                    mb_convert_variables('CP932', 'CP51932', $this->datlines);
                }

                if (strpos($this->datlines[0], '<>') === false) {
                    $this->dat_type = "2ch_old";
                }
            }
        } else {
            return false;
        }

        $this->rescount = sizeof($this->datlines);

        if ($_conf['flex_idpopup'] || $_conf['ngaborn_chain'] || $_conf['ngaborn_frequent'] ||
            ($_conf['ktai'] && ($_conf['mobile.clip_unique_id'] || $_conf['mobile.underline_id'])))
        {
            $this->_setIdCount();
        }

        return true;
    }

    // }}}
    // {{{ setIdCount()

    /**
     * ��̃X�����ł�ID�o�������Z�b�g����
     */
    protected function _setIdCount()
    {
        if (!$this->datlines) {
            return;
        }

        $i = 0;
        $idp = array_fill(1, $this->rescount, null);
        $ids = array_fill(1, $this->rescount, null);

        foreach ($this->datlines as $l) {
            $lar = explode('<>', $l);
            $i++;
            if (preg_match('<(ID: ?| )([0-9A-Za-z/.+]{8,11})(?=[^0-9A-Za-z/.+]|$)>', $lar[2], $m)) {
                $idp[$i] = $m[1];
                $ids[$i] = $m[2];
            }
        }

        $this->idp = $idp;
        $this->ids = $ids;
        $this->idcount = array_count_values(array_filter($ids, 'is_string'));
    }

    // }}}
    // {{{ explodeDatLine()

    /**
     * datline��explode����
     */
    public function explodeDatLine($aline)
    {
        $aline = rtrim($aline);

        if ($this->dat_type == "2ch_old") {
            $parts = explode(',', $aline);
        } else {
            $parts = explode('<>', $aline);
        }

        // iframe ���폜�B2ch�����퉻���ĕK�v�Ȃ��Ȃ����炱�̃R�[�h�͊O�������B2005/05/19
        $parts[3] = preg_replace('{<(iframe|script)( .*?)?>.*?</\\1>}i', '', $parts[3]);

        return $parts;
    }

    // }}}
    // {{{ scanOriginalHosts()

    /**
     * dat�𑖍����ăX�����Ď��̃z�X�g�������o����
     *
     * @param void
     * @return array
     */
    public function scanOriginalHosts()
    {
        if (P2Util::isHost2chs($this->host) &&
            file_exists($this->keydat) &&
            ($dat = file_get_contents($this->keydat)))
        {
            $bbs_re = preg_quote($this->bbs, '@');
            $pattern = "@/(\\w+\\.(?:2ch\\.net|bbspink\\.com))(?:/test/read\\.cgi)?/{$bbs_re}\\b@";
            if (preg_match_all($pattern, $dat, $matches, PREG_PATTERN_ORDER)) {
                $hosts = array_unique($matches[1]);
                $arKey = array_search($this->host, $hosts);
                if ($arKey !== false && array_key_exists($arKey, $hosts)) {
                    unset($hosts[$arKey]);
                }

                return $hosts;
            }
        }

        return array();
    }

    // }}}
    // {{{ getDefaultGetDatErrorMessageHTML()

    /**
     * �f�t�H���g��dat�擾���s�G���[���b�Z�[�WHTML���擾����
     *
     * @param void
     * @return string
     */
    public function getDefaultGetDatErrorMessageHTML()
    {
        global $_conf;

        $diedat_msg = '<p><b>p2 info - �T�[�o����ŐV�̃X���b�h�����擾�ł��܂���ł����B</b>';
        if ($hosts = $this->scanOriginalHosts()) {
            $common_q = '&amp;bbs=' . rawurldecode($this->bbs)
                      . '&amp;key=' . rawurldecode($this->key)
                      . '&amp;ls=' . rawurldecode($this->ls);
            $diedat_msg .= '<br>dat���瑼�̃z�X�g�������o���܂����B';
            foreach ($hosts as $host) {
                $diedat_msg .= " [<a href=\"{$_conf['read_php']}?host={$host}{$common_q}\">{$host}</a>]";
            }
        }
        $diedat_msg .= '</p>';

        return $diedat_msg;
    }

    // }}}
    // {{{ _generateMoritapoDatLink()

    /**
     * ����p2��(dat�擾�������Ȃ��ꍇ�̓����^�|�������)dat���擾���邽�߂̃����N�𐶐�����B
     *
     * @param void
     * @return string
     */
    protected function _generateMoritapoDatLink()
    {
        global $_conf;

        if ($_conf['p2_2ch_mail'] && $_conf['p2_2ch_pass']) {
            $csrfid = $this->_getCsrfIdForMoritapoDat();
            $query = htmlspecialchars('host=' . rawurldecode($this->host)
                                    . '&bbs=' . rawurldecode($this->bbs)
                                    . '&key=' . rawurldecode($this->key)
                                    . '&ls=' . rawurldecode($this->ls)
                                    . '&moritapodat=true&csrfid=' . $csrfid, ENT_QUOTES);
            return " [<a href=\"{$_conf['read_php']}?{$query}\">�����^�|��p2�Ɏ�荞��</a>]";
        } else {
            return '';
        }
    }

    // }}}
    // {{{ _downloadDat2chMoritapo()

    /**
     * ����p2��(dat�擾�������Ȃ��ꍇ�̓����^�|�������)dat���擾����
     *
     * @param void
     * @return bool
     */
    protected function _downloadDat2chMoritapo()
    {
         global $_conf;

        // dat���_�E�����[�h
        try {
            $client = P2Util::getP2Client();
            $body = $client->downloadDat($this->host, $this->bbs, $this->key, $response);
            // DEBUG
            /*
            $GLOBALS['_downloadDat2chMoritapo_response_dump'] = '<pre>' . htmlspecialchars(print_r($response, true)) . '</pre>';
            register_shutdown_function(create_function('', 'echo $GLOBALS[\'_downloadDat2chMoritapo_response_dump\'];'));
            */
        } catch (P2Exception $e) {
            p2die($e->getMessage());
        }

        // �f�[�^���؂���1
        if (!$body || (strpos($body, '<>') === false && strpos($body, ',') === false)) {
            return $this->_downloadDat2chMoritapoNotFound();
        }

        // ���s�ʒu�����o
        $posCR = strpos($body, "\r");
        $posLF = strpos($body, "\n");
        if ($posCR === false && $posLF === false) {
            $pos = strlen($body);
        } elseif ($posCR === false) {
            $pos = $posLF;
        } elseif ($posLF === false) {
            $pos = $posCR;
        } else {
            $pos = min($posLF, $posCR);
        }

        // 1�s�ڂ̎擾�ƃf�[�^���؂���2
        $firstLine = rtrim(substr($body, 0, $pos));
        if (strpos($firstLine, '<>') !== false) {
            $this->dat_type = '2ch';
        } elseif (strpos($firstLine, ',') !== false) {
            $this->dat_type = '2ch_old';
        } else {
            return $this->_downloadDat2chMoritapoNotFound();
        }

        // �f�[�^���؂���3 (�^�C�g�� = $ar[4])
        $ar = $this->explodeDatLine($firstLine);
        if (count($ar) < 5) {
            return $this->_downloadDat2chMoritapoNotFound();
        }

        // ���[�J��dat�ɏ�������
        FileCtl::make_datafile($this->keydat, $_conf['dat_perm']);
        if (FileCtl::file_write_contents($this->keydat, $body) === false) {
            p2die('cannot write file. downloadDat2chMoritapo()');
        }

        return true;
   }

    // }}}
    // {{{ _downloadDat2chMoritapoNotFound()

    /**
     * �����^�|�ł̎擾���ł��Ȃ������Ƃ��ɌĂяo�����
     *
     * @param void
     * @return bool
     */
    protected function _downloadDat2chMoritapoNotFound()
    {
        global $_conf;

        $csrfid = $this->_getCsrfIdForMoritapoDat();

        $marutori_ht = " [<a href=\"{$_conf['read_php']}?host={$this->host}&amp;bbs={$this->bbs}&amp;key={$this->key}&amp;ls={$this->ls}&amp;maru=true\">��ID��p2�Ɏ�荞��</a>]";

        if ($hosts = $this->scanOriginalHosts()) {
            $hosts_ht = '<br>dat���瑼�̃z�X�g�������o���܂����B';
            foreach ($hosts as $host) {
                $hosts_ht .= " [<a href=\"#\" onclick=\"this.parentNode.elements['host'].value='{$host}';return false;\">{$host}</a>]";
            }
        } else {
            $hosts_ht = '';
        }

        $this->getdat_error_msg_ht .= <<<EOF
<p>p2 info - �����^�|�ł̃X���b�h�擾�Ɏ��s���܂����B{$marutori_ht}</p>
<form action="{$_conf['read_php']}" method="get">
    �z�X�g��
    <input type="text" name="host" value="{$this->host}" size="12">
    <input type="hidden" name="bbs" value="{$this->bbs}">
    <input type="hidden" name="key" value="{$this->key}">
    <input type="hidden" name="ls" value="{$this->ls}">
    �ɕς���
    <input type="submit" name="moritapodat" value="�����^�|��p2�Ɏ�荞��ł݂�">
    <input type="hidden" name="csrfid" value="{$csrfid}">
    {$hosts_ht}
</form>\n
EOF;
        $this->diedat = true;

        return false;
    }

    // }}}
    // {{{ _getCsrfIdForMoritapoDat()

    /**
     * ����p2����dat���擾����ۂɎg��CSRF�h�~�g�[�N���𐶐�����
     *
     * @param void
     * @return string
     */
    protected function _getCsrfIdForMoritapoDat()
    {
        return P2Util::getCsrfId('moritapodat' . $this->host . $this->bbs . $this->key);
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
