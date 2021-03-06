<?php
/*
	+live - ハイライトワードのチェック ../ShowThread.php より読み込まれる
*/
// 連鎖ハイライト
if ($_conf['live.highlight_chain'] && $highlight_matches=$this->_getAnchorsFromMsg($msg)) {
	$this->_highlight_chain_nums = array_unique($highlight_matches);
	if (array_intersect($this->_highlight_chain_nums, $this->_highlight_nums)) {
		$ngaborns_hits['highlight_chain']++;
		$type |= $this->_markHighlight($i, self::HIGHLIGHT_CHAIN, true);

	}
}

// ハイライトネームチェック
if ($this->ngAbornCheck('highlight_name', $name) !== false) {

	$ngaborns_hits['highlight_name']++;
	$type |= $this->_markHighlight($i, self::HIGHLIGHT_NAME, false);
}

// ハイライトメールチェック
if ($this->ngAbornCheck('highlight_mail', $mail) !== false) {
	$ngaborns_hits['highlight_mail']++;
	$type |= $this->_markHighlight($i, self::HIGHLIGHT_MAIL, false);
}

// ハイライトIDチェック
if ($this->ngAbornCheck('highlight_id', $date_id) !== false) {
	$ngaborns_hits['highlight_id']++;
	$type |= $this->_markHighlight($i, self::HIGHLIGHT_ID, false);
}

// ハイライトメッセージチェック
// この場合のみ、ヒットした条件の配列が返る
$a_highlight_msg = $this->ngAbornCheck('highlight_msg', $msg);
if ($a_highlight_msg !== false) {
	$ngaborns_hits['highlight_msg']++;
	$type |= $this->_markHighlight($i, self::HIGHLIGHT_MSG, true);
	$this->_highlight_msgs = $a_highlight_msg;
}

?>