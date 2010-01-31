<?php

// {{{ Thread

/**
 * rep2 - �X���b�h�N���X
 */
class Thread
{
    // {{{ properties

    public $ttitle;     // �X���^�C�g�� // idxline[0] // < �� &lt; �������肷��
    public $key;        // �X���b�hID // idxline[1]
    public $length;     // local Dat Bytes(int) // idxline[2]
    public $gotnum;     //�i�l�ɂƂ��Ắj�������X�� // idxline[3]
    public $rescount;   // �X���b�h�̑����X���i���擾�����܂ށj
    public $modified;   // dat��Last-Modified // idxline[4]
    public $readnum;    // ���ǃ��X�� // idxline[5] // MacMoe�ł̓��X�\���ʒu�������Ǝv���ilast res�j
    public $fav;        //���C�ɓ���(bool�I��) // idxline[6] favlist.idx���Q��
    /*
    public $favs;       //���C�ɓ���Z�b�g�o�^���(bool�̔z��)
    */
    protected $_favs;   //���C�ɓ���Z�b�g�o�^���(bool�̔z��)
    /*
    public $name;       // �����ł͗��p���� idxline[7]�i�����ŗ��p�j
    public $mail;       // �����ł͗��p���� idxline[8]�i�����ŗ��p�j
    */
    public $newline;    // ���̐V�K�擾���X�ԍ� // idxline[9] �p�~�\��B���݊��̂��ߎc���Ă͂���B

    // ��host�Ƃ͂������̂́A2ch�O�̏ꍇ�́Ahost�ȉ��̃f�B���N�g���܂Ŋ܂܂�Ă����肷��B
    public $host;       // ex)pc.2ch.net // idxline[10]
    public $bbs;        // ex)mac // idxline[11]
    public $itaj;       // �� ex)�V�Emac

    public $datochiok;  // DAT�����擾�����������TRUE(1) // idxline[12]

    public $torder;     // �X���b�h�V�������ԍ�
    public $unum;       // ���ǁi�V�����X�j��
    public $nunum;      // �\�[�g�̂��߂̒��߂Ȃ��̖��ǐ�

    public $keyidx;     // idx�t�@�C���p�X
    public $keydat;     // ���[�J��dat�t�@�C���p�X

    public $isonline;   // �T�[�o�ɂ����true�Bsubject.txt��dat�擾���Ɋm�F���ăZ�b�g�����B
    public $new;        // �V�K�X���Ȃ�true

    /*
    public $ttitle_hc;  // < �� &lt; �ł������肷��̂ŁA�f�R�[�h�����X���^�C�g��
    public $ttitle_hd;  // HTML�\���p�ɁA�G���R�[�h���ꂽ�X���^�C�g��
    public $ttitle_ht;  // �X���^�C�g���\���pHTML�R�[�h�B�t�B���^�����O��������Ă�������B
    */
    protected $_ttitle_hc;  // < �� &lt; �ł������肷��̂ŁA�f�R�[�h�����X���^�C�g��
    protected $_ttitle_hd;  // HTML�\���p�ɁA�G���R�[�h���ꂽ�X���^�C�g��
    protected $_ttitle_ht;  // �X���^�C�g���\���pHTML�R�[�h�B�t�B���^�����O��������Ă�������B

    public $dayres;     // ���������̃��X���B�����B

    public $dat_type;   // dat�̌`���i2ch�̋��`��dat�i,��؂�j�Ȃ�"2ch_old"�j

    public $ls = '';    // �\�����X�ԍ��̎w��

    public $similarity; // �^�C�g���̗ގ���

    protected $_unknown_props;

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     */
    public function __construct()
    {
        $this->_ttitle_hc = null;
        $this->_ttitle_hd = null;
        $this->_ttitle_ht = null;
        $this->nunum = 0;
    }

    // }}}
    // {{{ __get()

    /**
     * �Q�b�^�[
     *
     * ����K�v�łȂ��A�����R�X�g�̂�����v���p�e�B
     * (ttitle_hc, ttitle_hd, ttitle_ht, favs)
     * ��K�v�ɂȂ����Ƃ��ɐݒ�E�擾����
     *
     * _unknown_props �͗\��
     *
     * @param   string  $name
     * @return  mixed
     */
    public function __get($name)
    {
        switch ($name) {
        case 'ttitle_hc':
            return $this->getTtitleHc();
        case 'ttitle_hd':
            return $this->getTtitleHd();
        case 'ttitle_ht':
            return $this->getTtitleHt();
        case 'favs':
            return $this->getFavStatus();
        default:
            if (!is_array($this->_unknown_props)) {
                $this->_unknown_props = array();
            }
            if (array_key_exists($name, $this->_unknown_props)) {
                return $this->_unknown_props[$name];
            }
            return null;
        }
    }

