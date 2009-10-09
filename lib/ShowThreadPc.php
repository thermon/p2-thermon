<?php
/**
 * rep2 - �X���b�h��\������ �N���X PC�p
 */

require_once P2_LIB_DIR . '/ShowThread.php';
require_once P2_LIB_DIR . '/StrCtl.php';
require_once P2EX_LIB_DIR . '/ExpackLoader.php';

ExpackLoader::loadAAS();
ExpackLoader::loadActiveMona();
ExpackLoader::loadImageCache();

// {{{ ShowThreadPc

class ShowThreadPc extends ShowThread
{
    // {{{ properties

    static private $_spm_objects = array();

    private $_quote_res_nums_checked; // �|�b�v�A�b�v�\�������`�F�b�N�ς݃��X�ԍ���o�^�����z��
    private $_quote_res_nums_done; // �|�b�v�A�b�v�\�������L�^�ς݃��X�ԍ���o�^�����z��
    private $_quote_check_depth; // ���X�ԍ��`�F�b�N�̍ċA�̐[�� checkQuoteResNums()

    private $_ids_for_render;   // �o�͗\���ID(�d���̂�)�̃��X�g(8��)
    private $_idcount_average;  // ID�d�����̕��ϒl
    private $_idcount_tops;     // ID�d�����̃g�b�v���܂܂ł̏d�����l

    public $am_autodetect = false; // AA������������邩�ۂ�
    public $am_side_of_id = false; // AA�X�C�b�`��ID�̉��ɕ\������
    public $am_on_spm = false; // AA�X�C�b�`��SPM�ɕ\������

