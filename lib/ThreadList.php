<?php
require_once P2_LIB_DIR . '/sort_threadlist.inc.php';

// {{{ ThreadList

/**
 * rep2 - ThreadList �N���X
 */
class ThreadList
{
    // {{{ properties

    public $threads;   // �N���XThread�̃I�u�W�F�N�g���i�[����z��
    public $num;       // �i�[���ꂽThread�I�u�W�F�N�g�̐�
    public $host;      // ex)pc.2ch.net
    public $bbs;       // ex)mac
    public $itaj;      // �� ex)�V�Emac��
    public $itaj_hd;   // HTML�\���p�ɁA���� htmlspecialchars() ��������
    public $spmode;    // ���ʔȊO�̃X�y�V�������[�h
    public $ptitle;    // �y�[�W�^�C�g��

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     */
    public function __construct()
    {
        $this->threads = array();
        $this->num = 0;
    }

    // }}}
    // {{{ setSpMode()

    /**
     * spmode��ݒ肷��
     */
    public function setSpMode($name)
    {
        global $_conf;

        $halfwidth = ($_conf['ktai'] && !$_conf['iphone']);

        switch ($name) {
        case 'recent':
            $this->spmode = $name;
            $this->ptitle = $halfwidth ? '�ŋߓǂ񂾽�' : '�ŋߓǂ񂾃X��';
            break;
        case 'res_hist':
            $this->spmode = $name;
            $this->ptitle = '�������ݗ���';
            break;
        case 'fav':
            $this->spmode = $name;
            $this->ptitle = $halfwidth ? '���C�ɽ�' : '���C�ɃX��';
            break;
        case 'taborn':
            $this->spmode = $name;
            $this->ptitle = $this->itaj . ($halfwidth ? ' (���ݒ�)' : ' (���ځ[��)');
            break;
        case 'soko':
            $this->spmode = $name;
            $this->ptitle = "{$this->itaj} (dat�q��)";
            break;
        case 'palace':
            $this->spmode = $name;
            $this->ptitle = $halfwidth ? '�ڂ̓a��' : '�X���̓a��';
            break;
        case 'merge_favita':
            $this->spmode = $name;
            if ($_conf['expack.misc.multi_favs']) {
                $this->ptitle = str_replace(array('&gt;', '&lt;', '&quot;', '&#039;'),
                                            array('>', '<', '"', "'"),
                                            FavSetManager::getFavSetPageTitleHt('m_favita_set', '���C�ɔ�')
                                            ) . ' (�܂Ƃ�)';
            } else {
                $this->ptitle = '���C�ɔ� (�܂Ƃ�)';
            }
            break;
        }
    }

    // }}}
    // {{{ setIta()

    /**
     * �� �����I�ɔ��ihost, bbs, ���j���Z�b�g����
     */
    public function setIta($host, $bbs, $itaj = "")
    {
        $this->host = $host;
        $this->bbs = $bbs;
        $this->setItaj($itaj);

        return true;
    }

    // }}}
    // {{{ setItaj()

    /**
     * �������Z�b�g����
     */
    public function setItaj($itaj)
    {
        if ($itaj) {
            $this->itaj = $itaj;
        } else {
            $this->itaj = $this->bbs;
        }
        $this->itaj_hd = htmlspecialchars($this->itaj, ENT_QUOTES);
        $this->ptitle = $this->itaj;

        return true;
    }

    // }}}
    // {{{ readList()