    // }}}
    // {{{ __set()

    /**
     * �Z�b�^�[
     *
     * ttitle_hc, ttitle_hd, ttitle_ht ��C�ӂ̒l�ɐݒ肷��
     *
     * _unknown_props �͗\��
     *
     * @param   string  $name
     * @param   mixed   $value
     * @return  void
     */
    public function __set($name, $value)
    {
        switch ($name) {
        case 'ttitle_hc':
            $this->_ttitle_hc = $value;
            break;
        case 'ttitle_hd':
            $this->_ttitle_hd = $value;
            break;
        case 'ttitle_ht':
            $this->_ttitle_ht = $value;
            break;
        default:
            if (!is_array($this->_unknown_props)) {
                $this->_unknown_props = array();
            }
            $this->_unknown_props[$name] = $value;
        }
    }

    // }}}
    // {{{ setTtitle()

    /**
     * ttitle���Z�b�g����
     */
    public function setTtitle($ttitle)
    {
        $this->ttitle = $ttitle;
    }

    // }}}
    // {{{ getTtitleHc()

    /**
     * HTML�̓��ꕶ�����f�R�[�h�����X���^�C�g�����擾����
     */
    public function getTtitleHc()
    {
        if ($this->_ttitle_hc === null) {
            // < �� &lt; �ł������肷��̂ŁA�f�R�[�h����
            //$this->_ttitle_hc = html_entity_decode($this->ttitle, ENT_COMPAT, 'Shift_JIS');

            // html_entity_decode() �͌��\�d���̂ő�ցA�A���������Ɣ������炢�̏�������
            $this->_ttitle_hc = str_replace(array('&lt;', '&gt;', '&amp;', '&quot;'),
                                            array('<'   , '>'   , '&'    , '"'     ), $this->ttitle);
        }
        return $this->_ttitle_hc;
    }

    // }}}
    // {{{ getTtitleHd()

    /**
     * HTML�\���p�ɓ��ꕶ�����G���R�[�h�����X���^�C�g�����擾����
     */
    public function getTtitleHd()
    {
        if ($this->_ttitle_hd === null) {
            // HTML�\���p�� htmlspecialchars() ��������
            $this->_ttitle_hd = htmlspecialchars($this->ttitle, ENT_QUOTES, 'Shift_JIS', false);
        }
        return $this->_ttitle_hd;
    }

    // }}}
    // {{{ getTtitleHt()

    /**
     * HTML�\���p�ɒ������ꂽ�X���^�C�g�����擾����
     */
    public function getTtitleHt()
    {
        global $_conf;

        if ($this->_ttitle_ht === null) {
            // �ꗗ�\���p�ɒ�����؂�l�߂Ă��� htmlspecialchars() ��������
            if ($_conf['ktai']) {
                $tt_max_len = $_conf['mobile.sb_ttitle_max_len'];
                $tt_trim_len = $_conf['mobile.sb_ttitle_trim_len'];
                $tt_trim_pos = $_conf['mobile.sb_ttitle_trim_pos'];
            } else {
                $tt_max_len = $_conf['sb_ttitle_max_len'];
                $tt_trim_len = $_conf['sb_ttitle_trim_len'];
                $tt_trim_pos = $_conf['sb_ttitle_trim_pos'];
            }

            $ttitle_hc = $this->getTtitleHc();
            $ttitle_len = strlen($ttitle_hc);

            if ($tt_max_len > 0 && $ttitle_len > $tt_max_len && $ttitle_len > $tt_trim_len) {
                switch ($tt_trim_pos) {
                case -1:
                    $a_ttitle = '... ';
                    $a_ttitle .= mb_strcut($ttitle_hc, $ttitle_len - $tt_trim_len);
                    break;
                case 0:
                    $trim_len = floor($tt_trim_len / 2);
                    $a_ttitle = mb_strcut($ttitle_hc, 0, $trim_len);
                    $a_ttitle .= ' ... ';
                    $a_ttitle .= mb_strcut($ttitle_hc, $ttitle_len - $trim_len);
                    break;
                case 1:
                default:
                    $a_ttitle = mb_strcut($ttitle_hc, 0, $tt_trim_len);
                    $a_ttitle .= ' ...';
                }
                $this->_ttitle_ht = htmlspecialchars($a_ttitle, ENT_QUOTES);
            } else {
                $this->_ttitle_ht = $this->getTtitleHd();
            }
        }
        return $this->_ttitle_ht;
    }

