<?php

// {{{ BbsMap

/**
 * BbsMap�N���X
 * ��-�z�X�g�̑Ή��\���쐬���A����Ɋ�Â��ăz�X�g�̓������s��
 *
 * @static
 */
class BbsMap
{
    // {{{ static properties

    /**
     * ��-�z�X�g�̑Ή��\
     *
     * @var array
     */
    static private $_map = null;

    // }}}
    // {{{ getCurrentHost()

    /**
     * �ŐV�̃z�X�g���擾����
     *
     * @param   string  $host   �z�X�g��
     * @param   string  $bbs    ��
     * @param   bool    $autosync   �ړ]�����o�����Ƃ��Ɏ����œ������邩�ۂ�
     * @return  string  �ɑΉ�����ŐV�̃z�X�g
     */
    static public function getCurrentHost($host, $bbs, $autosync = true)
    {
        global $_info_msg_ht;
        static $synced = false;

        // �}�b�s���O�ǂݍ���
        $map = self::_getMapping();
        if (!$map) {
            return $host;
        }
        $type = self::_detectHostType($host);

        // �`�F�b�N
        if (isset($map[$type]) && isset($map[$type][$bbs])) {
            $new_host = $map[$type][$bbs]['host'];
            if ($host != $new_host && $autosync && !$synced) {
                // �ړ]�����o�����炨�C�ɔA���C�ɃX���A�ŋߓǂ񂾃X���������œ���
                $msg_fmt = '<p>rep2 info: �z�X�g�̈ړ]�����o���܂����B(%s/%s �� %s/%s)<br>';
                $msg_fmt .= '���C�ɔA���C�ɃX���A�ŋߓǂ񂾃X���������œ������܂��B</p>';
                $_info_msg_ht .= sprintf($msg_fmt, $host, $bbs, $new_host, $bbs);
                self::syncFav();
                $synced = true;
            }
            $host = $new_host;
        }

        return $host;
    }

    // }}}
    // {{{ getBbsName()

    /**
     * ��LONG���擾����
     *
     * @param   string  $host   �z�X�g��
     * @param   string  $bbs    ��
     * @return  string  ���j���[�ɋL�ڂ���Ă����
     */
    static public function getBbsName($host, $bbs)
    {
        // �}�b�s���O�ǂݍ���
        $map = self::_getMapping();
        if (!$map) {
            return $bbs;
        }
        $type = self::_detectHostType($host);

        // �`�F�b�N
        if (isset($map[$type]) && isset($map[$type][$bbs])) {
            $itaj = $map[$type][$bbs]['itaj'];
        } else {
            $itaj = $bbs;
        }

        return $itaj;
    }

    // }}}
    // {{{ syncBrd()

    /**
     * ���C�ɔȂǂ�brd�t�@�C���𓯊�����
     *
     * @param   string  $brd_path   brd�t�@�C���̃p�X
     * @return  void
     */
    static public function syncBrd($brd_path)
    {
        global $_conf, $_info_msg_ht;
        static $done = array();

        // {{{ �Ǎ�

        if (isset($done[$brd_path])) {
            return;
        }

        if (!($lines = FileCtl::file_read_lines($brd_path))) {
            return;
        }
        $map = self::_getMapping();
        if (!$map) {
            return;
        }
        $neolines = array();
        $updated = false;

        // }}}
        // {{{ ����

        foreach ($lines as $line) {
            $setitaj = false;
            $data = explode("\t", rtrim($line, "\n"));
            $hoge = $data[0]; // �\��?
            $host = $data[1];
            $bbs  = $data[2];
            $itaj = $data[3];
            $type = self::_detectHostType($host);

            if (isset($map[$type]) && isset($map[$type][$bbs])) {
                $newhost = $map[$type][$bbs]['host'];
                if ($itaj === '') {
                    $itaj = $map[$type][$bbs]['itaj'];
                    if ($itaj != $bbs) {
                        $setitaj = true;
                    } else {
                        $itaj = '';
                    }
                }
            } else {
                $newhost = $host;
            }

            if ($host != $newhost || $setitaj) {
                $neolines[] = "{$hoge}\t{$newhost}\t{$bbs}\t{$itaj}\n";
                $updated = true;
            } else {
                $neolines[] = $line;
            }
        }

        // }}}
        // {{{ ����

        $brd_name = htmlspecialchars(basename($brd_path), ENT_QUOTES);
        if ($updated) {
            self::_writeData($brd_path, $neolines);
            $_info_msg_ht .= sprintf('<p class="info-msg">rep2 info: %s �𓯊����܂����B</p>', $brd_name);
        } else {
            $_info_msg_ht .= sprintf('<p class="info-msg">rep2 info: %s �͕ύX����܂���ł����B</p>', $brd_name);
        }
        $done[$brd_path] = true;

        // }}}
    }