    /**
     * �� readList ���\�b�h
     */
    public function readList()
    {
        global $_conf, $_info_msg_ht;

        //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('readList()');

        switch ($this->spmode) {

        // ���[�J���̗����t�@�C�� �ǂݍ���
        case 'recent':
            if ($lines = FileCtl::file_read_lines($_conf['recent_idx'])) {
                //$_info_msg_ht = '<p>�����͋���ۂł�</p>';
                //return false;
            }
            break;

        // ���[�J���̏������ݗ����t�@�C�� �ǂݍ���
        case 'res_hist':
            if ($lines = FileCtl::file_read_lines($_conf['res_hist_idx'])) {
                //$_info_msg_ht = '<p>�������ݗ����͋���ۂł�</p>';
                //return false;
            }
            break;

        //���[�J���̂��C�Ƀt�@�C�� �ǂݍ���
        case 'fav':
            if ($lines = FileCtl::file_read_lines($_conf['favlist_idx'])) {
                //$_info_msg_ht = '<p>���C�ɃX���͋���ۂł�</p>';
                //return false;
            }
            break;

        // ���C�ɔ��܂Ƃ߂ēǂݍ���
        case 'merge_favita':
            $favitas = array();

            if (file_exists($_conf['favita_brd'])) {
                foreach (file($_conf['favita_brd']) as $l) {
                    if (preg_match("/^\t?(.+?)\t(.+?)\t.+?\$/", rtrim($l), $m)) {
                        $favitas[] = array('host' => $m[1], 'bbs' => $m[2]);
                    }
                }
            }

            if (empty($_REQUEST['norefresh']) && !(empty($_REQUEST['refresh']) && isset($_REQUEST['word']))) {
                if ($_conf['expack.use_pecl_http'] == 1) {
                    P2HttpExt::activate();
                    P2HttpRequestPool::fetchSubjectTxt($favitas);
                    $GLOBALS['expack.subject.multi-threaded-download.done'] = true;
                } elseif ($_conf['expack.use_pecl_http'] == 2) {
                    if (P2CommandRunner::fetchSubjectTxt('merge_favita', $_conf)) {
                        $GLOBALS['expack.subject.multi-threaded-download.done'] = true;
                    }
                }
            }

            $lines = array();
            $i = 0;

            foreach ($favitas as $ita) {
                $aSubjectTxt = new SubjectTxt($ita['host'], $ita['bbs']);
                $k = (float)sprintf('0.%d', ++$i);

                if (is_array($aSubjectTxt->subject_lines)) {
                    $j = 0;

                    foreach ($aSubjectTxt->subject_lines as $l) {
                        if (preg_match('/^([0-9]+)\\.(?:dat|cgi)(?:,|<>)(.+) ?(?:\\(|�i)([0-9]+)(?:\\)|�j)/', $l, $m)) {
                            $lines[] = array(
                                'key' => $m[1],
                                'ttitle' => rtrim($m[2]),
                                'rescount' => (int)$m[3],
                                'host' => $ita['host'],
                                'bbs' => $ita['bbs'],
                                'torder' => ++$j + $k,
                            );
                        }
                    }
                }
            }
            break;

        // p2_threads_aborn.idx �ǂݍ���
        case 'taborn':
            $taborn_file = $this->getIdxDir() . 'p2_threads_aborn.idx';
            $lines = FileCtl::file_read_lines($taborn_file);
            break;

        // spmode��dat�q�ɂ̏ꍇ
        case 'soko':
            $dat_host_bbs_dir = $this->getDatDir(false);
            $idx_host_bbs_dir = $this->getIdxDir(false);

            $lines = array();

            //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('dat');
            // dat���O�f�B���N�g���𑖍����ČǗ�dat��idx�t��
            if ($cdir = dir($dat_host_bbs_dir)) { // or die ("���O�f�B���N�g�����Ȃ���I");
                // �f�B���N�g������
                while ($entry = $cdir->read()) {
                    if (preg_match('/([0-9]+)\\.dat$/', $entry, $matches)) {
                        $theidx = $idx_host_bbs_dir . DIRECTORY_SEPARATOR . $matches[1] . '.idx';
                        if (!file_exists($theidx)) {
                            $thedat = $dat_host_bbs_dir . DIRECTORY_SEPARATOR . $entry;
                            if ($datlines = FileCtl::file_read_lines($thedat, FILE_IGNORE_NEW_LINES)) {
                                $firstdatline = $datlines[0];
                                if (strpos($firstdatline, '<>') !== false) {
                                    $datline_sepa = '<>';
                                } else {
                                    $datline_sepa = ',';
                                }
                                $d = explode($datline_sepa, $firstdatline);
                                $atitle = $d[4];
                                $gotnum = sizeof($datlines);
                                $readnum = $gotnum;
                                $anewline = $readnum + 1;
                                $data = array($atitle, $matches[1], '', $gotnum, '',
                                            $readnum, '', '', '', $anewline,
                                            '', '', '');
                                P2Util::recKeyIdx($theidx, $data);
                            }
                        }
                        // array_push($lines, $idl[0]);
                    }
                }
                $cdir->close();
            }
            //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('dat');

            //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('idx');
            // {{{ idx���O�f�B���N�g���𑖍�����idx���𒊏o���ă��X�g��
            if ($cdir = dir($idx_host_bbs_dir)) { // or die ("���O�f�B���N�g�����Ȃ���I");
                // �f�B���N�g������
                while ($entry = $cdir->read()) {
                    if (preg_match('/([0-9]+)\\.idx$/', $entry)) {
                        $thedix = $idx_host_bbs_dir . DIRECTORY_SEPARATOR . $entry;
                        $idl = FileCtl::file_read_lines($thedix);
                        if (is_array($idl)) {
                            array_push($lines, $idl[0]);
                        }
                    }
                }
                $cdir->close();
            }
            // }}}
            //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('idx');
            break;

        // �X���̓a���̏ꍇ  // p2_palace.idx �ǂݍ���
        case 'palace':
            if ($lines = FileCtl::file_read_lines($_conf['palace_idx'])) {
                // $_info_msg_ht = "<p>�a���͂����ǂ��ł�</p>";
                // return false;
            }
            break;

        // �I�����C����� subject.txt ��ǂݍ��ށispmode�łȂ��ꍇ�j
        default:
            if (!$this->spmode) {
                $aSubjectTxt = new SubjectTxt($this->host, $this->bbs);
                $lines = $aSubjectTxt->subject_lines;
            }
        }

        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('readList()');

        return $lines;
    }

