<?php
/**
 * rep2- �X���b�h��\������ �N���X
 */
require_once P2_LIB_DIR . '/thermon/ErrorHandler.php';
//require_once P2_LIB_DIR . '/thermon/br2nl.php';
// {{{ ShowThread

abstract class ShowThread
{
    // {{{ constants

    /**
     * �����N�Ƃ��Ĉ����p�^�[��
     *
     * @var string
     */

    /**
     * ���_�C���N�^�̎��
     *
     * @var int
     */
    const REDIRECTOR_NONE = 0;
    const REDIRECTOR_IMENU = 1;
    const REDIRECTOR_PINKTOWER = 2;
    const REDIRECTOR_MACHIBBS = 3;

    /**
     * NG���ځ[��̎��
     *
     * @var int
     */
    const ABORN = -1;
    const NG_NONE = 0;
    const NG_NAME = 1;
    const NG_MAIL = 2;
    const NG_ID = 4;
    const NG_MSG = 8;
    const NG_FREQ = 16;
    const NG_CHAIN = 32;
    const NG_AA = 64;

	// +live �n�C���C�g���[�h�p
	const HIGHLIGHT_NONE = 128;
	const HIGHLIGHT_NAME = 256;
	const HIGHLIGHT_MAIL = 512;
	const HIGHLIGHT_ID = 1024;
	const HIGHLIGHT_MSG = 2048;
	const HIGHLIGHT_CHAIN = 4096;

    // }}}
    // {{{ static properties

    /**
     * �܂Ƃߓǂ݃��[�h���̃X���b�h��
     *
     * @var int
     */
    static private $_matome_count = 0;

    /**
     * �{���ȊO��NG���ځ[��Ƀq�b�g��������
     *
     * @var int
     */
    static protected $_ngaborns_head_hits = 0;

    /**
     * �{����NG���ځ[��Ƀq�b�g��������
     *
     * @var int
     */
    static protected $_ngaborns_body_hits = 0;

	static protected $_highlight_head_hits = 0; // �{���ȊO���n�C���C�g�Ƀq�b�g��������
	static protected $_highlight_body_hits = 0; // �{�����n�C���C�g�Ƀq�b�g��������

    /**
     * getAnchorRegex() �̃L���b�V��
     *
     * @var array
     */
    static private $_anchorRegexes = array();

    /**
     * _getAnchorRegexParts() �̃L���b�V��
     *
     * @var array
     */
    static private $_anchorRegexParts = null;

    // }}}
    // {{{ properties

    /**
     * �܂Ƃߓǂ݃��[�h���̃X���b�h�ԍ�
     *
     * @var int
     */
    protected $_matome;

    /**
     * URL����������֐��E���\�b�h���Ȃǂ��i�[����z��
     * (�g�ݍ���)
     *
     * @var array
     */
    protected $_url_handlers;

    /**
     * URL����������֐��E���\�b�h���Ȃǂ��i�[����z��
     * (���[�U��`�A�g�ݍ��݂̂��̂��D��)
     *
     * @var array
     */
    protected $_user_url_handlers;

    /**
     * �p�oID�����ځ[�񂷂�
     *
     * @var bool
     */
    protected $_ngaborn_frequent;

    /**
     * NG or ���ځ[�񃌃X�����邩�ǂ���
     *
     * @var bool
     */
    protected $_has_ngaborns;

    /**
     * ���ځ[�񃌃X�ԍ������NG���X�ԍ����i�[����z��
     * array_intersect()�������悭�s�����߁A�Y�����郌�X�ԍ��͕�����ɃL���X�g���Ċi�[����
     *
     * @var array
     */
    protected $_aborn_nums;
    protected $_ng_nums;

	protected $_highlight_nums; // �n�C���C�g���X�ԍ����i�[����z��
	protected $_highlight_msgs; // �n�C���C�g���b�Z�[�W���i�[����z��

    /**
     * ���_�C���N�^�̎��
     *
     * @var int
     */
    protected $_redirector;

    /**
     * �X���b�h�I�u�W�F�N�g
     *
     * @var ThreadRead
     */
    public $thread;

    /**
     * �A�N�e�B�u���i�[�E�I�u�W�F�N�g
     *
     * @var ActiveMona
     */
    public $activeMona;

    /**
     * �A�N�e�B�u���i�[���L�����ۂ�
     *
     * @var bool
     */
    public $am_enabled = false;

    protected $_quoter_list; // ��A���J�[���W�v�����z�� // [�A���J�[���X�� : [���X��, ...], ...)
    protected $_anchor_list; // �A���J�[���W�v�����z�� // [���X�� : [�A���J�[���X��, ...], ...)

    public $BBS_NONAME_NAME = '';

    private $_auto_fav_rank = false; // ���C�Ɏ��������N

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     */
    protected function __construct(ThreadRead $aThread, $matome = false)
    {
        global $_conf;

		// ���[�U�[�G���[�n���h���o�^
		set_error_handler("myErrorHandler");

        // �X���b�h�I�u�W�F�N�g��o�^
        $this->thread = $aThread;
		$this->_getAnchorRegexParts();
        $this->str_to_link_regex = $this->buildStrToLinkRegex();

        // �܂Ƃߓǂ݃��[�h���ۂ�
        if ($matome) {
            $this->_matome = ++self::$_matome_count;
        } else {
            $this->_matome = false;
        }

        $this->_url_handlers = array();
        $this->_user_url_handlers = array();

        $this->_ngaborn_frequent = 0;
        if ($_conf['ngaborn_frequent']) {
            if ($_conf['ngaborn_frequent_dayres'] == 0) {
                $this->_ngaborn_frequent = $_conf['ngaborn_frequent'];
            } elseif ($this->thread->setDayRes() && $this->thread->dayres < $_conf['ngaborn_frequent_dayres']) {
                $this->_ngaborn_frequent = $_conf['ngaborn_frequent'];
            }
        }

        $this->_has_ngaborns = false;
        $this->_aborn_nums = array();
        $this->_ng_nums = array();

		$this->_highlight_nums = array();
		$this->_highlight_chain_nums = array(); // �A���n�C���C�g�̃��X�ԍ�
		$this->_highlight_msgs = array();

        if (P2Util::isHostBbsPink($this->thread->host)) {
            $this->_redirector = self::REDIRECTOR_PINKTOWER;
        } elseif (P2Util::isHost2chs($this->thread->host)) {
            $this->_redirector = self::REDIRECTOR_IMENU;
        } elseif (P2Util::isHostMachiBbs($this->thread->host)) {
            $this->_redirector = self::REDIRECTOR_MACHIBBS;
        } else {
            $this->_redirector = self::REDIRECTOR_NONE;
        }
    }

    // }}}

    /**
     * @access  protected
     * @return  void
     */
    function setBbsNonameName()
    {
        if (P2Util::isHost2chs($this->thread->host)) {
            if (!class_exists('SettingTxt', false)) {
                require P2_LIB_DIR . '/SettingTxt.php';
            }
            $st = new SettingTxt($this->thread->host, $this->thread->bbs);
            if (!empty($st->setting_array['BBS_NONAME_NAME'])) {
                $this->BBS_NONAME_NAME = $st->setting_array['BBS_NONAME_NAME'];
            }
        }
    }

    // {{{ getDatToHtml()

    /**
     * Dat��HTML�ϊ��������̂��擾����
     *
     * @param   bool $is_fragment
     * @return  bool|string
     */
    public function getDatToHtml($is_fragment = false)
    {
        return $this->datToHtml(true, $is_fragment);
    }

    // }}}
    // {{{ datToHtml()

