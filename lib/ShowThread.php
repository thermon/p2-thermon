<?php
/**
 * rep2- �X���b�h��\������ �N���X
 */

require_once P2_LIB_DIR . '/HostCheck.php';
require_once P2_LIB_DIR . '/ThreadRead.php';
require_once P2_LIB_DIR . '/StrSjis.php';

// {{{ ShowThread

abstract class ShowThread
{
    // {{{ constants

    /**
     * �����N�Ƃ��Ĉ����p�^�[��
     *
     * @type string
     */
    const LINK_REGEX = '{
(?P<link>(<[Aa][ ].+?>)(.*?)(</[Aa]>)) # �����N�iPCRE�̓�����A�K�����̃p�^�[�����ŏ��Ɏ��s����j
|
(?:
  (?P<quote> # ���p
    ((?:&gt;|��){1,2}[ ]?) # ���p��
    (
      (?:[1-9]\\d{0,3}) # 1�ڂ̔ԍ�
      (?:
        (?:[ ]?(?:[,=]|�A)[ ]?[1-9]\\d{0,3})+ # �A��
        |
        -(?:[1-9]\\d{0,3})? # �͈�
      )?
    )
    (?=\\D|$)
  ) # ���p�����܂�
|                                  # PHP 5.3����ɂ���Ȃ�A����\'�̃G�X�P�[�v���O���ANOWDOC�ɂ���
  (?P<url>(ftp|h?t?tps?)://([0-9A-Za-z][\\w;/?:@=&$\\-_.+!*\'(),#%\\[\\]^~]+)) # URL
  ([^\\s<>]*) # URL�̒���A�^�Oor�z���C�g�X�y�[�X�������܂ł̕�����
|
  (?P<id>ID:[ ]?([0-9A-Za-z/.+]{8,11})(?=[^0-9A-Za-z/.+]|$)) # ID�i8,10�� +PC/�g�ю��ʃt���O�j
)
}x';

    /**
     * ���_�C���N�^�̎��
     *
     * @type int
     */
    const REDIRECTOR_NONE = 0;
    const REDIRECTOR_IMENU = 1;
    const REDIRECTOR_PINKTOWER = 2;
    const REDIRECTOR_MACHIBBS = 3;

    /**
     * NG���ځ[��̎��
     *
     * @type int
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

    // }}}
    // {{{ static properties

    /**
     * �܂Ƃߓǂ݃��[�h���̃X���b�h��
     *
     * @type int
     */
    static private $_matome_count = 0;

    /**
     * �{���ȊO��NG���ځ[��Ƀq�b�g��������
     *
     * @type int
     */
    static protected $_ngaborns_head_hits = 0;

    /**
     * �{����NG���ځ[��Ƀq�b�g��������
     *
     * @type int
     */
    static protected $_ngaborns_body_hits = 0;

    // }}}
    // {{{ properties

    /**
     * �܂Ƃߓǂ݃��[�h���̃X���b�h�ԍ�
     *
     * @type int
     */
    protected $_matome;

    /**
     * URL����������֐��E���\�b�h���Ȃǂ��i�[����z��
     * (�g�ݍ���)
     *
     * @type array
     */
    protected $_url_handlers;

    /**
     * URL����������֐��E���\�b�h���Ȃǂ��i�[����z��
     * (���[�U��`�A�g�ݍ��݂̂��̂��D��)
     *
     * @type array
     */
    protected $_user_url_handlers;

    /**
     * �p�oID�����ځ[�񂷂�
     *
     * @type bool
     */
    protected $_ngaborn_frequent;

    /**
     * ���ځ[�񃌃X�ԍ������NG���X�ԍ����i�[����z��
     * array_intersect()�������悭�s�����߁A�Y�����郌�X�ԍ��͕�����ɃL���X�g���Ċi�[����
     *
     * @type array
     */
    protected $_aborn_nums;
    protected $_ng_nums;

    /**
     * ���_�C���N�^�̎��
     *
     * @type int
     */
    protected $_redirector;

    /**
     * �X���b�h�I�u�W�F�N�g
     *
     * @type ThreadRead
     */
    public $thread;

    /**
     * �A�N�e�B�u���i�[�E�I�u�W�F�N�g
     *
     * @type ActiveMona
     */
    public $activeMona;