    public $asyncObjName;  // �񓯊��ǂݍ��ݗpJavaScript�I�u�W�F�N�g��
    public $spmObjName; // �X�}�[�g�|�b�v�A�b�v���j���[�pJavaScript�I�u�W�F�N�g��

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     */
    public function __construct($aThread, $matome = false)
    {
        parent::__construct($aThread, $matome);

        global $_conf;

        $this->_url_handlers = array(
            'plugin_linkThread',
            'plugin_link2chSubject',
        );
        // +Wiki
        if (isset($GLOBALS['linkplugin']))      $this->_url_handlers[] = 'plugin_linkPlugin';
        if (isset($GLOBALS['replaceimageurl'])) $this->_url_handlers[] = 'plugin_replaceImageURL';
        if (P2_IMAGECACHE_AVAILABLE == 2) {
            $this->_url_handlers[] = 'plugin_imageCache2';
        } elseif ($_conf['preview_thumbnail']) {
            $this->_url_handlers[] = 'plugin_viewImage';
        }
        if ($_conf['link_youtube']) {
            $this->_url_handlers[] = 'plugin_linkYouTube';
        }
        if ($_conf['link_niconico']) {
            $this->_url_handlers[] = 'plugin_linkNicoNico';
        }
        $this->_url_handlers[] = 'plugin_linkURL';

        // imepita��URL�����H����ImageCache2������v���O�C����o�^
        if (P2_IMAGECACHE_AVAILABLE == 2) {
            $this->addURLHandler(array($this, 'plugin_imepita_to_imageCache2'));
        }

        // �T���l�C���\����������ݒ�
        if (!isset($GLOBALS['pre_thumb_unlimited']) || !isset($GLOBALS['pre_thumb_limit'])) {
            if (isset($_conf['pre_thumb_limit']) && $_conf['pre_thumb_limit'] > 0) {
                $GLOBALS['pre_thumb_limit'] = $_conf['pre_thumb_limit'];
                $GLOBALS['pre_thumb_unlimited'] = FALSE;
            } else {
                $GLOBALS['pre_thumb_limit'] = NULL; // �k���l����isset()��FALSE��Ԃ�
                $GLOBALS['pre_thumb_unlimited'] = TRUE;
            }
        }
        $GLOBALS['pre_thumb_ignore_limit'] = FALSE;

        // �A�N�e�B�u���i�[������
        if (P2_ACTIVEMONA_AVAILABLE) {
            ExpackLoader::initActiveMona($this);
        }

        // ImageCache2������
        if (P2_IMAGECACHE_AVAILABLE == 2) {
            ExpackLoader::initImageCache($this);
        }

        // �񓯊����X�|�b�v�A�b�v�ESPM������
        $js_id = sprintf('%u', crc32($this->thread->keydat));
        if ($this->_matome) {
            $this->asyncObjName = "t{$this->_matome}asp{$js_id}";
            $this->spmObjName = "t{$this->_matome}spm{$js_id}";
        } else {
            $this->asyncObjName = "asp{$js_id}";
            $this->spmObjName = "spm{$js_id}";
        }

        // ������������
        $this->setBbsNonameName();
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
            $idstr = 'ID:' . $id;
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
        $rpop = '';
        if ($this->_matome) {
            $res_id = "t{$this->_matome}r{$i}";
            $msg_id = "t{$this->_matome}m{$i}";
        } else {
            $res_id = "r{$i}";
            $msg_id = "m{$i}";
        }
        $msg_class = 'message';

        // NG���ځ[��`�F�b�N
        $ng_type = $this->_ngAbornCheck($i, strip_tags($name), $mail, $date_id, $id, $msg, false, $ng_info);
        if ($ng_type == self::ABORN) {
            return $this->_abornedRes($res_id);
        }
        if ($ng_type != self::NG_NONE) {
            $ngaborns_head_hits = self::$_ngaborns_head_hits;
            $ngaborns_body_hits = self::$_ngaborns_body_hits;
        }

        // AA����
        if ($this->am_autodetect && $this->activeMona->detectAA($msg)) {
            $msg_class .= ' ActiveMona';
        }

        //=============================================================
        // ���X���|�b�v�A�b�v�\��
        //=============================================================
        if ($_conf['quote_res_view']) {
            $this->_quote_check_depth = 0;

            $quote_res_nums = $this->checkQuoteResNums($i, $name, $msg);

            foreach ($quote_res_nums as $rnv) {
                if (!isset($this->_quote_res_nums_done[$rnv])) {
                    $this->_quote_res_nums_done[$rnv] = true;
                    if (isset($this->thread->datlines[$rnv-1])) {
                        if ($this->_matome) {
                            $qres_id = "t{$this->_matome}qr{$rnv}";
                        } else {
                            $qres_id = "qr{$rnv}";
                        }
                        $ds = $this->qRes($this->thread->datlines[$rnv-1], $rnv);
                        $onPopUp_at = " onmouseover=\"showResPopUp('{$qres_id}',event)\" onmouseout=\"hideResPopUp('{$qres_id}')\"";
                        $rpop .= "<div id=\"{$qres_id}\" class=\"respopup\"{$onPopUp_at}>\n{$ds}</div>\n";
                    }
                }
            }
        }

        //=============================================================
        // �܂Ƃ߂ďo��
        //=============================================================

        $name = $this->transName($name); // ���OHTML�ϊ�
        $msg = $this->transMsg($msg, $i); // ���b�Z�[�WHTML�ϊ�


        // BE�v���t�@�C�������N�ϊ�
        $date_id = $this->replaceBeId($date_id, $i);

        // HTML�|�b�v�A�b�v
        if ($_conf['iframe_popup']) {
            $date_id = preg_replace_callback("{<a href=\"(http://[-_.!~*()0-9A-Za-z;/?:@&=+\$,%#]+)\"({$_conf['ext_win_target_at']})>((\?#*)|(Lv\.\d+))</a>}", array($this, 'iframePopupCallback'), $date_id);
        }

        // NG���b�Z�[�W�ϊ�
        if ($ng_type != self::NG_NONE && count($ng_info)) {
            $ng_info = implode(', ', $ng_info);
            $msg = <<<EOMSG
<span class="ngword" onclick="show_ng_message('ngm{$ngaborns_body_hits}', this);">{$ng_info}</span>
<div id="ngm{$ngaborns_body_hits}" class="ngmsg ngmsg-by-msg">{$msg}</div>
EOMSG;
        }

        // NG�l�[���ϊ�
        if ($ng_type & self::NG_NAME) {
            $name = <<<EONAME
<span class="ngword" onclick="show_ng_message('ngn{$ngaborns_head_hits}', this);">{$name}</span>
EONAME;
            $msg = <<<EOMSG
<div id="ngn{$ngaborns_head_hits}" class="ngmsg ngmsg-by-name">{$msg}</div>
EOMSG;

        // NG���[���ϊ�
        } elseif ($ng_type & self::NG_MAIL) {
            $mail = <<<EOMAIL
<span class="ngword" onclick="show_ng_message('ngn{$ngaborns_head_hits}', this);">{$mail}</span>
EOMAIL;
            $msg = <<<EOMSG
<div id="ngn{$ngaborns_head_hits}" class="ngmsg ngmsg-by-mail">{$msg}</div>
EOMSG;

        // NGID�ϊ�
        } elseif ($ng_type & self::NG_ID) {
            $date_id = <<<EOID
<span class="ngword" onclick="show_ng_message('ngn{$ngaborns_head_hits}', this);">{$date_id}</span>
EOID;
            $msg = <<<EOMSG
<div id="ngn{$ngaborns_head_hits}" class="ngmsg ngmsg-by-id">{$msg}</div>
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

        // SPM
        if ($_conf['expack.spm.enabled']) {
            $spmeh = " onmouseover=\"{$this->spmObjName}.show({$i},'{$msg_id}',event)\"";
            $spmeh .= " onmouseout=\"{$this->spmObjName}.hide(event)\"";
        } else {
            $spmeh = '';
        }

        $tores .= "<div id=\"{$res_id}\" class=\"res\">\n";
        $tores .= "<div class=\"res-header\">";
/*        $read_url = "{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;offline=1&amp;ls={$i}";
        $i_with_link="<a href=\"{$read_url}\">{$i}</a>";*/
        $i_with_link=$i;
        if ($this->thread->onthefly) {
            $GLOBALS['newres_to_show_flag'] = true;
            //�ԍ��i�I���U�t���C���j
            $tores .= "<span class=\"ontheflyresorder spmSW\"{$spmeh}>{$i_with_link}</span> : ";
        } elseif ($i_with_link > $this->thread->readnum) {
            $GLOBALS['newres_to_show_flag'] = true;
            // �ԍ��i�V�����X���j
            $tores .= "<span style=\"color:{$STYLE['read_newres_color']}\" class=\"spmSW\"{$spmeh}>{$i_with_link}</span> : ";
        } elseif ($_conf['expack.spm.enabled']) {
            // �ԍ��iSPM�j
            $tores .= "<span class=\"spmSW\"{$spmeh}>{$i_with_link}</span> : ";
        } else {
            // �ԍ�
            $tores .= "{$i_with_link} : ";
        }
        // ���O
        $tores .= preg_replace('{<b>[ ]*</b>}i', '', "<span class=\"name\"><b>{$name}</b></span> : ");

        // ���[��
        if ($mail) {
            if (strpos($mail, 'sage') !== false && $STYLE['read_mail_sage_color']) {
                $tores .= "<span class=\"sage\">{$mail}</span> : ";
            } elseif ($STYLE['read_mail_color']) {
                $tores .= "<span class=\"mail\">{$mail}</span> : ";
            } else {
                $tores .= $mail . ' : ';
            }
        }

        // ID�t�B���^
        if ($_conf['flex_idpopup'] == 1 && $id && $this->thread->idcount[$id] > 1) {
            $date_id = str_replace($idstr, $this->idFilter($idstr, $id), $date_id);
        }

        $tores .= $date_id; // ���t��ID
        if ($this->am_side_of_id) {
            $tores .= ' ' . $this->activeMona->getMona($msg_id);
        }
        $tores .= "</div>\n"; // res-header�����

        // �탌�X���X�g(�c�`��)
        if ($_conf['backlink_list'] == 1) {
            $tores .= $this->quoteback_list_html($i, 1);
        }

        $tores .= "<div id=\"{$msg_id}\" class=\"{$msg_class}\">{$msg}</div>\n"; // ���e
        // �탌�X���X�g(���`��)
        if ($_conf['backlink_list'] == 2) {
            $tores .= $this->quoteback_list_html($i, 2,false);
        }
        $tores .= "<p>";
        $tores .= "</div>\n";

//        $tores .= $rpop; // ���X�|�b�v�A�b�v�p���p
        /*if ($_conf['expack.am.enabled'] == 2) {
            $tores .= <<<EOJS
<script type="text/javascript">
//<![CDATA[
detectAA("{$msg_id}");
//]]>
</script>\n
EOJS;
        }*/

        // �܂Ƃ߂ăt�B���^�F����
        if (!empty($GLOBALS['word_fm']) && $res_filter['match'] != 'off') {
            $tores = StrCtl::filterMarking($GLOBALS['word_fm'], $tores);
        }

        return array('body' => $tores, 'q' => $rpop);
    }

    // }}}
    // {{{ quoteOne()

    /**
     * >>1 ��\������ (���p�|�b�v�A�b�v�p)
     */
    public function quoteOne()
    {
        global $_conf;

        if (!$_conf['quote_res_view']) {
            return false;
        }

        $rpop = '';
        $this->_quote_check_depth = 0;
        $quote_res_nums = $this->checkQuoteResNums(0, '1', '');

        foreach ($quote_res_nums as $rnv) {
            if (!isset($this->_quote_res_nums_done[$rnv])) {
                $this->_quote_res_nums_done[$rnv] = true;
                if (isset($this->thread->datlines[$rnv-1])) {
                    if ($this->_matome) {
                        $qres_id = "t{$this->_matome}qr{$rnv}";
                    } else {
                        $qres_id = "qr{$rnv}";
                    }
                    $ds = $this->qRes($this->thread->datlines[$rnv-1], $rnv);
                    $onPopUp_at = "onmouseover=\"showResPopUp('{$qres_id}',event)\" onmouseout=\"hideResPopUp('{$qres_id}')\"";
                    $rpop .= "<div id=\"{$qres_id}\" class=\"respopup\"{$onPopUp_at}>\n{$ds}</div>\n";
                }
            }
        }

        $res1['q'] = $rpop;
        $res1['body'] = $this->transMsg('&gt;&gt;1', 1);

        return $res1;
    }

    // }}}
    // {{{ qRes()

    /**
     * ���X���pHTML
     */
    public function qRes($ares, $i)
    {
        global $_conf;

        $resar = $this->thread->explodeDatLine($ares);
        $name = $this->transName($resar[0]);
        $mail = $resar[1];
        if (($id = $this->thread->ids[$i]) !== null) {
            $idstr = 'ID:' . $id;
            $date_id = str_replace($this->thread->idp[$i] . $id, $idstr, $resar[2]);
        } else {
            $idstr = null;
            $date_id = $resar[2];
        }
        $msg = $this->transMsg($resar[3], $i);

        $tores = '';

        if ($this->_matome) {
            $qmsg_id = "t{$this->_matome}qm{$i}";
        } else {
            $qmsg_id = "qm{$i}";
        }

        // >>1
        if ($i == 1) {
            $tores = "<h4 class=\"thread_title\">{$this->thread->ttitle_hd}</h4>";
        }

        // BE�v���t�@�C�������N�ϊ�
        $date_id = $this->replaceBeId($date_id, $i);

        // HTML�|�b�v�A�b�v
        if ($_conf['iframe_popup']) {
            $date_id = preg_replace_callback("{<a href=\"(http://[-_.!~*()0-9A-Za-z;/?:@&=+\$,%#]+)\"({$_conf['ext_win_target_at']})>((\?#*)|(Lv\.\d+))</a>}", array($this, 'iframePopupCallback'), $date_id);
        }
        //

        // ID�t�B���^
        if ($_conf['flex_idpopup'] == 1 && $id && $this->thread->idcount[$id] > 1) {
            $date_id = str_replace($idstr, $this->idFilter($idstr, $id), $date_id);
        }

        $msg_class = 'message';

        // AA ����
        if ($this->am_autodetect && $this->activeMona->detectAA($msg)) {
            $msg_class .= ' ActiveMona';
        }

        // SPM
        if ($_conf['expack.spm.enabled']) {
            $spmeh = " onmouseover=\"{$this->spmObjName}.show({$i},'{$qmsg_id}',event)\"";
            $spmeh .= " onmouseout=\"{$this->spmObjName}.hide(event)\"";
        } else {
            $spmeh = '';
        }

        // $tores�ɂ܂Ƃ߂ďo��
/*        $read_url = "{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;offline=1&amp;ls={$i}";
        $i_with_link="<a href=\"{$read_url}\">{$i}</a>";*/
        $i_with_link=$i;
        $tores .= '<div class="res-header">';
        $tores .= "<span class=\"spmSW\"{$spmeh}>{$i_with_link}</span> : "; // �ԍ�
        $tores .= preg_replace('{<b>[ ]*</b>}i', '', "<b>{$name}</b> : ");
        if ($mail) {
            $tores .= $mail . ' : '; // ���[��
        }
        $tores .= $date_id; // ���t��ID
        if ($this->am_side_of_id) {
            $tores .= ' ' . $this->activeMona->getMona($qmsg_id);
        }
        $tores .= "</div>\n";

        // �탌�X���X�g(�c�`��)
        if ($_conf['backlink_list'] == 1) {
            $tores .= $this->quoteback_list_html($i, 1);
        }

        $tores .= "<div id=\"{$qmsg_id}\" class=\"{$msg_class}\">{$msg}</div>\n"; // ���e
        // �탌�X���X�g(���`��)
        if ($_conf['backlink_list'] == 2) {
            $tores .= $this->quoteback_list_html($i, 2);
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
        global $_conf;

        // �g���b�v��z�X�g�t���Ȃ番������
        if (($pos = strpos($name, '��')) !== false) {
            $trip = substr($name, $pos);
            $name = substr($name, 0, $pos);
        } else {
            $trip = null;
        }

        // ���������p���X�|�b�v�A�b�v�����N��
        if ($_conf['quote_res_view']) {
            if (strlen($name) && $name != $this->BBS_NONAME_NAME) {
                $name = preg_replace_callback(
                    $this->getAnchorRegex('/(?P<quote>(%prefix%)?%nums%(?(1)%suffix%|%line_suffix%))/'),
                    array($this, 'quote_name_callback'), $name
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
        global $pre_thumb_ignore_limit;

        // 2ch���`����dat
        if ($this->thread->dat_type == '2ch_old') {
            $msg = str_replace('���M', ',', $msg);
            $msg = preg_replace('/&amp(?=[^;])/', '&', $msg);
        }

        // &�␳
        $msg = preg_replace('/&(?!#?\\w+;)/', '&amp;', $msg);

        // Safari���瓊�e���ꂽ�����N���`���_�̕��������␳
        //$msg = preg_replace('{(h?t?tp://[\w\.\-]+/)�`([\w\.\-%]+/?)}', '$1~$2', $msg);

        // >>1�̃����N����������O��
        // <a href="../test/read.cgi/accuse/1001506967/1" target="_blank">&gt;&gt;1</a>
        $msg = preg_replace('{<[Aa] .+?>(&gt;&gt;\\d[\\d\\-]*)</[Aa]>}', '$1', $msg);

        // �{����2ch��DAT���_�łȂ���Ă��Ȃ��ƃG�X�P�[�v�̐����������Ȃ��C������B�iURL�����N�̃}�b�`�ŕ���p���o�Ă��܂��j
        //$msg = str_replace(array('"', "'"), array('&quot;', '&#039;'), $msg);

        // 2006/05/06 �m�[�g���̌딽���΍� body onload=window()
        $msg = str_replace('onload=window()', '<i>onload=window</i>()', $msg);

        // �V�����X�̉摜�͕\�������𖳎�����ݒ�Ȃ�
        if ($mynum > $this->thread->readnum && $_conf['expack.ic2.newres_ignore_limit']) {
            $pre_thumb_ignore_limit = TRUE;
        }

        // �����̉��s�ƘA��������s������
        if ($_conf['strip_linebreaks']) {
            $msg = $this->stripLineBreaks($msg /*, ' <br><span class="stripped">***</span><br> '*/);
        }

        // ���p��URL�Ȃǂ������N
        $msg = $this->transLink($msg);

        // Wikipedia�L�@�ւ̎��������N
        if ($_conf['link_wikipedia']) {
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
        return <<<EOP
<div id="{$res_id}" class="res aborned">
<hr style="display:block;width:95%;height:1em" >
</div>\n
EOP;
    }

    // }}}
    // {{{ idFilter()

    /**
     * ID�t�B���^�����O�|�b�v�A�b�v�ϊ�
     *
     * @param   string  $idstr  ID:xxxxxxxxxx
     * @param   string  $id        xxxxxxxxxx
     * @return  string
     */
    public function idFilter($idstr, $id)
    {
        global $_conf;

        // ID��8���܂���10��(+�g��/PC���ʎq)�Ɖ��肵��
        /*
        if (strlen($id) % 2 == 1) {
            $id = substr($id, 0, -1);
        }
        */
        $num_ht = '';
        if (isset($this->thread->idcount[$id]) && $this->thread->idcount[$id] > 0) {
            $num = (string) $this->thread->idcount[$id];
            if ($_conf['iframe_popup'] == 3) {
                $num_ht = ' <img src="img/ida.png" width="2" height="12" alt="">';
                $num_ht .= preg_replace('/\\d/', '<img src="img/id\\0.png" height="12" alt="">', $num);
                $num_ht .= '<img src="img/idz.png" width="2" height="12" alt=""> ';
            } else {
                $num_ht = '('.$num.')';
            }
        } else {
            return $idstr;
        }

        if ($_conf['coloredid.enable'] > 0 && preg_match("/^ID:[0-9a-zA-Z+\/]{8,11}/",$idstr)) {
            if ($this->_ids_for_render === null) $this->_ids_for_render = array();
            $this->_ids_for_render[substr($id, 0, 8)] = $this->thread->idcount[$id];
            if ($_conf['coloredid.click'] > 0) {
                $num_ht = '<a href="javascript:void(0);" class="' . ShowThreadPc::cssClassedId($id) . '" onClick="idCol.click(\'' . substr($id, 0, 8) . '\', event); return false;" onDblClick="this.onclick(event); return false;">' . $num_ht . '</a>';
            }
            $idstr = $this->coloredIdStr(
                $idstr, $id, $_conf['coloredid.click'] > 0 ? true : false);
        }

        $word = rawurlencode($id);
        $filter_url = "{$_conf['read_php']}?bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;host={$this->thread->host}&amp;ls=all&amp;field=id&amp;word={$word}&amp;method=just&amp;match=on&amp;idpopup=1&amp;offline=1";

        if ($_conf['iframe_popup']) {
            return $this->iframePopup($filter_url, $idstr, $_conf['bbs_win_target_at']) . $num_ht;
        }
        return "<a href=\"{$filter_url}\"{$_conf['bbs_win_target_at']}>{$idstr}</a>{$num_ht}";
    }

    // }}}
    // {{{ link_wikipedia()

    /**
     * @see ShowThread
     */
    function link_wikipedia($word) {
        global $_conf;
        $link = 'http://ja.wikipedia.org/wiki/' . rawurlencode($word);
        return  '<a href="' . ($_conf['through_ime'] ?
            P2Util::throughIme($link) : $link) .
            "\"{$_conf['ext_win_target_at']}>{$word}</a>";
    }

    // }}}
    // {{{ quoteRes()

    /**
     * ���p�ϊ��i�P�Ɓj
     *
     * @param   string  $full           >>1-100
     * @param   string  $qsign          >>
     * @param   string  $appointed_num    1-100
     * @return  string
     */
    public function quoteRes(array $s)
    {
        global $_conf;

		list($full, $qsign, $appointed_num)=$s;
		$anchor_jump = false;
		$appointed_num=$this->getQuoteNum($appointed_num);
		if (!$appointed_num) {return $full;}
        if (preg_match("/\D/",$appointed_num)) {
            return $this->quoteResRange($full, $qsign, $appointed_num);
        }

        $qnum = intval($appointed_num);
        if ($qnum < 1 || $qnum > sizeof($this->thread->datlines)) {
            return $full;
        }

        if ($anchor_jump && $qnum >= $this->thread->resrange['start'] && $qnum <= $this->thread->resrange['to']) {
            $read_url = '#' . ($this->_matome ? "t{$this->_matome}" : '') . "r{$qnum}";
        } else {
            $read_url = "{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;offline=1&amp;ls={$appointed_num}";
        }
        $attributes = $_conf['bbs_win_target_at'];
        if ($_conf['quote_res_view']) {
            if ($this->_matome) {
                $qres_id = "t{$this->_matome}qr{$qnum}";
            } else {
                $qres_id = "qr{$qnum}";
            }
            $attributes .= " onmouseover=\"showResPopUp('{$qres_id}',event)\"";
            $attributes .= " onmouseout=\"hideResPopUp('{$qres_id}')\"";
        }
        return "<a href=\"{$read_url}\"{$attributes}>{$full}</a>";
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
    public function quoteResRange($full, $qsign, $appointed_num)
    {
        global $_conf;

        $read_url = "{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;offline=1&amp;ls={$appointed_num}n";

        if ($_conf['iframe_popup']) {
            $pop_url = $read_url . "&amp;renzokupop=true";
            return $this->iframePopup(array($read_url, $pop_url), $full, $_conf['bbs_win_target_at'], 1);
        }

        // ���ʂɃ����N
        return "<a href=\"{$read_url}\"{$_conf['bbs_win_target_at']}>{$full}</a>";

        // 1�ڂ����p���X�|�b�v�A�b�v
        /*
        $qnums = explode('-', $appointed_num);
        $qlink = $this->quoteRes($qsign . $qnum[0], $qsign, $qnum[0]) . '-';
        if (isset($qnums[1])) {
            $qlink .= $qnums[1];
        }
        return $qlink;
        */
    }

    // }}}
    // {{{ iframePopup()

    /**
     * HTML�|�b�v�A�b�v�ϊ�
     *
     * @param   string|array    $url
     * @param   string|array    $str
     * @param   string          $attr
     * @param   int|null        $mode
     * @return  string
     */
    public function iframePopup($url, $str, $attr = '', $mode = null)
    {
        global $_conf;

        // �����N�pURL�ƃ|�b�v�A�b�v�pURL
        if (is_array($url)) {
            $link_url = $url[0];
            $pop_url = $url[1];
        } else {
            $link_url = $url;
            $pop_url = $url;
        }

        // �����N������ƃ|�b�v�A�b�v�̈�
        if (is_array($str)) {
            $link_str = $str[0];
            $pop_str = $str[1];
        } else {
            $link_str = $str;
            $pop_str = null;
        }

        // �����N�̑���
        if (is_array($attr)) {
            $_attr = $attr;
            $attr = '';
            foreach ($_attr as $key => $value) {
                $attr .= ' ' . $key . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
            }
        } elseif ($attr !== '' && substr($attr, 0, 1) != ' ') {
            $attr = ' ' . $attr;
        }

        // �����N�̑�����HTML�|�b�v�A�b�v�p�̃C�x���g�n���h����������
        $pop_attr = $attr;
        if ($_conf['iframe_popup_event'] == 1) {
            $pop_attr .= " onClick=\"stophide=true; showHtmlPopUp('{$pop_url}',event,0); return false;\"";
        } else {
            $pop_attr .= " onmouseover=\"showHtmlPopUp('{$pop_url}',event,{$_conf['iframe_popup_delay']})\"";
        }
        $pop_attr .= " onmouseout=\"offHtmlPopUp()\"";

        // �ŏI����
        if (is_null($mode)) {
            $mode = $_conf['iframe_popup'];
        }
        if ($mode == 2 && !is_null($pop_str)) {
            $mode = 3;
        } elseif ($mode == 3 && is_null($pop_str)) {
            global $skin, $STYLE;
            $custom_pop_img = "skin/{$skin}/pop.png";
            if (file_exists($custom_pop_img)) {
                $pop_img = htmlspecialchars($custom_pop_img, ENT_QUOTES);
                $x = $STYLE['iframe_popup_mark_width'];
                $y = $STYLE['iframe_popup_mark_height'];
            } else {
                $pop_img = 'img/pop.png';
                $y = $x = 12;
            }
            $pop_str = "<img src=\"{$pop_img}\" width=\"{$x}\" height=\"{$y}\" hspace=\"2\" vspace=\"0\" border=\"0\" align=\"top\" alt=\"\">";
        }

        // �����N�쐬
        switch ($mode) {
        // �}�[�N����
        case 1:
            return "<a href=\"{$link_url}\"{$pop_attr}>{$link_str}</a>";
        // (p)�}�[�N
        case 2:
            return "(<a href=\"{$link_url}\"{$pop_attr}>p</a>)<a href=\"{$link_url}\"{$attr}>{$link_str}</a>";
        // [p]�摜�A�T���l�C���Ȃ�
        case 3:
            return "<a href=\"{$link_url}\"{$pop_attr}>{$pop_str}</a><a href=\"{$link_url}\"{$attr}>{$link_str}</a>";
        // �|�b�v�A�b�v���Ȃ�
        default:
            return "<a href=\"{$link_url}\"{$attr}>{$link_str}</a>";
        }
    }

    // }}}
    // {{{ iframePopupCallback()

    /**
     * HTML�|�b�v�A�b�v�ϊ��i�R�[���o�b�N�p�C���^�[�t�F�[�X�j
     *
     * @param   array   $s  ���K�\���Ƀ}�b�`�����v�f�̔z��
     * @return  string
     */
    public function iframePopupCallback($s)
    {
        return $this->iframePopup(htmlspecialchars($s[1], ENT_QUOTES, 'Shift_JIS', false),
                                  htmlspecialchars($s[3], ENT_QUOTES, 'Shift_JIS', false),
                                  $s[2]);
    }

    // }}}
    // {{{ coloredIdStr()

    /**
     * Merged from http://jiyuwiki.com/index.php?cmd=read&page=rep2%A4%C7%A3%C9%A3%C4%A4%CE%C7%D8%B7%CA%BF%A7%CA%D1%B9%B9&alias%5B%5D=pukiwiki%B4%D8%CF%A2
     *
     * @access  private
     * @return  string
     */
    function coloredIdStr($idstr, $id, $classed = false)
    {
        global $_conf;

        if (!(isset($this->thread->idcount[$id])
                && $this->thread->idcount[$id] > 1)) return $idstr;
        if ($classed) return $this->_coloredIdStrClassed($idstr, $id);

        switch ($_conf['coloredid.rate.type']) {
        case 1:
            $rate = $_conf['coloredid.rate.times'];
            break;
        case 2:
            $rate = $this->getIdCountRank(10);
            break;
        case 3:
            $rate = $this->getIdCountAverage();
            break;
        default:
            return $idstr;
        }
        if ($rate > 1 && $this->thread->idcount[$id] >= $rate) {
            switch ($_conf['coloredid.coloring.type']) {
            case 0:
                return $this->_coloredIdStr0($idstr, $id);
                break;
            case 1:
                return $this->_coloredIdStr1($idstr, $id);
                break;
            default:
                return $idstr;
            }
        }
        return $idstr;
    }

    // }}}
    // {{{ _coloredIdStrClassed()

    function _coloredIdStrClassed($idstr, $id) {
        $ret = array();
        foreach ($arr = explode(':', $idstr) as $i => $str) {
			$ret[] = ($i == 0 || $i == 1) ? 
				'<span class="' . ShowThreadPc::cssClassedId($id) 
				. ($i == 0 ? '-l' : '-b') . '">' . $str . '</span>'
			: $str;
        }
        return implode(':', $ret);
    }

    // }}}
    // {{{ _coloredIdStr0()

    /**
     * ID�J���[ �I���W�i�����F�p
     */
    function _coloredIdStr0($idstr, $id) {
        if (isset($this->idstyles[$id])) {
            $colored = $this->idstyles[$id];
        } else {
            require_once P2_LIB_DIR . '/ColoredIDStr0.php';
            $colored = coloredIdStyle($id, $this->thread->idcount[$id]);
            $this->idstyles[$id] = $colored;
        }
        $ret = array();
        foreach ($arr = explode(':', $idstr) as $i => $str) {
			$ret[] = ($colored[$i]) ? "<span style=\"{$colored[$i]}\">{$str}</span>" : $str;;
        }
        return implode(':', $ret);
    }

    // }}}
    // {{{ _coloredIdStr1()

    /**
     * ID�J���[ thermon�ŗp
     */
    function _coloredIdStr1($idstr, $id) {
        require_once P2_LIB_DIR . '/ColoredIDStr.php';
        $colored = coloredIdStyle($idstr,$id,$this->thread->idcount[$id]);
        $idstr2=preg_split('/:/',$idstr,2); // �R������ID������𕪊�
        $ret=array_shift($idstr2).':';
        if ($colored[1]) {
                    $idstr2[1]=substr($idstr2[0],4);
                    $idstr2[0]=substr($idstr2[0],0,4);
        }
        foreach ($idstr2 as $i=>$str) {
			$ret .= ($colored[$i]) ? "<span style=\"{$colored[$i]}\">{$str}</span>" : $str;
        }
        return $ret;
    }

    // }}}
    // {{{ cssClassedId()

    /**
     * ID�J���[�Ɏg�p����CSS�N���X����ID�����񂩂�Z�o���ĕԂ�.
     */
    static public function cssClassedId($id) {
        return 'idcss-' . bin2hex(
            base64_decode(str_replace('.', '+', substr($id, 0, 8))));
    }

    // }}}
    // {{{ ���[�e�B���e�B���\�b�h
    // {{{ checkQuoteResNums()

    /**
     * HTML���b�Z�[�W���̈��p���X�̔ԍ����ċA�`�F�b�N����
     */
    public function checkQuoteResNums($res_num, $name, $msg)
    {
        global $_conf;
		static $_cache=array();
		$matome=$_cache[$this->_matome] ? $_cache[$this->_matome] : "null";
		if (!array_key_exists($matome,$_cache)) {$_cache[$matome]=array();}
        if (array_key_exists($res_num,$_cache[$matome])) {
			return $_cache[$matome][$res_num];
		}

        // �ċA���~�b�^
        if ($this->_quote_check_depth > 60) {
            return array();
        } else {
            $this->_quote_check_depth++;
        }

        $quote_res_nums = array();
        $name = preg_replace('/(��.*)/', '', $name, 1);

        // ���O
        if ($matches = $this->getQuoteResNumsName($name)) {
            foreach ($matches as $a_quote_res_num) {
                if ($a_quote_res_num) {
                    $quote_res_nums[] = $a_quote_res_num;
                    $a_quote_res_idx = $a_quote_res_num - 1;

                    // �������g�̔ԍ��Ɠ���łȂ���΁A
                    if ($a_quote_res_num != $res_num) {
                        // �`�F�b�N���Ă��Ȃ��ԍ����ċA�`�F�b�N
                        if (!isset($this->_quote_res_nums_checked[$a_quote_res_num])) {
                            $this->_quote_res_nums_checked[$a_quote_res_num] = true;
                            if (isset($this->thread->datlines[$a_quote_res_idx])) {
                                $datalinear = $this->thread->explodeDatLine($this->thread->datlines[$a_quote_res_idx]);
                                $quote_name = $datalinear[0];
                                $quote_msg = $this->thread->datlines[$a_quote_res_idx];
                                $quote_res_nums = array_merge($quote_res_nums, $this->checkQuoteResNums($a_quote_res_num, $quote_name, $quote_msg));
                            }
                         }
                     }
                }
                // $name=preg_replace("/([0-9]+)/", "", $name, 1);
            }
        }
        if ($ranges=$this->_getAnchorsFromMsg($msg,$res_num)) {
            foreach ($ranges as $a_range) {
                if (preg_match($this->getAnchorRegex('/%range_delimiter%/'),$a_range)) {continue;}
                $a_quote_res_num = (int) (mb_convert_kana($a_range, 'n'));
                $a_quote_res_idx = $a_quote_res_num - 1;

                if (!$a_quote_res_num) {break;}
                $quote_res_nums[] = $a_quote_res_num;

                // �������g�̔ԍ��Ɠ���łȂ���΁A
                if ($a_quote_res_num == $res_num) {continue;}
                // �`�F�b�N���Ă��Ȃ��ԍ����ċA�`�F�b�N
                if (!isset($this->_quote_res_nums_checked[$a_quote_res_num])) {
                    $this->_quote_res_nums_checked[$a_quote_res_num] = true;
                    if (isset($this->thread->datlines[$a_quote_res_idx])) {
                        $datalinear = $this->thread->explodeDatLine($this->thread->datlines[$a_quote_res_idx]);
                        $quote_name = $datalinear[0];
                        $quote_msg = $this->thread->datlines[$a_quote_res_idx];
                        $quote_res_nums = array_merge($quote_res_nums, $this->checkQuoteResNums($a_quote_res_num, $quote_name, $quote_msg));
                    }
                }
            }
        }

        if ($_conf['backlink_list'] > 0) {
			 // ���X���t���Ă���ꍇ�͂�����Ώۂɂ���
            $quotee_lists = $this->get_quote_from();
			$quoting_path=array();
			$quoting_path[$res_num]++;
			while (list($quoter,$v)=each($quoting_path)) {
	            if (!array_key_exists($quoter, $quotee_lists)) {continue;}
	            foreach ($quotee_lists[$quoter] as $quotee) {
					if (array_key_exists($quotee,$quoting_path)) {continue;}
                    if ($quotee != $quoter && 
						!isset($this->_quote_res_nums_checked[$quotee])) {
                        $this->_quote_res_nums_checked[$quotee] = true;
                        if (isset($this->thread->datlines[$quotee - 1])) {
                            $datalinear = $this->thread->explodeDatLine($this->thread->datlines[$quotee - 1]);
                            $quote_name = $datalinear[0];
                            $quote_msg = $this->thread->datlines[$quotee - 1];
                            $quote_res_nums = array_merge($quote_res_nums, $this->checkQuoteResNums($quotee, $quote_name, $quote_msg));
                        }
                    }
					$quoting_path[$quotee]++;
					$quote_res_nums[] = $quotee;
				}
			}
        }
		if (count($quote_res_nums)) {
			$quote_res_nums=array_unique($quote_res_nums,SORT_NUMERIC);
			sort($quote_res_nums,SORT_NUMERIC);
//			trigger_error($this->_quote_check_depth .":checkQuoteResNums:{$res_num}=>(".join(",",$quote_res_nums).")");
		}
		$this->_quote_check_depth--;
        return $_cache[$matome][$res_num]=$quote_res_nums;
    }

    // }}}
    // {{{ imageHtmlPopup() 

    /**
     * �摜��HTML�|�b�v�A�b�v&�|�b�v�A�b�v�E�C���h�E�T�C�Y�ɍ��킹��
     */
    public function imageHtmlPopup($img_url, $img_tag, $link_str)
    {
        global $_conf;

        if ($_conf['expack.ic2.enabled'] && $_conf['expack.ic2.fitimage']) {
            $popup_url = 'ic2_fitimage.php?url=' . rawurlencode(str_replace('&amp;', '&', $img_url));
        } else {
            $popup_url = $img_url;
        }

        $pops = ($_conf['iframe_popup'] == 1) ? $img_tag . $link_str : array($link_str, $img_tag);
        return $this->iframePopup(array($img_url, $popup_url), $pops, $_conf['ext_win_target_at']);
    }

    // }}}
    // {{{ respopToAsync()

    /**
     * ���X�|�b�v�A�b�v��񓯊����[�h�ɉ��H����
     */
    public function respopToAsync($str)
    {
        $respop_regex = '/(onmouseover)=\"(showResPopUp\(\'(q(\d+)of\d+)\',event\).*?)\"/';
        $respop_replace = '$1="loadResPopUp(' . $this->asyncObjName . ', $4);$2"';
        return preg_replace($respop_regex, $respop_replace, $str);
    }

    // }}}
    // {{{ getASyncObjJs()

    /**
     * �񓯊��ǂݍ��݂ŗ��p����JavaScript�I�u�W�F�N�g�𐶐�����
     */
    public function getASyncObjJs()
    {
        global $_conf;
        static $done = array();

        if (isset($done[$this->asyncObjName])) {
            return;
        }
        $done[$this->asyncObjName] = TRUE;

        $code = <<<EOJS
<script type="text/javascript">
//<![CDATA[
var {$this->asyncObjName} = {
    host:"{$this->thread->host}", bbs:"{$this->thread->bbs}", key:"{$this->thread->key}",
    readPhp:"{$_conf['read_php']}", readTarget:"{$_conf['bbs_win_target']}"
};
//]]>
</script>\n
EOJS;
        return $code;
    }

    // }}}
    // {{{ getSpmObjJs()

    /**
     * �X�}�[�g�|�b�v�A�b�v���j���[�𐶐�����JavaScript�R�[�h�𐶐�����
     */
    public function getSpmObjJs($retry = false)
    {
        global $_conf, $STYLE;

        if (isset(self::$_spm_objects[$this->spmObjName])) {
            return $retry ? self::$_spm_objects[$this->spmObjName] : '';
        }

        $ttitle_en = rawurlencode(base64_encode($this->thread->ttitle));

        if ($_conf['expack.spm.filter_target'] == '' || $_conf['expack.spm.filter_target'] == 'read') {
            $_conf['expack.spm.filter_target'] = '_self';
        }

        $motothre_url = $this->thread->getMotoThread();
        $motothre_url = substr($motothre_url, 0, strlen($this->thread->ls) * -1);

        $_spmOptions = array(
            'null',
            ((!$_conf['disable_res'] && $_conf['expack.spm.kokores']) ? (($_conf['expack.spm.kokores_orig']) ? '2' : '1') : '0'),
            (($_conf['expack.spm.ngaborn']) ? (($_conf['expack.spm.ngaborn_confirm']) ? '2' : '1') : '0'),
            (($_conf['expack.spm.filter']) ? '1' : '0'),
            (($this->am_on_spm) ? '1' : '0'),
            (($_conf['expack.aas.enabled']) ? '1' : '0'),
        );
        $spmOptions = implode(',', $_spmOptions);

        // �G�X�P�[�v
        $_spm_title = StrCtl::toJavaScript($this->thread->ttitle_hc);
        $_spm_url = addslashes($motothre_url);
        $_spm_host = addslashes($this->thread->host);
        $_spm_bbs = addslashes($this->thread->bbs);
        $_spm_key = addslashes($this->thread->key);
        $_spm_ls = addslashes($this->thread->ls);

        $code = <<<EOJS
<script type="text/javascript">
//<![CDATA[\n
EOJS;

        if (!count(self::$_spm_objects)) {
            $code .= sprintf("spmFlexTarget = '%s';\n", StrCtl::toJavaScript($_conf['expack.spm.filter_target']));
            if ($_conf['expack.aas.enabled']) {
                $code .= sprintf("var aas_popup_width = %d;\n", $_conf['expack.aas.default.width'] + 10);
                $code .= sprintf("var aas_popup_height = %d;\n", $_conf['expack.aas.default.height'] + 10);
            }
        }

        $code .= <<<EOJS
var {$this->spmObjName} = {
    'objName':'{$this->spmObjName}',
    'rc':'{$this->thread->rescount}',
    'title':'{$_spm_title}',
    'ttitle_en':'{$ttitle_en}',
    'url':'{$_spm_url}',
    'host':'{$_spm_host}',
    'bbs':'{$_spm_bbs}',
    'key':'{$_spm_key}',
    'ls':'{$_spm_ls}',
    'spmOption':[{$spmOptions}]
};
SPM.init({$this->spmObjName});
//]]>
</script>\n
EOJS;

        self::$_spm_objects[$this->spmObjName] = $code;

        return $code;
    }

    // }}}
    // }}}
    // {{{ transLinkDo()����Ăяo�����URL�����������\�b�h
    /**
     * �����̃��\�b�h�͈����������Ώۃp�^�[���ɍ��v���Ȃ���FALSE��Ԃ��A
     * transLinkDo()��FALSE���Ԃ��Ă����$_url_handlers�ɓo�^����Ă��鎟�̊֐�/���\�b�h�ɏ��������悤�Ƃ���B
     */
    // {{{ plugin_linkURL()

    /**
     * URL�����N
     *
     * @param   string $url
     * @param   array $purl
     * @param   string $str
     * @return  string|false
     */
    public function plugin_linkURL($url, $purl, $str)
    {
        global $_conf;

        if (isset($purl['scheme'])) {
            // ime
            if ($_conf['through_ime']) {
                $link_url = P2Util::throughIme($purl[0]);
            } else {
                $link_url = $url;
            }

            $is_http = ($purl['scheme'] == 'http' || $purl['scheme'] == 'https');

            // HTML�|�b�v�A�b�v
            if ($_conf['iframe_popup'] && $is_http) {
                // p2pm/expm �w��̏ꍇ�̂݁A���ʂɎ蓮�]���w���ǉ�����
                if ($_conf['through_ime'] == 'p2pm') {
                    $pop_url = preg_replace('/\\?(enc=1&amp;)url=/', '?$1m=1&amp;url=', $link_url);
                } elseif ($_conf['through_ime'] == 'expm') {
                    $pop_url = preg_replace('/(&amp;d=-?\d+)?$/', '&amp;d=-1', $link_url);
                } else {
                    $pop_url = $link_url;
                }
                $link = $this->iframePopup(array($link_url, $pop_url), $str, $_conf['ext_win_target_at']);
            } else {
                $link = "<a href=\"{$link_url}\"{$_conf['ext_win_target_at']}>{$str}</a>";
            }

            // �u���N���`�F�b�J
            if ($_conf['brocra_checker_use'] && $_conf['brocra_checker_url'] && $is_http) {
                if (strlen($_conf['brocra_checker_query'])) {
                    $brocra_checker_url = $_conf['brocra_checker_url'] . '?' . $_conf['brocra_checker_query'] . '=' . rawurlencode($purl[0]);
                } else {
                    $brocra_checker_url = rtrim($_conf['brocra_checker_url'], '/') . '/' . $url;
                }
                // �u���N���`�F�b�J�Eime
                if ($_conf['through_ime']) {
                    $brocra_checker_url = P2Util::throughIme($brocra_checker_url);
                }
                $check_mark = '�`�F�b�N';
                $check_mark_prefix = '[';
                $check_mark_suffix = ']';
                // �u���N���`�F�b�J�EHTML�|�b�v�A�b�v
                if ($_conf['iframe_popup']) {
                    // p2pm/expm �w��̏ꍇ�̂݁A���ʂɎ蓮�]���w���ǉ�����
                    if ($_conf['through_ime'] == 'p2pm') {
                        $brocra_pop_url = preg_replace('/\\?(enc=1&amp;)url=/', '?$1m=1&amp;url=', $brocra_checker_url);
                    } elseif ($_conf['through_ime'] == 'expm') {
                        $brocra_pop_url = $brocra_checker_url . '&amp;d=-1';
                    } else {
                        $brocra_pop_url = $brocra_checker_url;
                    }
                    if ($_conf['iframe_popup'] == 3) {
                        $check_mark = '<img src="img/check.png" width="33" height="12" alt="">';
                        $check_mark_prefix = '';
                        $check_mark_suffix = '';
                    }
                    $brocra_checker_link = $this->iframePopup(array($brocra_checker_url, $brocra_pop_url), $check_mark, $_conf['ext_win_target_at']);
                } else {
                    $brocra_checker_link = "<a href=\"{$brocra_checker_url}\"{$_conf['ext_win_target_at']}>{$check_mark}</a>";
                }
                $link .= $check_mark_prefix . $brocra_checker_link . $check_mark_suffix;
            }

            return $link;
        }
        return FALSE;
    }

    // }}}
    // {{{ plugin_link2chSubject()

    /**
     * 2ch bbspink    �����N
     *
     * @param   string $url
     * @param   array $purl
     * @param   string $str
     * @return  string|false
     */
    public function plugin_link2chSubject($url, $purl, $str)
    {
        global $_conf;

        if (preg_match('{^http://(\\w+\\.(?:2ch\\.net|bbspink\\.com))/(\\w+)/$}', $purl[0], $m)) {
            $subject_url = "{$_conf['subject_php']}?host={$m[1]}&amp;bbs={$m[2]}";
            return "<a href=\"{$url}\" target=\"subject\">{$str}</a> [<a href=\"{$subject_url}\" target=\"subject\">��p2�ŊJ��</a>]";
        }
        return FALSE;
    }

    // }}}
    // {{{ plugin_linkThread()

    /**
     * �X���b�h�����N
     *
     * @param   string $url
     * @param   array $purl
     * @param   string $str
     * @return  string|false
     */
    public function plugin_linkThread($url, $purl, $str)
    {
        global $_conf;

        list($nama_url, $host, $bbs, $key, $ls) = P2Util::detectThread($purl[0]);
        if ($host && $bbs && $key) {
            $read_url = "{$_conf['read_php']}?host={$host}&amp;bbs={$bbs}&amp;key={$key}&amp;ls={$ls}";
            if ($_conf['iframe_popup']) {
                if ($ls && preg_match('/^[0-9\\-n]+$/', $ls)) {
                    $pop_url = $read_url;
                } else {
                    $pop_url = $read_url . '&amp;one=true';
                }
                return $this->iframePopup(array($read_url, $pop_url), $str, $_conf['bbs_win_target_at']);
            }
            return "<a href=\"{$read_url}{$_conf['bbs_win_target_at']}\">{$str}</a>";
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
        // http://m.youtube.com/watch?v=OhcX0xJsDK8&client=mv-google&gl=JP&hl=ja&guid=ON&warned=True
        if (preg_match('{^http://(www|jp|m)\\.youtube\\.com/watch\\?(?:.+&amp;)?v=([0-9a-zA-Z_\\-]+)}', $url, $m)) {
            // ime
            if ($_conf['through_ime']) {
                $link_url = P2Util::throughIme($url);
            } else {
                $link_url = $url;
            }

            // HTML�|�b�v�A�b�v
            if ($_conf['iframe_popup']) {
                $link = $this->iframePopup($link_url, $str, $_conf['ext_win_target_at']);
            } else {
                $link = "<a href=\"{$link_url}\"{$_conf['ext_win_target_at']}>{$str}</a>";
            }

            $subd = $m[1];
            $id = $m[2];

            if ($_conf['link_youtube'] == 2) {
                return <<<EOP
{$link} <img class="preview-video-switch" src="img/show.png" width="30" height="12" alt="show" onclick="preview_video_youtube('{$id}', this);">
EOP;
            } else {
                return <<<EOP
{$link}<div class="preview-video preview-video-youtuve"><object width="255" height="210"><param name="movie" value="http://www.youtube.com/v/{$id}" valuetype="ref" type="application/x-shockwave-flash"><param name="wmode" value="transparent"><embed src="http://www.youtube.com/v/{$id}" type="application/x-shockwave-flash" wmode="transparent" width="255" height="210"></object></div>
EOP;
            }
        }
        return FALSE;
    }

    // }}}
    // {{{ plugin_linkNicoNico()

    /**
     * �j�R�j�R����ϊ��v���O�C��
     *
     * @param   string $url
     * @param   array $purl
     * @param   string $str
     * @return  string|false
     */
    public function plugin_linkNicoNico($url, $purl, $str)
    {
        global $_conf;

        // http://www.nicovideo.jp/watch?v=utbrYUJt9CSl0
        // http://www.nicovideo.jp/watch/utvWwAM30N0No
        // http://m.nicovideo.jp/watch/sm7044684
        if (preg_match('{^http://(?:www|m)\\.nicovideo\\.jp/watch(?:/|(?:\\?v=))([0-9a-zA-Z_-]+)}', $url, $m)) {
            // ime
            if ($_conf['through_ime']) {
                $link_url = P2Util::throughIme($purl[0]);
            } else {
                $link_url = $url;
            }

            // HTML�|�b�v�A�b�v
            if ($_conf['iframe_popup']) {
                $link = $this->iframePopup($link_url, $str, $_conf['ext_win_target_at']);
            } else {
                $link = "<a href=\"{$link_url}\"{$_conf['ext_win_target_at']}>{$str}</a>";
            }

            $id = $m[1];

            if ($_conf['link_niconico'] == 2) {
                return <<<EOP
{$link} <img class="preview-video-switch" src="img/show.png" width="30" height="12" alt="show" onclick="preview_video_niconico('{$id}', this);">
EOP;
            } else {
                return <<<EOP
{$link}<div class="preview-video preview-video-niconico"><iframe src="http://ext.nicovideo.jp/thumb/{$id}" width="425" height="175" scrolling="auto" frameborder="0"></iframe></div>
EOP;
            }
        }
        return FALSE;
    }

    // }}}
    // {{{ plugin_viewImage()

    /**
     * �摜�|�b�v�A�b�v�ϊ�
     *
     * @param   string $url
     * @param   array $purl
     * @param   string $str
     * @return  string|false
     */
    public function plugin_viewImage($url, $purl, $str)
    {
        global $_conf;
        global $pre_thumb_unlimited, $pre_thumb_limit;

        if (P2Util::isUrlWikipediaJa($url)) {
            return false;
        }

        // �\������
        if (!$pre_thumb_unlimited && empty($pre_thumb_limit)) {
            return false;
        }

        if (preg_match('{^https?://.+?\\.(jpe?g|gif|png)$}i', $purl[0]) && empty($purl['query'])) {
            $pre_thumb_limit--; // �\�������J�E���^��������
            $img_tag = "<img class=\"thumbnail\" src=\"{$url}\" height=\"{$_conf['pre_thumb_height']}\" weight=\"{$_conf['pre_thumb_width']}\" hspace=\"4\" vspace=\"4\" align=\"middle\">";

            if ($_conf['iframe_popup']) {
                $view_img = $this->imageHtmlPopup($url, $img_tag, $str);
            } else {
                $view_img = "<a href=\"{$url}\"{$_conf['ext_win_target_at']}>{$img_tag}{$str}</a>";
            }

            // �u���N���`�F�b�J �i�v���r���[�Ƃ͑��e��Ȃ��̂ŃR�����g�A�E�g�j
            /*if ($_conf['brocra_checker_use']) {
                $link_url_en = rawurlencode($url);
                if ($_conf['iframe_popup'] == 3) {
                    $check_mark = '<img src="img/check.png" width="33" height="12" alt="">';
                    $check_mark_prefix = '';
                    $check_mark_suffix = '';
                } else {
                    $check_mark = '�`�F�b�N';
                    $check_mark_prefix = '[';
                    $check_mark_suffix = ']';
                }
                $view_img .= $check_mark_prefix . "<a href=\"{$_conf['brocra_checker_url']}?{$_conf['brocra_checker_query']}={$link_url_en}\"{$_conf['ext_win_target_at']}>{$check_mark}</a>" . $check_mark_suffix;
            }*/

            return $view_img;
        }

        return false;
    }

    // }}}
    // {{{ plugin_imageCache2()

    /**
     * ImageCache2�T���l�C���ϊ�
     *
     * @param   string $url
     * @param   array $purl
     * @param   string $str
     * @return  string|false
     */
    public function plugin_imageCache2($url, $purl, $str,
        $force = false, $referer = null)
    {
        global $_conf;
        global $pre_thumb_unlimited, $pre_thumb_ignore_limit, $pre_thumb_limit;
        static $serial = 0;

        if (P2Util::isUrlWikipediaJa($url)) {
            return false;
        }

        if ((preg_match('{^https?://.+?\\.(jpe?g|gif|png)$}i', $purl[0]) && empty($purl['query'])) || $force) {
            // ����
            $serial++;
            $thumb_id = 'thumbs' . $serial . $this->thumb_id_suffix;
            $tmp_thumb = './img/ic_load.png';
            $url_ht = $url;
            $url = $purl[0];
            $url_en = rawurlencode($url) .
                ($referer ? '&amp;ref=' . rawurlencode($referer) : '');
            $img_id = null;

            $icdb = new IC2_DataObject_Images;

            // r=0:�����N;r=1:���_�C���N�g;r=2:PHP�ŕ\��
            // t=0:�I���W�i��;t=1:PC�p�T���l�C��;t=2:�g�їp�T���l�C��;t=3:���ԃC���[�W
            $img_url = 'ic2.php?r=1&amp;uri=' . $url_en;
            $thumb_url = 'ic2.php?r=1&amp;t=1&amp;uri=' . $url_en;

            // DB�ɉ摜��񂪓o�^����Ă����Ƃ�
            if ($icdb->get($url)) {
                $img_id = $icdb->id;

                // �E�B���X�Ɋ������Ă����t�@�C���̂Ƃ�
                if ($icdb->mime == 'clamscan/infected') {
                    return "<img class=\"thumbnail\" src=\"./img/x04.png\" width=\"32\" height=\"32\" hspace=\"4\" vspace=\"4\" align=\"middle\"> <s>{$str}</s>";
                }
                // ���ځ[��摜�̂Ƃ�
                if ($icdb->rank < 0) {
                    return "<img class=\"thumbnail\" src=\"./img/x01.png\" width=\"32\" height=\"32\" hspace=\"4\" vspace=\"4\" align=\"middle\"> <s>{$str}</s>";
                }

                // �I���W�i�����L���b�V������Ă���Ƃ��͉摜�𒼐ړǂݍ���
                $_img_url = $this->thumbnailer->srcPath($icdb->size, $icdb->md5, $icdb->mime);
                if (file_exists($_img_url)) {
                    $img_url = $_img_url;
                    $cached = true;
                } else {
                    $cached = false;
                }

                // �T���l�C�����쐬����Ă��Ă���Ƃ��͉摜�𒼐ړǂݍ���
                $_thumb_url = $this->thumbnailer->thumbPath($icdb->size, $icdb->md5, $icdb->mime);
                if (file_exists($_thumb_url)) {
                    $thumb_url = $_thumb_url;
                    // �����X���^�C�����@�\��ON�ŃX���^�C���L�^����Ă��Ȃ��Ƃ���DB���X�V
                    if (!is_null($this->img_memo) && strpos($icdb->memo, $this->img_memo) === false){
                        $update = new IC2_DataObject_Images;
                        if (!is_null($icdb->memo) && strlen($icdb->memo) > 0) {
                            $update->memo = $this->img_memo . ' ' . $icdb->memo;
                        } else {
                            $update->memo = $this->img_memo;
                        }
                        $update->whereAddQuoted('uri', '=', $url);
                        $update->update();
                    }
                }

                // �T���l�C���̉摜�T�C�Y
                $thumb_size = $this->thumbnailer->calc($icdb->width, $icdb->height);
                $thumb_size = preg_replace('/(\d+)x(\d+)/', 'width="$1" height="$2"', $thumb_size);
                $tmp_thumb = './img/ic_load1.png';

                $orig_img_url   = $img_url;
                $orig_thumb_url = $thumb_url;

            // �摜���L���b�V������Ă��Ȃ��Ƃ�
            // �����X���^�C�����@�\��ON�Ȃ�N�G����UTF-8�G���R�[�h�����X���^�C���܂߂�
            } else {
                // �摜���u���b�N���X�gor�G���[���O�ɂ��邩�m�F
                if (false !== ($errcode = $icdb->ic2_isError($url))) {
                    return "<img class=\"thumbnail\" src=\"./img/{$errcode}.png\" width=\"32\" height=\"32\" hspace=\"4\" vspace=\"4\" align=\"middle\"> <s>{$str}</s>";
                }

                $cached = false;


                $orig_img_url   = $img_url;
                $orig_thumb_url = $thumb_url;
                $img_url .= $this->img_memo_query;
                $thumb_url .= $this->img_memo_query;
                $thumb_size = '';
                $tmp_thumb = './img/ic_load2.png';
            }

            // �L���b�V������Ă��炸�A�\�����������L���̂Ƃ�
            if (!$cached && !$pre_thumb_unlimited && !$pre_thumb_ignore_limit) {
                // �\�������𒴂��Ă�����A�\�����Ȃ�
                // �\�������𒴂��Ă��Ȃ���΁A�\�������J�E���^��������
                if ($pre_thumb_limit <= 0) {
                    $show_thumb = false;
                } else {
                    $show_thumb = true;
                    $pre_thumb_limit--;
                }
            } else {
                $show_thumb = true;
            }

            // �\�����[�h
            if ($show_thumb) {
                $img_tag = "<img class=\"thumbnail\" src=\"{$thumb_url}\" {$thumb_size} hspace=\"4\" vspace=\"4\" align=\"middle\">";
                if ($_conf['iframe_popup']) {
                    $view_img = $this->imageHtmlPopup($img_url, $img_tag, $str);
                } else {
                    $view_img = "<a href=\"{$img_url}\"{$_conf['ext_win_target_at']}>{$img_tag}{$str}</a>";
                }
            } else {
                $img_tag = "<img id=\"{$thumb_id}\" class=\"thumbnail\" src=\"{$tmp_thumb}\" width=\"32\" height=\"32\" hspace=\"4\" vspace=\"4\" align=\"middle\">";
                $view_img = "<a href=\"{$img_url}\" onclick=\"return loadThumb('{$thumb_url}','{$thumb_id}')\"{$_conf['ext_win_target_at']}>{$img_tag}</a><a href=\"{$img_url}\"{$_conf['ext_win_target_at']}>{$str}</a>";
            }

            // �\�[�X�ւ̃����N��ime�t���ŕ\��
            if ($_conf['expack.ic2.enabled'] && $_conf['expack.ic2.through_ime']) {
                $ime_url = P2Util::throughIme($url);
                if ($_conf['iframe_popup'] == 3) {
                    $ime_mark = '<img src="img/ime.png" width="22" height="12" alt="">';
                } else {
                    $ime_mark = '[ime]';
                }
                $view_img .= " <a class=\"img_through_ime\" href=\"{$ime_url}\"{$_conf['ext_win_target_at']}>{$ime_mark}</a>";
            }

            $view_img .= '<img class="ic2-info-opener" src="img/s2a.png" width="16" height="16" onclick="ic2info.show('
                       . (($img_id) ? $img_id : "'{$url_ht}'") . ', event)">';

            return $view_img;
        }

        return false;
    }

    /**
     * �u���摜URL+ImageCache2
     */
    function plugin_replaceImageURL($url, $purl, $str)
    {
        global $_conf;
        global $pre_thumb_unlimited, $pre_thumb_ignore_limit, $pre_thumb_limit;
        static $serial = 0;

        // +Wiki
        global $replaceimageurl;
        $url = $purl[0];
        $replaced = $replaceimageurl->replaceImageURL($url);
        if (!$replaced[0]) return FALSE;

        foreach($replaced as $v) {
            $url_en = rawurlencode($v['url']);
            $url_ht = htmlspecialchars($v['url'], ENT_QUOTES);
            $ref_en = $v['referer'] ? '&amp;ref=' . rawurlencode($v['referer']) : '';

            // ����
            $serial++;
            $thumb_id = 'thumbs' . $serial . $this->thumb_id_suffix;
            $tmp_thumb = './img/ic_load.png';

            $icdb = new IC2_DataObject_Images;

            // r=0:�����N;r=1:���_�C���N�g;r=2:PHP�ŕ\��
            // t=0:�I���W�i��;t=1:PC�p�T���l�C��;t=2:�g�їp�T���l�C��;t=3:���ԃC���[�W
            // +Wiki
            $img_url = 'ic2.php?r=1&amp;uri=' . $url_en . $ref_en;
            $thumb_url = 'ic2.php?r=1&amp;t=1&amp;uri=' . $url_en . $ref_en;

            // DB�ɉ摜��񂪓o�^����Ă����Ƃ�
            if ($icdb->get($v['url'])) {

                // �E�B���X�Ɋ������Ă����t�@�C���̂Ƃ�
                if ($icdb->mime == 'clamscan/infected') {
                    $result .= "<img class=\"thumbnail\" src=\"./img/x04.png\" width=\"32\" height=\"32\" hspace=\"4\" vspace=\"4\" align=\"middle\">";
                    continue;
                }
                // ���ځ[��摜�̂Ƃ�
                if ($icdb->rank < 0) {
                    $result .= "<img class=\"thumbnail\" src=\"./img/x01.png\" width=\"32\" height=\"32\" hspace=\"4\" vspace=\"4\" align=\"middle\">";
                    continue;
                }

                // �I���W�i�����L���b�V������Ă���Ƃ��͉摜�𒼐ړǂݍ���
                $_img_url = $this->thumbnailer->srcPath($icdb->size, $icdb->md5, $icdb->mime);
                if (file_exists($_img_url)) {
                    $img_url = $_img_url;
                    $cached = true;
                } else {
                    $cached = false;
                }

                // �T���l�C�����쐬����Ă��Ă���Ƃ��͉摜�𒼐ړǂݍ���
                $_thumb_url = $this->thumbnailer->thumbPath($icdb->size, $icdb->md5, $icdb->mime);
                if (file_exists($_thumb_url)) {
                    $thumb_url = $_thumb_url;
                    // �����X���^�C�����@�\��ON�ŃX���^�C���L�^����Ă��Ȃ��Ƃ���DB���X�V
                    if (!is_null($this->img_memo) && strpos($icdb->memo, $this->img_memo) === false){
                        $update = new IC2_DataObject_Images;
                        if (!is_null($icdb->memo) && strlen($icdb->memo) > 0) {
                            $update->memo = $this->img_memo . ' ' . $icdb->memo;
                        } else {
                            $update->memo = $this->img_memo;
                        }
                        $update->whereAddQuoted('uri', '=', $v['url']);
                        $update->update();
                    }
                }

                // �T���l�C���̉摜�T�C�Y
                $thumb_size = $this->thumbnailer->calc($icdb->width, $icdb->height);
                $thumb_size = preg_replace('/(\d+)x(\d+)/', 'width="$1" height="$2"', $thumb_size);
                $tmp_thumb = './img/ic_load1.png';

                $orig_img_url   = $img_url;
                $orig_thumb_url = $thumb_url;

            // �摜���L���b�V������Ă��Ȃ��Ƃ�
            // �����X���^�C�����@�\��ON�Ȃ�N�G����UTF-8�G���R�[�h�����X���^�C���܂߂�
            } else {
                // �摜���u���b�N���X�gor�G���[���O�ɂ��邩�m�F
                if (false !== ($errcode = $icdb->ic2_isError($v['url']))) {
                    $result .= "<img class=\"thumbnail\" src=\"./img/{$errcode}.png\" width=\"32\" height=\"32\" hspace=\"4\" vspace=\"4\" align=\"middle\">";
                    continue;
                }

                $cached = false;


                $orig_img_url   = $img_url;
                $orig_thumb_url = $thumb_url;
                $img_url .= $this->img_memo_query;
                $thumb_url .= $this->img_memo_query;
                $thumb_size = '';
                $tmp_thumb = './img/ic_load2.png';
            }

            // �L���b�V������Ă��炸�A�\�����������L���̂Ƃ�
            if (!$cached && !$pre_thumb_unlimited && !$pre_thumb_ignore_limit) {
                // �\�������𒴂��Ă�����A�\�����Ȃ�
                // �\�������𒴂��Ă��Ȃ���΁A�\�������J�E���^��������
                if ($pre_thumb_limit <= 0) {
                    $show_thumb = false;
                } else {
                    $show_thumb = true;
                    $pre_thumb_limit--;
                }
            } else {
                $show_thumb = true;
            }

            // �\�����[�h
            if ($show_thumb) {
                $img_tag = "<img class=\"thumbnail\" src=\"{$thumb_url}\" {$thumb_size} hspace=\"4\" vspace=\"4\" align=\"middle\">";
                if ($_conf['iframe_popup']) {
                    $view_img = $this->imageHtmlPopup($img_url, $img_tag, '');
                } else {
                    $view_img = "<a href=\"{$img_url}\"{$_conf['ext_win_target_at']}>{$img_tag}</a>";
                }
            } else {
                $img_tag = "<img id=\"{$thumb_id}\" class=\"thumbnail\" src=\"{$tmp_thumb}\" width=\"32\" height=\"32\" hspace=\"4\" vspace=\"4\" align=\"middle\">";
                $view_img = "<a href=\"{$img_url}\" onclick=\"return loadThumb('{$thumb_url}','{$thumb_id}')\"{$_conf['ext_win_target_at']}>{$img_tag}</a><a href=\"{$img_url}\"{$_conf['ext_win_target_at']}></a>";
            }

            $view_img .= '<img class="ic2-info-opener" src="img/s2a.png" width="16" height="16" onclick="ic2info.show('
                    //. "'{$url_ht}', '{$orig_img_url}', '{$_conf['ext_win_target']}', '{$orig_thumb_url}', event)\">";
                      . "'{$url_ht}', event)\">";

            $result .= $view_img;
        }
        // �\�[�X�ւ̃����N��ime�t���ŕ\��
        $ime_url = P2Util::throughIme($url);
        $result .= "<a class=\"img_through_ime\" href=\"{$ime_url}\"{$_conf['ext_win_target_at']}>{$str}</a>";
        return $result;
    }

    /**
     * +Wiki:�����N�v���O�C��
     */
    function plugin_linkPlugin($url, $purl, $str)
    {
        global $linkplugin;
        return $linkplugin->replaceLinkToHTML($url, $str);
    }

    // }}}
    // {{{ plugin_imepita_to_imageCache2()

    /**
     * imepita��URL�����H����ImageCache2������v���O�C��
     *
     * @param   string $url
     * @param   array $purl
     * @param   string $str
     * @return  string|false
     */
    public function plugin_imepita_to_imageCache2($url, $purl, $str)
    {
        if (preg_match('{^https?://imepita\.jp/(?:image/)?(\d{8}/\d{6})}i',
                $purl[0], $m) && empty($purl['query'])) {
            $_url = 'http://imepita.jp/image/' . $m[1];
            $_purl = @parse_url($_url);
            $_purl[0] = $_url;
            return $this->plugin_imageCache2($_url, $_purl, $str, true, $url);
        }
        return false;
    }

    // }}}
    // }}}

    public function get_quotebacks_json() {
        if ($this->_quote_from === null) {
            $this->_make_quote_from();  // �탌�X�f�[�^�W�v
        }
        $ret = array();
        foreach ($this->_quote_from as $resnum => $quotee_lists) {
            if (!$quotee_lists) continue;
            if ($resnum != 1 && ($resnum < $this->thread->resrange['start'] || $resnum > $this->thread->resrange['to'])) continue;
            $tmp = array();
            foreach ($quotee_lists as $quote) {
                if ($quote != 1 && ($quote < $this->thread->resrange['start'] || $quote > $this->thread->resrange['to'])) continue;
                $tmp[] = $quote;
            }
            if ($tmp) $ret[] = "{$resnum}:[" . join(',', $tmp) . "]";
        }
        return '{' . join(',', $ret) . '}';
    }

    public function getResColorJs() {
        global $_conf, $STYLE;
        $fontstyle_bold = empty($STYLE['fontstyle_bold']) ? 'normal' : $STYLE['fontstyle_bold'];
        $fontweight_bold = empty($STYLE['fontweight_bold']) ? 'normal' : $STYLE['fontweight_bold'];
        $fontfamily_bold = $STYLE['fontfamily_bold'];
        $backlinks = $this->get_quotebacks_json();
        $colors = array();
        $backlink_colors = join(',',
            array_map(create_function('$x', 'return "\'{$x}\'";'),
                explode(',', $_conf['backlink_coloring_track_colors']))
        );
        $prefix = $this->_matome ? "t{$this->_matome}" : '';
        return <<<EOJS
<script type="text/javascript">
if (typeof rescolObjs == 'undefined') rescolObjs = [];
rescolObjs.push((function() {
    var obj = new BacklinkColor('{$prefix}');
    obj.colors = [{$backlink_colors}];
    obj.highlightStyle = {fontStyle :'{$fontstyle_bold}', fontWeight : '{$fontweight_bold}', fontFamily : '{$fontfamily_bold}'};
    obj.backlinks = {$backlinks};
    return obj;
})());
</script>
EOJS;
    }

    public function get_ids_for_render_json() {
        $ret = array();
        if ($this->_ids_for_render) {
            foreach ($this->_ids_for_render as $id => $count) {
                $ret[] = "'{$id}':{$count}";
            }
        }
        return '{' . join(',', $ret) . '}';
    }

    public function getIdColorJs() {
        global $_conf, $STYLE;
        if ($_conf['coloredid.enable'] < 1 || $_conf['coloredid.click'] < 1)
            return '';
        if (count($this->thread->idcount) < 1) return;

        $idslist = $this->get_ids_for_render_json();

        $rate = $_conf['coloredid.rate.times'];
        $tops = $this->getIdCountRank(10);
        $average = $this->getIdCountAverage();
        $color_init = '';
        if ($_conf['coloredid.rate.type'] > 0) {
            switch($_conf['coloredid.rate.type']) {
            case 2:
                $init_rate = $tops;
                break;
            case 3:
                $init_rate = $average;
                break;
            case 1:
                $init_rate = $rate;
            default:
            }
            if ($init_rate > 1)
                $color_init .= 'idCol.initColor(' . $init_rate . ', idslist);';
        }
        $color_init .= "idCol.rate = {$rate};";
        if (!$this->_matome) {
            $color_init .= "idCol.tops = {$tops};";
            $color_init .= "idCol.average = {$average};";
        }
        $hissiCount = $_conf['coloredid.rate.hissi.times'];
        $mark_colors = join(',',
            array_map(create_function('$x', 'return "\'{$x}\'";'),
                explode(',', $_conf['coloredid.marking.colors']))
        );
        $fontstyle_bold = empty($STYLE['fontstyle_bold']) ? 'normal' : $STYLE['fontstyle_bold'];
        $fontweight_bold = empty($STYLE['fontweight_bold']) ? 'normal' : $STYLE['fontweight_bold'];
        $fontfamily_bold = $STYLE['fontfamily_bold'];
        $uline = $STYLE['a_underline_none'] != 1
            ? 'idCol.colorStyle["textDecoration"] = "underline"' : '';
        return <<<EOJS
<script>
(function() {
var idslist = {$idslist};
if (typeof idCol == 'undefined') {
    idCol = new IDColorChanger(idslist, {$hissiCount});
    idCol.colors = [{$mark_colors}];
{$uline};
    idCol.highlightStyle = {fontStyle :'{$fontstyle_bold}', fontWeight : '{$fontweight_bold}', fontFamily : '{$fontfamily_bold}', fontSize : '104%'};
} else idCol.addIdlist(idslist);
{$color_init}
idCol.setupSPM('{$this->spmObjName}');
})();
</script>
EOJS;
    }

    public function getIdCountAverage() {
        if ($this->_idcount_average !== null) return $this->_idcount_average;
        $sum = 0; $param = 0;
        foreach ($this->thread->idcount as $count) {
            if ($count > 1) {
                $sum += $count;
                $param++;
            }
        }
        return $this->_idcount_average = $param < 1 ? 0 : ceil($sum / $param);
    }

    public function getIdCountRank($rank) {
        if ($this->_idcount_tops !== null) return $this->_idcount_tops;
        $ranking = array();
        foreach ($this->thread->idcount as $count) {
            if ($count > 1) $ranking[] = $count;
        }
        if (count($ranking) == 0) return 0;
        rsort($ranking);
        $result = count($ranking) >= $rank ? $ranking[$rank - 1] : $ranking[count($ranking) - 1];
        return $this->_idcount_tops = $result;
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