    // }}}
    // {{{ syncIdx()

    /**
     * ���C�ɃX���Ȃǂ�idx�t�@�C���𓯊�����
     *
     * @param   string  $idx_path   idx�t�@�C���̃p�X
     * @return  void
     */
    static public function syncIdx($idx_path)
    {
        global $_conf, $_info_msg_ht;
        static $done = array();

        // {{{ �Ǎ�

        if (isset($done[$idx_path])) {
            return;
        }

        if (!($lines = FileCtl::file_read_lines($idx_path))) {
            return;
        }
        $map = self::_getMapping();
        if (!$map) {
            return;
        }
        $neolines = array();
        $updated = false;

        // }}}
        // {{{ ����

        foreach ($lines as $line) {
            $data = explode('<>', rtrim($line, "\n"));
            $host = $data[10];
            $bbs  = $data[11];
            $type = self::_detectHostType($host);

            if (isset($map[$type]) && isset($map[$type][$bbs])) {
                $newhost = $map[$type][$bbs]['host'];
            } else {
                $newhost = $host;
            }

            if ($host != $newhost) {
                $data[10] = $newhost;
                $neolines[] = implode('<>', $data) . "\n";
                $updated = true;
            } else {
                $neolines[] = $line;
            }
        }

        // }}}
        // {{{ ����

        $idx_name = htmlspecialchars(basename($idx_path), ENT_QUOTES);
        if ($updated) {
            self::_writeData($idx_path, $neolines);
            $_info_msg_ht .= sprintf('<p class="info-msg">rep2 info: %s �𓯊����܂����B</p>', $idx_name);
        } else {
            $_info_msg_ht .= sprintf('<p class="info-msg">rep2 info: %s �͕ύX����܂���ł����B</p>', $idx_name);
        }
        $done[$idx_path] = true;

        // }}}
    }

    // }}}
    // {{{ syncFav()

    /**
     * ���C�ɔA���C�ɃX���A�ŋߓǂ񂾃X���𓯊�����
     *
     * @return  void
     */
    static public function syncFav()
    {
        global $_conf;
        self::syncBrd($_conf['favita_brd']);
        self::syncIdx($_conf['favlist_idx']);
        self::syncIdx($_conf['recent_idx']);
    }

    // }}}
    // {{{ _getMapping()

