<?php

require_once P2_LIB_DIR . '/DataPhp.php';
require_once P2_LIB_DIR . '/FileCtl.php';

// {{{ P2Util

/**
 * rep2 - p2�p�̃��[�e�B���e�B�N���X
 * �C���X�^���X����炸�ɃN���X���\�b�h�ŗ��p����
 *
 * @create  2004/07/15
 * @static
 */
class P2Util
{
    // {{{ properties

    /**
     * getItaName() �̃L���b�V��
     */
    static private $_itaNames = array();

    /**
     * _p2DirOfHost() �̃L���b�V��
     */
    static private $_hostDirs = array();

    /**
     * isHost2chs() �̃L���b�V��
     */
    static private $_hostIs2chs = array();

    /**
     * isHostBe2chNet() �̃L���b�V��
     */
    //static private $_hostIsBe2chNet = array();

    /**
     * isHostBbsPink() �̃L���b�V��
     */
    static private $_hostIsBbsPink = array();

    /**
     * isHostMachiBbs() �̃L���b�V��
     */
    static private $_hostIsMachiBbs = array();

    /**
     * isHostMachiBbsNet() �̃L���b�V��
     */
    static private $_hostIsMachiBbsNet = array();

    /**
     * isHostJbbsShitaraba() �̃L���b�V��
     */
    static private $_hostIsJbbsShitaraba = array();

    // }}}
    // {{{ fileDownload()

    /**
     *  �t�@�C�����_�E�����[�h�ۑ�����
     */
    static public function fileDownload($url, $localfile, $disp_error = 1)
    {
        global $_conf, $_info_msg_ht;

        $perm = (isset($_conf['dl_perm'])) ? $_conf['dl_perm'] : 0606;

        if (file_exists($localfile)) {
            $modified = http_date(filemtime($localfile));
        } else {
            $modified = false;
        }

        // DL
        if (!class_exists('WapRequest', false)) {
            require P2_LIB_DIR . '/Wap.php';
        }
        $wap_ua = new WapUserAgent();
        $wap_ua->setTimeout($_conf['fsockopen_time_limit']);
        $wap_req = new WapRequest();
        $wap_req->setUrl($url);
        $wap_req->setModified($modified);
        if ($_conf['proxy_use']) {
            $wap_req->setProxy($_conf['proxy_host'], $_conf['proxy_port']);
        }
        $wap_res = $wap_ua->request($wap_req);

        if ($wap_res->isError() && $disp_error) {
            $url_t = self::throughIme($wap_req->url);
            $_info_msg_ht .= "<div>Error: {$wap_res->code} {$wap_res->message}<br>";
            $_info_msg_ht .= "p2 info: <a href=\"{$url_t}\"{$_conf['ext_win_target_at']}>{$wap_req->url}</a> �ɐڑ��ł��܂���ł����B</div>";
        }

        // �X�V����Ă�����
        if ($wap_res->isSuccess() && $wap_res->code != 304) {
            if (FileCtl::file_write_contents($localfile, $wap_res->content) === false) {
                p2die('cannot write file.');
            }
            chmod($localfile, $perm);
        }

        return $wap_res;
    }

    // }}}
    // {{{ checkDirWritable()

    /**
     * �p�[�~�b�V�����̒��ӂ����N����
     */
    static public function checkDirWritable($aDir)
    {
        global $_info_msg_ht, $_conf;

        // �}���`���[�U���[�h���́A��񃁃b�Z�[�W��}�����Ă���B

        if (!is_dir($aDir)) {
            /*
            $_info_msg_ht .= '<p class="infomsg">';
            $_info_msg_ht .= '����: �f�[�^�ۑ��p�f�B���N�g��������܂���B<br>';
            $_info_msg_ht .= $aDir."<br>";
            */
            if (is_dir(dirname(realpath($aDir))) && is_writable(dirname(realpath($aDir)))) {
                //$_info_msg_ht .= "�f�B���N�g���̎����쐬�����݂܂�...<br>";
                if (mkdir($aDir, $_conf['data_dir_perm'])) {
                    //$_info_msg_ht .= "�f�B���N�g���̎����쐬���������܂����B";
                    chmod($aDir, $_conf['data_dir_perm']);
                } else {
                    //$_info_msg_ht .= "�f�B���N�g���������쐬�ł��܂���ł����B<br>�蓮�Ńf�B���N�g�����쐬���A�p�[�~�b�V������ݒ肵�ĉ������B";
                }
            } else {
                    //$_info_msg_ht .= "�f�B���N�g�����쐬���A�p�[�~�b�V������ݒ肵�ĉ������B";
            }
            //$_info_msg_ht .= '</p>';

        } elseif (!is_writable($aDir)) {
            $_info_msg_ht .= '<p class="infomsg">����: �f�[�^�ۑ��p�f�B���N�g���ɏ������݌���������܂���B<br>';
            //$_info_msg_ht .= $aDir.'<br>';
            $_info_msg_ht .= '�f�B���N�g���̃p�[�~�b�V�������������ĉ������B</p>';
        }
    }

    // }}}
    // {{{ cacheFileForDL()

    /**
     * �_�E�����[�hURL����L���b�V���t�@�C���p�X��Ԃ�
     */
    static public function cacheFileForDL($url)
    {
        global $_conf;

        $parsed = parse_url($url); // URL����

        $save_uri  = isset($parsed['host'])  ?       $parsed['host']  : '';
        $save_uri .= isset($parsed['port'])  ? ':' . $parsed['port']  : '';
        $save_uri .= isset($parsed['path'])  ?       $parsed['path']  : '';
        $save_uri .= isset($parsed['query']) ? '?' . $parsed['query'] : '';

        $cachefile = $_conf['cache_dir'] . '/' . $save_uri;

        FileCtl::mkdir_for($cachefile);

        return $cachefile;
    }

    // }}}
    // {{{ getItaName()

    /**
     *  host��bbs�������Ԃ�
     */
    static public function getItaName($host, $bbs)
    {
        global $_conf;

        $id = $host . '/' . $bbs;

        if (array_key_exists($id, self::$_itaNames)) {
            return self::$_itaNames[$id];
        }

        $p2_setting_txt = self::idxDirOfHostBbs($host, $bbs) . 'p2_setting.txt';

        if (file_exists($p2_setting_txt)) {

            $p2_setting_cont = FileCtl::file_read_contents($p2_setting_txt);
            if ($p2_setting_cont) {
                $p2_setting = unserialize($p2_setting_cont);
                if (isset($p2_setting['itaj'])) {
                    self::$_itaNames[$id] = $p2_setting['itaj'];
                    return self::$_itaNames[$id];
                }
            }
        }

        // ��Long�̎擾
        if (!isset($p2_setting['itaj'])) {
            require_once P2_LIB_DIR . '/BbsMap.php';
            $itaj = BbsMap::getBbsName($host, $bbs);
            if ($itaj != $bbs) {
                self::$_itaNames[$id] = $p2_setting['itaj'] = $itaj;

                FileCtl::make_datafile($p2_setting_txt, $_conf['p2_perm']);
                $p2_setting_cont = serialize($p2_setting);
                if (FileCtl::file_write_contents($p2_setting_txt, $p2_setting_cont) === false) {
                    p2die("{$p2_setting_txt} ���X�V�ł��܂���ł���");
                }
                return self::$_itaNames[$id];
            }
        }

        return null;
    }

    // }}}
    // {{{ _p2DirOfHost()