    /**
     * Dat��HTML�ɕϊ����ĕ\������
     *
     * @param   bool $capture       true�Ȃ�ϊ����ʂ��o�͂����ɕԂ�
     * @param   bool $is_fragment   true�Ȃ�<div class="thread"></div>�ň͂܂Ȃ�
     * @return  bool|string
     */
    public function datToHtml($capture = false, $is_fragment = false,$return_array=false)
    {
        global $_conf;

        // �\�����X�͈͂��w�肳��Ă��Ȃ����
        if (!$this->thread->resrange) {
            $error = '<p><b>p2 error: {$this->resrange} is FALSE at datToHtml()</b></p>';
            if ($capture) {
                return $error;
            } else {
                echo $error;
                return false;
            }
        }

        $start = $this->thread->resrange['start'];
        $to = $this->thread->resrange['to'];
        $nofirst = $this->thread->resrange['nofirst'];

        $count = count($this->thread->datlines);

        $buf['body'] = $is_fragment ? '' : "<div class=\"thread\">\n";
        $buf['q'] = '';

        // �܂� 1 ��\��
        if (!$nofirst) {
            $res = $this->transRes($this->thread->datlines[0], 1);
            if (is_array($res)) {
                $buf['body'] .= $res['body'];
                $buf['q'] .= $res['q'] ? $res['q'] : '';
            } else {
                $buf['body'] .= $res;
            }
        }

        // �A���̂��߁A�͈͊O��NG���ځ[��`�F�b�N
        if ($_conf['ngaborn_chain_all'] && empty($_GET['nong'])) {
            $pre = min($count, $start);
            for ($i = ($nofirst) ? 0 : 1; $i < $pre; $i++) {
                list($name, $mail, $date_id, $msg) = $this->thread->explodeDatLine($this->thread->datlines[$i]);
                if (($id = $this->thread->ids[$i]) !== null) {
                    $date_id = str_replace($this->thread->idp[$i] . $id, $idstr, $date_id);
                }
                $this->_ngAbornCheck($i + 1, strip_tags($name), $mail, $date_id, $id, $msg);
            }
        }

        // �w��͈͂�\��
        $end = min($count, $to);
        for ($i = $start - 1; $i < $end; $i++) {
            if (!$nofirst and $i == 0) {
                continue;
            }
            $res = $this->transRes($this->thread->datlines[$i], $i + 1);
            if (is_array($res)) {
                $buf['body'] .= $res['body'];
                $buf['q'] .= $res['q']; // ? $res['q'] : '';
            } else {
                $buf['body'] .= $res;
            }
            if (!$capture && $i % 10 == 0) {
                echo $buf['body'];
                flush();
                $buf['body'] = '';
            }
        }
        if ($this->thread->readnum < $end) {
            $this->thread->readnum = $end;
        }

        if (!$is_fragment) {
            $buf['body'] .= "</div>\n";
        }

        if ($capture) {
            return $return_array ? array($buf['body'],$buf['q']) : $buf['body'] .$buf['q'];
        } else {
            echo $buf['body'];
            if (!$return_array) {echo $buf['q'];}
            flush();
            return $return_array ? array($buf['body'],$buf['q']) :true;
        }
    }

    // }}}
    // {{{ transRes()

    /**
     * Dat���X��HTML���X�ɕϊ�����
     *
     * @param   string  $ares   dat��1���C��
     * @param   int     $i      ���X�ԍ�
     * @return  string
     */
    abstract public function transRes($ares, $i);

    // }}}
    // {{{ transName()

    /**
     * ���O��HTML�p�ɕϊ�����
     *
     * @param   string  $name   ���O
     * @return  string
     */
    abstract public function transName($name);

    // }}}
    // {{{ transMsg()

    /**
     * dat�̃��X���b�Z�[�W��HTML�\���p���b�Z�[�W�ɕϊ�����
     *
     * @param   string  $msg    ���b�Z�[�W
     * @param   int     $mynum  ���X�ԍ�
     * @return  string
     */
    abstract public function transMsg($msg, $mynum);

    // }}}
    // {{{ replaceBeId()

    /**
     * BE�v���t�@�C�������N�ϊ�
     */
    public function replaceBeId($date_id, $i)
    {
        global $_conf;

        $beid_replace = "<a href=\"http://be.2ch.net/test/p.php?i=\$1&u=d:http://{$this->thread->host}/test/read.cgi/{$this->thread->bbs}/{$this->thread->key}/{$i}\"{$_conf['ext_win_target_at']}>Lv.\$2</a>";

        //<BE:23457986:1>
        $be_match = '|<BE:(\d+):(\d+)>|i';
        if (preg_match($be_match, $date_id)) {
            $date_id = preg_replace($be_match, $beid_replace, $date_id);

        } else {

            $beid_replace = "<a href=\"http://be.2ch.net/test/p.php?i=\$1&u=d:http://{$this->thread->host}/test/read.cgi/{$this->thread->bbs}/{$this->thread->key}/{$i}\"{$_conf['ext_win_target_at']}>?\$2</a>";
            $date_id = preg_replace('|BE: ?(\d+)-(#*)|i', $beid_replace, $date_id);
        }

        return $date_id;
    }

    // }}}
    // {{{ _ngAbornCheck()

    /**
     * NG���ځ[��`�F�b�N
     *
     * @param   int     $i          ���X�ԍ�
     * @param   string  $name       ���O��
     * @param   string  $mail       ���[����
     * @param   string  $date_id    ���t�EID��
     * @param   string  $id         ID
     * @param   string  $msg        ���X�{��
     * @param   bool    $nong       NG�`�F�b�N�����邩�ǂ���
     * @param   array  &$info       NG�̗��R���i�[�����ϐ��̎Q��
     * @return  int NG�^�C�v�BShowThread::NG_XXX �̃r�b�g�a�� ShowThread::ABORN
     */
    protected function _ngAbornCheck($i, $name, $mail, $date_id, $id, $msg, $nong = false, &$info = null)
    {
        global $_conf, $ngaborns_hits, $highlight_msgs, $highlight_chain_nums;

        $info = array();
        $type = self::NG_NONE;

        // {{{ �p�oID�`�F�b�N

        if ($this->_ngaborn_frequent && $id && $this->thread->idcount[$id] >= $_conf['ngaborn_frequent_num']) {
            if (!$_conf['ngaborn_frequent_one'] && $id == $this->thread->ids[1]) {
                // >>1 �͂��̂܂ܕ\��
            } elseif ($this->_ngaborn_frequent == 1) {
                $ngaborns_hits['aborn_freq']++;
                return $this->_markNgAborn($i, self::ABORN, false);
            } elseif (!$nong) {
                $ngaborns_hits['ng_freq']++;
                $type |= $this->_markNgAborn($i, self::NG_FREQ, false);
                $info[] = sprintf('�p�oID:%s(%d)', $id, $this->thread->idcount[$id]);
            }
        }

        // }}}
        // {{{ �A���`�F�b�N

        if ($_conf['ngaborn_chain'] && $this->_has_ngaborns &&
            $matches=$this->_getAnchorsFromMsg($msg)
        ) {
            $references = array_unique(preg_split('/[-,]+/',
                                                  trim(implode(',', $matches), '-,'),
                                                  -1,
                                                  PREG_SPLIT_NO_EMPTY));
            $intersections = array_intersect($references, $this->_aborn_nums);
            $info_suffix = '';

            if ($intersections) {
                if ($_conf['ngaborn_chain'] == 1) {
                    $ngaborns_hits['aborn_chain']++;
                    return $this->_markNgAborn($i, self::ABORN, true);
                }
                if ($nong) {
                    $intersections = null;
                } else {
                    $info_suffix = '(' . (($_conf['ktai']) ? '����' : '���ځ[��') . ')';
                }
            } elseif (!$nong) {
                $intersections = array_intersect($references, $this->_ng_nums);
            }

            if ($intersections) {
                $ngaborns_hits['ng_chain']++;
                $type |= $this->_markNgAborn($i, self::NG_CHAIN, true);
                $info[] = sprintf('�A��NG:&gt;&gt;%d%s', current($intersections), $info_suffix);
            }
        }

        // }}}
        // {{{ ���ځ[��`�F�b�N

        // ���ځ[�񃌃X
        if ($this->abornResCheck($i) !== false) {
            $ngaborns_hits['aborn_res']++;
            return $this->_markNgAborn($i, self::ABORN, false);
        }

        // ���ځ[��l�[��
        if ($this->ngAbornCheck('aborn_name', $name) !== false) {
            $ngaborns_hits['aborn_name']++;
            return $this->_markNgAborn($i, self::ABORN, false);
        }

        // ���ځ[�񃁁[��
        if ($this->ngAbornCheck('aborn_mail', $mail) !== false) {
            $ngaborns_hits['aborn_mail']++;
            return $this->_markNgAborn($i, self::ABORN, false);
        }

        // ���ځ[��ID
        if ($this->ngAbornCheck('aborn_id', $date_id) !== false) {
            $ngaborns_hits['aborn_id']++;
            return $this->_markNgAborn($i, self::ABORN, false);
        }

        // ���ځ[�񃁃b�Z�[�W
        if ($this->ngAbornCheck('aborn_msg', $msg) !== false) {
            $ngaborns_hits['aborn_msg']++;
            return $this->_markNgAborn($i, self::ABORN, true);
        }

        // }}}

        if ($nong) {
            return $type;
        }

        // {{{ NG�`�F�b�N

        // NG�l�[���`�F�b�N
        if ($this->ngAbornCheck('ng_name', $name) !== false) {
            $ngaborns_hits['ng_name']++;
            $type |= $this->_markNgAborn($i, self::NG_NAME, false);
        }

        // NG���[���`�F�b�N
        if ($this->ngAbornCheck('ng_mail', $mail) !== false) {
            $ngaborns_hits['ng_mail']++;
            $type |= $this->_markNgAborn($i, self::NG_MAIL, false);
        }

        // NGID�`�F�b�N
        if ($this->ngAbornCheck('ng_id', $date_id) !== false) {
            $ngaborns_hits['ng_id']++;
            $type |= $this->_markNgAborn($i, self::NG_ID, false);
        }

        // NG���b�Z�[�W�`�F�b�N
        $a_ng_msg = $this->ngAbornCheck('ng_msg', $msg);
        if ($a_ng_msg !== false) {
            $ngaborns_hits['ng_msg']++;
            $type |= $this->_markNgAborn($i, self::NG_MSG, true);
            $info[] = sprintf('NG%s:%s',
                              ($_conf['ktai']) ? 'ܰ��' : '���[�h',
                              htmlspecialchars($a_ng_msg, ENT_QUOTES));
        }
		// +live �n�C���C�g�`�F�b�N
		include (P2_LIB_DIR . '/live/live_highlight_check.php');
        // }}}
        return $type;
    }

