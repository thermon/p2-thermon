<?php
/**
 * rep2 - �g�їp�ŃX���b�h��\������ �N���X
 */

require_once P2EX_LIB_DIR . '/ExpackLoader.php';

ExpackLoader::loadAAS();
ExpackLoader::loadActiveMona();
ExpackLoader::loadImageCache();

// {{{ ShowThreadK

class ShowThreadK extends ShowThread
{
    // {{{ properties

    static private $_spm_objects = array();

    public $am_autong = false; // ����AA�������邩�ۂ�

    public $aas_rotate = '90����]'; // AAS ��]�����N������

    public $respopup_at = '';  // ���X�|�b�v�A�b�v�E�C�x���g�n���h��
    public $target_at = '';    // ���p�A�ȗ��AID�ANG���̃����N�^�[�Q�b�g
    public $check_st = '�m';   // �ȗ��ANG���̃����N������

    public $spmObjName; // �X�}�[�g�|�b�v�A�b�v���j���[�pJavaScript�I�u�W�F�N�g��

    private $_dateIdPattern;    // ���t���������̌����p�^�[��
    private $_dateIdReplace;    // ���t���������̒u��������

    //private $_lineBreaksReplace; // �A��������s�̒u��������

    private $_nanashiName = null;   // �f�t�H���g�̖��O
    private $_kushiYakiName = null; // BBQ�ɏĂ���Ă���Ƃ��̖��O�ړ���

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     */
    public function __construct(ThreadRead $aThread, $matome = false)
    {
        parent::__construct($aThread, $matome);

        global $_conf, $STYLE;

        if ($_conf['iphone']) {
            $this->respopup_at = ' onclick="return iResPopUp(this, event);"';
            $this->target_at = ' target="_blank"';
            $this->check_st = 'check';
        }

        $this->_url_handlers = array(
            'plugin_linkThread',
            'plugin_link2chSubject',
        );
        // +Wiki
        if (isset($GLOBALS['replaceimageurl'])) $this->_url_handlers[] = 'plugin_replaceImageURL';
        if (P2_IMAGECACHE_AVAILABLE == 2) {
            $this->_url_handlers[] = 'plugin_imageCache2';
        } elseif ($_conf['mobile.use_picto']) {
            $this->_url_handlers[] = 'plugin_viewImage';
        }
        if ($_conf['mobile.link_youtube']) {
            $this->_url_handlers[] = 'plugin_linkYouTube';
        }
        $this->_url_handlers[] = 'plugin_linkURL';

        if (!$_conf['mobile.bbs_noname_name']) {
            $st = new SettingTxt($this->thread->host, $this->thread->bbs);
            $st->setSettingArray();
            if (array_key_exists('BBS_NONAME_NAME', $st->setting_array)) {
                $BBS_NONAME_NAME = $st->setting_array['BBS_NONAME_NAME'];
                if (strlen($BBS_NONAME_NAME)) {
                    $this->_nanashiName = $BBS_NONAME_NAME;
                }
            }
        }

        if (P2Util::isHost2chs($aThread->host)) {
            $this->_kushiYakiName = ' </b>[�\{}@{}@{}-]<b> ';
        }

        if ($_conf['mobile.date_zerosuppress']) {
            $this->_dateIdPattern = '~^(?:' . date('Y|y') . ')/(?:0(\\d)|(\\d\\d))?(?:(/)0)?~';
            $this->_dateIdReplace = '$1$2$3';
        } else {
            $this->_dateIdPattern = '~^(?:' . date('Y|y') . ')/~';
            $this->_dateIdReplace = '';
        }

        // �A��������s�̒u���������ݒ�
        /*
        if ($_conf['mobile.strip_linebreaks']) {
            $ngword_color = $GLOBALS['STYLE']['mobile_read_ngword_color'];
            if (strpos($ngword_color, '\\') === false && strpos($ngword_color, '$') === false) {
                $this->_lineBreaksReplace = " <br><s><font color=\"{$ngword_color}\">***</font></s><br> ";
            } else {
                $this->_lineBreaksReplace = ' <br><s>***</s><br> ';
            }
        } else {
            $this->_lineBreaksReplace = null;
        }
        */

        // �T���l�C���\����������ݒ�
        if (!isset($GLOBALS['pre_thumb_unlimited']) || !isset($GLOBALS['expack.ic2.pre_thumb_limit_k'])) {
            if (isset($_conf['expack.ic2.pre_thumb_limit_k']) && $_conf['expack.ic2.pre_thumb_limit_k'] > 0) {
                $GLOBALS['pre_thumb_limit_k'] = $_conf['expack.ic2.pre_thumb_limit_k'];
                $GLOBALS['pre_thumb_unlimited'] = false;
            } else {
                $GLOBALS['pre_thumb_limit_k'] = null;   // �k���l����isset()��FALSE��Ԃ�
                $GLOBALS['pre_thumb_unlimited'] = true;
            }
        }
        $GLOBALS['pre_thumb_ignore_limit'] = false;

        // �A�N�e�B�u���i�[������
        if (P2_ACTIVEMONA_AVAILABLE) {
            ExpackLoader::initActiveMona($this);
        }

        // ImageCache2������
        if (P2_IMAGECACHE_AVAILABLE == 2) {
            ExpackLoader::initImageCache($this);
        }

        // AAS ������
        if (P2_AAS_AVAILABLE) {
            ExpackLoader::initAAS($this);
        }

        // SPM������
        //if ($this->_matome) {
        //    $this->spmObjName = sprintf('t%dspm%u', $this->_matome, crc32($this->thread->keydat));
        //} else {
            $this->spmObjName = sprintf('spm%u', crc32($this->thread->keydat));
        //}
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
    public function transRes($ares, $i)
    {
        global $_conf, $STYLE, $mae_msg, $res_filter;

        list($name, $mail, $date_id, $msg) = $this->thread->explodeDatLine($ares);
        if (($id = $this->thread->ids[$i]) !== null) {
            $idstr = $this->thread->idp[$i] . $id;
            $date_id = str_replace($this->thread->idp[$i] . $id, $idstr, $date_id);
        } else {
            $idstr = null;
        }

        // {{{ �t�B���^�����O

        if (isset($_REQUEST['word']) && strlen($_REQUEST['word']) > 0) {
            if (strlen($GLOBALS['word_fm']) <= 0) {
                return '';
            // �^�[�Q�b�g�ݒ�i��̂Ƃ��̓t�B���^�����O���ʂɊ܂߂Ȃ��j
            } elseif (!$target = $this->getFilterTarget($ares, $i, $name, $mail, $date_id, $msg)) {
                return '';
            // �}�b�`���O
            } elseif (!$this->filterMatch($target, $i)) {
                return '';
            }
        }

        // }}}

        $tores = '';
        if ($this->_matome) {
            $res_id = "t{$this->_matome}r{$i}";
        } else {
            $res_id = "r{$i}";
        }

        // NG���ځ[��`�F�b�N
        $nong = !empty($_GET['nong']);
        $ng_type = $this->_ngAbornCheck($i, strip_tags($name), $mail, $date_id, $id, $msg, $nong, $ng_info);
        if ($ng_type == self::ABORN) {
            return $this->_abornedRes($res_id);
        }
        if (!$nong && $this->am_autong && $this->activeMona->detectAA($msg)) {
            $is_ng = array_key_exists($i, $this->_ng_nums);
            $ng_type |= $this->_markNgAborn($i, self::NG_AA, true);
            $ng_info[] = 'AA��';
            // AA��A��NG�Ώۂ���O���ꍇ
            if (!$is_ng && $_conf['expack.am.autong_k'] == 2) {
                unset($this->_ng_nums[$i]);
            }
        }
        if ($ng_type != self::NG_NONE) {
            $ngaborns_head_hits = self::$_ngaborns_head_hits;
            $ngaborns_body_hits = self::$_ngaborns_body_hits;
        }

        // {{{ ���O�Ɠ��t�EID�𒲐�

        // ���Ă��}�[�N��Z�k
        if ($this->_kushiYakiName !== null && strpos($name, $this->_kushiYakiName) === 0) {
            $name = substr($name, strlen($this->_kushiYakiName));
            // �f�t�H���g�̖��O�͏ȗ�
            if ($name === $this->_nanashiName) {
                $name = '[��]';
            } else {
                $name = '[��]' . $name;
            }
        // �f�t�H���g�̖��O�Ɠ����Ȃ�ȗ�
        } elseif ($name === $this->_nanashiName) {
            $name = '';
        }

        // ���݂̔N���͏ȗ��J�b�g����B�����̐擪0���J�b�g�B
        $date_id = preg_replace($this->_dateIdPattern, $this->_dateIdReplace, $date_id);

        // �j���Ǝ��Ԃ̊Ԃ��l�߂�
        $date_id = str_replace(') ', ')', $date_id);

        // �b���J�b�g
        if ($_conf['mobile.clip_time_sec']) {
            $date_id = preg_replace('/(\\d\\d:\\d\\d):\\d\\d(?:\\.\\d\\d)?/', '$1', $date_id);
        }

        // ID
        if ($id !== null) {
            $id_suffix = substr($id, -1);

            if ($_conf['mobile.underline_id'] && $id_suffix == 'O' && strlen($id) % 2) {
                $do_underline_id_suffix = true;
            } else {
                $do_underline_id_suffix = false;
            }

            if ($this->thread->idcount[$id] > 1) {
                if ($_conf['flex_idpopup'] == 1) {
                    $date_id = str_replace($idstr, $this->idFilter($idstr, $id), $date_id);
                }
                if ($do_underline_id_suffix) {
                    $date_id = str_replace($idstr, substr($idstr, 0, -1) . '<u>' . $id_suffix . '</u>', $date_id);
                }
            } else {
                if ($_conf['mobile.clip_unique_id']) {
                    if ($do_underline_id_suffix) {
                        $date_id = str_replace($idstr, 'ID:*<u>' . $id_suffix . '</u>', $date_id);
                    } else {
                        $date_id = str_replace($idstr, 'ID:*' . $id_suffix, $date_id);
                    }
                } else {
                    if ($do_underline_id_suffix) {
                        $date_id = str_replace($idstr, substr($idstr, 0, -1) . '<u>' . $id_suffix . '</u>', $date_id);
                    }
                }
            }
        } else {
            if ($_conf['mobile.clip_unique_id']) {
                $date_id = str_replace('ID:???', 'ID:?', $date_id);
            }
        }

        // }}}

        //=============================================================
        // �܂Ƃ߂ďo��
        //=============================================================

        if ($name) {
            $name = $this->transName($name); // ���OHTML�ϊ�
        }
        $msg = $this->transMsg($msg, $i); // ���b�Z�[�WHTML�ϊ�

        // BE�v���t�@�C�������N�ϊ�
        $date_id = $this->replaceBeId($date_id, $i);

        // NG���b�Z�[�W�ϊ�
        if ($ng_type != self::NG_NONE && count($ng_info)) {
            $ng_info = implode(', ', $ng_info);
            if ($ng_type == self::NG_AA && $_conf['iphone']) {
                $msg = <<<EOMSG
<a class="button" href="{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;ls={$i}&amp;k_continue=1&amp;nong=1{$_conf['k_at_a']}"{$this->respopup_at}{$this->target_at}>{$ng_info}</a>
EOMSG;
            } else {
                $msg = <<<EOMSG
<s><font color="{$STYLE['mobile_read_ngword_color']}">{$ng_info}</font></s> <a class="button" href="{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;ls={$i}&amp;k_continue=1&amp;nong=1{$_conf['k_at_a']}"{$this->respopup_at}{$this->target_at}>{$this->check_st}</a>
EOMSG;
            }

            // AAS
            if (($ng_type & self::NG_AA) && P2_AAS_AVAILABLE) {
                $aas_url = "aas.php?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;resnum={$i}";
                if (P2_AAS_AVAILABLE == 2) {
                    $aas_txt = "<img src=\"{$aas_url}{$_conf['k_at_a']}&amp;inline=1\">";
                } else {
                    $aas_txt = "AAS";
                }
                if ($_conf['iphone']) {
                    //$img_title = htmlspecialchars($this->thread->getMotoThread(true, $i), ENT_QUOTES);
                    //$img_title = "{$this->thread->bbs}/{$this->thread->key}/{$i}";
                    //$img_title = "{$this->thread->ttitle_hd}&#10;&gt;&gt;{$i}";
                    $msg .= " <a class=\"aas limelight\" href=\"{$aas_url}&amp;b=pc\" title=\"&gt;&gt;{$i}\"{$this->target_at}>{$aas_txt}</a>";
                } else {
                    $msg .= " <a class=\"aas\" href=\"{$aas_url}{$_conf['k_at_a']}\"{$this->target_at}>{$aas_txt}</a>";
                    $msg .= " <a class=\"button\" href=\"{$aas_url}{$_conf['k_at_a']}&amp;rotate=1\"{$this->target_at}>{$this->aas_rotate}</a>";
                }
            }
        }

        // NG�l�[���ϊ�
        if ($ng_type & self::NG_NAME) {
            $name = <<<EONAME
<s><font color="{$STYLE['mobile_read_ngword_color']}">{$name}</font></s>
EONAME;
            $msg = <<<EOMSG
<a class="button" href="{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;ls={$i}&amp;k_continue=1&amp;nong=1{$_conf['k_at_a']}"{$this->respopup_at}{$this->target_at}>{$this->check_st}</a>
EOMSG;

        // NG���[���ϊ�
        } elseif ($ng_type & self::NG_MAIL) {
            $mail = <<<EOMAIL
<s class="ngword" onmouseover="document.getElementById('ngn{$ngaborns_head_hits}').style.display = 'block';">{$mail}</s>
EOMAIL;
            $msg = <<<EOMSG
<div id="ngn{$ngaborns_head_hits}" style="display:none;">{$msg}</div>
EOMSG;

        // NGID�ϊ�
        } elseif ($ng_type & self::NG_ID) {
            $date_id = <<<EOID
<s><font color="{$STYLE['mobile_read_ngword_color']}">{$date_id}</font></s>
EOID;
            $msg = <<<EOMSG
<a class="button" href="{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;ls={$i}&amp;k_continue=1&amp;nong=1{$_conf['k_at_a']}"{$this->respopup_at}{$this->target_at}>{$this->check_st}</a>
EOMSG;
        }

        /*
        //�u��������V���v�摜��}��
        if ($i == $this->thread->readnum +1) {
            $tores .= <<<EOP
                <div><img src="img/image.png" alt="�V�����X" border="0" vspace="4"></div>
EOP;
        }
        */

		$tores .="<a name=\"{$res_id}\"></a>";
        if ($_conf['iphone']) {
            $tores .= "<div id=\"{$res_id}\" class=\"res\"><div class=\"res-header\">";

            $no_class = 'no';
            $no_onclick = '';

            // �I���U�t���C��
            if ($this->thread->onthefly) {
                $GLOBALS['newres_to_show_flag'] = true;
                $no_class .= ' onthefly';
            // �V�����X��
            } elseif ($i > $this->thread->readnum) {
                $GLOBALS['newres_to_show_flag'] = true;
                $no_class .= ' newres';
            }

            // SPM
            if ($_conf['expack.spm.enabled']) {
                $no_onclick = " onclick=\"{$this->spmObjName}.show({$i},'{$res_id}',event)\"";
            }

            // �ԍ�
            $tores .= "<span class=\"{$no_class}\"{$no_onclick}>{$i}</span>";
            // ���O
            $tores .= " <span class=\"name\">{$name}</span>";
            // ���[��
            $tores .= " <span class=\"mail\">{$mail}</span>";
            // ���t��ID
            $tores .= " <span class=\"date-id\">{$date_id}</span></div>\n";
            // ���e
            $tores .= "<div class=\"message\">{$msg}</div>";
            // �탌�X���X�g
            if ($_conf['mobile.backlink_list'] == 1) {
                $linkstr = $this->quoteback_list_html($i, 2);
                if (strlen($linkstr)) {
                    $tores .= '<br>' . $linkstr;
                }
            }
            $tores .= "</div>\n"; // ���e�����
        } else {
            // �ԍ��i�I���U�t���C���j
            if ($this->thread->onthefly) {
                $GLOBALS['newres_to_show_flag'] = true;
                $tores .= "<div id=\"{$res_id}\" name=\"{$res_id}\">[<font color=\"{$STYLE['mobile_read_onthefly_color']}'\">{$i}</font>]";
            // �ԍ��i�V�����X���j
            } elseif ($i > $this->thread->readnum) {
                $GLOBALS['newres_to_show_flag'] = true;
                $tores .= "<div id=\"{$res_id}\" name=\"{$res_id}\">[<font color=\"{$STYLE['mobile_read_newres_color']}\">{$i}</font>]";
            // �ԍ�
            } else {
                $tores .= "<div id=\"{$res_id}\" name=\"{$res_id}\">[{$i}]";
            }

            // ���O
            if ($name) {
                $tores .= "{$name}: "; }
            // ���[��
            if ($mail) {
                $tores .= "{$mail}: ";
            }
            // ���t��ID
            $tores .= "{$date_id}<br>\n";
            // ���e
            $tores .= "{$msg}</div>\n";
            // �탌�X���X�g
            if ($_conf['mobile.backlink_list'] == 1) {
                $linkstr = $this->quoteback_list_html($i, 2);
                if (strlen($linkstr)) {
                    $tores .= '<br>' . $linkstr;
                }
            }
            $tores .= "<hr>\n";
        }

        // �܂Ƃ߂ăt�B���^�F����
        if ($GLOBALS['word_fm'] && $GLOBALS['res_filter']['match'] != 'off') {
            if (is_string($_conf['k_filter_marker'])) {
                $tores = StrCtl::filterMarking($GLOBALS['word_fm'], $tores, $_conf['k_filter_marker']);
            } else {
                $tores = StrCtl::filterMarking($GLOBALS['word_fm'], $tores);
            }
        }

        // �S�p�p���X�y�[�X�J�i�𔼊p��
        if (!empty($_conf['mobile.save_packet'])) {
            $tores = mb_convert_kana($tores, 'rnsk'); // CP932 ���� ask �� �� �� < �ɕϊ����Ă��܂��悤��
        }

        return $tores;
    }

    // }}}
    // {{{ transName()

    /**
     * ���O��HTML�p�ɕϊ�����
     *
     * @param   string  $name   ���O
     * @return  string
     */
    public function transName($name)
    {
        $name = strip_tags($name);

        // �g���b�v��z�X�g�t���Ȃ番������
        if (($pos = strpos($name, '��')) !== false) {
            $trip = substr($name, $pos);
            $name = substr($name, 0, $pos);
        } else {
            $trip = null;
        }

        // ���������p���X�|�b�v�A�b�v�����N��
        if (strlen($name) && $name != $this->BBS_NONAME_NAME) {
			try{
	            $name = preg_replace_callback(
	                $this->getAnchorRegex('/((?P<prefix>%prefix2%)|%line_prefix%)%nums%(?(line_prefix)%line_suffix%)/'),
	                array($this, 'quote_name_callback'), $name
	            );
			} catch (Exception $e) {
				trigger_error(
					"���K�\�����s���ł��B<br>".$e->getMessage(),E_USER_ERROR
				);
			}
        }

        if ($trip) {
            $name .= $trip;
        } elseif ($name) {
            // �����������
            $name = $name . ' ';
            //if (in_array(0xF0 & ord(substr($name, -1)), array(0x80, 0x90, 0xE0))) {
            //    $name .= ' ';
            //}
        }

        return $name;
    }

    // }}}
    // {{{ transMsg()

    /**
     * dat�̃��X���b�Z�[�W��HTML�\���p���b�Z�[�W�ɕϊ�����
     *
     * @param   string  $msg    ���b�Z�[�W
     * @param   int     $mynum  ���X�ԍ�
     * @return  string
     */
    public function transMsg($msg, $mynum)
    {
        global $_conf;
        global $res_filter, $word_fm;
        global $pre_thumb_ignore_limit;

        $ryaku = false;

        // 2ch���`����dat
        if ($this->thread->dat_type == '2ch_old') {
            $msg = str_replace('���M', ',', $msg);
            $msg = preg_replace('/&amp(?=[^;])/', '&', $msg);
        }

        // �Z�~�R�����̂Ȃ����̎Q�Ƃ��C��
        $msg = preg_replace("/(&#\d{3,5});?/","$1;",$msg);

        // &�␳
        $msg = preg_replace('/&(?!#?\\w+;)/', '&amp;', $msg);

        // >>1�̃����N����������O��
        // <a href="../test/read.cgi/accuse/1001506967/1" target="_blank">&gt;&gt;1</a>
        $msg = preg_replace('{<[Aa] .+?>(&gt;&gt;\\d[\\d\\-]*)</[Aa]>}', '$1', $msg);

        // �傫������
        if (empty($_GET['k_continue']) && strlen($msg) > $_conf['mobile.res_size']) {
            // <br>�ȊO�̃^�O���������A������؂�l�߂�
            $msg = strip_tags($msg, '<br>');
            $msg = mb_strcut($msg, 0, $_conf['mobile.ryaku_size']);
            $msg = preg_replace('/ *<[^>]*$/', '', $msg);

            // >>1, >1, ��1, ����1�����p���X�|�b�v�A�b�v�����N��
			try{
	            $msg = preg_replace_callback(
	                $this->getAnchorRegex('/%full%/m'),
	                array($this, 'quoteResCallback'), $msg
	            );
			} catch (Exception $e) {
				trigger_error(
					"���K�\�����s���ł��B<br>".$e->getMessage(),E_USER_ERROR
				);
			}
            $msg .= "<a href=\"{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;ls={$mynum}&amp;k_continue=1&amp;offline=1{$_conf['k_at_a']}\"{$this->respopup_at}{$this->target_at}>��</a>";
            return $msg;
        }

        // �V�����X�̉摜�͕\�������𖳎�����ݒ�Ȃ�
        if ($mynum > $this->thread->readnum && $_conf['expack.ic2.newres_ignore_limit_k']) {
            $pre_thumb_ignore_limit = TRUE;
        }

        // �����̉��s�ƘA��������s������
        if ($_conf['mobile.strip_linebreaks']) {
            $msg = $this->stripLineBreaks($msg /*, $this->_lineBreaksReplace*/);
        }

        // ���p��URL�Ȃǂ������N
        $msg = $this->transLink($msg);

        // Wikipedia�L�@�ւ̎��������N
        if ($_conf['mobile.link_wikipedia']) {
            $msg = $this->wikipediaFilter($msg);
        }

        return $msg;
    }

    // }}}
    // {{{ _abornedRes()

    /**
     * ���ځ[�񃌃X��HTML���擾����
     *
     * @param  string $res_id
     * @return string
     */
    protected function _abornedRes($res_id)
    {
        global $_conf;
        if ($_conf['ngaborn_purge_aborn']) return '';
        return <<<EOP
<div id="{$res_id}" name="{$res_id}" class="res aborned">&nbsp;</div>\n
EOP;
    }

    // }}}
    // {{{ getSpmObjJs()

    /**
     * �X�}�[�g�|�b�v�A�b�v���j���[�ɕK�v�ȃX���b�h�����i�[����JavaScript�R�[�h���擾
     */
    public function getSpmObjJs($retry = false)
    {
        global $_conf;

        if (isset(self::$_spm_objects[$this->spmObjName])) {
            return $retry ? self::$_spm_objects[$this->spmObjName] : '';
        }

        $ttitle_en = UrlSafeBase64::encode($this->thread->ttitle);

        $motothre_url = $this->thread->getMotoThread();
        $motothre_url = substr($motothre_url, 0, strlen($this->thread->ls) * -1);

        // �G�X�P�[�v
        $_spm_title = StrCtl::toJavaScript($this->thread->ttitle_hc);
        $_spm_url = addslashes($motothre_url);
        $_spm_host = addslashes($this->thread->host);
        $_spm_bbs = addslashes($this->thread->bbs);
        $_spm_key = addslashes($this->thread->key);
        $_spm_ls = addslashes($this->thread->ls);
        $_spm_b = ($_conf['view_forced_by_query']) ? "&b={$_conf['b']}" : '';

        $code = <<<EOJS
<script type="text/javascript">
//<![CDATA[
var {$this->spmObjName} = {
    'objName':'{$this->spmObjName}',
    'query':'&host={$_spm_host}&bbs={$_spm_bbs}&key={$_spm_key}&rescount={$this->thread->rescount}&ttitle_en={$ttitle_en}{$_spm_b}',
    'rc':'{$this->thread->rescount}',
    'title':'{$_spm_title}',
    'ttitle_en':'{$ttitle_en}',
    'url':'{$_spm_url}',
    'host':'{$_spm_host}',
    'bbs':'{$_spm_bbs}',
    'key':'{$_spm_key}',
    'ls':'{$_spm_ls}',
    'client':['{$_conf['b']}','{$_conf['client_type']}']
};
{$this->spmObjName}.show = (function(no,id,evt){SPM.show({$this->spmObjName},no,id,evt);});
{$this->spmObjName}.hide = SPM.hide; // (function(evt){SPM.hide(evt);});
//]]>
</script>\n
EOJS;

        self::$_spm_objects[$this->spmObjName] = $code;

        return $code;
    }

    // }}}
    // {{{ getSpmElementHtml()

    /**
     * �X�}�[�g�|�b�v�A�b�v���j���[�p��HTML�𐶐�����
     */
    static public function getSpmElementHtml()
    {
        global $_conf;

        return <<<EOP
<div id="spm">
<div id="spm-reply">
    <span id="spm-reply-quote" onclick="SPM.replyTo(true)">&gt;&gt;<span id="spm-num">???</span>�Ƀ��X</span>
    <span id="spm-reply-noquote" onclick="SPM.replyTo(false)">[���p�Ȃ�]</span>
</div>
<div id="spm-action"><select id="spm-select-target">
    <option value="name">���O</option>
    <option value="mail">���[��</option>
    <option value="id" selected>ID</option>
    <option value="msg">�{��</option>
</select>��<select id="spm-select-action">
    <option value="aborn" selected>���ځ[��</option>
    <option value="ng">NG</option>
<!-- <option value="search">����</option> -->
</select><input type="button" onclick="SPM.doAction()" value="OK"></div>
<img id="spm-closer" src="img/iphone/close.png" width="24" height="26" onclick="SPM.hide(event)">
</div>
EOP;
    }

    // }}}
    // {{{ idFilter()

    /**
     * ID�t�B���^�����O�����N�ϊ�
     *
     * @param   string  $idstr  ID:xxxxxxxxxx
     * @param   string  $id        xxxxxxxxxx
     * @return  string
     */
    public function idFilter($idstr, $id)
    {
        global $_conf;

        //$idflag = '';   // �g��/PC���ʎq
        // ID��8���܂���10��(+�g��/PC���ʎq)�Ɖ��肵��
        /*
        if (strlen($id) % 2 == 1) {
            $id = substr($id, 0, -1);
            $idflag = substr($id, -1);
        } elseif (isset($s[2])) {
            $idflag = $s[2];
        }
        */

        $filter_url = "{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;ls=all&amp;offline=1&amp;idpopup=1&amp;field=id&amp;method=just&amp;match=on&amp;word=" . rawurlencode($id).$_conf['k_at_a'];

        if (isset($this->thread->idcount[$id]) && $this->thread->idcount[$id] > 0) {
            $num_ht = "(<a href=\"{$filter_url}\"{$this->target_at}>{$this->thread->idcount[$id]}</a>)";
        } else {
            return $idstr;
        }

        return "{$idstr}{$num_ht}";
    }

    // }}}
    // {{{ link_wikipedia()

    /**
     * @see ShowThread
     */
    function link_wikipedia($word) {
        global $_conf;
        $link = 'http://ja.wapedia.org/' . rawurlencode($word);
        return  '<a href="' . ($_conf['through_ime'] ?
            P2Util::throughIme($link) : $link) .  "\">{$word}</a>";
    }

    // }}}
    // {{{ quoteRes()

    /**
     * ���p�ϊ��i�P�Ɓj
     *
     * @param  array  $s array([0]=">>1-100",['prefix']=">>",['num1']=1,['num2']=100)
     * @return string
     */
    public function quoteRes(array $s)
    {
        global $_conf, $STYLE;

		$full=$s[0];
		$qsign=$s['prefix'];
		$qnum=intval(
			preg_replace("/\s/",'',
				mb_convert_kana($s['num1'], 'ns')
			)
		);	// �S�p�̐����ƃX�y�[�X�𔼊p�ɕϊ����X�y�[�X�폜

        if ($s['num2']) {
			$to=intval(
				preg_replace("/\s/",'',
					mb_convert_kana($s['num2'], 'ns')
				)
			);	// �S�p�̐����ƃX�y�[�X�𔼊p�ɕϊ����X�y�[�X�폜
			if ($to < $qnum) {		// �͈͎w�肪�t����������
				return $full;
//				list($qnum,$to)=array($to,$qnum);	// �ԍ�����ւ�
			}
            return $this->quoteResRange($full, $qsign, $qnum, $to);
        }

        if ($qnum < 1 || $qnum > $this->thread->rescount) {
            return $full;
        }

		$start=$this->thread->resrange['start'];
		$end=min($this->thread->resrange['to'],$this->thread->rescount);
		$nofirst=$this->thread->resrange['nofirst'];
		$range=$_conf['mobile.rnum_range'];
		$anchor_jump=true;

		$read_url="";
		if ($_conf['mobile.anchor_link_page']>=1 &&
			$this->isFiltered($qnum) &&
			( 
				($qnum == 1 && !$nofirst) ||
				($qnum >= $start && $qnum <= $end)
			)
	 	) {	// �y�[�W���ɃW�����v�悪�\������Ă���
			$read_url = "#" .  $this->get_res_id("r{$qnum}"); //($this->_matome ? "t{$this->_matome}" : '') . "r{$qnum}";

		} elseif ($_conf['mobile.anchor_link_page']==1) {	// �y�[�W�P�ʂŃW�����v���\������
			if ($qnum<$start) {		// �O�̃y�[�W�ɃW�����v
				$new_start=$end+1 -ceil(($end -$qnum)/$range)*$range;
				if ($new_start<=0) {$new_start=1;}
			} else	{	// ���̃y�[�W�ɃW�����v
				$new_start=$start +floor(($qnum-$start)/$range)*$range;	// ls=all�̏ꍇ�A$start��1�̂͂�
			}
			$new_end=min($new_start-1+$range,$this->thread->rescount);
			return $this->quoteResRange($full, $qsign, $new_start, $new_end, $qnum);
		}
		if (!$read_url) {
			$read_url = "{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;offline=1&amp;ls={$qnum}{$_conf['k_at_a']}";	// �Q�Ɛ悾���̃y�[�W���J��URL
//	        $read_url = "{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;offline=1&amp;ls=all&amp;field=res&amp;word=^{$qnum}$&amp;method=regex&amp;match=on&amp;idpopup=0{$_conf['k_at_a']}";	// ���X�ԍ�����������URL
		}
        return "<a href=\"{$read_url}\"{$this->respopup_at}{$this->target_at}>"
            . (in_array($qnum, $this->_aborn_nums) ? "<s><font color=\"{$STYLE['mobile_read_ngword_color']}\">{$full}</font></s>" :
                (in_array($qnum, $this->_ng_nums) ? "<s>{$full}</s>" : "{$full}")) . "</a>";
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
    public function quoteResRange($full, $qsign, $from, $to, $anchor="")
    {
        global $_conf;

		if ($anchor) {
			$anchor="#r".$anchor;
		}
        if (!$from) {
            $from = 1;
        } elseif ($from < 1 || $from > $this->thread->rescount) {
            return $full;
        }
        // read.php�ŕ\���͈͂𔻒肷��̂ŏ璷�ł͂���
        if (!$to) {
            $to = min($from + $_conf['mobile.rnum_range'] - 1, $this->thread->rescount);
        } else {
            $to = min($to, $from + $_conf['mobile.rnum_range'] - 1, $this->thread->rescount);
        }

        $read_url = "{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;offline=1&amp;ls={$from}-{$to}";

        return "<a href=\"{$read_url}{$_conf['k_at_a']}{$anchor}\"{$this->target_at}>{$full}</a>";
    }

    // }}}
    // {{{ ktaiExtUrl()

    /**
     * �g�їp�O��URL�ϊ�
     *
     * @param   string  $full
     * @param   string  $url
     * @param   string  $str
     * @return  string
     */
    public function ktaiExtUrl($full, $url, $str)
    {
        global $_conf;

        // �ʋ΃u���E�U
        $tsukin_link = '';
        if ($_conf['mobile.use_tsukin']) {
            $tsukin_url = 'http://www.sjk.co.jp/c/w.exe?y=' . rawurlencode($url);
            if ($_conf['through_ime']) {
                $tsukin_url = P2Util::throughIme($tsukin_url);
            }
            $tsukin_link = '<a href="' . $tsukin_url . '">��</a>';
        }

        // jig�u���E�UWEB http://bwXXXX.jig.jp/fweb/?_jig_=
        $jig_link = '';
        /*
        $jig_url = 'http://bwXXXX.jig.jp/fweb/?_jig_=' . rawurlencode($url);
        if ($_conf['through_ime']) {
            $jig_url = P2Util::throughIme($jig_url);
        }
        $jig_link = '<a href="'.$jig_url.'">j</a>';
        */

        if ($tsukin_link || $jig_link) {
            $ext_pre = '(' . $tsukin_link . (($tsukin_link && $jig_link) ? '|' : '') . $jig_link . ')';
        } else {
            $ext_pre = '';
        }

        if ($_conf['through_ime']) {
            $url = P2Util::throughIme($url);
        }
        return $ext_pre . '<a href="' . $url . '">' . $str . '</a>';
    }

    // }}}
    // {{{ ktaiExtUrlCallback()

    /**
     * �g�їp�O��URL�ϊ�
     *
     * @param   array   $s  ���K�\���Ƀ}�b�`�����v�f�̔z��
     * @return  string
     */
    public function ktaiExtUrlCallback(array $s)
    {
        return $this->ktaiExtUrl($s[0], $s[1], $s[2]);
    }

    // }}}
    // {{{ transLinkDo()����Ăяo�����URL�����������\�b�h
    /**
     * �����̃��\�b�h�͈����������Ώۃp�^�[���ɍ��v���Ȃ���FALSE��Ԃ��A
     * transLinkDo()��FALSE���Ԃ��Ă����$_url_handlers�ɓo�^����Ă��鎟�̊֐�/���\�b�h�ɏ��������悤�Ƃ���B
     */
    // {{{ plugin_linkURL()

    /**
     * URL�����N
     */
    public function plugin_linkURL($url, $purl, $str)
    {
        global $_conf;

        if (isset($purl['scheme'])) {
            // �g�їp�O��URL�ϊ�
            if ($_conf['mobile.use_tsukin']) {
                return $this->ktaiExtUrl('', $purl[0], $str);
            }
            // ime
            if ($_conf['through_ime']) {
                $link_url = P2Util::throughIme($purl[0]);
            } else {
                $link_url = $url;
            }
            return "<a href=\"{$link_url}\">{$str}</a>";
        }
        return FALSE;
    }

    // }}}
    // {{{ plugin_link2chSubject()

    /**
     * 2ch bbspink �����N
     */
    public function plugin_link2chSubject($url, $purl, $str)
    {
        global $_conf;

        if (preg_match('{^http://(\\w+\\.(?:2ch\\.net|bbspink\\.com))/(\\w+)/$}', $purl[0], $m)) {
            $subject_url = "{$_conf['subject_php']}?host={$m[1]}&amp;bbs={$m[2]}";
            return "<a href=\"{$url}\">{$str}</a> [<a href=\"{$subject_url}{$_conf['k_at_a']}\">��p2�ŊJ��</a>]";
        }
        return FALSE;
    }

    // }}}
    // {{{ plugin_linkThread()

    /**
     * �X���b�h�����N
     */
    public function plugin_linkThread($url, $purl, $str)
    {
        global $_conf;

        list($nama_url, $host, $bbs, $key, $ls) = P2Util::detectThread($purl[0]);
        if ($host && $bbs && $key) {
            $read_url = "{$_conf['read_php']}?host={$host}&amp;bbs={$bbs}&amp;key={$key}&amp;ls={$ls}";
            return "<a href=\"{$read_url}{$_conf['k_at_a']}\">{$str}</a>";
        }

        return false;
    }

    // }}}
    // {{{ plugin_linkYouTube()

    /**
     * YouTube�����N�ϊ��v���O�C��
     *
     * Zend_Gdata_Youtube���g���΃T���l�C�����̑��̏����ȒP�Ɏ擾�ł��邪...
     *
     * @param   string $url
     * @param   array $purl
     * @param   string $str
     * @return  string|false
     */
    public function plugin_linkYouTube($url, $purl, $str)
    {
        global $_conf;

        // http://www.youtube.com/watch?v=Mn8tiFnAUAI
        if (preg_match('{^http://(www|jp)\\.youtube\\.com/watch\\?v=([0-9A-Za-z_\\-]+)}', $purl[0], $m)) {
            $subd = $m[1];
            $id = $m[2];

            if ($_conf['mobile.link_youtube'] == 2) {
                $link = $str;
            } else {
                $link = $this->plugin_linkURL($url, $purl, $str);
                if ($link === false) {
                    // plugin_linkURL()�������Ƌ@�\���Ă�����肱���ɂ͗��Ȃ�
                    if ($_conf['through_ime']) {
                        $link_url = P2Util::throughIme($purl[0]);
                    } else {
                        $link_url = $url;
                    }
                    $link = "<a href=\"{$link_url}\">{$str}</a>";
                }
            }

            return <<<EOP
{$link}<br><img src="http://img.youtube.com/vi/{$id}/default.jpg" alt="YouTube {$id}">
EOP;
        }
        return FALSE;
    }

    // }}}
    // {{{ plugin_viewImage()

    /**
     * �摜�����N�ϊ�
     */
    public function plugin_viewImage($url, $purl, $str)
    {
        global $_conf;

        if (P2Util::isUrlWikipediaJa($url)) {
            return false;
        }

        if (preg_match('{^https?://.+?\\.(jpe?g|gif|png)$}i', $url) && empty($purl['query'])) {
            $picto_url = 'http://pic.to/'.$purl['host'].$purl['path'];
            $picto_tag = '<a href="'.$picto_url.'">(��)</a> ';
            if ($_conf['through_ime']) {
                $link_url  = P2Util::throughIme($purl[0]);
                $picto_url = P2Util::throughIme($picto_url);
            } else {
                $link_url = $url;
            }
            return "{$picto_tag}<a href=\"{$link_url}\">{$str}</a>";
        }

        return false;
    }

    // }}}
    // {{{ plugin_imageCache2()

    /**
     * �摜URL��ImageCache2�ϊ�
     */
    public function plugin_imageCache2($url, $purl, $str)
    {
        global $_conf;
        global $pre_thumb_unlimited, $pre_thumb_ignore_limit, $pre_thumb_limit_k;

        if (P2Util::isUrlWikipediaJa($url)) {
            return false;
        }

        if (preg_match('{^https?://.+?\\.(jpe?g|gif|png)$}i', $purl[0]) && empty($purl['query'])) {
            // �C�����C���v���r���[�̗L������
            if ($pre_thumb_unlimited || $pre_thumb_ignore_limit || $pre_thumb_limit_k > 0) {
                $inline_preview_flag = true;
                $inline_preview_done = false;
            } else {
                $inline_preview_flag = false;
                $inline_preview_done = false;
            }

            $url_ht = $url;
            $url = $purl[0];
            $url_en = rawurlencode($url);
            $img_str = null;
            $img_id = null;

            $icdb = new IC2_DataObject_Images;

            // r=0:�����N;r=1:���_�C���N�g;r=2:PHP�ŕ\��
            // t=0:�I���W�i��;t=1:PC�p�T���l�C��;t=2:�g�їp�T���l�C��;t=3:���ԃC���[�W
            $img_url = 'ic2.php?r=0&amp;t=2&amp;uri=' . $url_en;
            $img_url2 = 'ic2.php?r=0&amp;t=2&amp;id=';
            $src_url = 'ic2.php?r=1&amp;t=0&amp;uri=' . $url_en;
            $src_url2 = 'ic2.php?r=1&amp;t=0&amp;id=';
            $src_exists = false;

            // ���C�ɃX�������摜�����N
            $rank = null;
            if ($_conf['expack.ic2.fav_auto_rank']) {
                $rank = $this->getAutoFavRank();
            }

            // DB�ɉ摜��񂪓o�^����Ă����Ƃ�
            if ($icdb->get($url)) {
                $img_id = $icdb->id;

                // �E�B���X�Ɋ������Ă����t�@�C���̂Ƃ�
                if ($icdb->mime == 'clamscan/infected') {
                    return '[IC2:�E�B���X�x��]';
                }
                // ���ځ[��摜�̂Ƃ�
                if ($icdb->rank < 0) {
                    return '[IC2:���ځ[��摜]';
                }

                // �I���W�i���̗L�����m�F
                $_src_url = $this->thumbnailer->srcPath($icdb->size, $icdb->md5, $icdb->mime);
                if (file_exists($_src_url)) {
                    $src_exists = true;
                    $img_url = $img_url2 . $icdb->id;
                    $src_url = $_src_url;
                } else {
                    $img_url = $this->thumbnailer->thumbPath($icdb->size, $icdb->md5, $icdb->mime);
                    $src_url = $src_url2 . $icdb->id;
                }

                // �C�����C���v���r���[���L���̂Ƃ�
                $prv_url = null;
                if ($this->thumbnailer->ini['General']['inline'] == 1) {
                    // PC��read_new_k.php�ɃA�N�Z�X�����Ƃ���
                    if (!isset($this->inline_prvw) || !is_object($this->inline_prvw)) {
                        $this->inline_prvw = $this->thumbnailer;
                    }
                    $prv_url = $this->inline_prvw->thumbPath($icdb->size, $icdb->md5, $icdb->mime);

                    // �T���l�C���\���������ȓ��̂Ƃ�
                    if ($inline_preview_flag) {
                        // �v���r���[�摜������Ă��邩�ǂ�����img�v�f�̑���������
                        if (file_exists($prv_url)) {
                            $prv_size = explode('x', $this->inline_prvw->calc($icdb->width, $icdb->height));
                            $img_str = "<img src=\"{$prv_url}\" width=\"{$prv_size[0]}\" height=\"{$prv_size[1]}\">";
                        } else {
                            $r_type = ($this->thumbnailer->ini['General']['redirect'] == 1) ? 1 : 2;
                            if ($src_exists) {
                                $prv_url = "ic2.php?r={$r_type}&amp;t=1&amp;id={$icdb->id}";
                            } else {
                                $prv_url = "ic2.php?r={$r_type}&amp;t=1&amp;uri={$url_en}";
                            }
                            $img_str = "<img src=\"{$prv_url}\">";
                        }
                        $inline_preview_done = true;
                    } else {
                        $img_str = '[p2:�����摜(�ݸ:' . $icdb->rank . ')]';
                    }
                }

                // �����X���^�C�����@�\��ON�ŃX���^�C���L�^����Ă��Ȃ��Ƃ���DB���X�V
                if (!is_null($this->img_memo) && strpos($icdb->memo, $this->img_memo) === false){
                    $update = new IC2_DataObject_Images;
                    if (!is_null($icdb->memo) && strlen($icdb->memo) > 0) {
                        $update->memo = $this->img_memo . ' ' . $icdb->memo;
                    } else {
                        $update->memo = $this->img_memo;
                    }
                    $update->whereAddQuoted('uri', '=', $url);
                }

                // expack.ic2.fav_auto_rank_override �̐ݒ�ƃ����N������OK�Ȃ�
                // ���C�ɃX�������摜�����N���㏑���X�V
                if ($rank !== null &&
                        self::isAutoFavRankOverride($icdb->rank, $rank)) {
                    if ($update === null) {
                        $update = new IC2_DataObject_Images;
                        $update->whereAddQuoted('uri', '=', $url);
                    }
                    $update->rank = $rank;

                }
                if ($update !== null) {
                    $update->update();
                }

            // �摜���L���b�V������Ă��Ȃ��Ƃ�
            // �����X���^�C�����@�\��ON�Ȃ�N�G����UTF-8�G���R�[�h�����X���^�C���܂߂�
            } else {
                // �摜���u���b�N���X�gor�G���[���O�ɂ��邩�m�F
                if (false !== ($errcode = $icdb->ic2_isError($url))) {
                    return "<s>[IC2:�װ({$errcode})]</s>";
                }

                // �C�����C���v���r���[���L���ŁA�T���l�C���\���������ȓ��Ȃ�
                if ($this->thumbnailer->ini['General']['inline'] == 1 && $inline_preview_flag) {
                    $img_str = "<img src=\"ic2.php?r=2&amp;t=1&amp;uri={$url_en}{$this->img_memo_query}\">";
                    $inline_preview_done = true;
                } else {
                    $img_url .= $this->img_memo_query;
                }
            }

            // �\�����������f�N�������g
            if ($inline_preview_flag && $inline_preview_done) {
                $pre_thumb_limit_k--;
            }

            if (!empty($_SERVER['REQUEST_URI'])) {
                $backto = '&amp;from=' . rawurlencode($_SERVER['REQUEST_URI']);
            } else {
                $backto = '';
            }

            if (is_null($img_str)) {
                return sprintf('<a href="%s%s">[IC2:%s:%s]</a>',
                               $img_url,
                               $backto,
                               htmlspecialchars($purl['host'], ENT_QUOTES),
                               htmlspecialchars(basename($purl['path']), ENT_QUOTES)
                               );
            }

            if ($_conf['iphone']) {
                $img_title = htmlspecialchars($purl['host'], ENT_QUOTES)
                           . '&#10;'
                           . htmlspecialchars(basename($purl['path']), ENT_QUOTES);
                return "<a class=\"limelight\" href=\"{$src_url}\" title=\"{$img_title}\" target=\"_blank\">{$img_str}</a>"
                   //. ' <img class="ic2-show-info" src="img/s2a.png" width="16" height="16" onclick="ic2info.show('
                     . ' <input type="button" class="ic2-show-info" value="i" onclick="ic2info.show('
                     . (($img_id) ? $img_id : "'{$url_ht}'") . ', event)">';
            } else {
                return "<a href=\"{$img_url}{$backto}\">{$img_str}</a>";
            }
        }

        return false;
    }

    function plugin_replaceImageURL($url, $purl, $str)
    {
        global $_conf;
        global $pre_thumb_unlimited, $pre_thumb_ignore_limit, $pre_thumb_limit_k;

        if (P2Util::isUrlWikipediaJa($url)) {
            return false;
        }

        // if (preg_match('{^https?://.+?\\.(jpe?g|gif|png)$}i', $url) && empty($purl['query'])) {
        // +Wiki
        global $replaceimageurl;
        $url = $purl[0];
        $replaced = $replaceimageurl->replaceImageURL($url);
        if (!$replaced[0]) return FALSE;
        foreach($replaced as $v) {
            // �C�����C���v���r���[�̗L������
            if ($pre_thumb_unlimited || $pre_thumb_ignore_limit || $pre_thumb_limit_k > 0) {
                $inline_preview_flag = true;
                $inline_preview_done = false;
            } else {
                $inline_preview_flag = false;
                $inline_preview_done = false;
            }

            // +Wiki
            // $url_en = rawurlencode($url);
            $url_ht = $url;
            $url_en = rawurlencode($v['url']);
            $ref_en = $v['referer'] ? '&amp;ref=' . rawurlencode($v['referer']) : '';
            $img_str = null;
            $img_id = null;

            $icdb = new IC2_DataObject_Images;

            // r=0:�����N;r=1:���_�C���N�g;r=2:PHP�ŕ\��
            // t=0:�I���W�i��;t=1:PC�p�T���l�C��;t=2:�g�їp�T���l�C��;t=3:���ԃC���[�W
            $img_url = 'ic2.php?r=0&amp;t=2&amp;uri=' . $url_en . $ref_en;
            $img_url2 = 'ic2.php?r=0&amp;t=2&amp;id=';
            $src_url = 'ic2.php?r=1&amp;t=0&amp;uri=' . $url_en . $ref_en;
            $src_url2 = 'ic2.php?r=1&amp;t=0&amp;id=';
            $src_exists = false;

            // ���C�ɃX�������摜�����N
            $rank = null;
            if ($_conf['expack.ic2.fav_auto_rank']) {
                $rank = $this->getAutoFavRank();
            }

            // DB�ɉ摜��񂪓o�^����Ă����Ƃ�
            if ($icdb->get($v['url'])) {
                $img_id = $icdb->id;

                // �E�B���X�Ɋ������Ă����t�@�C���̂Ƃ�
                if ($icdb->mime == 'clamscan/infected') {
                    return '[IC2:�E�B���X�x��]';
                }
                // ���ځ[��摜�̂Ƃ�
                if ($icdb->rank < 0) {
                    return '[IC2:���ځ[��摜]';
                }

                // �I���W�i���̗L�����m�F
                $_src_url = $this->thumbnailer->srcPath($icdb->size, $icdb->md5, $icdb->mime);
                if (file_exists($_src_url)) {
                    $src_exists = true;
                    $img_url = $img_url2 . $icdb->id;
                    $src_url = $_src_url;
                } else {
                    $img_url = $this->thumbnailer->thumbPath($icdb->size, $icdb->md5, $icdb->mime);
                    $src_url = $src_url2 . $icdb->id;
                }

                // �C�����C���v���r���[���L���̂Ƃ�
                $prv_url = null;
                if ($this->thumbnailer->ini['General']['inline'] == 1) {
                    // PC��read_new_k.php�ɃA�N�Z�X�����Ƃ���
                    if (!isset($this->inline_prvw) || !is_object($this->inline_prvw)) {
                        $this->inline_prvw = $this->thumbnailer;
                    }
                    $prv_url = $this->inline_prvw->thumbPath($icdb->size, $icdb->md5, $icdb->mime);

                    // �T���l�C���\���������ȓ��̂Ƃ�
                    if ($inline_preview_flag) {
                        // �v���r���[�摜������Ă��邩�ǂ�����img�v�f�̑���������
                        if (file_exists($prv_url)) {
                            $prvw_size = explode('x', $this->inline_prvw->calc($icdb->width, $icdb->height));
                            $img_str = "<img src=\"{$prv_url}\" width=\"{$prvw_size[0]}\" height=\"{$prvw_size[1]}\">";
                        } else {
                            $r_type = ($this->thumbnailer->ini['General']['redirect'] == 1) ? 1 : 2;
                            if ($src_exists) {
                                $prv_url = "ic2.php?r={$r_type}&amp;t=1&amp;id={$icdb->id}";
                            } else {
                                $prv_url = "ic2.php?r={$r_type}&amp;t=1&amp;uri={$url_en}";
                            }
                            $img_str = "<img src=\"{$prv_url}{$_conf['sid_at_a']}\">";
                        }
                        $inline_preview_done = true;
                    } else {
                        $img_str = '[p2:�����摜(�ݸ:' . $icdb->rank . ')]';
                    }
                }

                // �����X���^�C�����@�\��ON�ŃX���^�C���L�^����Ă��Ȃ��Ƃ���DB���X�V
                if (!is_null($this->img_memo) && strpos($icdb->memo, $this->img_memo) === false){
                    $update = new IC2_DataObject_Images;
                    if (!is_null($icdb->memo) && strlen($icdb->memo) > 0) {
                        $update->memo = $this->img_memo . ' ' . $icdb->memo;
                    } else {
                        $update->memo = $this->img_memo;
                    }
                    $update->whereAddQuoted('uri', '=', $v['url']);
                }

                // expack.ic2.fav_auto_rank_override �̐ݒ�ƃ����N������OK�Ȃ�
                // ���C�ɃX�������摜�����N���㏑���X�V
                if ($rank !== null &&
                        self::isAutoFavRankOverride($icdb->rank, $rank)) {
                    if ($update === null) {
                        $update = new IC2_DataObject_Images;
                        $update->whereAddQuoted('uri', '=', $v['url']);
                    }
                    $update->rank = $rank;

                }
                if ($update !== null) {
                    $update->update();
                }

            // �摜���L���b�V������Ă��Ȃ��Ƃ�
            // �����X���^�C�����@�\��ON�Ȃ�N�G����UTF-8�G���R�[�h�����X���^�C���܂߂�
            } else {
                // �摜���u���b�N���X�gor�G���[���O�ɂ��邩�m�F
                if (false !== ($errcode = $icdb->ic2_isError($v['url']))) {
                    return "<s>[IC2:�װ({$errcode})]</s>";
                }

                // �C�����C���v���r���[���L���ŁA�T���l�C���\���������ȓ��Ȃ�
                if ($this->thumbnailer->ini['General']['inline'] == 1 && $inline_preview_flag) {
                    $rank_str = ($rank !== null) ? '&rank=' . $rank : '';
                    $img_str = "<img src=\"ic2.php?r=2&amp;t=1&amp;uri={$url_en}{$this->img_memo_query}{$_conf['sid_at_a']}{$rank_str}{$ref_en}\">";
                    $inline_preview_done = true;
                } else {
                    $img_url .= $this->img_memo_query;
                }
            }

            // �\�����������f�N�������g
            if ($inline_preview_flag && $inline_preview_done) {
                $pre_thumb_limit_k--;
            }

            if (!empty($_SERVER['REQUEST_URI'])) {
                $backto = '&amp;from=' . rawurlencode($_SERVER['REQUEST_URI']);
            } else {
                $backto = '';
            }

            if (is_null($img_str)) {
                $result .= sprintf('<a href="%s%s">[IC2:%s:%s]</a>',
                               $img_url,
                               $backto,
                               htmlspecialchars($purl['host'], ENT_QUOTES),
                               htmlspecialchars(basename($purl['path']), ENT_QUOTES)
                               );
            }

            if ($_conf['iphone']) {
                $img_title = htmlspecialchars($purl['host'], ENT_QUOTES)
                           . '&#10;'
                           . htmlspecialchars(basename($purl['path']), ENT_QUOTES);
                $result .= "<a class=\"limelight\" href=\"{$src_url}\" title=\"{$img_title}\" target=\"_blank\">{$img_str}</a>"
                   //. ' <img class="ic2-show-info" src="img/s2a.png" width="16" height="16" onclick="ic2info.show('
                     . ' <input type="button" class="ic2-show-info" value="i" onclick="ic2info.show('
                     . (($img_id) ? $img_id : "'{$v['url']}'") . ', event)">';
            } else {
                $result .= "<a href=\"{$img_url}{$backto}\">{$img_str}</a>";
            }
        }
        $result .= $this->plugin_linkURL($url, $purl, $str);
        return $result;
    }

    // }}}
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
