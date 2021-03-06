<?php
/**
 * rep2 - スレッドを表示する クラス PC用
 */

require_once P2EX_LIB_DIR . '/ExpackLoader.php';

ExpackLoader::loadAAS();
ExpackLoader::loadActiveMona();
ExpackLoader::loadImageCache();

// {{{ ShowThreadPc

class ShowThreadPc extends ShowThread
{
    // {{{ properties

    static private $_spm_objects = array();

    private $_quote_res_nums_checked; // ポップアップ表示されるチェック済みレス番号を登録した配列
    private $_quote_res_nums_done; // ポップアップ表示される記録済みレス番号を登録した配列
    private $_quote_check_depth; // レス番号チェックの再帰の深さ checkQuoteResNums()

    private $_ids_for_render;   // 出力予定のID(重複のみ)のリスト(8桁)
    private $_idcount_average;  // ID重複数の平均値
    private $_idcount_tops;     // ID重複数のトップ入賞までの重複数値

    public $am_autodetect = false; // AA自動判定をするか否か
    public $am_side_of_id = false; // AAスイッチをIDの横に表示する
    public $am_on_spm = false; // AAスイッチをSPMに表示する

    public $asyncObjName;  // 非同期読み込み用JavaScriptオブジェクト名
    public $spmObjName; // スマートポップアップメニュー用JavaScriptオブジェクト名

    // }}}
    // {{{ constructor