    /**
     * host����rep2�̊e��f�[�^�ۑ��f�B���N�g����Ԃ�
     *
     * @param string $base_dir
     * @param string $host
     * @param bool $dir_sep
     * @return string
     */
    static private function _p2DirOfHost($base_dir, $host, $dir_sep = true)
    {
        $key = $base_dir . DIRECTORY_SEPARATOR . $host;
        if (array_key_exists($key, self::$_hostDirs)) {
            if ($dir_sep) {
                return self::$_hostDirs[$key] . DIRECTORY_SEPARATOR;
            }
            return self::$_hostDirs[$key];
        }

        $host = self::normalizeHostName($host);

        // 2channel or bbspink
        if (self::isHost2chs($host)) {
            $host_dir = $base_dir . DIRECTORY_SEPARATOR . '2channel';

        // machibbs.com
        } elseif (self::isHostMachiBbs($host)) {
            $host_dir = $base_dir . DIRECTORY_SEPARATOR . 'machibbs.com';

        // jbbs.livedoor.jp (livedoor �����^���f����)
        } elseif (self::isHostJbbsShitaraba($host)) {
            if (DIRECTORY_SEPARATOR == '/') {
                $host_dir = $base_dir . DIRECTORY_SEPARATOR . $host;
            } else {
                $host_dir = $base_dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $host);
            }

        // livedoor �����^���f���ȊO�ŃX���b�V�����̕������܂ނƂ�
        } elseif (preg_match('/[^0-9A-Za-z.\\-_]/', $host)) {
            $host_dir = $base_dir . DIRECTORY_SEPARATOR . rawurlencode($host);
            /*
            if (DIRECTORY_SEPARATOR == '/') {
                $old_host_dir = $base_dir . DIRECTORY_SEPARATOR . $host;
            } else {
                $old_host_dir = $base_dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $host);
            }
            if (is_dir($old_host_dir)) {
                rename($old_host_dir, $host_dir);
                clearstatcache();
            }
            */

        // ���̑�
        } else {
            $host_dir = $base_dir . DIRECTORY_SEPARATOR . $host;
        }

        // �L���b�V������
        self::$_hostDirs[$key] = $host_dir;

        // �f�B���N�g����؂蕶����ǉ�
        if ($dir_sep) {
            $host_dir .= DIRECTORY_SEPARATOR;
        }