    // }}}
    // {{{ _markNgAborn()

    /**
     * NG���ځ[��Ƀq�b�g�������X�ԍ����L�^����
     *
     * @param   int $num        ���X�ԍ�
     * @param   int $type       NG���ځ[��̎��
     * @param   bool $isBody    �{���Ƀq�b�g�������ǂ���
     * @return  int $type�Ɠ����l
     */
    protected function _markNgAborn($num, $type, $isBody)
    {
        if ($type) {
            if ($isBody) {
                self::$_ngaborns_body_hits++;
            } else {
                self::$_ngaborns_head_hits++;
            }

            // array_intersect()�������悭�s�����߁A���X�ԍ��𕶎���^�ɃL���X�g����
            $str = (string)$num;
            if ($type == self::ABORN) {
                $this->_aborn_nums[$num] = $str;
            } else {
                $this->_ng_nums[$num] = $str;
            }

            $this->_has_ngaborns = true;
        }

        return $type;
    }

    // }}}
	// {{{ _markHighlight()

	// +live �n�C���C�g�Ƀq�b�g�������X�ԍ����L�^����
	protected function _markHighlight($num, $type, $isBody)
	{
		global $_conf;
		if ($type) {
			if ($isBody) {
				self::$_highlight_body_hits++;
			} else {
				self::$_highlight_head_hits++;
			}

			$str = (string)$num;
			$this->_highlight_nums[] = $str;
		}

		return $type;
	}

	// }}}
    // {{{ ngAbornCheck()

    /**
     * NG���ځ[��`�F�b�N
     */
    public function ngAbornCheck($code, $resfield, $ic = false)
    {
        global $ngaborns;

        //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('ngAbornCheck()');
        if (isset($ngaborns[$code]['data']) && is_array($ngaborns[$code]['data'])) {
            // +Wiki:BE���ځ[��
            /* preg_replace ���G���[�ɂȂ�̂ł��̂ւ�R�����g�A�E�g
            if ($code == 'aborn_be' || $code == 'ng_be') {
                // �v���t�B�[��ID�𔲂��o��
                if ($prof_id = preg_replace('/BE:(\d+)/', '$1')) {
                    echo $prof_id;
                    $resfield = P2UtilWiki::calcBeId($prof_id);
                    if($resfield == 0) return false;
                } else {
                    return false;
                }
            }
             */
            $bbs = $this->thread->bbs;
            $title = $this->thread->ttitle_hc;
//if (preg_match("/highlight/",$code)) {var_export($ngaborns[$code]['data']);echo "<br>";}
			$matched_word=array();
            foreach ($ngaborns[$code]['data'] as $k => $v) {


                // �`�F�b�N
                if (isset($v['bbs']) && in_array($bbs, $v['bbs']) == false) {
                    continue;
                }

                // �^�C�g���`�F�b�N
                if (isset($v['title']) && stripos($title, $v['title']) === false) {
                    continue;
                }

                // ���[�h�`�F�b�N
                // ���K�\��
                if ($v['regex']) {
                    $re_method = $v['regex'];
                    /*if ($re_method($v['word'], $resfield, $matches)) {
                        $this->ngAbornUpdate($code, $k);
                        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
                        return htmlspecialchars($matches[0], ENT_QUOTES);
                    }*/
                     if ($re_method($v['word'], $resfield)) {
                        $this->ngAbornUpdate($code, $k);
                        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
                        $matched_word[] = $v['cond'];
                    }
                // +Wiki:BE���ځ[��(���S��v)
                } else if ($code == 'aborn_be' || $code == 'ng_be') {
					echo __LINE__,"OK<br>";
                    if ($resfield == $v['word']) {
                        $this->ngAbornUpdate($code, $k);
                        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
                        $matched_word[] = $v['cond'];
                    }
               // �啶���������𖳎�
                } elseif ($ic || !empty($v['ignorecase'])) {
                    if (stripos($resfield, $v['word']) !== false) {
                        $this->ngAbornUpdate($code, $k);
                        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
                        $matched_word[] = $v['cond'];
                    }
                // �P���ɕ����񂪊܂܂�邩�ǂ������`�F�b�N
                } else {
                    if (strpos($resfield, $v['word']) !== false) {
                        $this->ngAbornUpdate($code, $k);
                        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
                        $matched_word[] = $v['cond'];
                    }
                }
				if ($code != 'highlight_msg' && count($matched_word)) {	// �n�C���C�g���b�Z�[�W�����ȊO�̏ꍇ�́A�q�b�g����΂����ɖ߂�
					return $matched_word[0];
				}
            }
        }

        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
	// �n�C���C�g���b�Z�[�W�����̏ꍇ�́A�q�b�g���������̔z���Ԃ�
        return count($matched_word) ? $matched_word : false;
    }

    // }}}
    // {{{ abornResCheck()

    /**
     * ���背�X�̓������ځ[��`�F�b�N
     */
    public function abornResCheck($resnum)
    {
        global $ngaborns;

        $target = $this->thread->host . '/' . $this->thread->bbs . '/' . $this->thread->key . '/' . $resnum;

        if (isset($ngaborns['aborn_res']['data']) && is_array($ngaborns['aborn_res']['data'])) {
			// var_export($ngaborns['aborn_res']['data']);echo "<br>";
            foreach ($ngaborns['aborn_res']['data'] as $k => $v) {
                if ($ngaborns['aborn_res']['data'][$k]['word'] == $target) {
                    $this->ngAbornUpdate('aborn_res', $k);
                    return true;
                }
            }
        }
        return false;
    }

    // }}}
    // {{{ ngAbornUpdate()

    /**
     * NG/���ځ`������Ɖ񐔂��X�V
     */
    public function ngAbornUpdate($code, $k)
    {
        global $ngaborns;

        if (isset($ngaborns[$code]['data'][$k])) {
            $ngaborns[$code]['data'][$k]['lasttime'] = date('Y/m/d G:i'); // HIT���Ԃ��X�V
            if (empty($ngaborns[$code]['data'][$k]['hits'])) {
                $ngaborns[$code]['data'][$k]['hits'] = 1; // ��HIT
            } else {
                $ngaborns[$code]['data'][$k]['hits']++; // HIT�񐔂��X�V
            }
        }
    }