    /**
     * コンストラクタ
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

        // imepitaのURLを加工してImageCache2させるプラグインを登録
        if (P2_IMAGECACHE_AVAILABLE == 2) {
            $this->addURLHandler(array($this, 'plugin_imepita_to_imageCache2'));
        }

        // サムネイル表示制限数を設定
        if (!isset($GLOBALS['pre_thumb_unlimited']) || !isset($GLOBALS['pre_thumb_limit'])) {
            if (isset($_conf['pre_thumb_limit']) && $_conf['pre_thumb_limit'] > 0) {
                $GLOBALS['pre_thumb_limit'] = $_conf['pre_thumb_limit'];
                $GLOBALS['pre_thumb_unlimited'] = FALSE;
            } else {
                $GLOBALS['pre_thumb_limit'] = NULL; // ヌル値だとisset()はFALSEを返す
                $GLOBALS['pre_thumb_unlimited'] = TRUE;
            }
        }
        $GLOBALS['pre_thumb_ignore_limit'] = FALSE;

        // アクティブモナー初期化
        if (P2_ACTIVEMONA_AVAILABLE) {
            ExpackLoader::initActiveMona($this);
        }

        // ImageCache2初期化
        if (P2_IMAGECACHE_AVAILABLE == 2) {
            ExpackLoader::initImageCache($this);
        }

        // 非同期レスポップアップ・SPM初期化
        $js_id = sprintf('%u', crc32($this->thread->keydat));
        if ($this->_matome) {
            $this->asyncObjName = "t{$this->_matome}asp{$js_id}";
            $this->spmObjName = "t{$this->_matome}spm{$js_id}";
        } else {
            $this->asyncObjName = "asp{$js_id}";
            $this->spmObjName = "spm{$js_id}";
        }

        // 名無し初期化
        $this->setBbsNonameName();
    }

    // }}}
    // {{{ transRes()

    /**
     * DatレスをHTMLレスに変換する
     *
     * @param   string  $ares   datの1ライン
     * @param   int     $i      レス番号
     * @return  string			HTML
	 *			array	'body'	レス本体のHTML
	 *					'q'		ポップアップのHTML
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

        // {{{ フィルタリング
        if (isset($_REQUEST['word']) && strlen($_REQUEST['word']) > 0) {
            if (strlen($GLOBALS['word_fm']) <= 0) {
                return '';
            // ターゲット設定（空のときはフィルタリング結果に含めない）
            } elseif (!$target = $this->getFilterTarget($ares, $i, $name, $mail, $date_id, $msg)) {
                return '';
            // マッチング
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
        $msg_class = 'message ' . $msg_id;



        // NGあぼーんチェック
        $ng_type = $this->_ngAbornCheck($i, strip_tags($name), $mail, $date_id, $id, $msg, false, $ng_info);
        if ($ng_type == self::ABORN) {
            return "<a name=\"{$res_id}\"></a>" . $this->_abornedRes($res_id);
        }
        if ($ng_type != self::NG_NONE) {
            $ngaborns_head_hits = self::$_ngaborns_head_hits;
            $ngaborns_body_hits = self::$_ngaborns_body_hits;
        }
		// +live ハイライトチェック
		if ($ng_type != self::HIGHLIGHT_NONE) {
			$highlight_head_hits = self::$_highlight_head_hits;
			$highlight_body_hits = self::$_highlight_body_hits;
		}

        // AA判定
        if ($this->am_autodetect && $this->activeMona->detectAA($msg)) {
            $msg_class .= ' ActiveMona';
        }

        //=============================================================
        // レスをポップアップ表示
        //=============================================================
        if ($_conf['quote_res_view']) {
            $this->_quote_check_depth = 0;
            $popup_res_nums = array_keys($this->checkQuoteResNums($i, $name, $msg));

            foreach ($popup_res_nums as $rnv) {
                if (!isset($this->_quote_res_nums_done[$rnv])) {
                    $this->_quote_res_nums_done[$rnv] = true;
                    if (isset($this->thread->datlines[$rnv-1])) {
                        if ($this->_matome) {
                            $qres_id = "t{$this->_matome}qr{$rnv}";
                        } else {
                            $qres_id = "qr{$rnv}";
                        }
                        $ds = $this->qRes($this->thread->datlines[$rnv-1], $rnv);
                        $onPopUp_at = " onmouseover=\"showResPopUp('{$qres_id}',event,this)\" onmouseout=\"hideResPopUp('{$qres_id}',this)\"";
                        $rpop .= "<div id=\"{$qres_id}\" class=\"respopup\"{$onPopUp_at}>\n{$ds}</div>\n";
                    }
                }
            }
        }

        //=============================================================
        // まとめて出力
        //=============================================================

        $name = $this->transName($name); // 名前HTML変換
        $msg = $this->transMsg($msg, $i); // メッセージHTML変換


        // BEプロファイルリンク変換
        $date_id = $this->replaceBeId($date_id, $i);

        // HTMLポップアップ
        if ($_conf['iframe_popup']) {
            $date_id = preg_replace_callback("{<a href=\"(http://[-_.!~*()0-9A-Za-z;/?:@&=+\$,%#]+)\"({$_conf['ext_win_target_at']})>((\?#*)|(Lv\.\d+))</a>}", array($this, 'iframePopupCallback'), $date_id);
        }

        // NGメッセージ変換
        if ($ng_type != self::NG_NONE && count($ng_info)) {
            $ng_info = implode(', ', $ng_info);
            $msg = <<<EOMSG
<span class="ngword" onclick="show_ng_message('ngm{$ngaborns_body_hits}', this);">{$ng_info}</span>
<div id="ngm{$ngaborns_body_hits}" class="ngmsg ngmsg-by-msg">{$msg}</div>
EOMSG;
        }

        // NGネーム変換
        if ($ng_type & self::NG_NAME) {
            $name = <<<EONAME
<span class="ngword" onclick="show_ng_message('ngn{$ngaborns_head_hits}', this);">{$name}</span>
EONAME;
            $msg = <<<EOMSG
<div id="ngn{$ngaborns_head_hits}" class="ngmsg ngmsg-by-name">{$msg}</div>
EOMSG;

        // NGメール変換
        } elseif ($ng_type & self::NG_MAIL) {
            $mail = <<<EOMAIL
<span class="ngword" onclick="show_ng_message('ngn{$ngaborns_head_hits}', this);">{$mail}</span>
EOMAIL;
            $msg = <<<EOMSG
<div id="ngn{$ngaborns_head_hits}" class="ngmsg ngmsg-by-mail">{$msg}</div>
EOMSG;

        // NGID変換
        } elseif ($ng_type & self::NG_ID) {
            $date_id = <<<EOID
<span class="ngword" onclick="show_ng_message('ngn{$ngaborns_head_hits}', this);">{$date_id}</span>
EOID;
            $msg = <<<EOMSG
<div id="ngn{$ngaborns_head_hits}" class="ngmsg ngmsg-by-id">{$msg}</div>
EOMSG;

        }

		// +live ハイライトワード変換
		include (P2_LIB_DIR . '/live/live_highlight_convert.php');
        /*
        //「ここから新着」画像を挿入
        if ($i == $this->thread->readnum +1) {
            $tores .= <<<EOP
                <div><img src="img/image.png" alt="新着レス" border="0" vspace="4"></div>
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

        if ($_conf['backlink_block'] > 0) {
            // 被参照ブロック表示用にonclickを設定
            $tores .= "<div id=\"{$res_id}\" class=\"res {$res_id}\" onclick=\"toggleResBlk(event, this, " . $_conf['backlink_block_readmark'] . ")\">\n";
        } else {
            $tores .= "<div id=\"{$res_id}\" class=\"res {$res_id}\">\n";
        }
		$tores .="<a name=\"{$res_id}\"></a>";

		// 被参照ブロック表示で本体レスが非表示になった時に自分をポップアップ表示
        if ($_conf['quote_res_view'] && ($_conf['quote_res_view_ng'] != 0 ||
                !in_array($qnum, $this->_ng_nums))) {

            if ($this->_matome) {
                $qres_id = "t{$this->_matome}qr{$i}";
            } else {
                $qres_id = "qr{$i}";
            }
            $attributes = " onmouseover=\"showResPopUp('{$qres_id}',event,this)\"";
            $attributes .= " onmouseout=\"hideResPopUp('{$qres_id}',this)\"";
        }
		// 自己ポップアップ用にレス番号を変換
		$num = (string) $i;
		if ($_conf['iframe_popup'] == 3) {
			$num_ht = ' <img src="img/ida.png" width="2" height="12" alt="">';
			$num_ht .= preg_replace('/\\d/', '<img src="img/id\\0.png" height="12" alt="">', $num);
			$num_ht .= '<img src="img/idz.png" width="2" height="12" alt=""> ';
		} else {
			$num_ht = '('.$num.')';
		}

		$selfPopupUrl=$this->quoteRes(array('num1'=>$i),true);
		$selfPopup=$this->iframePopup($selfPopupUrl, $num_ht, $attributes,1);
        $tores .= "<div class=\"popupself\">{$selfPopup}</div>";
		// 自己ポップアップ処理終了

        $tores .= "<div class=\"res-header\">";

        if ($this->thread->onthefly) {
            $GLOBALS['newres_to_show_flag'] = true;
            //番号（オンザフライ時）
            $tores .= "<span class=\"ontheflyresorder spmSW\"{$spmeh}>{$i}</span> : ";
        } elseif ($i > $this->thread->readnum) {
            $GLOBALS['newres_to_show_flag'] = true;
            // 番号（新着レス時）
            $tores .= "<span class=\"spmSW newres\"{$spmeh}>{$i}</span> : ";
        } elseif ($_conf['expack.spm.enabled']) {
            // 番号（SPM）
            $tores .= "<span class=\"spmSW\"{$spmeh}>{$i}</span> : ";
        } else {
            // 番号
            $tores .= "{$i} : ";
        }


        // 名前
        $tores .= preg_replace('{<b>[ ]*</b>}i', '', "<span class=\"name\"><b>{$name}</b></span> : ");

        // メール
        if ($mail) {
            if (strpos($mail, 'sage') !== false && $STYLE['read_mail_sage_color']) {
                $tores .= "<span class=\"sage\">{$mail}</span> : ";
            } elseif ($STYLE['read_mail_color']) {
                $tores .= "<span class=\"mail\">{$mail}</span> : ";
            } else {
                $tores .= $mail . ' : ';
            }
        }

        // IDフィルタ
        if ($_conf['flex_idpopup'] == 1 && $id && $this->thread->idcount[$id] > 1) {
            $date_id = str_replace($idstr, $this->idFilter($idstr, $id), $date_id);
        }

        $tores .= $date_id; // 日付とID
        if ($this->am_side_of_id) {
            $tores .= ' ' . $this->activeMona->getMona($msg_id);
        }
        $tores .= "</div>\n"; // res-headerを閉じる

        // 被レスリスト(縦形式)
        if ($_conf['backlink_list'] == 1 || $_conf['backlink_list'] > 2) {
            $tores .= $this->quoteback_list_html($i, 1);
        }

        $tores .= "<div id=\"{$msg_id}\" class=\"{$msg_class}\">{$msg}</div>\n"; // 内容

		$tores2="";
        // 被レスリスト(横形式)
        if ($_conf['backlink_list'] == 2 || $_conf['backlink_list'] > 2) {
            $tores2 .= $this->quoteback_list_html($i, 2,false);
        }

        // 被レス展開用ブロック
        if ($_conf['backlink_block'] > 0) {
            $backlinks = $this->backlink_comment($i);
            $tores2 .= $backlinks;
            if (strlen($backlinks)) {
                $tores .= '<img class="buttonblock" src="img/btn_plus.gif" width="15" height="15" align="left">';
				$tores .= $tores2;
                $tores .= '<div class="resblock"></div>';
            }
        }
        $tores .= "<p></div>\n";
//		$tores=preg_replace('/(class="F(?:t\d+)?)qr/',"$1r",$tores);

//        $tores .= $rpop; // レスポップアップ用引用
        /*if ($_conf['expack.am.enabled'] == 2) {
            $tores .= <<<EOJS
<script type="text/javascript">
//<![CDATA[
detectAA("{$msg_id}");
//]]>
</script>\n
EOJS;
        }*/