    /**
     * �A�N�e�B�u���i�[���L�����ۂ�
     *
     * @type bool
     */
    public $am_enabled = false;

    protected $_quote_from; // ��A���J�[���W�v�����z�� // [��Q�ƃ��X�� : [�Q�ƃ��X��, ...], ...)

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

        // �X���b�h�I�u�W�F�N�g��o�^
        $this->thread = $aThread;
		$this->getAnchorRegexParts();
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

        $this->_aborn_nums = array();
        $this->_ng_nums = array();

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

    /**
     * static
     * @access  public
     * @param   string  $pattern  ex)'/%full%/'
     * @return  string
     */
    function getAnchorRegex($pattern,$name="")
    {
        static $caches_ = array();
		static $parts= array(); //ShowThread::getAnchorRegexParts()

        if (!array_key_exists($pattern, $caches_) || $name) {
            $caches_[$pattern] = strtr($pattern, $parts);
            // �卷�͂Ȃ��� compileMobile2chUriCallBack() �̂悤�� preg_relplace_callback()���Ă����������B
			if (preg_match("/%(.+?)%/",$caches_[$pattern],$out) ) {
				debug_print_backtrace();
				trigger_error("{$name}:{$out[1]}�̐��K�\�������ݒ�ł��B
", 	 E_USER_ERROR);

			}
			
			if ($out=preg_replace("/\(((?>[^()]+)|(?R))*\)/"," ... ",$caches_[$pattern])) {
				if (preg_match("/[()]/",$out)) {
					trigger_error("�J�b�R�����Ă��܂���F".$caches_[$pattern]);
					debug_print_backtrace();
				}
			}

			if ($name && !array_key_exists($name, $parts)) {
				$parts['%'.$name.'%']=$caches_[$pattern];
//				trigger_error("{$name} set {$caches_[$pattern]}");
			}
        }

        return $caches_[$pattern];
    }

    /**
     * static
     * @access  private
     * @return  string
     */
    function getAnchorRegexParts()
    {
        // �A���J�[�̍\���v�f�i���K�\���p�[�c�̔z��j
		$parts=array(
			// �󔒕���
			'anchor_space'=>"(?:\s|�@)",

			// ����
			'a_digit'	=>	"(?:\d|�O|�P|�Q|�R|�S|�T|�U|�V|�W|�X)",

			// �͈͎w��q
			'range_delimiter'	=>	"(?:-|�]|�|\x81\\x7c|\x81\\x5b)", // �[

			// �񋓎w��q
			'delimiter'		=>	"(?:%anchor_space%?(?:[,=+]|�A|�E|��|�C|��)%anchor_space%?)",
			'delimiter2'	=>	'%delimiter%',

			// ���ځ[��p�A���J�[���p�q
			'prefix_abon'	=>	"&gt;{1,2}{%anchor_space%}?",
			// ���X�͈�

			// �A���J�[���p�q >>
			'prefix'	=>	"(?:(?:(?:&gt;|&lt;|�r|��|��){1,2}|�t|��|(?:��){2})(?:\/|\*)?%anchor_space%*)",

			// ���X�ԍ�
			'a_num'		=>	'%a_digit%{1,4}',
			'a_range'	=>	"(?:%a_num%(?:%range_delimiter%\s*%prefix%?%a_num%)?+)",
			'a_range2'	=>	"(?:(?P<num1>%a_num%)(?P<num2>%range_delimiter%%prefix%?%a_num%)?+)",

			// �͈͎w�蒼��ɑ���������
			'a_num_suffix'	=>	"(?:����|����|�l)",

			// �͈͎w��Q�ɑ���������
			'ranges_suffix'		=>	"(?:�ł�|������)",

			// ���X�͈̗͂�
			'ranges'	=>
				"(?P<ranges>(?P<range1>%a_range2%)%a_num_suffix%?(?P<range2>%delimiter%%a_range%%a_num_suffix%?)*+%ranges_suffix%?)",

			// ���X�ԍ��̗�
			'nums'	=>	"%a_num%%a_num_suffix%?(?:%delimiter2%%a_num%%a_num_suffix%?)*+%ranges_suffix%?(?!%a_digit%)",

			// �T�t�B�b�N�X�ȍ~�̐��K�\���ɂ�0x40-0x7f�܂ł̕����͎g���Ȃ��iSJIS�̂Q�o�C�g�ڂƔ��̂Ō듮�삷��j
			// �v���t�B�b�N�X�t�����X�ԍ��ɑ����T�t�B�b�N�X
			'suffix'	=>	"(?:(?!�����(?:��|��)|%a_digit%|%prefix%|%anchor_space%(?:<br>|$)).)*",	//(?![\.]|)",
			'line_prefix'	=>	"(?P<line_prefix>(?:^|<br>)\s*)", 
			'line_suffix'	=>	"%anchor_space%*(?=<br>|$)", //(?=(?:\s|�@)*)"

/*
			// ���p�q�{�����ɑ���������i���p�q�A�����A�s���̒��O�܂Ń}�b�`�j
			'quote_follow'		=>	"(?:(?!%a_digit%|%prefix%|%anchor_space%(?:<br>|$)).)*", 

			// �s���v���t�B�b�N�X�^�T�t�B�b�N�X�i���X�ԍ��݂̂̍s���A���J�[��������j


			// ���̃A���J�[�̃v���t�B�b�N�X�^�T�t�B�b�N�X
			'no_prefix'	=>	
				 "(?P<no_prefix>((?:^|<br>)\s*)|" . StrSjis::getSjisRegex() . "|,)",

			'suffix_no_prefix'	=>	
				"(?=%a_num_suffix%|%ranges_suffix%|(?:��|&gt;){2}|�̑���)",

			'ignore_prefix'	=>	"(?P<ignore_prefix>�O�X��)",
			'reguler_prefix'	=>	
				"%ignore_prefix%?(?P<prefix>%prefix%)|%line_prefix%",
			'reguler_suffix'	=>	
				"", //|
*/
			'quote_follow' =>
				"(?P<quote_follow>".	//�A���J�[�ɑ���������
					"(?(line_prefix)".
						"%line_suffix%". //�s���v���t�B�b�N�X�Ȃ�΃X�y�[�X����				

					"|".
						"(?(no_prefix)".
							"(?=%a_num_suffix%|%ranges_suffix%|(?:��|&gt;){2}|�̑���)".	//�v���t�B�b�N�X�Ȃ��ŃA���J�[�F������㑱������
						")".
						"(?:(?!%a_digit%|%prefix%|%anchor_space%).)*".	//�����A�ʏ�v���t�B�b�N�X�A�X�y�[�X�ȊO�̂��̂�0�ȏ�

					")".
				")",

			'full'	=>	
				"(?P<ignore_prefix>�O�X��)?".	// �A���J�[�̑O�ɂ���ꍇ�A���J�[���̂��̂𖳎�����
				"(?:".
					"(?P<prefix>%prefix%)".		// �ʏ�v���t�B�b�N�X
					"|".
						"(?P<no_prefix>" . StrSjis::getSjisRegex() . "|,)".	//���̑��̃v���t�B�b�N�X
					"|".
						"%line_prefix%".	//�s���v���t�B�b�N�X
				")".
				"%ranges%".		//���X�͈́E�P�ƃ��X�̗�
				"%ranges_suffix%?".		//�A���J�[�Ɋ܂܂�镶����
				"%quote_follow%".
				"",

		);
		foreach ($parts as $k=>$v) {
			$this->getAnchorRegex($v,$k);
		}
//		trigger_error($this->getAnchorRegex("/%full%/"))."<br>";

		//�������������A���J�[��r�����邽�߂̏���W�J����
		$this->anchor_letter_ignore=self::_readWordFromFile('p2_anchor_ignore.txt');
	}
    /**
     * readNgAbornFromFile
     */
    static private function _readWordFromFile($filename)
    {
        global $_conf;

        $file = $_conf['pref_dir'] . '/' . $filename;
		$array=array();

        if ($lines = FileCtl::file_read_lines($file)) {
            foreach ($lines as $l) {
                $lar = explode("\t", trim($l));
				var_export($lar);
                if (strlen($lar[0]) == 0) {
                    continue;
                }
				$array[]=$lar[0];
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
        return $str_to_link_regex = '{'
            . '(?P<link>(<[Aa] .+?>)(.*?)(</[Aa]>))' // �����N�iPCRE�̓�����A�K�����̃p�^�[�����ŏ��Ɏ��s����j
            . '|'
            .   '(?P<url>'
            .       '(ftp|h?ttps?|tps?)://([0-9A-Za-z][\\w!#%&+*,\\-./:;=?@\\[\\]^~]+)' // URL
            .   ')'
            . '|'
            .   '(?P<id>ID: ?([0-9A-Za-z/.+]{8,11})(?=[^0-9A-Za-z/.+]|$))' // ID�i8,10�� +PC/�g�ю��ʃt���O�j
            . '|'
            .   '(?P<quote>' // ���p
			.       $this->getAnchorRegex(
						"%full%")
            .   ')'
            . '}';
    }
// (?(11)yes-regexp|no-regexp) 
// 11�Ԗڂ̃L���v�`���O���[�v(%prefix%)�Ƀ}�b�`����ꍇ��yes-regexp���A
// �����łȂ��ꍇ��no-regexp���g��

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
            for ($i = ($nofirst) ? 0 : 1; $i < $start; $i++) {
                list($name, $mail, $date_id, $msg) = $this->thread->explodeDatLine($this->thread->datlines[$i]);
                if (($id = $this->thread->ids[$i]) !== null) {
                    $date_id = str_replace($this->thread->idp[$i] . $id, $idstr, $date_id);
                }
                $this->_ngAbornCheck($i + 1, strip_tags($name), $mail, $date_id, $id, $msg);
            }
        }

        // �w��͈͂�\��
        for ($i = $start - 1; $i < $to; $i++) {
            if (!$nofirst and $i == 0) {
                continue;
            }
            if (!$this->thread->datlines[$i]) {
                $this->thread->readnum = $i;
                break;
            }
            $res = $this->transRes($this->thread->datlines[$i], $i + 1);
            if (is_array($res)) {
                $buf['body'] .= $res['body'];
                $buf['q'] .= $res['q'] ? $res['q'] : '';
            } else {
                $buf['body'] .= $res;
            }
            if (!$capture && $i % 10 == 0) {
                echo $buf['body'];
                flush();
                $buf['body'] = '';
            }
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
        global $_conf, $ngaborns_hits;

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

        if ($_conf['ngaborn_chain'] && preg_match_all('/(?:&gt;|��)([1-9][0-9\\-,]*)/', $msg, $matches)) {
            $references = array_unique(preg_split('/[-,]+/',
                                                  trim(implode(',', $matches[1]), '-,'),
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
                $info[] = sprintf('�A��NG:&gt;&gt;%d%s', array_shift($intersections), $info_suffix);
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
                if (!empty($v['regex'])) {
                    $re_method = $v['regex'];
                    /*if ($re_method($v['word'], $resfield, $matches)) {
                        $this->ngAbornUpdate($code, $k);
                        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
                        return htmlspecialchars($matches[0], ENT_QUOTES);
                    }*/
                     if ($re_method($v['word'], $resfield)) {
                        $this->ngAbornUpdate($code, $k);
                        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
                        return $v['cond'];
                    }
                // +Wiki:BE���ځ[��(���S��v)
                } else if ($code == 'aborn_be' || $code == 'ng_be') {
                    if ($resfield == $v['word']) {
                        $this->ngAbornUpdate($code, $k);
                        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
                        return $v['cond'];
                    }
               // �啶���������𖳎�
                } elseif ($ic || !empty($v['ignorecase'])) {
                    if (stripos($resfield, $v['word']) !== false) {
                        $this->ngAbornUpdate($code, $k);
                        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
                        return $v['cond'];
                    }
                // �P���ɕ����񂪊܂܂�邩�ǂ������`�F�b�N
                } else {
                    if (strpos($resfield, $v['word']) !== false) {
                        $this->ngAbornUpdate($code, $k);
                        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
                        return $v['cond'];
                    }
                }
            }
        }

        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
        return false;
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
    public function filterMatch($target, $resnum)
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

        $filter_hits++;

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
		$quote_index=10;
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
            return $this->idFilter($s['id'], $s[$id_index+1]);

        // ���p
        } elseif ($s['quote']) {
			$s2=array_slice($s,$quote_index+3);
//			if ($s2['line_prefix']) {
//				echo 'quote:';var_export($s2);echo "<br>";
//			}
//			if ($s2['prefix']) {var_export($s2);echo "<br>";}
			return $this->quoteResCallback($s2);
/*            return  preg_replace_callback(
                $this->getAnchorRegex('/(%prefix%)?(%a_range%)(%a_num_suffix%|%ranges_suffix%)?/'),
                array($this, 'quoteRes'), $s['quote']);
*/

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
        return preg_replace_callback(
            $this->getAnchorRegex('/(?P<quote>(?:%prefix%)?%a_num%)/'),
            array($this, 'quoteResCallback'), $s[0]
        );
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
    abstract public function quoteRes(array $s);

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
//		if (count($s)<10) {var_export($s);echo "<br>";}
		if ($s['ignore_prefix']) {return $s['quote'];}
		if ($s['quote_follow']) {
			foreach ($this->anchor_letter_ignore as $v) {
				if (strpos($s['quote_follow'],$v)=== 0) {
					return $s['quote'];
				}
			}
		}

		$var=preg_replace_callback(
			$this->getAnchorRegex('/(%prefix%)?(%a_range%)(%a_num_suffix%|%ranges_suffix%)?/'),
			array($this, 'quoteRes'), $s['quote']
		);
		return $var;
    }

    // }}}
    // {{{ quoteResRange()

    /**
     * ���p�ϊ��i�͈́j
     *
     * @param   string  $full           >>1-100
     * @param   string  $qsign          >>
     * @param   string  $appointed_num    1-100
     * @return  string
     */
    abstract public function quoteResRange($full, $qsign, $appointed_num);

    // }}}
    // {{{ quoteResRangeCallback()

    /**
     * ���p�ϊ��i�͈́j
     *
     * @param   array   $s  ���K�\���Ƀ}�b�`�����v�f�̔z��
     * @return  string
     */
    final public function quoteResRangeCallback(array $s)
    {
        return $this->quoteResRange($s[0], $s[1], $s[2]);
    }

    // }}}
	// {{{ getQuoteNum()
	/**
	 * �A���J�[���̃��X�ԍ��ϊ�
	 *
	 * @param   string  $appointed_num    1-100
	 * @return  string�@���X�ԍ��A�s�K�ȏꍇ��false��Ԃ�
	 */
	public function getQuoteNum($num)
	{
		$num = mb_convert_kana($num, 'n');   // �S�p�����𔼊p�����ɕϊ�
		if (preg_match("/\D/",$num)) {
			$num = preg_replace('/\D+/', '-', $num);
			if ($num == '-') {
				return false;
			}
			return $num;
		}
/*
		if (preg_match("/^0/", $num)) {
			return $full;
		}*/

		return $num;
	}
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

        if (preg_match_all($this->getAnchorRegex('/(?:^|%prefix%|%delimiter2%)(%a_num%)/'), $name, $matches)) {
            foreach ($matches[1] as $a_quote_res_num) {
                $quote_res_nums[] = (int)mb_convert_kana($a_quote_res_num, 'n');
            }
            return array_unique($quote_res_nums);
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
     * �탌�X�f�[�^���W�v����$this->_quote_from�ɕۑ�.
     */
    protected function _make_quote_from()
    {
        global $_conf;
        $this->_quote_from = array();
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
            if (!$ranges=$this->_getAnchorsFromMsg($msg,$num+1)) {continue;}
            foreach ($ranges as $a_range) {
                if (preg_match($this->getAnchorRegex('/(%a_num%)%range_delimiter%(?:%prefix%)?(%a_num%)/'), $a_range, $matches)) {
                    $from = intval(mb_convert_kana($matches[1], 'n'));
                    $to = intval(mb_convert_kana($matches[2], 'n'));
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
                    $this->_addQuoteNum($num,intval(mb_convert_kana($matches[1], 'n')));
                }
            }
        }
    }

    protected function _addQuoteNum($num,$quotee) {
		$quoter=$num+1;
		if ($_conf['backlink_list_future_anchor'] == 0) {
			if ($quotee >= $quoter) {return;}	// ���X�ԍ��ȍ~�̃A���J�[�͖�������
		}
        if (!array_key_exists($quotee, $this->_quote_from) || $this->_quote_from[$quotee] === null) {
            $this->_quote_from[$quotee] = array();
        }
        if (!in_array($quoter, $this->_quote_from[$quotee])) {
            $this->_quote_from[$quotee][] = $quoter;
        }
    }

    protected function _getAnchorsFromMsg($msg,$num) {
// debug_print_backtrace();
//		trigger_error("getAnchorsFromMsg:{$num}");
        $anchor_list=array();
        // >>1�̃����N����������O��
        // <a href="../test/read.cgi/accuse/1001506967/1" target="_blank">&gt;&gt;1</a>
        $msg = preg_replace('{<[Aa] .+?>(&gt;&gt;[1-9][\\d\\-]*)</[Aa]>}', '$1', $msg);
preg_match_all(
			$this->getAnchorRegex(
				"/%full%/"
			) , $msg, $out, PREG_SET_ORDER);

preg_match(
			$this->getAnchorRegex(
				"/%full%/"
			) , $msg, $out2);
//		echo "_getAnchorsFromMsg({$num}):";
//		var_export($out2);echo "<br>";
            
        if (!$out) {
//			echo "_getAnchorsFromMsg({$msg}) return null<br>";
		return null;}

		foreach ($out as $matches) {
			if ($matches['quote_follow']) {
//				var_export($matches);
				//�A���J�[�ɑ���������̐擪�ɖ������镶���񂪂�������A���̃A���J�[����������B
				foreach ($this->anchor_letter_ignore as $v) {
					if (strpos($matches['quote_follow'],$v)=== 0) {
						continue 2;		
					}
				}
			}

			$joined_ranges=$matches['ranges'];
			if (!preg_match_all(
				$this->getAnchorRegex('/(?:%prefix%)?(%a_range%)/'), 
				$joined_ranges, $ranges_list, PREG_PATTERN_ORDER)) 
			{continue;}
			$anchor_list=array_merge($anchor_list,$ranges_list[1]);
		}
//		echo "_getAnchorsFromMsg() return ";
//		var_export($anchor_list);echo"<br>";
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
        if ($this->_quote_from === null) {
            $this->_make_quote_from();  // �탌�X�f�[�^�W�v
        }
        return $this->_quote_from;
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
        $ret = '<div class="v_reslist"><ul>';
        $anchor_cnt = 1;
        foreach($anchors as $anchor) {
            if ($anchor_cnt > 1) $ret .= '<li>��</li>';
            if ($anchor_cnt < count($anchors)) {
                $ret .= '<li>��';
            } else {
                $ret .= '<li>��';
            }
            $ret .= $this->quoteRes(array($anchor, '', $anchor), true);
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
        $count=0;

		if ($_conf['ktai'] && count($anchors)>1) {
			$word="^(".join("|",$anchors).")$";
			$filter_url = "{$_conf['read_php']}?bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;host={$this->thread->host}&amp;ls=all&amp;field=res&amp;word={$word}&amp;method=regex&amp;match=on&amp;idpopup=0&amp;offline=1";

			$ret.="<a href=\"{$filter_url}&amp;b=k\">";
			$ret.="ڽ�ꊇ�\��";
			$ret.='</a>';
		}

        foreach($anchors as $idx=>$anchor) {
            $anchor_link= $this->quoteRes(array('>>'.$anchor, '>>', $anchor));
            $qres_id = ($this->_matome ? "t{$this->_matome}" : "" ) ."qr{$anchor}";
            $ret.='<div class="reslist_inner" >';
            $ret.=sprintf('<div>�y�Q�ƃ��X�F%s�z</div>',$anchor_link);
            $ret.='</div>';
            $count++;
        }
        $ret.='</div>';
        return $ret;
    }
    protected function _quoteback_res_data($anchors)
    {
        foreach($anchors as $idx=>$anchor) {
            $anchors2[]=($this->_matome ? "t{$this->_matome}" : "" ) ."qr{$anchor}";
        }

        return join('/',$anchors2);
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
        foreach($elines as $num => $line) {
            $res = $this->transRes($line, $num);
            $ret .= is_array($res) ? $res['body'] . $res['q'] : $res;
        }
        $ret .= "</div>\n";
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