    // }}}
    // {{{ addURLHandler()

    /**
     * ���[�U��`URL�n���h���i���b�Z�[�W����URL������������֐��j��ǉ�����
     *
     * �n���h���͍ŏ��ɒǉ����ꂽ���̂��珇�ԂɎ��s�����
     * URL�̓n���h���̕Ԃ�l�i������j�Œu�������
     * FALSE���A�����ꍇ�͎��̃n���h���ɏ������ς˂���
     *
     * ���[�U��`URL�n���h���̈�����
     *  1. string $url  URL
     *  2. array  $purl URL��parse_url()��������
     *  3. string $str  �p�^�[���Ƀ}�b�`����������AURL�Ɠ������Ƃ�����
     *  4. object $aShowThread �Ăяo�����̃I�u�W�F�N�g
     * �ł���
     * ���FALSE��Ԃ��A�����ŏ������邾���̊֐���o�^���Ă��悢
     *
     * @param   callback $function  �R�[���o�b�N���\�b�h
     * @return  void
     * @access  public
     * @todo    ���[�U��`URL�n���h���̃I�[�g���[�h�@�\������
     */
    public function addURLHandler($function)
    {
        $this->_user_url_handlers[] = $function;
    }

    // }}}
    // {{{ getFilterTarget()

    /**
     * ���X�t�B���^�����O�̃^�[�Q�b�g�𓾂�
     */
    public function getFilterTarget($ares, $i, $name, $mail, $date_id, $msg)
    {
        switch ($GLOBALS['res_filter']['field']) {
            case 'name':
                $target = $name; break;
            case 'mail':
                $target = $mail; break;
            case 'date':
                $target = preg_replace('| ?ID:[0-9A-Za-z/.+?]+.*$|', '', $date_id); break;
            case 'id':
                if ($target = preg_replace('|^.*ID:([0-9A-Za-z/.+?]+).*$|', '$1', $date_id)) {
                    break;
                } else {
                    return '';
                }
            case 'msg':
                $target = $msg; break;
            case 'res':
                $target = $i; break;
            default: // 'hole'
                $target = strval($i) . '<>' . $ares;
        }

        $target = @strip_tags($target, '<>');

        return $target;
    }

    // }}}
    // {{{ filterMatch()

    /**
     * ���X�t�B���^�����O�̃}�b�`����
     */
    public function filterMatch($target, $resnum,$counting=true)
    {
        global $_conf;
        global $filter_hits, $filter_range;

        $failed = ($GLOBALS['res_filter']['match'] == 'off') ? TRUE : FALSE;

        if ($GLOBALS['res_filter']['method'] == 'and') {
            $words_fm_hit = 0;
            foreach ($GLOBALS['words_fm'] as $word_fm_ao) {
                if (StrCtl::filterMatch($word_fm_ao, $target) == $failed) {
                    if ($GLOBALS['res_filter']['match'] != 'off') {
                        return false;
                    } else {
                        $words_fm_hit++;
                    }
                }
            }
            if ($words_fm_hit == count($GLOBALS['words_fm'])) {
                return false;
            }
        } else {
            if (StrCtl::filterMatch($GLOBALS['word_fm'], $target) == $failed) {
                return false;
            }
        }

        if ($counting) {$filter_hits++;}

        if ($_conf['filtering'] && !empty($filter_range) &&
            ($filter_hits < $filter_range['start'] || $filter_hits > $filter_range['to'])
        ) {
            return false;
        }

        $GLOBALS['last_hit_resnum'] = $resnum;

        if (!$_conf['ktai']) {
            echo <<<EOP
<script type="text/javascript">
//<![CDATA[
filterCount({$filter_hits});
//]]>
</script>\n
EOP;
        }

        return true;
    }

    // }}}
    // {{{ stripLineBreaks()

    /**
     * �����̉��s�ƘA��������s����菜��
     *
     * @param string $msg
     * @param string $replacement
     * @return string
     */
    public function stripLineBreaks($msg, $replacement = ' <br><br> ')
    {
        if (P2_MBREGEX_AVAILABLE) {
            $msg = mb_ereg_replace('(?:[\\s�@]*<br>)+[\\s�@]*$', '', $msg);
            $msg = mb_ereg_replace('(?:[\\s�@]*<br>){3,}', $replacement, $msg);
        } else {
            mb_convert_variables('UTF-8', 'CP932', $msg, $replacement);
            $msg = preg_replace('/(?:[\\s\\x{3000}]*<br>)+[\\s\\x{3000}]*$/u', '', $msg);
            $msg = preg_replace('/(?:[\\s\\x{3000}]*<br>){3,}/u', $replacement, $msg);
            $msg = mb_convert_encoding($msg, 'CP932', 'UTF-8');
        }

        return $msg;
    }

    // }}}
    // {{{ transLink()

    /**
     * �����N�Ώە������ϊ�����
     *
     * @param   string $str
     * @return  string
     */
    public function transLink($str)
    {
        return preg_replace_callback($this->str_to_link_regex, array($this, 'transLinkDo'), $str);
    }

    // }}}
    // {{{ transLinkDo()

    /**
     * �����N�Ώە�����̎�ނ𔻒肵�đΉ������֐�/���\�b�h�ɓn��
     *
     * @param   array   $s
     * @return  string
     */
    public function transLinkDo(array $s)
    {
        global $_conf;

        $orig = $s[0];
        $following = '';

        // PHP 5.2.7 ������ preg_replace_callback() �ł͖��O�t���ߊl���W�����g���Ȃ��̂�
        /*
        if (!array_key_exists('link', $s)) {
            $s['link']  = $s[1];
            $s['url']   = $s[8-3];
            $s['id']    = $s[11-3];
            $s['quote'] = $s[10];
        }
        */

		$link_index=1;
		$url_index=5;
		$id_index=8;
		$quote_index=11;
        // �}�b�`�����T�u�p�^�[���ɉ����ĕ���
        // �����N
        if ($s['link']) {
            if (preg_match('{ href=(["\'])?(.+?)(?(1)\\1)(?=[ >])}i', $s[2], $m)) {
                $url = $m[2];
                $str = $s[$link_index+2];
            } else {
                return $s[$link_index+2];
            }

        // http or ftp ��URL
        } elseif ($s['url']) {
            if ($_conf['ktai'] && $s[$url_index+1] == 'ftp') {
                return $orig;
            }
            $url = preg_replace('/^t?(tps?)$/', 'ht$1', $s[$url_index+1]) . '://' . $s[$url_index+2];
            $str = $s['url'];
            $following = $s[$url_index+3];
/*            if (strlen($following) > 0) {
                // �E�B�L�y�f�B�A���{��ł�URL�ŁASJIS��2�o�C�g�����̏�ʃo�C�g
                // (0x81-0x9F,0xE0-0xEF)�������Ƃ�
                if (P2Util::isUrlWikipediaJa($url)) {
                    $leading = ord($following);
                    if ((($leading ^ 0x90) < 32 && $leading != 0x80) || ($leading ^ 0xE0) < 16) {
                        $url .= rawurlencode(mb_convert_encoding($following, 'UTF-8', 'CP932'));
                        $str .= $following;
                        $following = '';
                    }
                } elseif (strpos($following, 'tp://') !== false) {
                    // �S�p�X�y�[�X+URL���̏ꍇ������̂ōă`�F�b�N
                    $following = $this->transLink($following);
                }
            }
*/

        // ID
        } elseif ($s['id'] && $_conf['flex_idpopup']) { // && $_conf['flex_idlink_k']
            return $this->idFilter($s['id'], $s[$id_index+( $s[$id_index+2] ? 2 : 1)]);

        // ���p
        } elseif ($s['quote'] && !$s['ignore_prefix']) {
			$s2=array_slice($s,$quote_index+3);
//			if (!$s2['prefix']) {
//				echo htmlspecialchars(var_export($s2,true))."<br>";
//			}
			$ret=$this->quoteResCallback($s2);
			return $ret;
        // ���̑��i�\���j
        } else {
            return strip_tags($orig);
        }

        // ���_�C���N�^���O��
        switch ($this->_redirector) {
            case self::REDIRECTOR_IMENU:
                $url = preg_replace('{^([a-z]+://)ime\\.nu/}', '$1', $url);
                break;
            case self::REDIRECTOR_PINKTOWER:
                $url = preg_replace('{^([a-z]+://)pinktower\\.com/}', '$1', $url);
                break;
            case self::REDIRECTOR_MACHIBBS:
                $url = preg_replace('{^[a-z]+://machi(?:bbs\\.com|\\.to)/bbs/link\\.cgi\\?URL=}', '', $url);
                break;
        }

        // �G�X�P�[�v����Ă��Ȃ����ꕶ�����G�X�P�[�v
        $url = htmlspecialchars($url, ENT_QUOTES, 'Shift_JIS', false);
        $str = htmlspecialchars($str, ENT_QUOTES, 'Shift_JIS', false);
        // ���ԎQ�ƁE���l�Q�Ƃ����S�Ƀf�R�[�h���悤�Ƃ���ƕ��ׂ��傫�����A
        // "&"�ȊO�̓��ꕶ���͂قƂ�ǂ̏ꍇURL�G���R�[�h����Ă���͂��Ȃ̂�
        // ���r���[�ɋÂ��������͂����A"&amp;"��"&"�̂ݍĕϊ�����B
        $raw_url = str_replace('&amp;', '&', $url);

        // URL���p�[�X�E�z�X�g������
        $purl = @parse_url($raw_url);
        if (!$purl || !array_key_exists('host', $purl) ||
            strpos($purl['host'], '.') === false ||
            $purl['host'] == '127.0.0.1' ||
            //HostCheck::isAddressLocal($purl['host']) ||
            //HostCheck::isAddressPrivate($purl['host']) ||
            P2Util::isHostExample($purl['host']))
        {
            return $orig;
        }
        // URL�̃}�b�`���O��"&amp;"���l�����Ȃ��čςނ悤�ɁA����URL��o�^���Ă���
        $purl[0] = $raw_url;

        // URL������
        foreach ($this->_user_url_handlers as $handler) {
            if (false !== ($link = call_user_func($handler, $url, $purl, $str, $this))) {
                return $link . $following;
            }
        }
        foreach ($this->_url_handlers as $handler) {
            if (false !== ($link = $this->$handler($url, $purl, $str))) {
                return $link . $following;
            }
        }

        return $orig;
    }