        // まとめてフィルタ色分け
        if (!empty($GLOBALS['word_fm']) && $res_filter['match'] != 'off') {
            $tores = StrCtl::filterMarking($GLOBALS['word_fm'], $tores);
        }

        return array('body' => $tores, 'q' => $rpop);
    }

    // }}}
    // {{{ quoteOne()

    /**
     * >>1 を表示する (引用ポップアップ用)
     */
    public function quoteOne()
    {
        global $_conf;

        if (!$_conf['quote_res_view']) {
            return false;
        }

        $rpop = '';
        $this->_quote_check_depth = 0;
        $popup_res_nums = array_keys($this->checkQuoteResNums(0, '1', ''));

        foreach ($popup_res_nums as $rnv) {
            if (!isset($this->_quote_res_nums_done[$rnv])) {
                $this->_quote_res_nums_done[$rnv] = true;
                if (isset($this->thread->datlines[$rnv-1])) {
                    if ($this->_matome) {
                        $qres_id = "t{$this->_matome}qr{$rnv}";
                    } else {
                        $qres_id = "qr{$rnv}";
                    }
                    $ds = $this->qRes($this->thread->datlines[$rnv-1], $rnv);
                    $onPopUp_at = " onmouseover=\"showResPopUp('{$qres_id}',event,this)\" onmouseout=\"hideResPopUp('{$qres_id}',this)\"";
                    $rpop .= "<div id=\"{$qres_id}\" class=\"respopup\"{$onPopUp_at}>\n{$ds}</div>\n";
                }
            }
        }

		return array('body'=>$this->transMsg('&gt;&gt;1', 1),'q'=>$rpop);
    }

    // }}}
    // {{{ qRes()

    /**
     * レス引用HTML
     */
    public function qRes($ares, $i)
    {
        global $_conf;

        $resar = $this->thread->explodeDatLine($ares);
        $name = $this->transName($resar[0]);
        $mail = $resar[1];
        if (($id = $this->thread->ids[$i]) !== null) {
            $idstr = $this->thread->idp[$i] . $id;
            $date_id = str_replace($this->thread->idp[$i] . $id, $idstr, $resar[2]);
        } else {
            $idstr = null;
            $date_id = $resar[2];
        }
        $msg = $this->transMsg($resar[3], $i);

		// レスアンカーのクラス名を変更
		// 呼び出し元をレス本体からレスポップアップにする
		$msg = preg_replace('/(class="F[^q]*)(r\d+)/',"$1q$2",$msg);

        $tores = '';

        if ($this->_matome) {
            $res_id = "t{$this->_matome}r{$i}";
            $msg_id = "t{$this->_matome}m{$i}";
            $qmsg_id = "t{$this->_matome}qm{$i}";
        } else {
            $res_id = "r{$i}";
            $msg_id = "m{$i}";
            $qmsg_id = "qm{$i}";
        }

        // >>1
        if ($i == 1) {
            $tores = "<h4 class=\"thread_title\">{$this->thread->ttitle_hd}</h4>";
        }

        // BEプロファイルリンク変換
        $date_id = $this->replaceBeId($date_id, $i);

        // NGあぼーんチェック
        $ng_type = $this->_ngAbornCheck($i, strip_tags($name), $mail, $date_id, $id, $msg, false, $ng_info);
		// +live ハイライトワード変換
		include (P2_LIB_DIR . '/live/live_highlight_convert.php');

        // HTMLポップアップ
        if ($_conf['iframe_popup']) {
            $date_id = preg_replace_callback("{<a href=\"(http://[-_.!~*()0-9A-Za-z;/?:@&=+\$,%#]+)\"({$_conf['ext_win_target_at']})>((\?#*)|(Lv\.\d+))</a>}", array($this, 'iframePopupCallback'), $date_id);
        }
        //

        // IDフィルタ
        if ($_conf['flex_idpopup'] == 1 && $id && $this->thread->idcount[$id] > 1) {
            $date_id = str_replace($idstr, $this->idFilter($idstr, $id), $date_id);
        }

        $msg_class = 'message ' . $qmsg_id;

        // AA 判定
        if ($this->am_autodetect && $this->activeMona->detectAA($resar[3])) {
            $msg_class .= ' ActiveMona';
//            $msg_class .= ' pre';
        }

        // SPM
        if ($_conf['expack.spm.enabled']) {
            $spmeh = " onmouseover=\"{$this->spmObjName}.show({$i},'{$qmsg_id}',event)\"";
            $spmeh .= " onmouseout=\"{$this->spmObjName}.hide(event)\"";
        } else {
            $spmeh = '';
        }

		// レス本体にジャンプするURLを生成
        if ($this->isFiltered($i) &&
			( 
				($i == 1 && !$this->thread->resrange['nofirst']) ||
				($i >= $this->thread->resrange['start'] && $i <= $this->thread->resrange['to'])
			)
 		) {
            $read_url = '#' . $res_id;
			$tagname='name="linkfrom"';
        } else {
            $read_url = "{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;offline=1&amp;ls={$i}";
		}

        // $toresにまとめて出力
        $tores .= '<div class="res-header">';
        $tores .= "<span class=\"spmSW\"{$spmeh}><a href=\"{$read_url}\" {$tagname}>{$i}</a></span> : "; // 番号
        $tores .= preg_replace('{<b>[ ]*</b>}i', '', "<b>{$name}</b> : ");
        if ($mail) {
            $tores .= $mail . ' : '; // メール
        }
        $tores .= $date_id; // 日付とID
        if ($this->am_side_of_id) {
            $tores .= ' ' . $this->activeMona->getMona($qmsg_id);
        }
        $tores .= "</div>\n";

        // 被レスリスト(縦形式)
        if ($_conf['backlink_list'] == 1 || $_conf['backlink_list'] > 2) {
            $tores .= $this->quoteback_list_html($i, 1);
        }

        $tores .= "<div id=\"{$qmsg_id}\" class=\"{$msg_class}\">{$msg}</div>\n"; // 内容
        // 被レスリスト(横形式)
        if ($_conf['backlink_list'] == 2 || $_conf['backlink_list'] > 2) {
			// 被参照レスリストのレスアンカーのクラス名を変更
			// 呼び出し元をレス本体からレスポップアップにする
            $tores .= preg_replace('/(class="F(?:t\d+)?)r/',"$1qr",$this->quoteback_list_html($i, 2));
        }

        // 被参照ブロック用データ
        if ($_conf['backlink_block'] > 0) {
            $tores .= $this->backlink_comment($i);
        }

        return $tores;
    }

    public function backlink_comment($i)
    {
        $backlinks = $this->quoteback_list_html($i, 3);
        if (strlen($backlinks)) {
            return '<!-- backlinks:' . $backlinks . ' -->';
        }
        return '';
    }

    // }}}
    // {{{ transName()

    /**
     * 名前をHTML用に変換する
     *
     * @param   string  $name   名前
     * @return  string
     */
    public function transName($name)
    {
        global $_conf;

        // トリップやホスト付きなら分解する
        if (($pos = strpos($name, '◆')) !== false) {
            $trip = substr($name, $pos);
            $name = substr($name, 0, $pos);
        } else {
            $trip = null;
        }

        // 数字を引用レスポップアップリンク化
        if ($_conf['quote_res_view']) {
            if (strlen($name) && $name != $this->BBS_NONAME_NAME) {
				try{
		            $name = preg_replace_callback(
		                $this->getAnchorRegex('/((?P<prefix>%prefix2%)|%line_prefix%)%nums%(?(line_prefix)%line_suffix%)/'),
		                array($this, 'quote_name_callback'), $name
		            );
				} catch (Exception $e) {
					trigger_error(
						"正規表現が不正です。<br>".$e->getMessage(),E_USER_ERROR
					);
				}
            }
        }

        if ($trip) {
            $name .= $trip;
        } elseif ($name) {
            // 文字化け回避
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
     * datのレスメッセージをHTML表示用メッセージに変換する
     *
     * @param   string  $msg    メッセージ
     * @param   int     $mynum  レス番号
     * @return  string
     */
    public function transMsg($msg, $mynum)
    {
        global $_conf;
        global $pre_thumb_ignore_limit;

        // 2ch旧形式のdat
        if ($this->thread->dat_type == '2ch_old') {
            $msg = str_replace('＠｀', ',', $msg);
            $msg = preg_replace('/&amp(?=[^;])/', '&', $msg);
        }

        // セミコロンのない実体参照を修正
        $msg = preg_replace("/(&#\d{3,5});?/","$1;",$msg);

        // &補正
        $msg = preg_replace('/&(?!#?\\w+;)/', '&amp;', $msg);

        // Safariから投稿されたリンク中チルダの文字化け補正
        //$msg = preg_replace('{(h?t?tp://[\w\.\-]+/)〜([\w\.\-%]+/?)}', '$1~$2', $msg);

        // >>1のリンクをいったん外す
        // <a href="../test/read.cgi/accuse/1001506967/1" target="_blank">&gt;&gt;1</a>
        $msg = preg_replace('{<[Aa] .+?>(&gt;&gt;\\d[\\d\\-]*)</[Aa]>}', '$1', $msg);

        // 本来は2chのDAT時点でなされていないとエスケープの整合性が取れない気がする。（URLリンクのマッチで副作用が出てしまう）
        //$msg = str_replace(array('"', "'"), array('&quot;', '&#039;'), $msg);

        // 2006/05/06 ノートンの誤反応対策 body onload=window()
        $msg = str_replace('onload=window()', '<i>onload=window</i>()', $msg);

        // 新着レスの画像は表示制限を無視する設定なら
        if ($mynum > $this->thread->readnum && $_conf['expack.ic2.newres_ignore_limit']) {
            $pre_thumb_ignore_limit = TRUE;
        }

        // 文末の改行と連続する改行を除去
        if ($_conf['strip_linebreaks']) {
            $msg = $this->stripLineBreaks($msg /*, ' <br><span class="stripped">***</span><br> '*/);
        }

        // 引用やURLなどをリンク
		$this->opnum=$mynum;
        $msg = $this->transLink($msg);

        // Wikipedia記法への自動リンク
        if ($_conf['link_wikipedia']) {
            $msg = $this->wikipediaFilter($msg);
        }

        return $msg;
    }

    // }}}
    // {{{ _abornedRes()

    /**
     * あぼーんレスのHTMLを取得する
     *
     * @param  string $res_id
     * @return string
     */
    protected function _abornedRes($res_id)
    {
        global $_conf;
        if ($_conf['ngaborn_purge_aborn']) return '';
        return <<<EOP
<div id="{$res_id}" class="res aborned">
<div class="res-header aborned">&nbsp;</div>
<div class="message aborned">&nbsp;</div>
</div>\n
EOP;
    }

    // }}}
    // {{{ idFilter()

    /**
     * IDフィルタリングポップアップ変換
     *
     * @param   string  $idstr  ID:xxxxxxxxxx
     * @param   string  $id        xxxxxxxxxx
     * @return  string
     */
    public function idFilter($idstr, $id)
    {
        global $_conf;

        // IDは8桁または10桁(+携帯/PC識別子)と仮定して
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

        if ($_conf['coloredid.enable'] > 0 && preg_match("|^ID:[ ]?[0-9A-Za-z/.+]{8,11}|",$idstr)) {
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
     * 引用変換（単独）
     *
     * @param   string  $full           >>1-100
     * @param   string  $qsign          >>
     * @param   string  $appointed_num    1-100
     * @return  string
     */
    public function quoteRes(array $s,$urlOnly=false)
    {
        global $_conf;
//		echo "quoteRes<br>";
//				echo nl2br(htmlspecialchars(var_export($s,true)))."<br>";
		$full=$s[0];
		$qsign=$s['prefix'];
		$qnum=intval(
			preg_replace("/\s/",'',
				mb_convert_kana($s['num1'], 'ns')
			)
		);	// 全角の数字とスペースを半角に変換しつつスペース削除


        if ($s['num2']) {
			$to=intval(
				preg_replace("/\s/",'',
					mb_convert_kana($s['num2'], 'ns')
				)
			);	// 全角の数字とスペースを半角に変換しつつスペース削除
			if ($to < $qnum) {		// 範囲指定が逆順だったら
				return $full;
//				list($qnum,$to)=array($to,$qnum);	// 番号入れ替え
			}
            return $this->quoteResRange($full, $qsign, $qnum, $to);
        }

		$anchor_jump = true;
        if ($qnum < 1 || $qnum > sizeof($this->thread->datlines)) {
            return $full;
        }
        // あぼーんレスへのアンカー
        if ($_conf['quote_res_view_aborn'] == 0 &&
                in_array($qnum, $this->_aborn_nums)) {
            return '<span class="abornanchor" title="あぼーん">' . "{$full}</span>";
        }

		$resnum= $this->get_res_id("r{$qnum}");
		// ($this->_matome ? "t{$this->_matome}" : '') . "r{$qnum}";

		// レスxxxからレスポップアップyyyを呼び出すことを意味する、レスアンカーに付与するクラス名
		// FrxxxTqryyy
		$fromnum ='F' . $this->get_res_id("r{$this->opnum}"); //($this->_matome ? "t{$this->_matome}" : '') . "r{$this->opnum}";
		$fromnum.=' ';
		$fromnum.='T' . $this->get_res_id("qr{$qnum}");

        if ($anchor_jump && $this->isFiltered($qnum) &&
			( 
				($qnum == 1 && !$this->thread->resrange['nofirst']) ||
				($qnum >= $this->thread->resrange['start'] && $qnum <= $this->thread->resrange['to'])
			)
 		) {
            $read_url = '#' . $resnum;
			$tagname='name="linkfrom"';

        } else {
            $read_url = "{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;offline=1&amp;ls={$qnum}";		// 参照先だけのページを開くURL
//        $read_url = "{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;offline=1&amp;ls=all&amp;field=res&amp;word=^{$appointed_num}$&amp;method=regex&amp;match=on&amp;idpopup=0";		// レス番号を検索するURL
        }

        $attributes = $_conf['bbs_win_target_at'];
        if ($_conf['quote_res_view'] && 
			(
				$_conf['quote_res_view_ng'] != 0 
				|| !in_array($qnum, $this->_ng_nums)
			)
		) {

            if ($this->_matome) {
                $qres_id = "t{$this->_matome}qr{$qnum}";
            } else {
                $qres_id = "qr{$qnum}";
            }
            $attributes .= " onmouseover=\"showResPopUp('{$qres_id}',event,this)\"";
            $attributes .= " onmouseout=\"hideResPopUp('{$qres_id}',this)\"";
        }
		$attributes .= " class=\"{$fromnum}\" {$tagname}";
		if ($urlOnly) {
	        return $read_url;
		} else {
	        return "<a href=\"{$read_url}\"{$attributes}"
	            . (in_array($qnum, $this->_aborn_nums) ? ' class="abornanchor"' :
	                (in_array($qnum, $this->_ng_nums) ? ' class="nganchor"' : 'class="anchor"'))
	            . ">{$full}</a>";
		}
    }

    // }}}
    // {{{ quoteResRange()

    /**
     * 引用変換（範囲）
     *
     * @param   string  $full           >>1-100
     * @param   string  $qsign          >>
     * @param   int  $from    1
     * @param   int  $to      100
     * @param   int  $anchor  リンク先URL内のアンカーレス番号
     * @return string
     */
    public function quoteResRange($full, $qsign, $from, $to, $anchor="")
    {
        global $_conf;

        $read_url = "{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;offline=1&amp;ls={$from}-{$to}n";

        if ($_conf['iframe_popup']) {
            $pop_url = $read_url . "&amp;renzokupop=true";
            return $this->iframePopup(array($read_url, $pop_url), $full, $_conf['bbs_win_target_at'], 1);
        }

        // 普通にリンク
        return "<a class=\"anchor\" href=\"{$read_url}\"{$_conf['bbs_win_target_at']}>{$full}</a>";

        // 1つ目を引用レスポップアップ
        /*
        $qnums = explode('-', $appointed_num);
        $qlink = $this->quoteRes(array($qsign . $qnum[0], $qsign, $qnum[0])) . '-';
        if (isset($qnums[1])) {
            $qlink .= $qnums[1];
        }
        return $qlink;
        */
    }

    // }}}
    // {{{ iframePopup()

    /**
     * HTMLポップアップ変換
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

        // リンク用URLとポップアップ用URL
        if (is_array($url)) {
            $link_url = $url[0];
            $pop_url = $url[1];
        } else {
            $link_url = $url;
            $pop_url = $url;
        }

        // リンク文字列とポップアップの印
        if (is_array($str)) {
            $link_str = $str[0];
            $pop_str = $str[1];
        } else {
            $link_str = $str;
            $pop_str = null;
        }

        // リンクの属性
        if (is_array($attr)) {
            $_attr = array();
            foreach ($attr as $key => $value) {
                $_attr[] = ' ' . $key . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
            }
			$attr=implode('',$_attr);
        } elseif ($attr !== '' && substr($attr, 0, 1) != ' ') {
            $attr = ' ' . $attr;
        }

        // リンクの属性にHTMLポップアップ用のイベントハンドラを加える
        $pop_attr = $attr;
        if ($_conf['iframe_popup_event'] == 1) {
            $pop_attr .= " onClick=\"stophide=true; showHtmlPopUp('{$pop_url}',event,0); return false;\"";
        } else {
            $pop_attr .= " onmouseover=\"showHtmlPopUp('{$pop_url}',event,{$_conf['iframe_popup_delay']})\"";
        }
        $pop_attr .= " onmouseout=\"offHtmlPopUp()\"";

        // 最終調整
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

        // リンク作成
        switch ($mode) {
        // マーク無し
        case 1:
            return "<a class=\"anchor\" href=\"{$link_url}\"{$pop_attr}>{$link_str}</a>";
        // (p)マーク
        case 2:
            return "(<a class=\"anchor\" href=\"{$link_url}\"{$pop_attr}>p</a>)<a href=\"{$link_url}\"{$attr}>{$link_str}</a>";
        // [p]画像、サムネイルなど
        case 3:
            return "<a class=\"anchor\" href=\"{$link_url}\"{$pop_attr}>{$pop_str}</a><a href=\"{$link_url}\"{$attr}>{$link_str}</a>";
        // ポップアップしない
        default:
            return "<a class=\"anchor\" href=\"{$link_url}\"{$attr}>{$link_str}</a>";
        }
    }

    // }}}
    // {{{ iframePopupCallback()

    /**
     * HTMLポップアップ変換（コールバック用インターフェース）
     *
     * @param   array   $s  正規表現にマッチした要素の配列
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
		if (!preg_match('([0-9A-Za-z/.+]{8,11}$)',$id)) return $idstr; // ID（8,10桁 +PC/携帯識別フラグ）

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
            if ($i == 0 || $i == 1) {
                $ret[] = '<span class="' . self::cssClassedId($id)
                    . ($i == 0 ? '-l' : '-b') . '">' . $str . '</span>';
            } else {
                $ret[] = $str;
            }
        }
        return implode(':', $ret);
    }

    // }}}
    // {{{ _coloredIdStr0()

    /**
     * IDカラー オリジナル着色用
     */
    function _coloredIdStr0($idstr, $id) {
        if (isset($this->idstyles[$id])) {
            $colored = $this->idstyles[$id];
        } else {
            require_once P2_LIB_DIR . '/ColoredIDStr0.php';
            $colored = coloredIdStyle($id, $this->thread->idcount[$id]);
            $this->idstyles[$id] = $colored;
        }

		return implode(':',
			array_map(
				array('ShowThreadPc','_makeColoredSpan'),
				explode(':', $idstr),$colored
			)
		);
    }

    // }}}
    // {{{ _coloredIdStr1()

    /**
     * IDカラー thermon版用
     */
    function _coloredIdStr1($idstr, $id) {
        require_once P2_LIB_DIR . '/ColoredIDStr.php';
        $colored = coloredIdStyle($idstr,$id,$this->thread->idcount[$id]);
        $idstr2=preg_split('/:/',$idstr,2); // コロンでID文字列を分割
        $ret=array_shift($idstr2);
        if ($colored[1]) {
                    $idstr2[1]=substr($idstr2[0],4);
                    $idstr2[0]=substr($idstr2[0],0,4);
        }

		return $ret.':'.implode('',
			array_map(
				array('ShowThreadPc','_makeColoredSpan'),
				$idstr2,$colored
			)
		);
    }

    // }}}
	static function _makeColoredSpan($str,$color) {
		return "<span style=\"{$color}\">{$str}</span>";
	}
    // {{{ cssClassedId()

    /**
     * IDカラーに使用するCSSクラス名をID文字列から算出して返す.
     */
    static public function cssClassedId($id) {
        return 'idcss-' . bin2hex(
            base64_decode(str_replace('.', '+', substr($id, 0, 8))));
    }

    // }}}
    // {{{ ユーティリティメソッド
    // {{{ checkQuoteResNums()

    /**
     * HTMLメッセージ中の引用レスの番号を再帰チェックする
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

		// 再帰リミッタ
		if ($this->_quote_check_depth > 60) {
			return array();
		} else {
			$this->_quote_check_depth++;
		}
//		trigger_error($this->_quote_check_depth .":checkQuoteResNums called:{$res_num}");
		$popup_res_nums = array();
		$quoters=array();

		$name = preg_replace('/(◆.*)/', '', $name, 1);

		// 名前
		if ($matches = $this->getQuoteResNumsName($name)) {
			$quoters=array_merge($quoters,$matches);
		}

		// レス参照先
		if ($ranges=$this->_getAnchorsFromMsg($msg)) {
			foreach ($ranges as $a_range) {
				try{
					if (preg_match($this->getAnchorRegex('/%range_delimiter%%prefix2%?/'),$a_range)) {continue;}
				} catch (Exception $e) {
					trigger_error(
						"正規表現が不正です。<br>".$e->getMessage(),E_USER_ERROR
					);
				}
				$quoters[] = (int) preg_replace("/\s/",'',mb_convert_kana($a_range, 'ns'));
			}
		}

		if ($_conf['backlink_list'] > 0 || $_conf['backlink_block'] > 0) {
			// 自分（レス番号$res_num）を参照しているレスを再帰的に検索
			$quoter_lists = $this->get_quote_from();
			$quoting_path=array();	// 参照側レス番号の配列
			$a_quotee=$res_num;	// レス番号（被参照側）

			// ループ中に$quoting_pathに要素を追加するので、foreachは使えない
			 do {
				if (!array_key_exists($a_quotee, $quoter_lists)) {continue;}

				// 配列のカウンタがリセットされるので、array_mergeが使えない
				// 参照側レス番号をレス番号リストに追加
				$unentried=array_diff($quoter_lists[$a_quotee],$quoting_path);	// $quoting_pathに未登録のレス番号を抜き出す
				foreach ($unentried as $quoter) {
					$quoting_path[]=$quoter;
				}
			} while(list( ,$a_quotee)=each($quoting_path));	// レス番号リストから被参照側レス番号を取得
			$quoters=array_merge($quoters,$quoting_path);
		}

		$quoters=array_unique($quoters);
		sort($quoters,SORT_NUMERIC);
		// 引用をさかのぼる
		foreach ($quoters as $a_quoter) {
			if (!$a_quoter || $a_quoter == $res_num) {continue;}
			// 参照側のレス番号が有効

			$popup_res_nums[$a_quoter]=true;
			// チェックしていない番号を再帰チェック
			if (!isset($this->_quote_res_nums_checked[$a_quoter])) {
				$this->_quote_res_nums_checked[$a_quoter] = true;

				if (isset($this->thread->datlines[$a_quoter_idx = $a_quoter - 1])) {
					$datalinear = $this->thread->explodeDatLine($this->thread->datlines[$a_quoter_idx]);
					$quote_name = $datalinear[0];
					$quote_msg = $datalinear[3];
					$popup_res_nums+= $this->checkQuoteResNums($a_quoter, $quote_name, $quote_msg);
				}
			}
		}

		$this->_quote_check_depth--;

		return $_cache[$matome][$res_num]=$popup_res_nums;
	}
    // }}}
    // {{{ imageHtmlPopup()

    /**
     * 画像をHTMLポップアップ&ポップアップウインドウサイズに合わせる
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
     * レスポップアップを非同期モードに加工する
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
     * 非同期読み込みで利用するJavaScriptオブジェクトを生成する
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
     * スマートポップアップメニューを生成するJavaScriptコードを生成する
     */
    public function getSpmObjJs($retry = false)
    {
        global $_conf, $STYLE;

        if (isset(self::$_spm_objects[$this->spmObjName])) {
            return $retry ? self::$_spm_objects[$this->spmObjName] : '';
        }

        $ttitle_en = UrlSafeBase64::encode($this->thread->ttitle);

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

        // エスケープ
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
    // {{{ transLinkDo()から呼び出されるURL書き換えメソッド
    /**
     * これらのメソッドは引数が処理対象パターンに合致しないとFALSEを返し、
     * transLinkDo()はFALSEが返ってくると$_url_handlersに登録されている次の関数/メソッドに処理させようとする。
     */
    // {{{ plugin_linkURL()

    /**
     * URLリンク
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

            // HTMLポップアップ
            if ($_conf['iframe_popup'] && $is_http) {
                // *pm 指定の場合のみ、特別に手動転送指定を追加する
                if (substr($_conf['through_ime'], -2) == 'pm') {
                    $pop_url = P2Util::throughIme($purl[0], -1);
                } else {
                    $pop_url = $link_url;
                }
                $link = $this->iframePopup(array($link_url, $pop_url), $str, $_conf['ext_win_target_at']);
            } else {
                $link = "<a href=\"{$link_url}\"{$_conf['ext_win_target_at']}>{$str}</a>";
            }

            // ブラクラチェッカ
            if ($_conf['brocra_checker_use'] && $_conf['brocra_checker_url'] && $is_http) {
                if (strlen($_conf['brocra_checker_query'])) {
                    $brocra_checker_url = $_conf['brocra_checker_url'] . '?' . $_conf['brocra_checker_query'] . '=' . rawurlencode($purl[0]);
                } else {
                    $brocra_checker_url = rtrim($_conf['brocra_checker_url'], '/') . '/' . $url;
                }
                $brocra_checker_url_orig = $brocra_checker_url;
                // ブラクラチェッカ・ime
                if ($_conf['through_ime']) {
                    $brocra_checker_url = P2Util::throughIme($brocra_checker_url);
                }
                $check_mark = 'チェック';
                $check_mark_prefix = '[';
                $check_mark_suffix = ']';
                // ブラクラチェッカ・HTMLポップアップ
                if ($_conf['iframe_popup']) {
                    // *pm 指定の場合のみ、特別に手動転送指定を追加する
                    if (substr($_conf['through_ime'], -2) == 'pm') {
                        $brocra_checker_url = P2Util::throughIme($brocra_checker_url_orig, -1);
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
     * 2ch bbspink    板リンク
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
            return "<a href=\"{$url}\" target=\"subject\">{$str}</a> [<a href=\"{$subject_url}\" target=\"subject\">板をp2で開く</a>]";
        }
        return FALSE;
    }

    // }}}
    // {{{ plugin_linkThread()

    /**
     * スレッドリンク
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
     * YouTubeリンク変換プラグイン
     *
     * Zend_Gdata_Youtubeを使えばサムネイルその他の情報を簡単に取得できるが...
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

            // HTMLポップアップ
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
{$link}<div class="preview-video preview-video-youtuve"><object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/{$id}" valuetype="ref" type="application/x-shockwave-flash"><param name="wmode" value="transparent"><embed src="http://www.youtube.com/v/{$id}" type="application/x-shockwave-flash" wmode="transparent" width="425" height="350"></object></div>
EOP;
            }
        }
        return FALSE;
    }

    // }}}
    // {{{ plugin_linkNicoNico()

    /**
     * ニコニコ動画変換プラグイン
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

            // HTMLポップアップ
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
     * 画像ポップアップ変換
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

        // 表示制限
        if (!$pre_thumb_unlimited && empty($pre_thumb_limit)) {
            return false;
        }

        if (preg_match('{^https?://.+?\\.(jpe?g|gif|png)$}i', $purl[0]) && empty($purl['query'])) {
            $pre_thumb_limit--; // 表示制限カウンタを下げる
            $img_tag = "<img class=\"thumbnail\" src=\"{$url}\" height=\"{$_conf['pre_thumb_height']}\" weight=\"{$_conf['pre_thumb_width']}\" hspace=\"4\" vspace=\"4\" align=\"middle\">";

            if ($_conf['iframe_popup']) {
                $view_img = $this->imageHtmlPopup($url, $img_tag, $str);
            } else {
                $view_img = "<a href=\"{$url}\"{$_conf['ext_win_target_at']}>{$img_tag}{$str}</a>";
            }

            // ブラクラチェッカ （プレビューとは相容れないのでコメントアウト）
            /*if ($_conf['brocra_checker_use']) {
                $link_url_en = rawurlencode($url);
                if ($_conf['iframe_popup'] == 3) {
                    $check_mark = '<img src="img/check.png" width="33" height="12" alt="">';
                    $check_mark_prefix = '';
                    $check_mark_suffix = '';
                } else {
                    $check_mark = 'チェック';
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
     * ImageCache2サムネイル変換
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
            // 準備
            $serial++;
            $thumb_id = 'thumbs' . $serial . $this->thumb_id_suffix;
            $tmp_thumb = './img/ic_load.png';
            $url_ht = $url;
            $url = $purl[0];
            $url_en = rawurlencode($url) .
                ($referer ? '&amp;ref=' . rawurlencode($referer) : '');
            $img_id = null;

            $icdb = new IC2_DataObject_Images;

            // r=0:リンク;r=1:リダイレクト;r=2:PHPで表示
            // t=0:オリジナル;t=1:PC用サムネイル;t=2:携帯用サムネイル;t=3:中間イメージ
            $img_url = 'ic2.php?r=1&amp;uri=' . $url_en;
            $thumb_url = 'ic2.php?r=1&amp;t=1&amp;uri=' . $url_en;

            // お気にスレ自動画像ランク
            $rank = null;
            if ($_conf['expack.ic2.fav_auto_rank']) {
                $rank = $this->getAutoFavRank();
                if ($rank !== null) $thumb_url .= '&rank=' . $rank;
            }

            // DBに画像情報が登録されていたとき
            if ($icdb->get($url)) {
                $img_id = $icdb->id;

                // ウィルスに感染していたファイルのとき
                if ($icdb->mime == 'clamscan/infected') {
                    return "<img class=\"thumbnail\" src=\"./img/x04.png\" width=\"32\" height=\"32\" hspace=\"4\" vspace=\"4\" align=\"middle\"> <s>{$str}</s>";
                }
                // あぼーん画像のとき
                if ($icdb->rank < 0) {
                    return "<img class=\"thumbnail\" src=\"./img/x01.png\" width=\"32\" height=\"32\" hspace=\"4\" vspace=\"4\" align=\"middle\"> <s>{$str}</s>";
                }

                // オリジナルがキャッシュされているときは画像を直接読み込む
                $_img_url = $this->thumbnailer->srcPath($icdb->size, $icdb->md5, $icdb->mime);
                if (file_exists($_img_url)) {
                    $img_url = $_img_url;
                    $cached = true;
                } else {
                    $cached = false;
                }

                // サムネイルが作成されていているときは画像を直接読み込む
                $_thumb_url = $this->thumbnailer->thumbPath($icdb->size, $icdb->md5, $icdb->mime);
                if (file_exists($_thumb_url)) {
                    $thumb_url = $_thumb_url;
                    // 自動スレタイメモ機能がONでスレタイが記録されていないときはDBを更新
                    if (!is_null($this->img_memo) && strpos($icdb->memo, $this->img_memo) === false){
                        $update = new IC2_DataObject_Images;
                        if (!is_null($icdb->memo) && strlen($icdb->memo) > 0) {
                            $update->memo = $this->img_memo . ' ' . $icdb->memo;
                        } else {
                            $update->memo = $this->img_memo;
                        }
                        $update->whereAddQuoted('uri', '=', $url);
                    }

                    // expack.ic2.fav_auto_rank_override の設定とランク条件がOKなら
                    // お気にスレ自動画像ランクを上書き更新
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
                }

                // サムネイルの画像サイズ
                $thumb_size = $this->thumbnailer->calc($icdb->width, $icdb->height);
                $thumb_size = preg_replace('/(\d+)x(\d+)/', 'width="$1" height="$2"', $thumb_size);
                $tmp_thumb = './img/ic_load1.png';

                $orig_img_url   = $img_url;
                $orig_thumb_url = $thumb_url;

            // 画像がキャッシュされていないとき
            // 自動スレタイメモ機能がONならクエリにUTF-8エンコードしたスレタイを含める
            } else {
                // 画像がブラックリストorエラーログにあるか確認
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

            // キャッシュされておらず、表示数制限が有効のとき
            if (!$cached && !$pre_thumb_unlimited && !$pre_thumb_ignore_limit) {
                // 表示制限を超えていたら、表示しない
                // 表示制限を超えていなければ、表示制限カウンタを下げる
                if ($pre_thumb_limit <= 0) {
                    $show_thumb = false;
                } else {
                    $show_thumb = true;
                    $pre_thumb_limit--;
                }
            } else {
                $show_thumb = true;
            }

            // 表示モード
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

            // ソースへのリンクをime付きで表示
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
     * 置換画像URL+ImageCache2
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

            // 準備
            $serial++;
            $thumb_id = 'thumbs' . $serial . $this->thumb_id_suffix;
            $tmp_thumb = './img/ic_load.png';

            $icdb = new IC2_DataObject_Images;

            // r=0:リンク;r=1:リダイレクト;r=2:PHPで表示
            // t=0:オリジナル;t=1:PC用サムネイル;t=2:携帯用サムネイル;t=3:中間イメージ
            // +Wiki
            $img_url = 'ic2.php?r=1&amp;uri=' . $url_en . $ref_en;
            $thumb_url = 'ic2.php?r=1&amp;t=1&amp;uri=' . $url_en . $ref_en;
            // お気にスレ自動画像ランク
            $rank = null;
            if ($_conf['expack.ic2.fav_auto_rank']) {
                $rank = $this->getAutoFavRank();
                if ($rank !== null) $thumb_url .= '&rank=' . $rank;
            }

            // DBに画像情報が登録されていたとき
            if ($icdb->get($v['url'])) {

                // ウィルスに感染していたファイルのとき
                if ($icdb->mime == 'clamscan/infected') {
                    $result .= "<img class=\"thumbnail\" src=\"./img/x04.png\" width=\"32\" height=\"32\" hspace=\"4\" vspace=\"4\" align=\"middle\">";
                    continue;
                }
                // あぼーん画像のとき
                if ($icdb->rank < 0) {
                    $result .= "<img class=\"thumbnail\" src=\"./img/x01.png\" width=\"32\" height=\"32\" hspace=\"4\" vspace=\"4\" align=\"middle\">";
                    continue;
                }

                // オリジナルがキャッシュされているときは画像を直接読み込む
                $_img_url = $this->thumbnailer->srcPath($icdb->size, $icdb->md5, $icdb->mime);
                if (file_exists($_img_url)) {
                    $img_url = $_img_url;
                    $cached = true;
                } else {
                    $cached = false;
                }

                // サムネイルが作成されていているときは画像を直接読み込む
                $_thumb_url = $this->thumbnailer->thumbPath($icdb->size, $icdb->md5, $icdb->mime);
                if (file_exists($_thumb_url)) {
                    $thumb_url = $_thumb_url;
                    // 自動スレタイメモ機能がONでスレタイが記録されていないときはDBを更新
                    if (!is_null($this->img_memo) && strpos($icdb->memo, $this->img_memo) === false){
                        $update = new IC2_DataObject_Images;
                        if (!is_null($icdb->memo) && strlen($icdb->memo) > 0) {
                            $update->memo = $this->img_memo . ' ' . $icdb->memo;
                        } else {
                            $update->memo = $this->img_memo;
                        }
                        $update->whereAddQuoted('uri', '=', $v['url']);
                    }

                    // expack.ic2.fav_auto_rank_override の設定とランク条件がOKなら
                    // お気にスレ自動画像ランクを上書き更新
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
                }

                // サムネイルの画像サイズ
                $thumb_size = $this->thumbnailer->calc($icdb->width, $icdb->height);
                $thumb_size = preg_replace('/(\d+)x(\d+)/', 'width="$1" height="$2"', $thumb_size);
                $tmp_thumb = './img/ic_load1.png';

                $orig_img_url   = $img_url;
                $orig_thumb_url = $thumb_url;

            // 画像がキャッシュされていないとき
            // 自動スレタイメモ機能がONならクエリにUTF-8エンコードしたスレタイを含める
            } else {
                // 画像がブラックリストorエラーログにあるか確認
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

            // キャッシュされておらず、表示数制限が有効のとき
            if (!$cached && !$pre_thumb_unlimited && !$pre_thumb_ignore_limit) {
                // 表示制限を超えていたら、表示しない
                // 表示制限を超えていなければ、表示制限カウンタを下げる
                if ($pre_thumb_limit <= 0) {
                    $show_thumb = false;
                } else {
                    $show_thumb = true;
                    $pre_thumb_limit--;
                }
            } else {
                $show_thumb = true;
            }

            // 表示モード
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
        // ソースへのリンクをime付きで表示
        $ime_url = P2Util::throughIme($url);
        $result .= "<a class=\"img_through_ime\" href=\"{$ime_url}\"{$_conf['ext_win_target_at']}>{$str}</a>";
        return $result;
    }

    /**
     * +Wiki:リンクプラグイン
     */
    function plugin_linkPlugin($url, $purl, $str)
    {
        global $linkplugin;
        return $linkplugin->replaceLinkToHTML($url, $str);
    }

    // }}}
    // {{{ plugin_imepita_to_imageCache2()

    /**
     * imepitaのURLを加工してImageCache2させるプラグイン
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
        if ($this->_quoter_list === null) {
            $this->_make_quote_from();  // 被レスデータ集計
        }
        $ret = array();
        foreach (array_filter($this->_quoter_list) as $resnum => $quoters) {
            if (!$this->isnum_in_resrange($resnum)) continue;
			$tmp=array_filter($quoters,array($this,'isnum_in_resrange'));
            if ($tmp) $ret[] = "{$resnum}:[" . join(',', $tmp) . "]";
        }
        $res= '{' . join(',', $ret) . '}';
		return $res;
    }

    private function isnum_in_resrange($num) {
		return ($num == 1 || ($num >= $this->thread->resrange['start'] && $num <= $this->thread->resrange['to']));
	}

    public function getResColorJs() {
        global $_conf, $STYLE;
        $fontstyle_bold = empty($STYLE['fontstyle_bold']) ? 'normal' : $STYLE['fontstyle_bold'];
        $fontweight_bold = empty($STYLE['fontweight_bold']) ? 'normal' : $STYLE['fontweight_bold'];
        $fontfamily_bold = $STYLE['fontfamily_bold'];
        $backlinks = $this->get_quotebacks_json();
        $colors = array();
        $backlink_colors = join(',',
            array_map(array('ShowThreadPc','_strQuoting'),
                explode(',', $_conf['backlink_coloring_track_colors']))
        );
        $prefix = $this->get_res_id();
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
        $ret = $this->_ids_for_render;
        if ($ret) {
            foreach ($ret as $id => &$count) {
                $count = "'{$id}':{$count}";
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
            array_map(array('ShowThreadPc','_strQuoting'),
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

	static function _strQuoting($x){
		return "'{$x}'";
	}

    public function getIdCountAverage() {
        if ($this->_idcount_average !== null) return $this->_idcount_average;

		$ranking=array_filter($this->thread->idcount,array('ShowThreadPc','_filterCount'));
		$param=count($ranking);

        $sum = 0;
        foreach ($ranking as $count) {
            $sum += $count;
        }
        return $this->_idcount_average = $param < 1 ? 0 : ceil($sum / $param);
    }

    public function getIdCountRank($rank) {
        if ($this->_idcount_tops !== null) return $this->_idcount_tops;
        $ranking = array_filter($this->thread->idcount,array('ShowThreadPc','_filterCount'));
        if (count($ranking) == 0) return 0;
        rsort($ranking);
        $result = count($ranking) >= $rank ? $ranking[$rank - 1] : $ranking[count($ranking) - 1];
        return $this->_idcount_tops = $result;
    }

	static function _filterCount($count) {
		return ($count>1);
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