    // }}}
    // {{{ getThreadInfoFromExtIdxLine()

    /**
     * fav, recent�p�̊g��idx���X�g���烉�C���f�[�^���擾����
     */
    public function getThreadInfoFromExtIdxLine($l)
    {
        $la = explode('<>', rtrim($l));
        $this->host = $la[10];
        $this->bbs = $la[11];
        $this->key = $la[1];

        if (!$this->ttitle) {
            if ($la[0]) {
                $this->setTtitle(rtrim($la[0]));
            }
        }

        //$this->fav = (int)$la[6];
    }

    // }}}
    // {{{ setThreadPathInfo()

    /**
     * Set Path info
     */
    public function setThreadPathInfo($host, $bbs, $key)
    {
        //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('setThreadPathInfo()');

        $this->host = $host;
        $this->bbs = $bbs;
        $this->key = $key;

        $this->keydat = $this->getDatDir() . $key . '.dat';
        $this->keyidx = $this->getIdxDir() . $key . '.idx';

        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('setThreadPathInfo()');

        return true;
    }

    // }}}
    // {{{ isKitoku()

    /**
     * �X���b�h�������ς݂Ȃ�true��Ԃ�
     */
    public function isKitoku()
    {
        // if (file_exists($this->keyidx)) {
        if ($this->gotnum || $this->readnum || $this->newline > 1) {
            return true;
        }
        return false;
    }

    // }}}
    // {{{ getThreadInfoFromIdx()

    /**
     * �����X���b�h�f�[�^��key.idx����擾����
     */
    public function getThreadInfoFromIdx()
    {
        //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('getThreadInfoFromIdx');

        if (!$lines = FileCtl::file_read_lines($this->keyidx)) {
            //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('getThreadInfoFromIdx');
            return false;
        }

        $key_line = rtrim($lines[0]);
        $lar = explode('<>', $key_line);
        if (!$this->ttitle) {
            if ($lar[0]) {
                $this->setTtitle(rtrim($lar[0]));
            }
        }

        if ($lar[5]) {
            $this->readnum = intval($lar[5]);

        // ���݊��[�u�i$lar[9] newline�̔p�~�j
        } elseif ($lar[9]) {
            $this->readnum = $lar[9] -1;
        }

        if ($lar[3]) {
            $this->gotnum = intval($lar[3]);

            if ($this->rescount) {
                $this->unum = $this->rescount - $this->readnum;
                // machi bbs ��subject�̍X�V�Ƀf�B���C������悤�Ȃ̂Œ������Ă���
                if ($this->unum < 0) {
                    $this->unum = 0;
                }
                $this->nunum = $this->unum;
            }
        } else {
            $this->gotnum = 0;
        }

        $this->fav = (int)$lar[6]; // ������bool�łȂ�

        if (isset($lar[12])) {
            $this->datochiok = $lar[12];
        }

        /*
        // ����key.idx�̂��̃J�����͎g�p���Ă��Ȃ��Bdat�T�C�Y�͒��ڃt�@�C���̑傫����ǂݎ���Ē��ׂ�
        if ($lar[2]) {
            $this->length = $lar[2];
        }
        */
        if ($lar[4]) { $this->modified = $lar[4]; }

        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('getThreadInfoFromIdx');

        return $key_line;
    }

    // }}}
    // {{{ getDatBytesFromLocalDat()

    /**
     * ���[�J��DAT�̃t�@�C���T�C�Y���擾����
     */
    public function getDatBytesFromLocalDat()
    {
        clearstatcache();
        if (file_exists($this->keydat)) {
            $this->length = filesize($this->keydat);
        } else {
            $this->length = 0;
        }
        return $this->length;
    }

    // }}}
    // {{{ getThreadInfoFromSubjectTxtLine()