    // }}}
    // {{{ addThread()

    /**
     * �� addThread ���\�b�h
     */
    public function addThread(Thread $aThread)
    {
        //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('addThread()');

        $this->threads[] = $aThread;
        $this->num++;

        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('addThread()');

        return $this->num;
    }

    // }}}
    // {{{ sort()

    /**
     * �X���b�h����ёւ���
     *
     * @param string $mode
     * @param bool $reverse
     * @return void
     */
    public function sort($mode, $reverse = false)
    {
        global $_conf, $_info_msg_ht;

        if (!$this->threads) {
            return;
        }

        //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('sort');

        $do_benchmark = false;
        $use_multisort = true;
        $cmp = null;

        switch ($mode) {
        case 'midoku':
            if ($this->spmode == 'soko') {
                $cmp = 'cmp_key';
            } else {
                $cmp = 'cmp_midoku';
            }
            break;
        case 'ikioi':
        case 'spd':
            if ($_conf['cmp_dayres_midoku']) {
                $cmp = 'cmp_dayres_midoku';
            } else {
                $cmp = 'cmp_dayres';
            }
            break;
        case 'no':
            if ($this->spmode == 'soko') {
                $cmp = 'cmp_key';
            } else {
                $cmp = 'cmp_no';
            }
            break;
        case 'bd':
            $cmp = 'cmp_key';
            break;
        case 'fav':
        case 'ita':
        case 'res':
        case 'title':
            $cmp = 'cmp_' . $mode;
            break;
        case 'similarity':
            if (!empty($GLOBALS['wakati_words'])) {
                $cmp = 'cmp_similarity';
            } else {
                $cmp = 'cmp_title';
            }
            break;
        default:
            $_info_msg_ht .= sprintf('<p class="info-msg">�\�[�g�w�肪�ςł��B(%s)</p>',
                                     htmlspecialchars($mode, ENT_QUOTES));
        }

        if ($cmp) {
            if ($do_benchmark) {
                $before = microtime(true);
            }
            if ($use_multisort) {
                $cmp = 'p2_multi_' . $cmp;
                $cmp($this, $reverse);
            } else {
                $cmp = 'p2_' . $cmp;
                usort($this->threads, $cmp);
            }
        }

        if (!($cmp && $use_multisort) && $reverse) {
            $this->threads = array_reverse($this->threads);
        }

        if ($cmp && $do_benchmark) {
            $after = microtime(true);
            $count = count($this->threads);
            $_info_msg_ht .= sprintf(
                '<p class="info-msg" style="font-family:monospace">%s(%d thread%s)%s = %0.6f sec.</p>',
                $cmp,
                number_format($count),
                ($count > 1) ? 's' : '',
                $reverse ? '+reverse' : '',
                $after - $before);
        }

        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('sort');
    }

    // }}}
    // {{{ getDatDir()

    /**
     * dat�̕ۑ��f�B���N�g����Ԃ�
     *
     * @param bool $dir_sep
     * @return string
     * @see P2Util::datDirOfHost(), Thread::getDatDir()
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
     * @see P2Util::idxDirOfHost(), Thread::getIdxDir()
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