    /**
     * 2ch�������j���[���p�[�X���A��-�z�X�g�̑Ή��\���쐬����
     *
     * @return  array   site/bbs/(host,itaj) �̑������A�z�z��
     *                  �_�E�����[�h�Ɏ��s�����Ƃ��� false
     */
    static private function _getMapping()
    {
        global $_conf, $_info_msg_ht;

        // {{{ �ݒ�

        $bbsmenu_url = 'http://menu.2ch.net/bbsmenu.html';  // �������j���[�� URL
        $altmenu_url = 'http://www.2ch.se/bbsmenu.html';    // ��փ��j���[�� URL
        $map_cache_path = $_conf['cache_dir'] . '/host_bbs_map.txt';
        $map_cache_lifetime = 600; // TTL�͏����Z�߂�
        $err_fmt = '<p>rep2 error: BbsMap: %s - %s ���_�E�����[�h�ł��܂���ł����B</p>';
        $use_alt = false;

        // }}}
        // {{{ �L���b�V���m�F

        if (!is_null(self::$_map)) {
            return self::$_map;
        } elseif (file_exists($map_cache_path)) {
            $mtime = filemtime($map_cache_path);
            $expires = $mtime + $map_cache_lifetime;
            if (time() < $expires) {
                $map_cahce = file_get_contents($map_cache_path);
                self::$_map = unserialize($map_cahce);
                return self::$_map;
            }
        } else {
            FileCtl::mkdir_for($map_cache_path);
        }
        touch($map_cache_path);
        clearstatcache();

        // }}}
        // {{{ ���j���[���_�E�����[�h

        if (!class_exists('HTTP_Request', false)) {
            require 'HTTP/Request.php';
        }

        $params = array();
        $params['timeout'] = $_conf['fsockopen_time_limit'];
        //$params['readTimeout'] = array($_conf['fsockopen_time_limit'], 0);
        if (isset($mtime)) {
            $params['requestHeaders'] = array('If-Modified-Since' => http_date($mtime));
        }
        if ($_conf['proxy_use']) {
            $params['proxy_host'] = $_conf['proxy_host'];
            $params['proxy_port'] = $_conf['proxy_port'];
        }
        $req = new HTTP_Request($bbsmenu_url, $params);
        $req->setMethod('GET');
        $err = $req->sendRequest(true);

        // �G���[�̂Ƃ��A����̃��j���[���g���Ă݂�
        if (PEAR::isError($err) && $use_alt) {
            $_info_msg_ht .= sprintf($err_fmt, htmlspecialchars($err->getMessage(), ENT_QUOTES), htmlspecialchars($bbsmenu_url, ENT_QUOTES));
            $_info_msg_ht .= sprintf("<p>����� %s ���_�E�����[�h���܂��B</p>", htmlspecialchars($altmenu_url, ENT_QUOTES));
            $bbsmenu_url = $altmenu_url;
            unset ($req, $err);
            $req = new HTTP_Request($bbsmenu_url, $params);
            $req->setMethod('GET');
            $err = $req->sendRequest(true);
        }

        // �G���[������
        if (PEAR::isError($err)) {
            $_info_msg_ht .= sprintf($err_fmt, htmlspecialchars($err->getMessage(), ENT_QUOTES), htmlspecialchars($bbsmenu_url, ENT_QUOTES));
            if (file_exists($map_cache_path)) {
                return unserialize(file_get_contents($map_cache_path));
            } else {
                return false;
            }
        }

        // ���X�|���X�R�[�h������
        $code = $req->getResponseCode();
        if ($code == 304) {
            $map_cahce = file_get_contents($map_cache_path);
            self::$_map = unserialize($map_cahce);
            return self::$_map;
        } elseif ($code != 200) {
            $_info_msg_ht .= sprintf($err_fmt, htmlspecialchars(strval($code), ENT_QUOTES), htmlspecialchars($bbsmenu_url, ENT_QUOTES));
            if (file_exists($map_cache_path)) {
                return unserialize(file_get_contents($map_cache_path));
            } else {
                return false;
            }
        }

        $res_body = $req->getResponseBody();

        // }}}
        // {{{ �p�[�X

        $regex = '!<A HREF=http://(\w+\.(?:2ch\.net|bbspink\.com|machi\.to|mathibbs\.com))/(\w+)/(?: TARGET=_blank)?>(.+?)</A>!';
        preg_match_all($regex, $res_body, $matches, PREG_SET_ORDER);

        $map = array();
        foreach ($matches as $match) {
            $host = $match[1];
            $bbs  = $match[2];
            $itaj = $match[3];
            $type = self::_detectHostType($host);
            if (!isset($map[$type])) {
                $map[$type] = array();
            }
            $map[$type][$bbs] = array('host' => $host, 'itaj' => $itaj);
        }

        // }}}
        // {{{ �L���b�V������

        $map_cache = serialize($map);
        if (FileCtl::file_write_contents($map_cache_path, $map_cache) === false) {
            p2die("cannot write file. ({$map_cache_path})");
        }

        // }}}

        return (self::$_map = $map);
    }

    // }}}
    // {{{ _writeData()

    /**
     * �X�V��̃f�[�^����������
     *
     * @param   string  $path   �������ރt�@�C���̃p�X
     * @param   array   $neolines   �������ރf�[�^�̔z��
     * @return  void
     */
    static private function _writeData($path, $neolines)
    {
        if (is_array($neolines) && count($neolines) > 0) {
            $cont = implode('', $neolines);
        /*} elseif (is_scalar($neolines)) {
            $cont = strval($neolines);*/
        } else {
            $cont = '';
        }
        if (FileCtl::file_write_contents($path, $cont) === false) {
            p2die("cannot write file. ({$path})");
        }
    }

    // }}}
    // {{{ _detectHostType()

    /**
     * �z�X�g�̎�ނ𔻒肷��
     *
     * @param   string  $host   �z�X�g��
     * @return  string  �z�X�g�̎��
     */
    static private function _detectHostType($host)
    {
        if (P2Util::isHostBbsPink($host)) {
            $type = 'bbspink';
        } elseif (P2Util::isHost2chs($host)) {
            $type = '2channel';
        } elseif (P2Util::isHostMachiBbs($host)) {
            $type = 'machibbs';
        } elseif (P2Util::isHostJbbsShitaraba($host)) {
            $type = 'jbbs';
        } else {
            $type = $host;
        }
        return $type;
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