    /**
     * subject.txt �̈�s����X�������擾����
     */
    public function getThreadInfoFromSubjectTxtLine($l)
    {
        //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('getThreadInfoFromSubjectTxtLine()');

        if (preg_match('/^([0-9]+)\\.(?:dat|cgi)(?:,|<>)(.+) ?(?:\\(|�i)([0-9]+)(?:\\)|�j)/', $l, $matches)) {
            $this->isonline = true;
            $this->key = $matches[1];
            $this->setTtitle(rtrim($matches[2]));
            $this->rescount = (int)$matches[3];
            if ($this->readnum) {
                $this->unum = $this->rescount - $this->readnum;
                // machi bbs ��sage��subject�̍X�V���s���Ȃ������Ȃ̂Œ������Ă���
                if ($this->unum < 0) {
                    $this->unum = 0;
                }
                $this->nunum = $this->unum;
            }

            //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('getThreadInfoFromSubjectTxtLine()');
            return TRUE;
        }

        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('getThreadInfoFromSubjectTxtLine()');
        return FALSE;
    }

    // }}}
    // {{{ setTitleFromLocal()

    /**
     * �X���^�C�g���擾���\�b�h
     */
    public function setTitleFromLocal()
    {
        if (!isset($this->ttitle)) {

            if ($this->datlines) {
                $firstdatline = rtrim($this->datlines[0]);
                $d = $this->explodeDatLine($firstdatline);
                $this->setTtitle($d[4]);

            // ���[�J��dat��1�s�ڂ���擾
            } elseif (is_readable($this->keydat)) {
                $fd = fopen($this->keydat, "rb");
                $l = fgets($fd, 32800);
                fclose($fd);
                $firstdatline = rtrim($l);
                if (strpos($firstdatline, '<>') !== false) {
                    $datline_sepa = "<>";
                } else {
                    $datline_sepa = ",";
                    $this->dat_type = "2ch_old";
                }
                $d = explode($datline_sepa, $firstdatline);
                $this->setTtitle($d[4]);

                // be.2ch.net �Ȃ�EUC��SJIS�ϊ�
                if (P2Util::isHostBe2chNet($this->host)) {
                    $ttitle = mb_convert_encoding($this->ttitle, 'CP932', 'CP51932');
                    $this->setTtitle($ttitle);
                }
            }

        }

        return $this->ttitle;
    }

    // }}}
    // {{{ getMotoThread()

    /**
     * ���X��URL��Ԃ�
     *
     * @param   bool    $force_pc   true�Ȃ�g�у��[�h�ł�PC�p�̌��X��URL��Ԃ�
     * @param   string  $ls         ���X�\���ԍ�or�͈́Bnull�Ȃ�ls�v���p�e�B���g��
     *                              �f���ɂ���Ă͖��������ꍇ������
     * @return  string  ���X��URL
     */
    public function getMotoThread($force_pc = false, $ls = null)
    {
        global $_conf;

        if ($force_pc) {
            $mobile = false;
        } elseif ($_conf['iphone']) {
            $mobile = false;
        } elseif ($_conf['ktai']) {
            $mobile = true;
        } else {
            $mobile = false;
        }

        if ($ls === null) {
            $ls = $this->ls;
        }

        // 2ch�n
        if (P2Util::isHost2chs($this->host)) {
            // PC
            if (!$mobile) {
                $motothre_url = "http://{$this->host}/test/read.cgi/{$this->bbs}/{$this->key}/{$ls}";
            // �g��
            } else {
                if (P2Util::isHostBbsPink($this->host)) {
                    //$motothre_url = "http://{$this->host}/test/r.i/{$this->bbs}/{$this->key}/{$ls}";
                    $motothre_url = "http://speedo.ula.cc/test/r.so/{$this->host}/{$this->bbs}/{$this->key}/{$ls}"; 
                } else {
                    $mail = rawurlencode($_conf['my_mail']);
                    // c.2ch��l�w��ɔ�Ή��Ȃ̂ŁA�����n
                    $ls = (substr($ls, 0, 1) == 'l') ? 'n' : $ls;
                    $motothre_url = "http://c.2ch.net/test/--3!mail={$mail}/{$this->bbs}/{$this->key}/{$ls}";
                }
            }

        // �܂�BBS
        } elseif (P2Util::isHostMachiBbs($this->host)) {
            if ($mobile) {
                $motothre_url = "http://{$this->host}/bbs/read.pl?IMODE=TRUE&BBS={$this->bbs}&KEY={$this->key}";
            } else {
                $motothre_url = "http://{$this->host}/bbs/read.cgi/{$this->bbs}/{$this->key}/{$ls}";
            }

        // �܂��т˂���
        } elseif (P2Util::isHostMachiBbsNet($this->host)) {
            $motothre_url = "http://{$this->host}/test/read.cgi?bbs={$this->bbs}&key={$this->key}";
            if ($mobile) { $motothre_url .= '&imode=true'; }

        // JBBS�������
        } elseif (P2Util::isHostJbbsShitaraba($this->host)) {
            list($host, $category) = explode('/', P2Util::adjustHostJbbs($this->host), 2);
            $bbs_cgi = ($mobile) ? 'i.cgi' : 'read.cgi';
            $motothre_url = "http://{$host}/bbs/{$bbs_cgi}/{$category}/{$this->bbs}/{$this->key}/{$ls}";

        // ���̑�
        } else {
            $motothre_url = "http://{$this->host}/test/read.cgi/{$this->bbs}/{$this->key}/{$ls}";
        }

        return $motothre_url;
    }

