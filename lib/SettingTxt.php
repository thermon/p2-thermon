<?php
// {{{ SettingTxt

/**
 * p2 - 2ch �� SETTING.TXT �������N���X
 * http://news19.2ch.net/newsplus/SETTING.TXT
 *
 * @since 2006/02/27
 */
class SettingTxt
{
    // {{{ properties

    public $setting_array; // SETTING.TXT���p�[�X�����A�z�z��

    private $_host;
    private $_bbs;
    private $_url;           // SETTING.TXT ��URL
    private $_setting_txt;   // SETTING.TXT ���[�J���ۑ��t�@�C���p�X
    private $_setting_srd;   // p2_kb_setting.srd $this->setting_array �� serialize() �����f�[�^�t�@�C���p�X
    private $_cache_interval;

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     */
    public function __construct($host, $bbs)
    {
        $this->_cache_interval = 60 * 60 * 12; // �L���b�V����12���ԗL��

        $this->_host = $host;
        $this->_bbs =  $bbs;

        $dat_host_bbs_dir_s = P2Util::datDirOfHostBbs($host, $bbs);
        $this->_setting_txt = $dat_host_bbs_dir_s . 'SETTING.TXT';
        $this->_setting_srd = $dat_host_bbs_dir_s . 'p2_kb_setting.srd';

        $this->_url = 'http://' . $host . '/' . $bbs . '/SETTING.TXT';
        //$this->_url = P2Util::adjustHostJbbs($this->_url); // ������΂�livedoor�ړ]�ɑΉ��B�Ǎ����livedoor�Ƃ���B

        $this->setting_array = array();

        // SETTING.TXT ���_�E�����[�h���Z�b�g����
        $this->dlAndSetData();
    }

    // }}}
    // {{{ dlAndSetData()

    /**
     * SETTING.TXT ���_�E�����[�h���Z�b�g����
     *
     * @return boolean �Z�b�g�ł���� true�A�ł��Ȃ���� false
     */
    public function dlAndSetData()
    {
        $this->downloadSettingTxt();

        return $this->setSettingArray();
    }

    // }}}
    // {{{ downloadSettingTxt()

    /**
     * SETTING.TXT ���_�E�����[�h���āA�p�[�X���āA�L���b�V������
     *
     * @return boolean ���s����
     */
    public function downloadSettingTxt()
    {
        global $_conf, $_info_msg_ht;

        // �܂�BBS�E������� �� SETTING.TXT �����݂��Ȃ����̂Ƃ���
        if (P2Util::isHostMachiBbs($this->_host) || P2Util::isHostJbbsShitaraba($this->_host)) {
            return false;
        }

        $perm = (isset($_conf['dl_perm'])) ? $_conf['dl_perm'] : 0606;

        FileCtl::mkdir_for($this->_setting_txt); // �f�B���N�g����������΍��

        if (file_exists($this->_setting_srd) && file_exists($this->_setting_txt)) {
            // �X�V���Ȃ��ꍇ�́A���̏�Ŕ����Ă��܂�
            if (!empty($_GET['norefresh']) || isset($_REQUEST['word'])) {
                return true;
            // �L���b�V�����V�����ꍇ��������
            } elseif ($this->isCacheFresh()) {
                return true;
            }
            $modified = http_date(filemtime($this->_setting_txt));
        } else {
            $modified = false;
        }

        // DL
        if (!class_exists('HTTP_Request', false)) {
            require 'HTTP/Request.php';
        }

        $params = array();
        $params['timeout'] = $_conf['fsockopen_time_limit'];
        if ($_conf['proxy_use']) {
            $params['proxy_host'] = $_conf['proxy_host'];
            $params['proxy_port'] = $_conf['proxy_port'];
        }
        $req = new HTTP_Request($this->_url, $params);
        $modified && $req->addHeader('If-Modified-Since', $modified);
        $req->addHeader('User-Agent', "Monazilla/1.00 ({$_conf['p2ua']})");

        $response = $req->sendRequest();

        if (PEAR::isError($response)) {
            $error_msg = $response->getMessage();
        } else {
            $code = $req->getResponseCode();

            if ($code == 302) {
                // �z�X�g�̈ړ]��ǐ�
                require_once P2_LIB_DIR . '/BbsMap.php';
                $new_host = BbsMap::getCurrentHost($this->_host, $this->_bbs);
                if ($new_host != $this->_host) {
                    $aNewSettingTxt = new SettingTxt($new_host, $this->_bbs);
                    $body = $aNewSettingTxt->downloadSettingTxt();
                    return true;
                }
            }

            if (!($code == 200 || $code == 206 || $code == 304)) {
                //var_dump($req->getResponseHeader());
                $error_msg = $code;
            }
        }

        // DL�G���[
        if (isset($error_msg) && strlen($error_msg) > 0) {
            $url_t = P2Util::throughIme($this->_url);
            $_info_msg_ht .= "<div>Error: {$error_msg}<br>";
            $_info_msg_ht .= "p2 info: <a href=\"{$url_t}\"{$_conf['ext_win_target_at']}>{$this->_url}</a> �ɐڑ��ł��܂���ł����B</div>";
            touch($this->_setting_txt); // DL���s�����ꍇ�� touch
            return false;

        }

        $body = $req->getResponseBody();

        // DL�������� ���� �X�V����Ă�����ۑ�
        if ($body && $code != "304") {

            // ������� or be.2ch.net �Ȃ�EUC��SJIS�ɕϊ�
            if (P2Util::isHostJbbsShitaraba($this->_host) || P2Util::isHostBe2chNet($this->_host)) {
                $body = mb_convert_encoding($body, 'CP932', 'CP51932');
            }

            if (FileCtl::file_write_contents($this->_setting_txt, $body) === false) {
                p2die('cannot write file');
            }
            chmod($this->_setting_txt, $perm);

            // �p�[�X���ăL���b�V����ۑ�����
            if (!$this->cacheParsedSettingTxt()) {
                return false;
            }

        } else {
            // touch���邱�ƂōX�V�C���^�[�o���������̂ŁA���΂炭�ă`�F�b�N����Ȃ��Ȃ�
            touch($this->_setting_txt);
            // �����ɃL���b�V����touch���Ȃ��ƁA_setting_txt��_setting_srd�ōX�V���Ԃ�����A
            // ���񂱂��܂ŏ���������i�T�[�o�ւ̃w�b�_���N�G�X�g����ԁj�ꍇ������B
            touch($this->_setting_srd);
        }

        return true;
    }