    // }}}
    // {{{ idFilter()

    /**
     * ID�t�B���^�����O�ϊ�
     *
     * @param   string  $idstr  ID:xxxxxxxxxx
     * @param   string  $id        xxxxxxxxxx
     * @return  string
     */
    abstract public function idFilter($idstr, $id);

    // }}}
    // {{{ idFilterCallback()

    /**
     * ID�t�B���^�����O�ϊ�
     *
     * @param   array   $s  ���K�\���Ƀ}�b�`�����v�f�̔z��
     * @return  string
     */
    final public function idFilterCallback(array $s)
    {
        return $this->idFilter($s[0], $s[1]);
    }

    // }}}
    /**
     * @access  protected
     * @return  string  HTML
     */
    function quote_name_callback($s)
    {
		try{
	        return preg_replace_callback(
	            $this->getAnchorRegex('/(?P<quote>(?:%prefix2%)?%a_num%)/'),
	            array($this, 'quoteResCallback'), $s[0]
	        );
		} catch (Exception $e) {
			trigger_error(
				"���K�\�����s���ł��B<br>".$e->getMessage(),E_USER_ERROR
			);
		}
    }
    // {{{ quoteRes()

    /**
     * ���p�ϊ��i�P�Ɓj
     *
     * @param   string  $full           >>1
     * @param   string  $qsign          >>
     * @param   string  $appointed_num    1
     * @return  string
     */
    //abstract public function quoteRes(array $s);

    // }}}
    // {{{ quoteResCallback()

    /**
     * ���p�ϊ��i�P�Ɓj
     *
     * @param   array   $s  ���K�\���Ƀ}�b�`�����v�f�̔z��
     * @return  string
     */
    final public function quoteResCallback(array $s)
    {
//	preg_match($this->getAnchorRegex('/(?P<prefix>%prefix%|%delimiter%%prefix2%?)?%a_range%/'),$s['quote'],$out);
//				echo "out:".htmlspecialchars(var_export($out,true))."<br>";
		if (!$s['ignore_prefix']) {
			try{
				$var=preg_replace_callback(
					$this->getAnchorRegex('/(?P<prefix>%prefix%|%delimiter%%prefix2%?)?%a_range%/'),
					array($this, 'quoteRes'), $s['quote']
				);
//				echo "quoteResCallback<br>";
//				echo nl2br(htmlspecialchars(var_export($var,true)))."<br>";
			} catch (Exception $e) {
				trigger_error("���K�\�����s���ł��B<br>".$e->getMessage(),E_USER_ERROR);
			}
		}
		return $var;
    }

    // }}}
    // {{{ quoteResRange()

    /**
     * ���p�ϊ��i�͈́j
     *
     * @param   string  $full           >>1-100
     * @param   string  $qsign          >>
     * @param   int  $from    1
     * @param   int  $to      100
     * @param   int  $anchor  �����N��URL���̃A���J�[���X�ԍ�
     * @return string
     */
    abstract public function quoteResRange($full, $qsign, $from, $to);

    // }}}
    // {{{ getQuoteResNumsName()

    function getQuoteResNumsName($name)
    {
        // �g���b�v������
        $name = preg_replace('/(��.*)/', '', $name, 1);

        /*
        //if (preg_match('/[0-9]+/', $name, $m)) {
             return (int)$m[0];
        }
         */


		try{
	        if (preg_match_all($this->getAnchorRegex('/(?:^|%prefix%|%delimiter%)(?P<num>%a_num%)/'), $name, $matches)) {
				$quote_res_nums=array_map(array('ShowThread','_str2num'),$matches['num']);
//	            foreach ($matches['num'] as $a_quote_res_num) {
//	                $quote_res_nums[] = (int) preg_replace("/\s/",'',mb_convert_kana($a_quote_res_num, 'ns'));
//	            }
	            return array_unique($quote_res_nums);
	        }
		} catch (Exception $e) {
			trigger_error("���K�\�����s���ł��B<br>".$e->getMessage(),E_USER_ERROR);
		}
        return false;
    }

    // }}}
    // {{{ wikipediaFilter()

    /**
     * [[���]]������������Wikipedia�֎��������N
     *
     * @param   string  $msg            ���b�Z�[�W
     * @return  string
     *
     * original code:
     *  http://akid.s17.xrea.com/p2puki/index.phtml?%A5%E6%A1%BC%A5%B6%A1%BC%A5%AB%A5%B9%A5%BF%A5%DE%A5%A4%A5%BA%28rep2%20Ver%201.7.0%A1%C1%29#led2c85d
     */
    protected function wikipediaFilter($msg) {
        $msg = mb_convert_encoding($msg, "UTF-8", "SJIS-win"); // SJIS�͂���������UTF-8�ɕϊ�����񂾂��H
        $wikipedia = "http://ja.wikipedia.org/wiki/"; // Wikipedia��URL�Ȃ񂾂��H
        $search = "/\[\[([^\[\]\n<>]+)\]\]+/"; // �ڈ�ƂȂ鐳�K�\���Ȃ񂾂��H
        preg_match_all($search, $msg, $matches); // [[���]]��T���񂾂��H
        foreach ($matches[1] as $value) { // �����N�ɕϊ�����񂾂��H
            $replaced = $this->link_wikipedia($value);
            $msg = str_replace("[[$value]]", "[[$replaced]]", $msg); // �ϊ���̖{����߂��񂾂��H
        }
        $msg = mb_convert_encoding($msg, "SJIS-win", "UTF-8"); // UTF-8����SJIS�ɖ߂��񂾂��H
        return $msg;
    }

    // }}}
    // {{{ link_wikipedia()

    /**
     * Wikipedia�̌��������N�ɕϊ����ĕԂ�.
     *
     * @param   string  $word   ���
     * @return  string
     */
    abstract protected function link_wikipedia($word);