        return $host_dir;
    }

    // }}}
    // {{{ datDirOfHost()

    /**
     * host����dat�̕ۑ��f�B���N�g����Ԃ�
     * �Â��R�[�h�Ƃ̌݊��̂��߁A�f�t�H���g�ł̓f�B���N�g����؂蕶����ǉ����Ȃ�
     *
     * @param string $host
     * @param bool $dir_sep
     * @return string
     * @see P2Util::_p2DirOfHost()
     */
    static public function datDirOfHost($host, $dir_sep = false)
    {
        return self::_p2DirOfHost($GLOBALS['_conf']['dat_dir'], $host, $dir_sep);
    }

    // }}}
    // {{{ idxDirOfHost()

    /**
     * host����idx�̕ۑ��f�B���N�g����Ԃ�
     * �Â��R�[�h�Ƃ̌݊��̂��߁A�f�t�H���g�ł̓f�B���N�g����؂蕶����ǉ����Ȃ�
     *
     * @param string $host
     * @param bool $dir_sep
     * @return string
     * @see P2Util::_p2DirOfHost()
     */
    static public function idxDirOfHost($host, $dir_sep = false)
    {
        return self::_p2DirOfHost($GLOBALS['_conf']['idx_dir'], $host, $dir_sep);
    }

    // }}}
    // {{{ datDirOfHostBbs()

    /**
     * host,bbs����dat�̕ۑ��f�B���N�g����Ԃ�
     * �f�t�H���g�Ńf�B���N�g����؂蕶����ǉ�����
     *
     * @param string $host
     * @param string $bbs
     * @param bool $dir_sep
     * @return string
     * @see P2Util::_p2DirOfHost()
     */
    static public function datDirOfHostBbs($host, $bbs, $dir_sep = true)
    {
        $dir = self::_p2DirOfHost($GLOBALS['_conf']['dat_dir'], $host) . $bbs;
        if ($dir_sep) {
            $dir .= DIRECTORY_SEPARATOR;
        }
        return $dir;
    }

    // }}}
    // {{{ idxDirOfHostBbs()

    /**
     * host,bbs����idx�̕ۑ��f�B���N�g����Ԃ�
     * �f�t�H���g�Ńf�B���N�g����؂蕶����ǉ�����
     *
     * @param string $host
     * @param string $bbs
     * @param bool $dir_sep
     * @return string
     * @see P2Util::_p2DirOfHost()
     */
    static public function idxDirOfHostBbs($host, $bbs, $dir_sep = true)
    {
        $dir = self::_p2DirOfHost($GLOBALS['_conf']['idx_dir'], $host) . $bbs;
        if ($dir_sep) {
            $dir .= DIRECTORY_SEPARATOR;
        }
        return $dir;
    }

    // }}}
    // {{{ getFailedPostFilePath()

    /**
     *  failed_post_file �̃p�X�𓾂�֐�
     */
    static public function getFailedPostFilePath($host, $bbs, $key = false)
    {
        if ($key) {
            $filename = $key.'.failed.data.php';
        } else {
            $filename = 'failed.data.php';
        }
        return $failed_post_file = self::idxDirOfHostBbs($host, $bbs) . $filename;
    }

    // }}}
    // {{{ getListNaviRange()

    /**
     * ���X�g�̃i�r�͈͂�Ԃ�
     */
    static public function getListNaviRange($disp_from, $disp_range, $disp_all_num)
    {
        if (!$disp_all_num) {
            return array(
                'all_once'  => true,
                'from'      => 0,
                'end'       => 0,
                'limit'     => 0,
                'offset'    => 0,
                'mae_from'  => 1,
                'tugi_from' => 1,
                'range_st'  => '-',
            );
        }

        $disp_from = max(1, $disp_from);
        $disp_range = max(0, $disp_range - 1);
        $disp_navi = array();

        $disp_navi['all_once'] = false;
        $disp_navi['from'] = $disp_from;

        // from���z����
        if ($disp_navi['from'] > $disp_all_num) {
            $disp_navi['from'] = max(1, $disp_all_num - $disp_range);
            $disp_navi['end'] = $disp_all_num;

        // from �z���Ȃ�
        } else {
            $disp_navi['end'] = $disp_navi['from'] + $disp_range;

            // end �z����
            if ($disp_navi['end'] > $disp_all_num) {
                $disp_navi['end'] = $disp_all_num;
                if ($disp_navi['from'] == 1) {
                    $disp_navi['all_once'] = true;
                }
            }
        }

        $disp_navi['offset'] = $disp_navi['from'] - 1;
        $disp_navi['limit'] = $disp_navi['end'] - $disp_navi['offset'];

        $disp_navi['mae_from'] = max(1, $disp_navi['offset'] - $disp_range);
        $disp_navi['tugi_from'] = min($disp_all_num, $disp_navi['end']) + 1;


        if ($disp_navi['from'] == $disp_navi['end']) {
            $range_on_st = $disp_navi['from'];
        } else {
            $range_on_st = "{$disp_navi['from']}-{$disp_navi['end']}";
        }
        $disp_navi['range_st'] = "{$range_on_st}/{$disp_all_num} ";

        return $disp_navi;
    }

    // }}}
    // {{{ recKeyIdx()

    /**
     *  key.idx �� data ���L�^����
     *
     * @param   array   $data   �v�f�̏��ԂɈӖ�����B
     */
    static public function recKeyIdx($keyidx, $data)
    {
        global $_conf;

        // ��{�͔z��Ŏ󂯎��
        if (is_array($data)) {
            $cont = implode('<>', $data);
        // ���݊��p��string����t
        } else {
            $cont = rtrim($data);
        }

        $cont = $cont . "\n";

        FileCtl::make_datafile($keyidx, $_conf['key_perm']);
        if (FileCtl::file_write_contents($keyidx, $cont) === false) {
            p2die('cannot write file.');
        }

        return true;
    }

    // }}}
    // {{{ cachePathForCookie()

    /**
     * �z�X�g����N�b�L�[�t�@�C���p�X��Ԃ�
     */
    static public function cachePathForCookie($host)
    {
        global $_conf;

        $host = self::normalizeHostName($host);

        if (preg_match('/[^.0-9A-Za-z.\\-_]/', $host)) {
            if (self::isHostJbbsShitaraba($host)) {
                if (DIRECTORY_SEPARATOR == '/') {
                    $cookie_host_dir = $_conf['cookie_dir'] . DIRECTORY_SEPARATOR . $host;
                } else {
                    $cookie_host_dir = $_conf['cookie_dir'] . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $host);
                }
            } else {
                $cookie_host_dir = $_conf['cookie_dir'] . DIRECTORY_SEPARATOR . rawurlencode($host);
                /*
                if (DIRECTORY_SEPARATOR == '/') {
                    $old_cookie_host_dir = $_conf['cookie_dir'] . DIRECTORY_SEPARATOR . $host;
                } else {
                    $old_cookie_host_dir = $_conf['cookie_dir'] . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $host);
                }
                if (is_dir($old_cookie_host_dir)) {
                    rename($old_cookie_host_dir, $cookie_host_dir);
                    clearstatcache();
                }
                */
            }
        } else {
            $cookie_host_dir = $_conf['cookie_dir'] . DIRECTORY_SEPARATOR . $host;
        }
        $cachefile = $cookie_host_dir . DIRECTORY_SEPARATOR . $_conf['cookie_file_name'];

        FileCtl::mkdir_for($cachefile);

        return $cachefile;
    }

    // }}}
    // {{{ throughIme()

    /**
     * ���p�Q�[�g��ʂ����߂�URL�ϊ�
     */
    static public function throughIme($url)
    {
        global $_conf;
        static $manual_exts = null;

        if (is_null($manual_exts)) {
            if ($_conf['ime_manual_ext']) {
                $manual_exts = explode(',', trim($_conf['ime_manual_ext']));
            } else {
                $manual_exts = array();
            }
        }

        $url_en = rawurlencode($url);

        $gate = $_conf['through_ime'];
        if ($manual_exts &&
            false !== ($ppos = strrpos($url, '.')) &&
            in_array(substr($url, $ppos + 1), $manual_exts) &&
            ($gate == 'p2' || $gate == 'ex')
        ) {
            $gate .= 'm';
        }

        // p2ime�́Aenc, m, url �̈����������Œ肳��Ă���̂Œ���
        switch ($gate) {
        case '2ch':
            $url_r = preg_replace('|^(\w+)://(.+)$|', '$1://ime.nu/$2', $url);
            break;
        case 'p2':
        case 'p2pm':
            $url_r = $_conf['p2ime_url'].'?enc=1&amp;url='.$url_en;
            break;
        case 'p2m':
            $url_r = $_conf['p2ime_url'].'?enc=1&amp;m=1&amp;url='.$url_en;
            break;
        case 'ex':
        case 'expm':
            $url_r = $_conf['expack.ime_url'].'?u='.$url_en.'&amp;d=1';
            break;
        case 'exq':
            $url_r = $_conf['expack.ime_url'].'?u='.$url_en.'&amp;d=0';
            break;
        case 'exm':
            $url_r = $_conf['expack.ime_url'].'?u='.$url_en.'&amp;d=-1';
            break;
        default:
            $url_r = $url;
        }

        return $url_r;
    }

    // }}}
    // {{{ normalizeHostName()

    /**
     * host�𐳋K������
     *
     * @param string $host
     * @return string
     */
    static public function normalizeHostName($host)
    {
        $host = trim($host, '/');
        if (($sp = strpos($host, '/')) !== false) {
            return strtolower(substr($host, 0, $sp)) . substr($host, $sp);
        }
        return strtolower($host);
    }

    // }}}
    // {{ isHostExample

    /**
     * host ���Ꭶ�p�h���C���Ȃ� true ��Ԃ�
     *
     * @param string $host
     * @return bool
     */
    static public function isHostExample($host)
    {
        return (bool)preg_match('/(?:^|\\.)example\\.(?:com|net|org|jp)$/i', $host);
    }

    // }}}
    // {{{ isHost2chs()

    /**
     * host �� 2ch or bbspink �Ȃ� true ��Ԃ�
     *
     * @param string $host
     * @return bool
     */
    static public function isHost2chs($host)
    {
        if (!array_key_exists($host, self::$_hostIs2chs)) {
            self::$_hostIs2chs[$host] = (bool)preg_match('<^\\w+\\.(?:2ch\\.net|bbspink\\.com)$>', $host);
        }
        return self::$_hostIs2chs[$host];
    }

    // }}}
    // {{{ isHostBe2chNet()

    /**
     * host �� be.2ch.net �Ȃ� true ��Ԃ�
     *
     * @param string $host
     * @return bool
     */
    static public function isHostBe2chNet($host)
    {
        return ($host == 'be.2ch.net');
        /*
        if (!array_key_exists($host, self::$_hostIsBe2chNet)) {
            self::$_hostIsBe2chNet[$host] = ($host == 'be.2ch.net');
        }
        return self::$_hostIsBe2chNet[$host];
        */
    }

    // }}}
    // {{{ isHostBbsPink()

    /**
     * host �� bbspink �Ȃ� true ��Ԃ�
     *
     * @param string $host
     * @return bool
     */
    static public function isHostBbsPink($host)
    {
        if (!array_key_exists($host, self::$_hostIsBbsPink)) {
            self::$_hostIsBbsPink[$host] = (bool)preg_match('<^\\w+\\.bbspink\\.com$>', $host);
        }
        return self::$_hostIsBbsPink[$host];
    }

    // }}}
    // {{{ isHostMachiBbs()

    /**
     * host �� machibbs �Ȃ� true ��Ԃ�
     *
     * @param string $host
     * @return bool
     */
    static public function isHostMachiBbs($host)
    {
        if (!array_key_exists($host, self::$_hostIsMachiBbs)) {
            self::$_hostIsMachiBbs[$host] = (bool)preg_match('<^\\w+\\.machi(?:bbs\\.com|\\.to)$>', $host);
        }
        return self::$_hostIsMachiBbs[$host];
    }

    // }}}
    // {{{ isHostMachiBbsNet()

    /**
     * host �� machibbs.net �܂��r�˂��� �Ȃ� true ��Ԃ�
     *
     * @param string $host
     * @return bool
     */
    static public function isHostMachiBbsNet($host)
    {
        if (!array_key_exists($host, self::$_hostIsMachiBbsNet)) {
            self::$_hostIsMachiBbsNet[$host] = (bool)preg_match('<^\\w+\\.machibbs\\.net$>', $host);
        }
        return self::$_hostIsMachiBbsNet[$host];
    }

    // }}}
    // {{{ isHostJbbsShitaraba()

    /**
     * host �� livedoor �����^���f���� : ������� �Ȃ� true ��Ԃ�
     *
     * @param string $host
     * @return bool
     */
    static public function isHostJbbsShitaraba($in_host)
    {
        if (!array_key_exists($in_host, self::$_hostIsJbbsShitaraba)) {
            if ($in_host == 'rentalbbs.livedoor.com') {
                self::$_hostIsJbbsShitaraba[$in_host] = true;
            } elseif (preg_match('<^jbbs\\.(?:shitaraba\\.com|livedoor\\.(?:com|jp))(?:/|$)>', $in_host)) {
                self::$_hostIsJbbsShitaraba[$in_host] = true;
            } else {
                self::$_hostIsJbbsShitaraba[$in_host] = false;
            }
        }
        return self::$_hostIsJbbsShitaraba[$in_host];
    }

    // }}}
    // {{{ adjustHostJbbs()

    /**
     * livedoor �����^���f���� : ������΂̃z�X�g���ύX�ɑΉ����ĕύX����
     *
     * @param   string  $in_str     �z�X�g���ł�URL�ł��Ȃ�ł��ǂ�
     * @return  string
     */
    static public function adjustHostJbbs($in_str)
    {
        return preg_replace('<(^|/)jbbs\\.(?:shitaraba|livedoor)\\.com(/|$)>', '\\1jbbs.livedoor.jp\\2', $in_str, 1);
        //return preg_replace('<(^|/)jbbs\\.(?:shitaraba\\.com|livedoor\\.(?:com|jp))(/|$)>', '\\1rentalbbs.livedoor.com\\2', $in_str, 1);
    }

    // }}}
    // {{{ header_nocache()

    /**
     * http header no cache ���o��
     *
     * @return void
     */
    static public function header_nocache()
    {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // ���t���ߋ�
        header("Last-Modified: " . http_date()); // ��ɏC������Ă���
        header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache"); // HTTP/1.0
    }

    // }}}
    // {{{ header_content_type()

    /**
     * HTTP header Content-Type �o��
     *
     * @param string $content_type
     * @return void
     */
    static public function header_content_type($content_type = null)
    {
        if ($content_type) {
            if (strpos($content_type, 'Content-Type: ') === 0) {
                header($content_type);
            } else {
                header('Content-Type: ' . $content_type);
            }
        } else {
            header('Content-Type: text/html; charset=Shift_JIS');
        }
    }

    // }}}
    // {{{ transResHistLogPhpToDat()

    /**
     * �f�[�^PHP�`���iTAB�j�̏������ݗ�����dat�`���iTAB�j�ɕϊ�����
     *
     * �ŏ��́Adat�`���i<>�j�������̂��A�f�[�^PHP�`���iTAB�j�ɂȂ�A�����Ă܂� v1.6.0 ��dat�`���i<>�j�ɖ߂���
     */
    static public function transResHistLogPhpToDat()
    {
        global $_conf;

        // �������ݗ������L�^���Ȃ��ݒ�̏ꍇ�͉������Ȃ�
        if ($_conf['res_write_rec'] == 0) {
            return true;
        }

        // p2_res_hist.dat.php ���ǂݍ��݉\�ł�������
        if (is_readable($_conf['res_hist_dat_php'])) {
            // �ǂݍ����
            if ($cont = DataPhp::getDataPhpCont($_conf['res_hist_dat_php'])) {
                // �^�u��؂肩��<>��؂�ɕύX����
                $cont = str_replace("\t", "<>", $cont);

                // p2_res_hist.dat ������΁A���O��ς��ăo�b�N�A�b�v�B�i�����v��Ȃ��j
                if (file_exists($_conf['res_hist_dat'])) {
                    $bak_file = $_conf['res_hist_dat'] . '.bak';
                    if (P2_OS_WINDOWS && file_exists($bak_file)) {
                        unlink($bak_file);
                    }
                    rename($_conf['res_hist_dat'], $bak_file);
                }

                // �ۑ�
                FileCtl::make_datafile($_conf['res_hist_dat'], $_conf['res_write_perm']);
                FileCtl::file_write_contents($_conf['res_hist_dat'], $cont);

                // p2_res_hist.dat.php �𖼑O��ς��ăo�b�N�A�b�v�B�i�����v��Ȃ��j
                $bak_file = $_conf['res_hist_dat_php'] . '.bak';
                if (P2_OS_WINDOWS && file_exists($bak_file)) {
                    unlink($bak_file);
                }
                rename($_conf['res_hist_dat_php'], $bak_file);
            }
        }
        return true;
    }

    // }}}
    // {{{ transResHistLogDatToPhp()

    /**
     * dat�`���i<>�j�̏������ݗ������f�[�^PHP�`���iTAB�j�ɕϊ�����
     */
    static public function transResHistLogDatToPhp()
    {
        global $_conf;

        // �������ݗ������L�^���Ȃ��ݒ�̏ꍇ�͉������Ȃ�
        if ($_conf['res_write_rec'] == 0) {
            return true;
        }

        // p2_res_hist.dat.php ���Ȃ��āAp2_res_hist.dat ���ǂݍ��݉\�ł�������
        if ((!file_exists($_conf['res_hist_dat_php'])) and is_readable($_conf['res_hist_dat'])) {
            // �ǂݍ����
            if ($cont = FileCtl::file_read_contents($_conf['res_hist_dat'])) {
                // <>��؂肩��^�u��؂�ɕύX����
                // �܂��^�u��S�ĊO����
                $cont = str_replace("\t", "", $cont);
                // <>���^�u�ɕϊ�����
                $cont = str_replace("<>", "\t", $cont);

                // �f�[�^PHP�`���ŕۑ�
                DataPhp::writeDataPhp($_conf['res_hist_dat_php'], $cont, $_conf['res_write_perm']);
            }
        }
        return true;
    }

    // }}}
    // {{{ getLastAccessLog()

    /**
     * �O��̃A�N�Z�X�����擾
     */
    static public function getLastAccessLog($logfile)
    {
        // �ǂݍ����
        if (!$lines = DataPhp::fileDataPhp($logfile)) {
            return false;
        }
        if (!isset($lines[1])) {
            return false;
        }
        $line = rtrim($lines[1]);
        $lar = explode("\t", $line);

        $alog['user'] = $lar[6];
        $alog['date'] = $lar[0];
        $alog['ip'] = $lar[1];
        $alog['host'] = $lar[2];
        $alog['ua'] = $lar[3];
        $alog['referer'] = $lar[4];

        return $alog;
    }

    // }}}
    // {{{ recAccessLog()

    /**
     * �A�N�Z�X�������O�ɋL�^����
     */
    static public function recAccessLog($logfile, $maxline = 100, $format = 'dataphp')
    {
        global $_conf, $_login;

        // ���O�t�@�C���̒��g���擾����
        if ($format == 'dataphp') {
            $lines = DataPhp::fileDataPhp($logfile);
        } else {
            $lines = FileCtl::file_read_lines($logfile);
        }

        if ($lines) {
            // �����s����
            while (sizeof($lines) > $maxline -1) {
                array_pop($lines);
            }
        } else {
            $lines = array();
        }
        $lines = array_map('rtrim', $lines);

        // �ϐ��ݒ�
        $date = date('Y/m/d (D) G:i:s');

        // HOST���擾
        if (!$remoto_host = $_SERVER['REMOTE_HOST']) {
            $remoto_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        }
        if ($remoto_host == $_SERVER['REMOTE_ADDR']) {
            $remoto_host = "";
        }

        $user = (isset($_login->user_u)) ? $_login->user_u : "";

        // �V�������O�s��ݒ�
        $newdata = $date."<>".$_SERVER['REMOTE_ADDR']."<>".$remoto_host."<>".$_SERVER['HTTP_USER_AGENT']."<>".$_SERVER['HTTP_REFERER']."<>".""."<>".$user;
        //$newdata = htmlspecialchars($newdata, ENT_QUOTES);

        // �܂��^�u��S�ĊO����
        $newdata = str_replace("\t", "", $newdata);
        // <>���^�u�ɕϊ�����
        $newdata = str_replace("<>", "\t", $newdata);

        // �V�����f�[�^����ԏ�ɒǉ�
        @array_unshift($lines, $newdata);

        $cont = implode("\n", $lines) . "\n";

        FileCtl::make_datafile($logfile, $_conf['p2_perm']);

        // �������ݏ���
        if ($format == 'dataphp') {
            DataPhp::writeDataPhp($logfile, $cont, $_conf['p2_perm']);
        } else {
            FileCtl::file_write_contents($logfile, $cont);
        }

        return true;
    }

    // }}}
    // {{{ isBrowserSafariGroup()

    /**
     * �u���E�U��Safari�n�Ȃ�true��Ԃ�
     */
    static public function isBrowserSafariGroup()
    {
        return (strpos($_SERVER['HTTP_USER_AGENT'], 'Safari')      !== false ||
                strpos($_SERVER['HTTP_USER_AGENT'], 'AppleWebKit') !== false ||
                strpos($_SERVER['HTTP_USER_AGENT'], 'Konqueror')   !== false);
    }

    // }}}
    // {{{ isClientOSWindowsCE()

    /**
     * �u���E�U��Windows CE�œ��삷����̂Ȃ�true��Ԃ�
     */
    static public function isClientOSWindowsCE()
    {
        return (strpos($_SERVER['HTTP_USER_AGENT'], 'Windows CE') !== false);
    }

    // }}}
    // {{{ isBrowserNintendoDS()

    /**
     * �j���e���h�[DS�u���E�U�[�Ȃ�true��Ԃ�
     */
    static public function isBrowserNintendoDS()
    {
        return (strpos($_SERVER['HTTP_USER_AGENT'], 'Nitro') !== false &&
                strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') !== false);
    }

    // }}}
    // {{{ isBrowserPSP()

    /**
     * �u���E�U��PSP�Ȃ�true��Ԃ�
     */
    static public function isBrowserPSP()
    {
        return (strpos($_SERVER['HTTP_USER_AGENT'], 'PlayStation Portable') !== false);
    }

    // }}}
    // {{{ isBrowserIphone()

    /**
     * �u���E�U��iPhone or iPod Touch�Ȃ�true��Ԃ�
     */
    static public function isBrowserIphone()
    {
        return (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') !== false ||
                strpos($_SERVER['HTTP_USER_AGENT'], 'iPod')   !== false);
    }

    // }}}
    // {{{ isUrlWikipediaJa()

    /**
     * URL���E�B�L�y�f�B�A���{��ł̋L���Ȃ�P�ꕔ����Ԃ�
     */
    static public function isUrlWikipediaJa($url)
    {
        if (strncmp($url, 'http://ja.wikipedia.org/wiki/', 29) == 0) {
		return substr($url,29);
	} else {
		return false;
	}
    }

    // }}}
    // {{{ saveIdPw2ch()

    /**
     * 2ch�����O�C����ID��PASS�Ǝ������O�C���ݒ��ۑ�����
     */
    static public function saveIdPw2ch($login2chID, $login2chPW, $autoLogin2ch = '')
    {
        global $_conf;

        require_once P2_LIB_DIR . '/md5_crypt.inc.php';

        $md5_crypt_key = self::getAngoKey();
        $crypted_login2chPW = md5_encrypt($login2chPW, $md5_crypt_key, 32);
        $idpw2ch_cont = <<<EOP
<?php
\$rec_login2chID = '{$login2chID}';
\$rec_login2chPW = '{$crypted_login2chPW}';
\$rec_autoLogin2ch = '{$autoLogin2ch}';
?>
EOP;
        FileCtl::make_datafile($_conf['idpw2ch_php'], $_conf['pass_perm']);    // �t�@�C�����Ȃ���ΐ���
        $fp = @fopen($_conf['idpw2ch_php'], 'wb');
        if (!$fp) {
            p2die("{$_conf['idpw2ch_php']} ���X�V�ł��܂���ł���");
        }
        flock($fp, LOCK_EX);
        fputs($fp, $idpw2ch_cont);
        flock($fp, LOCK_UN);
        fclose($fp);

        return true;
    }

    // }}}
    // {{{ readIdPw2ch()

    /**
     * 2ch�����O�C���̕ۑ��ς�ID��PASS�Ǝ������O�C���ݒ��ǂݍ���
     */
    static public function readIdPw2ch()
    {
        global $_conf;

        require_once P2_LIB_DIR . '/md5_crypt.inc.php';

        if (!file_exists($_conf['idpw2ch_php'])) {
            return false;
        }

        $rec_login2chID = NULL;
        $login2chPW = NULL;
        $rec_autoLogin2ch = NULL;

        include $_conf['idpw2ch_php'];

        // �p�X�𕡍���
        if (!is_null($rec_login2chPW)) {
            $md5_crypt_key = self::getAngoKey();
            $login2chPW = md5_decrypt($rec_login2chPW, $md5_crypt_key, 32);
        }

        return array($rec_login2chID, $login2chPW, $rec_autoLogin2ch);
    }

    // }}}
    // {{{ getAngoKey()

    /**
     * getAngoKey
     */
    static public function getAngoKey()
    {
        global $_login;

        return $_login->user . $_SERVER['SERVER_NAME'] . $_SERVER['SERVER_SOFTWARE'];
    }

    // }}}
    // {{{ getCsrfId()

    /**
     * getCsrfId
     */
    static public function getCsrfId()
    {
        global $_login;

        return md5($_login->user . $_login->pass_x . $_SERVER['HTTP_USER_AGENT']);
    }

    // }}}
    // {{{ print403()

    /**
     * 403 Fobbiden���o�͂���
     */
    static public function print403($msg = '')
    {
        header('HTTP/1.0 403 Forbidden');
        echo <<<ERR
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <title>403 Forbidden</title>
</head>
<body>
    <h1>403 Forbidden</h1>
    <p>{$msg}</p>
</body>
</html>
ERR;
        // IE�f�t�H���g�̃��b�Z�[�W��\�������Ȃ��悤�ɃX�y�[�X���o��
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {
            for ($i = 0 ; $i < 512; $i++) {
                echo ' ';
            }
        }
        exit;
    }

    // }}}
    // {{{ scandir_r()

    /**
     * �ċA�I�Ƀf�B���N�g���𑖍�����
     *
     * ���X�g���t�@�C���ƃf�B���N�g���ɕ����ĕԂ��B���ꂻ��̃��X�g�͒P���Ȕz��
     */
    static public function scandir_r($dir)
    {
        $dir = realpath($dir);
        $list = array('files' => array(), 'dirs' => array());
        $files = scandir($dir);
        foreach ($files as $filename) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }
            $filename = $dir . DIRECTORY_SEPARATOR . $filename;
            if (is_dir($filename)) {
                $child = self::scandir_r($filename);
                if ($child) {
                    $list['dirs'] = array_merge($list['dirs'], $child['dirs']);
                    $list['files'] = array_merge($list['files'], $child['files']);
                }
                $list['dirs'][] = $filename;
            } else {
                $list['files'][] = $filename;
            }
        }
        return $list;
    }

    // }}}
    // {{{ garbageCollection()

    /**
     * ������ЂƂ̃K�x�R��
     *
     * $targetDir����ŏI�X�V���$lifeTime�b�ȏソ�����t�@�C�����폜
     *
     * @param   string   $targetDir  �K�[�x�b�W�R���N�V�����Ώۃf�B���N�g��
     * @param   integer  $lifeTime   �t�@�C���̗L�������i�b�j
     * @param   string   $prefix     �Ώۃt�@�C�����̐ړ����i�I�v�V�����j
     * @param   string   $suffix     �Ώۃt�@�C�����̐ڔ����i�I�v�V�����j
     * @param   boolean  $recurive   �ċA�I�ɃK�[�x�b�W�R���N�V�������邩�ۂ��i�f�t�H���g�ł�FALSE�j
     * @return  array    �폜�ɐ��������t�@�C���Ǝ��s�����t�@�C����ʁX�ɋL�^�����񎟌��̔z��
     */
    static public function garbageCollection($targetDir,
                                             $lifeTime,
                                             $prefix = '',
                                             $suffix = '',
                                             $recursive = false
                                             )
    {
        $result = array('successed' => array(), 'failed' => array(), 'skipped' => array());
        $expire = time() - $lifeTime;
        //�t�@�C�����X�g�擾
        if ($recursive) {
            $list = self::scandir_r($targetDir);
            $files = $list['files'];
        } else {
            $list = scandir($targetDir);
            $files = array();
            $targetDir = realpath($targetDir) . DIRECTORY_SEPARATOR;
            foreach ($list as $filename) {
                if ($filename == '.' || $filename == '..') { continue; }
                $files[] = $targetDir . $filename;
            }
        }
        //�����p�^�[���ݒ�i$prefix��$suffix�ɃX���b�V�����܂܂Ȃ��悤�Ɂj
        if ($prefix || $suffix) {
            $prefix = (is_array($prefix)) ? implode('|', array_map('preg_quote', $prefix)) : preg_quote($prefix);
            $suffix = (is_array($suffix)) ? implode('|', array_map('preg_quote', $suffix)) : preg_quote($suffix);
            $pattern = '/^' . $prefix . '.+' . $suffix . '$/';
        } else {
            $pattern = '';
        }
        //�K�x�R���J�n
        foreach ($files as $filename) {
            if ($pattern && !preg_match($pattern, basename($filename))) {
                //$result['skipped'][] = $filename;
                continue;
            }
            if (filemtime($filename) < $expire) {
                if (@unlink($filename)) {
                    $result['successed'][] = $filename;
                } else {
                    $result['failed'][] = $filename;
                }
            }
        }
        return $result;
    }

    // }}}
    // {{{ session_gc()

    /**
     * �Z�b�V�����t�@�C���̃K�[�x�b�W�R���N�V����
     *
     * session.save_path�̃p�X�̐[����2���傫���ꍇ�A�K�[�x�b�W�R���N�V�����͍s���Ȃ�����
     * �����ŃK�[�x�b�W�R���N�V�������Ȃ��Ƃ����Ȃ��B
     *
     * @return  void
     *
     * @link http://jp.php.net/manual/ja/ref.session.php#ini.session.save-path
     */
    static public function session_gc()
    {
        global $_conf;

        if (session_module_name() != 'files') {
            return;
        }

        $d = (int)ini_get('session.gc_divisor');
        $p = (int)ini_get('session.gc_probability');
        mt_srand();
        if (mt_rand(1, $d) <= $p) {
            $m = (int)ini_get('session.gc_maxlifetime');
            self::garbageCollection($_conf['session_dir'], $m);
        }
    }

    // }}}
    // {{{ Info_Dump()

    /**
     * �������z����ċA�I�Ƀe�[�u���ɕϊ�����
     *
     * �Q�����˂��setting.txt���p�[�X�����z��p�̏������򂠂�
     * ���ʂɃ_���v����Ȃ� Var_Dump::display($value, TRUE) ��������
     * (�o�[�W����1.0.0�ȍ~�AVar_Dump::display() �̑��������^�̂Ƃ�
     *  ���ڕ\���������ɁA�_���v���ʂ�������Ƃ��ĕԂ�B)
     *
     * @param   array    $info    �e�[�u���ɂ������z��
     * @param   integer  $indent  ���ʂ�HTML�����₷�����邽�߂̃C���f���g��
     * @return  string   <table>~</table>
     */
    static public function Info_Dump($info, $indent = 0)
    {
        $table = '<table border="0" cellspacing="1" cellpadding="0">' . "\n";
        $n = count($info);
        foreach ($info as $key => $value) {
            if (!is_object($value) && !is_resource($value)) {
                for ($i = 0; $i < $indent; $i++) { $table .= "\t"; }
                if ($n == 1 && $key === 0) {
                    $table .= '<tr><td class="tdcont">';
                /*} elseif (preg_match('/^\w+$/', $key)) {
                    $table .= '<tr class="setting"><td class="tdleft"><b>' . $key . '</b></td><td class="tdcont">';*/
                } else {
                    $table .= '<tr><td class="tdleft"><b>' . $key . '</b></td><td class="tdcont">';
                }
                if (is_array($value)) {
                    $table .= self::Info_Dump($value, $indent+1); //�z��̏ꍇ�͍ċA�Ăяo���œW�J
                } elseif ($value === true) {
                    $table .= '<i>TRUE</i>';
                } elseif ($value === false) {
                    $table .= '<i>FALSE</i>';
                } elseif (is_null($value)) {
                    $table .= '<i>NULL</i>';
                } elseif (is_scalar($value)) {
                    if ($value === '') { //��O:�󕶎���B0���܂߂Ȃ��悤�Ɍ^���l�����Ĕ�r
                        $table .= '<i>(no value)</i>';
                    } elseif ($key == '���O�擾��<br>�X���b�h��') { //���O�폜��p
                        $table .= $value;
                    } elseif ($key == '���[�J�����[��') { //���[�J�����[����p
                        $table .= '<table border="0" cellspacing="1" cellpadding="0" class="child">';
                        $table .= "\n\t\t<tr><td id=\"rule\">{$value}</tr></td>\n\t</table>";
                    } elseif (preg_match('/^(https?|ftp):\/\/[\w\/\.\+\-\?=~@#%&:;]+$/i', $value)) { //�����N
                        $table .= '<a href="' . self::throughIme($value) . '" target="_blank">' . $value . '</a>';
                    } elseif ($key == '�w�i�F' || substr($key, -6) == '_COLOR') { //�J���[�T���v��
                        $table .= "<span class=\"colorset\" style=\"color:{$value};\">��</span>�i{$value}�j";
                    } else {
                        $table .= htmlspecialchars($value, ENT_QUOTES);
                    }
                }
                $table .= '</td></tr>' . "\n";
            }
        }
        for ($i = 1; $i < $indent; $i++) { $table .= "\t"; }
        $table .= '</table>';
        $table = str_replace('<td class="tdcont"><table border="0" cellspacing="1" cellpadding="0">',
            '<td class="tdcont"><table border="0" cellspacing="1" cellpadding="0" class="child">', $table);

        return $table;
    }

    // }}}
    // {{{ re_htmlspecialchars()

    /**
     * ["&<>]�����̎Q�ƂɂȂ��Ă��邩�ǂ����s���ȕ�����ɑ΂���htmlspecialchars()��������
     */
    static public function re_htmlspecialchars($str, $charset = 'Shift_JIS')
    {
        return htmlspecialchars($str, ENT_QUOTES, $charset, false);
    }

    // }}}
    // {{{ mkTrip()

    /**
     * �g���b�v�𐶐�����
     */
    static public function mkTrip($key)
    {
        if (strlen($key) < 12) {
            //if (strlen($key) > 8) {
            //    return self::mkTrip1(substr($key, 0, 8));
            //} else {
                return self::mkTrip1($key);
            //}
        }

        switch (substr($key, 0, 1)) {
            case '$';
                return '???';

            case '#':
                if (preg_match('|^#([0-9A-Fa-f]{16})([./0-9A-Za-z]{0,2})$|', $key, $matches)) {
                    return self::mkTrip1(pack('H*', $matches[1]), $matches[2]);
                } else {
                    return '???';
                }

            default:
                return self::mkTrip2($key);
        }
    }

    // }}}
    // {{{ mkTrip1()

    /**
     * �������g���b�v�𐶐�����
     */
    static public function mkTrip1($key, $length = 10, $salt = null)
    {
        if (is_null($salt)) {
            $salt = substr($key . 'H.', 1, 2);
        } else {
            $salt = substr($salt . '..', 0, 2);
        }
        $salt = preg_replace('/[^.-z]/', '.', $salt);
        $salt = strtr($salt, ':;<=>?@[\\]^_`', 'ABCDEFGabcdef');
        return substr(crypt($key, $salt), -$length);
    }

    // }}}
    // {{{ mkTrip2()

    /**
     * �V�����g���b�v�𐶐�����
     */
    static public function mkTrip2($key)
    {
        return str_replace('+', '.', substr(base64_encode(sha1($key, true)), 0, 12));
    }

    // }}}
    // {{{ getWebPage

    /**
     * Web�y�[�W���擾����
     *
     * 200 OK
     * 206 Partial Content
     * 304 Not Modified �� ���s����
     *
     * @return array|false ����������y�[�W���e��Ԃ��B���s������false��Ԃ��B
     */
    static public function getWebPage($url, &$error_msg, $timeout = 15)
    {
        if (!class_exists('HTTP_Request', false)) {
            require 'HTTP/Request.php';
        }

        $params = array("timeout" => $timeout);

        if (!empty($_conf['proxy_use'])) {
            $params['proxy_host'] = $_conf['proxy_host'];
            $params['proxy_port'] = $_conf['proxy_port'];
        }

        $req = new HTTP_Request($url, $params);
        //$req->addHeader("X-PHP-Version", phpversion());

        $response = $req->sendRequest();

        if (PEAR::isError($response)) {
            $error_msg = $response->getMessage();
        } else {
            $code = $req->getResponseCode();
            if ($code == 200 || $code == 206) { // || $code == 304) {
                return $req->getResponseBody();
            } else {
                //var_dump($req->getResponseHeader());
                $error_msg = $code;
            }
        }

        return false;
    }

    // }}}
    // {{{ getMyUrl()

    /**
     * ���݂�URL���擾����iGET�N�G���[�͂Ȃ��j
     *
     * @return string
     * @see http://ns1.php.gr.jp/pipermail/php-users/2003-June/016472.html
     */
    static public function getMyUrl()
    {
        $s = empty($_SERVER['HTTPS']) ? '' : 's';
        $url = "http{$s}://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
        // ��������
        //$port = ($_SERVER['SERVER_PORT'] == ($s ? 443 : 80)) ? '' : ':' . $_SERVER['SERVER_PORT'];
        //$url = "http{$s}://" . $_SERVER['SERVER_NAME'] . $port . $_SERVER['SCRIPT_NAME'];

        return $url;
    }

    // }}}
    // {{{ printSimpleHtml()

    /**
     * �V���v����HTML��\������
     *
     * @return void
     */
    static public function printSimpleHtml($body)
    {
        echo "<html><body>{$body}</body></html>";
    }

    // }}}
    // {{{ pushInfoHtml()

    /**
     * 2006/11/24 $_info_msg_ht �𒼐ڈ����̂͂�߂Ă��̃��\�b�h��ʂ�������
     *
     * @return  void
     */
    static public function pushInfoHtml($html)
    {
        global $_info_msg_ht;

        if (!isset($_info_msg_ht)) {
            $_info_msg_ht = $html;
        } else {
            $_info_msg_ht .= $html;
        }
    }

    // }}}
    // {{{ printInfoHtml()

    /**
     * @return  void
     */
    static public function printInfoHtml()
    {
        global $_info_msg_ht, $_conf;

        if (!isset($_info_msg_ht)) {
            return;
        }

        if ($_conf['ktai'] && $_conf['k_save_packet']) {
            echo mb_convert_kana($_info_msg_ht, 'rnsk');
        } else {
            echo $_info_msg_ht;
        }

        $_info_msg_ht = '';
    }

    // }}}
    // {{{ getInfoHtml()

    /**
     * @return  string|null
     */
    static public function getInfoHtml()
    {
        global $_info_msg_ht;

        if (!isset($_info_msg_ht)) {
            return null;
        }

        $info_msg_ht = $_info_msg_ht;
        $_info_msg_ht = '';

        return $info_msg_ht;
    }

    // }}}
    // {{{ isNetFront()

    /**
     * isNetFront?
     *
     * @return boolean
     */
    static public function isNetFront()
    {
        if (preg_match('/(NetFront|AVEFront\/|AVE-Front\/)/', $_SERVER['HTTP_USER_AGENT'])) {
            return true;
        } else {
            return false;
        }
    }

    // }}}
    // {{{ encodeResponseTextForSafari()

    /**
     * XMLHttpRequest�̃��X�|���X��Safari�p�ɃG���R�[�h����
     *
     * @return string
     */
    static public function encodeResponseTextForSafari($response, $encoding = 'CP932')
    {
        $response = mb_convert_encoding($response, 'UTF-8', $encoding);
        $response = mb_encode_numericentity($response, array(0x80, 0xFFFF, 0, 0xFFFF), 'UTF-8');
        return $response;
    }

    // }}}
    // {{{ detectThread()

    /**
     * �X���b�h�w������o����
     *
     * @param string $url
     * @return array
     */
    static public function detectThread($url = null)
    {
        if ($url) {
            $nama_url = $url;
        } elseif (isset($_GET['nama_url'])) {
            $nama_url = trim($_GET['nama_url']);
        } elseif (isset($_GET['url'])) {
            $nama_url = trim($_GET['url']);
        } else {
            $nama_url = null;
        }

        // �X��URL�̒��ڎw��
        if ($nama_url) {

            // 2ch or pink - http://choco.2ch.net/test/read.cgi/event/1027770702/
            //               http://same.ula.cc/test/r.so/anchorage.2ch.net/occult/1250118296/332?guid=ON 
            if (preg_match('<^http://(same\\.ula\\.cc/test/r\\.so/)?(\\w+\\.(?:2ch\\.net|bbspink\\.com))(/test/read\\.(?:cgi|html))?
                    /(\\w+)/([0-9]+)(?:/([^/]*))?>x', $nama_url, $matches)
                    && $matches[1] xor $matches[3]
                    )
            {
                $host = $matches[2];
                $bbs = $matches[4];
                $key = $matches[5];
                $ls = (isset($matches[6]) && strlen($matches[6])) ? $matches[6] : '';

            // 2ch or pink �ߋ����Ohtml - http://pc.2ch.net/mac/kako/1015/10153/1015358199.html
            } elseif (preg_match('<^(http://(\\w+\\.(?:2ch\\.net|bbspink\\.com))(?:/[^/]+)?/(\\w+)
                    /kako/\\d+(?:/\\d+)?/(\\d+)).html>x', $nama_url, $matches))
            {
                $host = $matches[2];
                $bbs = $matches[3];
                $key = $matches[4];
                $ls = '';
                $kakolog_url = $matches[1];
                $_GET['kakolog'] = rawurlencode($kakolog_url);

            // �܂�BBS - http://kanto.machi.to/bbs/read.cgi/kanto/1241815559/
            } elseif (preg_match('<^http://(\\w+\\.machi(?:bbs\\.com|\\.to))/bbs/read\\.cgi
                    /(\\w+)/([0-9]+)(?:/([^/]*))?>x', $nama_url, $matches))
            {
                $host = $matches[1];
                $bbs = $matches[2];
                $key = $matches[3];
                $ls = (isset($matches[4]) && strlen($matches[4])) ? $matches[4] : '';

            // �������JBBS - http://jbbs.livedoor.com/bbs/read.cgi/computer/2999/1081177036/-100
            } elseif (preg_match('<^http://(jbbs\\.(?:livedoor\\.(?:jp|com)|shitaraba\\.com))/bbs/read\\.cgi
                    /(\\w+)/(\\d+)/(\\d+)/((?:\\d+)?-(?:\\d+)?)?[^"]*>x', $nama_url, $matches))
            {
                $host = $matches[1] . '/' . $matches[2];
                $bbs = $matches[3];
                $key = $matches[4];
                $ls = isset($matches[5]) ? $matches[5] : '';

            // �����܂����������JBBS - http://kanto.machibbs.com/bbs/read.pl?BBS=kana&KEY=1034515019
            } elseif (preg_match('<^http://(\\w+\\.machi(?:bbs\\.com|\\.to))/bbs/read\\.(?:pl|cgi)\\?(.+)>' ,
                    $nama_url, $matches))
            {
                $host = $matches[1];
                list($bbs, $key, $ls) = self::parseMachiQuery($matches[2]);

            } elseif (preg_match('<^http://((jbbs\\.(?:livedoor\\.(?:jp|com)|shitaraba\\.com))(?:/(\\w+))?)/bbs/read\\.(?:pl|cgi)\\?(.+)>',
                    $nama_url, $matches))
            {
                $host = $matches[1];
                list($bbs, $key, $ls) = self::parseMachiQuery($matches[4]);

            } else {
                $host = null;
                $bbs = null;
                $key = null;
                $ls = null;
            }

            // �␳
            if ($ls == '-') {
                $ls = '';
            }

        } else {
            $host = isset($_REQUEST['host']) ? $_REQUEST['host'] : null; // "pc.2ch.net"
            $bbs  = isset($_REQUEST['bbs'])  ? $_REQUEST['bbs']  : null; // "php"
            $key  = isset($_REQUEST['key'])  ? $_REQUEST['key']  : null; // "1022999539"
            $ls   = isset($_REQUEST['ls'])   ? $_REQUEST['ls']   : null; // "all"
        }

        return array($nama_url, $host, $bbs, $key, $ls);
    }

    // }}}
    // {{{ parseMachiQuery()

    /**
     * �����܂����������JBBS�̃X���b�h���w�肷��QUERY_STRING����͂���
     *
     * @param   string  $query
     * @return  array
     */
    static public function parseMachiQuery($query)
    {
        parse_str($query, $params);

        if (array_key_exists('BBS', $params) && ctype_alnum($params['BBS'])) {
            $bbs = $params['BBS'];
        } else {
            $bbs = null;
        }

        if (array_key_exists('KEY', $params) && ctype_digit($params['KEY'])) {
            $key = $params['KEY'];
        } else {
            $key = null;
        }

        if (array_key_exists('LAST', $params) && ctype_digit($params['LAST'])) {
            $ls = 'l' . $params['LAST'];
        } else {
            $ls = '';
            if (array_key_exists('START', $params) && ctype_digit($params['START'])) {
                $ls = $params['START'];
            }
            $ls .= '-';
            if (array_key_exists('END', $params) && ctype_digit($params['END'])) {
                $ls .= $params['END'];
            }
        }

        return array($bbs, $key, $ls);
    }

    // }}}
    // {{{ getHtmlDom()

    /**
     * HTML����DOMDocument�𐶐�����
     *
     * @param   string  $html
     * @param   string  $charset
     * @param   bool    $report_error
     * @return  DOMDocument
     */
    static public function getHtmlDom($html, $charset = null, $report_error = true)
    {
        if ($charset) {
            $charset = str_replace(array('$', '\\'), array('\\$', '\\\\'), $charset);
            $html = preg_replace(
                '{<head>(.*?)(?:<meta http-equiv="Content-Type" content="text/html(?:; ?charset=.+?)?">)(.*)</head>}is',
                '<head><meta http-equiv="Content-Type" content="text/html; charset=' . $charset . '">$1$2</head>',
                $html, 1, $count);
            if (!$count) {
                $html = preg_replace(
                    '{<head>}i',
                    '<head><meta http-equiv="Content-Type" content="text/html; charset=' . $charset . '">',
                    $html, 1);
            }
        }

        $erl = error_reporting(E_ALL & ~E_WARNING);
        try {
            $doc = new DOMDocument();
            $doc->loadHTML($html);
            error_reporting($erl);
            return $doc;
        } catch (DOMException $e) {
            error_reporting($erl);
            if ($report_error) {
                trigger_error($e->getMessage(), E_USER_WARNING);
            }
            return null;
        }
    }

    // }}}
    // {{{ getHostGroupName()

    /**
     * �z�X�g�ɑΉ����邨�C�ɔE���C�ɃX���O���[�v�����擾����
     *
     * @param string $host
     * @return void
     */
    static public function getHostGroupName($host)
    {
        if (self::isHost2chs($host)) {
            return '2channel';
        } elseif (self::isHostMachiBbs($host)) {
            return 'machibbs';
        } elseif (self::isHostJbbsShitaraba($host)) {
            return 'shitaraba';
        } else {
            return $host;
        }
    }

    // }}}
    // {{{ debug()
    /*
    static public function debug()
    {
        echo PHP_EOL;
        echo '/', '*', '<pre>', PHP_EOL;
        echo htmlspecialchars(print_r(self::$_hostDirs, true)), PHP_EOL;
        echo htmlspecialchars(print_r(array_map('intval', self::$_hostIs2chs), true)), PHP_EOL;
        //echo htmlspecialchars(print_r(array_map('intval', self::$_hostIsBe2chNet), true)), PHP_EOL;
        echo htmlspecialchars(print_r(array_map('intval', self::$_hostIsBbsPink), true)), PHP_EOL;
        echo htmlspecialchars(print_r(array_map('intval', self::$_hostIsMachiBbs), true)), PHP_EOL;
        echo htmlspecialchars(print_r(array_map('intval', self::$_hostIsMachiBbsNet), true)), PHP_EOL;
        echo htmlspecialchars(print_r(array_map('intval', self::$_hostIsJbbsShitaraba), true)), PHP_EOL;
        echo '</pre>', '*', '/', PHP_EOL;
    }
    */
    // }}}
}

// }}}

//register_shutdown_function(array('P2Util', 'debug'));

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