    // }}}
    // {{{ isCacheFresh()

    /**
     * �L���b�V�����V�N�Ȃ� true ��Ԃ�
     *
     * @return boolean �V�N�Ȃ� true�B�����łȂ���� false�B
     */
    public function isCacheFresh()
    {
        // �L���b�V��������ꍇ
        if (file_exists($this->_setting_srd)) {
            // �L���b�V���̍X�V���w�莞�Ԉȓ��Ȃ�
            // clearstatcache();
            if (filemtime($this->_setting_srd) > time() - $this->_cache_interval) {
                return true;
            }
        }

        return false;
    }

    // }}}
    // {{{ cacheParsedSettingTxt()

    /**
     * SETTING.TXT ���p�[�X���ăL���b�V���ۑ�����
     *
     * ��������΁A$this->setting_array ���Z�b�g�����
     *
     * @return boolean ���s����
     */
    public function cacheParsedSettingTxt()
    {
        global $_conf;

        $this->setting_array = array();

        if (!$lines = FileCtl::file_read_lines($this->_setting_txt)) {
            return false;
        }

        foreach ($lines as $line) {
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                $this->setting_array[$key] = $value;
            }
        }
        $this->setting_array['p2version'] = $_conf['p2version'];

        // �p�[�X�L���b�V���t�@�C����ۑ�����
        if (FileCtl::file_write_contents($this->_setting_srd, serialize($this->setting_array)) === false) {
            return false;
        }

        return true;
    }

    // }}}
    // {{{ setSettingArray()

    /**
     * SETTING.TXT �̃p�[�X�f�[�^��ǂݍ���
     *
     * ��������΁A$this->setting_array ���Z�b�g�����
     *
     * @return boolean ���s����
     */
    public function setSettingArray()
    {
        global $_conf;

        if (!file_exists($this->_setting_srd)) {
            return false;
        }

        $this->setting_array = unserialize(file_get_contents($this->_setting_srd));

        /*
        if ($this->setting_array['p2version'] != $_conf['p2version']) {
            unlink($this->_setting_srd);
            unlink($this->_setting_txt);
        }
        */

        return (bool)$this->setting_array;
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