    // {{{ _make_quote_from()

    /**
     * �탌�X�f�[�^���W�v����$this->_quoter_list�ɕۑ�.
     */
    protected function _make_quote_from()
    {
        global $_conf;
        $this->_quoter_list = array();
        $this->_anchor_list = array();
        if (!$this->thread->datlines) return;
        foreach($this->thread->datlines as $num => $line) {
            list($name, $mail, $date_id, $msg) = $this->thread->explodeDatLine($line);

			// NG���ځ[��`�F�b�N
			$ng_type = $this->_ngAbornCheck($num+1, strip_tags($name), $mail, $date_id, $id, $msg, true);
			if ($ng_type == self::ABORN) {continue;}

            $name = preg_replace('/(��.*)/', '', $name, 1);

            // ���O
/*            if ($matches = $this->getQuoteResNumsName($name)) {
                foreach ($matches as $a_quote_num) {
                    if ($a_quote_num) {$this->_addQuoteNum($num,$a_quote_num);}
                }
            }
*/
            if (!$ranges=$this->_getAnchorsFromMsg($msg)) {continue;}
            foreach ($ranges as $a_range) {
				try{
		            if (preg_match($this->getAnchorRegex('/(%a_num%)%range_delimiter%(?:%prefix%)?(%a_num%)/'), $a_range, $matches)) {
		                $from = self::_str2num($matches[1]);
		                $to   = self::_str2num($matches[2]);
		                if ($from < 1 || $to < 1 || $from > $to
		                    || ($to - $from + 1) > sizeof($this->thread->datlines))
		                        {continue;}
		                    if ($_conf['backlink_list_range_anchor_limit'] != 0) {
		                        if ($to - $from >= $_conf['backlink_list_range_anchor_limit'])
		                            continue;
		                    }
		                for ($i = $from; $i <= $to; $i++) {
		                    if ($i > sizeof($this->thread->datlines)) {break;}
		                    $this->_addQuoteNum($num,$i);
		                }
		            } else if (preg_match($this->getAnchorRegex('/(%a_num%)/'), $a_range, $matches)) {
		                $this->_addQuoteNum($num,self::_str2num($matches[1]));
		            }
				} catch (Exception $e) {
					trigger_error("���K�\�����s���ł��B<br>".$e->getMessage(),E_USER_ERROR);
				}
            }
        }
    }

    protected function _addQuoteNum($num,$quotee) {
		$quoter=$num+1;
		if ($_conf['backlink_list_future_anchor'] == 0) {
			if ($quotee >= $quoter) {return;}	// ���X�ԍ��ȍ~�̃A���J�[�͖�������
		}
        if (!array_key_exists($quotee, $this->_quoter_list) || $this->_quoter_list[$quotee] === null) {
            $this->_quoter_list[$quotee] = array();
        }
        if (!in_array($quoter, $this->_quoter_list[$quotee])) {
            $this->_quoter_list[$quotee][] = $quoter;
        }
    }

    protected function _getAnchorsFromMsg($msg) {
        $anchor_list=array();
        // >>1�̃����N����������O��
        // <a href="../test/read.cgi/accuse/1001506967/1" target="_blank">&gt;&gt;1</a>
        $msg = preg_replace('{<[Aa] .+?>(&gt;&gt;[1-9][\\d\\-]*)</[Aa]>}', '$1', $msg);
//		$msg=br2nl($msg);

		try{
			preg_match_all(
						$this->getAnchorRegex(
							"/%full%/"
						) , $msg, $out, PREG_SET_ORDER);
		} catch (Exception $e) {
			trigger_error("���K�\�����s���ł��B<br>".$e->getMessage(),E_USER_ERROR);
		}            
        if (!$out) {return null;}


		foreach ($out as $matches) {
			if ($matches['ignore_prefix']) {continue;}
			$joined_ranges=$matches['ranges']; 
			try{
				if (!preg_match_all(
					$this->getAnchorRegex('/(?:%prefix%)?%a_range%/'), 
					$joined_ranges, $ranges_list, PREG_PATTERN_ORDER)
				) {continue;}
			} catch (Exception $e) {
				trigger_error(
					"���K�\�����s���ł��B<br>".$e->getMessage(),E_USER_ERROR
				);
			}
			$anchor_list=array_merge($anchor_list,$ranges_list['a_range']);
		}
		return $anchor_list;                    
    }

    // }}}
    // {{{ _get_quote_from()

    /**
     * �탌�X���X�g��Ԃ�.
     *
     * @return  array
     */
    public function get_quote_from()
    {
        if ($this->_quoter_list === null) {
            $this->_make_quote_from();  // �탌�X�f�[�^�W�v
        }
        return $this->_quoter_list;
    }

    // }}}
    // {{{ _quoteback_list_html()

    /**
     * �탌�X���X�g��HTML�Ő��`���ĕԂ�.
     *
     * @param   int     $resnum ���X�ԍ�
     * @param   int     $type   1:�c�`�� 2:���`�� 3:�W�J�p�u���b�N�p������
     * @param   bool    $popup  ���`���ł̃|�b�v�A�b�v����(true:�|�b�v�A�b�v����Afalse:�}������)
     * @return  string
     */
    protected function quoteback_list_html($resnum, $type,$popup=true)
    {
        $quote_from = $this->get_quote_from();
        if (!array_key_exists($resnum, $quote_from)) return $ret;

        $anchors = $quote_from[$resnum];
        sort($anchors);

        if ($type == 1) {
            return $this->_quoteback_vertical_list_html($anchors);
        } else if ($type == 2) {
            return $this->_quoteback_horizontal_list_html($anchors,$popup);
        } else if ($type == 3) {
            return $this->_quoteback_res_data($anchors);
        }
    }
    protected function _quoteback_vertical_list_html($anchors)
    {
        $ret = '<div class="v_reslist"><ul class="v_reslist_block">';
        $anchor_cnt = 1;
        foreach($anchors as $anchor) {
			$ret .= '<li class="v_reslist_item">';
            if ($anchor_cnt > 1) $ret .= '��</li>';
            if ($anchor_cnt < count($anchors)) {
                $ret .= '��';
            } else {
                $ret .= '��';
            }
            $ret .= $this->quoteRes(array($anchor, 'prefix'=>'', 'num1'=>$anchor), true);
            $anchor_cnt++;
        }
        $ret .= '</ul></div>';
        return $ret;
    }
    protected function _quoteback_horizontal_list_html($anchors,$popup)
    {
		global $_conf;

        $ret="";
        $ret.= '<div class="reslist">';
//        $count=0;

		if ($_conf['ktai'] && count($anchors)>1) {
			$word="^(".join("|",$anchors).")$";
			$filter_url = "{$_conf['read_php']}?bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;host={$this->thread->host}&amp;ls=all&amp;field=res&amp;word={$word}&amp;method=regex&amp;match=on&amp;idpopup=0&amp;offline=1";

			$ret.="<a href=\"{$filter_url}&amp;b=k\">";
			$ret.="ڽ�ꊇ�\��";
			$ret.='</a>';
		}

        foreach($anchors as $idx=>$anchor) {
            $anchor_link= $this->quoteRes(array('>>'.$anchor, 'prefix'=>'>>', 'num1'=>$anchor));

            $qres_id = $this->get_res_id("qr{$anchor}");
            $ret.="<div class=\"reslist_inner ${qres_id}\" >";
            $ret.=sprintf('�y�Q�ƃ��X�F%s�z',$anchor_link);
            $ret.='</div>';
//            $count++;
        }
        $ret.='</div>';

        return $ret;
    }
    protected function _quoteback_res_data($anchors)
    {
        foreach($anchors as &$anchor) {
            $anchor=($this->_matome ? "t{$this->_matome}" : "" ) ."qr".$anchor;
        }
		return join('/',$anchors);
    }

	protected function get_res_id($res = '')
	{
		return ($this->_matome ? "t{$this->_matome}" : '').$res ;
	}

    // }}}
    // {{{ getDatochiResiduums()