    // }}}
    // {{{ setDayRes()

    /**
     * �����i���X/���j���Z�b�g����
     */
    public function setDayRes($nowtime = false)
    {
        //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('setDayRes()');

        if (!isset($this->key) || !isset($this->rescount)) {
            //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('setDayRes()');
            return false;
        }

        if (!$nowtime) {
            $nowtime = time();
        }
        if ($pastsc = $nowtime - $this->key) {
            $this->dayres = $this->rescount / $pastsc * 60 * 60 * 24;
            //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('setDayRes()');
            return true;
        }

        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('setDayRes()');
        return false;
    }

    // }}}
    // {{{ getTimePerRes()

    /**
     * ���X�Ԋu�i����/���X�j���擾����
     */
    public function getTimePerRes()
    {
        $noresult_st = "-";

        if (!isset($this->dayres)) {
            if (!$this->setDayRes(time())) {
                return $noresult_st;
            }
        }

        if ($this->dayres <= 0) {
            return $noresult_st;

        } elseif ($this->dayres < 1/365) {
            $spd = 1/365 / $this->dayres;
            $spd_suffix = "�N";
        } elseif ($this->dayres < 1/30.5) {
            $spd = 1/30.5 / $this->dayres;
            $spd_suffix = "����";
        } elseif ($this->dayres < 1) {
            $spd = 1 / $this->dayres;
            $spd_suffix = "��";
        } elseif ($this->dayres < 24) {
            $spd = 24 / $this->dayres;
            $spd_suffix = "����";
        } elseif ($this->dayres < 24*60) {
            $spd = 24*60 / $this->dayres;
            $spd_suffix = "��";
        } elseif ($this->dayres < 24*60*60) {
            $spd = 24*60*60 / $this->dayres;
            $spd_suffix = "�b";
        } else {
            $spd = 1;
            $spd_suffix = "�b�ȉ�";
        }
        if ($spd > 0) {
            $spd_st = sprintf("%01.1f", @round($spd, 2)) . $spd_suffix;
        } else {
            $spd_st = "-";
        }
        return $spd_st;
    }

    // }}}
    // {{{ getFavStatus()

    /**
     * ���C�ɓ���o�^��Ԃ��擾����
     */
    public function getFavStatus()
    {
        global $_conf;

        if (!is_array($this->_favs)) {
            if (!$_conf['expack.misc.multi_favs'] || $_conf['expack.misc.favset_num'] < 0) {
                $this->_favs = array($this->fav);
            } else {
                $this->_favs = array_fill(0, $_conf['expack.misc.favset_num'] + 1, false);
                $group = P2Util::getHostGroupName($this->host);
                foreach ($_conf['favlists'] as $num => $favlist) {
                    foreach ($favlist as $fav) {
                        if ($this->key == $fav['key'] && $this->bbs == $fav['bbs'] && $group == $fav['group']) {
                            $this->_favs[$num] = true;
                            break;
                        }
                    }
                }
            }
        }

        return $this->_favs;
    }

    // }}}
    // {{{ getDatDir()

    /**
     * dat�̕ۑ��f�B���N�g����Ԃ�
     *
     * @param bool $dir_sep
     * @return string
     * @see P2Util::datDirOfHost(), ThreadList::getDatDir()
     */
    public function getDatDir($dir_sep = true)
    {
        return P2Util::datDirOfHostBbs($this->host, $this->bbs, $dir_sep);
    }

    // }}}
    // {{{ getIdxDir()

    /**
     * idx�̕ۑ��f�B���N�g����Ԃ�
     *
     * @param bool $dir_sep
     * @return string
     * @see P2Util::idxDirOfHost(), ThreadList::getIdxDir()
     */
    public function getIdxDir($dir_sep = true)
    {
        return P2Util::idxDirOfHostBbs($this->host, $this->bbs, $dir_sep);
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
