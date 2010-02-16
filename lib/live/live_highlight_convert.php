<?php
/*
	+live - ハイライトワードの変換 ../ShowThreadPc.php より読み込まれる
*/

// 連鎖ハイライト変換
if ($ng_type & self::HIGHLIGHT_CHAIN) {
	$highlight_chain_nums = implode('|', array_intersect($this->_highlight_chain_nums, $this->_highlight_nums));
	$highlight_chain_nums = "(" . $highlight_chain_nums . ")(?!\d)(?![^<]*>)"; // 数字が一部被ってしまうアンカーとHTMLタグ内にマッチさせない
	$msg = preg_replace("(((?:&gt;|＞|-)+)($highlight_chain_nums))", "<span class=\"live_highlight_chain\">$1$2</span>", $msg);
}

// ハイライトメッセージ変換
if ($ng_type & self::HIGHLIGHT_MSG) {
	foreach ($this->_highlight_msgs as $highlight_word) {
		if (!preg_match("(<regex(?:i)?>)",$highlight_word)) {
			$highlight_word=quotemeta($highlight_word);
		}
		$highlight_word = "((" . $highlight_word . ")(?![^<]*>))"; // HTMLタグ内にマッチさせない
		if (P2_MBREGEX_AVAILABLE) {
			$replace_method = preg_match("(<(regex:i|i)>)", $highlight_word) ? 'mb_eregi_replace' : 'mb_ereg_replace';
		} else {
			$replace_method = 'preg_replace';
			if (preg_match("(<(regex:i|i)>)", $highlight_word)) {
				$highlight_word .='i';
			}
		}

		$highlight_word=preg_replace("(<(regex(:i)?|i)>)",'',$highlight_word);
		$msg = $replace_method($highlight_word, "<span class=\"live_highlight\">\\1</span>", $msg);
	}
}

// ハイライトネーム変換
if ($ng_type & self::HIGHLIGHT_NAME) {
	$name = preg_replace("(</?b>)", "", $name);	
	$name = "</b><span class=\"live_highlight\">$name</span><b>";
}

// ハイライトメール変換
if ($ng_type & self::HIGHLIGHT_MAIL) {
	$mail = "<span class=\"live_highlight\">$mail</span>";
}

// ハイライトID変換
if ($ng_type & self::HIGHLIGHT_ID) {
	$date_id = preg_replace("((ID:))", "<span class=\"live_highlight\">$1", $date_id ."</span>");
}

?>