    /**
     * DAT�����̍ۂɎ擾�ł���>>1�ƍŌ�̃��X��HTML�ŕԂ�.
     *
     * @return  string|false
     */
    public function getDatochiResiduums()
    {
        $ret = '';
        $elines = $this->thread->datochi_residuums;
        if (!count($elines)) return $ret;

        $this->thread->onthefly = true;
        $ret = "<div><span class=\"onthefly\">on the fly</span></div>\n";
        $ret .= "<div class=\"thread\">\n";
		$reses=array();
        foreach($elines as $num => $line) {
            $res = $this->transRes($line, $num);
            $reses[] = is_array($res) ? $res['body'] . $res['q'] : $res;
        }
        $ret .= join('',$reses)."</div>\n";
        return $ret;
    }
    // }}}
    // {{{ getAutoFavRanks()

    /**
     * ���������N�ݒ��Ԃ�.
     *
     * @return  array
     */
    public function getAutoFavRank()
    {
        if ($this->_auto_fav_rank !== false) return $this->_auto_fav_rank;
        global $_conf;

        $ranks = explode(',', strtr($_conf['expack.ic2.fav_auto_rank_setting'], ' ', ''));
        $ret = null;
        if ($_conf['expack.misc.multi_favs']) {
            $idx = 0;
            if (!is_array($this->thread->favs)) return null;
            foreach ($this->thread->favs as $fav) {
                if ($fav) {
                    $rank = $ranks[$idx];
                    if (is_numeric($rank)) {
                        $rank = intval($rank);
                        $ret = $ret === null ? $rank
                            : ($ret < $rank ? $rank : $ret);
                    }
                }
                $idx++;
            }
        } else {
            if ($this->thread->fav && is_numeric($ranks[0])) {
                $ret = intval($ranks[0]);
            }
        }
        return $this->_auto_fav_rank = $ret;
    }

    // }}}
    // {{{ isAutoFavRankOverride()

    /**
     * ���������N�ݒ�Ń����N���㏑�����ׂ����Ԃ�.
     *
     * @param   int $now    ���݂̃����N
     * @param   int $new    ���������N
     * @return  bool
     */
    static public function isAutoFavRankOverride($now, $new)
    {
        global $_conf;

        switch ($_conf['expack.ic2.fav_auto_rank_override']) {
        case 0:
            return false;
            break;
        case 1:
            return $now != $new;
            break;
        case 2:
            return $now == 0 && $now != $new;
            break;
        case 3:
            return $now < $new;
            break;
        default:
            return false;
        }
        return false;
    }

    // }}}
	// {{{
    /**
     * ���X���i�t�B���^�����O��ʉ߂��āj�\������邩�ǂ�����m��
	*/	
    protected function isFiltered($i)
    {
		global $filter_hits;
		if (isset($this->thread->res_matched[$i])) {return $this->thread->res_matched[$i];}
        list($name, $mail, $date_id, $msg) = $this->thread->explodeDatLine(
			$this->thread->datlines[$i - 1]
		);
        if (($id = $this->thread->ids[$i]) !== null) {
            $idstr = $this->thread->idp[$i] . $id;
            $date_id = str_replace($this->thread->idp[$i] . $id, $idstr, $date_id);
        }

        // {{{ �t�B���^�����O
        if (isset($_REQUEST['word']) && strlen($_REQUEST['word']) > 0) {
            if (strlen($GLOBALS['word_fm']) <= 0) {return $this->thread->res_matched[$i]=false;}
            // �^�[�Q�b�g�ݒ�i��̂Ƃ��̓t�B���^�����O���ʂɊ܂߂Ȃ��j
            if (!$target = $this->getFilterTarget($ares, $i, $name, $mail, $date_id, $msg)) {return $this->thread->res_matched[$i]=false;}
            // �}�b�`���O
            if (!$this->filterMatch($target, $i,false)) {
//				$filter_hits--;	// �t�B���^�}�b�`�����̏d�����C��
return $this->thread->res_matched[$i]=false;
}
		}
		return $this->thread->res_matched[$i]=true;
	}

    /**
     * static
     * @access  public
     * @param   string  $pattern  ex)'/%full%/'
     * @return  string
     */
	static $_parts= array(); //ShowThread::getAnchorRegexParts()
    function getAnchorRegex($pattern,$name="")
    {
        static $caches_ = array();
		static $caches_ex=array();

		$pattern.="";
        if (!array_key_exists($pattern, $caches_) || $name) {
            $caches_ex[$pattern] = StrSjis::fixSjisRegex($pattern);
			foreach (self::$_parts as $token=>$regex) {
				$caches_ex[$pattern]=preg_replace_callback("/([ ]*)({$token})/",array('ShowThread','replaceAnchorRegex'),
$caches_ex[$pattern]);
			}

            $caches_[$pattern] = preg_replace("/[ ]*\n[ ]*/",'',$caches_ex[$pattern]);
//			echo "<pre>".htmlspecialchars($caches_ex[$pattern])."</pre><br>";
;
            // �卷�͂Ȃ��� compileMobile2chUriCallBack() �̂悤�� preg_relplace_callback()���Ă����������B
			if (preg_match("/%(\w+)%/",$caches_[$pattern],$out) ) {
				trigger_error("{$out[1]}�̐��K�\�������ݒ�ł��B",E_USER_WARNING);
			}

			// ���K�\�������@�I�ɐ��������ǂ����e�X�g
			if (preg_match("/^[\/{]/",$pattern)) {
				$matched=@preg_match($caches_[$pattern],"test");
			} else {
				$matched=@preg_match("/".$caches_[$pattern]."/","test");
			}
			if ($matched === false) {
				$errobj=error_get_last();
				if (preg_match("/offset (\d+)/",$errobj['message'],$out)) {
					$offsetChr=substr($caches_[$pattern],$out[1],1);
					$caches_[$pattern]=substr_replace($caches_[$pattern],"<b>{$offsetChr}</b>",$out[1],1);
				}
				$p=htmlspecialchars($pattern);
				$v=htmlspecialchars($caches_[$pattern]);

				$v=preg_replace("{&lt;(/?)b&gt;}","<$1b>",$v);
				throw new Exception(
					$errobj['message']
//					."<br>�W�J�O���K�\���F<pre>".$p."</pre>"
					."<br>�W�J�㐳�K�\���F<pre>".$v."</pre>"
				);
			}

			if ($name && !array_key_exists($name, self::$_parts)) {
				if (preg_match("/\W/",$name)) {
					throw new Exception("�s���ȃg�[�N�����ł��F{$name}");
				}
				self::$_parts['%'.$name.'%']=$caches_ex[$pattern];
//				echo nl2br(htmlspecialchars('%'.$name.'%'." set {$caches_ex[$pattern]}"))."<br><br>";
			}
//			echo nl2br(htmlspecialchars("{$pattern} changed {$caches_ex[$pattern]}"))."<br><br>";
        }
        return $caches_[$pattern];
    }

	static function replaceAnchorRegex(array $m) {
		$regex=self::$_parts[$m[2]];
//		var_export(array($m[2],$regex));echo "<br><br>";
		$regex=preg_replace("/^/m",$m[1],$regex);
		return $regex;
    }

    /**
     * static
     * @access  private
     * @return  string
     */
    function _getAnchorRegexParts()
    {

		$partsRegex['anchor_space']="(?: |�@)";	// �󔒕���

		// �A���J�[���p�q�i�_�u���A�V���O���j
		$prefix_double=self::_readRegexFromFile('p2_anchor_prefix_double.txt');
		array_unshift($prefix_double,"&gt;");	// �f�t�H���g���p�q

		$partsRegex['prefix_double']="(?:".join("|",$prefix_double).")";
		$partsRegex['prefix_double'].="\n%anchor_space%*\n".$partsRegex['prefix_double'];

		// �A���J�[���p�q�i�V���O���j
		$prefix_single=self::_readRegexFromFile('p2_anchor_prefix_single.txt');
		array_unshift($prefix_single,"&gt;");	// �f�t�H���g���p�q

		$partsRegex['prefix_single']="(?:".join("|",$prefix_single).")";

		// �A���J�[���p�q�i�I�v�V�����j
		$prefix_option=self::_readRegexFromFile('p2_anchor_prefix_option.txt');
		$partsRegex['prefix_option']=count($prefix_option) ? "(?:".join("|",$prefix_option).")?" : "";

		// ���X�ԍ��̋�؂�
		$delimiter=self::_readRegexFromFile('p2_anchor_delimiter.txt');
		array_unshift($delimiter,",");	// �f�t�H���g�̋�؂蕶��

//		array_unshift($delimiter,"%anchor_space%?");
//		array_push($delimiter,"%anchor_space%?");

		$partsRegex['delimiter']="(?:".join("|",$delimiter).")";

		// ���X�͈͎w��
		$range_delimiter=self::_readRegexFromFile('p2_anchor_range_delimiter.txt');
		array_unshift($range_delimiter,"-");	// �f�t�H���g�͈͎̔w�蕶��

		$partsRegex['range_delimiter']="(?:".join("|",$range_delimiter).")";

		// ���X�ԍ��ɕt������P��
		$a_num_suffix=self::_readRegexFromFile('p2_anchor_num_option.txt');
		$partsRegex['a_num_suffix']=count($a_num_suffix) ? "(?:".join("|",$a_num_suffix).")?" : "";

		// �͈͎w��Q�ɕt������P��
		$ranges_suffix=	self::_readRegexFromFile('p2_anchor_ranges_option.txt');
		$partsRegex['ranges_suffix']=count($ranges_suffix) ? "(?:".	join("|",$ranges_suffix).")?" : "";

		$partsRegex['no_prefix_suffix']="(".join("|",
			array_merge($a_num_suffix,$ranges_suffix,
				array(
					"%anchor_space%*(��|&gt;){1,2}",
					"�̑���"
				)
			)
		).")";

		// �A���J�[�𖳎�����㑱������
		$ignore_suffix=self::_readRegexFromFile('p2_anchor_ignore.txt');
		$partsRegex['ignore_suffix']=count($ignore_suffix) ? "(?!".join("|",$ignore_suffix).")" : "";

		$non_prefix_enable=false;	// �v���t�B�b�N�X�Ȃ��̃A���J�[�������邩�ǂ���

        // �A���J�[�̍\���v�f�i���K�\���p�[�c�̔z��j
		$parts=array(

			// ����
			'a_digit'	=>	"(?:\d|�O|�P|�Q|�R|�S|�T|�U|�V|�W|�X)",

			// �A���J�[���p�q >>
//			'prefix'	=>	"(((?P<prefix_double>%prefix_double%)|(?P<prefix_single>%prefix_single%))%prefix_option%%anchor_space%*)",
			'prefix'	=>
"(?:
  (?:
    %prefix_double%
  |
    %prefix_single%
  )
  %prefix_option%
  %anchor_space%*
)",
			'prefix2'	=>	
"(?:
  (?:
    %prefix_double%
  |
    %prefix_single%
  )
  %prefix_option%
  %anchor_space%*
)",

			// ���X�ԍ�
			'a_num'		=>	
'(?:
  %a_digit%{1,4}
)',
//			'a_num'		=>	'(%a_digit%{1,4}+)',
			'a_range'	=>	
"(?P<a_range>
  (?P<num1>
    %a_num%
  )
  %a_num_suffix%
  (?:
    %range_delimiter%
    %prefix2%?
    (?P<num2>
      %a_num%
    )
    %a_num_suffix%
  )?+
)",
			'a_range2'	=>	
"(?P<a_range2>
  %a_num%
  %a_num_suffix%
  (?:
    %range_delimiter%
    %prefix2%?
    %a_num%
    %a_num_suffix%
  )?+
)",

			// ���X�͈̗͂�
			'ranges'	=>
"(?P<ranges>
  %a_range%
  (?:
    (?:
      %delimiter%
      %prefix2%?
    |
      %prefix2%
    )
    %a_range2%
  )*
  %ranges_suffix%
  (?!
    %a_digit%
  )
)",

			// ���X�ԍ��̗�
			'nums'	=>	
"%a_num%
%a_num_suffix%
(
  %delimiter%
  %a_num%%a_num_suffix%
)*
%ranges_suffix%
(?!
  %a_digit%
)",

			// �T�t�B�b�N�X�ȍ~�̐��K�\���ɂ�0x40-0x7f�܂ł̕����͎g���Ȃ��iSJIS�̂Q�o�C�g�ڂƔ��̂Ō듮�삷��j
			// �v���t�B�b�N�X�t�����X�ԍ��ɑ����T�t�B�b�N�X
			'line_prefix'	=>	
"(?P<line_prefix>
  (?:^|<br>)%anchor_space%?
)",
 
			'line_suffix'	=>	
"(?:%anchor_space%*(?:$|<br>))", //(?=(\s|�@)*)"

			'full'	=>
"(?P<ignore_prefix>
  �O�X��
)?
(
  (?P<prefix>
     %prefix%
  )
|
  %line_prefix%
)
%ranges%
(?P<anchor_option>
  (?(prefix)
    %ignore_suffix%
  |
    (?(line_prefix)
      %line_suffix%
    )
  )
)",

		);
		$parts=array_merge($partsRegex,$parts);
		foreach ($parts as $k=>$v) {
			try{
				$this->getAnchorRegex($v,$k);
			} catch (Exception $e) {
				$print_v=htmlspecialchars($v);
				trigger_error(
					"�g�[�N�� %{$k}% �ɐݒ肷��p�^�[��<pre><code>{$print_v}</code></pre>������������܂���B<br>".$e->getMessage(),E_USER_WARNING
				);
			}
		}
	}

    /**
     * readRegexFromFile
     */
    static private function _readRegexFromFile($filename)
    {
        global $_conf;

        $file = $_conf['pref_dir'] . '/' . $filename;
		$array=array();

        if ($lines = FileCtl::file_read_lines($file)) {
			$lineno=0;
            foreach ($lines as $l) {
				$lineno++;
                $lar = explode("\t", trim($l));
                if (strlen($lar[0]) == 0) {
                    continue;
                }
				try{
					$array[]=self::getAnchorRegex($lar[0]);
				} catch (Exception $e) {
					$print_v=htmlspecialchars($lar[0]);
					trigger_error(
						"{$filename}��{$lineno}�s�ڂ���ǂݍ��񂾐��K�\��<pre><code>{$lar[0]}</code></pre>������������܂���B"
//						." in <b>".__FILE__ ."</b> on line <b>".__LINE__."</b>"
						."<br>".$e->getMessage(),E_USER_ERROR
					);
				}
			}
        }
        return $array;
    }

    /**
     * @access  private
     * @return  string
     */
    function buildStrToLinkRegex()
    {
		try{
			return $str_to_link_regex = $this->getAnchorRegex(
				'{'
	            . '(?P<link>(<[Aa][ ].+?>)(.*?)(</[Aa]>))' // �����N�iPCRE�̓�����A�K�����̃p�^�[�����ŏ��Ɏ��s����j
	            . '|'
	            .   '(?P<url>'
	            .       '(ftp|h?ttps?|tps?)://([0-9A-Za-z][\\w!#%&+*,\\-./:;=?@\\[\\]\\|^~]+)' // URL
	            .   ')'
	            . '|'
	            .   '(?P<id>'.
						'(?:ID:[ ]?([0-9A-Za-z/.+]{8,11}))'. // ID�i8,10�� +PC/�g�ю��ʃt���O�j
					'|'.
						'(?:���M��:((?:[1-9]\d{0,2})(?:\.[1-9]\d{0,2}){3}))'.
					')'
	            . '|'
	            .   '(?P<quote>' // ���p
				.       "%full%"
	            .   ')'
	            . '}'
			)
			;
		} catch (Exception $e) {
			trigger_error(
				"$str_to_link_regex������������܂���B<br>".$e->getMessage(),E_USER_ERROR
			);
		}
    }

// (?(11)yes-regexp|no-regexp) 
// 11�Ԗڂ̃L���v�`���O���[�v(%prefix%)�Ƀ}�b�`����ꍇ��yes-regexp���A
// �����łȂ��ꍇ��no-regexp���g��
	static function _str2num($str){
		return intval(preg_replace("/\s/","",mb_convert_kana($str, "ns")));
	}
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
